<?php
function _hashRead() {
	$_GET['p'] = isset($_GET['p']) ? $_GET['p'] : 'zayav';
	if(empty($_GET['hash'])) {
		define('HASH_VALUES', false);
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
	setcookie('p', $_GET['p'], time() + 2592000, '/');
	setcookie('d', isset($_GET['d']) ? $_GET['d'] : '', time() + 2592000, '/');
	setcookie('d1', isset($_GET['d1']) ? $_GET['d1'] : '', time() + 2592000, '/');
	setcookie('id', isset($_GET['id']) ? $_GET['id'] : '', time() + 2592000, '/');
}//_hashCookieSet()
function _cacheClear() {
	xcache_unset(CACHE_PREFIX.'setup_global');
	xcache_unset(CACHE_PREFIX.'product');
	xcache_unset(CACHE_PREFIX.'product_sub');
	xcache_unset(CACHE_PREFIX.'income');
	xcache_unset(CACHE_PREFIX.'zayavrashod');
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
		'<script type="text/javascript" src="'.SITE.'/js/G_values.js?'.G_VALUES_VERSION.'"></script>'.

		'</head>'.
		'<body>'.
			'<div id="frameBody">'.
				'<iframe id="frameHidden" name="frameHidden"></iframe>';
}//_header()
function _footer() {
	global $html, $sqlQuery, $sqlCount, $sqlTime;
	if(SA) {
		$d = empty($_GET['d']) ? '' :'&pre_d='.$_GET['d'];
		$d1 = empty($_GET['d1']) ? '' :'&pre_d1='.$_GET['d1'];
		$id = empty($_GET['id']) ? '' :'&pre_id='.$_GET['id'];
		$html .= '<div id="admin">'.
		  //  ($_GET['p'] != 'sa' && !SA_VIEWER_ID ? '<a href="'.URL.'&p=sa&pre_p='.$_GET['p'].$d.$d1.$id.'">Admin</a> :: ' : '').
			'<a class="debug_toggle'.(DEBUG ? ' on' : '').'">�'.(DEBUG ? '�' : '').'������� Debug</a> :: '.
			'<a id="cache_clear">������� ��� ('.VERSION.')</a> :: '.
			'sql <b>'.$sqlCount.'</b> ('.round($sqlTime, 3).') :: '.
			'php '.round(microtime(true) - TIME, 3).' :: '.
			'js <EM></EM>'.
			'</div>'
			.(DEBUG ? $sqlQuery : '');
	}
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
	$html .= '<script type="text/javascript">hashSet({'.implode(',', $gValues).'})</script>'.
		'</div></body></html>';
}//_footer()

function GvaluesCreate() {//����������� ����� G_values.js
	$save = //'function _toSpisok(s){var a=[];for(k in s)a.push({uid:k,title:s[k]});return a}'.
		//'function _toAss(s){var a=[];for(var n=0;n<s.length;a[s[n].uid]=s[n].title,n++);return a}'.
		'var '.
		"\n".'WORKER_SPISOK='.query_selJson("SELECT `viewer_id`,CONCAT(`first_name`,' ',`last_name`) FROM `vk_user`
											 WHERE `worker`=1
											   AND `viewer_id`!=982006
											 ORDER BY `dtime_add`").','.
		"\n".'PRODUCT_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_product` ORDER BY `name`").','.
		 //"\n".'PRODUCT_ASS=_toSpisok(PRODUCT_ASS),'.
		"\n".'PRIHOD_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_income` ORDER BY `sort`").','.
		"\n".'ZAYAVRASHOD_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_zayavrashod` ORDER BY `sort`").','.
		"\n".'ZAYAVRASHOD_TXT_ASS='.query_ptpJson("SELECT `id`,`show_txt` FROM `setup_zayavrashod` WHERE `show_txt`=1").','.
		"\n".'ZAYAVRASHOD_WORKER_ASS='.query_ptpJson("SELECT `id`,`show_worker` FROM `setup_zayavrashod` WHERE `show_worker`=1").','.
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
		"\n".'ZAMER_DURATION='._selJson(_zamerDuration()).',';

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
					'invoice_id' => $r['invoice_id']
				);
			xcache_set($key, $arr, 86400);
		}
		if(!defined('INCOME_LOADED')) {
			foreach($arr as $id => $r) {
				define('INCOME_'.$id, $r['name']);
				define('INCOME_INVOICE_'.$id, $r['invoice_id']);
			}
			define('INCOME_0', '');
			define('INCOME_INVOICE_0', 0);
			define('INCOME_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'invoice')
		return constant('INCOME_INVOICE_'.$type_id);
	return constant('INCOME_'.$type_id);
}//_income()
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
			$sql = "SELECT * FROM `setup_zayavrashod` ORDER BY `sort`";
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
//	_remindActiveSet();
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
			'name' => '�����������',
			'page' => 'remind',
			'show' => 1
		),
		array(
			'name' => '������',
			'page' => 'report',
			'show' => 1
		),
		array(
			'name' => '���������',
			'page' => 'setup',
			'show' => RULES_SETUP
		)
	);

	$send = '<div id="mainLinks">';
	foreach($links as $l)
		if($l['show'])
			$send .= '<a href="'.URL.'&p='.$l['page'].'"'.($l['page'] == $_GET['p'] ? ' class="sel"' : '').'>'.$l['name'].'</a>';
	$send .= pageHelpIcon().'</div>';

	$html .= $send;
}//_mainLinks()

function rulesList($v=false) {
	$rules = array(
		'RULES_APPENTER' => 1,      // ��������� ���� � ����������
		'RULES_SETUP' => 1,         // ���������� �����������
		'RULES_WORKER' => 1,	    // ����������
		'RULES_REKVISIT' => 1,      // ��������� �����������
		'RULES_PRODUCT' => 1,       // ���� �������
		'RULES_INCOME' => 1,        // ���� ��������
		'RULES_ZAYAVRASHOD' => 1,   // ������� �� ������
		'RULES_HISTORYSHOW' => 1    // ����� ������ ������� ��������
	);
	return $v ? isset($rules[$v]) : $rules;
}//rulesList()
function workerRulesArray($rules, $noList=false) {
	$send = array();
	foreach(explode(',', $rules) as $name)
		$send[$name] = 1;
	if(!$noList)
		foreach(rulesList() as $name => $v)
			$send[$name] = isset($send[$name]) ? 1 : 0;
	unset($send['']);
	return $send;
}//workerRulesArray()
function _norules($txt=false) {
	return '<div class="norules">'.($txt ? '<b>'.$txt.'</b>: �' : '�').'����������� ����.</div>';
}//_norules()

function numberToWord($num, $firstSymbolUp=false) {
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

function translit($str) {
	$list = array(
		'�' => 'A',
		'�' => 'B',
		'�' => 'V',
		'�' => 'G',
		'�' => 'D',
		'�' => 'E',
		'�' => 'J',
		'�' => 'Z',
		'�' => 'I',
		'�' => 'Y',
		'�' => 'K',
		'�' => 'L',
		'�' => 'M',
		'�' => 'N',
		'�' => 'O',
		'�' => 'P',
		'�' => 'R',
		'�' => 'S',
		'�' => 'T',
		'�' => 'U',
		'�' => 'F',
		'�' => 'H',
		'�' => 'TS',
		'�' => 'CH',
		'�' => 'SH',
		'�' => 'SCH',
		'�' => '',
		'�' => 'YI',
		'�' => '',
		'�' => 'E',
		'�' => 'YU',
		'�' => 'YA',
		'�' => 'a',
		'�' => 'b',
		'�' => 'v',
		'�' => 'g',
		'�' => 'd',
		'�' => 'e',
		'�' => 'j',
		'�' => 'z',
		'�' => 'i',
		'�' => 'y',
		'�' => 'k',
		'�' => 'l',
		'�' => 'm',
		'�' => 'n',
		'�' => 'o',
		'�' => 'p',
		'�' => 'r',
		'�' => 's',
		'�' => 't',
		'�' => 'u',
		'�' => 'f',
		'�' => 'h',
		'�' => 'ts',
		'�' => 'ch',
		'�' => 'sh',
		'�' => 'sch',
		'�' => 'y',
		'�' => 'yi',
		'�' => '',
		'�' => 'e',
		'�' => 'yu',
		'�' => 'ya',
		' ' => '_',
		'�' => 'N'
	);
	return strtr($str, $list);
}

function _calendarFilter($data=array()) {
	$year = empty($data['year']) ? strftime('%Y') : $data['year'];
	$month = empty($data['month']) ? strftime('%m') : ($data['month'] < 10 ? 0 : '').$data['month'];
	$days = empty($data['days']) ? array() : $data['days'];

	$send = '<div class="_calendarFilter">'.
				'<table class="month">'.
					'<tr class="week-name"><td>��<td>��<td>��<td>��<td>��<td>��<td>��';

	$unix = strtotime($year.'-'.$month.'-01');
	$dayCount = date('t', $unix);   // ���������� ���� � ������
	$week = date('w', $unix);       // ����� ������� ��� ������
	if(!$week)
		$week = 7;

	$curUnix = strtotime(strftime('%Y-%m-%d')); // ������� ���� ��� ��������� ��������� ����

	$curMonth = $year == strftime('%Y') && $month == strftime('%m');
	$curDay = round(strftime('%d'));

	$send .= '<tr>';
	for($n = $week; $n > 1; $n--, $send .= '<td>'); // ������� ������ �����, ���� ������ ���� ������ �� �����������
	for($n = 1; $n <= $dayCount; $n++) {
		$cur = $curMonth && $curDay == $n ? ' cur' : '';
		$on = empty($days[$year.'-'.$month.'-'.($n < 10 ? '0' : '').$n]) ? '' : ' on';
		$old = $unix + $n * 86400 <= $curUnix ? ' old' : '';
		$val = $on ? ' val="'.$year.'-'.$month.'-'.($n < 10 ? '0' : '').$n.'"' : '';
		$send .= '<td class="d '.$cur.$on.$old.'"'.$val.'>'.$n;
		$week++;
		if($week > 7)
			$week = 1;
		if($week == 1)
			$send .= '<tr>';
	}
	$send .= '</table></div>';

	return $send;
}//_calendarFilter()


// ---===! client !===--- ������ ��������

function _clientLink($arr, $fio=0) {//���������� ����� � ������ ������� � ������ ��� ������� �� id
	$clientArr = array(is_array($arr) ? 0 : $arr);
	if(is_array($arr)) {
		$ass = array();
		foreach($arr as $r) {
			$clientArr[$r['client_id']] = $r['client_id'];
			if($r['client_id'])
				$ass[$r['client_id']][] = $r['id'];
		}
		unset($clientArr[0]);
	}
	if(!empty($clientArr)) {
		$sql = "SELECT
					`id`,
					`fio`,
					`deleted`
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
	if(!preg_match(REGEXP_WORDFIND, win1251($v['fast'])))
		$v['fast'] = '';
	if(!preg_match(REGEXP_BOOL, $v['dolg']))
		$v['dolg'] = 0;
	$filter = array(
		'fast' => win1251(htmlspecialchars(trim($v['fast']))),
		'dolg' => intval($v['dolg'])
	);
	return $filter;
}//clientFilter()
function client_data($page=1, $filter=array()) {
	$cond = "`deleted`=0";
	$reg = '';
	$regEngRus = '';
	if(!empty($filter['fast'])) {
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
		if(isset($filter['dolg']) && $filter['dolg'] == 1)
			$cond .= " AND `balans`<0";
	}
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `client` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0) {
		$send['spisok'] = '<div class="_empty">�������� �� �������.</div>';
		return $send;
	}
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

	$send['spisok'] = '';
	foreach($spisok as $r)
		$send['spisok'] .= '<div class="unit'.(isset($r['comm']) ? ' i' : '').'">'.
			($r['balans'] ? '<div class="balans">������: <b style=color:#'.($r['balans'] < 0 ? 'A00' : '090').'>'.$r['balans'].'</b></div>' : '').
			'<table>'.
				'<tr><td class="label">���:<td><a href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>'.
				($r['telefon'] ? '<tr><td class="label">�������:<td>'.$r['telefon'] : '').
				(isset($r['adres']) ? '<tr><td class="label">�����:<td>'.$r['adres'] : '').
				(isset($r['zayav_count']) ? '<tr><td class="label">������:<td>'.$r['zayav_count'] : '').
			'</table>'.
		'</div>';
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .= '<div class="ajaxNext" val="'.($page + 1).'"><span>�������� ��� '.$c.' ������'._end($c, '�', '�', '��').'</span></div>';
	}
	return $send;
}//client_data()
function client_list($data) {
	return
	'<div id="client">'.
		'<div id="find"></div>'.
		'<div class="result">'.client_count($data['all']).'</div>'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate"><a>����� ������</a></div>'.
					'<div class="filter">'.
						_check('dolg', '��������').
					'</div>'.
		'</table>'.
	'</div>';
}//client_list()
function client_count($count, $dolg=0) {
	if($dolg)
		$dolg = abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE `deleted`=0 AND `balans`<0 LIMIT 1"));
	return ($count > 0 ?
		'������'._end($count, ' ', '� ').$count.' ������'._end($count, '', '�', '��').
		($dolg ? '<span class="dolg_sum">(����� ����� ����� = <b>'._sumSpace($dolg).'</b> ���.)</span>' : '')
		:
		'�������� �� �������');
}//client_count()

function clientInfoGet($client) {
	return
		'<div class="fio">'.$client['fio'].'</div>'.
		'<table class="cinf">'.
			'<tr><td class="label">�������:<td>'.$client['telefon'].
			'<tr><td class="label">�����:  <td>'.$client['adres'].
			'<tr><td class="label">������: <td><b style=color:#'.($client['balans'] < 0 ? 'A00' : '090').'>'.$client['balans'].'</b>'.
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
	$sql = "SELECT * FROM `client` WHERE `deleted`=0 AND `id`=".$client_id;
	if(!$client = mysql_fetch_assoc(query($sql)))
		return _noauth('������� �� ����������');

	$commCount = query_value("SELECT COUNT(`id`)
							  FROM `vk_comment`
							  WHERE `status`=1
								AND `parent_id`=0
								AND `table_name`='client'
								AND `table_id`=".$client_id);

	$money = income_spisok(1, array('client_id'=>$client_id,'limit'=>15));

   // $remindData = remind_data(1, array('client'=>$client_id));

	if(RULES_HISTORYSHOW)
		$histCount = query_value("SELECT COUNT(`id`) FROM `history` WHERE `client_id`=".$client_id);

	$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `client_id`=".$client_id;
	$q = query($sql);
	$zopl = array();
	$zayav = array();
	while($r = mysql_fetch_assoc($q)) {
		$zopl[$r['id']] = array(
			'title' => '������ �'.$r['id'],
			'content' => '������ �'.$r['id']
		);
		$zayav[$r['id']] = $r;
	}

	$zayavCount = count($zayav);
	$zayavSpisok = '';
	if($zayavCount) {
		$zayav = _dogNomer($zayav);
		$zayav = zayav_product_array($zayav);
		foreach($zayav as $r) {
			if(!$r['dogovor_id'] && $r['dogovor_require'])
				$zayavSpisok .= dogovor_unit($r, 1);
			elseif($r['zakaz_status'])
				$zayavSpisok .= zakaz_unit($r, 1);
			elseif($r['zamer_status'] == 1 || $r['zamer_status'] == 3)
				$zayavSpisok .= zamer_unit($r, 1);
			elseif($r['set_status'])
				$zayavSpisok .= set_unit($r, 1);
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
						'<a class="cedit">�������������</a>'.
						'<a class="zayav_add"><b>����� ������</b></a>'.
						'<a class="oplata-add">������ �����</a>'.
						'<a class="cdel">������� �������</a>'.
					'</div>'.
		'</table>'.

		'<div id="dopLinks">'.
			'<a class="link sel" val="zayav">������'.($zayavCount ? ' ('.$zayavCount.')' : '').'</a>'.
			'<a class="link" val="money">�������'.($money['all'] ? ' ('.$money['all'].')' : '').'</a>'.
		//	'<a class="link" val="remind">�������'.(!empty($remindData) ? ' ('.$remindData['all'].')' : '').'</a>'.
			'<a class="link" val="comm">�������'.($commCount ? ' ('.$commCount.')' : '').'</a>'.
			(RULES_HISTORYSHOW ? '<a class="link" val="hist">�������'.($histCount ? ' ('.$histCount.')' : '').'</a>' : '').
		'</div>'.

		'<table class="tabLR">'.
			'<tr><td class="left">'.
					'<div id="zayav_spisok">'.($zayavSpisok ? $zayavSpisok : '<div class="_empty">������ ���</div>').'</div>'.
					'<div id="income_spisok">'.$money['spisok'].'</div>'.
					'<div id="remind_spisok">'.(!empty($remindData) ? report_remind_spisok($remindData) : '<div class="_empty">������� ���.</div>').'</div>'.
					'<div id="comments">'._vkComment('client', $client_id).'</div>'.
					(RULES_HISTORYSHOW ? '<div id="histories">'.history_spisok(1, array('client_id'=>$client_id)).'</div>' : '').
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
	foreach($arr as $r)
		if($r['zayav_id']) {
			$ids[$r['zayav_id']] = 1;
			$arrIds[$r['zayav_id']][] = $r['id'];
		}
	if(empty($ids))
		return $arr;
	$sql = "SELECT * FROM `zayav` WHERE `id` IN (".implode(',', array_keys($ids)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($arrIds[$r['id']] as $id)
			$arr[$id]['zayav_link'] = '<a'.($r['deleted'] ? ' class="deleted" title="������ �������"' : '').' href="'.URL.'&p=zayav&d=info&id='.$r['id'].'">�'.$r['id'].'</a>';
	return $arr;
}//_zayavLink()
function _zayavStatus($id=false) {
	$arr = array(
		'0' => array(
			'name' => '����� ������',
			'color' => 'ffffff'
		),
		'1' => array(
			'name' => '� ��������',
			'color' => 'E8E8FF'
		),
		'2' => array(
			'name' => '���������',
			'color' => 'CCFFCC'
		),
		'3' => array(
			'name' => '������',
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
	foreach($arr as $r) {
		$send .= '<tr><td>'._product($r['product_id']).
			($r['product_sub_id'] ? ' '._productSub($r['product_sub_id']) : '').':'.
			'<td>'.$r['count'].' ��.';
		$json[] = '['.$r['product_id'].','.$r['product_sub_id'].','.$r['count'].']';
		$array[] = array($r['product_id'], $r['product_sub_id'], $r['count']);
		$cash[] = _product($r['product_id']).($r['product_sub_id'] ? ' '._productSub($r['product_sub_id']) : '');
	}
	$send .= '</table>';
	switch($type) {
		default:
		case 'html': return $send;
		case 'json': return implode(',', $json);
		case 'array': return $array;
		case 'cash': return implode('<br />', $cash);
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
	$sql = "SELECT * FROM `zayav_rashod` WHERE `zayav_id`=".$zayav_id." ORDER BY `id`";
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
							(_zayavRashod($r['category_id'], 'worker') && $r['worker_id'] ? _viewer($r['worker_id'], 'link') : '').
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
				 '<tr><td colspan="2" class="itog">�������:<td class="sum">'.$z['expense_left'].' �.';
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
		$_GET['d'] = empty($_COOKIE['zayav_dop']) ? 'zamer' : $_COOKIE['zayav_dop'];
	setcookie('zayav_dop', $_GET['d'] , time() + 846000, "/");
	switch(@$_GET['d']) {
		default:
			$_GET['d'] = 'zamer';
		case 'zakaz':
			$right = '<div id="buttonCreate" class="zakaz_add"><a>����� �����</a></div>';
			$data = zakaz_spisok();
			$result = $data['result'];
			$spisok = $data['spisok'];
			break;
		case 'zamer':
			$right = '<div id="buttonCreate" class="zamer_add"><a>����� �����</a></div>'.
					 '<a class="zamer_table">������� �������</a>';
			$data = zamer_spisok();
			$result = $data['result'];
			$spisok = $data['spisok'];
			break;
		case 'dog':
			$right = '';
			$data = dogovor_spisok();
			$result = $data['result'];
			$spisok = $data['spisok'];
			break;
		case 'set':
			$right = '<div id="buttonCreate" class="set_add"><a>����� ������<br />�� ���������</a></div>';
			$data = set_spisok();
			$result = $data['result'];
			$spisok = $data['spisok'];
			break;
	}
	$zakazCount = query_value("SELECT COUNT(`id`) AS `all`
	                         FROM `zayav`
	                         WHERE `deleted`=0
	                           AND `dogovor_require`=0
	                           AND `zakaz_status`=1
							 LIMIT 1");
	$zamerCount = query_value("SELECT COUNT(`id`) AS `all`
							   FROM `zayav`
							   WHERE `deleted`=0
							     AND `dogovor_require`=0
							     AND (`zamer_status`=1 OR `zamer_status`=3)
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
	'<div id="zayav">'.
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
				'<td class="right">'.$right.
		'</table>'.
	'</div>';
}//zayav()

function _zakazStatus($id) {
	$arr = array(
		'0' => '����� ������',
		'1' => '����� ������� ����������',
		'2' => '����� ��������',
		'3' => '����� ������'
	);
	return $arr[$id];
}//_zakazStatus()
function zakazFilter($v) {
	$filter = array(
		'find' => win1251(htmlspecialchars(trim($v['find'])))
	);
	return $filter;
}//zakazFilter()
function zakaz_spisok($page=1, $filter=array()) {
	$cond = "`deleted`=0
		 AND `dogovor_require`=0
	 	 AND `zakaz_status`>0";

	if(empty($filter['desc']))
		$filter['desc'] = 'DESC';
	if(isset($filter['client']) && $filter['client'] > 0)
		$cond .= " AND `client_id`=".$filter['client'];

	$clear = '<a class="filter_clear">������� ������� ������</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'������� �� �������',
			'spisok' => '<div class="_empty">������� �� �������.</div>'
		);

	$send['result'] = $clear.'�������'._end($send['all'], '', '�').' '.$send['all'].' �����'._end($send['all'], '', '�', '��');

	$limit=20;
	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `id` ".$filter['desc']."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$zayav = array();
	while($r = mysql_fetch_assoc($q))
		$zayav[$r['id']] = $r;

	$zayav = _clientLink($zayav);
	$zayav = _dogNomer($zayav);
	$zayav = zayav_product_array($zayav);

	$send['spisok'] = '';
	foreach($zayav as $r)
		$send['spisok'] .= zakaz_unit($r);
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="ajaxNext" id="zakaz_next" val="'.($page + 1).'">'.
				'<span>�������� ��� '.$c.' �����'._end($c, '', '�', '��').'</span>'.
			'</div>';
	}
	return $send;
}//zakaz_data()
function zakaz_unit($r, $no_client=0) {
	$dop = $r['nomer_vg'] ? ' ��'.$r['nomer_vg'] :
		  ($r['nomer_g'] ? ' �'.$r['nomer_g'] :
		  ($r['nomer_d'] ? ' �'.$r['nomer_d'] : ''));
	return
		'<div class="zayav_unit" style="background-color:#'._statusColor($r['zakaz_status']).'" val="'.$r['id'].'">'.
			'<div class="dtime">#'.$r['id'].'<br />'.FullData($r['dtime_add'], 1).'</div>'.
			'<a class="name">�����'.$dop.($r['dogovor_id'] ? ' <span>(������� '.$r['dogovor_nomer'].')</span>' : '').'</a>'.
			'<table class="ztab">'.
				($no_client ? '' : '<tr><td class="label">������:<td>'.$r['client_link']).
				'<tr><td class="label top">�������:<td>'.(isset($r['product']) ? zayav_product_spisok($r['product']) : '').$r['zakaz_txt'].
			'</table>'.
		'</div>';
}//zamer_unit()

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
function zamerFilter($v) {
	$filter = array(
		'find' => win1251(htmlspecialchars(trim($v['find'])))
	);
	return $filter;
}//zamerFilter()
function zamer_spisok($page=1, $filter=array()) {
	$cond = "`deleted`=0
		 AND `dogovor_require`=0
		 AND (`zamer_status`=1 OR `zamer_status`=3)";

	if(empty($filter['desc']))
		$filter['desc'] = 'DESC';
	if(isset($filter['client']) && $filter['client'] > 0)
		$cond .= " AND `client_id`=".$filter['client'];

	$clear = '<a class="filter_clear">������� ������� ������</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'������� �� �������',
			'spisok' => '<div class="_empty">������� �� �������.</div>'
		);

	$send['result'] = $clear.'�������'._end($send['all'], '', '�').' '.$send['all'].' �����'._end($send['all'], '', '�', '��');

	$limit=20;
	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `id` ".$filter['desc']."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$zayav = array();
	while($r = mysql_fetch_assoc($q)) {
		if(isset($zayav_id) && $zayav_id == $r['id'])
			continue;
		$zayav[$r['id']] = $r;
	}

	$zayav = _clientLink($zayav);
	$zayav = zayav_product_array($zayav);

	$send['spisok'] = '';
	foreach($zayav as $r)
		$send['spisok'] .= zamer_unit($r);
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="ajaxNext" id="zamer_next" val="'.($page + 1).'">'.
				'<span>�������� ��� '.$c.' �����'._end($c, '', '�', '��').'</span>'.
			'</div>';
	}
	return $send;
}//zayav_data()
function zamer_unit($r, $no_client=0) {
	return
	'<div class="zayav_unit" style="background-color:#'._statusColor($r['zamer_status']).'" val="'.$r['id'].'">'.
		'<div class="dtime">#'.$r['id'].'<br />'.FullData($r['dtime_add'], 1).'</div>'.
		'<a class="name">�����</a>'.
		'<table class="ztab">'.
			($no_client ? '' : '<tr><td class="label">������:<td>'.$r['client_link']).
			'<tr><td class="label top">�����:<td>'.$r['adres'].
			'<tr><td class="label top">�������:<td>'.zayav_product_spisok($r['product']).
	'</table>'.
	'</div>';
}//zamer_unit()

function _dogNomer($arr) {//���������� � ������ ������ �� ��������, ����������� �� dogovor_id
	$ids = array(); // �������� ���������
	$arrIds = array();
	foreach($arr as $r)
		if($r['dogovor_id']) {
			$ids[$r['dogovor_id']] = 1;
			$arrIds[$r['dogovor_id']][] = $r['id'];
		}
	if(empty($ids))
		return $arr;
	$sql = "SELECT * FROM `zayav_dogovor` WHERE `id` IN (".implode(',', array_keys($ids)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($arrIds[$r['id']] as $id) {
			$d = explode('-', $r['data_create']);
			$arr[$id]['dogovor_nomer'] = '�'.$r['nomer'];
			$arr[$id]['dogovor_n'] = $r['nomer'];
			$arr[$id]['dogovor_data'] = $d[2].'/'.$d[1].'/'.$d[0].' �.';
			$arr[$id]['dogovor_sum'] = $r['sum'];
			$arr[$id]['dogovor_avans'] = $r['avans'];
		}
	return $arr;
}//_dogNomer()
function dogovorFilter($v) {
	$filter = array(
		'find' => win1251(htmlspecialchars(trim($v['find'])))
	);
	return $filter;
}//dogovorFilter()
function dogovor_spisok($page=1, $filter=array()) {
	$cond = "`deleted`=0
		 AND `dogovor_id`=0
		 AND `dogovor_require`=1";

	if(empty($filter['desc']))
		$filter['desc'] = 'DESC';
	if(isset($filter['client']) && $filter['client'] > 0)
		$cond .= " AND `client_id`=".$filter['client'];

	$clear = '<a class="filter_clear">������� ������� ������</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'������ �� ���������� �������� �� �������',
			'spisok' => '<div class="_empty">������ �� ���������� �������� �� �������.</div>'
		);

	$send['result'] = $clear.'�������'._end($send['all'], '', '�').' '.$send['all'].' ����'._end($send['all'], '��', '��', '��');

	$limit=20;
	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `id` ".$filter['desc']."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$zayav = array();
	while($r = mysql_fetch_assoc($q)) {
		if(isset($zayav_id) && $zayav_id == $r['id'])
			continue;
		$zayav[$r['id']] = $r;
	}

	$zayav = _clientLink($zayav);

	$send['spisok'] = '';
	foreach($zayav as $r)
		$send['spisok'] .= dogovor_unit($r);
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="ajaxNext" id="dog_next" val="'.($page + 1).'">'.
				'<span>�������� ��� '.$c.' ����'._end($c, '��', '��', '��').'</span>'.
			'</div>';
	}
	return $send;
}//dogovor_spisok()
function dogovor_unit($r, $no_client=0) {
	return
	'<div class="zayav_unit" val="'.$r['id'].'">'.
		'<div class="dtime">#'.$r['id'].'<br />'.FullData($r['dtime_add'], 1).'</div>'.
		'<a class="name">'.
			'������� �� �������� '.
			'<span>('.($r['set_status'] ? '���������' : ($r['zakaz_status'] ? '�����' : '�����')).')</span>'.
		'</a>'.
		'<table class="ztab">'.
			($no_client ? '' : '<tr><td class="label">������:<td>'.$r['client_link']).
			'<tr><td class="label top">�����:<td>'.$r['adres'].
		'</table>'.
	'</div>';
}//zamer_unit()

function _setStatus($id) {
	$arr = array(
		'0' => '����� ������',
		'1' => '������� ���������',
		'2' => '��������� ���������',
		'3' => '��������� ��������'
	);
	return $arr[$id];
}//_zakazStatus()
function setFilter($v) {
	$filter = array(
		'find' => win1251(htmlspecialchars(trim($v['find'])))
	);
	return $filter;
}//setFilter()
function set_spisok($page=1, $filter=array()) {
	$cond = "`deleted`=0
	     AND `dogovor_require`=0
	     AND `set_status`>0";

	if(empty($filter['desc']))
		$filter['desc'] = 'DESC';
	if(isset($filter['client']) && $filter['client'] > 0)
		$cond .= " AND `client_id`=".$filter['client'];

	$clear = '<a class="filter_clear">������� ������� ������</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'��������� �� �������',
			'spisok' => '<div class="_empty">��������� �� �������.</div>'
		);

	$send['result'] = $clear.'�������'._end($send['all'], '', '�').' '.$send['all'].' ����'._end($send['all'], '��', '��', '��');

	$limit=20;
	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `id` ".$filter['desc']."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$zayav = array();
	while($r = mysql_fetch_assoc($q)) {
		if(isset($zayav_id) && $zayav_id == $r['id'])
			continue;
		$zayav[$r['id']] = $r;
	}

	$zayav = _clientLink($zayav);
	$zayav = _dogNomer($zayav);

	$send['spisok'] = '';
	foreach($zayav as $r)
		$send['spisok'] .= set_unit($r);
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="ajaxNext" id="set_next" val="'.($page + 1).'">'.
				'<span>�������� ��� '.$c.' ����'._end($c, '��', '��', '��').'</span>'.
			'</div>';
	}
	return $send;
}//set_spisok()
function set_unit($r, $no_client=0) {
	$dop = $r['nomer_vg'] ? ' ��'.$r['nomer_vg'] :
		  ($r['nomer_g'] ? ' �'.$r['nomer_g'] :
		  ($r['nomer_d'] ? ' �'.$r['nomer_d'] : ''));
	return
	'<div class="zayav_unit" style="background-color:#'._statusColor($r['set_status']).'" val="'.$r['id'].'">'.
		'<div class="dtime">#'.$r['id'].'<br />'.FullData($r['dtime_add'], 1).'</div>'.
		'<a class="name">���������'.$dop.($r['dogovor_id'] ? ' <span>(������� '.$r['dogovor_nomer'].')</span>' : '').'</a>'.
		'<table class="ztab">'.
			($no_client ? '' : '<tr><td class="label">������:<td>'.$r['client_link']).
			'<tr><td class="label top">�����:<td>'.$r['adres'].
		'</table>'.
	'</div>';
}//zamer_unit()

function zayavDogovorList($zayav_id) {//������ ��������� ��� ������
	$sql = "SELECT * FROM `zayav_dogovor` WHERE `zayav_id`=".$zayav_id;
	$q = query($sql);
	$send = '';
	while($r = mysql_fetch_assoc($q)) {
		$d = explode('-', $r['data_create']);
		$data = $d[2].'/'.$d[1].'/'.$d[0];
		$reason = $r['reason'] ? "\n".$r['reason'] : '';
		$title = '�� '.$data.' �. �� ����� '.$r['sum'].' ���.'.$reason;
		$del = $r['deleted'] ? ' d' : '';
		$send .= '<b class="dogn'.$del.'" title="'.$title.'">�'.$r['nomer'].'</b> '.
			'<a class="img_word" href="'.LINK_DOGOVOR.$r['link'].'.doc" title="�����������"></a>';
	}
	return $send;
}//zayavDogovorList()
function zayav_info($zayav_id) {
	$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$zayav_id." LIMIT 1";
	if(!$z = mysql_fetch_assoc(query($sql)))
		return _noauth('������ �� ����������.');

	if(!$z['dogovor_id'] && $z['dogovor_require'])
		$type = 'dog';
	elseif($z['zakaz_status'])
		$type = 'zakaz';
	elseif($z['zamer_status'] == 1 || $z['zamer_status'] == 3)
		$type = 'zamer';
	elseif($z['set_status'])
		$type = 'set';
	else return _noauth('����������� ��������� ������');

	setcookie('zayav_dop', $type, time() + 846000, "/");
	define('ZAKAZ', $type == 'zakaz');
	define('ZAMER', $type == 'zamer');
	define('DOG', $type == 'dog');
	define('SET', $type == 'set');

	switch($type) {
		case 'zakaz':
			$head = '����� �'.$z['id'];
			$status_name = _zakazStatus($z['zakaz_status']);
			break;
		case 'zamer':
			$head = '����� �'.$z['id'];
			$status_name = _zamerStatus($z['zamer_status']);
			break;
		case 'dog':
			$head = '�������� ���������� �������� - '.($z['set_status'] ? '���������' : ($z['zakaz_status'] ? '�����' : '�����')).' �'.$z['id'];
			$status_name = $z['zamer_status'] ? _zamerStatus($z['zamer_status']) : '';
			break;
		case 'set':
			$head = '��������� �'.$z['id'];
			$status_name = _setStatus($z['set_status']);
			break;
	}

	$sql = "SELECT * FROM `client` WHERE `deleted`=0 AND `id`=".$z['client_id']." LIMIT 1";
	$client = mysql_fetch_assoc(query($sql));

	$dog = $z['dogovor_id'] ? query_assoc("SELECT * FROM `zayav_dogovor` WHERE `id`=".$z['dogovor_id']) : array();
	$dogSpisok = $z['dogovor_id'] ? zayavDogovorList($z['id']).'<a class="reneg">�������������</a>' : '<input type="hidden" id="dogovor_action" />';

	$d = explode(' ', $z['zamer_dtime']);
	$time = explode(':', $d[1]);

	$accSum = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE `deleted`=0 AND `zayav_id`=".$zayav_id);
	$rashod = zayav_rashod_spisok($z['id'], 'all');

	return
	'<script type="text/javascript">'.
		'var ZAYAV={'.
			'id:'.$z['id'].','.
			'head:"'.$head.'",'.
			'client_fio:"'.$client['fio'].'",'.
			'product:['.zayav_product_spisok($z['id'], 'json').'],'.
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
			'nomer:'.(empty($dog) ? _maxSql('zayav_dogovor', 'nomer') : $dog['nomer']).','.
			'data_create:"'.(empty($dog) ? '' : $dog['data_create']).'",'.
			'fio:"'.(empty($dog) ? $client['fio'] : $dog['fio']).'",'.
			'adres:"'.(empty($dog) ? $client['adres'] : $dog['adres']).'",'.
			'pasp_seria:"'.(empty($dog) ? $client['pasp_seria'] : $dog['pasp_seria']).'",'.
			'pasp_nomer:"'.(empty($dog) ? $client['pasp_nomer'] : $dog['pasp_nomer']).'",'.
			'pasp_adres:"'.(empty($dog) ? $client['pasp_adres'] : $dog['pasp_adres']).'",'.
			'pasp_ovd:"'.(empty($dog) ? $client['pasp_ovd'] : $dog['pasp_ovd']).'",'.
			'pasp_data:"'.(empty($dog) ? $client['pasp_data'] : $dog['pasp_data']).'",'.
			'sum:"'.(empty($dog) ? '' : $dog['sum']).'",'.
			'avans:"'.(empty($dog['avans']) ? '' : $dog['avans']).'"'.
		'},'.
		'OPL={'.
			'from:"zayav",'.
			'client_id:'.$z['client_id'].','.
			'client_fio:"'.addslashes(_clientLink($z['client_id'])).'",'.
			'zayav_id:'.$z['id'].
		'};'.
	'</script>'.
	'<div class="zayav-info '.$type.'">'.
		'<div id="dopLinks">'.
			'<a class="delete">������� ������</a>'.
			'<a class="link sel zinfo">����������</a>'.
			'<a class="link '.$type.'_edit">��������������</a>'.
(ZAKAZ || SET ?
			'<a class="link acc-add">���������</a>'.
			'<a class="link oplata-add">������ �����</a>'
: '').
			(RULES_HISTORYSHOW ? '<a class="link hist">�������</a>' : '').
		'</div>'.
		'<div class="content">'.
			'<TABLE class="tabmain"><TR>'.
				'<TD class="mainleft">'.
					'<div class="headName">'.$head.'</div>'.
					'<table class="tabInfo">'.
						'<tr><td class="label">������:<td>'._clientLink($z['client_id']).
						'<tr><td class="label top">�������:<td>'.zayav_product_spisok($z['id']).$z['zakaz_txt'].
			   (ZAMER ? '<tr><td class="label">����� ������:<td><b>'.$z['adres'].'</b>'.
						'<tr><td class="label">���� ������:'.
							'<td><span class="zamer-dtime" title="'._zamerDuration($z['zamer_duration']).'">'.
									FullDataTime($z['zamer_dtime']).
								'</span>'.
								($z['zamer_status'] == 1 ? '<span class="zamer-left">'.remindDayLeft($z['zamer_dtime']).'</span>' : '').
								'<a class="zamer_table" val="'.$z['id'].'">������� �������</a>'
		       : '').

((DOG || SET) && $z['adres'] ?
						'<tr><td class="label">����� ���������:<td><b>'.$z['adres'].'</b>'
: '').

(ZAKAZ || SET ?
						'<tr><td class="label">�������:<td>'.$dogSpisok.
	  ($z['nomer_vg'] ? '<tr><td class="label">����� ��:<td>'.$z['nomer_vg'].'&nbsp;&nbsp;&nbsp;'._attach('vg', $z['id'], '���������� ��������') : '').
	   ($z['nomer_g'] ? '<tr><td class="label">����� �:<td>'.$z['nomer_g'].'&nbsp;&nbsp;&nbsp;'._attach('g', $z['id'], '���������� ��������') : '').
	   ($z['nomer_d'] ? '<tr><td class="label">����� �:<td>'.$z['nomer_d'].'&nbsp;&nbsp;&nbsp;'._attach('d', $z['id'], '���������� ��������') : '').
						'<tr><td class="label top">�����:<td>'._attach('files', $z['id'], '���������', 1)
: '').
					($status_name ?
						'<tr><td class="label">������'.($type == 'dog' ? ' ������' : '').':'.
							'<td><div style="background-color:#'._statusColor($z[($type == 'dog' ? 'zamer' : $type).'_status']).'" class="status '.$type.'_status">'.$status_name.'</div>'
					: '').
					'</table>'.
	(ZAKAZ || SET ?
				'<TD class="mainright">'.
					'<div class="headBlue">������� �� ������<a class="add rashod-edit">��������</a></div>'.
					'<div class="acc-sum">'.
						($accSum ? '����� ����� ����������: <b>'.$accSum.'</b> ���.' : '���������� ���.').
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

	(!DOG ?	'<div class="headBlue mon">���������� � �������'.
				'<a class="add oplata-add">������ �����</a>'.
				'<em>::</em>'.
				'<a class="add acc-add">���������</a>'.
			'</div>'.
			'<div id="income_spisok">'.zayav_money($z['id']).'</div>'
	: '').

			_vkComment('zayav', $z['id']).
		'</div>'.
		(RULES_HISTORYSHOW ? '<div class="histories"><div class="headName">'.$head.'</div>'.history_spisok(1, array('zayav_id'=>$z['id'])).'</div>' : '').
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
				'<td class="sum acc" title="����������"><b>'._sumSpace($r['sum']).'</b>'.
				'<td>'.$r['prim'].
				'<td class="dtime" title="��'.(_viewer($r['viewer_id_add'], 'sex') == 1 ? '����' : '��').' '._viewer($r['viewer_id_add'], 'name').'">'.FullDataTime($r['dtime_add']).
				'<td class="ed" align="right">'.
					(!$r['dogovor_id'] ? '<div class="img_del accrual-del"></div>' : '');

	ksort($money);
	if(empty($money))
		return '';
	return '<table class="_spisok _money">'.implode('', $money).'</table>';
}//zayav_money()
function _attach($type, $zayav_id, $name='�����...', $files_block=false) {
	return
	'<div class="_attach">'.
		'<div class="files'.($files_block ? ' block' : '').'">'._attach_files($type, $zayav_id).'</div>'.
		'<div class="form">'.
			'<form method="post" action="'.SITE.'/ajax/main.php?'.VALUES.'" enctype="multipart/form-data" target="'.$type.$zayav_id.'_frame">'.
				_attach_form($type, $zayav_id).
			'</form>'.
			'<a class="attach_a">'.$name.'</a>'.
		'</form>'.
		'<iframe name="'.$type.$zayav_id.'_frame"></iframe>'.
	'</div>';
}
function _attach_files($type, $zayav_id) {
	$sql = "SELECT * FROM `attach` WHERE `deleted`=0 AND `type`='".$type."' AND `zayav_id`=".$zayav_id." ORDER BY `id`";
	$q = query($sql);
	$send = array();
	while($r = mysql_fetch_assoc($q))
		$send[] =
			'<span>'.
				'<a href="'.$r['link'].'">'.$r['name'].'</a>'.
				'<div class="img_minidel" val="'.$r['id'].'"></div>'.
			'</span>';
	return implode(' ', $send);
}//_attach_files()
function _attach_form($type, $zayav_id) {
	return
	'<input type="file" name="f1" class="inp2">'.
	'<input type="file" name="f2">'.
	'<input type="hidden" name="op" value="attach_upload">'.
	'<input type="hidden" name="type" class="type" value="'.$type.'">'.
	'<input type="hidden" name="zayav_id" class="zayav_id" value="'.$zayav_id.'">';
}

function dogovor_print($dog_id) {
	require_once(VKPATH.'clsMsDocGenerator.php');

	$v = $dog_id;
	$cash_id = 0;
	if(!is_array($v)) {
		$sql = "SELECT * FROM `zayav_dogovor` WHERE `deleted`=0 AND `id`=".$dog_id." LIMIT 1";
		$v = mysql_fetch_assoc(query($sql));
		if($v['avans'])
			$cash_id = query_value("SELECT `id` FROM `money` WHERE `dogovor_id`=".$v['id']." LIMIT 1");
	}

	$sql = "SELECT * FROM `setup_global`";
	$g = mysql_fetch_assoc(query($sql));

	$d = explode('-', $v['data_create']);

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

	$doc->addParagraph(
	'<div class="head-name">������� �'.$v['nomer'].'</div>'.
	'<table class="city_data"><tr><td>����� �������<th>'.$d[2].'/'.$d[1].'/'.$d[0].' �.</table>'.
	'<div class="paragraph">'.
		'<p>�������� � ������������ ���������������� ����������� ��������, '.
		'� ���� ��������� �� ��������, ��������� ���� �������������, ����������� �� ��������� ������������, '.
		'� ����� �������, � '.$v['fio'].', '.($v['pasp_adres'] ? $v['pasp_adres'] : $v['adres']).', ��������� � ���������� ���������, � ������ �������, '.
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
				$v['pasp_adres'].
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
			'<td class="sum">'.$money['sum'].'.00'.
			'<td class="summa">'.$money['sum'].'.00'.
		'</table>'.
	'<div class="summa-propis">'.numberToWord($money['sum'], 1).' ����'._end($money['sum'], '�', '�', '��').'</div>'.
	'<div class="shop-about">(����� ��������)</div>'.
	'<table class="cash-podpis">'.
		'<tr><td>�������� ______________________<div class="prod-bot">(�������)</div>'.
			'<td><u>/��������� �.�./</u><div class="r-bot">(����������� �������)</div>'.
	'</table>';
}




// ---===! remind !===--- ������ �����������

function remindDayLeft($d) {
	$dayLeft = floor((strtotime($d) - TODAY_UNIXTIME) / 3600 / 24);
	if($dayLeft < 0)
		return '���������'._end($dayLeft * -1, ' ', '� ').($dayLeft * -1)._end($dayLeft * -1, ' ����', ' ���', ' ����');
	if($dayLeft > 2)
		return '�����'._end($dayLeft, '�� ', '��� ').$dayLeft._end($dayLeft, ' ����', ' ���', ' ����');
	switch($dayLeft) {
		default:
		case 0: return '��������� �������';
		case 1: return '��������� ������';
		case 2: return '��������� �����������';
	}
}//remindDayLeft()
function remindDayLeftBg($d) {
	$dayLeft = floor((strtotime($d) - TODAY_UNIXTIME) / 3600 / 24);
	if($dayLeft < 0)
		return 'f99';
	if($dayLeft == 0)
		return 'ffa';
	return 'ddf';
}
function remind() {
	$sql = "SELECT DATE_FORMAT(`zamer_dtime`,'%Y-%m-%d') AS `day`
			FROM `zayav`
			WHERE `deleted`=0
			  AND `zamer_status`=1
			  AND `zamer_dtime` LIKE ('".strftime('%Y-')."%')
			GROUP BY DATE_FORMAT(`zamer_dtime`,'%Y-%m-%d')";
	$q = query($sql);
	$days = array();
	while($r = mysql_fetch_assoc($q))
		$days[$r['day']] = 1;

	$curMon = abs(strftime('%m'));

	$fullCalendar = '<table class="ftab">';
	$qw = 1;
	$data['days'] = $days;
	$data['year'] = strftime('%Y');
	for($n = 1; $n <= 12; $n++) {
		if($qw == 1)
			$fullCalendar .= '<tr>';
		$data['month'] = $n;
		$cur = $n == $curMon;
		$fullCalendar .=
			'<td class="ftd'.($cur ? ' fcur' : '').'">'.
				'<a class="fmon" val="'.$n.'">'._monthDef($n).'</a>'.
				_calendarFilter($data);
		$qw++;
		if($qw > 3)
			$qw = 1;
	}
	$fullCalendar .= '</table>';

	return
	'<div id="remind">'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.remind_spisok().
				'<td class="right">'.
					'<div class="cal_select"><a class="goyear"><span>'._monthDef($curMon).'</span> '.strftime('%Y').'</a></div>'.
					'<div id="cal_div">'._calendarFilter(array('days'=>$days)).'</div>'.
		'</table>'.
		'<div class="full"><div class="fhead">��������� �����������: 2013 </div>'.$fullCalendar.'</div>'.
	'</div>';
}//remind()
function remind_spisok($page=1, $filter=array()) {
	$cond = "`deleted`=0 AND `zamer_status`=1";
	if(isset($filter['day']))
		$cond .= " AND `zamer_dtime` LIKE '".$filter['day']." %'";
	$sql = "SELECT *
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `zamer_dtime`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '����������� ���.';
	$remind = array();
	while($r = mysql_fetch_assoc($q)) {
		$remind[$r['id']] = $r;
	}
	$send = '';
	foreach($remind as $r) {
		$send .=
		'<div class="remind_unit">'.
			'<a class="head" '.
			   'href="'.URL.'&p=zayav&d=info&id='.$r['id'].'" '.
			   'style="background-color:#'.remindDayLeftBg($r['zamer_dtime']).'">'.
					'������ �� ����� �'.$r['id'].
			'</a>'.
			'<div class="to">����: '.FullDataTime($r['zamer_dtime']).'<span class="dur">'._zamerDuration($r['zamer_duration']).'</span></div>'.
			'<div class="day_left">'.remindDayLeft($r['zamer_dtime']).'<a class="action zamer_status" val="'.$r['id'].'">��������</a></div>'.
		'</div>';
	}

	return $send;
}//remind_spisok()


// ---===! report !===--- ������ �������

function report() {
	$def = 'history';
	$pages = array(
		'history' => '������� ��������',
		'money' => '������'
	);

	if(!RULES_HISTORYSHOW)
		unset($pages['history']);

	$d = empty($_GET['d']) ? $def : $_GET['d'];
	if(empty($_GET['d']) && !empty($pages) && empty($pages[$d])) {
		foreach($pages as $p => $name) {
			$d = $p;
			break;
		}
	}

	$links = '';
	if($pages)
		foreach($pages as $p => $name)
			$links .= '<a href="'.URL.'&p=report&d='.$p.'"'.($d == $p ? ' class="sel"' : '').'>'.$name.'</a>';

	$right = '';
	switch($d) {
		default:
		case 'history':
			$left = RULES_HISTORYSHOW ? '<div id="report_history">'.history_spisok().'</div>' : _norules();
			break;
		case 'money':
			$d1 = empty($_GET['d1']) ? 'invoice' : $_GET['d1'];
			switch($d1) {
				default:
				case 'invoice': $left = invoice(); break;
				case 'income':
					$data = income_spisok();
					$left =
						'<div id="income">'.
							'<div class="headName">������ ��������</div>'.
							'<div id="spisok">'.$data['spisok'].'</div>'.
						'</div>';
					$right = income_right();
					break;
				case 'expense': $left = '�������'; break;
			}
			$left = report_money_dopLinks($d1).$left;
			break;
	}
	return
	'<table class="tabLR" id="report">'.
		'<tr><td class="left">'.$left.
			'<td class="right">'.
				'<div class="rightLink">'.$links.'</div>'.
				$right.
				'<a href="'.SITE.'/view/_report.php?'.VALUES.'">�����</a>'.
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
function history_types($v) {
	switch($v['type']) {
		case 1: return '�������� ������ ������� '.$v['client_link'].'.';
		case 2: return '��������� ������ ������� '.$v['client_link'].':<div class="changes">'.$v['value'].'</div>';
		case 3: return '�������� ������� '.$v['client_link'].'.';

		case 4: return '�������� ����� ������ '.$v['zayav_link'].'<em>(�����)</em> ��� ������� '.$v['client_link'].'.';
		case 5: return '��������� ������ ������ '.$v['zayav_link'].'<em>(�����)</em>:<div class="changes">'.$v['value1'].'</div>';
		case 6: return '�������� ������ '.$v['zayav_link'].' � ������� '.$v['client_link'].'.';

		case 7: return '���������� �� ����� <b>'.$v['value'].'</b> ���.'.
						($v['value1'] ? '<em>('.$v['value1'].')</em>' : '').
						' �� ������ '.$v['zayav_link'].'.';
		case 8: return '�������� ���������� �� ����� <b>'.$v['value'].'</b> ���.'.
						($v['value1'] ? '<em>('.$v['value1'].')</em>' : '').
						' � ������ '.$v['zayav_link'].'.';
		case 9: return '�������������� ���������� �� ����� <b>'.$v['value'].'</b> ���.'.
						($v['value1'] ? '<em>('.$v['value1'].')</em>' : '').
						' � ������ '.$v['zayav_link'].'.';

		case 10: return
			'����� <span class="oplata">'._income($v['value2']).'</span> '.
			'�� ����� <b>'.$v['value'].'</b> ���.'.
			($v['value1'] ? '<em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' �� ������ '.$v['zayav_link'] : '').
			'.';
		case 11: return
			'�������� ������� <span class="oplata">'._income($v['value2']).'</span> '.
			'�� ����� <b>'.$v['value'].'</b> ���.'.
			($v['value1'] ? '<em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' � ������ '.$v['zayav_link'] : '').
			'.';
		case 12: return
			'�������������� ������� <span class="oplata">'._income($v['value2']).'</span> '.
			'�� ����� <b>'.$v['value'].'</b> ���.'.
			($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' � ������ '.$v['zayav_link'] : '').
			'.';

		case 13: return '� ����������: ���������� ������ ���������� '._viewer($v['value'], 'link').'.';
		case 14: return '� ����������: �������� ���������� '._viewer($v['value'], 'link').'.';

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

		case 21: return '�������� ����� ������ '.$v['zayav_link'].'<em>(���������)</em> ��� ������� '.$v['client_link'].'.';
		case 22: return '��������� ������ ������ �� ��������� '.$v['zayav_link'].'<em>(���������)</em>:<div class="changes">'.$v['value1'].'</div>';

		case 23: return '�������� ������ ������ '.$v['zayav_link'].'<em>(�����)</em> ��� ������� '.$v['client_link'].'.';
		case 24: return '��������� ������ ������ '.$v['zayav_link'].'<em>(�����)</em>:<div class="changes">'.$v['value1'].'</div>';
		case 25: return '��������� ������� ������ '.$v['zayav_link'].'<em>(�����)</em>:<br />'.
						'<span style="background-color:#'._statusColor($v['value']).'">'._zakazStatus($v['value']).'</span>'.
						' � '.
						'<span style="background-color:#'._statusColor($v['value1']).'">'._zakazStatus($v['value1']).'</span>';
		case 26: return '��������� ������� ������ '.$v['zayav_link'].'<em>(���������)</em>:<br />'.
						'<span style="background-color:#'._statusColor($v['value']).'">'._setStatus($v['value']).'</span>'.
						' � '.
						'<span style="background-color:#'._statusColor($v['value1']).'">'._setStatus($v['value1']).'</span>';

		case 27: return '�������� ����� '.$v['value'].' ��� ������ '.$v['zayav_link'].'.';
		case 28: return '�������� ����� '.$v['value'].' � ������ '.$v['zayav_link'].'.';

		case 29: return '��������� �������� �� ������ '.$v['zayav_link'].':<div class="changes">'.$v['value'].'</div>';

		case 501: return '� ����������: �������� ������ ������������ ������� "'.$v['value'].'".';
		case 502: return '� ����������: ��������� ������ ������� "'.$v['value1'].'":<div class="changes">'.$v['value'].'</div>';
		case 503: return '� ����������: �������� ������������ ������� "'.$v['value'].'".';

		case 510: return '� ����������: ��������� ���������� �����������:<div class="changes">'.$v['value'].'</div>';

		case 504: return '� ����������: �������� ������ ������� ��� ������� "'.$v['value'].'": '.$v['value1'].'.';
		case 505: return '� ����������: ��������� ������� � ������� "'.$v['value'].'":<div class="changes">'.$v['value1'].'</div>';
		case 506: return '� ����������: �������� ������� � ������� "'.$v['value'].'": '.$v['value1'].'.';

		case 507: return '� ����������: �������� ������ ������������ ������� "'.$v['value'].'".';
		case 508: return '� ����������: ��������� ������ ������� "'.$v['value'].'":<div class="changes">'.$v['value1'].'</div>';
		case 509: return '� ����������: �������� ������ ������� "'.$v['value'].'".';

		case 511: return '� ����������: �������� ����� ��������� �������� ������ <u>'.$v['value'].'</u>.';
		case 512: return '� ����������: ��������� ������ ��������� �������� ������ <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 513: return '� ����������: �������� ������ ��������� �������� ������ <u>'.$v['value'].'</u>.';

		case 514: return '� ����������: ��������� ������ ���������� <u>'._viewer($v['value'], 'name').'</u>:<div class="changes">'.$v['value1'].'</div>';

		case 515: return '� ����������: �������� ������ ����� <u>'.$v['value'].'</u>.';
		case 516: return '� ����������: ��������� ������ ����� <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 517: return '� ����������: �������� ����� <u>'.$v['value'].'</u>.';

		default: return $v['type'];
	}
}//history_types()
function history_spisok($page=1, $filter=array()) {
	$limit = 30;
	$cond = "`id`".
		(isset($filter['client_id']) ? ' AND `client_id`='.$filter['client_id'] : '').
		(isset($filter['zayav_id']) ? ' AND `zayav_id`='.$filter['zayav_id'] : '');
	$sql = "SELECT COUNT(`id`) AS `all`
			FROM `history`
			WHERE ".$cond."
			LIMIT 1";
	$all = query_value($sql);
	if(!$all)
		return '������� �� ��������� �������� ���.';
	$start = ($page - 1) * $limit;

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

	$send = '';
	$txt = '';
	end($history);
	$keyEnd = key($history);
	reset($history);
	foreach($history as $r) {
		if(!$txt) {
			$time = strtotime($r['dtime_add']);
			$viewer_id = $r['viewer_id_add'];
		}
		$txt .= '<div class="txt">'.history_types($r).'</div>';
		$key = key($history);
		if(!$key ||
			$key == $keyEnd ||
			$time - strtotime($history[$key]['dtime_add']) > 900 ||
			$viewer_id != $history[$key]['viewer_id_add']) {
			$send .=
				'<div class="history_unit">'.
					'<div class="head">'.FullDataTime($r['dtime_add']).$r['viewer_link'].'</div>'.
					$txt.
				'</div>';
			$txt = '';
		}
		next($history);
	}
	if($start + $limit < $all)
		$send .= '<div class="ajaxNext" id="history_next" val="'.($page + 1).'"><span>�������� ����� ������ ������...</span></div>';
	return $send;
}//history_spisok()

function invoice() {
	return
	'<div id="invoice">'.
		'<div class="headName">�����</div>'.
		invoice_spisok().
		'<a href="'.URL.'&p=setup&d=invoice" class="setup">���������� �������</a>'.
	'</div>';
}//invoice()
function invoice_spisok() {
	$sql = "SELECT * FROM `invoice` ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '����� �� ����������.';

	$spisok = array();
	while($r = mysql_fetch_assoc($q)) {
		if($r['start'] != -1) {
			$income = query_value("SELECT SUM(`sum`) FROM `money` WHERE `deleted`=0 AND `invoice_id`=".$r['id']);
			$r['balans'] = $income - $r['start'];
		}
		$spisok[$r['id']] = $r;
	}

	$send = '<table class="_spisok">';
	foreach($spisok as $id => $r)
		$send .= '<tr>'.
			'<td class="name"><b>'.$r['name'].'</b><pre>'.$r['about'].'</pre>'.
			'<td class="balans">'.
			(isset($r['balans']) ? '<b>'.$r['balans'].'</b> ���.' : '<a class="invoice_set" val="'.$id.'">���������� ��������� �����</a>');
	$send .= '</table>';
	return $send;
}//invoice_spisok()

function report_money_dopLinks($d1) {
	return
	'<div id="dopLinks">'.
		'<a class="link'.($d1 == 'invoice' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=invoice">�����</a>'.
		'<a class="link'.($d1 == 'income' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=income">�������</a>'.
		'<a class="link'.($d1 == 'expense' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=expense">�������</a>'.
	'</div>';
}//report_money_dopLinks()

function income_insert($v) {//�������� �������
	if(empty($v['from']))
		$v['from'] = '';
	if($v['zayav_id']) {
		$sql = "SELECT *
				FROM `zayav`
				WHERE `deleted`=0
				  AND `id`=".$v['zayav_id'];
		if(!$z = mysql_fetch_assoc(query($sql)))
			return false;
		if(!empty($v['client_id']) && $v['client_id'] != $z['client_id'])
			return false;
		$v['client_id'] = $z['client_id'];
	}
	if(empty($v['client_id']))
		$v['client_id'] = 0;

	$sql = "INSERT INTO `money` (
				`zayav_id`,
				`client_id`,
				`invoice_id`,
				`income_id`,
				`sum`,
				`prim`,
				`viewer_id_add`
			) VALUES (
				".$v['zayav_id'].",
				".$v['client_id'].",
				"._income($v['type'], 'invoice').",
				".$v['type'].",
				".$v['sum'].",
				'".addslashes($v['prim'])."',
				".VIEWER_ID."
			)";
	query($sql);
	$insert_id = mysql_insert_id();

	clientBalansUpdate($v['client_id']);
	history_insert(array(
		'type' => 10,
		'zayav_id' => $v['zayav_id'],
		'client_id' => $v['client_id'],
		'value' => $v['sum'],
		'value1' => $v['prim'],
		'value2' => $v['type']
	));

	switch($v['from']) {
		case 'client':
			$data = income_spisok(1, array('client_id'=>$v['client_id'],'limit'=>15));
			return $data['spisok'];
		case 'zayav': return zayav_money($v['zayav_id']);
		default: return $insert_id;
	}
}//income_insert()
function incomeFilter($v) {
	$send = array(
		'limit' => 30,
		'client_id' => 0,
		'zayav_id' => 0
	);
	if(isset($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) && $v['limit'] > 0)
		$send['limit'] = $v['limit'];
	if(isset($v['client_id']) && preg_match(REGEXP_NUMERIC, $v['client_id']))
		$send['client_id'] = $v['client_id'];
	if(isset($v['zayav_id']) && preg_match(REGEXP_NUMERIC, $v['zayav_id']))
		$send['zayav_id'] = $v['zayav_id'];
	return $send;
}//incomeFilter()
function income_spisok($page=1, $filter=array()) {
	$cond = '`deleted`=0 AND `sum`>0';

	$filter = incomeFilter($filter);
	if($filter['client_id'])
		$cond .= " AND `client_id`=".$filter['client_id'];
	if($filter['zayav_id'])
		$cond .= " AND `zayav_id`=".$filter['zayav_id'];

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
		(!$filter['zayav_id'] ?
			'<div class="_moneysum">'.
				'�������'._end($send['all'], '', '�').
				' <b>'.$send['all'].'</b> ������'._end($send['all'], '', '�', '��').
				' �� ����� <b>'._sumSpace($send['sum']).'</b> ���.'.
			'</div>' : '').
			'<table class="_spisok _money">'.
		(!$filter['zayav_id'] ?
				'<tr><th class="sum">�����'.
					'<th>��������'.
					'<th class="data">����'.
					'<th>'
		: '');
	foreach($money as $r)
		$send['spisok'] .= income_unit($r, $filter);
	if($start + $filter['limit'] < $send['all']) {
		$c = $send['all'] - $start - $filter['limit'];
		$c = $c > $filter['limit'] ? $filter['limit'] : $c;
		$send['spisok'] .=
			'<tr class="ajaxNext" val="'.($page + 1).'" id="money_next"><td colspan="4">'.
				'<span>�������� ��� '.$c.' ������'._end($c, '', '�', '��').'</span>';
	}
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
		$about .= '������ '.$r['zayav_link'].'. ';
	$about .= $r['prim'];
	$sumTitle = !$filter['zayav_id'] ? ' title="�����"' : '';
	return
		'<tr val="'.$r['id'].'">'.
			'<td class="sum opl"'.$sumTitle.'><b>'._sumSpace($r['sum']).'</b>'.
			'<td><span class="type">'._income($r['income_id']).(empty($about) ? '' : ':').'</span> '.$about.
			'<td class="dtime" title="��'.(_viewer($r['viewer_id_add'], 'sex') == 1 ? '����' : '��').' '._viewer($r['viewer_id_add'], 'name').'">'.FullDataTime($r['dtime_add']).
			'<td class="ed"><a href="'.SITE.'/view/cashmemo.php?'.VALUES.'&id='.$r['id'].'" class="img_doc" target="_blank"></a>'.
				(!$r['dogovor_id'] ? '<div class="img_del oplata-del"></div>' : '');
}//income_unit()

function income_right() {
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y-%m-%d') AS `day`
			FROM `money`
			WHERE `deleted`=0
			  AND `sum`>0
			  AND `dtime_add` LIKE ('".strftime('%Y-%m-')."%')
			GROUP BY DATE_FORMAT(`dtime_add`,'%Y-%m-%d')";
	$q = query($sql);
	$days = array();
	while($r = mysql_fetch_assoc($q))
		$days[$r['day']] = 1;
	return
		'<div class="income_data">'.
			'<a class="income_mon" val="2014">'._monthDef(strftime('%m', time())).'</a> '.
			'<a class="income_year">'.strftime('%Y', time()).'</a>'.
		'</div>'.
		_calendarFilter(array(
			'days' => $days,
			'year' => strftime('%Y')
		));
}//income_right()


// ---===! setup !===--- ������ ��������

function setup() {
	$pageDef = 'worker';
	$pages = array(
		'worker' => '����������',
		'rekvisit' => '��������� �����������',
		'product' => '���� �������',
		'invoice' => '�����',
		'income' => '���� ��������',
		'zayavrashod' => '������� �� ������'
	);

	if(!RULES_WORKER)
		unset($pages['worker']);
	if(!RULES_REKVISIT)
		unset($pages['rekvisit']);
	if(!RULES_PRODUCT)
		unset($pages['product']);
	if(!RULES_INCOME)
		unset($pages['income']);
	if(!RULES_ZAYAVRASHOD)
		unset($pages['zayavrashod']);

	$d = empty($_GET['d']) ? $pageDef : $_GET['d'];
	if(empty($_GET['d']) && !empty($pages) && empty($pages[$d])) {
		foreach($pages as $p => $name) {
			$d = $p;
			break;
		}
	}

	switch($d) {
		default: $d = $pageDef;
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
		case 'zayavrashod': $left = setup_zayavrashod(); break;
	}
	$links = '';
	if($pages)
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
	$rule = workerRulesArray($u['rules']);
	return
	'<script type="text/javascript">var RULES_VIEWER_ID='.$viewer_id.';</script>'.
	'<div id="setup_rules">'.

		'<table class="utab">'.
			'<tr><td>'.$u['photo'].
				'<td><div class="name">'.$u['name'].'</div>'.
					 ($viewer_id < VIEWER_MAX ? '<a href="http://vk.com/id'.$viewer_id.'" class="vklink" target="_blank">������� �� �������� VK</a>' : '').
		'</table>'.

		'<div class="headName">�����</div>'.
		'<table class="gtab">'.
			'<tr><td class="label">���:<td><input type="text" id="first_name" value="'.$u['first_name'].'" />'.
			'<tr><td class="label">�������:<td><input type="text" id="last_name" value="'.$u['last_name'].'" />'.
			'<tr><td class="label">���������:<td><input type="text" id="post" value="'.$u['post'].'" />'.
			'<tr><td><td><div class="vkButton"><button id="gtab_save">���������</button></div>'.
		'</table>'.

	(!$u['admin'] && $viewer_id < VIEWER_MAX ?
		'<div class="headName">�����</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">��������� ���� � ����������:<td>'._check('rules_appenter', '', $rule['RULES_APPENTER']).
		'</table>'.
		'<div class="app-div'.($rule['RULES_APPENTER'] ? '' : ' dn').'">'.
			'<table class="rtab">'.
				'<tr><td class="lab">���������� �����������:<td>'._check('rules_setup', '', $rule['RULES_SETUP']).
				'<tr><td class="lab"><td>'.
					'<div class="setup-div'.($rule['RULES_SETUP'] ? '' : ' dn').'">'.
						_check('rules_worker', '����������', $rule['RULES_WORKER']).
						_check('rules_rekvisit', '��������� �����������', $rule['RULES_REKVISIT']).
						_check('rules_product', '���� �������', $rule['RULES_PRODUCT']).
						_check('rules_income', '���� ��������', $rule['RULES_INCOME']).
						_check('rules_zayavrashod', '������� �� ������', $rule['RULES_ZAYAVRASHOD']).
					'</div>'.
				'<tr><td class="lab">����� ������ ������� ��������:<td>'._check('rules_historyshow', '', $rule['RULES_HISTORYSHOW']).
			'</table>'.
		'</div>'
	: '').
	'</div>';
}//setup_worker_rules()

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
//	if(!RULES_INCOME)
//		return _norules('��������� ����� ��������');
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
				'<div class="img_edit"></div>'.
				'<div class="img_del"></div>';
	$send .= '</table>';
	return $send;
}//setup_invoice_spisok()

function setup_income() {
	if(!RULES_INCOME)
		return _norules('��������� ����� ��������');
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
					'<td class="money">'.$money.
					'<td class="set">'.
						'<div class="img_edit"></div>'.
						(!$r['money'] && $id > 1 ? '<div class="img_del"></div>' : '').
			'</table>';
	}
	$send .= '</dl>';
	return $send;
}//setup_income_spisok()

function setup_zayavrashod() {
	if(!RULES_ZAYAVRASHOD)
		return _norules('��������� �������� �� ������');
	return
	'<div id="setup_zayavrashod">'.
		'<div class="headName">��������� ��������� �������� �� ������<a class="add">��������</a></div>'.
		'<div class="spisok">'.setup_zayavrashod_spisok().'</div>'.
	'</div>';
}//setup_zayavrashod()
function setup_zayavrashod_spisok() {
	$sql = "SELECT `s`.*,
				   COUNT(`zr`.`id`) AS `use`
			FROM `setup_zayavrashod` AS `s`
			  LEFT JOIN `zayav_rashod` AS `zr`
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
	'<dl class="_sort" val="setup_zayavrashod">';
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
}//setup_zayavrashod_spisok()