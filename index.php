<?php
require_once 'config.php';

_header();
_hashRead();

if(!AUTH || !RULES_APPENTER)
	$html .= _noauth();
elseif(PIN_ENTER) {
	xcache_unset(PIN_TIME_KEY);
	$html .= pin_enter();
} else {
	xcache_set(PIN_TIME_KEY, time(), 10800);
	_mainLinks();
	switch($_GET['p']) {
		case 'client':
			switch(@$_GET['d']) {
				case 'info':
					if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
						$html .= '�������� �� ����������';
						break;
					}
					$html .= client_info(intval($_GET['id']));
					break;
				default:
					$html .= client_list(client_data());
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
						$html .= '�������� �� ����������';
						break;
					}
					$html .= zayav_info(intval($_GET['id']));
					break;
				default: $html .= zayav();
			}
			break;
		case 'remind': $html .= remind(); break;
		case 'report': $html .= report(); break;
		case 'setup': $html .= setup(); break;
		default: header('Location:'.URL.'&p=zayav');
	}
}

_footer();
mysql_close();
echo $html;