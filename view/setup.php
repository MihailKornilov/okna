<?php

// ---===! setup !===--- ������ ��������

function setup() {
	$pages = array(
		'my' => '��� ���������',
		'worker' => '����������',
		'rekvisit' => '��������� �����������',
		'product' => '���� �������',
		'invoice' => '�����',
		'expense' => '��������� ��������',
		'zayavexpense' => '������� �� ������'
	);

	if(!RULES_WORKER)
		unset($pages['worker']);
	if(!RULES_REKVISIT)
		unset($pages['rekvisit']);
	if(!RULES_PRODUCT)
		unset($pages['product']);
	if(!RULES_INVOICE)
		unset($pages['invoice']);
	if(!RULES_ZAYAVRASHOD)
		unset($pages['zayavexpense']);

	$d = empty($_GET['d']) ? 'my' : $_GET['d'];

	switch($d) {
		default: $d = 'my';
		case 'my': $left = setup_my(); break;
		case 'worker':
			if(preg_match(REGEXP_NUMERIC, @$_GET['id'])) {
				$left = setup_worker_rules(intval($_GET['id']));
				break;
			}
			$left = setup_worker();
			break;
		case 'rekvisit': $left = setup_rekvisit(); break;
		case 'product':
			if(preg_match(REGEXP_NUMERIC, @$_GET['id'])) {
				$left = setup_product_sub(intval($_GET['id']));
				break;
			}
			$left = setup_product();
			break;
		case 'invoice': $left = setup_invoice(); break;
		case 'expense': $left = setup_expense(); break;
		case 'zayavexpense': $left = setup_zayavexpense(); break;
	}
	$links = '';
	foreach($pages as $p => $name)
		$links .= '<a href="'.URL.'&p=setup&d='.$p.'"'.($d == $p ? ' class="sel"' : '').'>'.$name.'</a>';
	return
	'<div id="setup">'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$left.
				'<td class="right"><div class="rightLink">'.$links.'</div>'.
		'</table>'.
	'</div>';
}//setup()

function setup_my() {
	return
	'<div id="setup_my">'.
		'<div class="headName">���-���</div>'.
		'<div class="_info">'.
			'<p>���-��� ��������� ��� ��������������� ������������� ����� ��������, '.
			'���� ������ ������������ ������� ������ � ����� �������� ���������.'.
			'<br />'.
			'<p>���-��� ����� ����� ������� ������ ����� ����� � ����������, '.
			'� ����� ��� ��������� �������� � ��������� � ������� 1-�� ����.'.
			'<br />'.
			'<p>���� �� �������� ���-���, ���������� � ������������, ����� �������� ���.'.
		'</div>'.
	(PIN ?
		'<div class="vkButton pinchange"><button>�������� ���-���</button></div>'.
		'<div class="vkButton pindel"><button>������� ���-���</button></div>'
		 :
		'<div class="vkButton pinset"><button>���������� ���-���</button></div>'
	).
	'</div>';
}//setup_my()

function setup_worker() {
	if(!RULES_WORKER)
		return _norules('���������� ������������');
	return
	'<div id="setup_worker">'.
		'<div class="headName">���������� ������������<a class="add">����� ���������</a></div>'.
		'<div id="spisok">'.setup_worker_spisok().'</div>'.
	'</div>';
}//setup_worker()
function setup_worker_spisok() {
	$sql = "SELECT *,
				   CONCAT(`first_name`,' ',`last_name`) AS `name`
			FROM `vk_user`
			WHERE `worker`=1
			  AND `viewer_id`!=982006
			ORDER BY `dtime_add`";
	$q = query($sql);
	$send = '';
	while($r = mysql_fetch_assoc($q)) {
		$send .=
		'<table class="unit" val="'.$r['viewer_id'].'">'.
			'<tr><td class="photo"><a href="'.URL.'&p=setup&d=worker&id='.$r['viewer_id'].'"><img src="'.$r['photo'].'"></a>'.
				'<td>'.($r['admin'] ? '' : '<div class="img_del"></div>').
					   '<a href="'.URL.'&p=setup&d=worker&id='.$r['viewer_id'].'" class="name">'.$r['name'].'</a>'.
					   '<div class="post">'.$r['post'].'</div>'.
					  ($r['enter_last'] != '0000-00-00 00:00:00' ? '<div class="activity">�������'.($r['sex'] == 1 ? 'a' : '').' � ���������� '.FullDataTime($r['enter_last']).'</div>' : '').
		'</table>';
	}
	return $send;
}//setup_worker_spisok()
function setup_worker_rules($viewer_id) {
	$u = _viewer($viewer_id);
	if(!RULES_WORKER)
		return _norules('��������� ���� ��� ���������� '.$u['name']);
	if(!isset($u['worker']))
		return '���������� �� ����������.';
	$rule = _viewerRules($viewer_id);
	$client_id = query_value("SELECT `id` FROM `client` WHERE `worker_id`=".$viewer_id);
	return
	'<script type="text/javascript">var RULES_VIEWER_ID='.$viewer_id.';</script>'.
	'<div id="setup_rules">'.

		'<table class="utab">'.
			'<tr><td>'.$u['photo'].
				'<td><div class="name">'.$u['name'].'</div>'.
					 ($viewer_id < VIEWER_MAX ? '<a href="http://vk.com/id'.$viewer_id.'" class="vklink" target="_blank">�������� VK</a>' : '').
					 '<a href="'.URL.'&p=report&d=salary&id='.$viewer_id.'" class="vklink">�������� �/�</a>'.
	   ($client_id ? '<a href="'.URL.'&p=client&d=info&id='.$client_id.'" class="vklink">���������� ��������</a>' : '').
		'</table>'.

		'<div class="headName">�����</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">�������:<td><input type="text" id="last_name" value="'.$u['last_name'].'" />'.
			'<tr><td class="lab">���:<td><input type="text" id="first_name" value="'.$u['first_name'].'" />'.
			'<tr><td class="lab">��������:<td><input type="text" id="middle_name" value="'.$u['middle_name'].'" />'.
			'<tr><td class="lab">���������:<td><input type="text" id="post" value="'.$u['post'].'" />'.
			'<tr><td><td><div class="vkButton g-save"><button>���������</button></div>'.
		'</table>'.

	(!$u['admin'] && $u['pin'] ?
		'<div class="headName">���-���</div>'.
		'<div class="vkButton pin-clear"><button>�������� ���-���</button></div>'
	: '').

	'<div class="headName">�������������</div>'.
	'<table class="rtab">'.
		'<tr><td class="lab">�� ����������<br />� ����������� �/�:<td>'._check('rules_nosalary', '', $rule['RULES_NOSALARY']).
		'<tr><td class="lab">��������� ����� ���<br />���������� ����� �� ������:<td>'._check('rules_zpzayavauto', '', $rule['RULES_ZPZAYAVAUTO']).
		'<tr><td><td><div class="vkButton dop-save"><button>���������</button></div>'.
	'</table>'.

	(!$u['admin'] && $viewer_id < VIEWER_MAX && RULES_RULES?
		'<div class="headName">�����</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">��������� ����<br />� ����������:<td>'._check('rules_appenter', '', $rule['RULES_APPENTER']).
		'</table>'.
		'<div class="app-div'.($rule['RULES_APPENTER'] ? '' : ' dn').'">'.
			'<table class="rtab">'.
				'<tr><td class="lab top">���������� �����������:'.
					'<td class="setup-div">'.
						_check('rules_worker', '����������', $rule['RULES_WORKER']).
						_check('rules_rules', '��������� ���� �����������', $rule['RULES_RULES']).
						_check('rules_rekvisit', '��������� �����������', $rule['RULES_REKVISIT']).
						_check('rules_product', '���� �������', $rule['RULES_PRODUCT']).
						_check('rules_invoice', '�����', $rule['RULES_INVOICE']).
						_check('rules_zayavrashod', '������� �� ������', $rule['RULES_ZAYAVRASHOD']).
				'<tr><td class="lab">����� ������� ��������:<td>'._check('rules_historyshow', '', $rule['RULES_HISTORYSHOW']).
				'<tr><td class="lab">����� ������ �������:<td><input type="hidden" id="rules_money" value="'.$rule['RULES_MONEY'].'" />'.
			'</table>'.
		'</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab"><td><div class="vkButton rules-save"><button>���������</button></div>'.
		'</table>'
	: '').
	'</div>';
}//setup_worker_rules()
function setup_worker_rules_save($post, $viewer_id) {
	$rules = array();
	foreach($post as $i => $v)
		if(preg_match('/^rules_/', $i))
			if(!preg_match(REGEXP_BOOL, $v))
				jsonError();
			else
				$rules[strtoupper($i)] = $v;

	$cur = query_ass("SELECT `key`,`value` FROM `vk_user_rules` WHERE `viewer_id`=".$viewer_id);
	$rules += $cur;
	foreach($rules as $i => $v)
		if(isset($cur[$i]))
			query("UPDATE `vk_user_rules` SET `value`=".$v." WHERE `key`='".$i."' AND `viewer_id`=".$viewer_id);
		else
			query("INSERT INTO `vk_user_rules` (
						`viewer_id`,
						`key`,
						`value`
					  ) VALUES (
					    ".$viewer_id.",
					    '".$i."',
					    ".$v."
					  )");
	xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
	xcache_unset(CACHE_PREFIX.'viewer_rules_'.$viewer_id);
}//setup_worker_rules_save()

function setup_rekvisit() {
	if(!RULES_REKVISIT)
		return _norules('��������� �����������');
	$sql = "SELECT * FROM `setup_global`";
	$g = mysql_fetch_assoc(query($sql));
	return
	'<div id="setup_rekvisit">'.
		'<div class="headName">��������� �����������</div>'.
		'<table class="t">'.
			'<tr><td class="label">�������� �����������:<td><input type="text" id="org_name" maxlength="100" value="'.$g['org_name'].'">'.
			'<tr><td class="label">����:<td><input type="text" id="ogrn" maxlength="100" value="'.$g['ogrn'].'">'.
			'<tr><td class="label">���:<td><input type="text" id="inn" maxlength="100" value="'.$g['inn'].'">'.
			'<tr><td class="label">���:<td><input type="text" id="kpp" maxlength="100" value="'.$g['kpp'].'">'.
			'<tr><td class="label top">����������� �����:<td><textarea id="yur_adres">'.$g['yur_adres'].'</textarea>'.
			'<tr><td class="label">��������:<td><input type="text" id="telefon" maxlength="100" value="'.$g['telefon'].'">'.
			'<tr><td class="label">����� �����:<td><input type="text" maxlength="100" id="ofice_adres" value="'.$g['ofice_adres'].'">'.
			'<tr><td><td><div class="vkButton"><button>���������</button></div>'.
		'</table>'.
	'</div>';
}//setup_rekvisit()

function setup_product() {
	if(!RULES_PRODUCT)
		return _norules('��������� ����� �������');
	return
	'<div id="setup_product">'.
		'<div class="headName">��������� ����� �������<a class="add">��������</a></div>'.
		'<div class="spisok">'.setup_product_spisok().'</div>'.
	'</div>';
}//setup_product()
function setup_product_spisok() {
	$sql = "SELECT `p`.*,
				   COUNT(`ps`.`id`) AS `sub`
			FROM `setup_product` AS `p`
			  LEFT JOIN `setup_product_sub` AS `ps`
			  ON `p`.`id`=`ps`.`product_id`
			GROUP BY `p`.`id`
			ORDER BY `p`.`name`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$product = array();
	while($r = mysql_fetch_assoc($q))
		$product[$r['id']] = $r;

	$sql = "SELECT `p`.`id`,
				   COUNT(`z`.`id`) AS `zayav`
			FROM `setup_product` AS `p`,
				 `zayav_product` AS `z`
			WHERE `p`.`id`=`z`.`product_id`
			GROUP BY `p`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$product[$r['id']]['zayav'] = $r['zayav'];

	$send = '<table class="_spisok">'.
				'<tr><th>������������'.
					'<th>�������'.
					'<th>���-��<br />������'.
					'<th>';
	foreach($product as $id => $r)
		$send .= '<tr val="'.$id.'">'.
					'<td class="name"><a href="'.URL.'&p=setup&d=product&id='.$id.'">'.$r['name'].'</a>'.
					'<td class="sub">'.($r['sub'] ? $r['sub'] : '').
					'<td class="zayav">'.(isset($r['zayav']) ? $r['zayav'] : '').
					'<td><div class="img_edit"></div>'.
						($r['sub'] || isset($r['zayav']) ? '' :'<div class="img_del"></div>');
	$send .= '</table>';
	return $send;
}//setup_product_spisok()

function setup_product_sub($product_id) {
	if(!RULES_PRODUCT)
		return _norules('��������� �������� �������');
	$sql = "SELECT * FROM `setup_product` WHERE `id`=".$product_id;
	if(!$pr = mysql_fetch_assoc(query($sql)))
		return '������� id = '.$product_id.' �� ����������.';
	return
	'<script type="text/javascript">var PRODUCT_ID='.$product_id.';</script>'.
	'<div id="setup_product_sub">'.
		'<a href="'.URL.'&p=setup&d=product"><< ����� � ����� �������</a>'.
		'<div class="headName">������ �������� ������� ��� "'.$pr['name'].'"<a class="add">��������</a></div>'.
		'<div class="spisok">'.setup_product_sub_spisok($product_id).'</div>'.
	'</div>';
}//setup_product_sub()
function setup_product_sub_spisok($product_id) {
	$sql = "SELECT `p`.`id`,
				   `p`.`name`,
				   COUNT(`z`.`id`) AS `zayav`
			FROM `setup_product_sub` AS `p`
				 LEFT JOIN `zayav_product` AS `z`
				 ON `p`.`id`=`z`.`product_sub_id`
			WHERE `p`.`product_id`=".$product_id."
			GROUP BY `p`.`id`
			ORDER BY `name`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$send = '<table class="_spisok">'.
				 '<tr><th>������������'.
					 '<th>���-��<br />������'.
					 '<th>';
	while($r = mysql_fetch_assoc($q))
		$send .= '<tr val="'.$r['id'].'">'.
			 '<td class="name">'.$r['name'].
			 '<td class="zayav">'.($r['zayav'] ? $r['zayav'] : '').
			 '<td><div class="img_edit"></div>'.
					($r['zayav'] ? '' : '<div class="img_del"></div>');
		$send .= '</table>';
	return $send;
}//setup_product_sub_spisok()

function setup_invoice() {
	if(!RULES_INVOICE)
		return _norules('��������� ������ ��� ����� ��������');
	return
	'<div id="setup_invoice">'.
		'<div class="headName">���������� �������<a class="add">����� ����</a></div>'.
		'<div class="spisok">'.setup_invoice_spisok().'</div>'.
	'</div>';
}//setup_invoice()
function setup_invoice_spisok() {
	$sql = "SELECT * FROM `invoice` WHERE  !`deleted` ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$spisok = array();
	while($r = mysql_fetch_assoc($q)) {
		$r['worker'] = array();
		if($r['visible'])
			foreach(explode(',', $r['visible']) as $i)
				$r['worker'][] = _viewer($i, 'name');
		$spisok[$r['id']] = $r;
	}

	$send =
	'<table class="_spisok">'.
		'<tr><th>������������'.
			'<th>�������������'.
			'<th>���������<br />��� �����������'.
			'<th>';
	foreach($spisok as $id => $r)
		$send .=
		'<tr val="'.$id.'">'.
			'<td class="name">'.
				'<div>'.$r['name'].'</div>'.
				'<pre>'.$r['about'].'</pre>'.
			'<td class="confirm">'.
				($r['confirm_income'] ? '����������� �� ����' : '').
				($r['confirm_transfer'] ? ($r['confirm_income'] ? ',<br />' : '').'��������' : '').
				'<input type="hidden" class="confirm_income" value="'.$r['confirm_income'].'" />'.
				'<input type="hidden" class="confirm_transfer" value="'.$r['confirm_transfer'].'" />'.
		'<td class="visible">'.
				implode('<br />', $r['worker']).
				'<input type="hidden" class="visible_id" value="'.(empty($r['worker']) ? 0 : $r['visible']).'" />'.
		'<td class="set">'.
				'<div class="img_edit"></div>';
				//'<div class="img_del"></div>'
	$send .= '</table>';
	return $send;
}//setup_invoice_spisok()

function setup_expense() {
	return
	'<div id="setup_expense">'.
		'<div class="headName">��������� �������� �����������<a class="add">��������</a></div>'.
		'<div class="spisok">'.setup_expense_spisok().'</div>'.
	'</div>';
}//setup_expense()
function setup_expense_spisok() {
	$sql = "SELECT `s`.*,
				   COUNT(`m`.`id`) AS `use`
			FROM `setup_expense` AS `s`
			  LEFT JOIN `money` AS `m`
			  ON `s`.`id`=`m`.`expense_id` AND `m`.`deleted`=0
			GROUP BY `s`.`id`
			ORDER BY `s`.`sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$rashod = array();
	while($r = mysql_fetch_assoc($q))
		$rashod[$r['id']] = $r;

	$send =
		'<table class="_spisok">'.
			'<tr><th class="name">������������'.
				'<th class="worker">����������<br />������<br />�����������'.
				'<th class="use">���-��<br />�������'.
				'<th class="set">'.
		'</table>'.
		'<dl class="_sort" val="setup_expense">';
	foreach($rashod as $id => $r) {
		$send .='<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="worker">'.($r['show_worker'] ? '��' : '').
					'<td class="use">'.($r['use'] ? $r['use'] : '').
					'<td class="set">'.
						'<div class="img_edit"></div>'.
						(!$r['use'] ? '<div class="img_del"></div>' : '').
			'</table>';
	}
	$send .= '</dl>';
	return $send;
}//setup_expense_spisok()

function setup_zayavexpense() {
	if(!RULES_ZAYAVRASHOD)
		return _norules('��������� �������� �� ������');
	return
	'<div id="setup_zayavexpense">'.
		'<div class="headName">��������� ��������� �������� �� ������<a class="add">��������</a></div>'.
		'<div class="spisok">'.setup_zayavexpense_spisok().'</div>'.
	'</div>';
}//setup_zayavexpense()
function setup_zayavexpense_spisok() {
	$sql = "SELECT `s`.*,
				   COUNT(`zr`.`id`) AS `use`
			FROM `setup_zayavexpense` AS `s`
			  LEFT JOIN `zayav_expense` AS `zr`
			  ON `s`.`id`=`zr`.`category_id`
			GROUP BY `s`.`id`
			ORDER BY `s`.`sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$rashod = array();
	while($r = mysql_fetch_assoc($q))
		$rashod[$r['id']] = $r;

	$send =
	'<table class="_spisok">'.
		'<tr><th class="name">������������'.
			'<th class="txt">����������<br />���������<br />����'.
			'<th class="worker">����������<br />������<br />�����������'.
			'<th class="use">���-��<br />�������'.
			'<th class="set">'.
	'</table>'.
	'<dl class="_sort" val="setup_zayavexpense">';
	foreach($rashod as $id => $r) {
		$send .='<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="txt">'.($r['show_txt'] ? '��' : '').
					'<td class="worker">'.($r['show_worker'] ? '��' : '').
					'<td class="use">'.($r['use'] ? $r['use'] : '').
					'<td class="set">'.
						'<div class="img_edit"></div>'.
						(!$r['use'] ? '<div class="img_del"></div>' : '').
			'</table>';
	}
	$send .= '</dl>';
	return $send;
}//setup_zayavexpense_spisok()
