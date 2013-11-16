<?php
require_once('config.php');

_hashRead();
_header();

if(!AUTH || !RULES_APPENTER)
    $html .= '<div class="noauth"><div>Недостаточно прав.</div></div>';
else {
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
                        $html .= 'Страницы не существует';
                        break;
                    }
                    $html .= zayav_info(intval($_GET['id']));
                    break;
                default:
                    $values = array();
                    if(HASH_VALUES) {
                        $ex = explode('.', HASH_VALUES);
                        foreach($ex as $r) {
                            $arr = explode('=', $r);
                            $values[$arr[0]] = $arr[1];
                        }
                    } else {
                        foreach($_COOKIE as $k => $val) {
                            $arr = explode('zayav_', $k);
                            if(isset($arr[1]))
                                $values[$arr[1]] = $val;
                        }
                    }
                    $values = array(
                        'find' => isset($values['find']) ? unescape($values['find']) : '',
                        'sort' => isset($values['sort']) ? intval($values['sort']) : 1,
                        'desc' => isset($values['desc']) && intval($values['desc']) == 1 ? 1 : 0,
                        'status' => isset($values['status']) ? intval($values['status']) : 0,
                    );
                    $html .= zayav_list(zayav_data(1, zayavfilter($values)), $values);
            }
            break;
        case 'report': $html .= report(); break;
        case 'setup':
            $html .= RULES_SETUP ? setup() : _norules('Настройки');
            break;
        default: header('Location:'.URL.'&p=zayav');
    }
}

_footer();
mysql_close();
echo $html;