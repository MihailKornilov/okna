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
	xcache_unset(CACHE_PREFIX.'prihodtype');
	GvaluesCreate();
}//_cacheClear()

function _header() {
	global $html;
	$html =
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
		'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.

		'<head>'.
		'<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
		'<title>����������� ���� - ���������� '.API_ID.'</title>'.

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

		'<link href="'.SITE.'/css/main.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
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
		'var '."\n".'PRODUCT_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_product` ORDER BY `name`").','.
		 //"\n".'PRODUCT_ASS=_toSpisok(PRODUCT_ASS),'.
		"\n".'PRIHOD_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_prihodtype` ORDER BY `sort`").','.
		"\n".'PRIHODKASSA_ASS='.query_ptpJson("SELECT `id`,`kassa_put` FROM `setup_prihodtype` WHERE `kassa_put`=1").','.
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
function _prihodType($type_id=false, $i='name') {//������ ������� ��� ������
	if(!defined('PRIHODTYPE_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'prihodtype';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT `id`,`name`,`kassa_put` FROM `setup_prihodtype` ORDER BY `sort`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = array(
					'name' => $r['name'],
					'kassa' => $r['kassa_put']
				);
			xcache_set($key, $arr, 86400);
		}
		if(!defined('PRIHODTYPE_LOADED')) {
			foreach($arr as $id => $r) {
				define('PRIHODTYPE_'.$id, $r['name']);
				define('PRIHODTYPE_KASSA_'.$id, $r['kassa']);
			}
			define('PRIHODTYPE_0', '');
			define('PRIHODTYPE_KASSA_0', '');
			define('PRIHODTYPE_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'kassa')
		return constant('PRIHODTYPE_KASSA_'.$type_id);
	return constant('PRIHODTYPE_'.$type_id);
}//_prihodType()
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
function _dogLink($nomer, $text, $class='') {
	return '<a'.($class ? ' class="'.$class.'"' : '').' href="'.SITE.'/files/dogovor/dogovor_'.$nomer.'.doc" title="�����������">'.$text.'</a>';
}

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
		'RULES_APPENTER' => 1,  // ��������� ���� � ����������
		'RULES_SETUP' => 1,     // ���������� �����������
		'RULES_WORKER' => 1,	// ����������
		'RULES_REKVISIT' => 1,	// ��������� �����������
		'RULES_PRODUCT' => 1,	// ���� �������
		'RULES_PRIHODTYPE' => 1 // ���� ��������
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




// ---===! client !===--- ������ ��������

function _clientLink($arr) {
	if(empty($arr))
		return array();
	$id = false;
	if(!is_array($arr)) {
		$id = $arr;
		$arr = array($arr);
	}
	$sql = "SELECT `id`,`fio`,`status` FROM `client` WHERE `id` IN (".implode(',', $arr).")";
	$q = query($sql);
	$send = array();
	while($r = mysql_fetch_assoc($q))
		$send[$r['id']] = '<a '.($r['status'] ? '' : 'class="deleted"').' href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>';
	if($id)
		return $send[$id];
	return $send;
}//_clientsLink()
function clientBalansUpdate($client_id) {//���������� ������� �������
	$prihod = query_value("SELECT SUM(`sum`) FROM `money` WHERE `deleted`=0 AND `client_id`=".$client_id." AND `sum`>0");
	$acc = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE `status`=1 AND `client_id`=".$client_id);
	$dog = query_value("SELECT SUM(`sum`) FROM `zayav_dogovor` WHERE `client_id`=".$client_id);
	$balans = $prihod - $acc - $dog;
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
	$cond = "`status`=1";
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

	$send['spisok'] = '';
	foreach($spisok as $r)
		$send['spisok'] .= '<div class="unit">'.
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
		$dolg = abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE `balans`<0 LIMIT 1"));
	return ($count > 0 ?
		'������'._end($count, ' ', '� ').$count.' ������'._end($count, '', '�', '��').
		($dolg ? '<em>(����� ����� ����� = '.$dolg.' ���.)</em>' : '')
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
	$sql = "SELECT * FROM `client` WHERE `status`=1 AND `id`=".$client_id;
	if(!$client = mysql_fetch_assoc(query($sql)))
		return _noauth('������� �� ����������');

	$zamer = zamer_spisok(1, array('client'=>$client_id), 10);
	$commCount = query_value("SELECT COUNT(`id`)
							  FROM `vk_comment`
							  WHERE `status`=1
								AND `parent_id`=0
								AND `table_name`='client'
								AND `table_id`=".$client_id);

	$money = money_spisok(1, array('client_id'=>$client_id,'limit'=>15));

   // $remindData = remind_data(1, array('client'=>$client_id));

	$histCount = query_value("SELECT COUNT(`id`) FROM `history` WHERE `client_id`=".$client_id);

	$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `client_id`=".$client_id;
	$q = query($sql);
	$zopl = array();
	while($r = mysql_fetch_assoc($q))
		$zopl[$r['id']] = array(
			'title' => '������ �'.$r['id'],
			'content' => '������ �'.$r['id'].
						($r['dogovor_nomer'] ? '<div class="pole2">������� �'.$r['dogovor_nomer'].'</div>' : '')
		);

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
						'<a class="oplata-add">������ �����</a>'.
						'<a class="zamer_add"><b>����� �����</b></a>'.
						'<a class="cdel">������� �������</a>'.
					'</div>'.
		'</table>'.

		'<div id="dopLinks">'.
			'<a class="link sel" val="zayav">������'.($zamer['all'] ? ' ('.$zamer['all'].')' : '').'</a>'.
			'<a class="link" val="money">�������'.($money['all'] ? ' ('.$money['all'].')' : '').'</a>'.
			//'<a class="link" val="remind">�������'.(!empty($remindData) ? ' ('.$remindData['all'].')' : '').'</a>'.
			'<a class="link" val="comm">�������'.($commCount ? ' ('.$commCount.')' : '').'</a>'.
			'<a class="link" val="hist">�������'.($histCount ? ' ('.$histCount.')' : '').'</a>'.
		'</div>'.

		'<table class="tabLR">'.
			'<tr><td class="left">'.
					'<div id="zayav_spisok">'.$zamer['spisok'].'</div>'.
					'<div id="money_spisok">'.$money['spisok'].'</div>'.
					'<div id="remind_spisok">'.(!empty($remindData) ? report_remind_spisok($remindData) : '<div class="_empty">������� ���.</div>').'</div>'.
					'<div id="comments">'._vkComment('client', $client_id).'</div>'.
					'<div id="histories">'.report_history_spisok(1, array('client_id'=>$client_id)).'</div>'.
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

function _zamerLink($zayav_id, $zamer_nomer) {
	return '<a href="'.URL.'&p=zayav&d=info&id='.$zayav_id.'">�'.$zamer_nomer.'</a>';
}//_zamerLink()
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

function product_spisok_test($product) {// �������� ������������ ������ ������� ��� �������� � ����
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
}//product_spisok_test()


function zayav() {
	switch(@$_GET['d']) {
		default:
			$_GET['d'] = 'zamer';
		case 'zamer':
			$right = '<div id="buttonCreate" class="zamer_add"><a>����� �����</a></div>';
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
			$right = '<div id="buttonCreate"><a>����� ������<br />�� ���������</a></div>';
			$data = set_spisok();
			$result = $data['result'];
			$spisok = $data['spisok'];
			break;
	}
	$zamerCount = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE `deleted`=0 AND `zamer_status`=1 LIMIT 1");
	$dogovorCount = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE `deleted`=0 AND `zamer_status`=2 AND `dogovor_nomer`=0 LIMIT 1");
	$setCount = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE `deleted`=0 AND `set_status`=1 LIMIT 1");
	return
	'<div id="zayav">'.
		'<div id="dopLinks">'.
			'<div id="find"></div>'.
			'<a class="link'.($_GET['d'] == 'zamer' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=zamer">�����'.($zamerCount ? ' ('.$zamerCount.')' : '').'</a>'.
			'<a class="link'.($_GET['d'] == 'dog' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=dog">�������'.($dogovorCount ? ' ('.$dogovorCount.')' : '').'</a>'.
			'<a class="link'.($_GET['d'] == 'set' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=set">���������'.($setCount ? ' ('.$setCount.')' : '').'</a>'.
		'</div>'.
		'<div class="result">'.$result.'</div>'.
		'<table class="tabLR">'.
			'<tr><td id="spisok">'.$spisok.
				'<td class="right">'.$right.
		'</table>'.
	'</div>';
}//zayav()
function zamer_spisok($page=1, $filter=array()) {
	$cond = "(`zamer_status`=1 OR `zamer_status`=3)";

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
	$client = array();
	while($r = mysql_fetch_assoc($q)) {
		if(isset($zayav_id) && $zayav_id == $r['id'])
			continue;
		$zayav[$r['id']] = $r;
		$client[$r['client_id']] = $r['client_id'];
	}

	if(empty($filter['client']))
		$client = _clientLink($client);

	$send['spisok'] = '';
	foreach($zayav as $id => $r)
		$send['spisok'] .=
			'<div class="zayav_unit" style="background-color:#'._zayavStatusColor($r['zamer_status']).'" val="'.$id.'">'.
				'<div class="dtime">'.FullData($r['dtime_add'], 1).'</div>'.
				'<a class="name">����� �'.$r['zamer_nomer'].'</a>'.
				'<table class="ztab">'.
					(empty($filter['client']) ? '<tr><td class="label">������:<td>'.$client[$r['client_id']] : '').
					'<tr><td class="label top">�����:<td>'.$r['adres'].
				'</table>'.
			'</div>';
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="ajaxNext" val="'.($page + 1).'">'.
				'<span>�������� ��� '.$c.' �����'._end($c, '', '�', '��').'</span>'.
			'</div>';
	}
	return $send;
}//zayav_data()
function dogovor_spisok($page=1, $filter=array()) {
	$cond = "`zamer_status`=2 AND `dogovor_nomer`=0";

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
			ORDER BY `dogovor_nomer` ".$filter['desc']."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$zayav = array();
	$client = array();
	while($r = mysql_fetch_assoc($q)) {
		if(isset($zayav_id) && $zayav_id == $r['id'])
			continue;
		$zayav[$r['id']] = $r;
		$client[$r['client_id']] = $r['client_id'];
	}

	if(empty($filter['client']))
		$client = _clientLink($client);

	$send['spisok'] = '';
	foreach($zayav as $id => $r)
		$send['spisok'] .=
		'<div class="zayav_unit" val="'.$id.'">'.
			'<div class="dtime">'.FullData($r['dtime_add'], 1).'</div>'.
			'<a class="name">������� �� �������� <span>(����� �'.$r['zamer_nomer'].')</span></a>'.
			'<table class="ztab">'.
				(empty($filter['client']) ? '<tr><td class="label">������:<td>'.$client[$r['client_id']] : '').
				'<tr><td class="label top">�����:<td>'.$r['adres'].
			'</table>'.
		'</div>';
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="ajaxNext" val="'.($page + 1).'">'.
				'<span>�������� ��� '.$c.' ����'._end($c, '��', '��', '��').'</span>'.
			'</div>';
	}
	return $send;
}//dogovor_spisok()
function set_spisok($page=1, $filter=array()) {
	$cond = "`zamer_status`!=1 && `zamer_status`!=3 AND `dogovor_nomer`>0";

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
			ORDER BY `set_nomer` ".$filter['desc']."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$zayav = array();
	$client = array();
	while($r = mysql_fetch_assoc($q)) {
		if(isset($zayav_id) && $zayav_id == $r['id'])
			continue;
		$zayav[$r['id']] = $r;
		$client[$r['client_id']] = $r['client_id'];
	}

	if(empty($filter['client']))
		$client = _clientLink($client);

	$send['spisok'] = '';
	foreach($zayav as $id => $r)
		$send['spisok'] .=
		'<div class="zayav_unit" style="background-color:#'._zayavStatusColor($r['set_status']).'" val="'.$id.'">'.
			'<div class="dtime">'.FullData($r['dtime_add'], 1).'</div>'.
			'<a class="name">��������� �'.$r['set_nomer'].'</a>'.
			'<table class="ztab">'.
				(empty($filter['client']) ? '<tr><td class="label">������:<td>'.$client[$r['client_id']] : '').
				'<tr><td class="label top">�����:<td>'.$r['adres'].
			'</table>'.
		'</div>';
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="ajaxNext" val="'.($page + 1).'">'.
				'<span>�������� ��� '.$c.' ����'._end($c, '��', '��', '��').'</span>'.
			'</div>';
	}
	return $send;
}//set_spisok()

function zayav_info($zayav_id) {
	$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$zayav_id." LIMIT 1";
	if(!$r = mysql_fetch_assoc(query($sql)))
		return _noauth('������ �� ����������.');

	if($r['zamer_status'] == 1 || $r['zamer_status'] == 3)
		return zamer_info($r);
	if($r['dogovor_nomer'] == 0)
		return dogovor_info($r);
	if($r['dogovor_nomer'] > 0 && $r['set_status'] > 0)
		return set_info($r);
	return _noauth('����������� ������');
}//zayav_info()
function zamer_info($z) {
	$ex = explode(' ', $z['zamer_dtime']);
	$time = explode(':', $ex[1]);

	return
		'<script type="text/javascript">'.
			'var ZAYAV={'.
				'id:'.$z['id'].','.
				'client_id:'.$z['client_id'].','.
				'product:['.zayav_product_spisok($z['id'], 'json').'],'.
				'adres:"'.$z['adres'].'",'.
				'day:"'.$ex[0].'",'.
				'hour:'.intval($time[0]).','.
				'min:'.intval($time[1]).','.
				'dur:'.$z['zamer_duration'].
			'};'.
		'</script>'.
		'<div class="zayav-info zamer">'.
			'<div id="dopLinks">'.
				'<a class="delete">������� �����</a>'.
				'<a class="link sel zinfo">����������</a>'.
				'<a class="link zedit">��������������</a>'.
				'<a class="link hist">�������</a>'.
			'</div>'.
			'<div class="headName">������ �� ����� �'.$z['zamer_nomer'].'</div>'.
			'<div class="content">'.
				'<table class="tabInfo">'.
					'<tr><td class="label">������:<td>'._clientLink($z['client_id']).
					'<tr><td class="label">���� �����:'.
						'<td class="dtime_add" title="������ ��� '._viewer($z['viewer_id_add'], 'name').'">'.FullDataTime($z['dtime_add']).
					'<tr><td class="label top">�������:<td>'.zayav_product_spisok($z['id']).
					'<tr><td class="label">����� ������:<td>'.$z['adres'].
					'<tr><td class="label">���� ������:'.
						'<td><span class="zamer-dtime" title="'._zamerDuration($z['zamer_duration']).'">'.FullDataTime($z['zamer_dtime']).'</span>'.
							($z['zamer_status'] == 1 ? '<span class="zamer-left">'.remindDayLeft($z['zamer_dtime']).'</span>' : '').
					'<tr><td class="label">������:'.
						'<td><div style="background-color:#'._zayavStatusColor($z['zamer_status']).'" class="status zamer_status">'.
								_zayavStatusName($z['zamer_status']).
							'</div>'.
				'</table>'.
				_vkComment('zayav', $z['id']).
			'</div>'.
		'<div class="histories">'.report_history_spisok(1, array('zayav_id'=>$z['id'])).'</div>'.
	'</div>';
}//zamer_info()
function dogovor_info($z) {
	$sql = "SELECT * FROM `client` WHERE `id`=".$z['client_id'];
	$client = mysql_fetch_assoc(query($sql));

	return
	'<script type="text/javascript">'.
		'var ZAYAV={'.
			'id:'.$z['id'].','.
			'fio:"'.$client['fio'].'",'.
			'adres:"'.$client['adres'].'",'.
			'pasp_seria:"'.$client['pasp_seria'].'",'.
			'pasp_nomer:"'.$client['pasp_nomer'].'",'.
			'pasp_adres:"'.$client['pasp_adres'].'",'.
			'pasp_ovd:"'.$client['pasp_ovd'].'",'.
			'pasp_data:"'.$client['pasp_data'].'"'.
		'};'.
	'</script>'.
	'<div class="zayav-info">'.
		'<div id="dopLinks">'.
			'<a class="delete">������� ������</a>'.
			'<a class="link sel zinfo">����������</a>'.
			'<a class="link zedit">��������������</a>'.
			'<a class="link hist">�������</a>'.
		'</div>'.
		'<div class="headName">����� �'.$z['zamer_nomer'].' - �������� ������� �������</div>'.
		'<div class="content">'.
			'<table class="tabInfo">'.
				'<tr><td class="label">������:<td>'._clientLink($z['client_id']).
//				'<tr><td class="label">���� �����:'.
//					'<td class="dtime_add" title="������ ��� '._viewer($z['viewer_id_add'], 'name').'">'.FullDataTime($z['dtime_add']).
				'<tr><td class="label top">�������:<td>'.zayav_product_spisok($z['id']).
				'<tr><td class="label">�����:<td>'.$z['adres'].
				'<tr><td class="label">������ ������:'.
					'<td><div style="background-color:#'._zayavStatusColor(2).'" class="status">'._zayavStatusName(2).'</div>'.
			'</table>'.
			'<div class="vkButton dogovor_create"><button>��������� �������</button></div>'.
			_vkComment('zayav', $z['id']).
		'</div>'.
		'<div class="histories">'.report_history_spisok(1, array('zayav_id'=>$z['id'])).'</div>'.
	'</div>';
}//dogovor_info()
function set_info($z) {
	$sql = "SELECT * FROM `zayav_dogovor` WHERE `zayav_id`=".$z['id'];
	$dog = mysql_fetch_assoc(query($sql));
	$ex = explode(' ', $dog['dtime_add']);
	$d = explode('-', $ex[0]);

	$money = money_spisok(1, array('zayav_id'=>$z['id']));

	return
	'<script type="text/javascript">'.
		'var ZAYAV={'.
			'id:'.$z['id'].
		'},'.
		'OPL={'.
			'from:"zayav",'.
			'client_id:'.$z['client_id'].','.
			'client_fio:"'.addslashes(_clientLink($z['client_id'])).'",'.
			'zayav_id:'.$z['id'].','.
			'zayav_name:"<b>�'.$z['id'].'</b>"'.
		'};'.
	'</script>'.
	'<div class="zayav-info">'.
		'<div id="dopLinks">'.
//			'<a class="delete">������� ������</a>'.
			'<a class="link sel zinfo">����������</a>'.
			'<a class="link zedit">��������������</a>'.
			'<a class="link">���������</a>'.
			'<a class="link oplata-add">������ �����</a>'.
			'<a class="link hist">�������</a>'.
		'</div>'.
		'<div class="headName">��������� �'.$z['set_nomer'].'</div>'.
		'<div class="content">'.
			'<table class="tabInfo">'.
				'<tr><td class="label">������:<td>'._clientLink($z['client_id']).
//				'<tr><td class="label">���� �����:'.
//					'<td class="dtime_add" title="������ ��� '._viewer($z['viewer_id_add'], 'name').'">'.FullDataTime($z['dtime_add']).
				'<tr><td class="label">�������:'.
					'<td><b class="dogn" title="�� '.$d[2].'/'.$d[1].'/'.$d[0].' �. �� ����� '.$dog['sum'].' ���. ">�'.$z['dogovor_nomer'].'</b> '.
						 _dogLink($z['dogovor_nomer'], '', 'img_word').
						'<a class="reneg">�������������</a>'.
				'<tr><td class="label top">�������:<td>'.zayav_product_spisok($z['id']).
				'<tr><td class="label">����� ���������:<td>'.$z['adres'].
				'<tr><td class="label">������:'.
					'<td><div style="background-color:#'._zayavStatusColor($z['set_status']).'" class="status">'._zayavStatusName($z['set_status']).'</div>'.
			'</table>'.
			'<div class="headBlue'.($money['all'] ? '' : ' dn').'">�������<a class="add oplata-add">������ �����</a></div>'.
			'<div id="money_spisok">'.($money['all'] ? $money['spisok'] : '').'</div>'.
			_vkComment('zayav', $z['id']).
		'</div>'.
		'<div class="histories">'.report_history_spisok(1, array('zayav_id'=>$z['id'])).'</div>'.
	'</div>';
}//set_info()
function dogovor_print($dog_id) {
	require_once(VKPATH.'clsMsDocGenerator.php');

	$v = $dog_id;
	if(!is_array($v)) {
		$sql = "SELECT * FROM `zayav_dogovor` WHERE `id`=".$dog_id." LIMIT 1";
		$v = mysql_fetch_assoc(query($sql));
	}

	$sql = "SELECT * FROM `setup_global`";
	$g = mysql_fetch_assoc(query($sql));

	$ex = explode(' ', $v['dtime_add']);
	$d = explode('-', $ex[0]);

	$ex = explode(' ', $v['fio']);
	$fioPodpis = $ex[0].' '.
				 (isset($ex[1]) ? ' '.$ex[1][0].'.' : '').
				 (isset($ex[2]) ? ' '.$ex[2][0].'.' : '');

	$doc = new clsMsDocGenerator(
		$pageOrientation = 'PORTRAIT',
		$pageType = 'A4',
		$cssFile = DOCUMENT_ROOT.'/css/dogovor.css',
		$topMargin = 0.6,
		$rightMargin = 2.5,
		$bottomMargin = 1.5,
		$leftMargin = 1.0
	);

	$dopl = $v['sum'] - $v['avans'];

	$doc->addParagraph(
	'<div class="head-name">������� �'.$v['id'].'</div>'.
	'<table class="city_data"><tr><td>����� �������<th>'.$d[2].'/'.$d[1].'/'.$d[0].' �.</table>'.
	'<div class="paragraph">'.
		'<p>�������� � ������������ ���������������� ����������� ��������, '.
		'� ���� ��������� �� ��������, ��������� ���� �������������, ����������� �� ��������� ������������, '.
		'� ����� �������, � '.$v['fio'].', '.($v['pasp_empty'] ? $v['adres'] : $v['pasp_adres']).', ��������� � ���������� ���������, � ������ �������, '.
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
	($v['pasp_empty'] ? '<br />'.$v['adres'] :
				'������� ����� '.$v['pasp_seria'].' '.$v['pasp_nomer'].'<br />'.
				'����� '.$v['pasp_ovd'].' '.$v['pasp_data'].'<br /><br />'.
				$v['pasp_adres']).
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
		'<tr><td class="label">�����:<td class="title">'.$v['id'].'<td class="label">��������:<td>'.$fioPodpis.
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
		'<tr><td class="label">�����:<td class="title">'.$v['id'].'<td class="label">��������:<td>'.$fioPodpis.
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

	$doc->output('dogovor_'.$v['id'], is_numeric($dog_id) ? PATH.'files/dogovor' : '');
}//dogovor_print()

function zayav_product_spisok($arr, $type='html') {
	if(!is_array($arr)) {
		$sql = "SELECT * FROM `zayav_product` WHERE `zayav_id`=".$arr." ORDER BY `id`";
		$q = query($sql);
		$arr = array();
		while($r = mysql_fetch_assoc($q))
			$arr[] = $r;
	}
	if(empty($arr))
		return '';
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

/*
function zayavFilter($v) {
	if(empty($v['category']) || !preg_match(REGEXP_NUMERIC, $v['category']))
		$v['category'] = 0;
	if(empty($v['status']) || !preg_match(REGEXP_NUMERIC, $v['status']))
		$v['status'] = 0;
	if(empty($v['client']) || !preg_match(REGEXP_NUMERIC, $v['client']))
		$v['client'] = 0;

	$filter = array();
	$filter['find'] = htmlspecialchars(trim(@$v['find']));
	$filter['desc'] = intval(@$v['desc']) == 1 ? 'ASC' : 'DESC';
	$filter['category'] = intval($v['category']);
	$filter['status'] = intval($v['status']);
	if($v['client'] > 0)
		$filter['client'] = intval($v['client']);
	return $filter;
}//zayavFilter()
function zayav_data($page=1, $filter=array(), $limit=20) {
	$cond = "`status`>0";

	if(empty($filter['desc']))
		$filter['desc'] = 'DESC';
	if(!empty($filter['find'])) {
		$cond .= " AND `adres_set` LIKE '%".$filter['find']."%'";
		if($page ==1 && preg_match(REGEXP_NUMERIC, $filter['find']))
			$zayav_id = intval($filter['find']);
	} else {
		if(isset($filter['category']) && $filter['category'] > 0)
			$cond .= " AND `zamer`=".($filter['category'] == 1 ? 1 : 0);
		if(isset($filter['status']) && $filter['status'] > 0)
			$cond .= " AND `status`=".$filter['status'];
		if(isset($filter['client']) && $filter['client'] > 0)
			$cond .= " AND `client_id`=".$filter['client'];
	}
	$zayav = array();
	$client = array();

	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return $send;

	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `id` ".$filter['desc']."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		if(isset($zayav_id) && $zayav_id == $r['id'])
			continue;
		$zayav[$r['id']] = $r;
		$client[$r['client_id']] = $r['client_id'];
	}

	if(empty($filter['client']))
		$client = _clientLink($client);

	$sql = "SELECT * FROM `zayav_product` WHERE `zayav_id` IN (".implode(',', array_keys($zayav)).") ORDER BY `id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zayav[$r['zayav_id']]['product'][] = $r;

	foreach($zayav as $id => $r) {
		$unit = array(
			'zamer' => $r['zamer'],
			'status_color' => _zayavStatusColor($r['status']),
			'product' => $r['product'],
			'dtime' => FullData($r['dtime_add'], 1)
		);
		if(empty($filter['client']))
			$unit['client'] = $client[$r['client_id']];
		$send['spisok'][$id] = $unit;
	}
	$send['limit'] = $limit;
	if($start + $limit < $send['all'])
		$send['next'] = $page + 1;
	return $send;
}//zayav_data()
function zayav_count($count, $filter_break_show=true) {
	return
		($filter_break_show ? '<a id="filter_break">�������� ������� ������</a>' : '').
		($count > 0 ?
			'�������'._end($count, '�', '�').' '.$count.' ����'._end($count, '��', '��', '��')
			:
			'������ �� �������');
}//zayav_count()
function zayav_list($data, $values) {
	return
	'<div id="zayav">'.
		'<div class="result">'.zayav_count($data['all']).'</div>'.
		'<table class="tabLR">'.
			'<tr><td id="spisok">'.zayav_spisok($data).
				'<td class="right">'.
				'<div id="buttonCreate"><a HREF="'.URL.'&p=zayav&d=add&back=zayav">����� �����</a></div>'.
				'<div id="find"></div>'.
				'<div class="findHead">�������</div>'.
//				_radio('sort', array(1=>'�� ���� ����������',2=>'�� ���������� �������'), $values['sort']).
				_check('desc', '�������� �������', $values['desc']).
				'<div class="condLost'.(!empty($values['find']) ? ' hide' : '').'">'.
				//	'<div class="findHead">���������</div>'.
				//	_radio('category', array(0=>'��� ������',1=>'�����',2=>'���������'), $values['category'], 1).
					'<div class="findHead">������</div>'.
					_rightLink('status', _zayavStatusName(), $values['status']).
				'</div>'.
		'</table>'.
	'</div>'.
	'<script type="text/javascript">'.
		'var ZAYAV = {'.
			'find:"'.unescape($values['find']).'",'.
			'status:'.$values['status'].
		'};'.
	'</script>';
}//zayav_list()
function zayav_spisok($data) {
	if(!isset($data['spisok']))
		return '<div class="_empty">������ �� �������.</div>';
	$send = '';
	foreach($data['spisok'] as $id => $r)
		$send .=
		'<div class="zayav_unit" style="background-color:#'.$r['status_color'].'" val="'.$id.'">'.
			'<div class="dtime">'.$r['dtime'].'</div>'.
			'<a class="name">'.($r['zamer'] ? '�����' : '���������').' �'.$id.'</a>'.
			'<table class="ztab">'.
				(isset($r['client']) ? '<tr><td class="label">������:<td>'.$r['client'] : '').
				'<tr><td class="label top">�������:<td>'.zayav_product_spisok($r['product']).
			'</table>'.
		'</div>';

	if(isset($data['next']))
		$send .= '<div class="ajaxNext" val="'.($data['next']).'"><span>��������� '.$data['limit'].' ������</span></div>';
	return $send;
}//zayav_spisok()

function zayav_info_($zayav_id) {
	$sql = "SELECT * FROM `zayav` WHERE `status`>0 AND `id`=".$zayav_id." LIMIT 1";
	if(!$zayav = mysql_fetch_assoc(query($sql)))
		return _noauth('������ �� ����������.');

	$sql = "SELECT *
		FROM `accrual`
		WHERE `status`=1
		  AND `zayav_id`=".$zayav_id."
		ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$money = array();
	$accSum = 0;
	while($acc = mysql_fetch_assoc($q)) {
		$money[strtotime($acc['dtime_add'])] = zayav_accrual_unit($acc);
		$accSum += $acc['sum'];
	}

	$sql = "SELECT *
		FROM `money`
		WHERE `deleted`=0
		  AND `sum`>0
		  AND `zayav_id`=".$zayav_id."
		ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$opSum = 0;
	while($op = mysql_fetch_assoc($q)) {
		$money[strtotime($op['dtime_add'])] = zayav_oplata_unit($op);
		$opSum += $op['sum'];
	}
	$dopl = $accSum - $opSum;
	ksort($money);

	$ex = explode(' ', $zayav['zamer_dtime']);
	$time = explode(':', $ex[1]);

	return
	'<script type="text/javascript">'.
		'var ZAYAV={'.
			'id:'.$zayav_id.','.
			'client_id:'.$zayav['client_id'].','.
			'nomer_vg:"'.$zayav['nomer_vg'].'",'.
			'product:['.zayav_product_spisok($zayav_id, 'json').'],'.
			'adres_set:"'.$zayav['adres_set'].'",'.
			'day:"'.$ex[0].'",'.
			'hour:'.intval($time[0]).','.
			'min:'.intval($time[1]).','.
			'dur:'.$zayav['zamer_duration'].
		'};'.
	'</script>'.
	'<div id="zayavInfo">'.
		'<div id="dopLinks">'.
			'<a class="delete'.(!empty($money) ?  ' dn': '').'">������� ������</a>'.
			'<a class="link sel zinfo">����������</a>'.
			'<a class="link zedit">��������������</a>'.
			(!$zayav['zamer'] ? '<a class="link acc_add">���������</a>' : '').
			(!$zayav['zamer'] ? '<a class="link op_add">������� �����</a>' : '').
			'<a class="link hist">�������</a>'.
		'</div>'.
		'<div class="headName">'.($zayav['zamer'] ? '������ �� �����' : '��������� �������').' �'.$zayav_id.'</div>'.
		'<div class="content">'.
			'<table class="tabInfo">'.
				'<tr><td class="label">������:<td>'._clientLink($zayav['client_id']).
				'<tr><td class="label">���� �����:'.
					'<td class="dtime_add" title="������ ��� '._viewer($zayav['viewer_id_add'], 'name').'">'.FullDataTime($zayav['dtime_add']).
				(!$zayav['zamer'] ? '<tr><td class="label">����� ��������:<td><a>��������� �������</a>' : '').
				($zayav['nomer_vg'] ? '<tr><td class="label">����� ��:<td>'.$zayav['nomer_vg'] : '').
				'<tr><td class="label top">�������:<td>'.zayav_product_spisok($zayav_id).
				'<tr><td class="label">����� '.($zayav['zamer'] ? '������' : '���������').':<td>'.$zayav['adres_set'].
				($zayav['zamer'] ?
					'<tr><td class="label">���� ������:'.
					    '<td><span class="zamer-dtime" title="'._zamerDuration($zayav['zamer_duration']).'">'.FullDataTime($zayav['zamer_dtime']).'</span>'.
							'<span class="zamer-left">'.remindDayLeft($zayav['zamer_dtime']).'</span>' : '').
				'<tr><td class="label">������:'.
					'<td><div style="background-color:#'._zayavStatusColor($zayav['status']).'" class="status zamer_'.($zayav['zamer'] ? 'status' : 'set').'">'.
							_zayavStatusName($zayav['status']).
						'</div>'.
						'<div class="status_dtime">�� '.FullDataTime($zayav['status_dtime'], 1).'</div>'.
				'<tr class="acc_tr'.($accSum > 0 ? '' : ' dn').'"><td class="label">���������: <td><b class="acc">'.$accSum.'</b> ���.'.
				'<tr class="op_tr'.($opSum > 0 ? '' : ' dn').'"><td class="label">��������:	<td><b class="op">'.$opSum.'</b> ���.'.
					'<span class="dopl'.($dopl == 0 ? ' dn' : '').'" title="����������� �������'."\n".'���� �������� �������������, �� ��� ���������">'.
						($dopl > 0 ? '+' : '').$dopl.
					'</span>'.
			'</table>'.
	//		'<div class="headBlue">�������<a class="add remind_add">�������� �������</a></div>'.
	//		'<div id="remind_spisok">'.report_remind_spisok(remind_data(1, array('zayav'=>$zayav['id']))).'</div>'.
			_vkComment('zayav', $zayav_id).

			(!$zayav['zamer'] ?
				'<div class="headBlue mon">���������� � �������'.
					'<a class="add op_add">������� �����</a>'.
					'<em>::</em>'.
					'<a class="add acc_add">���������</a>'.
				'</div>'.
				'<table class="_spisok _money">'.implode($money).'</table>' : '').
		'</div>'.
		'<div class="histories">'.report_history_spisok(1, array('zayav_id'=>$zayav_id)).'</div>'.
	'</div>';
}//zayav_info()
function zayav_accrual_unit($acc) {
	return
	'<tr><td class="sum acc" title="����������">'.$acc['sum'].'</td>'.
		'<td>'.$acc['prim'].'</td>'.
		'<td class="dtime" title="�������� '._viewer(isset($acc['viewer_id_add']) ? $acc['viewer_id_add'] : VIEWER_ID, 'name').'">'.
			FullDataTime(isset($acc['dtime_add']) ? $acc['dtime_add'] : curTime()).
		'</td>'.
		'<td class="del"><div class="img_del acc_del" title="������� ����������" val="'.$acc['id'].'"></div></td>'.
	'</tr>';
}//zayav_accrual_unit()
function zayav_oplata_unit($op) {
	return
	'<tr><td class="sum op" title="�����">'.$op['sum'].'</td>'.
		'<td><em>'._prihodType($op['prihod_type']).($op['prim'] ? ':' : '').'</em>'.$op['prim'].'</td>'.
		'<td class="dtime" title="����� ��� '._viewer(isset($op['viewer_id_add']) ? $op['viewer_id_add'] : VIEWER_ID, 'name').'">'.
			FullDataTime(isset($op['dtime_add']) ? $op['dtime_add'] : curTime()).
		'</td>'.
		'<td class="del"><div class="img_del op_del" title="������� �����" val="'.$op['id'].'"></div></td>'.
	'</tr>';
}//zayav_oplata_unit()
*/




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
function remindCalendar($data=array()) {
	$year = empty($data['year']) ? strftime('%Y') : $data['year'];
	$month = empty($data['month']) ? strftime('%m') : ($data['month'] < 10 ? 0 : '').$data['month'];
	$days = empty($data['days']) ? array() : $data['days'];

	$send = '<div class="remind_calendar">'.
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
}//remindCalendar()
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
				remindCalendar($data);
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
					'<div id="cal_div">'.remindCalendar(array('days'=>$days)).'</div>'.
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
		'money' => '�������'
	);

	$d = empty($_GET['d']) ? $def : $_GET['d'];

	$links = '';
	if($pages)
		foreach($pages as $p => $name)
			$links .= '<a href="'.URL.'&p=report&d='.$p.'"'.($d == $p ? ' class="sel"' : '').'>'.$name.'</a>';

	switch(@$_GET['d']) {
		default:
		case 'history':
			$left = report_history_spisok();
			break;
		case 'money':
			$data = money_spisok();
			$left = '<div class="headName">������ ��������</div>'.$data['spisok'];
			break;
	}
	return
	'<table class="tabLR" id="report">'.
		'<tr><td class="left">'.$left.
			'<td class="right"><div class="rightLink">'.$links.'</div>'.
	'</table>';
}//report()

function history_insert($arr) {
	$sql = "INSERT INTO `history` (
			   `type`,
			   `value`,
			   `value1`,
			   `value2`,
			   `value3`,
			   `client_id`,
			   `zayav_id`,
			   `viewer_id_add`
			) VALUES (
				".$arr['type'].",
				'".(isset($arr['value']) ? $arr['value'] : '')."',
				'".(isset($arr['value1']) ? $arr['value1'] : '')."',
				'".(isset($arr['value2']) ? $arr['value2'] : '')."',
				'".(isset($arr['value3']) ? $arr['value3'] : '')."',
				".(isset($arr['client_id']) ? $arr['client_id'] : 0).",
				".(isset($arr['zayav_id']) ? $arr['zayav_id'] : 0).",
				".VIEWER_ID."
			)";
	query($sql);
}//history_insert()
function history_types($v) {
	switch($v['type']) {
		case 1: return '�������� ������ ������� '.$v['client'].'.';
		case 2: return '��������� ������ ������� '.$v['client'].':<div class="changes">'.$v['value'].'</div>';
		case 3: return '�������� ������� '.$v['client'].'.';

		case 4: return '�������� ����� ������ �� ����� '._zamerLink($v['zayav_id'], $v['value']).' ��� ������� '.$v['client'].'.';
		case 5: return '��������� ������ ������ '.$v['zayav'].':<div class="changes">'.$v['value'].'</div>';
		case 6: return '�������� ������ '.$v['zayav'].'.';

		case 7: return '���������� �� ����� <b>'.$v['value'].'</b> ���.'.($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').' �� ������ '.$v['zayav'].'.';
		case 8: return '�������� ���������� �� ����� <b>'.$v['value'].'</b> ���.'.($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').' � ������ '.$v['zayav'].'.';
		case 9: return '�������������� ���������� �� ����� <b>'.$v['value'].'</b> ���.'.($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').' � ������ '.$v['zayav'].'.';

		case 10: return
			'����� <span class="oplata">'._prihodType($v['value2']).'</span> '.
			'�� ����� <b>'.$v['value'].'</b> ���.'.
			($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').
			' �� ������ '._zamerLink($v['zayav_id'], $v['zayav_id']).'.';
		case 11: return
			'�������� ������� <span class="oplata">'._prihodType($v['value2']).'</span> '.
			'�� ����� <b>'.$v['value'].'</b> ���.'.
			($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').
			' � ������ '._zamerLink($v['zayav_id'], $v['zayav_id']).'.';
		case 12: return
			'�������������� ������� <span class="oplata">'._prihodType($v['value2']).'</span> '.
			'�� ����� <b>'.$v['value'].'</b> ���.'.
			($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').
			' � ������ '._zamerLink($v['zayav_id'], $v['zayav_id']).'.';

		case 13: return '���������� ������ ���������� '._viewer($v['value'], 'link').'.';
		case 14: return '�������� ���������� '._viewer($v['value'], 'link').'.';

		case 15: return '��������� ���������� � ���� ��� ����������������� ������ '._zamerLink($v['zayav_id'], $v['value']).':<div class="changes">'.$v['value1'].'</div>';
		case 16: return '����� '._zamerLink($v['zayav_id'], $v['value']).' �������� � ��������� �� ���������� ��������.';
		case 17: return '����� '._zamerLink($v['zayav_id'], $v['value']).' ������.';
		case 18: return '����� '._zamerLink($v['zayav_id'], $v['value']).' ������������.';
		case 19: return
			'���������� '._dogLink($v['value1'], '�������� �'.$v['value1']).
			' �� '.$v['value2'].' �.'.
			' �� ����� <b>'.$v['value3'].'</b> ���.'.
			' ��� ������ '._zamerLink($v['zayav_id'], $v['value']).'.';
		case 20: return
			'�������� ���������� ������� ��  �� ����� <b>'.$v['value2'].'</b> ���.'.
			' ��� ������ '._zamerLink($v['zayav_id'], $v['value']).
			' ��� ���������� '._dogLink($v['value1'], '�������� �'.$v['value1']).'.';

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
		default: return $v['type'];
	}
}//history_types()
function report_history_spisok($page=1, $filter=array()) {
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
	$viewer = array();
	$client = array();
	while($r = mysql_fetch_assoc($q)) {
		$viewer[$r['viewer_id_add']] = $r['viewer_id_add'];
		if($r['client_id'])
			$client[$r['client_id']] = $r['client_id'];
		$history[] = $r;
	}
	$viewer = _viewer($viewer);
	$client = _clientLink($client);
	$send = '';
	$time = strtotime($history[0]['dtime_add']);
	$txt = '';
	$viewer_id = $history[0]['viewer_id_add'];
	$count = count($history) - 1;
	foreach($history as $n => $r) {
		if(!$time) {
			$time = strtotime($r['dtime_add']);
			$txt = '';
			$viewer_id = $r['viewer_id_add'];
		}
		if($r['client_id'])
			$r['client'] = $client[$r['client_id']];
		$txt .= '<div class="txt">'.history_types($r).'</div>';
		if($count == $n
		   || $time - strtotime($history[$n + 1]['dtime_add']) > 600
		   || $viewer_id != $history[$n + 1]['viewer_id_add']) {
			$time = 0;
			$send .=
			'<div class="history_unit">'.
				'<div class="head">'.FullDataTime($r['dtime_add']).$viewer[$r['viewer_id_add']]['link'].'</div>'.
				$txt.
			'</div>';
		}
	}
	if($start + $limit < $all)
		$send .= '<div class="ajaxNext" id="report_history_next" val="'.($page + 1).'"><span>�����...</span></div>';
	return $send;
}//report_history_spisok()


function _zamerNomer($arr) {//���������� � ������ 'zamer_nomer', ����������� �� zayav_id
	if(empty($arr))
		return array();
	$ids = array(); // �������� ������
	$arrIds = array();
	foreach($arr as $r)
		if($r['zayav_id']) {
			$ids[$r['zayav_id']] = $r['zayav_id'];
			$arrIds[$r['zayav_id']][] = $r['id'];
		}
	if(empty($ids))
		return $arr;
	$sql = "SELECT `id`,`zamer_nomer` FROM `zayav` WHERE `id` IN (".implode(',', array_keys($ids)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($arrIds[$r['id']] as $id)
			$arr[$id]['zamer_nomer'] = $r['zamer_nomer'];
	return $arr;
}//_zamerNomer()

function money_insert($v) {//�������� �������
	if(empty($v['from']))
		$v['from'] = '';
	if($v['zayav_id'] > 0) {
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

	if(empty($v['kassa']))
		$v['kassa'] = 0;
	$v['kassa'] = _prihodType($v['type'], 'kassa') ? $v['kassa'] : 0;
	if($v['kassa'] > 1)
		return false;

	$sql = "INSERT INTO `money` (
				`zayav_id`,
				`client_id`,
				`prihod_type`,
				`sum`,
				`kassa`,
				`prim`,
				`viewer_id_add`
			) VALUES (
				".$v['zayav_id'].",
				".$v['client_id'].",
				".$v['type'].",
				".$v['sum'].",
				".$v['kassa'].",
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
		case 'client': return money_spisok(1, array('client_id'=>$v['client_id'],'limit'=>15));
		case 'zayav': return money_spisok(1, array('zayav_id'=>$v['zayav_id'],'limit'=>10));
		default: return $insert_id;
	}
}//money_insert()
function moneyFilter($v) {
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
}//moneyFilter()
function money_spisok($page=1, $filter=array()) {
	$cond = '`deleted`=0 AND `sum`>0';

	$filter = moneyFilter($filter);
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

	$money = _zamerNomer($money);

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
				' �� ����� <b>'.$send['sum'].'</b> ���.'.
			'</div>' : '').
			'<table class="_spisok _money">'.
		(!$filter['zayav_id'] ?
				'<tr><th class="sum">�����'.
					'<th>��������'.
					'<th class="data">����'.
					'<th>'
		: '');
	foreach($money as $r) {
		$about = '<span class="type">'._prihodType($r['prihod_type']).':</span> ';
		if($r['dogovor_nomer'] > 0)
			$about .= '��������� ������'.
				(!$filter['zayav_id'] ?
				' ��� ������ '._zamerLink($r['zayav_id'], $r['zamer_nomer']).
				' ��� ���������� '._dogLink($r['dogovor_nomer'], '�������� �'.$r['dogovor_nomer']).'.' : '');
		elseif($r['zayav_id'] > 0)
			$about .= '������ '.$r['zayav_id'].'.';

		$about .= ' '.$r['prim'];
		$send['spisok'] .=
			'<tr val="'.$r['id'].'"><td class="sum"><b>'.$r['sum'].'</b>'.
				'<td>'.$about.
				'<td class="dtime" title="��'.(_viewer($r['viewer_id_add'], 'sex') == 1 ? '����' : '��').' '._viewer($r['viewer_id_add'], 'name').'">'.FullDataTime($r['dtime_add']).
				'<td class="ed"><a href="'.URL.'&p=cashmemo&id='.$r['id'].'" class="img_doc" target="_blank"></a>'.
					(!$r['dogovor_nomer'] ? '<div class="img_del"></div>' : '');
	}
	if($start + $filter['limit'] < $send['all']) {
		$c = $send['all'] - $start - $filter['limit'];
		$c = $c > $filter['limit'] ? $filter['limit'] : $c;
		$send['spisok'] .=
			'<tr class="ajaxNext" val="'.($page + 1).'" id="money_next"><td colspan="4">'.
				'<span>�������� ��� '.$c.' ������'._end($c, '', '�', '��').'</span>';
	}
	$send['spisok'] .= '</table>';
	return $send;
}//money_spisok()
function cash_memo() {
	if(!preg_match(REGEXP_NUMERIC, @$_GET['id'])) {
		echo '������������ id.';
		exit;
	}
	$id = intval($_GET['id']);
	$sql = "SELECT *
			FROM `money`
			WHERE `deleted`=0
			  AND `id`=".$id;
	if(!$r = mysql_fetch_assoc(query($sql))) {
		echo '������� id = '.$id.' �� ����������.';
		exit;
	}

	setlocale(LC_ALL, "ru_RU.CP1251");

	$sql = "SELECT * FROM `setup_global`";
	$g = mysql_fetch_assoc(query($sql));

	$zayav = array();
	if($r['zayav_id']) {
		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$r['zayav_id'];
		$zayav = mysql_fetch_assoc(query($sql));
	}

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
				($zayav['dogovor_nomer'] ? ' �� �������� �'.$zayav['dogovor_nomer'] : '').
				' �� '.zayav_product_spisok($r['zayav_id'], 'cash').
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
	exit;
}//cash_memo()


// ---===! setup !===--- ������ ��������

function setup() {
	$pageDef = 'worker';
	$pages = array(
		'worker' => '����������',
		'rekvisit' => '��������� �����������',
		'product' => '���� �������',
		'prihodtype' => '���� ��������'
	);

	if(!RULES_WORKER)
		unset($pages['worker']);
	if(!RULES_REKVISIT)
		unset($pages['rekvisit']);
	if(!RULES_PRODUCT)
		unset($pages['product']);
	if(!RULES_PRIHODTYPE)
		unset($pages['prihodtype']);

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
		case 'prihodtype': $left = setup_prihodtype(); break;
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
	$sql = "SELECT `viewer_id`,
				   CONCAT(`first_name`,' ',`last_name`) AS `name`,
				   `photo`,
				   `admin`
			FROM `vk_user`
			WHERE `worker`=1
			  AND `viewer_id`!=982006
			ORDER BY `dtime_add`";
	$q = query($sql);
	$send = '';
	while($r = mysql_fetch_assoc($q)) {
		$send .=
		'<table class="unit" val="'.$r['viewer_id'].'">'.
			'<tr><td class="photo"><img src="'.$r['photo'].'">'.
				'<td>'.
					($r['admin'] ? '' : '<div class="img_del"></div>').
					'<a class="name">'.$r['name'].'</a>'.
					($r['admin'] ? '' : '<a href="'.URL.'&p=setup&d=worker&id='.$r['viewer_id'].'" class="rules_set">��������� �����</a>').
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
	if($u['admin'])
		return '���������� �������� ����� ���������� <b>'.$u['name'].'</b>.';
//	print_r(workerRulesArray($u['rules']));
	$rule = workerRulesArray($u['rules']);
	return
	'<script type="text/javascript">var RULES_VIEWER_ID='.$viewer_id.';</script>'.
	'<div id="setup_rules">'.
		'<div class="headName">��������� ���� ��� ���������� '.$u['name'].'</div>'.
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
						_check('rules_prihodtype', '���� ��������', $rule['RULES_PRIHODTYPE']).
					'</div>'.
			'</table>'.
		'</div>'.
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
	$sql = "SELECT `p`.`id`,
				   `p`.`name`,
				   `p`.`dogovor`,
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
					'<th>���������<br />����������<br />��������'.
					'<th>�������'.
					'<th>���-��<br />������'.
					'<th>';
	foreach($product as $id => $r)
		$send .= '<tr val="'.$id.'">'.
					'<td class="name"><a href="'.URL.'&p=setup&d=product&id='.$id.'">'.$r['name'].'</a>'.
					'<td class="dog">'.($r['dogovor'] ? '��' : '').
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

function setup_prihodtype() {
	if(!RULES_PRIHODTYPE)
		return _norules('��������� ����� ��������');
	return
	'<div id="setup_prihodtype">'.
		'<div class="headName">��������� ����� ��������<a class="add">��������</a></div>'.
		'<div class="spisok">'.setup_prihodtype_spisok().'</div>'.
	'</div>';
}//setup_prihodtype()
function setup_prihodtype_spisok() {
	$sql = "SELECT `p`.`id`,
				   `p`.`name`,
				   `p`.`kassa_put`,
				   COUNT(`m`.`id`) AS `money`
			FROM `setup_prihodtype` AS `p`
			  LEFT JOIN `money` AS `m`
			  ON `p`.`id`=`m`.`prihod_type`
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
			FROM `setup_prihodtype` AS `p`,`money` AS `m`
			WHERE `p`.`id`=`m`.`prihod_type` AND `m`.`deleted`=1
			GROUP BY `p`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$prihod[$r['id']]['del'] = $r['del'];

	$send =
	'<table class="_spisok">'.
		'<tr><th class="name">������������'.
			'<th class="kassa">�����������<br />��������<br />� �����'.
			'<th class="money">���-��<br />��������'.
			'<th class="set">'.
	'</table>'.
	'<dl class="_sort" val="setup_prihodtype">';
	foreach($prihod as $id => $r) {
		$money = $r['money'] ? '<b>'.$r['money'].'</b>' : '';
		$money .= isset($r['del']) ? ' <span class="del" title="� ��� ����� ��������">('.$r['del'].')</span>' : '';
		$send .='<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="kassa">'.($r['kassa_put'] ? '��' : '').
					'<td class="money">'.$money.
					'<td class="set">'.
						'<div class="img_edit"></div>'.
						(!$r['money'] && $id > 1 ? '<div class="img_del"></div>' : '').
			'</table>';
	}
	$send .= '</dl>';
	return $send;
}//setup_prihodtype_spisok()