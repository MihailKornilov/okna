<?php
require_once('config.php');
require_once('view/ws.php');

_hashRead();
_header();

switch($_GET['p']) {
    default: header('Location:'.URL.'&p=zayav');
}

_footer();
mysql_close();
echo $html;