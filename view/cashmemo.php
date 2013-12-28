<?php
/*
 * Распечатка товарного чека
*/
require_once('../config.php');

if(!preg_match(REGEXP_NUMERIC, @$_GET['id'])) {
	echo 'Некорректный id.';
	exit;
}
$id = intval($_GET['id']);
$sql = "SELECT * FROM `money` WHERE `deleted`=0 AND `id`=".$id;
if(!$r = mysql_fetch_assoc(query($sql))) {
	echo 'Платежа id = '.$id.' не существует.';
	exit;
}

require_once(VKPATH.'clsMsDocGenerator.php');
$doc = new clsMsDocGenerator(
	$pageOrientation = 'PORTRAIT',
	$pageType = 'A4',
	$cssFile = DOCUMENT_ROOT.'/css/dogovor.css',
	$topMargin = 1,
	$rightMargin = 2,
	$bottomMargin = 1,
	$leftMargin = 1
);
$doc->addParagraph(cashmemoParagraph($id));
$doc->output('cash-memo_'.$id.'.doc');
mysql_close();