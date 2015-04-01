<?php
require_once 'config.php';

_header();
_hashRead();

if(!AUTH || !RULES_APPENTER)
	$html .= _noauth();
elseif(PIN_ENTER) {
	unset($_SESSION[PIN_TIME_KEY]);
	$html .= pin_enter();
} else {
	$_SESSION[PIN_TIME_KEY] = time() + PIN_TIME_LEN;
	_mainLinks();
	switch($_GET['p']) {
		case 'client':
			switch(@$_GET['d']) {
				case 'info':
					if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
						$html .= 'Страницы не существует';
						break;
					}
					$html .= client_info(intval($_GET['id']));
					break;
				default:
					$v = array();
					if(HASH_VALUES) {
						$ex = explode('.', HASH_VALUES);
						foreach($ex as $r) {
							$arr = explode('=', $r);
							$v[$arr[0]] = $arr[1];
						}
					} else {
						foreach($_COOKIE as $k => $val) {
							$arr = explode('client_', $k);
							if(isset($arr[1]))
								$v[$arr[1]] = $val;
						}
					}
					$v['find'] = unescape(@$v['find']);
					$html .= client_list($v);
			}
			break;
		case 'zayav':
			switch(@$_GET['d']) {
				case 'add':
					$v = array(
						'client_id' => 0,
						'back' => 'zayav'
					);
					if(isset($_GET['id']) && preg_match(REGEXP_NUMERIC, $_GET['id']) && $_GET['id'] > 0)
						$v['client_id'] = intval($_GET['id']);
					if(@$_GET['back'] == 'client' && $v['client_id'] > 0)
						$v['back'] = 'client&d=info&id='.$v['client_id'];
					$html .= zayav_add($v);
					break;
				case 'info':
					if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
						$html .= 'Страницы не существует';
						break;
					}
					$html .= zayav_info(intval($_GET['id']));
					break;
				default: $html .= zayav();
			}
			break;
		case 'remind':
			$remind = _remind();
			$html .= $remind['list'];
			break;
		case 'report': $html .= report(); break;
		case 'setup': $html .= setup(); break;
		case 'sa':
			if(!SA)
				header('Location:'.URL.'&p=zayav');
			require_once('view/sa.php');
			$html .= sa_index();
			break;
		default: header('Location:'.URL.'&p=zayav');
	}
}

_footer();
mysql_close();
echo $html;