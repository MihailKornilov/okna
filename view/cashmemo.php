<?php
/*
 * ���������� ��������� ����
*/
require_once('../config.php');

if(!preg_match(REGEXP_NUMERIC, @$_GET['id'])) {
	echo '������������ id.';
	exit;
}
$id = intval($_GET['id']);
$sql = "SELECT * FROM `money` WHERE `deleted`=0 AND `id`=".$id;
if(!$r = mysql_fetch_assoc(query($sql))) {
	echo '������� id = '.$id.' �� ����������.';
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
	'<div class="org-name">�������� � ������������ ���������������� <b>�'.$g['org_name'].'�</b></div>'.
	'<div class="rekvisit">'.
		'��� '.$g['inn'].'<br />'.
		'���� '.$g['ogrn'].'<br />'.
		'��� '.$g['kpp'].'<br />'.
		str_replace("\n", '<br />', $g['yur_adres']).'<br />'.
		'���.: '.$g['telefon'].
	'</div>'.
	'<div class="head">�������� ��� �'.$r['id'].'</div>'.
	'<div class="shop">�������</div>'.
	'<div class="shop-about">(������������ ��������, ������������ �������������, ������������� ��������, � �.�.)</div>'.
	'<table class="tab">'.
		'<tr><th>�<br />�.�.'.
			'<th>������������ ������'.
			'<th>����������'.
			'<th>����'.
			'<th>�����'.
		'<tr><td class="nomer">1'.
			'<td class="about">'.
				'������'.
				($zayav['dogovor_id'] ? ' �� �������� �'.$dog['nomer'] : '').
				' �� '.
				($r['zayav_id'] ? zayav_product_spisok($r['zayav_id'], 'cash') : '"'.$r['prim'].'"').
			'<td class="count">1.00'.
			'<td class="sum">'.$r['sum'].'.00'.
			'<td class="summa">'.$r['sum'].'.00'.
	'</table>'.
	'<div class="summa-propis">'.numberToWord($r['sum'], 1).' ����'._end($r['sum'], '�', '�', '��').'</div>'.
	'<div class="shop-about">(����� ��������)</div>'.
	'<table class="podpis">'.
		'<tr><td>�������� ______________________<div class="prod-bot">(�������)</div>'.
			'<td><u>/��������� �.�./</u><div class="r-bot">(����������� �������)</div>'.
	'</table>'
);
$doc->output('cash-memo_'.$id.'.doc');
mysql_close();