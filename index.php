<?php
require_once 'config.php';


$html = _header();
$html .= _menu();
$html .= _global_index();

switch(@$_GET['p']) {
}


$html .= _footer();

die($html);
