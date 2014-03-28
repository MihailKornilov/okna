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
		if(isset($_GET['start'])) {// �������������� ��������� ���������� ��������
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
	xcache_unset(CACHE_PREFIX.'setup_global');
	xcache_unset(CACHE_PREFIX.'product');
	xcache_unset(CACHE_PREFIX.'product_sub');
	xcache_unset(CACHE_PREFIX.'invoice');
	xcache_unset(CACHE_PREFIX.'income');
	xcache_unset(CACHE_PREFIX.'expense');
	xcache_unset(CACHE_PREFIX.'zayavrashod');
	xcache_unset(PIN_TIME_KEY);
	GvaluesCreate();
}//_cacheClear()

function _header() {
	global $html;
	$html =
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
		'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.

		'<head>'.
		'<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
		'<title>Evrookna - ���������� '.API_ID.'</title>'.

		//������������ ������ � ��������
		(SA ? '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/errors.js?'.VERSION.'"></script>' : '').

		//�������� �������
		'<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/jquery-2.0.3.min.js"></script>'.
//		'<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/highstock.js"></script>'.
		'<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/xd_connection'.(DEBUG ? '' : '.min').'.js"></script>'.

		//��������� ���������� �������� �������.
		(SA ? '<script type="text/javascript">var TIME=(new Date()).getTime();</script>' : '').

		'<script type="text/javascript">'.
			(LOCAL ? 'for(var i in VK)if(typeof VK[i]=="function")VK[i]=function(){return false};' : '').
			'var DOMAIN="'.DOMAIN.'",'.
			'VALUES="'.VALUES.'",'.
			'VIEWER_ID='.VIEWER_ID.';'.
		'</script>'.

		//����������� api VK. ����� VK ������ ������ �� �������� ������ �����
		'<link href="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/vk'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
		'<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/vk'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.

		'<link href="'.SITE.'/css/main'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
		'<script type="text/javascript" src="'.SITE.'/js/main'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.
		(@$_GET['p'] == 'setup' ? '<script type="text/javascript" src="'.SITE.'/js/setup'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>' : '').
		'<script type="text/javascript" src="'.SITE.'/js/G_values.js?'.G_VALUES_VERSION.'"></script>'.

		'</head>'.
		'<body>'.
			'<div id="frameBody">'.
				'<iframe id="frameHidden" name="frameHidden"></iframe>';
}//_header()
function _footer() {
	global $html, $sqlQuery, $sqlCount, $sqlTime;
	if(SA)
		$html .=
			'<div id="admin">'.
				'<a class="debug_toggle'.(DEBUG ? ' on' : '').'">�'.(DEBUG ? '�' : '').'������� Debug</a> :: '.
				'<a id="cache_clear">������� ��� ('.VERSION.')</a> :: '.
				'<a href="'.SITE.'/_sxdump" target="_blank">sxd</a> :: '.
				'sql <b>'.$sqlCount.'</b> ('.round($sqlTime, 3).') :: '.
				'php '.round(microtime(true) - TIME, 3).' :: '.
				'js <em></em>'.
			'</div>'
			.(DEBUG ? $sqlQuery : '');
	$html .= '</div></body></html>';
}//_footer()

function GvaluesCreate() {//����������� ����� G_values.js
	$save = //'function _toSpisok(s){var a=[];for(k in s)a.push({uid:k,title:s[k]});return a}'.
		'function _toAss(s){var a=[];for(var n=0;n<s.length;a[s[n].uid]=s[n].title,n++);return a}'.
		'var '.
		"\n".'WORKER_SPISOK='.query_selJson("SELECT `viewer_id`,CONCAT(`first_name`,' ',`last_name`) FROM `vk_user`
											 WHERE `worker`=1
											   AND `viewer_id`!=982006
											 ORDER BY `dtime_add`").','.
		"\n".'PRODUCT_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_product` ORDER BY `name`").','.
		"\n".'PRODUCT_ASS=_toAss(PRODUCT_SPISOK),'.
		"\n".'INVOICE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `invoice` ORDER BY `id`").','.
		"\n".'INCOME_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_income` ORDER BY `sort`").','.
		"\n".'INCOME_CONFIRM='.query_ptpJson("SELECT `id`,`confirm` FROM `setup_income` WHERE `confirm`").','.
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
		"\n".'HISTORY_GROUP='._selJson(history_group()).',';

	$sql = "SELECT * FROM `setup_product_sub` ORDER BY `product_id`,`name`";
	$q = query($sql);
	$sub = array();
	while($r = mysql_fetch_assoc($q)) {
		if(!isset($sub[$r['product_id']]))
			$sub[$r['product_id']] = array();
		$sub[$r['product_id']][] = '{uid:'.$r['id'].',title:"'.$r['name'].'"}';
	}
	$v = array();
	foreach($sub as $n => $sp)
		$v[] = $n.':['.implode(',', $sp).']';
	$save .= "\n".'PRODUCT_SUB_SPISOK={'.implode(',', $v).'}';
		//'PRODUCT_ASS=[],'.
		//'PRODUCT_ASS[0]="";'.
		//'for(var k in G.vendor_spisok){for(var n=0;n<G.vendor_spisok[k].length;n++){var sp=G.vendor_spisok[k][n];G.vendor_ass[sp.uid]=sp.title;}}';
	$fp = fopen(PATH.'js/G_values.js','w+');
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
}//_product()
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
				define('INVOICE_START_'.$id, $r['start']);
			}
			define('INVOICE_0', '');
			define('INVOICE_START_0', 0);
			define('INVOICE_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'start')
		return constant('INVOICE_START_'.$type_id);
	return constant('INVOICE_'.$type_id);
}//_invoice()
function _income($type_id=false, $i='name') {//������ ������� ��� ������
	if(!defined('INCOME_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'income';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT * FROM `setup_income` ORDER BY `sort`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = array(
					'name' => $r['name'],
					'invoice_id' => $r['invoice_id'],
					'confirm' => $r['confirm']
				);
			xcache_set($key, $arr, 86400);
		}
		if(!defined('INCOME_LOADED')) {
			foreach($arr as $id => $r) {
				define('INCOME_'.$id, $r['name']);
				define('INCOME_INVOICE_'.$id, $r['invoice_id']);
				define('INCOME_CONFIRM_'.$id, $r['confirm']);
			}
			define('INCOME_0', '');
			define('INCOME_INVOICE_0', 0);
			define('INCOME_CONFIRM_0', 0);
			define('INCOME_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'invoice')
		return constant('INCOME_INVOICE_'.$type_id);
	if($i == 'confirm')
		return constant('INCOME_CONFIRM_'.$type_id);
	return constant('INCOME_'.$type_id);
}//_income()
function _expense($type_id=false, $i='name') {//������ ������� ��� ������
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
function _zayavRashod($type_id=false, $i='name') {//������ �������� ������
	if(!defined('ZAYAVRASHOD_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'zayavrashod';
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
		if(!defined('ZAYAVRASHOD_LOADED')) {
			foreach($arr as $id => $r) {
				define('ZAYAVRASHOD_'.$id, $r['name']);
				define('ZAYAVRASHOD_TXT_'.$id, $r['txt']);
				define('ZAYAVRASHOD_WORKER_'.$id, $r['worker']);
			}
			define('ZAYAVRASHOD_0', '');
			define('ZAYAVRASHOD_TXT_0', '');
			define('ZAYAVRASHOD_WORKER_0', 0);
			define('ZAYAVRASHOD_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'txt')
		return constant('ZAYAVRASHOD_TXT_'.$type_id);
	if($i == 'worker')
		return constant('ZAYAVRASHOD_WORKER_'.$type_id);
	return constant('ZAYAVRASHOD_'.$type_id);
}//_zayavRashod()

function _mainLinks() {
	global $html;

	$cur = strftime('%Y-%m-%d');
	$cRemind = query_value("SELECT COUNT(*) FROM `remind` WHERE `status`=1 AND `day`<='".$cur."' AND (`private`=0 OR `private`=1 AND `viewer_id_add`=".VIEWER_ID.")");
	$cRemind += query_value("SELECT COUNT(*) FROM `zayav` WHERE !`deleted` AND `zamer_status`=1 AND `zamer_dtime`<='".$cur." 23:59:59'");

	if(VIEWER_ADMIN && $count = query_value("SELECT COUNT(`id`) FROM `invoice_transfer` WHERE !`invoice_to` AND `worker_to` AND !`confirm`"))
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
			'name' => '�����������'.($cRemind ? ' (<b>'.$cRemind.'</b>)' : ''),
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

	$send = '<div id="mainLinks">';
	foreach($links as $l)
		if($l['show'])
			$send .= '<a href="'.URL.'&p='.$l['page'].'"'.($l['page'] == $_GET['p'] ? ' class="sel"' : '').'>'.$l['name'].'</a>';
	$send .= pageHelpIcon().'</div>';

	$html .= $send;
}//_mainLinks()

function _setupRules($rls, $admin=0) {
	$rules = array(
		'RULES_BONUS' => array(	    // ���������� �������: �� ���� �������� ������, �� ���� ���������� ������
			'def' => 0
		),
		'RULES_CASH' => array(	    // ���������� �������� ����
			'def' => 0
		),
		'RULES_GETMONEY' => array(	// ����� ��������� � ���������� ������:
			'def' => 0
		),
		'RULES_NOSALARY' => array(	// �� ���������� � ����������� �/�:
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
				'RULES_INCOME' => array(	// ��������� �����������
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

function _clientLink($arr, $fio=0) {//���������� ����� � ������ ������� � ������ ��� ������� �� id
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
				return $fio ? $r['fio'] : '<a'.($r['deleted'] ? ' class="deleted"' : '').' href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>';
			return '';
		}
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id) {
				$arr[$id]['client_link'] = '<a'.($r['deleted'] ? ' class="deleted" title="������ �����"' : '').' href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>';
				$arr[$id]['client_fio'] = $r['fio'];
				$arr[$id]['client_tel'] = $r['telefon'];
			}
	}
	return $arr;
}//_clientLink()
function clientBalansUpdate($client_id) {//���������� ������� �������
	$prihod = query_value("SELECT SUM(`sum`) FROM `money` WHERE `deleted`=0 AND `zayav_id`>0 AND `client_id`=".$client_id." AND `sum`>0");
	$acc = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE `deleted`=0 AND `client_id`=".$client_id);
	$balans = $prihod - $acc;
	query("UPDATE `client` SET `balans`=".$balans." WHERE `id`=".$client_id);
	return $balans;
}//clientBalansUpdate()

function clientFilter($v) {
	return array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? intval($v['page']) : 1,
		'fast' => !empty($v['fast']) && preg_match(REGEXP_WORDFIND, win1251($v['fast'])) ? win1251(htmlspecialchars(trim($v['fast']))) : '',
		'dolg' => !empty($v['dolg']) && preg_match(REGEXP_BOOL, $v['dolg']) ? intval($v['dolg']) : 0,
		'note' => !empty($v['note']) && preg_match(REGEXP_BOOL, $v['note']) ? intval($v['note']) : 0,
		'zayav_cat' => !empty($v['zayav_cat']) && preg_match(REGEXP_NUMERIC, $v['zayav_cat']) ? intval($v['zayav_cat']) : 0,
		'product_id' => !empty($v['product_id']) && preg_match(REGEXP_NUMERIC, $v['product_id']) ? intval($v['product_id']) : 0
	);
}//clientFilter()
function client_data($filter=array()) {
	$filter = clientFilter($filter);
	$cond = "`deleted`=0";
	$reg = '';
	$regEngRus = '';
	if($filter['fast']) {
		$engRus = _engRusChar($filter['fast']);
		$cond .= " AND (`fio` LIKE '%".$filter['fast']."%'
					 OR `telefon` LIKE '%".$filter['fast']."%'
					 OR `adres` LIKE '%".$filter['fast']."%'
					 ".($engRus ?
						"OR `fio` LIKE '%".$engRus."%'
						OR `telefon` LIKE '%".$engRus."%'
						OR `adres` LIKE '%".$engRus."%'"
				: '')."
					 )";
		$reg = '/('.$filter['fast'].')/i';
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
			        WHERE `z`.`deleted`=0
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
				$sql = "SELECT DISTINCT `client_id` FROM `zayav` WHERE !`deleted` ".$cnd;
				foreach(explode(',', query_ids($sql)) as $id)
					$cids[$id] = $id;
			}
		}
		if($filter['note']) {
			$sql = "SELECT DISTINCT `table_id`
					FROM `vk_comment`
					WHERE `status`=1 AND `table_name`='client'";
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
			'result' => '�������� �� �������.',
			'spisok' => '<div class="_empty">�������� �� �������.</div>'
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
		if(!empty($filter['fast'])) {
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
			WHERE `deleted`=0
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

	$dolg = $filter['dolg'] ? abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE `deleted`=0 AND `balans`<0 LIMIT 1")) : 0;
	$send = array(
		'all' => $all,
		'spisok' => '',
		'result' => '������'._end($all, ' ', '� ').$all.' ������'._end($all, '', '�', '��').
					($dolg ? '<span class="dolg_sum">(����� ����� ����� = <b>'._sumSpace($dolg).'</b> ���.)</span>' : '')
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
function client_list() {
	$data = client_data();
	return
	'<div id="client">'.
		'<div id="find"></div>'.
		'<div class="result">'.$data['result'].'</div>'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate"><a>����� ������</a></div>'.
					'<div class="filter">'.
						_check('dolg', '��������').
						_check('note', '���� �������').
						'<div class="findHead">��������� ������</div>'.
						'<input type="hidden" id="zayav_cat">'.
						'<div class="findHead">������������ �������</div>'.
						'<input type="hidden" id="product_id">'.
					'</div>'.
		'</table>'.
	'</div>';
}//client_list()

function clientInfoGet($client) {
	return
		($client['deleted'] ? '<div class="_info">������ �����</div>' : '').
		'<div class="fio">'.$client['fio'].'</div>'.
		'<table class="cinf">'.
			'<tr><td class="label">�������:<td>'.$client['telefon'].
			'<tr><td class="label">�����:  <td>'.$client['adres'].
			'<tr><td class="label">������: <td><b style=color:#'.($client['balans'] < 0 ? 'A00' : '090').'>'.round($client['balans'], 2).'</b>'.
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

	$sql = "(SELECT `id`
			FROM `zayav`
			WHERE !`deleted`
			  AND `zamer_status`=1
			  AND `client_id`=".$client_id."
		) UNION (
			SELECT `id`
			FROM `remind`
			WHERE `status`=1
			  AND `client_id`=".$client_id."
		)";
	$remindCount = mysql_num_rows(query($sql));

	if(RULES_HISTORYSHOW)
		$histCount = query_value("SELECT COUNT(`id`) FROM `history` WHERE `client_id`=".$client_id);

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
	return
	'<script type="text/javascript">'.
		'var CLIENT={'.
			'id:'.$client_id.','.
			'fio:"'.$client['fio'].'",'.
			'telefon:"'.$client['telefon'].'",'.
			'adres:"'.$client['adres'].'",'.
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
						'<a class="remind-add">������ �����������</a>'.
						'<a class="cdel">������� �������</a>'
 : '').
					'</div>'.
		'</table>'.

		'<div id="dopLinks">'.
			'<a class="link sel" val="zayav">������'.($zayavCount ? ' ('.$zayavCount.')' : '').'</a>'.
			'<a class="link" val="money">�������'.($money['all'] ? ' ('.$money['all'].')' : '').'</a>'.
			'<a class="link" val="remind">�����������'.($remindCount ? ' ('.$remindCount.')' : '').'</a>'.
			'<a class="link" val="comm">�������'.($commCount ? ' ('.$commCount.')' : '').'</a>'.
			(RULES_HISTORYSHOW ? '<a class="link" val="hist">�������'.($histCount ? ' ('.$histCount.')' : '').'</a>' : '').
		'</div>'.

		'<table class="tabLR">'.
			'<tr><td class="left">'.
					'<div id="zayav_spisok">'.($zayavSpisok ? $zayavSpisok : '<div class="_empty">������ ���</div>').'</div>'.
					'<div id="income_spisok">'.$money['spisok'].'</div>'.
					'<div class="remind_spisok">'.remind_spisok(array('client_id'=>$client_id)).'</div>'.
					'<div id="comments">'._vkComment('client', $client_id).'</div>'.
					(RULES_HISTORYSHOW ? '<div id="histories">'.history_spisok(array('client_id'=>$client_id)).'</div>' : '').
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
		}
	return $arr;
}//_zayavLink()
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
		  ($z['nomer_d'] ? ' �'.$z['nomer_d'] : ' #'.$z['id']));
	if(!$z['dogovor_id'] && $z['dogovor_require'])
		$send = array(
			'type' => 'dog',
			'head' => '������� �� �������� <span class="zayav-dog">('.($z['set_status'] ? '���������' : ($z['zakaz_status'] ? '�����' : '�����')).$dop.')</span>',
			'status_id' => $z['zamer_status'],
			'status_name' => $z['zamer_status'] ? _zamerStatus($z['zamer_status']) : ''
		);
	elseif($z['zakaz_status'])
		$send = array(
			'type' => 'zakaz',
			'head' => '�����'.$dop,
			'status_id' => $z['zakaz_status'],
			'status_name' => _zakazStatus($z['zakaz_status'])
		);
	elseif($z['zamer_status'] == 1 || $z['zamer_status'] == 3)
		$send = array(
			'type' => 'zamer',
			'head' => '�����'.$dop,
			'status_id' => $z['zamer_status'],
			'status_name' => _zamerStatus($z['zamer_status'])
		);
	elseif($z['set_status'])
		$send = array(
			'type' => 'set',
			'head' => '���������'.$dop,
			'status_id' => $z['set_status'],
			'status_name' => _setStatus($z['set_status'])
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
	$accrual_sum = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `accrual` WHERE `deleted`=0 AND `zayav_id`=".$zayav_id);
	$oplata_sum = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `money` WHERE `deleted`=0 AND `sum`>0 AND `zayav_id`=".$zayav_id);
	$expense_sum = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `zayav_expense` WHERE `zayav_id`=".$zayav_id);
	$sql = "UPDATE `zayav`
			SET `accrual_sum`=".$accrual_sum.",
				`oplata_sum`=".$oplata_sum.",
				`expense_sum`=".$expense_sum.",
				`net_profit`=".($accrual_sum - $expense_sum)."
			WHERE `id`=".$zayav_id;
	query($sql);
	return true;
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

function zayav_rashod_test($rashod) {// �������� ������������ ������ �������� ������ ��� �������� � ����
	if(empty($rashod))
		return array();
	$send = array();
	$ex = explode(',', $rashod);
	foreach($ex as $r) {
		$ids = explode(':', $r);
		if(!preg_match(REGEXP_NUMERIC, $ids[0]) || !$ids[0])
			return false;
		if(_zayavRashod($ids[0], 'worker') && !preg_match(REGEXP_NUMERIC, $ids[1]))
			return false;
		if(!preg_match(REGEXP_NUMERIC, $ids[2]) || !$ids[2])
			return false;
		if(_zayavRashod($ids[0], 'txt'))
			$ids[1] = win1251(htmlspecialchars(trim($ids[1])));
		if(!_zayavRashod($ids[0], 'txt') && !_zayavRashod($ids[0], 'worker'))
			$ids[1] = '';
		$send[] = $ids;
	}
	return $send;
}//zayav_rashod_test()
function zayav_rashod_spisok($zayav_id, $type='html') {//��������� ������ �������� ������
	$sql = "SELECT * FROM `zayav_expense` WHERE `zayav_id`=".$zayav_id." ORDER BY `id`";
	$q = query($sql);
	$arr = array();
	while($r = mysql_fetch_assoc($q))
		$arr[] = $r;
	$send = '<table class="zayav-rashod-spisok">';
	$json = array();
	$array = array();
	foreach($arr as $r) {
		$send .= '<tr><td class="name">'._zayavRashod($r['category_id']).
					 '<td>'.(_zayavRashod($r['category_id'], 'txt') ? $r['txt'] : '').
							(_zayavRashod($r['category_id'], 'worker') && $r['worker_id'] ? _viewer($r['worker_id'], 'name') : '').
					 '<td class="sum">'.$r['sum'].' �.';
		$json[] = '['.
					$r['category_id'].',"'.
					(_zayavRashod($r['category_id'], 'txt') ? $r['txt'] : '').
					(_zayavRashod($r['category_id'], 'worker') ? $r['worker_id'] : '').'",'.
					$r['sum'].
				  ']';
		$array[] = array(
					$r['category_id'],
					(_zayavRashod($r['category_id'], 'txt') ? $r['txt'] : '').
					(_zayavRashod($r['category_id'], 'worker') ? $r['worker_id'] : ''),
					$r['sum']);
	}
	if(!empty($arr)) {
		$z = query_assoc("SELECT * FROM `zayav` WHERE `id`=".$zayav_id." LIMIT 1");
		$send .= '<tr><td colspan="2" class="itog">����:<td class="sum"><b>'.$z['expense_sum'].'</b> �.'.
				 '<tr><td colspan="2" class="itog">�������:<td class="sum">'.$z['net_profit'].' �.';
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
}//zayav_rashod_spisok()

function zayav() {
	if(empty($_GET['d']))
		$_GET['d'] = empty($_COOKIE['zayav_dop']) ? 'zakaz' : $_COOKIE['zayav_dop'];
	setcookie('zayav_dop', $_GET['d'] , time() + 846000, "/");
	switch($_GET['d']) {
		default:
		case 'zakaz':
			$right =
				'<div id="buttonCreate" class="zakaz_add"><a>����� �����</a></div>';
			$data = zayav_spisok('zakaz');
			$status = '<div class="findHead">������ ������</div>'.
					  _rightLink('status', _zayavStatusName());
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
					'</div>'.
		'</table>'.
	'</div>';
}//zayav()
function zayavFilter($v) {
	return array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? intval($v['page']) : 1,
		'client' => !empty($v['client']) && preg_match(REGEXP_NUMERIC, $v['client']) ? intval($v['client']) : 0,
		'product' => !empty($v['product']) && preg_match(REGEXP_NUMERIC, $v['product']) ? intval($v['product']) : 0,
		'status' => !empty($v['status']) && preg_match(REGEXP_NUMERIC, $v['status']) ? intval($v['status']) : 0
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

	$zayav = _clientLink($zayav);
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

	$cond = "`deleted`=0 AND (".$cond.")";

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

	$zayav = _clientLink($zayav);
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
		if($r['dogovor_id']) {
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

	$sql = "SELECT * FROM `client` WHERE `deleted`=0 AND `id`=".$z['client_id']." LIMIT 1";
	$client = mysql_fetch_assoc(query($sql));

	$dog = $z['dogovor_id'] ? query_assoc("SELECT * FROM `zayav_dogovor` WHERE `id`=".$z['dogovor_id']) : array();
	$dogSpisok = $z['dogovor_id'] ? zayavDogovorList($z['id']).'<input type="hidden" id="dogovor_reaction" />' : '<input type="hidden" id="dogovor_action" />';

	$d = explode(' ', $z['zamer_dtime']);
	$time = explode(':', $d[1]);

	$accSum = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE !`deleted` AND `zayav_id`=".$zayav_id);
	$rashod = zayav_rashod_spisok($z['id'], 'all');

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

			'nomer_vg:"'.$z['nomer_vg'].'",'.
			'nomer_g:"'.$z['nomer_g'].'",'.
			'nomer_d:"'.$z['nomer_d'].'",'.

			'day:"'.$d[0].'",'.
			'hour:'.intval($time[0]).','.
			'min:'.intval($time[1]).','.
			'dur:'.$z['zamer_duration'].
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
			'<a class="delete">������� ������</a>'
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
					'</div>'.
					'<table class="tabInfo">'.
						'<tr><td class="label">������:<td>'._clientLink($z['client_id']).
						'<tr><td class="label top">�������:<td>'.zayav_product_spisok($z['id']).$z['zakaz_txt'].
			   (ZAMER ? '<tr><td class="label">����� ������:<td><b>'.$z['adres'].'</b>'.
						'<tr><td class="label">���� ������:'.
							'<td><span class="zamer-dtime" title="'._zamerDuration($z['zamer_duration']).'">'.
									FullDataTime($z['zamer_dtime']).
								'</span>'.
								($z['zamer_status'] == 1 ? '<span class="zamer-left">'.remindDayLeft(1, $z['zamer_dtime']).'</span>' : '').
								'<a class="zamer_table" val="'.$z['id'].'">������� �������</a>'
		       : '').

((DOG || SET) && $z['adres'] ?
						'<tr><td class="label">����� ���������:<td><b>'.$z['adres'].'</b>'
: '').

(ZAKAZ || SET ?
						'<tr><td class="label">�������:<td>'.$dogSpisok.
	  ($z['nomer_vg'] ? '<tr><td class="label top">����� ��:<td>'._attach('vg', $z['id'], '���������� ��������', $z['nomer_vg']) : '').
	   ($z['nomer_g'] ? '<tr><td class="label top">����� �:<td>'._attach('g', $z['id'], '���������� ��������', $z['nomer_g']) : '').
	   ($z['nomer_d'] ? '<tr><td class="label top">����� �:<td>'._attach('d', $z['id'], '���������� ��������', $z['nomer_d']) : '').
						'<tr><td class="label top">�����:<td>'._attach('files', $z['id'], '���������')
: '').
					(_zayavCategory($z, 'status_name') ?
						'<tr><td class="label">������'.($type == 'dog' ? ' ������' : '').':'.
							'<td><div style="background-color:#'._statusColor($z[($type == 'dog' ? 'zamer' : $type).'_status']).'" class="status '.$type.'_status">'.
									_zayavCategory($z, 'status_name').
									(_zayavCategory($z, 'status_id') == 2  && !DOG ? ' '.FullData($z['status_day'], 1) : '').
								'</div>'
					: '').
					'</table>'.
	(ZAKAZ || SET ?
				'<TD class="mainright">'.
					'<div class="headBlue">������� �� ������<a class="add rashod-edit">��������</a></div>'.
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


			'<div class="headBlue">�����������<a class="add remind-add">����� �����������</a></div>'.
			'<div class="remind_spisok">'.remind_spisok(array('zayav_id'=>$zayav_id)).'</div>'.

	(!DOG ?	'<div class="headBlue mon">���������� � �������'.
		(!$z['deleted'] ? '<a class="add income-add">������ �����</a>'.
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
				'<div class="headName">'._zayavCategory($z, 'head').'<div class="zid">#'.$z['id'].'</div>'.
			'</div>'.
			history_spisok(array('zayav_id'=>$z['id'])).'</div>'
		: '').
	'</div>';
}//zayav_info()
function zayav_money($zayav_id) {
	$sql = "SELECT *
	        FROM `money`
	        WHERE `deleted`=0
	          AND `sum`>0
	          AND `zayav_id`=".$zayav_id;
	$q = query($sql);
	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']] = $r;

	$money = array();
	foreach(_dogNomer($spisok) as $r)
		$money[strtotime($r['dtime_add']).$r['id']] = income_unit($r, array('zayav_id'=>$zayav_id));

	$sql = "SELECT *
	        FROM `accrual`
	        WHERE `deleted`=0
	          AND `sum`>0
	          AND `zayav_id`=".$zayav_id;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$money[strtotime($r['dtime_add']).$r['id']] =
			'<tr val="'.$r['id'].'">'.
				'<td class="sum acc'._tooltip('����������', -5).'<b>'._sumSpace($r['sum']).'</b>'.
				'<td>'.$r['prim'].
				'<td class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -40).FullDataTime($r['dtime_add']).
				'<td class="ed" align="right">'.
					(!$r['dogovor_id'] ? '<div class="img_del accrual-del'._tooltip('������� ����������', -116, 'r').'</div>' : '');

	if(empty($money))
		return '';
	ksort($money);
	return '<table class="_spisok _money">'.implode('', $money).'</table>';
}//zayav_money()
function _attach($type, $zayav_id, $name='�����...', $num='') {
	return
	'<div class="_attach">'.
		'<table><tr>'.
			($num ? '<td class="num">'.$num : '').
			'<td><div class="files">'._attach_files($type, $zayav_id).'</div>'.
				'<div class="form">'.
					'<form method="post" action="'.SITE.'/ajax/main.php?'.VALUES.'" enctype="multipart/form-data" target="'.$type.$zayav_id.'_frame">'.
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

	require_once VKPATH.'word/PHPWord.php';

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
	require_once(VKPATH.'clsMsDocGenerator.php');

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
		'� ���� ��������� �� ��������, ��������� ���� �������������, ����������� �� ��������� ������������, '.
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
		'<tr><td>��������� ________________ ��������� �.�.'.
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
	$money = query_assoc("SELECT * FROM `money` WHERE `deleted`=0 AND `id`=".$id);
	$zayav = query_assoc("SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$money['zayav_id']);
	$dog = query_assoc("SELECT * FROM `zayav_dogovor` WHERE `deleted`=0 AND `zayav_id`=".$money['zayav_id']);

	return
	'<div class="org-name">�������� � ������������ ���������������� <b>�'.$g['org_name'].'�</b></div>'.
	'<div class="cash-rekvisit">'.
		'��� '.$g['inn'].'<br />'.
		'���� '.$g['ogrn'].'<br />'.
		'��� '.$g['kpp'].'<br />'.
		str_replace("\n", '<br />', $g['yur_adres']).'<br />'.
		'���.: '.$g['telefon'].
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
			'<td><u>/��������� �.�./</u><div class="r-bot">(����������� �������)</div>'.
	'</table>';
}//cashmemoParagraph()




// ---===! remind !===--- ������ �����������

function remindDayLeft($status, $d) {
	if($status == 2)
		return '���������';
	if($status == 0)
		return '��������';
	$dayLeft = floor((strtotime($d) - TODAY_UNIXTIME) / 3600 / 24);
	if($dayLeft < 0)
		return '���������'._end($dayLeft * -1, ' ', '� ').($dayLeft * -1)._end($dayLeft * -1, ' ����', ' ���', ' ����');
	if($dayLeft > 2)
		return '�����'._end($dayLeft, '�� ', '��� ').$dayLeft._end($dayLeft, ' ����', ' ���', ' ����').
			   '<span class="oday">('.FullData($d, 1).')</span>';
	switch($dayLeft) {
		default:
		case 0: return '��������� �������';
		case 1: return '��������� ������';
		case 2: return '��������� �����������';
	}
}//remindDayLeft()
function remindDayLeftBg($status, $d) {
	if($status == 2)
		return '9f9';
	if($status == 0)
		return 'ddd';
	$dayLeft = floor((strtotime($d) - TODAY_UNIXTIME) / 3600 / 24);
	if($dayLeft < 0)
		return 'faa';
	if($dayLeft == 0)
		return 'ffa';
	return 'ddf';
}
function remind_days() {
	$sql = "(SELECT DATE_FORMAT(`zamer_dtime`,'%Y-%m-%d') AS `day`
				FROM `zayav`
				WHERE !`deleted`
				  AND `zamer_status`=1
				GROUP BY DATE_FORMAT(`zamer_dtime`,'%d')
			) UNION (
				SELECT `day`
				FROM `remind`
				WHERE `status`=1
				  AND (`private`=0 OR `private`=1 AND `viewer_id_add`=".VIEWER_ID.")
				GROUP BY `day`
			)";
	$q = query($sql);
	$days = array();
	while($r = mysql_fetch_assoc($q))
		$days[$r['day']] = 1;
	return $days;
}//remind_days()
function remind() {
	$curMon = abs(strftime('%m'));

	$fullCalendar = '<table class="ftab">';
	$qw = 1;
	$data = array(
		'days' => remind_days(),
		'noweek' => 1,
		'norewind' => 1,
		'func' => 'remind_days',

	);
	for($n = 1; $n <= 12; $n++) {
		if($qw == 1)
			$fullCalendar .= '<tr>';
		$data['month'] = '2014-'.($n < 10 ? 0 : '').$n;
		$fullCalendar .= '<td class="ftd'.($n == $curMon ? ' fcur' : '').'">'._calendarFilter($data);
		$qw++;
		if($qw > 3)
			$qw = 1;
	}
	$fullCalendar .= '</table>';

	unset($data['month']);
	unset($data['norewind']);

	$status = array(
		1 => '��������',
		2 => '���������',
		0 => '��������'
	);

	return
	'<div id="remind">'.
		'<table class="tabLR">'.
			'<tr><td class="left remind_spisok">'.remind_spisok().
				'<td class="right">'.
					'<div id="buttonCreate" class="remind-add"><a>����� �����������</a></div>'.
					_calendarFilter($data).
					'<a class="goyear">��������� �� ���</a>'.
					'<div class="findHead">������</div>'.
					_radio('status', $status, 1, 1).
		'</table>'.
		'<div class="full"><div class="fhead">��������� �����������: 2014 </div>'.$fullCalendar.'</div>'.
	'</div>';
}//remind()
function remindFilter($v) {
	return array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? intval($v['page']) : 1,
		'client_id' => !empty($v['client_id']) && preg_match(REGEXP_NUMERIC, $v['client_id']) ? intval($v['client_id']) : 0,
		'zayav_id' => !empty($v['zayav_id']) && preg_match(REGEXP_NUMERIC, $v['zayav_id']) ? intval($v['zayav_id']) : 0,
		'day' => !empty($v['day']) ? $v['day'] : '',
		'private' => !empty($v['private']) && preg_match(REGEXP_BOOL, $v['private']) ? intval($v['private']) : 0,
		'status' => isset($v['status']) && preg_match(REGEXP_NUMERIC, $v['status']) ? intval($v['status']) : 1
	);
}//remindFilter()
function remind_spisok($v=array()) {
	$filter = remindFilter($v);
	$sql = "(SELECT
				0 AS `cut`,
				0 AS `private`,
				'zamer_status' AS `action`,
				`id`,
				`client_id`,
				`id` AS `zayav_id`,
				`zamer_duration`,
				`zamer_dtime`,
				CONCAT('������ �� ����� �',`id`) AS `txt`,
				DATE_FORMAT(`zamer_dtime`,'%Y-%m-%d') AS `day`,
				1 AS `status`
			FROM `zayav`
			WHERE !`deleted`
			  AND `zamer_status`=1
			  ".($filter['day'] ? "AND `zamer_dtime` LIKE '".$filter['day']."%'" : '')."
			  ".($filter['client_id'] ? "AND `client_id`=".$filter['client_id'] : '')."
			  ".($filter['zayav_id'] ? "AND `id`=".$filter['zayav_id'] : '')."
			  ".($filter['status'] != 1 ? "AND !`id`" : '')."
		) UNION (
			SELECT
				`cut`,
				`private`,
				'remind_status' AS `action`,
				`id`,
				`client_id`,
				`zayav_id`,
				'' AS `zamer_duration`,
				'' AS `zamer_dtime`,
				`txt`,
				`day`,
				`status`
			FROM `remind`
			WHERE `status`=".$filter['status']."
			".($filter['day'] ? "AND `day` LIKE '".$filter['day']."%'" : '')."
			".($filter['client_id'] ? "AND `client_id`=".$filter['client_id'] : '')."
			".($filter['zayav_id'] ? "AND `zayav_id`=".$filter['zayav_id'] : '')."
			  AND (`private`=0 OR `private`=1 AND `viewer_id_add`=".VIEWER_ID.")
		)
		ORDER BY `day`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '����������� ���.';
	$remind = array();
	$zayav = array();
	while($r = mysql_fetch_assoc($q)) {
		$remind[$r['id']] = $r;
		$zayav[$r['zayav_id']] = $r['zayav_id'];
	}
	if(!empty($zayav)) {
		$sql = "SELECT * FROM `zayav` WHERE `id` IN (".implode(',', array_keys($zayav)).")";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$zayav[$r['id']] = $r;
		$zayav = _dogNomer($zayav);
		$zayav = _clientLink($zayav);
	}

	$send = '';
	foreach($remind as $r) {
		if($filter['zayav_id'] && $r['action'] == 'zamer_status')
			continue;
		$z = $zayav[$r['zayav_id']];
		$send .=
		'<div class="remind_unit" id="ru'.$r['id'].'">'.
			'<div class="head" style="background-color:#'.remindDayLeftBg($r['status'], $r['day']).'">'.
				($r['private'] ? '<span class="private">������:</span> ' : '').
				($r['zayav_id'] && !$filter['zayav_id'] ? '<a href="'.URL.'&p=zayav&d=info&id='.$r['zayav_id'].'">'._zayavCategory($z, 'head').'</a>: ' : '').
				($r['cut'] ? '����� <b>'.$r['txt'].'</b> ���. ���.'.$z['dogovor_n'].'. ' : '').
				(!$r['cut'] ? '<b>'.$r['txt'].'</b>' : '').
			'</div>'.
			'<table class="to">'.
		($r['action'] == 'zamer_status' ?
				'<tr><td class="label">����:'.
					'<td>'.FullDataTime($r['zamer_dtime']).
						'<span class="dur">'._zamerDuration($r['zamer_duration']).'</span>'
		: '').
				($z['client_id'] ? '<tr><td class="label">������:<td>'.$z['client_link'].($z['client_tel'] ? ', '.$z['client_tel'] : '') : '').
			'</table>'.
			'<div class="day_left">'.
				remindDayLeft($r['status'], $r['day']).
				'<a class="remind_history" val="'.$r['id'].'">�������</a>'.
				($filter['status'] == 1 ? '<tt> :: </tt><a class="action '.$r['action'].'" val="'.$r['id'].'">��������</a>' : '').
			'</div>'.
			'<div class="hist"></div>'.
		'</div>';
	}

	return $send;
}//remind_spisok()
function remind_history_add($v) {
	$v = array(
		'remind_id' => $v['remind_id'],
		'status' => isset($v['status']) ? $v['status'] : 1,
		'day' => !empty($v['day']) ? $v['day'] : '0000-00-00',
		'txt' => !empty($v['txt']) ? win1251(htmlspecialchars(trim($v['txt']))) : ''
	);
	$sql = "INSERT INTO `remind_history` (
				`remind_id`,
				`status`,
				`day`,
				`txt`,
				`viewer_id_add`
			) VALUES (
				".$v['remind_id'].",
				".$v['status'].",
				'".$v['day']."',
				'".addslashes($v['txt'])."',
				".VIEWER_ID."
			)";
	query($sql);
}//remind_history_add()
function remind_history($remind_id) {
	$sql = "SELECT * FROM `remind_history` WHERE `remind_id`=".$remind_id." ORDER BY `id` DESC";
	$q = query($sql);
	$count = mysql_num_rows($q);
	if(!$count)
		return '������� ���.';
	$send = '<table>';
	while($r = mysql_fetch_assoc($q)) {
		$about = '';
		$count--;
		if($r['status'] == 1 && !$count)
			$about = '�������� �����������. ����: '.FullData($r['day']).'.';
		else
			switch($r['status']) {
				case 1:
					$about = '������ ����� ����: '.FullData($r['day']).'.'.
						($r['txt'] ? '<br />�������: '.$r['txt'].'.' : '');
					break;
				case 2: $about = '����������� ���������.'; break;
				case 0: $about = '����������� ��������.'; break;
			}
		$send .=
			'<tr><td>'.FullDataTime($r['dtime_add'], 1).
				'<td>'._viewer($r['viewer_id_add'], 'name').
				'<td>'.$about;
	}
	$send .= '</table>';
	return $send;
}//remind_history()


// ---===! report !===--- ������ �������

function report() {
	$def = 'history';
	$pages = array(
		'history' => '������� ��������',
		'money' => '������'.(TRANSFER_CONFIRM ? ' (<b>'.TRANSFER_CONFIRM.'</b>)' : ''),
		'month' => '������ ����� �� �������',
		'salary' => '�������� �����������'
	);

	if(!RULES_HISTORYSHOW)
		unset($pages['history']);

	$d = empty($_GET['d']) ? $def : $_GET['d'];
	if(empty($_GET['d']) && !empty($pages) && empty($pages[$d]))
		foreach($pages as $p => $name) {
			$d = $p;
			break;
		}

	$d1 = '';
	$right = '';
	switch($d) {
		default: $d = $def;
		case 'history':
			if(RULES_HISTORYSHOW) {
				$left = history_spisok();
				$right = history_right();
			} else
				_norules();
			break;
		case 'money':
			$d1 = empty($_GET['d1']) ? 'income' : $_GET['d1'];
			switch($d1) {
				default: $d1 = 'income';
				case 'income':
					switch(@$_GET['d2']) {
						case 'all': $left = income_all(); break;
						case 'year':
							if(empty($_GET['year']) || !preg_match(REGEXP_YEAR, $_GET['year'])) {
								$left = '������ ������������ ���.';
								break;
							}
							$left = income_year(intval($_GET['year']));
							break;
						case 'month':
							if(empty($_GET['mon']) || !preg_match(REGEXP_YEARMONTH, $_GET['mon'])) {
								$left = '������ ������������ �����.';
								break;
							}
							$left = income_month($_GET['mon']);
							break;
						default:
							if(!_calendarDataCheck(@$_GET['day']))
								$_GET['day'] = strftime('%Y-%m-%d', time());
							$left = income_day($_GET['day']);
							$right = income_right($_GET['day']);
					}
					break;
				case 'expense':
					$left = expense();
					$right = expense_right();
					break;
				case 'invoice': $left = invoice(); break;
			}
			$left =
				'<div id="dopLinks">'.
					'<a class="link'.($d1 == 'income' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=income">�������</a>'.
					'<a class="link'.($d1 == 'expense' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=expense">�������</a>'.
					'<a class="link'.($d1 == 'invoice' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=invoice">�����'.(TRANSFER_CONFIRM ? ' (<b>'.TRANSFER_CONFIRM.'</b>)' : '').'</a>'.
				'</div>'.
				$left;
			break;
		case 'month': $left = report_month(); break;
		case 'salary':
			if(!empty($_GET['id']) && preg_match(REGEXP_NUMERIC, $_GET['id'])) {
				$worker_id = intval($_GET['id']);
				$left = salary_worker($worker_id);
				$right = '<input type="hidden" id="year" />'.
						 '<div id="monthList">'.salary_monthList($worker_id, strftime('%Y'), strftime('%m')).'</div>';
			} else
				$left = salary();
			break;
	}

	$links = '';
	if($pages)
		foreach($pages as $p => $name)
			$links .= '<a href="'.URL.'&p=report&d='.$p.'"'.($d == $p ? ' class="sel"' : '').'>'.$name.'</a>';

	return
	'<table class="tabLR '.($d1 ? $d1 : $d).'" id="report">'.
		'<tr><td class="left">'.$left.
			'<td class="right">'.
				'<div class="rightLink">'.$links.'</div>'.
				$right.
	'</table>';
}//report()

function history_insert($v) {
	$sql = "INSERT INTO `history` (
			   `type`,
			   `value`,
			   `value1`,
			   `value2`,
			   `value3`,
			   `client_id`,
			   `zayav_id`,
			   `dogovor_id`,
			   `viewer_id_add`
			) VALUES (
				".$v['type'].",
				'".(isset($v['value']) ? $v['value'] : '')."',
				'".(isset($v['value1']) ? $v['value1'] : '')."',
				'".(isset($v['value2']) ? $v['value2'] : '')."',
				'".(isset($v['value3']) ? $v['value3'] : '')."',
				".(isset($v['client_id']) ? $v['client_id'] : 0).",
				".(isset($v['zayav_id']) ? $v['zayav_id'] : 0).",
				".(isset($v['dogovor_id']) ? $v['dogovor_id'] : 0).",
				".VIEWER_ID."
			)";
	query($sql);
}//history_insert()
function history_group() {
	return array(
		1 => '�������',
		2 => '������',
		3 => '��������',
		4 => '�����',
		5 => '������',
		6 => '������� �����������',
		7 => '���������'
	);
}//history_group()
function history_group_ids($v) {
	$ids = array(
		1 => '1,2,3',
		2 => '4,5,6,7,8,9,15,16,17,18,21,22,23,24,25,26,29,30,31',
		3 => '19,20,42',
		4 => '27,28',
		5 => '10,11,12,20,36,37,38,39,40,41',
		6 => '32,33,34,35,37',
		7 => '13,14,501,502,503,504,505,506,507,508,509,510,511,512,513,514,515,516,517,518,519,520'
	);
	return $ids[$v];
}//history_group_ids()
function history_types($v) {
	switch($v['type']) {
		case 1: return '�������� ������ ������� '.$v['client_link'].'.';
		case 2: return '��������� ������ ������� '.$v['client_link'].':<div class="changes">'.$v['value'].'</div>';
		case 3: return '�������� ������� '.$v['client_link'].'.';

		case 4: return '�������� ����� ������  <em>(�����)</em> '.$v['zayav_link'].' ��� ������� '.$v['client_link'].'.';
		case 5: return '��������� ������ ������ <em>(�����)</em> '.$v['zayav_link'].':<div class="changes">'.$v['value1'].'</div>';
		case 6: return '�������� ������ '.$v['zayav_link'].' � ������� '.$v['client_link'].'.';

		case 7: return '���������� �� ����� <b>'.$v['value'].'</b> ���.'.
						($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
						' �� ������ '.$v['zayav_link'].'.';
		case 8: return '�������� ���������� �� ����� <b>'.round($v['value'], 2).'</b> ���.'.
						($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
						' � ������ '.$v['zayav_link'].'.';
		case 9: return '�������������� ���������� �� ����� <b>'.round($v['value'], 2).'</b> ���.'.
						($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
						' � ������ '.$v['zayav_link'].'.';

		case 10: return
			'����� <span class="oplata">'._income($v['value2']).'</span> '.
			'�� ����� <b>'.$v['value'].'</b> ���.'.
			($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' �� ������ '.$v['zayav_link'] : '').
			($v['dogovor_id'] ? '. ��������� ����� �� �������� '.$v['dogovor_nomer'] : '').
			'.';
		case 11: return
			'�������� ������� <span class="oplata">'._income($v['value2']).'</span> '.
			'�� ����� <b>'.round($v['value'], 2).'</b> ���.'.
			($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' � ������ '.$v['zayav_link'] : '').
			'.';
		case 12: return
			'�������������� ������� <span class="oplata">'._income($v['value2']).'</span> '.
			'�� ����� <b>'.round($v['value'], 2).'</b> ���.'.
			($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' � ������ '.$v['zayav_link'] : '').
			'.';

		case 13: return '� ����������: ���������� ������ ���������� <u>'._viewer($v['value'], 'name').'</u>.';
		case 14: return '� ����������: �������� ���������� <u>'._viewer($v['value'], 'name').'</u>.';

		case 15: return '��������� ���������� � ���� ��� ����������������� ������ '.$v['zayav_link'].':<div class="changes">'.$v['value1'].'</div>';
		case 16: return '����� '.$v['zayav_link'].' �������� � ��������� �� ���������� ��������.';
		case 17: return '����� '.$v['zayav_link'].' ������.';
		case 18: return '����� '.$v['zayav_link'].' ������������.';
		case 19: return
			($v['value'] ? '����' : '�').'��������� �������� '.$v['dogovor_nomer'].
			' �� '.$v['dogovor_data'].
			' �� ����� <b>'.$v['dogovor_sum'].'</b> ���.'.
			' ��� ������ '.$v['zayav_link'].'.'.
			($v['value'] ? ' <em>(�������: '.$v['value'].'.)</em>' : '');
		case 20: return
			'�������� ���������� ������� ��  �� ����� <b>'.$v['dogovor_avans'].'</b> ���.'.
			' ��� ������ '.$v['zayav_link'].
			' ��� ���������� �������� '.$v['dogovor_nomer'].'.';

		case 21: return '�������� ����� ������ '.$v['zayav_link'].' <em>(���������)</em> ��� ������� '.$v['client_link'].'.';
		case 22: return '��������� ������ ������ '.$v['zayav_link'].' <em>(���������)</em>:<div class="changes">'.$v['value'].'</div>';

		case 23: return '�������� ����� ������ '.$v['zayav_link'].' <em>(�����)</em> ��� ������� '.$v['client_link'].'.';
		case 24: return '��������� ������ ������ '.$v['zayav_link'].' <em>(�����)</em>:<div class="changes">'.$v['value1'].'</div>';
		case 25: return '��������� ������� ������ '.$v['zayav_link'].' <em>(�����)</em>:<br />'.
						'<span style="background-color:#'._statusColor($v['value']).'" class="zstatus">'._zakazStatus($v['value']).'</span>'.
						' � '.
						'<span style="background-color:#'._statusColor($v['value1']).'" class="zstatus">'._zakazStatus($v['value1']).'</span>.'.
						($v['value2'] ? ' ���� ����������: <u>'.FullData($v['value2']).'</u>.' : '');
		case 26: return '��������� ������� ������ '.$v['zayav_link'].' <em>(���������)</em>:<br />'.
						'<span style="background-color:#'._statusColor($v['value']).'" class="zstatus">'._setStatus($v['value']).'</span>'.
						' � '.
						'<span style="background-color:#'._statusColor($v['value1']).'" class="zstatus">'._setStatus($v['value1']).'</span>'.
						($v['value2'] ? ' ���� ����������: <u>'.FullData($v['value2']).'</u>.' : '');

		case 27: return '�������� ����� '.$v['value'].' ��� ������ '.$v['zayav_link'].'.';
		case 28: return '�������� ����� '.$v['value'].' � ������ '.$v['zayav_link'].'.';

		case 29: return '��������� �������� �� ������ '.$v['zayav_link'].':<div class="changes">'.$v['value'].'</div>';
		case 30: return '������ '.$v['zayav_link'].' ���������� �� <u>�������</u> � <u>���������</u>. ������ ����� "'.$v['value'].'"';

		case 31: return '������� ����� ���� ���������� ������ '.$v['zayav_link'].': <u>'.FullData($v['value']).'</u>.';

		case 32: return '�������� ������� �����������: '.
			($v['value1'] ? '<span class="oplata">'._expense($v['value1']).'</span> ' : '').
			($v['value2'] ? '<em>('.$v['value2'].')</em> ' : '').
			($v['value3'] ? '<u>'._viewer($v['value3'], 'name').'</u> ' : '').
			'�� ����� <b>'.$v['value'].'</b> ���.';
		case 33: return '�������� ������� �����������: '.
			($v['value1'] ? '<span class="oplata">'._expense($v['value1']).'</span> ' : '').
			($v['value2'] ? '<em>('.$v['value2'].')</em> ' : '').
			($v['value3'] ? '��� ���������� <u>'._viewer($v['value3'], 'name').'</u> ' : '').
			'�� ����� <b>'.$v['value'].'</b> ���.';
		case 34: return '�������������� ������� �����������: '.
			($v['value1'] ? '<span class="oplata">'._expense($v['value1']).'</span> ' : '').
			($v['value2'] ? '<em>('.$v['value2'].')</em> ' : '').
			($v['value3'] ? '��� ���������� <u>'._viewer($v['value3'], 'name').'</u> ' : '').
			'�� ����� <b>'.$v['value'].'</b> ���.';
		case 35: return '��������� ������ ������� �� '.FullDataTime($v['value2']).':<div class="changes">'.$v['value'].'</div>';

		case 36: return
			'�������� ���������� �/� �� ����� <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'��� ���������� <u>'._viewer($v['value2'], 'name').'</u>.';
		case 37: return
			'������ �/� �� ����� <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'��� ���������� <u>'._viewer($v['value2'], 'name').'</u>.';
		case 38: return '��������� ������� ����� ��� ����� <span class="oplata">'._invoice($v['value1']).'</span>: <b>'.$v['value'].'</b> ���.'.
						($v['value2'] ? '<br /><div class="changes">'.$v['value2'].'</div>' : '');
		case 39:
			return '������� �� ����� <span class="oplata">'._invoice($v['value1'] > 100 ? 1 : $v['value1']).'</span> '.
					($v['value1'] > 100 ? '<u>'._viewer($v['value1'], 'name').'</u> ' : '').
				   '�� ���� <span class="oplata">'._invoice($v['value2'] > 100 ? 1 : $v['value2']).'</span> '.
					($v['value2'] > 100 ? '<u>'._viewer($v['value2'], 'name').'</u> ' : '').
				   '� ����� <b>'.$v['value'].'</b> ���.'.
				   ($v['value3'] ? ' <em>('.$v['value3'].')</em> ' : '');
		case 40:
			return '��������� ������ �/� � ����� <b>'.$v['value1'].'</b> ���. '.
				   '��� ���������� <u>'._viewer($v['value'], 'name').'</u>. '.
				   '���������� '.$v['value2'].'-�� ����� ������� ������.';
		case 41: return '�������� ������ �/� � ���������� <u>'._viewer($v['value'], 'name').'</u>.';

		case 42: return '��������� ������ �������� '.$v['dogovor_nomer'].' '.
						'� ������ '.$v['zayav_link'].':'.
						'<div class="changes">'.$v['value'].'</div>';

		case 43: return '������������� ����������� �� ����: <a class="income-show" val="'.$v['value1'].'">'.$v['value'].' ������'._end($v['value'], '', '�', '��').'</a>.';

		case 44: return
			'�������� ������ �� �/� �� ����� <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'� ���������� <u>'._viewer($v['value2'], 'name').'</u>.';
		case 45: return '��������� ������� �/� � ����� <b>'.$v['value1'].'</b> ���. '.
				        '��� ���������� <u>'._viewer($v['value'], 'name').'</u>. ';

		case 46: return '�������������� ���������� �/� ���������� <u>'._viewer($v['value1'], 'name').'</u> '.
						'� ������� <b>'.$v['value'].'</b> ���. <em>('.$v['value2'].')</em>.';
		case 47: return '������������ ����� �� <a href="'.$v['value1'].'">'.$v['value'].'</a>.';

		case 50: return '�������� ���������� �/� � ����� <b>'.$v['value'].'</b> ���. � ���������� <u>'._viewer($v['value1'], 'name').'</u>.';
		case 51: return '�������� ������ �/� � ����� <b>'.$v['value'].'</b> ���. � ���������� <u>'._viewer($v['value1'], 'name').'</u>.';

		case 52: return '�����������'._end($v['value'], '', '�').' '.
						'<a class="transfer-show" val="'.$v['value1'].'">'.$v['value'].' �������'._end($v['value'], '', '�', '��').'</a>'.
						($v['value2'] ? ' <em>('.$v['value2'].')</em>' : '').
						'.';

		case 501: return '� ����������: �������� ������ ������������ ������� "'.$v['value'].'".';
		case 502: return '� ����������: ��������� ������ ������� "'.$v['value1'].'":<div class="changes">'.$v['value'].'</div>';
		case 503: return '� ����������: �������� ������������ ������� "'.$v['value'].'".';

		case 510: return '� ����������: ��������� ���������� �����������:<div class="changes">'.$v['value'].'</div>';

		case 504: return '� ����������: �������� ������ ������� ��� ������� "'.$v['value'].'": '.$v['value1'].'.';
		case 505: return '� ����������: ��������� ������� � ������� "'.$v['value'].'":<div class="changes">'.$v['value1'].'</div>';
		case 506: return '� ����������: �������� ������� � ������� "'.$v['value'].'": '.$v['value1'].'.';

		case 507: return '� ����������: �������� ������ ���� ������� "'.$v['value'].'".';
		case 508: return '� ����������: ��������� ���� ������� "'.$v['value'].'":<div class="changes">'.$v['value1'].'</div>';
		case 509: return '� ����������: �������� ���� ������� "'.$v['value'].'".';

		case 511: return '� ����������: �������� ����� ��������� �������� ������ <u>'.$v['value'].'</u>.';
		case 512: return '� ����������: ��������� ������ ��������� �������� ������ <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 513: return '� ����������: �������� ������ ��������� �������� ������ <u>'.$v['value'].'</u>.';

		case 514: return '� ����������: ��������� ������ ���������� <u>'._viewer($v['value'], 'name').'</u>:<div class="changes">'.$v['value1'].'</div>';

		case 515: return '� ����������: �������� ������ ����� <u>'.$v['value'].'</u>.';
		case 516: return '� ����������: ��������� ������ ����� <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 517: return '� ����������: �������� ����� <u>'.$v['value'].'</u>.';

		case 518: return '� ����������: �������� ����� ��������� �������� ����������� <u>'.$v['value'].'</u>.';
		case 519: return '� ����������: ��������� ������ ��������� �������� ����������� <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 520: return '� ����������: �������� ������ ��������� �������� ����������� <u>'.$v['value'].'</u>.';

		default: return $v['type'];
	}
}//history_types()
function history_spisok($v=array()) {
	$filter = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 30,
		'worker_id' => !empty($v['worker_id']) && preg_match(REGEXP_NUMERIC, $v['worker_id']) ? $v['worker_id'] : 0,
		'cat_id' => !empty($v['cat_id']) && preg_match(REGEXP_NUMERIC, $v['cat_id']) ? $v['cat_id'] : 0,
		'client_id' => !empty($v['client_id']) && preg_match(REGEXP_NUMERIC, $v['client_id']) ? $v['client_id'] : 0,
		'zayav_id' => !empty($v['zayav_id']) && preg_match(REGEXP_NUMERIC, $v['zayav_id']) ? $v['zayav_id'] : 0
	);

	$cond = "`id`";
	if($filter['worker_id'])
		$cond .= " AND `viewer_id_add`=".$filter['worker_id'];
	if($filter['cat_id'])
		$cond .= " AND `type` IN(".history_group_ids($filter['cat_id']).")";
	if($filter['client_id'])
		$cond .= " AND `client_id`=".$filter['client_id'];
	if($filter['zayav_id'])
		$cond .= " AND `zayav_id`=".$filter['zayav_id'];

	$page = $filter['page'];
	$limit = $filter['limit'];
	$start = ($page - 1) * $limit;

	$send = $page == 1 ?
		'<input type="hidden" id="history_limit" value="'.$filter['limit'].'" />'.
		'<input type="hidden" id="history_worker_id" value="'.$filter['worker_id'].'" />'.
		'<input type="hidden" id="history_cat_id" value="'.$filter['cat_id'].'" />'.
		'<input type="hidden" id="history_client_id" value="'.$filter['client_id'].'" />'.
		'<input type="hidden" id="history_zayav_id" value="'.$filter['zayav_id'].'" />'
		: '';

	$sql = "SELECT COUNT(`id`) AS `all`
			FROM `history`
			WHERE ".$cond."
			LIMIT 1";
	$all = query_value($sql);
	if(!$all)
		return $send.'������� �� ��������� �������� ���.';

	$sql = "SELECT *
			FROM `history`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$history = array();
	while($r = mysql_fetch_assoc($q))
		$history[$r['id']] = $r;
	$history = _viewer($history);
	$history = _clientLink($history);
	$history = _zayavLink($history);
	$history = _dogNomer($history);

	$txt = '';
	end($history);
	$keyEnd = key($history);
	reset($history);
	foreach($history as $r) {
		if(!$txt) {
			$time = strtotime($r['dtime_add']);
			$viewer_id = $r['viewer_id_add'];
		}
		$txt .= '<li><div class="li">'.history_types($r).'</div>';
		$key = key($history);
		if(!$key ||
			$key == $keyEnd ||
			$time - strtotime($history[$key]['dtime_add']) > 900 ||
			$viewer_id != $history[$key]['viewer_id_add']) {
			$send .=
				'<div class="history_unit">'.
					'<div class="head"><span>'.FullDataTime($r['dtime_add']).'</span>'.($r['viewer_id_add'] ? $r['viewer_name'] : '').'</div>'.
					'<ul>'.$txt.'</ul>'.
				'</div>';
			$txt = '';
		}
		next($history);
	}
	if($start + $limit < $all)
		$send .= '<div class="_next" id="history_next" val="'.($page + 1).'"><span>�������� ����� ������ ������...</span></div>';
	return $send;
}//history_spisok()
function history_right() {
	$workers = query_selJson("
		SELECT
			DISTINCT `h`.`viewer_id_add`,
			CONCAT(`u`.`first_name`,' ',`u`.`last_name`)
        FROM `history` `h`,`vk_user` `u`
        WHERE `h`.`viewer_id_add`=`u`.`viewer_id`");
	return
		'<script type="text/javascript">var WORKERS='.$workers.';</script>'.
		'<div class="findHead">���������</div>'.
		'<input type="hidden" id="worker_id">'.
		'<div class="findHead">���������</div>'.
		'<input type="hidden" id="cat_id">';
}//history_right()

function _invoiceBalans($invoice_id, $start=false) {// ��������� �������� ������� �����
	if($start === false)
		$start = $invoice_id > 100 ? _viewer($invoice_id, 'cash') : _invoice($invoice_id, 'start');
	$income = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `money` WHERE `deleted`=0 AND `confirm`=0 AND `invoice_id`=".($invoice_id > 100 ? "1 AND `viewer_id_add`=" : '').$invoice_id);
	$from = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `invoice_transfer` WHERE `invoice_from`=".($invoice_id > 100 ? "1 AND `worker_from`=" : '').$invoice_id);
	$to = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `invoice_transfer` WHERE `invoice_to`=".($invoice_id > 100 ? "1 AND `worker_to`=" : '').$invoice_id);
	return round($income - $start - $from + $to, 2);
}//_invoiceBalans()
function invoice() {
	$data = cash_spisok();
	$sql = "SELECT `viewer_id` FROM `vk_user_rules` WHERE `key`='RULES_GETMONEY' AND `value`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$data['cash_spisok'][] = '{'.
				'uid:'.$r['viewer_id'].','.
				'title:"'.addslashes(_viewer($r['viewer_id'], 'name')).'"'.
			'}';
	return
		'<script type="text/javascript">'.
			'var CASH=['.implode(',', $data['cash']).'],'.
				'CASH_SPISOK=['.implode(',', $data['cash_spisok']).'],'.
				'INVOICE_NAME="'._invoice(1).'";'.
		'</script>'.
		'<div class="headName">'.
			'�����'.
			'<a class="add transfer">������� ����� �������</a>'.
			'<span>::</span>'.
			'<a href="'.URL.'&p=setup&d=invoice" class="add">���������� �������</a>'.
		'</div>'.
		'<div id="confirm-info">'.income_confirm_info().'</div>'.
	(TRANSFER_CONFIRM ? //������������� ��������� ������������
		'<div class="_info">'.
			'���� ��������, ��������� �������������: <b>'.TRANSFER_CONFIRM.'</b>. '.
			'<a class="transfer-confirm">�����������</a>'.
		'</div>'
	: '').
		'<div id="cash-spisok">'.$data['spisok'].'</div>'.
		'<div id="invoice-spisok">'.invoice_spisok().'</div>'.
		'<div class="headName">������� ���������</div>'.
		'<div class="transfer-spisok">'.transfer_spisok().'</div>';
}//invoice()
function income_confirm_info() {
	if(!$confirm = query_value("SELECT COUNT(`id`) FROM `money` WHERE !`deleted` AND `confirm`"))
		return '';
	return
	'<div class="_info">'.
		'<b>'.$confirm.' ������'._end($confirm, '', '�', '��').'</b> �����'._end($confirm, '�', '�').'� ������������� ����������� �� ����. '.
		'<a class="income-confirm">�����������</a>'.
	'</div>';
}
function cash_spisok() {
	$send = array(
		'cash' => array(),
		'cash_spisok' => array(),
		'spisok' => ''
	);
	$sql = "SELECT
 				DISTINCT(`u`.`viewer_id`) AS `viewer_id`,
 				`u`.`cash`
	        FROM `vk_user` AS `u`,
	        	 `vk_user_rules` AS `r`
			WHERE `u`.`worker`=1
			  AND `u`.`viewer_id`=`r`.`viewer_id`
			  AND `r`.`key`='RULES_CASH'
			  AND `r`.`value`=1
	        ORDER BY `u`.`dtime_add`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return $send;

	$send['spisok'] = '<table class="_spisok">';
	while($r = mysql_fetch_assoc($q)) {
		$sum = $r['cash'] == -1 ? '' : _invoiceBalans($r['viewer_id']);
		$send['spisok'] .= '<tr>'.
			'<td><span>��������:</span> '._viewer($r['viewer_id'], 'name').
			'<td class="r">'.$sum.($r['cash'] == -1 ? '' : ' ���.').
			'<td><div val="'.$r['viewer_id'].'" class="img_note'._tooltip('���������� ������� ��������', -95).'</div>';
		$send['cash'][] = '{'.
				'id:'.$r['viewer_id'].','.
				'name:"'.addslashes(_viewer($r['viewer_id'], 'name')).'",'.
				'sum:"'.$sum.'"'.
			'}';
		$send['cash_spisok'][] = '{'.
				'uid:'.$r['viewer_id'].','.
				'title:"��������: '.addslashes(_viewer($r['viewer_id'], 'name')).'"'.
			'}';
	}
	$send['spisok'] .= '</table>';
	return $send;
}//cash_spisok()
function invoice_spisok() {
	$invoice = _invoice();
	if(empty($invoice))
		return '����� �� ����������.';

	$send = '<table class="_spisok">';
	foreach($invoice as $r)
		$send .= '<tr>'.
			'<td class="name"><b>'.$r['name'].'</b><pre>'.$r['about'].'</pre>'.
			'<td class="balans">'.
			($r['start'] != -1 ? '<b>'._sumSpace(_invoiceBalans($r['id'])).'</b> ���.' : (VIEWER_ADMIN ? '' : '<a class="invoice_set" val="'.$r['id'].'">���������� ������� �����</a>')).
			'<td><div val="'.$r['id'].'" class="img_note'._tooltip('���������� ������� ��������', -95).'</div>'.
			(VIEWER_ADMIN ? '<td><a class="invoice_set" val="'.$r['id'].'">���������� ������� �����</a>' : '');
	$send .= '</table>';
	return $send;
}//invoice_spisok()
function transfer_spisok($v=array()) {
	$v = array(
	//	'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
	//	'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 15,
		'confirm' => !empty($v['confirm']) && preg_match(REGEXP_NUMERIC, $v['confirm']) ? $v['confirm'] : 0,
		'ids' => !empty($v['ids']) ? $v['ids'] : ''
	);
	$sql = "SELECT *
	        FROM `invoice_transfer`
	        WHERE `id`
	        ".($v['confirm'] ? "AND !`invoice_to` AND `worker_to` AND !`confirm`" : '')."
	        ".($v['ids'] ? "AND `id` IN (".$v['ids'].")" : '')."
	        ORDER BY `id` DESC";
	$q = query($sql);
	$send = '<table class="_spisok _money">'.
		'<tr>'.
			($v['confirm'] ? '<th>' : '').
			'<th>C����'.
			'<th>�� �����'.
			'<th>�� ����'.
			'<th>��������'.
			'<th>����';
	while($r = mysql_fetch_assoc($q))
		$send .=
			'<tr>'.
				($v['confirm'] ? '<td>'._check($r['id'].'_') : '').
				'<td class="sum">'._sumSpace($r['sum']).
				'<td>'.($r['worker_from'] && _viewerRules($r['worker_from'], 'RULES_CASH') || $r['invoice_from'] ? '<span class="type">'._invoice($r['invoice_from']).'</span>' : '').
					   ($r['worker_from'] && $r['invoice_from'] ? '<br />' : '').
					   ($r['worker_from'] ? _viewer($r['worker_from'], 'name') : '').
				'<td>'.($r['worker_to'] && _viewerRules($r['worker_to'], 'RULES_CASH') || $r['invoice_to'] ? '<span class="type">'._invoice($r['invoice_to']).'</span>' : '').
					   ($r['worker_to'] && $r['invoice_to'] ? '<br />' : '').
					   ($r['worker_to'] ? _viewer($r['worker_to'], 'name') : '').
					   (!$r['invoice_to'] && $r['worker_to'] ? '<br /><span class="confirm'.($r['confirm'] ? '' : ' no').'">'.($r['confirm'] ? '' : '�� ').'������������</span>' : '').
				'<td class="about">'.
					($r['income_count'] ? '<a class="income-show" val="'.$r['income_ids'].'">'.$r['income_count'].' ������'._end($r['income_count'], '', '�', '��').'</a>' : '').
					(VIEWER_ADMIN && $r['confirm'] && $r['about'] ? ($r['income_count'] ? '<br />' : '').$r['about'] : '').
				'<td class="dtime">'.FullDataTime($r['dtime_add'], 1);
	$send .= '</table>';
	return $send;
}//transfer_spisok()
function invoiceHistoryAction($id, $i='name') {//�������� �������� � ������� ������
	$action = array(
		1 => array(
			'name' => '�������� �������',
			'znak' => '',
			'cash' => 1 //��������� ���������� ����� ��� ��������
		),
		2 => array(
			'name' => '�������� �������',
			'znak' => '-',
			'cash' => 1
		),
		3 => array(
			'name' => '�������������� �������',
			'znak' => '',
			'cash' => 1
		),
		4 => array(
			'name' => '������� ����� �������',
			'znak' => '',
			'cash' => 0
		),
		5 => array(
			'name' => '��������� ������� �����',
			'znak' => '',
			'cash' => 0
		),
		6 => array(
			'name' => '�������� �������',
			'znak' => '-',
			'cash' => 1
		),
		7 => array(
			'name' => '�������� �������',
			'znak' => '',
			'cash' => 1
		),
		8 => array(
			'name' => '�������������� �������',
			'znak' => '-',
			'cash' => 1
		),
		9 => array(
			'name' => '�������������� �������',
			'znak' => '',
			'cash' => 0
		),
		10 => array(
			'name' => '��������� �������',
			'znak' => '',
			'cash' => 1
		),
		11 => array(
			'name' => '������������� �������',
			'znak' => '',
			'cash' => 1
		)
	);
	return $action[$id][$i];
}//invoiceHistoryAction()
function invoice_history($v) {
	$v = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 15,
		'invoice_id' => intval($v['invoice_id'])
	);
	$invoice = $v['invoice_id'] > 100 ? '�������� '._viewer($v['invoice_id'], 'name') : _invoice($v['invoice_id']);
	$send = '';
	if($v['page'] == 1)
		$send = '<div>���� <u>'.$invoice.'</u>:</div>'.
				'<input type="hidden" id="invoice_history_id" value="'.$v['invoice_id'].'" />';

	$all = query_value("SELECT COUNT(*) FROM `invoice_history` WHERE `invoice_id`=".$v['invoice_id']);
	if(!$all)
		return $send.'<br />������� ���.';

	$start = ($v['page'] - 1) * $v['limit'];
	$sql = "SELECT `h`.*,
				   IFNULL(`m`.`zayav_id`,0) AS `zayav_id`,
				   IFNULL(`m`.`income_id`,0) AS `income_id`,
				   IFNULL(`m`.`expense_id`,0) AS `expense_id`,
				   IFNULL(`m`.`worker_id`,0) AS `worker_id`,
				   IFNULL(`m`.`dogovor_id`,0) AS `dogovor_id`,
				   IFNULL(`m`.`prim`,'') AS `prim`,
				   IFNULL(`i`.`invoice_from`,0) AS `invoice_from`,
				   IFNULL(`i`.`invoice_to`,0) AS `invoice_to`,
				   IFNULL(`i`.`worker_from`,0) AS `worker_from`,
				   IFNULL(`i`.`worker_to`,0) AS `worker_to`,
				   IFNULL(`i`.`income_count`,0) AS `income_count`,
				   IFNULL(`i`.`income_ids`,'') AS `income_ids`
			FROM `invoice_history` `h`
				LEFT JOIN `money` `m`
				ON `h`.`table`='money' AND `h`.`table_id`=`m`.`id`
				LEFT JOIN `invoice_transfer` `i`
				ON `h`.`table`='invoice_transfer' AND `h`.`table_id`=`i`.`id`
			WHERE `h`.`invoice_id`=".$v['invoice_id']."
			ORDER BY `h`.`id` DESC
			LIMIT ".$start.",".$v['limit'];
	$q = query($sql);
	$history = array();
	while($r = mysql_fetch_assoc($q))
		$history[$r['id']] = $r;

	$history = _zayavLink($history);
	$history = _dogNomer($history);

	if($v['page'] == 1)
		$send .= '<table class="_spisok _money invoice-history">'.
					'<tr><th>��������'.
						'<th>�����'.
						'<th>������'.
						'<th>��������'.
						'<th>����';
	foreach($history as $r) {
		$about = '';
		if($r['zayav_id'])
			$about = $r['zayav_link'].
					 ($r['dogovor_id'] ? '. '.'��������� ����� (������� '.$r['dogovor_nomer'].')' : '').
					 ' ';
		$about .= $r['prim'].' ';
		$worker = $r['worker_id'] ? '<u>'._viewer($r['worker_id'], 'name').'</u> ' : '';
		$expense = $r['expense_id'] ? '<span class="type">'._expense($r['expense_id']).(!trim($about) && !$worker ? '' : ': ').'</span> ' : '';
		//$income = $r['income_id'] ? '<div class="type">'._income($r['income_id']).(empty($about) ? '' : ': ').'</div>' : '';
		if($r['invoice_from'] != $r['invoice_to']) {//����� �� �����, ������� �������
			if(!$r['invoice_to'])//������ ���� �������� ������������
				$about .= '�������� ���������� '._viewer($r['worker_to'], 'name');
			elseif(!$r['invoice_from'])//������ ���� �������� �� ������������
				$about .= '��������� �� ���������� '._viewer($r['worker_from'], 'name');
			elseif($r['invoice_id'] == $r['invoice_from'])//��������������� ���� ����� - ����������
				$about .= '����������� �� ���� <span class="type">'._invoice($r['invoice_to']).'</span>'.
						 ($r['worker_to'] ? ' '._viewer($r['worker_to'], 'name') : '').
						 ($r['worker_from'] ? ' �� ����� <span class="type">'._invoice($r['invoice_from']).'</span> '._viewer($r['worker_from'], 'name') : '');
			elseif($r['invoice_id'] == $r['invoice_to'])//��������������� ���� ����� - ����������
				$about .= '����������� �� ����� <span class="type">'._invoice($r['invoice_from']).'</span>'.
					($r['worker_from'] ? ' '._viewer($r['worker_from'], 'name') : '').
					($r['worker_to'] ? ' �� ���� <span class="type">'._invoice($r['invoice_to']).'</span> '._viewer($r['worker_to'], 'name') : '');
			elseif($r['invoice_id'] == $r['worker_from'])//��������������� ���� ���������� - ����������
				$about .= '����������� �� ���� <span class="type">'._invoice($r['invoice_to']).'</span>';
			elseif($r['invoice_id'] == $r['worker_to'])//��������������� ���� ���������� - ����������
				$about .= '����������� �� ����� <span class="type">'._invoice($r['invoice_from']).'</span>';
		} else {//����� �����, ������� ����������
			if($r['invoice_id'] == $r['worker_from'])//��������������� ���� ���������� - ����������
				$about .= '����������� �� ���� <span class="type">'._invoice($r['invoice_to']).'</span> '._viewer($r['worker_to'], 'name');
			if($r['invoice_id'] == $r['worker_to'])//��������������� ���� ���������� - ����������
				$about .= '����������� �� ����� <span class="type">'._invoice($r['invoice_from']).'</span> '._viewer($r['worker_from'], 'name');
		}
		$about .=
			($r['income_count'] ?
					' <a class="income-show" val="'.$r['income_ids'].'">'.
						$r['income_count'].' ������'._end($r['income_count'], '', '�', '��').
					'</a>'
			: '');
		$sum = '';
		if($r['sum_prev'] != 0)
			$sum = _sumSpace($r['sum'] - $r['sum_prev']).
				   '<div class="sum-change">('.round($r['sum_prev'], 2).' &raquo; '.round($r['sum'], 2).')</div>';
		elseif($r['sum'] != 0)
			$sum = _sumSpace($r['sum']);
		$send .=
			'<tr><td class="action">'.invoiceHistoryAction($r['action']).
				'<td class="sum">'.$sum.
				'<td class="balans">'._sumSpace($r['balans']).
				'<td>'.$expense.$worker.$about.
				'<td class="dtime">'.FullDataTime($r['dtime_add']);
	}

	if($start + $v['limit'] < $all) {
		$c = $all - $start - $v['limit'];
		$c = $c > $v['limit'] ? $v['limit'] : $c;
		$send .=
			'<tr class="_next" val="'.($v['page'] + 1).'"><td colspan="5">'.
				'<span>�������� ��� '.$c.' �����'._end($c, '�', '�', '��').'</span>';
	}
	if($v['page'] == 1)
		$send .= '</table>';
	return $send;
}//invoice_history()
function invoice_history_insert($v) {
	$v = array(
		'action' => $v['action'],
		'table' => empty($v['table']) ? '' : $v['table'],
		'id' => empty($v['id']) ? 0 : $v['id'],
		'sum' => empty($v['sum']) ? 0 : $v['sum'],
		'sum_prev' => empty($v['sum_prev']) ? 0 : $v['sum_prev'],
		'worker_id' => empty($v['worker_id']) ? 0 : $v['worker_id'],
		'invoice_id' => empty($v['invoice_id']) ? 0 : $v['invoice_id']
	);
	if($v['worker_id'] && _viewerRules($v['worker_id'], 'RULES_CASH')) //���� ���������� ��������� � ���� ������ ����, �� �� �������� ������
		$v['invoice_id'] = $v['worker_id'];
	if($v['table']) {
		$r = query_assoc("SELECT * FROM `".$v['table']."` WHERE `id`=".$v['id']);
		$v['sum'] = abs($r['sum']);
		switch($v['table']) {
			case 'money':
				if($r['confirm'])
					return;
				$v['invoice_id'] = $r['invoice_id'];
				$v['sum'] = invoiceHistoryAction($v['action'], 'znak').$v['sum'];
				if(invoiceHistoryAction($v['action'], 'cash') && $r['invoice_id'] == 1)
					if(query_value("SELECT COUNT(*) FROM `vk_user_rules` WHERE `viewer_id`=".$r['viewer_id_add']." AND `key`='RULES_CASH' AND `value`=1"))
						invoice_history_insert_sql($r['viewer_id_add'], $v);
				break;
			case 'invoice_transfer':
				if($r['invoice_from'] && $r['invoice_to'] && $r['invoice_from'] == $r['invoice_to']) {//���������� �������
					$v['invoice_id'] = $r['worker_from'];
					invoice_history_insert_sql($r['worker_to'], $v);
					$v['sum'] *= -1;
					break;
				}
				if(!$r['invoice_from'] && !$r['invoice_to'])
					return;
				if(!$r['invoice_from']) {//������ ������� � ������������
					$v['invoice_id'] = $r['invoice_to'];
					if($r['worker_to'])
						invoice_history_insert_sql($r['worker_to'], $v);
					break;
				}
				if(!$r['invoice_to']) {//�������� ������� ������������
					$v['invoice_id'] = $r['invoice_from'];
					$v['sum'] *= -1;
					if($r['worker_from'])
						invoice_history_insert_sql($r['worker_from'], $v);
					break;
				}
				//�������� �� ����� � �������� � �� ����� �����������
				$v['invoice_id'] = $r['invoice_from'];
				invoice_history_insert_sql($r['invoice_to'], $v);
				if($r['worker_from']) {
					$v['sum'] *= -1;
					invoice_history_insert_sql($r['worker_from'], $v);
					$v['sum'] *= -1;
				}
				if($r['worker_to'])
					invoice_history_insert_sql($r['worker_to'], $v);
				$v['sum'] *= -1;
				break;
			}
	}
	invoice_history_insert_sql($v['invoice_id'], $v);
}//invoice_history_insert()
function invoice_history_insert_sql($invoice_id, $v) {
	$sql = "INSERT INTO `invoice_history` (
				`action`,
				`table`,
				`table_id`,
				`invoice_id`,
				`sum`,
				`sum_prev`,
				`balans`,
				`viewer_id_add`
			) VALUES (
				".$v['action'].",
				'".$v['table']."',
				".$v['id'].",
				".$invoice_id.",
				".$v['sum'].",
				".$v['sum_prev'].",
				"._invoiceBalans($invoice_id).",
				".VIEWER_ID."
			)";
	query($sql);
}

function income_path($data) {
	$ex = explode(':', $data);
	$d = explode('-', $ex[0]);
	define('YEAR', $d[0]);
	define('MON', @$d[1]);
	define('DAY', @$d[2]);
	$to = '';
	if(!empty($ex[1])) {
		$d = explode('-', $ex[1]);
		$to = ' - '.intval($d[2]).
			($d[1] != MON ? ' '._monthDef($d[1]) : '').
			($d[0] != YEAR ? ' '.$d[0] : '');
	}
	return
	'<a href="'.URL.'&p=report&d=money&d1=income&d2=all">���</a> � '.(YEAR ? '' : '<b>�� �� �����</b>').
	(MON ? '<a href="'.URL.'&p=report&d=money&d1=income&d2=year&year='.YEAR.'">'.YEAR.'</a> � ' : '<b>'.YEAR.'</b>').
	(DAY ? '<a href="'.URL.'&p=report&d=money&d1=income&d2=month&mon='.YEAR.'-'.MON.'">'._monthDef(MON, 1).'</a> � ' : (MON ? '<b>'._monthDef(MON, 1).'</b>' : '')).
	(DAY ? '<b>'.intval(DAY).$to.'</b>' : '');

}//income_path()
function income_all() {
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y') AS `year`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `deleted`=0
			  AND `sum`>0
			  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%Y')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['year']] = '<tr>'.
			'<td><a href="'.URL.'&p=report&d=money&d1=income&d2=year&year='.$r['year'].'">'.$r['year'].'</a>'.
			'<td class="r"><b>'._sumSpace($r['sum']).'</b>';

	$th = '';
	foreach(_income() as $income_id => $i) {
		$th .= '<th>'.$i['name'];
		foreach($spisok as $y => $r)
			$spisok[$y] .= '<td class="r">';
		$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y') AS `year`,
					   SUM(`sum`) AS `sum`
				FROM `money`
				WHERE `deleted`=0
				  AND `sum`>0
				  AND `income_id`=".$income_id."
				  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
				GROUP BY DATE_FORMAT(`dtime_add`,'%Y')
				ORDER BY `dtime_add` ASC";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$spisok[$r['year']] .= _sumSpace($r['sum']);
	}

	return
	'<div class="headName">����� �������� �� �����</div>'.
	'<table class="_spisok sums">'.
		'<tr><th>���'.
			'<th>�����'.
			$th.
			implode('', $spisok).
	'</table>';
}//income_all()
function income_year($year) {
	$spisok = array();
	for($n = 1; $n <= (strftime('%Y', time()) == $year ? intval(strftime('%m', time())) : 12); $n++)
		$spisok[$n] =
			'<tr><td class="r grey">'._monthDef($n, 1).
				'<td class="r">';
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%m') AS `mon`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `deleted`=0
			  AND `sum`>0
			  AND `dtime_add` LIKE '".$year."%'
			  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%m')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[intval($r['mon'])] =
			'<tr><td class="r"><a href="'.URL.'&p=report&d=money&d1=income&d2=month&mon='.$year.'-'.$r['mon'].'">'._monthDef($r['mon'], 1).'</a>'.
				'<td class="r"><b>'._sumSpace($r['sum']).'</b>';

	$th = '';
	foreach(_income() as $income_id => $i) {
		$th .= '<th>'.$i['name'];
		foreach($spisok as $y => $r)
			$spisok[$y] .= '<td class="r">';
		$sql = "SELECT DATE_FORMAT(`dtime_add`,'%m') AS `mon`,
					   SUM(`sum`) AS `sum`
				FROM `money`
				WHERE `deleted`=0
				  AND `sum`>0
				  AND `dtime_add` LIKE '".$year."%'
				  AND `income_id`=".$income_id."
				  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
				GROUP BY DATE_FORMAT(`dtime_add`,'%m')
				ORDER BY `dtime_add` ASC";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$spisok[intval($r['mon'])] .= _sumSpace($r['sum']);
	}
	return
	'<div class="headName">����� �������� �� ������� �� '.$year.' ���</div>'.
	'<div class="inc-path">'.income_path($year).'</div>'.
	'<table class="_spisok sums">'.
		'<tr><th>�����'.
			'<th>�����'.
			$th.
			implode('', $spisok).
	'</table>';
}//income_year()
function income_month($mon) {
	$path = income_path($mon);
	$spisok = array();
	for($n = 1; $n <= (strftime('%Y%m', time()) == YEAR.MON ? intval(strftime('%d', time())) : date('t', strtotime($mon.'-01'))); $n++)
		$spisok[$n] =
			'<tr><td class="r grey">'.$n.'.'.MON.'.'.YEAR.
				'<td class="r">';
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%d') AS `day`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `deleted`=0
			  AND `sum`>0
			  AND `dtime_add` LIKE '".$mon."%'
			  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%d')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[intval($r['day'])] =
			'<tr><td class="r"><a href="'.URL.'&p=report&d=money&d1=income&day='.$mon.'-'.$r['day'].'">'.intval($r['day']).'.'.MON.'.'.YEAR.'</a>'.
			'<td class="r"><b>'._sumSpace($r['sum']).'</b>';

	$th = '';
	foreach(_income() as $income_id => $i) {
		$th .= '<th>'.$i['name'];
		foreach($spisok as $y => $r)
			$spisok[$y] .= '<td class="r">';
		$sql = "SELECT DATE_FORMAT(`dtime_add`,'%d') AS `day`,
					   SUM(`sum`) AS `sum`
				FROM `money`
				WHERE `deleted`=0
				  AND `sum`>0
				  AND `dtime_add` LIKE '".$mon."%'
				  AND `income_id`=".$income_id."
				  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
				GROUP BY DATE_FORMAT(`dtime_add`,'%d')
				ORDER BY `dtime_add` ASC";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$spisok[intval($r['day'])] .= _sumSpace($r['sum']);
	}
	return
	'<div class="headName">����� �������� �� ���� �� '._monthDef(MON, 1).' '.YEAR.'</div>'.
	'<div class="inc-path">'.$path.'</div>'.
	'<table class="_spisok sums">'.
		'<tr><th>�����'.
			'<th>�����'.
			$th.
			implode('', $spisok).
	'</table>';
}//income_month()
function income_day($day) {
	$data = income_spisok(array('day' => $day));
	return
	'<script type="text/javascript">var OPL={from:"income"};</script>'.
	'<div class="headName">������ ��������<a class="add income-add">������ �����</a></div>'.
	'<div class="inc-path">'.income_path($day).'</div>'.
	'<div id="spisok">'.$data['spisok'].'</div>';
}//income_day()
function income_days($month=0) {
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y-%m-%d') AS `day`
			FROM `money`
			WHERE `deleted`=0
			  AND `sum`>0
			  AND `dtime_add` LIKE ('".($month ? $month : strftime('%Y-%m'))."%')
			  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%d')";
	$q = query($sql);
	$days = array();
	while($r = mysql_fetch_assoc($q))
		$days[$r['day']] = 1;
	return $days;
}//income_days()
function income_right($sel) {
	if(RULES_MONEY)
		$workers = query_selJson("
			SELECT
				DISTINCT `m`.`viewer_id_add`,
				CONCAT(`u`.`first_name`,' ',`u`.`last_name`)
	        FROM `money` `m`,`vk_user` `u`
	        WHERE `m`.`viewer_id_add`=`u`.`viewer_id`
	          AND `m`.`deleted`=0
	          AND `m`.`sum`>0");
	return
		_calendarFilter(array(
			'days' => income_days(),
			'func' => 'income_days',
			'sel' => $sel
		)).
		'<div class="findHead">���� ��������</div>'.
		'<input type="hidden" id="income_id">'.
	(RULES_MONEY ?
		'<script type="text/javascript">var WORKERS='.$workers.';</script>'.
		'<div class="findHead">������ ���������</div>'.
		'<input type="hidden" id="worker_id">'
	: '').
		_check('deleted', '�������� �������');
}//income_right()

function income_insert($v) {//�������� �������
	$v = array(
		'from' => empty($v['from']) ? '' : $v['from'],
		'type' => $v['type'],
		'confirm' => empty($v['confirm']) ? 0 : $v['confirm'],
		'zayav_id' => empty($v['zayav_id']) ? 0 : $v['zayav_id'],
		'client_id' => empty($v['client_id']) ? 0 : $v['client_id'],
		'dogovor_id' => empty($v['dogovor_id']) ? 0 : $v['dogovor_id'],
		'sum' => $v['sum'],
		'prim' => empty($v['prim']) ? '' : $v['prim']
	);
	if($v['zayav_id']) {
		$sql = "SELECT *
				FROM `zayav`
				WHERE `deleted`=0
				  AND `id`=".$v['zayav_id'];
		if(!$z = mysql_fetch_assoc(query($sql)))
			return false;
		if($v['client_id'] && $v['client_id'] != $z['client_id'])
			return false;
		$v['client_id'] = $z['client_id'];
	}

	$sql = "INSERT INTO `money` (
				`zayav_id`,
				`client_id`,
				`invoice_id`,
				`income_id`,
				`confirm`,
				`owner_id`,
				`dogovor_id`,
				`sum`,
				`prim`,
				`viewer_id_add`
			) VALUES (
				".$v['zayav_id'].",
				".$v['client_id'].",
				"._income($v['type'], 'invoice').",
				".$v['type'].",
				".$v['confirm'].",
				".VIEWER_ID.",
				".$v['dogovor_id'].",
				".$v['sum'].",
				'".addslashes($v['prim'])."',
				".VIEWER_ID."
			)";
	query($sql);
	$insert_id = mysql_insert_id();

	invoice_history_insert(array(
		'action' => 1,
		'table' => 'money',
		'id' => $insert_id
	));
	clientBalansUpdate($v['client_id']);
	_zayavBalansUpdate($v['zayav_id']);

	history_insert(array(
		'type' => 10,
		'zayav_id' => $v['zayav_id'],
		'client_id' => $v['client_id'],
		'dogovor_id' => $v['dogovor_id'],
		'value' => $v['sum'],
		'value1' => $v['prim'],
		'value2' => $v['type']
	));

	switch($v['from']) {
		case 'client':
			$data = income_spisok(array('client_id'=>$v['client_id'],'limit'=>15));
			return $data['spisok'];
		case 'zayav': return zayav_money($v['zayav_id']);
		default: return $insert_id;
	}
}//income_insert()
function incomeFilter($v) {
	$send = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 30,
		'income_id' => !empty($v['income_id']) && preg_match(REGEXP_NUMERIC, $v['income_id']) ? $v['income_id'] : 0,
		'confirm' => !empty($v['confirm']),
		'worker_id' => !empty($v['worker_id']) && preg_match(REGEXP_NUMERIC, $v['worker_id']) ? $v['worker_id'] : 0,
		'deleted' => isset($v['deleted']) && preg_match(REGEXP_BOOL, $v['deleted']) ? $v['deleted'] : 0,
		'owner_id' => !empty($v['owner_id']) && preg_match(REGEXP_NUMERIC, $v['owner_id']) && $v['owner_id'] > 100 ? $v['owner_id'] : 0,
		'client_id' => !empty($v['client_id']) && preg_match(REGEXP_NUMERIC, $v['client_id']) ? $v['client_id'] : 0,
		'zayav_id' => !empty($v['zayav_id']) && preg_match(REGEXP_NUMERIC, $v['zayav_id']) ? $v['zayav_id'] : 0,
		'day' => '',
		'from' => '',
		'to' => '',
		'ids' => !empty($v['ids']) ? $v['ids'] : '',
		'ids_ass' => array()
	);
	$send = _calendarPeriod(@$v['day']) + $send;
	if($send['ids'])
		foreach(explode(',', $send['ids']) as $id)
			$send['ids_ass'][$id] = $id;
	return $send;
}//incomeFilter()
function income_spisok($filter=array()) {
	$filter = incomeFilter($filter);

	$cond = '`sum`>0';
	$deleted = 0;

	if(!RULES_MONEY && !$filter['client_id'])
		$cond .= " AND `viewer_id_add`=".VIEWER_ID;
	if(RULES_MONEY && $filter['worker_id'])
		$cond .= " AND `viewer_id_add`=".$filter['worker_id'];
	if($filter['income_id'])
		$cond .= " AND `income_id`=".$filter['income_id'];
	if($filter['confirm'])
		$cond .= " AND `confirm`";
	if($filter['owner_id'])
		$cond .= " AND `owner_id`=".$filter['owner_id'];
	if($filter['client_id'])
		$cond .= " AND `client_id`=".$filter['client_id'];
	if($filter['zayav_id'])
		$cond .= " AND `zayav_id`=".$filter['zayav_id'];
	if($filter['day'])
		$cond .= " AND `dtime_add` LIKE '".$filter['day']."%'";
	if($filter['from'])
		$cond .= " AND `dtime_add`>='".$filter['from']." 00:00:00' AND `dtime_add`<='".$filter['to']." 23:59:59'";
	if(!$filter['owner_id'] && $filter['ids']) {
		$cond .= " AND `id` IN (".$filter['ids'].")";
		$deleted = 1;
	}
	if(!$deleted && !$filter['deleted'])
		$cond .=" AND !`deleted`";


	$sql = "SELECT
	            COUNT(`id`) AS `all`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE ".$cond."
			LIMIT 1";
	$send = mysql_fetch_assoc(query($sql));
	if(!$send['all'])
		return array(
			'all' => 0,
			'spisok' => '<div class="_empty">�������� ���.</div>'
		);

	$page = $filter['page'];
	$start = ($page - 1) * $filter['limit'];
	$sql = "SELECT *
			FROM `money`
			WHERE ".$cond."
			ORDER BY `id` ASC
			LIMIT ".$start.",".$filter['limit'];
	$q = query($sql);
	$money = array();
	while($r = mysql_fetch_assoc($q))
		$money[$r['id']] = $r;

	$money = _dogNomer($money);
	$money = _zayavLink($money);

	$send['spisok'] = '';
	if($page == 1)
		$send['spisok'] =
			'<input type="hidden" id="money_limit" value="'.$filter['limit'].'" />'.
			'<input type="hidden" id="money_client_id" value="'.$filter['client_id'].'" />'.
			'<input type="hidden" id="money_zayav_id" value="'.$filter['zayav_id'].'" />'.
			'<input type="hidden" id="money_deleted" value="'.$filter['deleted'].'" />'.
			'<input type="hidden" id="money_income_id" value="'.$filter['income_id'].'" />'.
			'<input type="hidden" id="money_worker_id" value="'.$filter['worker_id'].'" />'.
		(!$filter['zayav_id'] ?
			'<div class="_moneysum">'.
				'�������'._end($send['all'], '', '�').
				' <b>'.$send['all'].'</b> ������'._end($send['all'], '', '�', '��').
				' �� ����� <b>'._sumSpace($send['sum']).'</b> ���.'.
			'</div>' : '').
			'<table class="_spisok inc _money">'.
		(!$filter['zayav_id'] ?
				'<tr>'.
					($filter['owner_id'] || $filter['confirm'] ? '<th>'._check('money_all') : '').
					'<th>�����'.
					'<th>��������'.
					'<th>����'.
					(!$filter['owner_id'] && !$filter['ids'] && !$filter['confirm'] ? '<th>' : '')
		: '');
	foreach($money as $r)
		$send['spisok'] .= income_unit($r, $filter);
	if($start + $filter['limit'] < $send['all']) {
		$c = $send['all'] - $start - $filter['limit'];
		$c = $c > $filter['limit'] ? $filter['limit'] : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'" id="income_next"><td colspan="5">'.
				'<span>�������� ��� '.$c.' ������'._end($c, '', '�', '��').'</span>';
	}
	if($page == 1)
		$send['spisok'] .= '</table>';
	return $send;
}//income_spisok()
function income_unit($r, $filter=array()) {
	$about = '';
	if($r['dogovor_id'])
		$about .= '��������� ������ '.
			(!$filter['zayav_id'] ? '�� ������ '.$r['zayav_link'].' ' : '').
			'(������� '.$r['dogovor_nomer'].').';
	elseif($r['zayav_id'] && !$filter['zayav_id'])
		$about .= $r['zayav_link'].'. ';
	$about .= $r['prim'];
	if($r['confirm'])
		$about .= '<br /><span class="red">������� �������������</span>';
	$sumTitle = $filter['zayav_id'] ? _tooltip('�����', 5) : '">';
	return
		'<tr val="'.$r['id'].'"'.($r['deleted'] ? ' class="deleted"' : '').'>'.
			(!empty($filter['owner_id']) || !empty($filter['confirm']) ? '<td class="choice">'._check('money_'.$r['id'], '', isset($filter['ids_ass'][$r['id']])) : '').
			'<td class="sum opl'.$sumTitle.''._sumSpace($r['sum']).
			'<td><span class="type">'._income($r['income_id']).(empty($about) ? '' : ':').'</span> '.$about.
			'<td class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -40).FullDataTime($r['dtime_add']).
		(empty($filter['owner_id']) && empty($filter['ids']) && empty($filter['confirm']) ?
			'<td class="ed"><a href="'.SITE.'/view/cashmemo.php?'.VALUES.'&id='.$r['id'].'" target="_blank" class="img_doc'._tooltip('����������� ���������', -140, 'r').'</a>'.
				(!$r['dogovor_id'] ?
					'<div class="img_del income-del'._tooltip('������� �����', -95, 'r').'</div>'.
					'<div class="img_rest income-rest'._tooltip('������������ �����', -125, 'r').'</div>'
				: '')
		: '');
}//income_unit()

function expense_right() {
	$workers = query_selJson("
		SELECT
			DISTINCT `m`.`worker_id`,
			CONCAT(`u`.`first_name`,' ',`u`.`last_name`)
	    FROM `money` `m`,`vk_user` `u`
	    WHERE `m`.`worker_id`=`u`.`viewer_id`
	      AND `worker_id`>0
	      AND `m`.`deleted`=0
	      AND `m`.`sum`<0
	    ORDER BY `u`.`dtime_add`");
	$invoice = array(0=>'����� ����');
	foreach(_invoice() as $id => $r)
		$invoice[$id] = $r['name'];
	return '<script type="text/javascript">var WORKERS='.$workers.';</script>'.
	'<div class="findHead">���������</div>'.
	'<input type="hidden" id="category">'.
	'<div class="findHead">���������</div>'.
	'<input type="hidden" id="worker">'.
	'<div class="findHead">����</div>'.
	_radio('invoice_id', $invoice, 0, 1).
	'<input type="hidden" id="year">'.
	'<div id="monthList">'.expenseMonthSum().'</div>';
}//expense_right()
function expenseMonthSum($v=array()) {
	$filter = expenseFilter($v);
	$sql = "SELECT
				DISTINCT(DATE_FORMAT(`dtime_add`,'%m')) AS `month`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `deleted`=0
			  AND `sum`<0
			  AND `dtime_add` LIKE '".$filter['year']."%'".
			  ($filter['category'] ? " AND `expense_id`=".$filter['category'] : '').
			  ($filter['worker'] ? " AND `worker_id`=".$filter['worker'] : '').
			  ($filter['invoice_id'] ? " AND `invoice_id`=".$filter['invoice_id'] : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%m')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$res = array();
	while($r = mysql_fetch_assoc($q))
		$res[intval($r['month'])] = abs($r['sum']);
	$send = '';
	for($n = 1; $n <= 12; $n++)
		$send .= _check(
			'c'.$n,
			_monthDef($n).(isset($res[$n]) ? '<span class="sum">'.$res[$n].'</span>' : ''),
			isset($filter['month'][$n]),
			1
		);
	return $send;
}//expenseMonthSum()
function expense() {
	$data = expense_spisok();
	$year = array();
	for($n = 2014; $n <= strftime('%Y'); $n++)
		$year[$n] = $n;
	return
	'<script type="text/javascript">'.
		'var MON_SPISOK='._selJson(_monthDef(0, 1)).','.
			'YEAR_SPISOK='._selJson($year).';'.
	'</script>'.
	'<div class="headName">������ �������� �����������<a class="add">����� ������</a></div>'.
	'<div id="spisok">'.$data['spisok'].'</div>';
}//expense()
function expenseFilter($v) {
	$send = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 30,
		'category' => !empty($v['category']) && preg_match(REGEXP_NUMERIC, $v['category']) ? $v['category'] : 0,
		'worker' => !empty($v['worker']) && preg_match(REGEXP_NUMERIC, $v['worker']) ? $v['worker'] : 0,
		'invoice_id' => !empty($v['invoice_id']) && preg_match(REGEXP_NUMERIC, $v['invoice_id']) ? $v['invoice_id'] : 0,
		'year' => !empty($v['year']) && preg_match(REGEXP_NUMERIC, $v['year']) ? $v['year'] : strftime('%Y'),
		'month' => isset($v['month']) ? $v['month'] : intval(strftime('%m')),
		'del' => isset($v['del']) && preg_match(REGEXP_BOOL, $v['del']) ? $v['del'] : 0
	);
	$mon = array();
	if(!empty($send['month']))
		foreach(explode(',', $send['month']) as $r)
			$mon[$r] = 1;
	$send['month'] = $mon;
	return $send;
}//expenseFilter()
function expense_spisok($filter=array()) {
	$filter = expenseFilter($filter);
	$dtime = array();
	foreach($filter['month'] as $mon => $k)
		$dtime[] = "`dtime_add` LIKE '".$filter['year']."-".($mon < 10 ? 0 : '').$mon."%'";
	$cond = "`deleted`=0
		AND `sum`<0".
		(!empty($dtime) ? " AND (".implode(' OR ', $dtime).")" : '').
		($filter['category'] ? ' AND `expense_id`='.$filter['category'] : '').
		($filter['worker'] ? " AND `worker_id`=".$filter['worker'] : '').
		($filter['invoice_id'] ? " AND `invoice_id`=".$filter['invoice_id'] : '');


	$sql = "SELECT
				COUNT(`id`) AS `all`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE ".$cond;
	$send = mysql_fetch_assoc(query($sql));
	$send['filter'] = $filter;
	if(!$send['all'])
		return $send + array('spisok' => '<div class="_empty">�������� ���.</div>');

	$all = $send['all'];
	$page = $filter['page'];
	$limit = $filter['limit'];
	$start = ($page - 1) * $limit;

	$send['spisok'] = '';
	if($page == 1) {
		$send['spisok'] =
		'<div class="_moneysum">'.
			'�������'._end($all, '�', '�').' <b>'.$all.'</b> �����'._end($all, '�', '�', '��').
			' �� ����� <b>'.abs($send['sum']).'</b> ���.'.
			(empty($dtime) ? ' �� �� �����.' : '').
		'</div>'.
		'<table class="_spisok _money">'.
			'<tr><th>�����'.
				'<th>��������'.
				'<th>����'.
				'<th>';
	}
	$sql = "SELECT *
			FROM `money`
			WHERE ".$cond."
			ORDER BY `dtime_add` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$rashod = array();
	while($r = mysql_fetch_assoc($q))
		$rashod[$r['id']] = $r;
	$rashod = _viewer($rashod);
	foreach($rashod as $r) {
		$dtimeTitle = _tooltip(viewerAdded($r['viewer_id_add']), -40);
		//if($r['deleted'])
			//$dtimeTitle .= "\n".'������: '.$r['viewer_del']."\n".FullDataTime($r['dtime_del']);
		$send['spisok'] .= '<tr'.($r['deleted'] ? ' class="deleted"' : '').' val="'.$r['id'].'">'.
			'<td class="sum"><b>'._sumSpace(abs($r['sum'])).'</b>'.
			'<td>'.($r['expense_id'] ? '<span class="type">'._expense($r['expense_id']).($r['prim'] || $r['worker_id'] ? ':' : '').'</span> ' : '').
				($r['worker_id'] ? '<u>'._viewer($r['worker_id'], 'name').'</u>' : '').
				($r['prim'] && $r['worker_id'] ? ', ' : '').$r['prim'].
			'<td class="dtime'.$dtimeTitle.FullDataTime($r['dtime_add']).
			'<td class="ed r">'.
				//'<div class="img_edit" title="�������������"></div>'.
				'<div class="img_del'._tooltip('������� ������', -52).'</div>'.
				'<div class="img_rest'._tooltip('������������ ������', -67).'</div>';
	}
	if($start + $limit < $all)
		$send['spisok'] .= '<tr class="_next" val="'.($page + 1).'"><td colspan="4"><span>�������� �����...</span>';
	if($page == 1)
		$send['spisok'] .= '</table>';
	return $send;
}//expense_spisok()

function report_month() {
	$sql = "SELECT SUBSTR(`dtime_add`, 1, 4) AS `year`,
				   SUBSTR(`dtime_add`, 6, 2) AS `mon`
	        FROM `zayav`
	        GROUP BY SUBSTR(`dtime_add`, 1, 7)
	        ORDER BY `dtime_add`";
	$q = query($sql);
	$years = array();
	while($r = mysql_fetch_assoc($q))
		$years[$r['year']][] = $r['mon'];

	$saved = query_ass("SELECT `name`,`link` FROM `attach` WHERE `type`='report'");
	$savedDtime = query_ass("SELECT `name`,`dtime_add` FROM `attach` WHERE `type`='report'");

	$curYear = intval(strftime('%Y'));
	$curMon = strftime('%m');
	if(empty($years[$curYear]) || end($years[$curYear]) != $curMon)
		$years[$curYear][] = $curMon;
	$spisok = '';
	foreach($years as $y => $r) {
		if($y < 2014)
			continue;
		$months = '';
		foreach($r as $mon) {
			$mName = _monthDef($mon, 1);
			$s = isset($saved[$y.'-'.$mon]);
			$dtime = $s ? '<div class="dtime'._tooltip('���� ��������', -20).FullDataTime($savedDtime[$y.'-'.$mon], 1).'</div>' : '';
			if($y == 2014 && $mon == 1)
				$td = '<span class="grey">'.$mName.'</span>';
			elseif($s)
				$td = '<a href="'.$saved[$y.'-'.$mon].'">'.$mName.': ������������� �����</a>';
			else
				$td = '<a href="'.SITE.'/view/report_month.php?'.VALUES.'">'.$mName.': ������� �����</a>';
			$months .= '<tr><td>'.$td.'<td>'.$dtime;
		}
		$spisok .= '<a class="yr">'.$y.'</a>'.
				   '<table class="_spisok'.($curYear != $y ? ' dn' : '').'">'.$months.'</table>';
	}
	return
	'<div id="report_month">'.
		'<div class="headName">������������ ������� �� �����</div>'.
		'<div class="_info">'.
			'������ ������������� ����������� 1-�� ����� ������� ������ � ���������� �������������� (�������������). '.
			'���� ����� ��� �� ����������, ���� ����������� ���������� ������� �����.'.
		'</div>'.
		$spisok.
	'</div>';
}//report_month()

function salary() {
	return
		'<div class="headName">���������� �������� �����������</div>'.
		'<div id="spisok">'.salary_spisok().'</div>';
}//salary()
function salary_spisok() {
	$sql = "SELECT
				`u`.`viewer_id`,
				`u`.`rate`,
				CONCAT(`u`.`first_name`,' ',`u`.`last_name`) AS `name`,
				IFNULL(SUM(`m`.`sum`),0) AS `zp`
			FROM `vk_user` AS `u`
				LEFT JOIN `money` AS `m`
				ON `u`.`viewer_id`=`m`.`worker_id`
					AND !`m`.`deleted`
					AND `m`.`worker_id`
					AND `m`.`sum`<0
			WHERE `u`.`worker`=1
			  AND `u`.`viewer_id`!=982006
			GROUP BY `u`.`viewer_id`
			ORDER BY `u`.`dtime_add`";
	$q = query($sql);
	$worker = array();
	while($r = mysql_fetch_assoc($q))
		$worker[$r['viewer_id']] = $r;

	//���������� � ��������
	$sql = "SELECT
 				`e`.`worker_id`,
				IFNULL(SUM(`e`.`sum`),0) AS `ze`
			FROM `zayav_expense` AS `e`,
			 	 `zayav` AS `z`
			WHERE `e`.`worker_id`!=982006
			  AND `e`.`worker_id`
			  AND `e`.`zayav_id`
			  AND `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			GROUP BY `e`.`worker_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$worker[$r['worker_id']]['zp'] += $r['ze'];

	//���������� ��� ������
	$sql = "SELECT
 				`u`.`viewer_id`,
				IFNULL(SUM(`e`.`sum`),0) AS `ze`
			FROM `vk_user` AS `u`
				LEFT JOIN `zayav_expense` AS `e`
				ON `u`.`viewer_id`=`e`.`worker_id`
					AND `e`.`worker_id`
					AND !`e`.`zayav_id`
			WHERE `u`.`worker`=1
			  AND `u`.`viewer_id`!=982006
			GROUP BY `u`.`viewer_id`
			ORDER BY `u`.`dtime_add`";
	$q = query($sql);

	$send = '<table class="_spisok">'.
				'<tr><th>���'.
					'<th>������'.
					'<th>������';
	while($r = mysql_fetch_assoc($q))
		if(!_viewerRules($r['viewer_id'], 'RULES_NOSALARY')) {
			$w = $worker[$r['viewer_id']];
			$start = _viewer($r['viewer_id'], 'salary_balans_start');
			$balans = $start == -1 ? '' : round($w['zp'] + $r['ze'] + $start, 2);
			$send .=
			'<tr><td class="fio"><a href="'.URL.'&p=report&d=salary&id='.$r['viewer_id'].'" class="name">'.$w['name'].'</a>'.
				'<td class="rate">'.($w['rate'] == 0 ? '' : round($w['rate'], 2)).
				'<td class="balans" style="color:#'.($balans < 0 ? 'A00' : '090').'">'.$balans;
		}
	$send .= '</table>';
	return $send;
}//salary_spisok()
function salary_monthList($worker_id, $year, $m) {
	$acc = array();
	$zp = array();
	for($n = 1; $n <= 12; $n++) {
		$acc[$n] = 0;
		$zp[$n] = 0;
	}

	//��������� ���� �������������� � ������ ����������
	$sql = "SELECT
	            DISTINCT(DATE_FORMAT(`mon`,'%m')) AS `mon`,
				SUM(`sum`) AS `sum`
			FROM `zayav_expense`
			WHERE `worker_id`=".$worker_id."
			  AND !`zayav_id`
			  AND `mon` LIKE '".$year."%'
			GROUP BY DATE_FORMAT(`mon`,'%m')";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$acc[intval($r['mon'])] = $r['sum'];

	//��������� ���� ���������� �� �������
	define('BNS', _viewerRules($worker_id, 'RULES_BONUS'));
	$sql = "SELECT
	            DISTINCT(DATE_FORMAT(`z`.`".(BNS ? 'dtime_add' : 'status_day')."`,'%m')) AS `mon`,
				SUM(`e`.`sum`) AS `sum`
			FROM `zayav_expense` `e`,
				 `zayav` `z`
			WHERE `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			  AND `z`.`".(BNS ? 'dtime_add' : 'status_day')."` LIKE '".$year."%'
			  AND `e`.`worker_id`=".$worker_id."
			GROUP BY DATE_FORMAT(`z`.`".(BNS ? 'dtime_add' : 'status_day')."`,'%m')";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$acc[intval($r['mon'])] += $r['sum'];

	//��������� ���� ��
	$sql = "SELECT
	            DISTINCT(DATE_FORMAT(`mon`,'%m')) AS `mon`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE !`deleted`
			  AND `worker_id`=".$worker_id."
			  AND `mon` LIKE '".$year."%'
			GROUP BY DATE_FORMAT(`mon`,'%m')";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zp[intval($r['mon'])] = abs(round($r['sum'], 2));

	$mon = array();
	foreach(_monthDef(0, 1) as $i => $r) {
		$mon[$i] = $r.($acc[$i] || $zp[$i]? '<em>'.$acc[$i].'/'.$zp[$i].'</em>' : '');
	}
	return _radio('salmon', $mon, $m, 1);
}
function salary_worker($worker_id) {
	if(!query_value("SELECT COUNT(*) FROM `vk_user` WHERE `viewer_id`=".$worker_id))
		return '���������� �� ����������.';
	if(_viewerRules($worker_id, 'RULES_NOSALARY'))
		return '� ���������� <u>'._viewer($worker_id, 'name').'</u> �� ����������� ��������.';
	$year = array();
	for($n = 2014; $n <= strftime('%Y'); $n++)
		$year[$n] = $n;
	return
		'<script type="text/javascript">'.
			'var WORKER_ID='.$worker_id.','.
				'MON='.intval(strftime('%m')).','.
				'MON_SPISOK='._selJson(_monthDef(0, 1)).','.
				'YEAR='.strftime('%Y').','.
				'YEAR_SPISOK='._selJson($year).','.
				'RATE='.round(_viewer($worker_id, 'rate'), 2).','.
				'RATE_DAY='._viewer($worker_id, 'rate_day').';'.
		'</script>'.
		'<div class="headName">'._viewer($worker_id, 'name').': ������� �/� �� <em>'._monthDef(strftime('%m'), 1).' '.strftime('%Y').'</em>.</div>'.
		'<div id="spisok">'.salary_worker_spisok(array('worker_id'=>$worker_id)).'</div>';
}//salary_worker()
function salary_worker_spisok($v) {
	$filter = array(
		'worker_id' => !empty($v['worker_id']) && preg_match(REGEXP_NUMERIC, $v['worker_id']) ? intval($v['worker_id']) : 0,
		'mon' => empty($v['mon']) ? strftime('%Y-%m') : $v['mon']
	);

	if(!$filter['worker_id'])
		return '������������ id ����������';

	define('BONUS', _viewerRules($filter['worker_id'], 'RULES_BONUS'));

	$start = _viewer($filter['worker_id'], 'salary_balans_start');
	if($start != -1) {
		$sMoney = query_value("
			SELECT IFNULL(SUM(`sum`),0)
			FROM `money`
			WHERE `worker_id`=".$filter['worker_id']."
			  AND `sum`<0
			  AND !`deleted`");
		$sZExpense = query_value("
			SELECT IFNULL(SUM(`e`.`sum`),0)
			FROM `zayav_expense` `e`,
				 `zayav` `z`
			WHERE `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			  AND `e`.`worker_id`=".$filter['worker_id']);
		$sExpense = query_value("
			SELECT IFNULL(SUM(`sum`),0)
			FROM `zayav_expense`
			WHERE !`zayav_id`
			  AND `worker_id`=".$filter['worker_id']);
		$balans = round($sMoney + $sZExpense + $sExpense + $start, 2);
		$balans = '<b style="color:#'.($balans < 0 ? 'A00' : '090').'">'.$balans.'</b> ���.';
	} else
		$balans = '<a class="start-set">����������</a>';
	$rate = _viewer($filter['worker_id'], 'rate');
	$send =
		'<div class="uhead">'.
			'<h1>'.
				'������: '.($rate != 0 ? '<b>'.round($rate, 2).'</b> ���.<span>('._viewer($filter['worker_id'], 'rate_day').'-� ����� ������)</span>' : '���').
				'<a class="rate-set">�������� ������</a>'.
			'</h1>'.
			'������: '.$balans.
			'<div class="a">'.
				'<a class="up">���������</a> :: '.
				'<a class="down">������ �/�</a> :: '.
				'<a class="deduct">������ �����</a>'.
			'</div>'.
		'</div>'.
		'<div id="salary-sel"></div>';

	$sql = "(SELECT
				'�/�' AS `type`,
				`id`,
				`sum`,
				`prim` AS `about`,
				0 AS `zayav_id`,
				`mon`,
				' zp_del' AS `del`
			FROM `money`
			WHERE !`deleted`
			  AND `worker_id`=".$filter['worker_id']."
			  AND `sum`<0
			  AND `mon` LIKE '".$filter['mon']."%'
		) UNION (
			SELECT
				'����������' AS `type`,
				`e`.`id`,
			    `e`.`sum`,
				'".(BONUS ? '��' : '���:')."' AS `about`,
				`e`.`zayav_id`,
				`z`.`".(BONUS ? 'dtime_add' : 'status_day')."` AS `mon`,
				'' AS `del`
			FROM `zayav_expense` `e`,
				 `zayav` `z`
			WHERE `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			  AND `z`.`".(BONUS ? 'dtime_add' : 'status_day')."` LIKE '".$filter['mon']."%'
			  AND `e`.`worker_id`=".$filter['worker_id']."
			  AND `e`.`sum`>0
			  AND `mon`='0000-00-00'
			GROUP BY `e`.`id`
		) UNION (
			SELECT
				'����������' AS `type`,
				`id`,
			    `sum`,
				`txt` AS `about`,
				0 AS `zayav_id`,
				`mon`,
				' ze_del' AS `del`
			FROM `zayav_expense`
			WHERE `worker_id`=".$filter['worker_id']."
			  AND `sum`>0
			  AND `mon` LIKE '".$filter['mon']."%'
		) UNION (
			SELECT
				'�����' AS `type`,
				`id`,
			    `sum`,
				`txt` AS `about`,
				0 AS `zayav_id`,
				`mon`,
				' ze_del' AS `del`
			FROM `zayav_expense`
			WHERE `worker_id`=".$filter['worker_id']."
			  AND `sum`<0
			  AND `mon` LIKE '".$filter['mon']."%'
		)
		ORDER BY `mon` DESC";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return $send.'<div class="_empty">������ ����.</div>';
	$spisok = array();
	while($r = mysql_fetch_assoc($q)) {
		$key = $r['mon'];
		$key = strtotime($key);
		while(isset($spisok[$key]))
			$key++;
		$spisok[$key] = $r;
	}

	$spisok = _zayavLink($spisok);

	$send .= '<table class="_spisok _money">'.
		'<tr><th>'._check('salary_all').
			'<th>���'.
			'<th>�����'.
			'<th>��������'.
			'<th>';


	krsort($spisok);
	$toAll = ' to-all';
	foreach($spisok as $r) {
		$about = $r['zayav_id'] ? $r['zayav_link'].' '.$r['about'].' '.FullData($r['mon'], 1) : $r['about'];
		if($r['type'] == '�/�') //���� ����������� �����, �� ���������� ���������� ����� �������� �� ����������
			$toAll = '';
		$send .=
			'<tr val="'.$r['id'].'">'.
				'<td class="ch'.$toAll.'">'.($r['type'] != '�/�' ? _check('s'.$r['id']) : '').
				'<td class="type">'.$r['type'].
				'<td class="sum">'.round($r['sum'], 2).
				'<td class="about">'.$about.
				'<td class="ed">'.
					($r['del'] ? '<div class="img_del'.$r['del']._tooltip('�������', -29).'</div>' : '');
	}
	$send .= '</table>';
	return $send;
}//salary_worker_spisok()




// ---===! setup !===--- ������ ��������

function setup() {
	$pages = array(
		'my' => '��� ���������',
		'worker' => '����������',
		'rekvisit' => '��������� �����������',
		'product' => '���� �������',
		'invoice' => '�����',
		'income' => '���� ��������',
		'expense' => '��������� ��������',
		'zayavrashod' => '������� �� ������'
	);

	if(!RULES_WORKER)
		unset($pages['worker']);
	if(!RULES_REKVISIT)
		unset($pages['rekvisit']);
	if(!RULES_PRODUCT)
		unset($pages['product']);
	if(!RULES_INCOME) {
		unset($pages['invoice']);
		unset($pages['income']);
	}
	if(!RULES_ZAYAVRASHOD)
		unset($pages['zayavrashod']);

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
		case 'income': $left = setup_income(); break;
		case 'expense': $left = setup_expense(); break;
		case 'zayavrashod': $left = setup_zayavexpense(); break;
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
			'� ����� ��� ��������� �������� � ��������� � ������� 3-� �����.'.
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
	return
	'<script type="text/javascript">var RULES_VIEWER_ID='.$viewer_id.';</script>'.
	'<div id="setup_rules">'.

		'<table class="utab">'.
			'<tr><td>'.$u['photo'].
				'<td><div class="name">'.$u['name'].'</div>'.
					 ($viewer_id < VIEWER_MAX ? '<a href="http://vk.com/id'.$viewer_id.'" class="vklink" target="_blank">������� �� �������� VK</a>' : '').
		'</table>'.

		'<div class="headName">�����</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">���:<td><input type="text" id="first_name" value="'.$u['first_name'].'" />'.
			'<tr><td class="lab">�������:<td><input type="text" id="last_name" value="'.$u['last_name'].'" />'.
			'<tr><td class="lab">���������:<td><input type="text" id="post" value="'.$u['post'].'" />'.
			'<tr><td><td><div class="vkButton g-save"><button>���������</button></div>'.
		'</table>'.

	(!$u['admin'] && $u['pin'] ?
		'<div class="headName">���-���</div>'.
		'<div class="vkButton pin-clear"><button>�������� ���-���</button></div>'
	: '').

	'<div class="headName">�������������</div>'.
	'<table class="rtab">'.
		'<tr><td class="lab">���������� �������:<td><input type="hidden" id="rules_bonus" value="'.$rule['RULES_BONUS'].'" />'.
		'<tr><td class="lab">���������� �������� ����:<td>'._check('rules_cash', '', $rule['RULES_CASH']).
		'<tr><td class="lab">����� ���������<br />� ���������� ������:<td>'._check('rules_getmoney', '', $rule['RULES_GETMONEY']).
		'<tr><td class="lab">�� ����������<br />� ����������� �/�:<td>'._check('rules_nosalary', '', $rule['RULES_NOSALARY']).
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
						_check('rules_income', '����� � ���� ��������', $rule['RULES_INCOME']).
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
	if(!RULES_INCOME)
		return _norules('��������� ������ ��� ����� ��������');
	return
	'<div id="setup_invoice">'.
		'<div class="headName">���������� �������<a class="add">����� ����</a></div>'.
		'<div class="spisok">'.setup_invoice_spisok().'</div>'.
	'</div>';
}//setup_invoice()
function setup_invoice_spisok() {
	$sql = "SELECT * FROM `invoice` ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']] = $r;

	$sql = "SELECT *
	        FROM `setup_income`
	        WHERE `invoice_id`>0
	        ORDER BY `sort`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$spisok[$r['invoice_id']]['type_name'][] = $r['name'];
		$spisok[$r['invoice_id']]['type_id'][] = $r['id'];
	}

	$send =
	'<table class="_spisok">'.
		'<tr><th class="name">������������'.
			'<th class="type">���� ��������'.
			'<th class="set">';
	foreach($spisok as $id => $r)
		$send .=
		'<tr val="'.$id.'">'.
			'<td class="name">'.
				'<div>'.$r['name'].'</div>'.
				'<pre>'.$r['about'].'</pre>'.
			'<td class="type">'.
				(isset($r['type_name']) ? implode('<br />', $r['type_name']) : '').
				'<input type="hidden" class="type_id" value="'.(isset($r['type_id']) ? implode(',', $r['type_id']) : 0).'" />'.
			'<td class="set">'.
				'<div class="img_edit"></div>';
				//'<div class="img_del"></div>'
	$send .= '</table>';
	return $send;
}//setup_invoice_spisok()

function setup_income() {
	if(!RULES_INCOME)
		return _norules('��������� ������ ��� ����� ��������');
	return
	'<div id="setup_income">'.
		'<div class="headName">��������� ����� ��������<a class="add">��������</a></div>'.
		'<div class="spisok">'.setup_income_spisok().'</div>'.
	'</div>';
}//setup_income()
function setup_income_spisok() {
	$sql = "SELECT `p`.*,
				   COUNT(`m`.`id`) AS `money`
			FROM `setup_income` AS `p`
			  LEFT JOIN `money` AS `m`
			  ON `p`.`id`=`m`.`income_id`
			GROUP BY `p`.`id`
			ORDER BY `p`.`sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$prihod = array();
	while($r = mysql_fetch_assoc($q))
		$prihod[$r['id']] = $r;

	$sql = "SELECT `p`.`id`,
				   COUNT(`m`.`id`) AS `del`
			FROM `setup_income` AS `p`,`money` AS `m`
			WHERE `p`.`id`=`m`.`income_id` AND `m`.`deleted`=1
			GROUP BY `p`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$prihod[$r['id']]['del'] = $r['del'];

	$send =
	'<table class="_spisok">'.
		'<tr><th class="name">������������'.
			'<th class="confirm">�������������<br />�����������<br />�� ����'.
			'<th class="money">���-��<br />��������'.
			'<th class="set">'.
	'</table>'.
	'<dl class="_sort" val="setup_income">';
	foreach($prihod as $id => $r) {
		$money = $r['money'] ? '<b>'.$r['money'].'</b>' : '';
		$money .= isset($r['del']) ? ' <span class="del" title="� ��� ����� ��������">('.$r['del'].')</span>' : '';
		$send .='<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="confirm">'.($r['confirm'] ? '��' : '').
					'<td class="money">'.$money.
					'<td class="set">'.
						'<div class="img_edit'._tooltip('��������', -33).'</div>'.
						(!$r['money'] && $id > 1 ? '<div class="img_del'._tooltip('�������', -29).'</div>' : '').
			'</table>';
	}
	$send .= '</dl>';
	return $send;
}//setup_income_spisok()

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


/*
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
*/