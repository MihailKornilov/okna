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

$sql = "SELECT * FROM `setup_global`";
$g = mysql_fetch_assoc(query($sql));

$zayav = query_assoc("SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$r['zayav_id']);
$dog = query_assoc("SELECT * FROM `zayav_dogovor` WHERE `deleted`=0 AND `zayav_id`=".$r['zayav_id']);

require_once(VKPATH.'clsMsDocGenerator.php');
$doc = new clsMsDocGenerator(
	$pageOrientation = 'PORTRAIT',
	$pageType = 'A4',
	$cssFile = DOCUMENT_ROOT.'/css/cashmemo.css',
	$topMargin = 1.0,
	$rightMargin = 1.5,
	$bottomMargin = 1.0,
	$leftMargin = 3.0
);
$doc->addParagraph(
	'<div class="org-name">Общество с ограниченной ответственностью <b>«'.$g['org_name'].'»</b></div>'.
	'<div class="rekvisit">'.
		'ИНН '.$g['inn'].'<br />'.
		'ОГРН '.$g['ogrn'].'<br />'.
		'КПП '.$g['kpp'].'<br />'.
		str_replace("\n", '<br />', $g['yur_adres']).'<br />'.
		'Тел.: '.$g['telefon'].
	'</div>'.
	'<div class="head">Товарный чек №'.$r['id'].'</div>'.
	'<div class="shop">Магазин</div>'.
	'<div class="shop-about">(наименование магазина, структурного подразделения, транспортного средства, и т.д.)</div>'.
	'<table class="tab">'.
		'<tr><th>№<br />п.п.'.
			'<th>Наименование товара'.
			'<th>Количество'.
			'<th>Цена'.
			'<th>Сумма'.
		'<tr><td class="nomer">1'.
			'<td class="about">'.
				'Оплата'.
				($zayav['dogovor_id'] ? ' по договору №'.$dog['nomer'] : '').
				' за '.
				($r['zayav_id'] ? zayav_product_spisok($r['zayav_id'], 'cash') : '"'.$r['prim'].'"').
			'<td class="count">1.00'.
			'<td class="sum">'.$r['sum'].'.00'.
			'<td class="summa">'.$r['sum'].'.00'.
	'</table>'.
	'<div class="summa-propis">'.numberToWord($r['sum'], 1).' рубл'._end($r['sum'], 'ь', 'я', 'ей').'</div>'.
	'<div class="shop-about">(сумма прописью)</div>'.
	'<table class="podpis">'.
		'<tr><td>Продавец ______________________<div class="prod-bot">(подпись)</div>'.
			'<td><u>/Билоченко Ю.А./</u><div class="r-bot">(расшифровка подписи)</div>'.
	'</table>'
);
$doc->output('cash-memo_'.$id.'.doc');
mysql_close();