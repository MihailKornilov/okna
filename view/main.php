<?php
function _hashRead() {
	if(PIN_ENTER) { // ���� ��������� ���-���, hash ����������� � cookie
		setcookie('hash', empty($_GET['hash']) ? @$_COOKIE['hash'] : $_GET['hash'], time() + 2592000, '/');
		return;
	}

	$_GET['hash'] = isset($_COOKIE['hash']) ? $_COOKIE['hash'] : @$_GET['hash'];
	setcookie('hash', '', time() - 5, '/');
	$_GET['p'] = isset($_GET['p']) ? $_GET['p'] : 'zayav';
	if(empty($_GET['hash'])) {
		define('HASH_VALUES', false);
		if(APP_START) {// �������������� ��������� ���������� ��������
			$_GET['p'] = isset($_COOKIE['p']) ? $_COOKIE['p'] : $_GET['p'];
			$_GET['d'] = isset($_COOKIE['d']) ? $_COOKIE['d'] : '';
			$_GET['d1'] = isset($_COOKIE['d1']) ? $_COOKIE['d1'] : '';
			$_GET['id'] = isset($_COOKIE['id']) ? $_COOKIE['id'] : '';
		} else
			_hashCookieSet();
		return;
	}
	$ex = explode('.', $_GET['hash']);
	$r = explode('_', $ex[0]);
	unset($ex[0]);
	define('HASH_VALUES', empty($ex) ? false : implode('.', $ex));
	$_GET['p'] = $r[0];
	unset($_GET['d']);
	unset($_GET['d1']);
	unset($_GET['id']);
	switch($_GET['p']) {
		case 'client':
			if(isset($r[1]))
				if(preg_match(REGEXP_NUMERIC, $r[1])) {
					$_GET['d'] = 'info';
					$_GET['id'] = intval($r[1]);
				}
			break;
		case 'zayav':
			if(isset($r[1]))
				if(preg_match(REGEXP_NUMERIC, $r[1])) {
					$_GET['d'] = 'info';
					$_GET['id'] = intval($r[1]);
				} else {
					$_GET['d'] = $r[1];
					if(isset($r[2]))
						$_GET['id'] = intval($r[2]);
				}
			break;
		default:
			if(isset($r[1])) {
				$_GET['d'] = $r[1];
				if(isset($r[2]))
					$_GET['d1'] = $r[2];
			}
	}
	_hashCookieSet();
}//_hashRead()
function _hashCookieSet() {
	global $html;
	setcookie('p', $_GET['p'], time() + 2592000, '/');
	setcookie('d', isset($_GET['d']) ? $_GET['d'] : '', time() + 2592000, '/');
	setcookie('d1', isset($_GET['d1']) ? $_GET['d1'] : '', time() + 2592000, '/');
	setcookie('id', isset($_GET['id']) ? $_GET['id'] : '', time() + 2592000, '/');
	$getArr = array(
		'start' => 1,
		'api_url' => 1,
		'api_id' => 1,
		'api_settings' => 1,
		'viewer_id' => 1,
		'viewer_type' => 1,
		'sid' => 1,
		'secret' => 1,
		'access_token' => 1,
		'user_id' => 1,
		'group_id' => 1,
		'is_app_user' => 1,
		'auth_key' => 1,
		'language' => 1,
		'parent_language' => 1,
		'ad_info' => 1,
		'is_secure' => 1,
		'referrer' => 1,
		'lc_name' => 1,
		'hash' => 1
	);
	$gValues = array();
	foreach($_GET as $k => $val) {
		if(isset($getArr[$k]) || empty($_GET[$k])) continue;
		$gValues[] = '"'.$k.'":"'.$val.'"';
	}
	$html .= '<script type="text/javascript">hashSet({'.implode(',', $gValues).'})</script>';
}//_hashCookieSet()
function _cacheClear() {
	$sql = "SELECT `viewer_id` FROM `vk_user` WHERE `worker`=1";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		xcache_unset(CACHE_PREFIX.'viewer_'.$r['viewer_id']);
		xcache_unset(CACHE_PREFIX.'viewer_rules_'.$r['viewer_id']);
		xcache_unset(CACHE_PREFIX.'pin_enter_count'.$r['viewer_id']);
	}
	query("UPDATE `setup_global` SET `version`=`version`+1");

	xcache_unset(CACHE_PREFIX.'setup_global');
	xcache_unset(CACHE_PREFIX.'product');
	xcache_unset(CACHE_PREFIX.'product_sub');
	xcache_unset(CACHE_PREFIX.'invoice');
	xcache_unset(CACHE_PREFIX.'expense');
	xcache_unset(CACHE_PREFIX.'zayav_expense');
	GvaluesCreate();
}//_cacheClear()

function _header() {
	global $html;
	$html =
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
		'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.

		'<head>'.
		'<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
		'<title>Evrookna - ���������� '.APP_ID.'</title>'.

		_api_scripts().

		'<script type="text/javascript" src="'.APP_HTML.'/js/G_values.js?'.G_VALUES_VERSION.'"></script>'.


		'<link rel="stylesheet" type="text/css" href="'.APP_HTML.'/css/main'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" />'.
		'<script type="text/javascript" src="'.APP_HTML.'/js/main'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.

		(@$_GET['p'] == 'setup' ? '<script type="text/javascript" src="'.APP_HTML.'/js/setup'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>' : '').

		//������� � ����� ��� �������������������
		(@$_GET['p'] == 'sa' ?
			'<link rel="stylesheet" type="text/css" href="'.APP_HTML.'/css/sa'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" />'.
			'<script type="text/javascript" src="'.APP_HTML.'/js/sa'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'
		: '').

		'</head>'.
		'<body>'.
			'<div id="frameBody">'.
				'<iframe id="frameHidden" name="frameHidden"></iframe>';
}//_header()

function GvaluesCreate() {//����������� ����� G_values.js
	$save = //'function _toSpisok(s){var a=[];for(k in s)a.push({uid:k,title:s[k]});return a}'.
		'function _toAss(s){var a=[];for(var n=0;n<s.length;a[s[n].uid]=s[n].title,n++);return a}'.
		'var '.
		"\n".'WORKER_SPISOK='.query_selJson("SELECT `viewer_id`,CONCAT(`first_name`,' ',`last_name`) FROM `vk_user`
											 WHERE `worker`=1
											   AND `viewer_id`!=982006
											 ORDER BY `dtime_add`").','.
		"\n".'WORKER_ASS=_toAss(WORKER_SPISOK),'.
		"\n".'PRODUCT_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_product` ORDER BY `name`").','.
		"\n".'PRODUCT_ASS=_toAss(PRODUCT_SPISOK),'.
		"\n".'INVOICE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `invoice` WHERE !`deleted` ORDER BY `id`").','.
		"\n".'INVOICE_ASS=_toAss(INVOICE_SPISOK),'.
		"\n".'INVOICE_CONFIRM_INCOME='.query_ptpJson("SELECT `id`,1 FROM `invoice` WHERE !`deleted` AND `confirm_income`").','.
		"\n".'INVOICE_CONFIRM_TRANSFER='.query_ptpJson("SELECT `id`,1 FROM `invoice` WHERE !`deleted` AND `confirm_transfer`").','.
		"\n".'EXPENSE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_expense` ORDER BY `sort`").','.
		"\n".'EXPENSE_WORKER='.query_ptpJson("SELECT `id`,`show_worker` FROM `setup_expense` WHERE `show_worker`").','.
		"\n".'ZAYAVEXPENSE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_zayavexpense` ORDER BY `sort`").','.
		"\n".'ZAYAVEXPENSE_TXT='.query_ptpJson("SELECT `id`,`show_txt` FROM `setup_zayavexpense` WHERE `show_txt`").','.
		"\n".'ZAYAVEXPENSE_WORKER='.query_ptpJson("SELECT `id`,`show_worker` FROM `setup_zayavexpense` WHERE `show_worker`").','.
		"\n".'ZAMER_HOUR=['.
				'{uid:10,title:10},'.
				'{uid:11,title:11},'.
				'{uid:12,title:12},'.
				'{uid:13,title:13},'.
				'{uid:14,title:14},'.
				'{uid:15,title:15},'.
				'{uid:16,title:16},'.
				'{uid:17,title:17},'.
				'{uid:18,title:18},'.
				'{uid:19,title:19},'.
				'{uid:20,title:20},'.
				'{uid:21,title:21}],'.
		"\n".'ZAMER_MIN=['.
				'{uid:0,title:"00"},'.
				'{uid:10,title:10},'.
				'{uid:20,title:20},'.
				'{uid:30,title:30},'.
				'{uid:40,title:40},'.
				'{uid:50,title:50}],'.
		"\n".'ZAMER_DURATION='._selJson(_zamerDuration()).','.
		"\n".'HISTORY_GROUP='._selJson(history_group_name()).','.
		"\n".'PRODUCT_SUB_SPISOK='.Gvalues_obj('setup_product_sub', '`product_id`,`name`', 'product_id');

	$fp = fopen(APP_PATH.'/js/G_values.js','w+');
	fwrite($fp, $save.';');
	fclose($fp);

	query("UPDATE `setup_global` SET `g_values`=`g_values`+1");
	xcache_unset(CACHE_PREFIX.'setup_global');
}//GvaluesCreate()

function _product($product_id=false) {//������ ������� ��� ������
	if(!defined('PRODUCT_LOADED') || $product_id === false) {
		$key = CACHE_PREFIX.'product';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT `id`,`name` FROM `setup_product` ORDER BY `name`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = $r['name'];
			xcache_set($key, $arr, 86400);
		}
		if(!defined('PRODUCT_LOADED')) {
			foreach($arr as $id => $name)
				define('PRODUCT_'.$id, $name);
			define('PRODUCT_0', '');
			define('PRODUCT_LOADED', true);
		}
	}
	return $product_id !== false ? constant('PRODUCT_'.$product_id) : $arr;
}//_product()
function _productSub($product_id=false) {//������ ������� ��� ������
	if(!defined('PRODUCT_SUB_LOADED') || $product_id === false) {
		$key = CACHE_PREFIX.'product_sub';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT `id`,`name` FROM `setup_product_sub` ORDER BY `product_id`,`name`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = $r['name'];
			xcache_set($key, $arr, 86400);
		}
		if(!defined('PRODUCT_SUB_LOADED')) {
			foreach($arr as $id => $name)
				define('PRODUCT_SUB_'.$id, $name);
			define('PRODUCT_SUB_0', '');
			define('PRODUCT_SUB_LOADED', true);
		}
	}
	return $product_id !== false ? constant('PRODUCT_SUB_'.$product_id) : $arr;
}//_productSub()
function _invoice($type_id=false, $i='name') {//������ ������� ��� ������
	if(!defined('INVOICE_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'invoice';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT * FROM `invoice` ORDER BY `id`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q)) {
				$r['start'] = round($r['start'], 2);
				$arr[$r['id']] = $r;
			}
			xcache_set($key, $arr, 86400);
		}
		if(!defined('INVOICE_LOADED')) {
			foreach($arr as $id => $r) {
				define('INVOICE_'.$id, $r['name']);
				define('INVOICE_INCOME_'.$id, $r['confirm_income']);
				define('INVOICE_TRANSFER_'.$id, $r['confirm_transfer']);
				define('INVOICE_START_'.$id, $r['start']);
			}
			define('INVOICE_0', '');
			define('INVOICE_START_0', 0);
			define('INVOICE_INCOME_0', 0);
			define('INVOICE_TRANSFER_0', 0);
			define('INVOICE_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'start')
		return constant('INVOICE_START_'.$type_id);
	if($i == 'confirm_income')
		return constant('INVOICE_INCOME_'.$type_id);
	if($i == 'confirm_transfer')
		return constant('INVOICE_TRANSFER_'.$type_id);
	return constant('INVOICE_'.$type_id);
}//_invoice()
function _expense($type_id=false, $i='name') {// ����  ��������
	if(!defined('EXPENSE_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'expense';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT * FROM `setup_expense` ORDER BY `sort`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = array(
					'name' => $r['name'],
					'worker' => $r['show_worker']
				);
			xcache_set($key, $arr, 86400);
		}
		if(!defined('EXPENSE_LOADED')) {
			foreach($arr as $id => $r) {
				define('EXPENSE_'.$id, $r['name']);
				define('EXPENSE_WORKER_'.$id, $r['worker']);
			}
			define('EXPENSE_0', '');
			define('EXPENSE_WORKER_0', 0);
			define('EXPENSE_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'worker')
		return constant('EXPENSE_WORKER_'.$type_id);
	return constant('EXPENSE_'.$type_id);
}//_expense()
function _zamerDuration($v=false) {
	$arr = array(
		'30' => '30 ���.',
		'60' => '1 ���',
		'90' => '1 ��� 30 ���.',
		'120' => '2 ����',
		'150' => '2 ���� 30 ���.',
		'180' => '3 ����'
	);
	return $v ? $arr[$v] : $arr;
}//_zamerDuration()
function _zayavExpense($type_id=false, $i='name') {//��������� �������� ������
	if(!defined('ZAYAV_EXPENSE_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'zayav_expense';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT * FROM `setup_zayavexpense` ORDER BY `sort`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = array(
					'name' => $r['name'],
					'txt' => $r['show_txt'],
					'worker' => $r['show_worker']
				);
			xcache_set($key, $arr, 86400);
		}
		if(!defined('ZAYAV_EXPENSE_LOADED')) {
			foreach($arr as $id => $r) {
				define('ZAYAV_EXPENSE_'.$id, $r['name']);
				define('ZAYAV_EXPENSE_TXT_'.$id, $r['txt']);
				define('ZAYAV_EXPENSE_WORKER_'.$id, $r['worker']);
			}
			define('ZAYAV_EXPENSE_0', '');
			define('ZAYAV_EXPENSE_TXT_0', '');
			define('ZAYAV_EXPENSE_WORKER_0', 0);
			define('ZAYAV_EXPENSE_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'txt')
		return constant('ZAYAV_EXPENSE_TXT_'.$type_id);
	if($i == 'worker')
		return constant('ZAYAV_EXPENSE_WORKER_'.$type_id);
	return constant('ZAYAV_EXPENSE_'.$type_id);
}//_zayavExpense()

function _mainLinks() {
	global $html;

	_remindActiveSet();

	if($count = query_value("SELECT COUNT(`id`) FROM `invoice_transfer` WHERE !`deleted` AND `confirm`=1"))
		define('TRANSFER_CONFIRM', $count);
	else
		define('TRANSFER_CONFIRM', 0);

	$links = array(
		array(
			'name' => '�������',
			'page' => 'client',
			'show' => 1
		),
		array(
			'name' => '������',
			'page' => 'zayav',
			'show' => 1
		),
		array(
			'name' => '�����������'.REMIND_ACTIVE,
			'page' => 'remind',
			'show' => 1
		),
		array(
			'name' => '������'.(TRANSFER_CONFIRM ? ' (<b>'.TRANSFER_CONFIRM.'</b>)' : ''),
			'page' => 'report',
			'show' => 1
		),
		array(
			'name' => '���������',
			'page' => 'setup',
			'show' => 1
		)
	);

	$send =
		(VIEWER_ADMIN ? '<a href="//vk.com/app4659909" target="_blank" id="go-louvers">&raquo; ������</a>' : '').
		'<div id="mainLinks">';
	foreach($links as $l)
		if($l['show'])
			$send .= '<a href="'.URL.'&p='.$l['page'].'"'.($l['page'] == $_GET['p'] ? ' class="sel"' : '').'>'.$l['name'].'</a>';
	$send .= pageHelpIcon().'</div>';

	$html .= $send;
}//_mainLinks()

function _setupRules($rls, $admin=0) {
	$rules = array(
		'RULES_NOSALARY' => array(	// �� ���������� � ����������� �/�
			'def' => 0
		),
		'RULES_ZPZAYAVAUTO' => array(	// ��������� ����� �� ������ ��� ���������� �����
			'def' => 0
		),
		'RULES_APPENTER' => array(	// ��������� ���� � ����������
			'def' => 0,
			'admin' => 1,
			'childs' => array(
				'RULES_WORKER' => array(	// ����������
					'def' => 0,
					'admin' => 1
				),
				'RULES_RULES' => array(	    // ��������� ���� �����������
					'def' => 0,
					'admin' => 1
				),
				'RULES_REKVISIT' => array(	// ��������� �����������
					'def' => 0,
					'admin' => 1
				),
				'RULES_PRODUCT' => array(	// ���� �������
					'def' => 0,
					'admin' => 1
				),
				'RULES_INVOICE' => array(	// �����
					'def' => 0,
					'admin' => 1
				),
				'RULES_ZAYAVRASHOD' => array(// ������� �� ������
					'def' => 0,
					'admin' => 1
				),
				'RULES_HISTORYSHOW' => array(// ����� ������� ��������
					'def' => 0,
					'admin' => 1
				),
				'RULES_MONEY' => array(	    // ����� ������ �������: ������ ����, ��� �������
					'def' => 0,
					'admin' => 1
				)
			)
		)
	);
	$ass = array();
	foreach($rules as $i => $r) {
		$ass[$i] = $admin && isset($r['admin']) ? $r['admin'] : (isset($rls[$i]) ? $rls[$i] : $r['def']);
		//$parent = $ass[$i];
		if(isset($r['childs']))
			foreach($r['childs'] as $ci => $cr)
				$ass[$ci] = $admin && isset($cr['admin']) ? $cr['admin'] : (isset($rls[$ci]) ? $rls[$ci] : $cr['def']);
	}
	return $ass;
}//_setupRules()
function _viewerRules($viewer_id=VIEWER_ID, $rule='') {
	$key = CACHE_PREFIX.'viewer_rules_'.$viewer_id;
	$wr = xcache_get($key);
	if(empty($wr)) {
		$rules = query_ass("SELECT `key`,`value` FROM `vk_user_rules` WHERE `viewer_id`=".$viewer_id);
		$admin = _viewer($viewer_id, 'admin');
		$wr = _setupRules($rules, $admin);
		xcache_set($key, $wr, 86400);
	}
	return $rule ? $wr[$rule] : $wr;
}//_viewerRules()
function _norules($txt=false) {
	return '<div class="norules">'.($txt ? '<b>'.$txt.'</b>: �' : '�').'����������� ����.</div>';
}//_norules()

function numberToWord($num, $firstSymbolUp=false) {
	$num = intval($num);
	$one = array(
		0 => '����',
		1 => '����',
		2 => '���',
		3 => '���',
		4 => '������',
		5 => '����',
		6 => '�����',
		7 => '����',
		8 => '������',
		9 => '������',
		10 => '��c���',
		11 => '�����������',
		12 => '����������',
		13 => '����������',
		14 => '������������',
		15 => '����������',
		16 => '�����������',
		17 => '����������',
		18 => '������������',
		19 => '������������'
	);
	$ten = array(
		2 => '��������',
		3 => '��������',
		4 => '�����',
		5 => '���������',
		6 => '����������',
		7 => '���������',
		8 => '�����������',
		9 => '���������'
	);
	$hundred = array(
		1 => '���',
		2 => '������',
		3 => '������',
		4 => '���������',
		5 => '�������',
		6 => '��������',
		7 => '�������',
		8 => '���������',
		9 => '���������'
	);

	if($num < 20)
		return $one[$num];

	$word = '';
	if($num % 100 > 0)
		if($num % 100 < 20)
			$word = $one[$num % 100];
		else
			$word = $ten[floor($num / 10) % 10].($num % 10 > 0 ? ' '.$one[$num % 10] : '');

	if($num % 1000 >= 100)
		$word = $hundred[floor($num / 100) % 10].' '.$word;

	if($num >= 1000) {
		$t = floor($num / 1000) % 1000;
		$word = ' �����'._end($t, '�', '�', '').' '.$word;
		if($t % 100 > 2 && $t % 100 < 20)
			$word = $one[$t % 100].$word;
		else {
			if($t % 10 == 1)
				$word = '����'.$word;
			elseif($t % 10 == 2)
				$word = '���'.$word;
			elseif($t % 10 != 0)
				$word = $one[$t % 10].' '.$word;
			if($t % 100 >= 20)
				$word = $ten[floor($t / 10) % 10].' '.$word;
		}
		if($t >= 100)
			$word = $hundred[floor($t / 100) % 10].' '.$word;
	}
	if($firstSymbolUp)
		$word[0] = strtoupper($word[0]);
	return $word;
}//numberToWord()

function viewerAdded($viewer_id) {//����� ����������, ������� ������ ������ � ������ ����
	return '��'.(_viewer($viewer_id, 'sex') == 1 ? '����' : '��').' '._viewer($viewer_id, 'name');
}

function pin_enter() {
	return
	'<div id="pin-enter">'.
		'���: '.
		'<input type="password" id="pin" maxlength="10"> '.
		'<div class="vkButton"><button>Ok</button></div>'.
		'<div class="red">&nbsp;</div>'.
	'</div>';
}//pin_enter()

// ---===! client !===--- ������ ��������

function _clientLink($arr, $fio=0, $tel=0) {//���������� ����� � ������ ������� � ������ ��� ������� �� id
	$clientArr = array(is_array($arr) ? 0 : $arr);
	$ass = array();
	if(is_array($arr)) {
		foreach($arr as $r)
			if(!empty($r['client_id'])) {
				$clientArr[$r['client_id']] = $r['client_id'];
				$ass[$r['client_id']][] = $r['id'];
			}
		unset($clientArr[0]);
	}
	if(!empty($clientArr)) {
		$sql = "SELECT *
		        FROM `client`
				WHERE `id` IN (".implode(',', $clientArr).")";
		$q = query($sql);
		if(!is_array($arr)) {
			if($r = mysql_fetch_assoc($q))
				return $fio ? $r['fio'] : '<a href="'.URL.'&p=client&d=info&id='.$r['id'].'" class="'.($r['deleted'] ? ' deleted' : '').($tel && $r['telefon'] ? _tooltip($r['telefon'], -2, 'l') : '">').$r['fio'].'</a>';
			return '';
		}
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id) {
				$arr[$id]['client_link'] = '<a href="'.URL.'&p=client&d=info&id='.$r['id'].'" class="'.($r['deleted'] ? ' deleted' : '').($tel && $r['telefon'] ? _tooltip($r['telefon'], -2, 'l') : '">').$r['fio'].'</a>';
				$arr[$id]['client_fio'] = $r['fio'];
				$arr[$id]['client_tel'] = $r['telefon'];
			}
	}
	return $arr;
}//_clientLink()
function _clientValues($arr, $fio=0, $tel=0) {//���������� ����� � ������ ������� � ������ ��� ������� �� id
	$clientArr = array(is_array($arr) ? 0 : $arr);
	$ass = array();
	if(is_array($arr)) {
		foreach($arr as $r)
			if(!empty($r['client_id'])) {
				$clientArr[$r['client_id']] = $r['client_id'];
				$ass[$r['client_id']][] = $r['id'];
			}
		unset($clientArr[0]);
	}
	if(!empty($clientArr)) {
		$sql = "SELECT *
		        FROM `client`
				WHERE `id` IN (".implode(',', $clientArr).")";
		$q = query($sql);
		if(!is_array($arr)) {
			if($r = mysql_fetch_assoc($q))
				return $fio ? $r['fio'] : '<a href="'.URL.'&p=client&d=info&id='.$r['id'].'" class="'.($r['deleted'] ? ' deleted' : '').($tel && $r['telefon'] ? _tooltip($r['telefon'], -2, 'l') : '">').$r['fio'].'</a>';
			return '';
		}
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id) {
				$arr[$id]['client_link'] = '<a href="'.URL.'&p=client&d=info&id='.$r['id'].'" class="'.($r['deleted'] ? ' deleted' : '').($tel && $r['telefon'] ? _tooltip($r['telefon'], -2, 'l') : '">').$r['fio'].'</a>';
				$arr[$id]['client_fio'] = $r['fio'];
				$arr[$id]['client_tel'] = $r['telefon'];
				$arr[$id]['client_telefon'] = $r['telefon'];
			}
	}
	return $arr;
}//_clientValues()
function clientBalansUpdate($client_id) {//���������� ������� �������
	$prihod = query_value("SELECT SUM(`sum`) FROM `money` WHERE !`deleted` AND `client_id`=".$client_id);
	$acc = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE !`deleted` AND `client_id`=".$client_id);
	$balans = $prihod - $acc;
	query("UPDATE `client` SET `balans`=".$balans." WHERE `id`=".$client_id);
	return $balans;
}//clientBalansUpdate()

function clientFilter($v) {
	$default = array(
		'page' => 1,
		'find' => '',
		'dolg' => 0,
		'worker' => 0,
		'note' => 0,
		'zayav_cat' => 0,
		'product_id' => 0
	);
	$filter = array(
		'page' => _num(@$v['page']) ? $v['page'] : 1,
		'find' => trim(@$v['find']),
		'dolg' => _isbool(@$v['dolg']),
		'worker' => _isbool(@$v['worker']),
		'note' => _isbool(@$v['note']),
		'zayav_cat' => _num(@$v['zayav_cat']),
		'product_id' => _num(@$v['product_id']),
		'clear' => ''
	);
	foreach($default as $k => $r)
		if($r != $filter[$k]) {
			$filter['clear'] = '<a id="filter_clear">�������� ������</a>';
			break;
		}
	return $filter;
}//clientFilter()
function client_data($filter=array()) {
	$filter = clientFilter($filter);
	$cond = "!`deleted`";
	$reg = '';
	$regEngRus = '';
	if($filter['find']) {
		$engRus = _engRusChar($filter['find']);
		$cond .= " AND (`fio` LIKE '%".$filter['find']."%'
					 OR `telefon` LIKE '%".$filter['find']."%'
					 OR `adres` LIKE '%".$filter['find']."%'
					 ".($engRus ?
					   "OR `fio` LIKE '%".$engRus."%'
						OR `telefon` LIKE '%".$engRus."%'
						OR `adres` LIKE '%".$engRus."%'"
				: '')."
					 )";
		$reg = '/('.$filter['find'].')/i';
		if($engRus)
			$regEngRus = '/('.$engRus.')/i';
	} else {
		$cids = array();
		if($filter['dolg'])
			$cond .= " AND `balans`<0";
		if($filter['product_id']) {
			$sql = "SELECT `z`.`client_id`
			        FROM `zayav` AS `z`,
			             `zayav_product` AS `p`
			        WHERE !`z`.`deleted`
			          AND `z`.`id`=`p`.`zayav_id`
			          AND `p`.`product_id`=".$filter['product_id'];
			foreach(explode(',', query_ids($sql)) as $id)
				$cids[$id] = $id;
		}
		if($filter['zayav_cat']) {
			$cnd = '';
			switch($filter['zayav_cat']) {
				case 1: $cnd = "AND !`dogovor_require` AND `zakaz_status`"; break;
				case 2: $cnd = "AND !`dogovor_require` AND (`zamer_status`=1 OR `zamer_status`=3)"; break;
				case 3: $cnd = "AND !`dogovor_id` AND `dogovor_require`"; break;
				case 4: $cnd = "AND !`dogovor_require` AND `set_status`";
			}
			if($cnd) {
				$sql = "SELECT DISTINCT `client_id` FROM `zayav` WHERE !`deleted` ".$cnd.(!empty($cids) ? " AND `client_id` IN (".implode(',', $cids).")" : '');
				$cids = array();
				foreach(explode(',', query_ids($sql)) as $id)
					$cids[$id] = $id;
			}
		}
		if($filter['worker'])
			$cond .= " AND `worker_id`";
		if($filter['note']) {
			$sql = "SELECT DISTINCT `table_id`
					FROM `vk_comment`
					WHERE `status`
					  AND `table_name`='client'".
					 (!empty($cids) ? " AND `table_id` IN (".implode(',', $cids).")" : '');
			$cids = array();
			foreach(explode(',', query_ids($sql)) as $id)
				$cids[$id] = $id;
		}
		if(!empty($cids))
			$cond .= " AND `id` IN (".implode(',', $cids).")";
	}

	$all = query_value("SELECT COUNT(`id`) AS `all` FROM `client` WHERE ".$cond." LIMIT 1");
	if(!$all)
		return array(
			'all' => 0,
			'result' => '�������� �� �������.'.$filter['clear'],
			'spisok' => '<div class="_empty">�������� �� �������.</div>',
			'filter' => $filter
		);

	$page = $filter['page'];
	$limit = 20;
	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT *
			FROM `client`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$spisok[$r['id']] = $r;
		unset($spisok[$r['id']]['adres']);
		if(!empty($filter['find'])) {
			if(preg_match($reg, $r['fio']))
				$spisok[$r['id']]['fio'] = preg_replace($reg, '<em>\\1</em>', $r['fio'], 1);
			if(preg_match($reg, $r['telefon']))
				$spisok[$r['id']]['telefon'] = preg_replace($reg, '<em>\\1</em>', $r['telefon'], 1);
			if(preg_match($reg, $r['adres']))
				$spisok[$r['id']]['adres'] = preg_replace($reg, '<em>\\1</em>', $r['adres'], 1);
			if($regEngRus && preg_match($regEngRus, $r['fio']))
				$spisok[$r['id']]['fio'] = preg_replace($regEngRus, '<em>\\1</em>', $r['fio'], 1);
			if($regEngRus && preg_match($regEngRus, $r['telefon']))
				$spisok[$r['id']]['telefon'] = preg_replace($regEngRus, '<em>\\1</em>', $r['telefon'], 1);
			if($regEngRus && preg_match($regEngRus, $r['adres']))
				$spisok[$r['id']]['adres'] = preg_replace($regEngRus, '<em>\\1</em>', $r['adres'], 1);
		}
	}

	$sql = "SELECT
				`client_id` AS `id`,
				COUNT(`id`) AS `count`
			FROM `zayav`
			WHERE !`deleted`
			  AND `client_id` IN (".implode(',', array_keys($spisok)).")
			GROUP BY `client_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']]['zayav_count'] = $r['count'];

	$sql = "SELECT
				`table_id` AS `id`
			FROM `vk_comment`
			WHERE `status`=1
			  AND `table_name`='client'
			  AND `table_id` IN (".implode(',', array_keys($spisok)).")
			GROUP BY `table_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']]['comm'] = 1;

	$dolg = $filter['dolg'] ? abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE !`deleted` AND `balans`<0 LIMIT 1")) : 0;
	$send = array(
		'all' => $all,
		'spisok' => '',
		'result' => '������'._end($all, ' ', '� ').$all.' ������'._end($all, '', '�', '��').
					(empty($filter['find']) && $dolg ? '<span class="dolg_sum">(����� ����� ����� = <b>'._sumSpace($dolg).'</b> ���.)</span>' : '').
					$filter['clear'],
		'filter' => $filter
	);
	foreach($spisok as $r)
		$send['spisok'] .= '<div class="unit'.(isset($r['comm']) ? ' i' : '').'">'.
			($r['balans'] != 0 ? '<div class="balans">������: <b style=color:#'.($r['balans'] < 0 ? 'A00' : '090').'>'.round($r['balans'], 2).'</b></div>' : '').
			'<table>'.
				'<tr><td class="label">���:<td><a href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>'.
				($r['telefon'] ? '<tr><td class="label">�������:<td>'.$r['telefon'] : '').
				(isset($r['adres']) ? '<tr><td class="label">�����:<td>'.$r['adres'] : '').
				(isset($r['zayav_count']) ? '<tr><td class="label">������:<td>'.$r['zayav_count'] : '').
			'</table>'.
		'</div>';
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .= '<div class="_next" val="'.($page + 1).'"><span>�������� ��� '.$c.' ������'._end($c, '�', '�', '��').'</span></div>';
	}
	return $send;
}//client_data()
function client_list($v) {
	$data = client_data($v);
	$v = $data['filter'];
	return
	'<div id="client">'.
		'<div id="find"></div>'.
		'<div class="result">'.$data['result'].'</div>'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate"><a>����� ������</a></div>'.
					'<div class="filter'.(empty($v['find']) ? '' : ' dn').'">'.
						_check('dolg', '��������', $v['dolg']).
						_check('worker', '���������', $v['worker']).
						_check('note', '���� �������', $v['note']).
						'<div class="findHead">��������� ������</div>'.
						'<input type="hidden" id="zayav_cat" value="'.$v['zayav_cat'].'" />'.
						'<div class="findHead">������������ �������</div>'.
						'<input type="hidden" id="product_id" value="'.$v['product_id'].'" />'.
					'</div>'.
		'</table>'.
	'</div>'.
	'<script type="text/javascript">'.
		'var C={'.
			'find:"'.$v['find'].'"'.
		'};'.
	'</script>';
}//client_list()

function clientInfoGet($client) {
	return
		($client['deleted'] ? '<div class="_info">������ �����</div>' : '').
		'<div class="fio">'.$client['fio'].'</div>'.
		'<table class="cinf">'.
			'<tr><td class="label">�������:<td>'.$client['telefon'].
			'<tr><td class="label">�����:  <td>'.$client['adres'].
			'<tr><td class="label">������: <td><b style=color:#'.($client['balans'] < 0 ? 'A00' : '090').'>'.round($client['balans'], 2).'</b>'.
		($client['worker_id'] ?
			'<tr><td class="label">���������:'.
				'<td><a href="'.URL.'&p=report&d=salary&id='.$client['worker_id'].'" class="'._tooltip('�/� ����������', -30).
						_viewer($client['worker_id'], 'name').
					'</a>'
		: '').
		'</table>'.
	($client['pasp_seria'] || $client['pasp_nomer'] || $client['pasp_adres'] || $client['pasp_ovd'] || $client['pasp_data'] ?
		'<div class="pasp-head">���������� ������:</div>'.
		'<table class="pasp">'.
			'<tr><td class="label">����� � �����:<td>'.$client['pasp_seria'].' '.$client['pasp_nomer'].
			'<tr><td class="label">��������:<td>'.$client['pasp_adres'].
			'<tr><td class="label">�����:<td>'.$client['pasp_ovd'].', '.$client['pasp_data'].
		'</table>' : '').
		'<div class="dtime_add">������� ��'.(_viewer($client['viewer_id_add'], 'sex') == 1 ? '����' : '��').' '
			._viewer($client['viewer_id_add'], 'name').' '.
			FullData($client['dtime_add'], 1).
		'</div>';

}
function client_info($client_id) {
	$sql = "SELECT * FROM `client` WHERE `id`=".$client_id;
	if(!$client = mysql_fetch_assoc(query($sql)))
		return _noauth('������� �� ����������');

	if(!VIEWER_ADMIN && $client['deleted'])
		return _noauth('������ �����');

	$commCount = query_value("SELECT COUNT(`id`)
							  FROM `vk_comment`
							  WHERE `status`=1
								AND `parent_id`=0
								AND `table_name`='client'
								AND `table_id`=".$client_id);

	$money = income_spisok(array('client_id'=>$client_id,'limit'=>15));

	$remind = _remind_spisok(array('client_id'=>$client_id));

	$history = RULES_HISTORYSHOW ? history(array('client_id'=>$client_id,'limit'=>15)) : '';

	$sql = "SELECT * FROM `zayav` WHERE ".(VIEWER_ADMIN ? '' : '!`deleted` AND ')." `client_id`=".$client_id;
	$q = query($sql);
	$zayav = array();
	while($r = mysql_fetch_assoc($q))
		$zayav[$r['id']] = $r;

	$zayavCount = count($zayav);
	$zayavSpisok = '';
	$zopl = array();
	if($zayavCount) {
		$zayav = _dogNomer($zayav);
		$zayav = zayav_product_array($zayav);
		foreach($zayav as $r) {
			$r['no_client'] = 1;
			$zayavSpisok .= _zayavCategory($r, 'unit');
			$zopl[$r['id']] = array(
				'title' => _zayavCategory($r, 'head'),
				'content' => _zayavCategory($r, 'head').($r['dogovor_id'] ? ' <span>������� '.$r['dogovor_nomer'].'</span>' : '')
			);
		}
	}

	$workers = query_selJson("
		SELECT
			`viewer_id`,
			CONCAT(`first_name`,' ',`last_name`)
        FROM `vk_user`
        WHERE `viewer_id`!=982006 AND `worker`");
	return
	'<script type="text/javascript">'.
		'var CLIENT={'.
			'id:'.$client_id.','.
			'fio:"'.$client['fio'].'",'.
			'telefon:"'.$client['telefon'].'",'.
			'adres:"'.$client['adres'].'",'.
			'worker_id:'.$client['worker_id'].','.
			'workers:'.$workers.','.
			'pasp_seria:"'.$client['pasp_seria'].'",'.
			'pasp_nomer:"'.$client['pasp_nomer'].'",'.
			'pasp_adres:"'.$client['pasp_adres'].'",'.
			'pasp_ovd:"'.$client['pasp_ovd'].'",'.
			'pasp_data:"'.$client['pasp_data'].'"'.
		'},'.
		'OPL={'.
			'from:"client",'.
			'client_id:'.$client_id.','.
			'client_fio:"'.$client['fio'].'",'.
			'zayav_spisok:'._selJson($zopl).
		'};'.
	'</script>'.
	'<div id="clientInfo">'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.clientInfoGet($client).
				'<td class="right">'.
					'<div class="rightLink">'.
						'<a class="sel">����������</a>'.
 (!$client['deleted'] ? '<a class="cedit">�������������</a>'.
						'<a class="zayav_add"><b>����� ������</b></a>'.
						'<a class="income-add">������ �����</a>'.
						'<a class="_remind-add">������ �����������</a>'.
						'<a class="cdel">������� �������</a>'
 : '').
					'</div>'.
		'</table>'.

		'<div id="dopLinks">'.
			'<a class="link sel" val="zayav">������'.($zayavCount ? ' <b class="count">'.$zayavCount.'</b>' : '').'</a>'.
			'<a class="link" val="money">�������'.($money['all'] ? ' <b class="count">'.$money['all'].'</b>' : '').'</a>'.
			'<a class="link" val="remind">�����������'.($remind['all'] ? ' <b class="count">'.$remind['all'].'</b>' : '').'</a>'.
			'<a class="link" val="comm">�������'.($commCount ? ' <b class="count">'.$commCount.'</b>' : '').'</a>'.
			(RULES_HISTORYSHOW ? '<a class="link" val="hist">�������'.($history['all'] ? ' <b class="count">'.$history['all'].'</b>' : '').'</a>' : '').
		'</div>'.

		'<table class="tabLR">'.
			'<tr><td class="left">'.
					'<div id="zayav_spisok">'.($zayavSpisok ? $zayavSpisok : '<div class="_empty">������ ���</div>').'</div>'.
					'<div id="income_spisok">'.$money['spisok'].'</div>'.
					'<div id="remind-spisok">'.$remind['spisok'].'</div>'.
					'<div id="comments">'._vkComment('client', $client_id).'</div>'.
					(RULES_HISTORYSHOW ? '<div id="histories">'.$history['spisok'].'</div>' : '').
				'<td class="right">'.
					'<div id="zayav_filter">'.
						//'<div id="zayav_result">'.zayav_count($zayavData['all'], 0).'</div>'.
						//'<div class="findHead">������ ������</div>'.
						//_rightLink('status', _zayavStatusName()).
					'</div>'.
		'</table>'.
	'</div>';
}//client_info()



// ---===! zayav !===--- ������ ������

function _statusColor($id) {
	$arr = array(
		'0' => 'ffffff',
		'1' => 'E8E8FF',
		'2' => 'CCFFCC',
		'3' => 'FFDDDD'
	);
	return $arr[$id];
}//_statusColor()
function _zamerDataTest($dtime, $duration, $zayav_id=0) {//��������, ����� ���� ������ �� ���������� ������ ����
	$sql = "SELECT COUNT(`id`)
		        FROM `zayav`
		        WHERE `deleted`=0
		          AND `zamer_status`=1
  ".($zayav_id ? "AND `id`!=".$zayav_id : '')."
		          AND '".$dtime."'>=`zamer_dtime`
				  AND '".$dtime."'<DATE_ADD(zamer_dtime, INTERVAL `zamer_duration` MINUTE)
				   OR `deleted`=0
		          AND `zamer_status`=1
  ".($zayav_id ? "AND `id`!=".$zayav_id : '')."
			      AND DATE_ADD('".$dtime."', INTERVAL ".$duration." MINUTE)>`zamer_dtime`
				  AND DATE_ADD('".$dtime."', INTERVAL ".$duration." MINUTE)<=DATE_ADD(zamer_dtime, INTERVAL `zamer_duration` MINUTE)";
	return query_value($sql);
}//_zamerDataTest()
function _zayavLink($arr) {
	$ids = array(); // �������� ������
	$arrIds = array();
	foreach($arr as $key => $r)
		if($r['zayav_id']) {
			$ids[$r['zayav_id']] = 1;
			$arrIds[$r['zayav_id']][] = $key;
		}
	if(empty($ids))
		return $arr;
	$sql = "SELECT * FROM `zayav` WHERE `id` IN (".implode(',', array_keys($ids)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($arrIds[$r['id']] as $key) {
			$head = _zayavCategory($r, 'head');
			$arr[$key]['zayav_link'] = '<a'.($r['deleted'] ? ' class="deleted" title="������ �������"' : '').' href="'.URL.'&p=zayav&d=info&id='.$r['id'].'">'.$head.'</a>';
			$arr[$key]['zayav_head'] = $head;
			$arr[$key]['zayav_vg'] = _zayavCategory($r, 'vg');;
			$arr[$key]['zayav_add'] = $r['dtime_add'];
			$arr[$key]['zayav_status_day'] = $r['status_day'];
			$arr[$key]['zayav_status_color'] = _zayavCategory($r, 'status_color');
			$dolg = $r['accrual_sum'] - $r['oplata_sum'];
			$arr[$key]['zayav_dolg'] = $dolg > 0 ? $dolg : 0;
		}
	return $arr;
}//_zayavLink()
function _zayavValues($arr) {
	$ids = array(); // �������� ������
	$arrIds = array();
	foreach($arr as $key => $r)
		if($r['zayav_id']) {
			$ids[$r['zayav_id']] = 1;
			$arrIds[$r['zayav_id']][] = $key;
		}
	if(empty($ids))
		return $arr;
	$sql = "SELECT * FROM `zayav` WHERE `id` IN (".implode(',', array_keys($ids)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($arrIds[$r['id']] as $key) {
			$head = _zayavCategory($r, 'head');
			$arr[$key]['zayav_link'] = '<a'.($r['deleted'] ? ' class="deleted" title="������ �������"' : '').' href="'.URL.'&p=zayav&d=info&id='.$r['id'].'">'.$head.'</a>';
			$arr[$key]['zayav_head'] = $head;
			$arr[$key]['zayav_vg'] = _zayavCategory($r, 'vg');;
			$arr[$key]['zayav_add'] = $r['dtime_add'];
			$arr[$key]['zayav_status_day'] = $r['status_day'];
			$arr[$key]['zayav_status_color'] = _zayavCategory($r, 'status_color');
			$dolg = $r['accrual_sum'] - $r['oplata_sum'];
			$arr[$key]['zayav_dolg'] = $dolg > 0 ? $dolg : 0;
		}
	return $arr;
}//_zayavValues()

function _zayavStatus($id=false) {
	$arr = array(
		'0' => array(
			'name' => '����� ������',
			'color' => 'ffffff'
		),
		'1' => array(
			'name' => '������� ����������',
			'color' => 'E8E8FF'
		),
		'2' => array(
			'name' => '���������',
			'color' => 'CCFFCC'
		),
		'3' => array(
			'name' => '��������',
			'color' => 'FFDDDD'
		)
	);
	return $id ? $arr[$id] : $arr;
}//_zayavStatus()
function _zayavStatusName($id=false) {
	$status = _zayavStatus();
	if($id)
		return $status[$id]['name'];
	$send = array();
	foreach($status as $id => $r)
		$send[$id] = $r['name'];
	return $send;
}//_zayavStatusName()
function _zayavStatusColor($id=false) {
	$status = _zayavStatus();
	if($id)
		return $status[$id]['color'];
	$send = array();
	foreach($status as $id => $r)
		$send[$id] = $r['color'];
	return $send;
}//_zayavStatusColor()
function _zayavCategory($z, $i='type') {// ����������� ��������� ������
	$dop = $z['nomer_vg'] ? ' ��'.$z['nomer_vg'] :
		  ($z['nomer_g'] ? ' �'.$z['nomer_g'] :
		  ($z['nomer_d'] ? ' �'.$z['nomer_d'] :
		  ($z['nomer_t'] ? ' T'.$z['nomer_t'] : ' #'.$z['id'])));
	if(!$z['dogovor_id'] && $z['dogovor_require'])
		$send = array(
			'type' => 'dog',
			'head' => '������� �� �������� <span class="zayav-dog">('.($z['set_status'] ? '���������' : ($z['zakaz_status'] ? '�����' : '�����')).$dop.')</span>',
			'status_id' => $z['zamer_status'],
			'status_name' => $z['zamer_status'] ? _zamerStatus($z['zamer_status']) : '',
			'status_color' => 'fff'
		);
	elseif($z['zakaz_status'])
		$send = array(
			'type' => 'zakaz',
			'head' => '�����'.$dop,
			'status_id' => $z['zakaz_status'],
			'status_name' => _zakazStatus($z['zakaz_status']),
			'status_color' => _statusColor($z['zakaz_status'])
		);
	elseif($z['zamer_status'] == 1 || $z['zamer_status'] == 3)
		$send = array(
			'type' => 'zamer',
			'head' => '�����'.$dop,
			'status_id' => $z['zamer_status'],
			'status_name' => _zamerStatus($z['zamer_status']),
			'status_color' => 'fff'
		);
	elseif($z['set_status'])
		$send = array(
			'type' => 'set',
			'head' => '���������'.$dop,
			'status_id' => $z['set_status'],
			'status_name' => _setStatus($z['set_status']),
			'status_color' => _statusColor($z['set_status'])
		);
	if($i == 'unit') {
		$diff = $z['accrual_sum'] - $z['oplata_sum'];
		return
			'<div class="zayav_unit"'.($send['type'] != 'dog' ? ' style="background-color:#'._statusColor($send['status_id']) : '').'" val="'.$z['id'].'">'.
				($z['deleted'] ? '<div class="zdel">������ �������</div>' : '').
				'<div class="dtime">'.
					'#'.(isset($z['find_id']) ? $z['find_id'] : $z['id']).'<br />'.
					FullData($z['dtime_add'], 1).
					(($send['type'] == 'zakaz' || $send['type'] == 'set') && ($z['accrual_sum'] || $z['oplata_sum']) ?
						'<div class="balans'.($z['accrual_sum'] != $z['oplata_sum'] ? ' diff' : '').'">'.
							'<span class="acc'._tooltip('���������', -39).$z['accrual_sum'].'</span>/'.
							'<span class="opl'._tooltip($diff ? '��������� '.$diff.' ���.' : '��������', -17, 'l').$z['oplata_sum'].'</span>'.
						'</div>'
					: '').
					'</div>'.
				'<a class="name">'.$send['head'].($z['dogovor_id'] ? ' <span class="zayav-dog">(������� '.$z['dogovor_nomer'].')</span>' : '').'</a>'.
				'<table class="ztab">'.
					(empty($z['no_client']) ?
						'<tr><td class="label">������:<td>'.$z['client_link'].
						(isset($z['client_tel']) ? '<tr><td class="label">�������:<td>'.$z['client_tel'] : '')
					: '').
					($z['adres'] ? '<tr><td class="label top">�����:<td>'.$z['adres'] : '').
					'<tr><td class="label top">�������:<td>'.(isset($z['product']) ? zayav_product_spisok($z['product']) : '').$z['zakaz_txt'].
				'</table>'.
			'</div>';
	}
	$send['vg'] = $dop;
	return $send[$i];
}//_zayavCategory()
function _zayavBalansUpdate($zayav_id) {//���������� ����������, ����� ��������, ������ ������
	if(!$zayav_id)
		return 0;
	$accrual_sum = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `accrual` WHERE !`deleted` AND `zayav_id`=".$zayav_id);
	$oplata_sum = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `money` WHERE !`deleted` AND `zayav_id`=".$zayav_id);
	$expense_sum = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `zayav_expense` WHERE `zayav_id`=".$zayav_id);
	$sql = "UPDATE `zayav`
			SET `accrual_sum`=".$accrual_sum.",
				`oplata_sum`=".$oplata_sum.",
				`expense_sum`=".$expense_sum.",
				`net_profit`=".($accrual_sum - $expense_sum)."
			WHERE `id`=".$zayav_id;
	query($sql);
	return array(
		'acc' => round($accrual_sum, 2),
		'opl' => round($oplata_sum, 2),
		'dolg' => round($oplata_sum - $accrual_sum)
	);
}//_zayavBalansUpdate()

function zayav_product_test($product) {// �������� ������������ ������ ������� ��� �������� � ����
	if(empty($product))
		return false;
	$send = array();
	$ex = explode(',', $product);
	foreach($ex as $r) {
		$ids = explode(':', $r);
		foreach($ids as $id)
			if(!preg_match(REGEXP_NUMERIC, $id))
				return false;
		if($ids[0] == 0 || $ids[2] == 0)
			return false;
		$send[] = $ids;
	}
	return empty($send) ? false : $send;
}//zayav_product_test()
function zayav_product_array($arr) {//���������� � ��������� ������� ������ ������ product
	if(empty($arr))
		return array();
	foreach($arr as $r)
		if(!empty($r['id']))
			$arr[$r['id']]['product'] = array();
	$sql = "SELECT * FROM `zayav_product` WHERE `zayav_id` IN (".implode(',', array_keys($arr)).") ORDER BY `id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$arr[$r['zayav_id']]['product'][] = $r;
	return $arr;
}
function zayav_product_spisok($arr, $type='html') {
	if(!is_array($arr)) {
		$sql = "SELECT * FROM `zayav_product` WHERE `zayav_id`=".$arr." ORDER BY `id`";
		$q = query($sql);
		$arr = array();
		while($r = mysql_fetch_assoc($q))
			$arr[] = $r;
	}
	$send = '<table class="product">';
	$json = array();
	$array = array();
	$cash = array();
	$report = array();
	foreach($arr as $r) {
		$send .= '<tr><td>'._product($r['product_id']).
			($r['product_sub_id'] ? ' '._productSub($r['product_sub_id']) : '').':'.
			'<td>'.$r['count'].' ��.';
		$json[] = '['.$r['product_id'].','.$r['product_sub_id'].','.$r['count'].']';
		$array[] = array($r['product_id'], $r['product_sub_id'], $r['count']);
		$cash[] = _product($r['product_id']).($r['product_sub_id'] ? ' '._productSub($r['product_sub_id']) : '');
		$report[] = _product($r['product_id']).($r['product_sub_id'] ? ' '._productSub($r['product_sub_id']) : '').': '.$r['count'].' ��.';
	}
	$send .= '</table>';
	switch($type) {
		default:
		case 'html': return $send;
		case 'json': return implode(',', $json);
		case 'array': return $array;
		case 'cash': return implode('<br />', $cash);
		case 'report': return implode("\n", $report);
	}
}//zayav_product_spisok()

function zayav_expense_test($v) {// �������� ������������ ������ �������� ������ ��� �������� � ����
	if(empty($v))
		return array();
	$send = array();
	$ex = explode(',', $v);
	foreach($ex as $r) {
		$ids = explode(':', $r);
		if(!preg_match(REGEXP_NUMERIC, $ids[0]) || !$ids[0])
			return false;
		if(_zayavExpense($ids[0], 'worker') && !preg_match(REGEXP_NUMERIC, $ids[1]))
			return false;
		if(!_cena($ids[2]))
			return false;
		if(!preg_match(REGEXP_NUMERIC, $ids[3]))
			return false;
		if(_zayavExpense($ids[0], 'txt'))
			$ids[1] = _txt($ids[1]);
		if(!_zayavExpense($ids[0], 'txt') && !_zayavExpense($ids[0], 'worker'))
			$ids[1] = '';
		$send[] = $ids;
	}
	return $send;
}//zayav_expense_test()
function zayav_expense_equal($old, $new) {// ��������� ������� � ������ �������� �������� ������
	if(empty($old) && empty($new))
		return true;
	if(empty($old) || empty($new))
		return false;
	if(count($old) != count($new))
		return false;
	foreach($old as $k => $arr)
		foreach($arr as $i => $r)
			if($r != $new[$k][$i])
				return false;
	return true;
}//zayav_expense_equal()
function zayav_expense_spisok($zayav_id, $type='html') {//��������� ������ �������� ������
	$sql = "SELECT * FROM `zayav_expense` WHERE `zayav_id`=".$zayav_id." ORDER BY `id`";
	$q = query($sql);
	$arr = array();
	while($r = mysql_fetch_assoc($q))
		$arr[] = $r;
	$send = '<table class="zayav-rashod-spisok">';
	$json = array();
	$array = array();
	foreach($arr as $r) {
		$mon = explode('-', $r['mon']);
		$sum = round($r['sum'], 2);
		$send .= '<tr'.($r['category_id'] == 2 && !$r['acc'] ? ' class="noacc"' : '').'>'.
					'<td class="name">'._zayavExpense($r['category_id']).
					'<td>'.(_zayavExpense($r['category_id'], 'txt') ? $r['txt'] : '').
						   (_zayavExpense($r['category_id'], 'worker') && $r['worker_id'] ?
							   (!_viewerRules($r['worker_id'], 'RULES_NOSALARY') ?
									'<a class="go-report-salary" val="'.$r['worker_id'].':'.substr($r['mon'], 0, 7).':'.$r['id'].'">'.
										_viewer($r['worker_id'], 'name').
									'</a>' :
									_viewer($r['worker_id'], 'name')
							   )
						   : '').
					'<td class="sum'.($r['acc'] ? _tooltip(_monthCut($mon[1]).' '.$mon[0], -7) : '">').$sum.' �.';
		$json[] = '['.
					$r['category_id'].',"'.
					(_zayavExpense($r['category_id'], 'txt') ? $r['txt'] : '').
					(_zayavExpense($r['category_id'], 'worker') ? $r['worker_id'] : '').'",'.
					$sum.','.
					$r['salary_list_id'].
				  ']';
		$array[] = array(
			intval($r['category_id']),
			(_zayavExpense($r['category_id'], 'txt') ? $r['txt'] : '').
			(_zayavExpense($r['category_id'], 'worker') ? intval($r['worker_id']) : ''),
			$sum,
			intval($r['salary_list_id'])
		);
	}
	if(!empty($arr)) {
		$z = query_assoc("SELECT * FROM `zayav` WHERE `id`=".$zayav_id);
		$send .= '<tr><td colspan="2" class="itog">����:<td class="sum"><b>'.round($z['expense_sum'], 2).'</b> �.'.
				 '<tr><td colspan="2" class="itog">�������:<td class="sum">'.round($z['net_profit'], 2).' �.';
	}
	$send .= '</table>';
	switch($type) {
		default:
		case 'html': return $send;
		case 'json': return implode(',', $json);
		case 'array': return $array;
		case 'all': return array(
			'html' => $send,
			'json' => implode(',', $json),
			'array' => $array
		);
	}
}//zayav_expense_spisok()

function zayav() {
	if(empty($_GET['d']))
		$_GET['d'] = empty($_COOKIE['zayav_dop']) ? 'zakaz' : $_COOKIE['zayav_dop'];
	setcookie('zayav_dop', $_GET['d'] , time() + 846000, '/');
	$accrual = '';
	$account = '';
	switch($_GET['d']) {
		default:
		case 'zakaz':
			$right = '<div id="buttonCreate" class="zakaz_add"><a>����� �����</a></div>';
			$data = zayav_spisok('zakaz');
			$status = '<div class="findHead">������ ������</div>'.
					  _rightLink('status', _zayavStatusName());
		$worker = query_selJson("SELECT
						DISTINCT `ze`.`worker_id`,
						CONCAT(`u`.`first_name`,' ',`u`.`last_name`)
					FROM `zayav_expense` `ze`,
						 `vk_user` `u`
					WHERE `ze`.`worker_id`=`u`.`viewer_id`
					  AND `ze`.`category_id`=2
					  AND `ze`.`zayav_id`
					  AND `ze`.`worker_id`
					  AND !`ze`.`acc`");

		$accrual = '<div class="findHead">���������� �/�</div>'.
					_radio('zp_expense', array(
						0 => '����� ������',
						1 => '���������� �/� ���',
						2 => '��������� �� ������',
						3 => '��������� ������, �� �� ���������'
					), 0, 1).
				'<script type="text/javascript">var ZPE_WORKER='.$worker.';</script>'.
				'<input type="hidden" id="zpe_worker">';
			$account = '<div class="findHead">�������������</div>'.
						_check('account', '�� ������ ����');
			break;
		case 'zamer':
			$right = '<div id="buttonCreate" class="zamer_add"><a>����� �����</a></div>'.
					 '<a class="zamer_table">������� �������</a>';
			$data = zayav_spisok('zamer');
			$st = _zayavStatusName();
			unset($st[2]);
			$status = '<div class="findHead">������ ������</div>'.
					  _rightLink('status', $st);
			break;
		case 'dog':
			$right = '';
			$data = zayav_spisok('dog');
			$status = '';
			break;
		case 'set':
			$right = '<div id="buttonCreate" class="set_add"><a>����� ������<br />�� ���������</a></div>';
			$data = zayav_spisok('set');
			$status = '<div class="findHead">������ ������</div>'.
					  _rightLink('status', _zayavStatusName());
			$worker = query_selJson("SELECT
						DISTINCT `ze`.`worker_id`,
						CONCAT(`u`.`first_name`,' ',`u`.`last_name`)
					FROM `zayav_expense` `ze`,
						 `vk_user` `u`
					WHERE `ze`.`worker_id`=`u`.`viewer_id`
					  AND `ze`.`category_id`=2
					  AND `ze`.`zayav_id`
					  AND `ze`.`worker_id`
					  AND !`ze`.`acc`");
			$accrual = '<div class="findHead">���������� �/�</div>'.
					_radio('zp_expense', array(
						0 => '����� ������',
						1 => '���������� �/� ���',
						2 => '��������� �� ������',
						3 => '��������� ������, �� �� ���������'
					), 0, 1).
				'<script type="text/javascript">var ZPE_WORKER='.$worker.';</script>'.
				'<input type="hidden" id="zpe_worker">';
			$account = '<div class="findHead">�������������</div>'.
						_check('account', '�� ������ ����');
			break;
	}
	$result = $data['result'];
	$spisok = $data['spisok'];

	$zakazCount = query_value("SELECT COUNT(`id`) AS `all`
	                         FROM `zayav`
	                         WHERE !`deleted`
	                           AND !`dogovor_require`
	                           AND `zakaz_status`=1
							 LIMIT 1");
	$zamerCount = query_value("SELECT COUNT(`id`) AS `all`
							   FROM `zayav`
							   WHERE `deleted`=0
							     AND `dogovor_require`=0
							     AND `zamer_status`=1
							   LIMIT 1");
	$dogovorCount = query_value("SELECT COUNT(`id`) AS `all`
								 FROM `zayav`
								 WHERE `deleted`=0
								   AND `dogovor_id`=0
								   AND `dogovor_require`=1
								 LIMIT 1");
	$setCount = query_value("SELECT COUNT(`id`) AS `all`
	                         FROM `zayav`
	                         WHERE `deleted`=0
							   AND `dogovor_require`=0
	                           AND `set_status`=1
							 LIMIT 1");
	return
	'<script type="text/javascript">'.
		'var PRODUCT_IDS=['.$data['product_ids'].'];'.
	'</script>'.
	'<div id="zayav" val="'.$_GET['d'].'">'.
		'<div id="dopLinks">'.
			'<div id="find"></div>'.
			'<a class="link'.($_GET['d'] == 'zakaz' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=zakaz">������'.($zakazCount ? ' ('.$zakazCount.')' : '').'</a>'.
			'<a class="link'.($_GET['d'] == 'zamer' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=zamer">������'.($zamerCount ? ' ('.$zamerCount.')' : '').'</a>'.
			'<a class="link'.($_GET['d'] == 'dog' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=dog">��������'.($dogovorCount ? ' ('.$dogovorCount.')' : '').'</a>'.
			'<a class="link'.($_GET['d'] == 'set' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=set">���������'.($setCount ? ' ('.$setCount.')' : '').'</a>'.
		'</div>'.
		'<div class="result">'.$result.'</div>'.
		'<table class="tabLR">'.
			'<tr><td id="spisok">'.$spisok.
				'<td class="right">'.
					$right.
					'<div class="find-hide">'.
						$status.
						'<div class="findHead">�������</div>'.
						'<input type="hidden" id="product_id">'.
						$accrual.
						$account.
					'</div>'.
		'</table>'.
	'</div>';
}//zayav()
function zayavFilter($v) {
	return array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? intval($v['page']) : 1,
		'client' => !empty($v['client']) && preg_match(REGEXP_NUMERIC, $v['client']) ? intval($v['client']) : 0,
		'product' => !empty($v['product']) && preg_match(REGEXP_NUMERIC, $v['product']) ? intval($v['product']) : 0,
		'status' => !empty($v['status']) && preg_match(REGEXP_NUMERIC, $v['status']) ? intval($v['status']) : 0,
		'zpe' => _num(@$v['zpe']),
		'zpe_worker' => _num(@$v['zpe_worker']),
		'account' => _isbool(@$v['account'])
	);
}//zayavFilter()
function zayav_spisok($category, $v=array()) {
	$filter = zayavFilter($v);

	switch($category) {
		case 'zakaz':
			$cond = "!`deleted`
		         AND !`dogovor_require`
	 	         AND `zakaz_status`>0";
			if($filter['status'])
				$cond .= " AND `zakaz_status`=".$filter['status'];
			break;
		case 'zamer':
			$cond = "!`deleted`
				 AND !`dogovor_require`
				 AND (`zamer_status`=1 OR `zamer_status`=3)";
			if($filter['status'])
				$cond .= " AND `zamer_status`=".$filter['status'];
			break;
		case 'dog':
			$cond = "!`deleted`
				 AND !`dogovor_id`
				 AND `dogovor_require`";
			break;
		case 'set':
			$cond = "!`deleted`
			     AND !`dogovor_require`
	             AND `set_status`";
			if($filter['status'])
				$cond .= " AND `set_status`=".$filter['status'];
			break;
		default: return '����������� ��������� ������';
	}

	if($filter['client'])
		$cond .= " AND `client_id`=".$filter['client'];
	if($filter['product'])
		$cond .= " AND `id` IN (".query_ids("SELECT `zayav_id` FROM `zayav_product` WHERE `product_id`=".$filter['product']).")";
	switch($filter['zpe']) {
		case 1:
			$sql = "SELECT DISTINCT `zayav_id`
					FROM `zayav_expense`
					WHERE `category_id`=2
					  AND `zayav_id`";
			$cond .= " AND `id` NOT IN (".query_ids($sql).")";
			break;
		case 2:
			$sql = "SELECT DISTINCT `zayav_id`
					FROM `zayav_expense`
					WHERE `category_id`=2
					  AND `zayav_id`
					  AND !`worker_id`";
			$cond .= " AND `id` IN (".query_ids($sql).")";
			break;
		case 3:
			$sql = "SELECT DISTINCT `zayav_id`
					FROM `zayav_expense`
					WHERE `category_id`=2
					  AND `zayav_id`
					  AND `worker_id`".($filter['zpe_worker'] ? '='.$filter['zpe_worker'] : '')."
					  AND !`acc`";
			$cond .= " AND `id` IN (".query_ids($sql).")";
			break;
	}
	if($filter['account']) {
		$sql = "SELECT DISTINCT `zayav_id`
					FROM `zayav_expense`
					WHERE `category_id`=1
					  AND `zayav_id`";
		$cond .= " AND `id` NOT IN (".query_ids($sql).")";
	}

	$clear = '<a class="filter_clear">������� ������� ������</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'������ �� �������',
			'spisok' => '<div class="_empty">������ �� �������.</div>',
			'product_ids' => ''
		);

	$send['result'] = $clear.'�������'._end($send['all'], '�', '�').' '.$send['all'].' ����'._end($send['all'], '��', '��', '��');

	$page = $filter['page'];
	$limit = 20;
	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$zayav = array();
	while($r = mysql_fetch_assoc($q))
		$zayav[$r['id']] = $r;

	$zayav = _clientLink($zayav, 0, 1);
	$zayav = _dogNomer($zayav);
	$zayav = zayav_product_array($zayav);

	$send['spisok'] = '';
	foreach($zayav as $r) {
		unset($r['client_tel']);
		$send['spisok'] .= _zayavCategory($r, 'unit');
	}

	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="_next" val="'.($page + 1).'">'.
				'<span>�������� ��� '.$c.' ����'._end($c, '��', '��', '��').'</span>'.
			'</div>';
	}

	$ids = query_ids("SELECT DISTINCT `id` FROM `zayav` WHERE ".$cond);
	$send['product_ids'] = query_ids("SELECT DISTINCT `product_id` FROM `zayav_product` WHERE `zayav_id` IN (".$ids.")");

	return $send;
}//zayav_spisok()
function zayav_findfast($page=1, $find) {
	$cond = "`nomer_vg`='".$find."'
		  OR `nomer_g`='".$find."'
		  OR `nomer_d`='".$find."'
		  OR `nomer_t`='".$find."'
		  OR `adres` LIKE '%".$find."%'
		  OR `zakaz_txt` LIKE '%".$find."%'";
	$ids = array();
	if(preg_match(REGEXP_NUMERIC, $find)) {
		$ids[] = $find;
		$dog_id = query_value("SELECT `zayav_id` FROM `zayav_dogovor` WHERE `deleted`=0 AND `nomer`=".$find." LIMIT 1");
		if($dog_id)
			$ids[] = $dog_id;
	}

	//����� ������, ���� ���� ���������� � ��� � ��������� ��������
	$client_ids = query_ids("
		SELECT `id`
		FROM `client`
		WHERE !`deleted`
		  AND (`fio` LIKE '%".$find."%' OR `telefon` LIKE '%".$find."%')
	");
	if($client_ids) {
		$sql = "SELECT `id` FROM `zayav` WHERE !`deleted` AND `client_id` IN (".$client_ids.")";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$ids[] = $r['id'];
	}

	if(!empty($ids))
		$cond .= " OR `id` IN (".implode(',', array_unique($ids)).")";

	$cond = "!`deleted` AND (".$cond.")";

	$clear = '<a class="filter_clear">������� ������� ������</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'������ �� �������',
			'spisok' => '<div class="_empty">������ �� �������.</div>'
		);

	$send['result'] = $clear.'������'._end($send['all'], '�', '�').' '.$send['all'].' ����'._end($send['all'], '��', '��', '��');

	$limit = 20;
	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$zayav = array();
	while($r = mysql_fetch_assoc($q))
		$zayav[$r['id']] = $r;

	$zayav = _clientLink($zayav, 0, 1);
	$zayav = _dogNomer($zayav);
	$zayav = zayav_product_array($zayav);

	$reg = '/('.$find.')/i';
	$send['spisok'] = '';
	foreach($zayav as $r) {
		if($r['id'] == $find)
			$r['find_id'] = '<em>'.$r['id'].'</em>';
		if($r['dogovor_id'] && $r['dogovor_n'] == $find)
			$r['dogovor_nomer'] = '�<em>'.$r['dogovor_n'].'</em>';
		if($r['nomer_vg'] == $find)
			$r['nomer_vg'] = '<em>'.$r['nomer_vg'].'</em>';
		if($r['nomer_g'] == $find)
			$r['nomer_g'] = '<em>'.$r['nomer_g'].'</em>';
		if($r['nomer_d'] == $find)
			$r['nomer_d'] = '<em>'.$r['nomer_d'].'</em>';
		if($r['nomer_t'] == $find)
			$r['nomer_t'] = '<em>'.$r['nomer_t'].'</em>';
		if(preg_match($reg, $r['adres']))
			$r['adres'] = preg_replace($reg, '<em>\\1</em>', $r['adres'], 1);
		if(preg_match($reg, $r['zakaz_txt']))
			$r['zakaz_txt'] = preg_replace($reg, '<em>\\1</em>', $r['zakaz_txt'], 1);
		if(preg_match($reg, $r['client_link']))
			$r['client_link'] = preg_replace($reg, '<em>\\1</em>', $r['client_link'], 1);
		if(preg_match($reg, $r['client_tel']))
			$r['client_tel'] = preg_replace($reg, '<em>\\1</em>', $r['client_tel'], 1);
		else
			unset($r['client_tel']);
		$send['spisok'] .= _zayavCategory($r, 'unit');
	}
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="_next" id="zakaz_next" val="'.($page + 1).'">'.
				'<span>�������� ��� '.$c.' ����'._end($send['all'], '��', '��', '��').'</span>'.
			'</div>';
	}
	return $send;
}//zayav_findfast()

function _zakazStatus($id) {
	$arr = array(
		'0' => '����� ������',
		'1' => '����� ������� ����������',
		'2' => '����� ��������',
		'3' => '����� ������'
	);
	return $arr[$id];
}//_zakazStatus()

function zamer_table($mon=false, $zayav_id=0) {
	if(!$mon)
		$mon = strftime('%Y-%m');

	//���������� ���� � ������
	$monDaysCount = date('t', strtotime($mon));

	//����� ����� � �������� �� ��������� ������������ ������
	$zd = array(
		'30' => 18,
		'60' => 36,
		'90' => 54,
		'120' => 72,
		'150' => 90,
		'180' => 108
	);
	//������ ��� ������� �����
	$titleLeft = array(
		'30' => -45,
		'60' => -36,
		'90' => -27,
		'120' => -18,
		'150' => -9,
		'180' => 0
	);
	$sql = "SELECT *
			FROM `zayav`
			WHERE `deleted`=0
			  AND `zamer_status`=1
			  AND `zamer_dtime` LIKE '".$mon."%'
			ORDER BY `zamer_dtime` ASC";
	$q = query($sql);
	$zamer = array();
	while($r = mysql_fetch_assoc($q)) {
		$ex = explode(' ', $r['zamer_dtime']);
		$d = explode('-', $ex[0]);
		$h = explode(':', $ex[1]);
		$zamer[intval($d[2])][] = array(
			'id' => $r['id'],
			'nomer' => $r['id'],
			'dtime' => FullDataTime($r['zamer_dtime']),
			'dur' => _zamerDuration($r['zamer_duration']),
			'left' => ($h[0] - 10) * 36 + $h[1] / 10 * 6,
			'width' => $zd[$r['zamer_duration']],
			'tleft' => $titleLeft[$r['zamer_duration']],
			'sel' => $r['id'] == $zayav_id ? ' sel' : ''
		);
	}

	$days = '';
	for($n = 1; $n <= $monDaysCount; $n ++) {
		$z = '';
		if(isset($zamer[$n])) {
			$left = 0;
			foreach($zamer[$n] as $r) {
				$z .= '<div class="ztu'.$r['sel'].'" val="'.$r['id'].'" style="margin-left:'.($r['left'] - $left).'px;width:'.($r['width'] - 1).'px">'.
						'<div class="title" style="left:'.$r['tleft'].'px">'.
							'<div><b>����� �'.$r['nomer'].'</b></div>'.
							'<div><span>����:</span> '.$r['dtime'].'</div>'.
							'<div><span>������������:</span> '.$r['dur'].'</div>'.
							'<div class="ugb"></div>'.
						'</div>'.
					  '</div>';
				$left = $r['left'] + $r['width'];
			}
		}
		$days .= '<tr>'.
					'<td class="num">'.$n.
					'<td class="z">'.$z;
	}
	$hours = '';
	for($n = 10; $n <= 23; $n ++)
		$hours .= '<em>'.$n.'</em>';

	//������������ ������� ��� ��������������
	$ex = explode('-', $mon);
	$m = intval($ex[1]);
	$y = intval($ex[0]);

	$back_mon = $m - 1;
	$back_year = $y;
	if(!$back_mon) {
		$back_mon = 12;
		$back_year--;
	}
	$back = $back_year.'-'.($back_mon < 10 ? 0 : '').$back_mon;

	$next_mon = $m + 1;
	$next_year = $y;
	if($next_mon > 12) {
		$next_mon = 1;
		$next_year++;
	}
	$next = $next_year.'-'.($next_mon < 10 ? 0 : '').$next_mon;

	$sql = "SELECT COUNT(`id`)
			FROM `zayav`
			WHERE `deleted`=0
			  AND `zamer_status`=1
			  AND `zamer_dtime`<'".$mon."-01 00:00:00'
			LIMIT 1";
	$back_hide = !query_value($sql) ? ' class="vh"' : '';

	$sql = "SELECT COUNT(`id`)
			FROM `zayav`
			WHERE `deleted`=0
			  AND `zamer_status`=1
			  AND `zamer_dtime`>'".$mon."-".$monDaysCount." 23:59:59'
			LIMIT 1";
	$next_hide = !query_value($sql) ? ' class="vh"' : '';

	return
	'<div id="zamer-table">'.
		'<div class="mon">'.
			'<a val="'.$back.'"'.$back_hide.'>&laquo</a>'.
			'<b>'._monthDef($m, 1).' '.$y.'</b>'.
			'<a val="'.$next.'"'.$next_hide.'>&raquo</a>'.
		'</div>'.
		'<div class="hours">'.$hours.'</div>'.
		'<table>'.$days.'</table>'.
	'</div>';
}//zamer_table()
function _zamerStatus($id) {
	$arr = array(
		'0' => '����� ������',
		'1' => '������� ���������� ������',
		'2' => '����� ��������',
		'3' => '����� ������'
	);
	return $arr[$id];
}//_zakazStatus()

function _dogNomer($arr) {//���������� � ������ ������ �� ��������, ����������� �� dogovor_id
	$ids = array(); // �������� ���������
	$arrIds = array();
	foreach($arr as $key => $r)
		if(!empty($r['dogovor_id'])) {
			$ids[$r['dogovor_id']] = 1;
			$arrIds[$r['dogovor_id']][] = $key;
		}
	if(empty($ids))
		return $arr;
	$sql = "SELECT * FROM `zayav_dogovor` WHERE `id` IN (".implode(',', array_keys($ids)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($arrIds[$r['id']] as $id) {
			$arr[$id]['dogovor_nomer'] = '�'.$r['nomer'];
			$arr[$id]['dogovor_n'] = $r['nomer'];
			$arr[$id]['dogovor_data'] = dogovorData($r['data_create']);
			$arr[$id]['dogovor_sum'] = round($r['sum'], 2);
			$arr[$id]['dogovor_avans'] = round($r['avans'], 2);
		}
	return $arr;
}//_dogNomer()

function _setStatus($id) {
	$arr = array(
		'0' => '����� ������',
		'1' => '������� ���������',
		'2' => '��������� ���������',
		'3' => '��������� ��������'
	);
	return $arr[$id];
}//_zakazStatus()

function zayavDogovorList($zayav_id) {//������ ��������� ��� ������
	$sql = "SELECT * FROM `zayav_dogovor` WHERE `zayav_id`=".$zayav_id;
	$q = query($sql);
	$send = '';
	while($r = mysql_fetch_assoc($q)) {
		$d = explode('-', $r['data_create']);
		$data = $d[2].'/'.$d[1].'/'.$d[0];
		$reason = $r['reason'] ? "\n".$r['reason'] : '';
		$title = '�� '.$data.' �. �� ����� '.round($r['sum'], 2).' ���.'.$reason;
		$del = $r['deleted'] ? ' d' : '';
		$send .= '<b class="dogn'.$del._tooltip($title, -7, 'l').'�'.$r['nomer'].'</b> '.
			'<a href="'.LINK_DOGOVOR.$r['link'].'.doc" class="img_word'._tooltip('�����������', -41).'</a>';
	}
	return $send;
}//zayavDogovorList()
function zayav_info($zayav_id) {
	$sql = "SELECT * FROM `zayav` WHERE `id`=".$zayav_id." LIMIT 1";
	if(!$z = mysql_fetch_assoc(query($sql)))
		return _noauth('������ �� ����������.');

	if(!VIEWER_ADMIN && $z['deleted'])
		return _noauth('������ ������');


	$type = _zayavCategory($z);

	setcookie('zayav_dop', $type, time() + 846000, "/");
	define('ZAKAZ', $type == 'zakaz');
	define('ZAMER', $type == 'zamer');
	define('DOG', $type == 'dog');
	define('SET', $type == 'set');

	$client = query_assoc("SELECT * FROM `client` WHERE !`deleted` AND `id`=".$z['client_id']);

	$dog = $z['dogovor_id'] ? query_assoc("SELECT * FROM `zayav_dogovor` WHERE `id`=".$z['dogovor_id']) : array();
	$dogSpisok = $z['dogovor_id'] ? zayavDogovorList($z['id']).'<input type="hidden" id="dogovor_reaction" />' : '<input type="hidden" id="dogovor_action" />';

	$d = explode(' ', $z['zamer_dtime']);
	$time = explode(':', $d[1]);

	$accSum = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE !`deleted` AND `zayav_id`=".$zayav_id);
	$rashod = zayav_expense_spisok($z['id'], 'all');
	define('DOPL', $z['accrual_sum'] - $z['oplata_sum']);

	$history = RULES_HISTORYSHOW ? history(array('zayav_id'=>$zayav_id)) : '';

	$invoices_sum = array();
	foreach(_invoice() as $id => $r)
		$invoices_sum[$id] = _invoiceBalans($id);

	$avans_owner = query_value("SELECT COUNT(`id`) FROM `money` WHERE !`deleted` AND `zayav_id`=".$zayav_id." AND `owner_id`=".VIEWER_ID);
	define('MONEY_EXIST', query_value("SELECT COUNT(`id`) FROM `money` WHERE !`deleted` AND `zayav_id`=".$zayav_id));

	return
	'<script type="text/javascript">'.
		'var ZAYAV={'.
			'id:'.$zayav_id.','.
			'head:"'.addslashes(_zayavCategory($z, 'head')).'",'.
			'client_fio:"'.$client['fio'].'",'.
			'client_adres:"'.addslashes(htmlspecialchars_decode($client['adres'])).'",'.
			'product:['.zayav_product_spisok($z['id'], 'json').'],'.
			'status:'._zayavCategory($z, 'status_id').','.
			(_zayavCategory($z, 'status_id') == 2 ? 'status_day:"'.$z['status_day'].'",' : '').
			'zakaz_txt:"'.$z['zakaz_txt'].'",'.
			'adres:"'.$z['adres'].'",'.
			'rashod:['.$rashod['json'].'],'.

			'nomer_vg:"'.addslashes($z['nomer_vg']).'",'.
			'nomer_g:"'.addslashes($z['nomer_g']).'",'.
			'nomer_d:"'.addslashes($z['nomer_d']).'",'.
			'nomer_t:"'.addslashes($z['nomer_t']).'",'.

			'day:"'.$d[0].'",'.
			'hour:'.intval($time[0]).','.
			'min:'.intval($time[1]).','.
			'dur:'.$z['zamer_duration'].','.
			'isum:'._assJson($invoices_sum).
		'},'.
		'DOG={'.
			'id:'.(empty($dog) ? 0 : $dog['id']).','.
			'nomer:"'.(empty($dog) ? '' : $dog['nomer']).'",'.
			'data_create:"'.(empty($dog) ? '' : $dog['data_create']).'",'.
			'fio:"'.(empty($dog) ? $client['fio'] : $dog['fio']).'",'.
			'adres:"'.(empty($dog) ? $client['adres'] : $dog['adres']).'",'.
			'pasp_seria:"'.(empty($dog) ? $client['pasp_seria'] : $dog['pasp_seria']).'",'.
			'pasp_nomer:"'.(empty($dog) ? $client['pasp_nomer'] : $dog['pasp_nomer']).'",'.
			'pasp_adres:"'.(empty($dog) ? $client['pasp_adres'] : $dog['pasp_adres']).'",'.
			'pasp_ovd:"'.(empty($dog) ? $client['pasp_ovd'] : $dog['pasp_ovd']).'",'.
			'pasp_data:"'.(empty($dog) ? $client['pasp_data'] : $dog['pasp_data']).'",'.
			'sum:"'.(empty($dog) ? '' : round($dog['sum'], 2)).'",'.
			'avans:"'.(empty($dog) || $dog['avans'] == 0 ? '' : round($dog['avans'], 2)).'",'.
			'avans_owner:'.(empty($dog) ? 1 : (@$dog['avans'] > 0 && $avans_owner ? 1 : 0)).','.
			'cut:"'.(empty($dog) ? '' : $dog['cut']).'"'.
		'},'.
		'OPL={'.
			'from:"zayav",'.
			'client_id:'.$z['client_id'].','.
			'client_fio:"'.addslashes(_clientLink($z['client_id'])).'",'.
			'zayav_id:'.$zayav_id.','.
			'zayav_head:"'.addslashes(_zayavCategory($z, 'head')).'"'.
		'};'.
	'</script>'.
	'<div class="zayav-info '.$type.'">'.
		'<div id="dopLinks">'.
			'<a class="link sel zinfo">����������</a>'.
(!$z['deleted'] ?
			'<a class="link '.$type.'_edit">��������������</a>'.
	(ZAKAZ || SET ?
			'<a class="link acc-add">���������</a>'.
			'<a class="link income-add">������ �����</a>'.
			(!MONEY_EXIST ? '<a class="delete">������� ������</a>' : '')
	: '')
: '').
			(RULES_HISTORYSHOW ? '<a class="link hist">�������</a>' : '').
		'</div>'.
		($z['deleted'] ? '<div class="_info">������ ������</div>' : '').
		'<div class="content">'.
			'<TABLE class="tabmain"><TR>'.
				'<TD class="mainleft">'.
					'<div class="headName">'.
						_zayavCategory($z, 'head').
						'<div class="zid">#'.$z['id'].'</div>'.
						(ZAKAZ && !$z['deleted'] ? '<a class="zakaz-to-set">��������� � ���������</a>' : '').
						(SET && !$z['deleted'] ? '<a class="set-to-zakaz">��������� � ������</a>' : '').
					'</div>'.
					'<table class="tabInfo">'.
						'<tr><td class="label">������:<td>'._clientLink($z['client_id'], 0, 1).
						'<tr><td class="label top">�������:<td>'.zayav_product_spisok($z['id']).$z['zakaz_txt'].
			   (ZAMER ? '<tr><td class="label">����� ������:<td><b>'.$z['adres'].'</b>'.
						'<tr><td class="label">���� ������:'.
							'<td><span class="zamer-dtime" title="'._zamerDuration($z['zamer_duration']).'">'.
									FullDataTime($z['zamer_dtime']).
								'</span>'.
								($z['zamer_status'] == 1 ? '<span class="zamer-left">'._remindDayLeft(1, $z['zamer_dtime']).'</span>' : '').
								'<a class="zamer_table" val="'.$z['id'].'">������� �������</a>'
		       : '').

((DOG || SET) && $z['adres'] ?
						'<tr><td class="label">����� ���������:<td><b>'.$z['adres'].'</b>'
: '').
(ZAKAZ && $z['adres'] ? '<tr><td class="label">�����:<td>'.$z['adres'] : '').

(ZAKAZ || SET ? 		'<tr><td class="label">�������:<td>'.$dogSpisok.
	  ($z['nomer_vg'] ? '<tr><td class="label top">����� ��:<td>'._attach('vg', $z['id'], '���������� ��������', $z['nomer_vg']) : '').
	   ($z['nomer_g'] ? '<tr><td class="label top">����� �:<td>'._attach('g', $z['id'], '���������� ��������', $z['nomer_g']) : '').
	   ($z['nomer_d'] ? '<tr><td class="label top">����� �:<td>'._attach('d', $z['id'], '���������� ��������', $z['nomer_d']) : '').
	   ($z['nomer_t'] ? '<tr><td class="label top">����� T:<td>'._attach('t', $z['id'], '���������� ��������', $z['nomer_t']) : '').
						'<tr><td class="label top">�����:<td>'._attach('files', $z['id'], '���������')
: '').
					(_zayavCategory($z, 'status_name') ?
						'<tr><td class="label">������'.($type == 'dog' ? ' ������' : '').':'.
							'<td><div style="background-color:#'._statusColor($z[($type == 'dog' ? 'zamer' : $type).'_status']).'" class="status '.$type.'_status">'.
									_zayavCategory($z, 'status_name').
									(_zayavCategory($z, 'status_id') == 2  && !DOG ? ' '.FullData($z['status_day'], 1) : '').
								'</div>'
					: '').
						'<tr class="acc_tr'.($z['accrual_sum'] ? '' : ' dn').'">'.
							'<td class="label">���������:'.
							'<td><b class="acc">'.$z['accrual_sum'].'</b> ���.'.
						'<tr class="opl_tr'.($z['oplata_sum'] ? '' : ' dn').'">'.
							'<td class="label">��������:'.
							'<td><b class="opl">'.$z['oplata_sum'].'</b> ���.'.
								'<span class="dopl'.(DOPL ? '' : ' dn')._tooltip('����������� �������', -60).(DOPL > 0 ? '+' : '').DOPL.'</span>'.
					'</table>'.
	(ZAKAZ || SET ?
				'<TD class="mainright">'.
					'<div class="headBlue">������� �� ������<a class="add expense-edit">��������</a></div>'.
					'<div class="acc-sum">'.
						($accSum != 0 ? '����� ����� ����������: <b>'._sumSpace($accSum).'</b> ���.' : '���������� ���.').
					'</div>'.
					'<div class="zrashod">'.$rashod['html'].'</div>'
	: '').
			'</TABLE>'.
	(DOG ?  '<div class="vkButton dogovor_create"><button>��������� �������</button></div>'.
				'<a class="dogovor_no_require">������� �� ���������</a>'
	: '').

			'<div class="dtime_add">������ ��'.(_viewer($z['viewer_id_add'], 'sex') == 1 ? '����' : '��').' '.
				_viewer($z['viewer_id_add'], 'name').' '.
				FullDataTime($z['dtime_add']).
			'</div>'.

			_remind_zayav($zayav_id).

	(!DOG ?	'<div class="headBlue mon">���������� � �������'.
		(!$z['deleted'] ? '<a class="add refund-add'._tooltip('���������� ������� �������� �������', -215, 'r').'�������</a>'.
						  '<em>::</em>'.
						  '<a class="add income-add">������ �����</a>'.
						  '<em>::</em>'.
						  '<a class="add acc-add">���������</a>'

		: '').
			'</div>'.
			'<div id="income_spisok">'.zayav_money($z['id']).'</div>'
	: '').

			_vkComment('zayav', $z['id']).
		'</div>'.
		(RULES_HISTORYSHOW ?
			'<div class="histories">'.
				'<div class="headName">'.
					_zayavCategory($z, 'head').
					'<div class="zid">#'.$z['id'].'</div>'.
				'</div>'.
				'<div id="hspisok">'.$history['spisok'].'</div>'.
			'</div>'
		: '').
	'</div>';
}//zayav_info()
function zayav_money($zayav_id) {
	$sql = "(
		SELECT
			'acc' AS `type`,
			`id`,
			0 AS `invoice_id`,
			`sum`,
			`client_id`,
			`zayav_id`,
			`dogovor_id`,
			`prim`,
			0 AS `confirm`,
			'' AS `confirm_dtime`,
			0 AS `owner_id`,
			0 AS `refund`,
			`dtime_add`,
			`viewer_id_add`,
			`deleted`
		FROM `accrual`
		WHERE !`deleted`
		  AND `zayav_id`=".$zayav_id."
	) UNION (
		SELECT
			'opl' AS `type`,
			`id`,
			`invoice_id`,
			`sum`,
			`client_id`,
			`zayav_id`,
			`dogovor_id`,
			`prim`,
			`confirm`,
			`confirm_dtime`,
			`owner_id`,
			`refund`,
			`dtime_add`,
			`viewer_id_add`,
			`deleted`
        FROM `money`
        WHERE !`deleted`
          AND `zayav_id`=".$zayav_id."
	)
		ORDER BY `dtime_add`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '';

	$spisok = array();
	while($r = mysql_fetch_assoc($q)) {
		$key = strtotime($r['dtime_add']);
		if(isset($spisok[$key]))
			$key++;
		$spisok[$key] = $r;
	}

	$spisok = _dogNomer($spisok);

	$send = '<table class="_spisok _money">';
	foreach($spisok as $r)
		if($r['type'] == 'acc')
			$send .= zayav_accrual_unit($r);
		elseif($r['sum'] > 0)
				$send .= income_unit($r, array('zayav_id'=>$zayav_id));
			else
				$send .= zayav_refund_unit($r);

	$send .= '</table>';
	return $send;
}//zayav_money()
function zayav_accrual_unit($r) {
	return '<tr val="'.$r['id'].'">'.
		'<td class="sum acc'._tooltip('����������', -5).'<b>'._sumSpace($r['sum']).'</b>'.
		'<td>'.$r['prim'].
		'<td class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -40).FullDataTime($r['dtime_add']).
		'<td class="ed" align="right">'.
			(!$r['dogovor_id'] ? '<div class="img_del accrual-del'._tooltip('������� ����������', -116, 'r').'</div>' : '');
}//zayav_accrual_unit()
function zayav_refund_unit($r) {
	return '<tr val="'.$r['id'].'">'.
		'<td class="sum ref'._tooltip('�������', 5).'<b>'._sumSpace($r['sum']).'</b>'.
		'<td><span class="type">'._invoice($r['invoice_id']).(empty($r['prim']) ? '' : ':').'</span> '.$r['prim'].
		'<td class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -40).FullDataTime($r['dtime_add']).
		'<td class="ed" align="right">'.
			(!$r['dogovor_id'] && TODAY == substr($r['dtime_add'], 0, 10) && VIEWER_ID == $r['owner_id'] ?
				'<div class="img_del refund-del'._tooltip('������� �������', -97, 'r').'</div>'
			: '');
}//zayav_refund_unit()
function _attach($type, $zayav_id, $name='�����...', $num='') {
	return
	'<div class="_attach">'.
		'<table><tr>'.
			($num ? '<td class="num">'.$num : '').
			'<td><div class="files">'._attach_files($type, $zayav_id).'</div>'.
				'<div class="form">'.
					'<form method="post" action="'.APP_HTML.'/ajax/main.php?'.VALUES.'" enctype="multipart/form-data" target="'.$type.$zayav_id.'_frame">'.
						_attach_form($type, $zayav_id).
					'</form>'.
					'<a class="attach_a">'.$name.'</a>'.
				'</div>'.
				'<iframe name="'.$type.$zayav_id.'_frame"></iframe>'.
		'</table>'.
	'</div>';
}
function _attach_files($type, $zayav_id) {
	$sql = "SELECT * FROM `attach` WHERE `deleted`=0 AND `type`='".$type."' AND `zayav_id`=".$zayav_id." ORDER BY `id`";
	$q = query($sql);
	$send = array();
	while($r = mysql_fetch_assoc($q))
		$send[] =
			'<div>'.
				'<a href="'.$r['link'].'">'.$r['name'].'</a>'.
				'<div class="img_minidel" val="'.$r['id'].'"></div>'.
			'</div>';
	return implode(' ', $send);
}//_attach_files()
function _attach_form($type, $zayav_id) {
	return
	'<input type="file" name="f1" class="inp2">'.
	'<input type="file" name="f2">'.
	'<input type="hidden" name="op" value="attach_upload">'.
	'<input type="hidden" name="type" class="type" value="'.$type.'">'.
	'<input type="hidden" name="zayav_id" class="zayav_id" value="'.$zayav_id.'">';
}//_attach_form()

/*
function wordSm($sm) {//������� ����������� � ������
	$twips = round($sm * 567);
	return $twips;
}//wordSm()
*/
function dogovorData($v) {//�������������� ���� ��� ��������
	$d = explode('-', $v);
	return $d[2].'/'.$d[1].'/'.$d[0].' �.';
}//dogovorData()
function dogovorFilter($v) {
	if(!preg_match(REGEXP_NUMERIC, $v['id']))
		return '������: ������������ ������������� ��������.';
	if(!preg_match(REGEXP_NUMERIC, $v['zayav_id']) && !$v['zayav_id'])
		return '������: �������� ����� ������.';
	if(!preg_match(REGEXP_NUMERIC, $v['nomer']) && !$v['nomer'])
		return '������: ����������� ������ ����� ��������.';
	if(!preg_match(REGEXP_DATE, $v['data_create']))
		return '������: ����������� ������� ���� ���������� ��������.';
	if(!preg_match(REGEXP_CENA, $v['sum']) || $v['sum'] == 0)
		return '������: ����������� ������� ����� �� ��������.';
	if(!empty($v['avans']) && !preg_match(REGEXP_CENA, $v['avans']))
		return '������: ����������� ������ ��������� �����.';
	if(!empty($v['cut']))
		foreach(explode(',', $v['cut']) as $r) {
			$ex = explode(':', $r);
			if(!preg_match(REGEXP_CENA, $v['sum']) || $ex[0] == 0 || !preg_match(REGEXP_DATE, $ex[1]))
				return '������: ������������ ������ ��� �������� �������.';
			if(strtotime($ex[1]) < TODAY_UNIXTIME)
				return '������: � �������� ������� ������ ���������� ����.';
		}
	$send = array(
		'id' => intval($v['id']),
		'zayav_id' => intval($v['zayav_id']),
		'nomer' => intval($v['nomer']),
		'fio' => htmlspecialchars(trim($v['fio'])),
		'adres' => htmlspecialchars(trim($v['adres'])),
		'sum' => str_replace(',', '.', $v['sum']),
		'avans' => round(str_replace(',', '.', $v['avans']), 2),
		'data_create' => $v['data_create'],
		'link' => time().'_dogovor_'.intval($v['nomer']).'_'.$v['data_create'],
		'pasp_seria' => htmlspecialchars(trim($v['pasp_seria'])),
		'pasp_nomer' => htmlspecialchars(trim($v['pasp_nomer'])),
		'pasp_adres' => htmlspecialchars(trim($v['pasp_adres'])),
		'pasp_ovd' => htmlspecialchars(trim($v['pasp_ovd'])),
		'pasp_data' => htmlspecialchars(trim($v['pasp_data'])),
		'cut' => $v['cut'],
		'reason' => htmlspecialchars(trim($v['reason']))
	);

	if(query_value("SELECT COUNT(`id`) FROM `zayav_dogovor` WHERE `deleted`=0 AND `id`!=".$send['id']." AND `nomer`=".$send['nomer']))
		return '������: ������� � ������� <b>'.$send['nomer'].'</b> ��� ��� ��������.';

	if(empty($send['fio']))
		return '������: �� ������� ��� �������.';

	if($send['sum'] < $send['avans'])
		return '������: ��������� ����� �� ����� ���� ������ ����� ��������.';

	if(!$send['client_id'] = query_value("SELECT `client_id` FROM `zayav` WHERE `deleted`=0 AND `id`=".$send['zayav_id']))
		return '������: ������ id = '.$send['zayav_id'].' �� ����������, ���� ��� ���� �������.';

	return $send;
}//dogovorFilter()
/*
function dogovor_print_($dog_id) {
	$v = $dog_id;
	$cash_id = 0;
	if(!is_array($v)) {
		$v = query_assoc("SELECT * FROM `zayav_dogovor` WHERE `deleted`=0 AND `id`=".$dog_id);
		if($v['avans'] > 0)
			$cash_id = query_value("SELECT `id` FROM `money` WHERE `deleted`=0 AND `dogovor_id`=".$v['id']." LIMIT 1");
	}

	$g = query_assoc("SELECT * FROM `setup_global` LIMIT 1");

	$ex = explode(' ', $v['fio']);
	$fioPodpis = $ex[0].' '.
		(isset($ex[1]) ? ' '.$ex[1][0].'.' : '').
		(isset($ex[2]) ? ' '.$ex[2][0].'.' : '');

	$dopl = $v['sum'] - $v['avans'];
	$adres = $v['pasp_adres'] ? $v['pasp_adres'] : $v['adres'];

	require_once API_PATH.'/word/PHPWord.php';

	$b = array('bold' => true);
	$r = array('align' => 'right');

	$word = new PHPWord();
	$section = $word->createSection(array(
		'orientation' => null,
		'marginLeft' => wordSm(1),
		'marginRight' => wordSm(2),
		'marginTop' => wordSm(1),
		'marginBottom' => wordSm(1)
	));

	$headNameStyle = array(
		'bold' => true,
		'name' => 'Arial',
		'size' => 10
	);
	$headNamePar = array(
		'align'=>'center',
		'spaceBefore' => wordSm(2.4),
		'spaceAfter' => wordSm(0.47)
	);
	$section->addText(utf8('������� �').$v['nomer'], $headNameStyle, $headNamePar);

	$table = $section->addTable();
	$table->addRow();
	$table->addCell(wordSm(10))->addText(utf8('����� �������'), $b);
	$table->addCell(wordSm(8))->addText(utf8(dogovorData($v['data_create'])), $b, $r);

	$section->addText(utf8(
		'�������� � ������������ ���������������� ����������� ��������, '.
		'� ���� ��������� �� ��������, ��������� ���� �������������, ����������� �� ��������� ������������, '.
		'� ����� �������, � '.$v['fio'].($adres ? ', '.$adres : '').', ��������� � ���������� ���������, � ������ �������, '.
		'��������� ��������� �������, ����� ��������, � �������������:'
	), null, array('align'=>'both','spacing'=>wordSm(0.005)));




	header('Content-Type:application/vnd.ms-word');
	header('Content-Disposition:attachment;filename="dogovor.doc"');
	$writer = PHPWord_IOFactory::createWriter($word, 'Word2007');
	$writer->save('php://output');
}//dogovor_print()
*/
function dogovor_print($dog_id) {
	require_once(API_PATH.'/clsMsDocGenerator.php');

	$v = $dog_id;
	$cash_id = 0;
	if(!is_array($v)) {
		$v = query_assoc("SELECT * FROM `zayav_dogovor` WHERE `deleted`=0 AND `id`=".$dog_id);
		if($v['avans'] > 0)
			$cash_id = query_value("SELECT `id` FROM `money` WHERE `deleted`=0 AND `dogovor_id`=".$v['id']." LIMIT 1");
	}

	$g = query_assoc("SELECT * FROM `setup_global` LIMIT 1");

	$ex = explode(' ', $v['fio']);
	$fioPodpis = $ex[0].' '.
				 (isset($ex[1]) ? ' '.$ex[1][0].'.' : '').
				 (isset($ex[2]) ? ' '.$ex[2][0].'.' : '');

	$doc = new clsMsDocGenerator(
		$pageOrientation = 'PORTRAIT',
		$pageType = 'A4',
		$cssFile = DOCUMENT_ROOT.'/css/dogovor.css',
		$topMargin = 1,
		$rightMargin = 2,
		$bottomMargin = 1,
		$leftMargin = 1
	);

	$dopl = $v['sum'] - $v['avans'];
	$adres = $v['pasp_adres'] ? $v['pasp_adres'] : $v['adres'];

	$doc->addParagraph(
	'<div class="head-name">������� �'.$v['nomer'].'</div>'.
	'<table class="city_data"><tr><td>����� �������<th>'.dogovorData($v['data_create']).'</table>'.
	'<div class="paragraph">'.
		'<p>�������� � ������������ ���������������� ����������� ��������, '.
		'� ���� ��������� �� ��������, '._viewer(VIEWER_ID, 'name_full').', ����������� �� ��������� ������������, '.
		'� ����� �������, � '.$v['fio'].($adres ? ', '.$adres : '').', ��������� � ���������� ���������, � ������ �������, '.
		'��������� ��������� �������, ����� ��������, � �������������:'.
	'</div>'.
	'<div class="p-head">1. ������� ��������</div>'.
	'<div class="paragraph">'.
		'<p>1.1. ��������� ��������� �� ���� ������������� �� ���������� ������ �� ������������ � �������� ������� (������� ������, ������� ������, �������� ������, �������� � ������������ �����) � ������������ � ��������������� ���������������� ������� � ������������ ��������� (����� ������). ������ �� ��������� ������� � ����������� �� ��� �� ������ ���������.'.
		'<p>1.2. ������ �������������� ������ ���������� � ������������, ���������� ������������ ������ ���������� ��������.'.
	'</div>'.
	'<div class="p-head">2. ����������� ������</div>'.
	'<div class="paragraph">'.
		'<p>2.1. ��������� ��������� ��������� ����� � ����������� ������� ���������� �������� � ����������, ������������� � ��������� ������� ���� � ��������� � ������ �23166-99 ������ ������� �ӻ, �30970-2002 ������ ������� �� ��ջ ��� ������� ������, � ������� ������������ ������������� ������ �������� ��� ������� ������, � ����������� �� ������������ ������, ����������� �� ������������ �����, � ������ �111-2001 ������� ��������, �24866-99 ������������� ������� ������������� ���������� �.'.
		'<p>2.2. ��������������� ������������� ���� �������� ������ � ���������� ��������������� ����� ���������� 20 ������� ����. ������������� ���� ���������� �������� �� ����� �������� ������� ���� � ������� ����������� �� ��������� ������ ������ �� �������� � ����������� ���������� ������� ������� 2.3. � 2.4. ������ ����� ������������� �� ����������� ��������. � ������ ������ ������� � ������� �������, ���� �������� ������������� �� ���������� �������������� ���� �� ������������ ������� �����������, ��������� � ������������.'.
		'<p>2.3. �������� ��������� ���������� ������ ����������� � ������ �������������� � ������� �������, ������ ������� ���������, ��������� �������� �� ���� � �����������, ���� ������� ������������ ���������� ����� �� ��������� ��������� �� ������ ���������. '.
		'��������� �� �������� �� ����������� �������� �������� � ���� �����. ��������� �� ���� ��������������� �� ��������� ��������� ����������� ������� ������ ��� ���������� ��������� � ���������� �����, ��������� � ��������� �������� �������� � ������� ������� ������������ ��������. ����������������� ������ ���������� �� ������� � �� ���� ���������. � ��������� ������� ������� �� ������ ������������ ��������� ��� �� 4 �� �������.'.
		'<p>2.4. �������� ��������� ������� ���� �� ����������� ���������� �������������� �� �������� ��� �������� ��������. � ������, ���� �������� �� ������ ������ ���� � ������ ����������� ����������, �������� ���������� �������������� ����� ��������� ������� �� ������� 1000 ���./�����, ��� ���� ������������� ���� ���������� �������� �������� 10 ������� ���� � ������� ����������� ����������� ������� � ����� ���������.'.
		'<p>2.5. ��� ����������� ��������� ������������ ����������� �� ����� ��������� � ������� ���������. � ������, ���������� ��������� ��� ��� ������������� �� ������� � ������������� ���� ��������, ��������� �������� ������������ �� ������� 1000 ���./�����, ��� ���� ������������� ���� ���������� �������� �������� 10 ������� ���� � ������� ����������� ����������� ������� � ����� ���������.'.
		'<p>2.6. ��������� ��������� ������� ������������ ����� � �����, ���� ��� ������������ �� �������. ��������� �� ����� ��������������� �� ����� ������������� ������, ������������� ����� ���������� ����� �� ��������� ���������. �������� ��������� ����������� ����� � ���������� ������ �������� ����������� ������. ��������� ��������� ������� ������������ ����� �� ������������������ �������� (� ������������ � ������� �239-29 �� 29.05.2003), ������ � ������, ���� ������ ������ ���� �������� ���������� � ������� � ������������.'.
		'<p>2.7. �������� ��������� �������� ������ ��������� ������ �� ������ ��������� ������� � ����������� �� ��� � ������������ �� �������������.'.
		'<p>2.8. ����� ������������� �� ����� ��������� � ��������� � ������ ���������� �� ���������������������� ����������. � ������ ����������� �� ����������, ������������� � �������� ����, ���� ����������� ���������� � ���������������������� ����������.'.
		'<p>2.9. �������� ��������� ����������� ������ ����������� ����� �� ������� �������, �� ����������, ��������, �������������, �������� ���� � �������� ������� � ��������� ��� ���������� ����� ������������ � ���� ����� � ������ ������ � ��� ���������� ����� ���� ����� - ������ ������. �������� ����� �1 ������������ ������� � ���������, � ����� �2 ������� ������������ ���������� ��������� ������������. ��� �����-�������� ��������� ��������� �������� �� �������������� ������� ��� ��������� ������������ � ������, ���� ������� ������������ ��������������� ��������. ����������� ���������� ��� ������������ ����������� ��������� ���������� �� �������� ���������� ����������� ������� ��������. � ������, ���� �������� ������������ ����������� ���� �����-������� ������ �/��� �����������, ����� ��������� ������������� �����������.'.
		'<p>2.10. � ������ ���������� ���������� ���������������������� ���������� ��� ���� ����������� ����� � �������������, ��������� ��������� ����������� ������ ����������� � ������� 5 ����, ��� ���� ���� ���������� �������� ������������� ������������ �� ��������� ����.'.
		'<p>2.11. � ������ �������� � ���������� ��������� ��������� ������ �������� ��������������� ����� ������ ��� ��������� ���� ��������, ��������������� ��������� ��������� ��� ����� ������� � ������� 14 ������� ����, ��������� �� ���� ��������� ��������� ���������.'.
	'</div>'.
	'<div class="p-head">3. ���� ������ � ������� ��������</div>'.
	'<div class="paragraph">'.
		'<p>3.1. ������ ��������� ������ ����������: '.$v['sum'].' ('.numberToWord($v['sum']).' ����'._end($v['sum'], '�', '�', '��').') ��������� � ������������, �������� ��������, � ��������� ��� ��������� �������� ������ �� ��������.'.
		($v['avans'] ?
		'<p>3.2. ������ �� ���������� �������� �������������� � ��������� �������:'.
		'<p>3.2.1. ��������� ����� � ������� '.$v['avans'].' ('.numberToWord($v['avans']).' ����'._end($v['avans'], '�', '�', '��').') �������� ���������� � ���� ���������� ���������� ��������. � ������ ���������� ����� �� ��������, ��������� ����� ���������� 100% ����� ��������.'.
		($dopl ? '<p>3.2.2. ������� �� ��������, � ����� '.$dopl.' ('.numberToWord($dopl).' ����'._end($dopl, '�', '�', '��').'), ������������ � ����� �� ��������� �������: ______________________________________.' : '')
		: '').
	'</div>'.
	'<div class="p-head">4. �������� � ����������� �������������</div>'.
	'<div class="paragraph">'.
		'<p>4.1. ����������� ���� �� ������� ����� � ��� ����, �� ��������� � ���������� ������ �� ������� ������ � ���� ���. ����������� ���� �� ������� �����, ��������� ������� � ������ - ���� ���. �� ��������� � ���������� ������ �� ��������� ������� ������, ��������� ������ � ����� � ���� ���. ����������� ���� ��������� � ������� ���������� ��������� ����������� ���������� (��� ����� � ������� ������). ��� ������������� ������� ������-��������� � ������������ ������� ������ ������������� ������������ ������������ ������������. �������� ���������������, ��� ��� ��������� ������������� ������������, �������� ����������� ���������� � ����������� ������������ � ������ ������ ��� ����� ������� ����������� ���������� �����, ��� ��� ��������� ������������� ������������. �������� �����������, ��� ��� ���������� ����������� ��������� ���������� � ����������� ������ �� �������������, ���������� ����������� ������ ����������� � ��������� ���������������� ��� ������ ���������.'.
		'<p>4.2. ��������� ��������� �������� �������� � ������ ������ ������������� �� ���� ����, � ������ ������ �� �� ����� � ������� ������������ �����. ���� ���������� ����������� ����� ���������� �� ����� 20 ������� ���� � ������� ����������� ���������� ���������. ���������� ��������� ����������� � ����������� ����� �������� ���� �� �����.'.
		'<p>4.3. �������� �� ���������������� �� ������, ����� ����� (��� ��� �������������) �������� ���� ������������ �������������� ���������� ������������ ������������ ������, �������� ������� ��� ��� � ������ ������������� ������������� ������������� ����.'.
	'</div>'.
	'<div class="p-head">5. ��������������� ������, ����-�������� �������������� � ��������������� ������</div>'.
	'<div class="paragraph">'.
		'<p>5.1. ������� ������������� �� ��������������� �� ��������� ��� ������ ������������ ������������ �� ���������� ��������, ���� ��� ������� ���������� ������������� ������������� ���� (����-�����), �.�. ������, ��������� ��������, �����, ������, �������� ����������������� ����������� ����������, ���������� ��������� � ��������. ��� ���� ���� ���������� ������������ �� �������� ������������ �� ������ �������� ��������� �������������.'.
		'<p>5.2. �� ������������ ��� ������������ ���������� ������������ ������� ����� ��������������� � ������������ � ����������� ����������������� ���������� ���������. � ������ ��������� ������ ���������� �������� ��������� ����������� ��������� ��������� � ������������ � ������� �� "� ������ ���� ������������" ������� 3% � ���� �� ����� ���������������� ������������� ������ ��������� � ������������ � �� ����� �� ��������� ����� � �����, ��������� � ������������.'.
	'</div>'.
	'<div class="p-head">6. ��������� ������� �������� � ������� ���������� ������</div>'.
	'<div class="paragraph">'.
		'<p>6.1. ��� ��������� � ���������� � ���������� �������� ������������� ���� � ��� ������, ���� ��� ��������� � ���������� ���� � ��������� ������ ���������.'.
		'<p>6.2. ��� ����� � �����������, ������� ����� ���������� �� ���������� �������� ����� �� ����������� ����������� ���� ������������ �����������.'.
		'<p>6.3. �����, �� ���������� ���������� � ���������� �����������, �������� ���������� � ������������ � ����������� ����������������� ��.'.
	'</div>'.
	'<div class="p-head">7. ���� �������� ��������</div>'.
	'<div class="paragraph">'.
		'<p>7.1. ��������� ������� �������� � ���� � ������� ��� ���������� � ��������� �� ������� ���������� ������������ ������ ���������.'.
	'</div>'.
	'<div class="p-head">8. �������������� ���������</div>'.
	'<div class="paragraph">'.
		'<p>8.1. ��������� ������� ��������� � ���� ����������� �� ������ ��� ������ �� ������, ������� ������ ����������� ����.'.
	'</div>'.
	'<div class="p-head">9. ����������� ������ � ���������� ��������� ������</div>'.
	'<table class="rekvisit">'.
		'<tr><td><b>���������:</b><br />'.
				'��� �'.$g['org_name'].'�<br />'.
				'���� '.$g['ogrn'].'<br />'.
				'��� '.$g['inn'].'<br />'.
				'��� '.$g['kpp'].'<br />'.
				str_replace("\n", '<br />', $g['yur_adres']).'<br />'.
				'���. '.$g['telefon'].'<br /><br />'.
				'����� �����: '.$g['ofice_adres'].
			'<td><b>��������:</b><br />'.
				$v['fio'].'<br />'.
				'������� ����� '.$v['pasp_seria'].' '.$v['pasp_nomer'].'<br />'.
				'����� '.$v['pasp_ovd'].' '.$v['pasp_data'].'<br /><br />'.
				$adres.
	'</table>'.
	'<div class="podpis-head">������� ������:</div>'.
	'<table class="podpis">'.
		'<tr><td>��������� ________________ '._viewer(VIEWER_ID, 'name_init').
			'<td>�������� ________________ '.$fioPodpis.
	'</table>'.
	'<div class="mp">�.�.</div>');

	$doc->newPage();

	$doc->addParagraph(
	'<div class="ekz">��������� ���������</div>'.
	'<div class="act-head">��� �����-������ ������</div>'.
	'<table class="act-tab">'.
		'<tr><td class="label">�� ������:<td class="title">'.$v['adres'].'<td><td>'.
		'<tr><td class="label">�����:<td class="title">'.$v['nomer'].'<td class="label">��������:<td>'.$fioPodpis.
	'</table>'.
	'<div class="act-inf">��������� ��������� �������� ���������� ��� ����������� ���������.</div>'.
	'<div class="act-p">'.
		'<p>1. ������� ����� ������ ��� ���������, �� ���������� ����������� (�������� ����������) �� ����������, ��������, ������������� � �������� ����:'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
	'</div>'.
	'<div class="act-p">'.
		'<p>2. ����������� ������ ������ ��� ���������, �� ���������� ����������� (�������� ����������):'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
	'</div>'.
	'<div class="act-p">�� ��������� ___________________________________</div>'.
	'<div class="act-p">�� ���������� /�������� �����������/ ____________________________________</div>'.
	'<div class="act-p">���� _______________</div>'.
	'<div class="cut-line">��������</div>'.
	'<div class="ekz">��������� ��������� �����������</div>'.
	'<div class="act-head">��� �����-������ ������</div>'.
	'<table class="act-tab">'.
		'<tr><td class="label">�� ������:<td class="title">'.$v['adres'].'<td><td>'.
		'<tr><td class="label">�����:<td class="title">'.$v['nomer'].'<td class="label">��������:<td>'.$fioPodpis.
	'</table>'.
	'<div class="time-dost">����� �������� _____________________</div>'.
	'<div class="act-p">'.
		'<p>1. ������� ����� ������ ��� ���������, �� ���������� ����������� (�������� ����������) �� ����������, ��������, ������������� � �������� ����:'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
	'</div>'.
	'<div class="act-p">'.
		'<p>2. ����������� ������ ������ ��� ���������, �� ���������� ����������� (�������� ����������):'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
	'</div>'.
	'<div class="act-p">�� ��������� ___________________________________</div>'.
	'<div class="act-p">�� ���������� /�������� �����������/ ____________________________________</div>'.
	'<div class="act-p">���� _______________</div>'
	);

	if($cash_id) {
		$doc->newPage();
		$doc->addParagraph(cashmemoParagraph($cash_id));
	}

	$doc->output($v['link'], is_numeric($dog_id) ? PATH_DOGOVOR : '');
}//dogovor_print()
function cashmemoParagraph($id) {
	$g = query_assoc("SELECT * FROM `setup_global`");
	$money = query_assoc("SELECT * FROM `money` WHERE !`deleted` AND `id`=".$id);
	$zayav = query_assoc("SELECT * FROM `zayav` WHERE !`deleted` AND `id`=".$money['zayav_id']);
	$dog = query_assoc("SELECT * FROM `zayav_dogovor` WHERE !`deleted` AND `zayav_id`=".$money['zayav_id']);

	return
	'<div class="org-name">�������� � ������������ ���������������� <b>�'.$g['org_name'].'�</b></div>'.
	'<div class="cash-rekvisit">'.
		'��� '.$g['inn'].'<br />'.
		'���� '.$g['ogrn'].'<br />'.
		'��� '.$g['kpp'].'<br />'.
		str_replace("\n", '<br />', $g['yur_adres']).'<br />'.
		'<table><tr>'.
			'<td>���.: '.$g['telefon'].
			'<th>'.FullData($money['dtime_add']).' �.'.
		'</table>'.
	'</div>'.
	'<div class="head">�������� ��� �'.$money['id'].'</div>'.
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
				($money['zayav_id'] ? zayav_product_spisok($money['zayav_id'], 'cash') : '"'.$money['prim'].'"').
			'<td class="count">1.00'.
			'<td class="sum">'.$money['sum'].
			'<td class="summa">'.$money['sum'].
		'</table>'.
	'<div class="summa-propis">'.numberToWord($money['sum'], 1).' ����'._end($money['sum'], '�', '�', '��').'</div>'.
	'<div class="shop-about">(����� ��������)</div>'.
	'<table class="cash-podpis">'.
		'<tr><td>�������� ______________________<div class="prod-bot">(�������)</div>'.
			'<td><u>/'._viewer($money['viewer_id_add'], 'name_init').'/</u><div class="r-bot">(����������� �������)</div>'.
	'</table>';
}//cashmemoParagraph()


/*
function remind_to_global() {//������� ����������� � ������
	$sql = "SELECT * FROM `remind`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		die('end');
	$ids = array();
	while($r = mysql_fetch_assoc($q)) {
		$ids[] = $r['id'];

		$sql = "INSERT INTO `remind` (
				`app_id`,
				`client_id`,
				`zayav_id`,
				`money_cut`,
				`txt`,
				`day`,
				`status`,
				`viewer_id_add`,
				`dtime_add`
			) VALUES (
				".APP_ID.",
				".$r['client_id'].",
				".$r['zayav_id'].",
				".$r['cut'].",
				'".addslashes($r['txt'])."',
				'".$r['day']."',
				".$r['status'].",
				".$r['viewer_id_add'].",
				'".$r['dtime_add']."'
			)";
		query($sql, GLOBAL_MYSQL_CONNECT);

		$insert_id = query_value("SELECT `id` FROM `remind` WHERE `app_id`=".APP_ID." ORDER BY `id` DESC LIMIT 1", GLOBAL_MYSQL_CONNECT);

		$sql = "SELECT * FROM `remind_history` WHERE `remind_id`=".$r['id']." ORDER BY `id`";
		$q2 = query($sql);
		$arr = array();
		while($h = mysql_fetch_assoc($q2)) {
			$arr[] = "(
				".$insert_id.",
				".$h['status'].",
				'".$h['day']."',
				'".addslashes($h['txt'])."',
				".$h['viewer_id_add'].",
				'".$h['dtime_add']."'
			)";
		}
		$sql = "INSERT INTO `remind_history` (
				`remind_id`,
				`status`,
				`day`,
				`txt`,
				`viewer_id_add`,
				`dtime_add`
			) VALUES ".implode(',', $arr);
		query($sql, GLOBAL_MYSQL_CONNECT);
	}
	$sql = "DELETE FROM `remind` WHERE `id` IN (".implode(',', $ids).")";
	query($sql);
	echo 'deleted 500<br />';
}



function histChangeVk() { // ������ ������ � ������� (vk.com)
	$sql = "SELECT * FROM `history` WHERE value like '%zayav-rashod-spisok%' AND `value` LIKE '%vk.com%' limit 500";
	$q = query($sql);
	$txt = '';
	while($r = mysql_fetch_assoc($q)) {
		$ex = explode('href="', $r['value'], 2);
		$ex1 = explode('" target="_blank"', $ex[1], 2);
		$txt .= $ex1[0].'<br />';
		$ex2 = explode('id', $ex1[0]);
		$value = $ex[0].'class="go-report-salary" val="'.$ex2[1].':0:0"'.$ex1[1];
		$sql = "UPDATE `history` SET `value`='".addslashes($value)."' where id=".$r['id'];
		query($sql);
	}
	echo $txt;
}
function histChangeHref() { // ������ ������ � ������� (href)
	$sql = "SELECT * FROM `history` WHERE value like '%zayav-rashod-spisok%' AND `value` LIKE '%href=%' limit 300";
	$q = query($sql);
	$txt = '';
	while($r = mysql_fetch_assoc($q)) {
		$ex = explode('href="', $r['value'], 2);
		$ex1 = explode('">', $ex[1], 2);
		$txt .= $ex1[0].'<br />';
		$worker = explode('&id=', $ex1[0]);
		$mon = 0;
		$acc = 0;
		if(!_num($worker[1])) {
			$mon = explode('&mon=', $worker[1]);
			$acc = explode('&acc_id=', $mon[1]);
			$worker = $mon[0];
			$mon = $acc[0];
			$acc = $acc[1];
		} else
			$worker = $worker[1];
		$txt .= $worker.':'.$mon.':'.$acc.'<br />';
		$value = $ex[0].'class="go-report-salary" val="'.$worker.':'.$mon.':'.$acc.'">'.$ex1[1];
		$sql = "UPDATE `history` SET `value`='".addslashes($value)."' where id=".$r['id'];
		//echo '<textarea style="width:700px;height:500px">'.$sql.'</textarea>'.$value;
		query($sql);
	}
	echo $txt;
}


function zayav_expense_remake() {//�������� � ���� ������ �������� �� ������� ��� �����������
	$sql = "SELECT DISTINCT `worker_id` FROM `zayav_expense` WHERE `worker_id`";
	$q = query($sql);
	while($u = mysql_fetch_assoc($q)) {
		$bonus = _viewerRules($u['worker_id'], 'RULES_BONUS');
		$sql = "SELECT
				`e`.`id`,
				`z`.`status_day`,
				`z`.`dtime_add`
			FROM `zayav_expense` `e`,
				 `zayav` `z`
			WHERE `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			  AND `e`.`worker_id`=".$u['worker_id']."
			  AND `e`.`sum`>0
			GROUP BY `e`.`id`";
		$zq = query($sql);
		while($r = mysql_fetch_assoc($zq)) {
			$mon = $bonus ? substr($r['dtime_add'], 0, 10) : $r['status_day'];
			if($mon == '0000-00-00')
				continue;
			query("UPDATE `zayav_expense`
				   SET `acc`=1,
				       `mon`='".$mon."'
				   WHERE `id`=".$r['id']);
		}
	}
}


function c1() {// ����������� ���� � �������� ������
	$sql = "SELECT `zayav_id`,`dtime_add` FROM `history` WHERE `type`=29 GROUP BY `zayav_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		query("UPDATE `zayav_expense` SET `dtime_add`='".$r['dtime_add']."' WHERE `zayav_id`=".$r['zayav_id']);
}

// ���������� �������� ������
insert into zayav (id,client_id,viewer_id_add,accrual_sum)
	SELECT zayav_id,0,0,IFNULL(SUM(`sum`),0) FROM `accrual` WHERE `deleted`=0 group by zayav_id
on duplicate key update accrual_sum=values(accrual_sum);

insert into zayav (id,client_id,viewer_id_add,oplata_sum)
	SELECT zayav_id,0,0,IFNULL(SUM(`sum`),0) FROM `money` WHERE `deleted`=0 AND `sum`>0 group by zayav_id
on duplicate key update oplata_sum=values(oplata_sum);

insert into zayav (id,client_id,viewer_id_add,expense_sum)
	SELECT zayav_id,0,0,IFNULL(SUM(`sum`),0) FROM `zayav_expense` group by zayav_id
on duplicate key update expense_sum=values(expense_sum);

update zayav set `net_profit`=accrual_sum-expense_sum;

// ���������� ��� ������������� ��������
insert into money (id,confirm_dtime)
	select table_id,dtime_add from invoice_history where action=11
on duplicate key update confirm_dtime=values(confirm_dtime);
*/