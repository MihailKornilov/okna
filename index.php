<?php
require_once('config.php');

_hashRead();
_header();

switch($_GET['p']) {
    case 'client':
        _mainLinks();
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
        _mainLinks();
        $html .= zayav_list('');
        break;
    default: header('Location:'.URL.'&p=zayav');
}

_footer();
mysql_close();
echo $html;