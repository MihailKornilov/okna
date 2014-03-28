<?php
function _hashRead() {
	if(PIN_ENTER) { // Если требуется пин-код, hash сохраняется в cookie
		setcookie('hash', empty($_GET['hash']) ? @$_COOKIE['hash'] : $_GET['hash'], time() + 2592000, '/');
		return;
	}
	$_GET['hash'] = isset($_COOKIE['hash']) ? $_COOKIE['hash'] : @$_GET['hash'];
	setcookie('hash', '', time() - 5, '/');
	$_GET['p'] = isset($_GET['p']) ? $_GET['p'] : 'zayav';
	if(empty($_GET['hash'])) {
		if(isset($_GET['start'])) {// восстановление последней посещённой страницы
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
		'<title>Evrookna - Приложение '.API_ID.'</title>'.

		//Отслеживание ошибок в скриптах
		(SA ? '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/errors.js?'.VERSION.'"></script>' : '').

		//Стороние скрипты
		'<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/jquery-2.0.3.min.js"></script>'.
//		'<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/highstock.js"></script>'.
		'<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/xd_connection'.(DEBUG ? '' : '.min').'.js"></script>'.

		//Установка начального значения таймера.
		(SA ? '<script type="text/javascript">var TIME=(new Date()).getTime();</script>' : '').

		'<script type="text/javascript">'.
			(LOCAL ? 'for(var i in VK)if(typeof VK[i]=="function")VK[i]=function(){return false};' : '').
			'var DOMAIN="'.DOMAIN.'",'.
			'VALUES="'.VALUES.'",'.
			'VIEWER_ID='.VIEWER_ID.';'.
		'</script>'.

		//Подключение api VK. Стили VK должны стоять до основных стилей сайта
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
				'<a class="debug_toggle'.(DEBUG ? ' on' : '').'">В'.(DEBUG ? 'ы' : '').'ключить Debug</a> :: '.
				'<a id="cache_clear">Очисить кэш ('.VERSION.')</a> :: '.
				'<a href="'.SITE.'/_sxdump" target="_blank">sxd</a> :: '.
				'sql <b>'.$sqlCount.'</b> ('.round($sqlTime, 3).') :: '.
				'php '.round(microtime(true) - TIME, 3).' :: '.
				'js <em></em>'.
			'</div>'
			.(DEBUG ? $sqlQuery : '');
	$html .= '</div></body></html>';
}//_footer()

function GvaluesCreate() {//Составление файла G_values.js
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

function _product($product_id=false) {//Список изделий для заявок
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
function _productSub($product_id=false) {//Список изделий для заявок
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
function _invoice($type_id=false, $i='name') {//Список изделий для заявок
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
function _income($type_id=false, $i='name') {//Список изделий для заявок
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
function _expense($type_id=false, $i='name') {//Список изделий для заявок
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
		'30' => '30 мин.',
		'60' => '1 час',
		'90' => '1 час 30 мин.',
		'120' => '2 часа',
		'150' => '2 часа 30 мин.',
		'180' => '3 часа'
	);
	return $v ? $arr[$v] : $arr;
}//_zamerDuration()
function _zayavRashod($type_id=false, $i='name') {//Список расходов заявки
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
			'name' => 'Клиенты',
			'page' => 'client',
			'show' => 1
		),
		array(
			'name' => 'Заявки',
			'page' => 'zayav',
			'show' => 1
		),
		array(
			'name' => 'Напоминания'.($cRemind ? ' (<b>'.$cRemind.'</b>)' : ''),
			'page' => 'remind',
			'show' => 1
		),
		array(
			'name' => 'Отчёты'.(TRANSFER_CONFIRM ? ' (<b>'.TRANSFER_CONFIRM.'</b>)' : ''),
			'page' => 'report',
			'show' => 1
		),
		array(
			'name' => 'Настройки',
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
		'RULES_BONUS' => array(	    // Начисление бонусов: по дате внесения заявок, по дате выполнения заявок
			'def' => 0
		),
		'RULES_CASH' => array(	    // Внутренний наличный счёт
			'def' => 0
		),
		'RULES_GETMONEY' => array(	// Может принимать и передавать деньги:
			'def' => 0
		),
		'RULES_NOSALARY' => array(	// Не отображать в начислениях з/п:
			'def' => 0
		),
		'RULES_APPENTER' => array(	// Разрешать вход в приложение
			'def' => 0,
			'admin' => 1,
			'childs' => array(
				'RULES_WORKER' => array(	// Сотрудники
					'def' => 0,
					'admin' => 1
				),
				'RULES_RULES' => array(	    // Настройка прав сотрудников
					'def' => 0,
					'admin' => 1
				),
				'RULES_REKVISIT' => array(	// Реквизиты организации
					'def' => 0,
					'admin' => 1
				),
				'RULES_PRODUCT' => array(	// Виды изделий
					'def' => 0,
					'admin' => 1
				),
				'RULES_INCOME' => array(	// Реквизиты организации
					'def' => 0,
					'admin' => 1
				),
				'RULES_ZAYAVRASHOD' => array(// Расходы по заявке
					'def' => 0,
					'admin' => 1
				),
				'RULES_HISTORYSHOW' => array(// Видит историю действий
					'def' => 0,
					'admin' => 1
				),
				'RULES_MONEY' => array(	    // Может видеть платежи: только свои, все платежи
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
	return '<div class="norules">'.($txt ? '<b>'.$txt.'</b>: н' : 'Н').'едостаточно прав.</div>';
}//_norules()

function numberToWord($num, $firstSymbolUp=false) {
	$num = intval($num);
	$one = array(
		0 => 'ноль',
		1 => 'один',
		2 => 'два',
		3 => 'три',
		4 => 'четыре',
		5 => 'пять',
		6 => 'шесть',
		7 => 'семь',
		8 => 'восемь',
		9 => 'девять',
		10 => 'деcять',
		11 => 'одиннадцать',
		12 => 'двенадцать',
		13 => 'тринадцать',
		14 => 'четырнадцать',
		15 => 'пятнадцать',
		16 => 'шестнадцать',
		17 => 'семнадцать',
		18 => 'восемнадцать',
		19 => 'девятнадцать'
	);
	$ten = array(
		2 => 'двадцать',
		3 => 'тридцать',
		4 => 'сорок',
		5 => 'пятьдесят',
		6 => 'шестьдесят',
		7 => 'семьдесят',
		8 => 'восемьдесят',
		9 => 'девяносто'
	);
	$hundred = array(
		1 => 'сто',
		2 => 'двести',
		3 => 'триста',
		4 => 'четыреста',
		5 => 'пятьсот',
		6 => 'шестьсот',
		7 => 'семьсот',
		8 => 'восемьсот',
		9 => 'девятьсот'
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
		$word = ' тысяч'._end($t, 'а', 'и', '').' '.$word;
		if($t % 100 > 2 && $t % 100 < 20)
			$word = $one[$t % 100].$word;
		else {
			if($t % 10 == 1)
				$word = 'одна'.$word;
			elseif($t % 10 == 2)
				$word = 'две'.$word;
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

function viewerAdded($viewer_id) {//Вывод сотрудника, который вносил запись с учётом пола
	return 'Вн'.(_viewer($viewer_id, 'sex') == 1 ? 'есла' : 'ёс').' '._viewer($viewer_id, 'name');
}

function pin_enter() {
	return
	'<div id="pin-enter">'.
		'Пин: '.
		'<input type="password" id="pin" maxlength="10"> '.
		'<div class="vkButton"><button>Ok</button></div>'.
		'<div class="red">&nbsp;</div>'.
	'</div>';
}//pin_enter()

// ---===! client !===--- Секция клиентов

function _clientLink($arr, $fio=0) {//Добавление имени и ссылки клиента в массив или возврат по id
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
				$arr[$id]['client_link'] = '<a'.($r['deleted'] ? ' class="deleted" title="Клиент удалён"' : '').' href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>';
				$arr[$id]['client_fio'] = $r['fio'];
				$arr[$id]['client_tel'] = $r['telefon'];
			}
	}
	return $arr;
}//_clientLink()
function clientBalansUpdate($client_id) {//Обновление баланса клиента
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
			'result' => 'Клиентов не найдено.',
			'spisok' => '<div class="_empty">Клиентов не найдено.</div>'
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
		'result' => 'Найден'._end($all, ' ', 'о ').$all.' клиент'._end($all, '', 'а', 'ов').
					($dolg ? '<span class="dolg_sum">(Общая сумма долга = <b>'._sumSpace($dolg).'</b> руб.)</span>' : '')
	);
	foreach($spisok as $r)
		$send['spisok'] .= '<div class="unit'.(isset($r['comm']) ? ' i' : '').'">'.
			($r['balans'] != 0 ? '<div class="balans">Баланс: <b style=color:#'.($r['balans'] < 0 ? 'A00' : '090').'>'.round($r['balans'], 2).'</b></div>' : '').
			'<table>'.
				'<tr><td class="label">Имя:<td><a href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>'.
				($r['telefon'] ? '<tr><td class="label">Телефон:<td>'.$r['telefon'] : '').
				(isset($r['adres']) ? '<tr><td class="label">Адрес:<td>'.$r['adres'] : '').
				(isset($r['zayav_count']) ? '<tr><td class="label">Заявки:<td>'.$r['zayav_count'] : '').
			'</table>'.
		'</div>';
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .= '<div class="_next" val="'.($page + 1).'"><span>Показать ещё '.$c.' клиент'._end($c, 'а', 'а', 'ов').'</span></div>';
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
					'<div id="buttonCreate"><a>Новый клиент</a></div>'.
					'<div class="filter">'.
						_check('dolg', 'Должники').
						_check('note', 'Есть заметки').
						'<div class="findHead">Категории заявок</div>'.
						'<input type="hidden" id="zayav_cat">'.
						'<div class="findHead">Заказывались изделия</div>'.
						'<input type="hidden" id="product_id">'.
					'</div>'.
		'</table>'.
	'</div>';
}//client_list()

function clientInfoGet($client) {
	return
		($client['deleted'] ? '<div class="_info">Клиент удалён</div>' : '').
		'<div class="fio">'.$client['fio'].'</div>'.
		'<table class="cinf">'.
			'<tr><td class="label">Телефон:<td>'.$client['telefon'].
			'<tr><td class="label">Адрес:  <td>'.$client['adres'].
			'<tr><td class="label">Баланс: <td><b style=color:#'.($client['balans'] < 0 ? 'A00' : '090').'>'.round($client['balans'], 2).'</b>'.
		'</table>'.
	($client['pasp_seria'] || $client['pasp_nomer'] || $client['pasp_adres'] || $client['pasp_ovd'] || $client['pasp_data'] ?
		'<div class="pasp-head">Паспортные данные:</div>'.
		'<table class="pasp">'.
			'<tr><td class="label">Серия и номер:<td>'.$client['pasp_seria'].' '.$client['pasp_nomer'].
			'<tr><td class="label">Прописка:<td>'.$client['pasp_adres'].
			'<tr><td class="label">Выдан:<td>'.$client['pasp_ovd'].', '.$client['pasp_data'].
		'</table>' : '').
		'<div class="dtime_add">Клиента вн'.(_viewer($client['viewer_id_add'], 'sex') == 1 ? 'есла' : 'ёс').' '
			._viewer($client['viewer_id_add'], 'name').' '.
			FullData($client['dtime_add'], 1).
		'</div>';

}
function client_info($client_id) {
	$sql = "SELECT * FROM `client` WHERE `id`=".$client_id;
	if(!$client = mysql_fetch_assoc(query($sql)))
		return _noauth('Клиента не существует');

	if(!VIEWER_ADMIN && $client['deleted'])
		return _noauth('Клиент удалён');

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
				'content' => _zayavCategory($r, 'head').($r['dogovor_id'] ? ' <span>Договор '.$r['dogovor_nomer'].'</span>' : '')
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
						'<a class="sel">Информация</a>'.
 (!$client['deleted'] ? '<a class="cedit">Редактировать</a>'.
						'<a class="zayav_add"><b>Новая заявка</b></a>'.
						'<a class="income-add">Внести платёж</a>'.
						'<a class="remind-add">Внести напоминание</a>'.
						'<a class="cdel">Удалить клиента</a>'
 : '').
					'</div>'.
		'</table>'.

		'<div id="dopLinks">'.
			'<a class="link sel" val="zayav">Заявки'.($zayavCount ? ' ('.$zayavCount.')' : '').'</a>'.
			'<a class="link" val="money">Платежи'.($money['all'] ? ' ('.$money['all'].')' : '').'</a>'.
			'<a class="link" val="remind">Напоминания'.($remindCount ? ' ('.$remindCount.')' : '').'</a>'.
			'<a class="link" val="comm">Заметки'.($commCount ? ' ('.$commCount.')' : '').'</a>'.
			(RULES_HISTORYSHOW ? '<a class="link" val="hist">История'.($histCount ? ' ('.$histCount.')' : '').'</a>' : '').
		'</div>'.

		'<table class="tabLR">'.
			'<tr><td class="left">'.
					'<div id="zayav_spisok">'.($zayavSpisok ? $zayavSpisok : '<div class="_empty">Заявок нет</div>').'</div>'.
					'<div id="income_spisok">'.$money['spisok'].'</div>'.
					'<div class="remind_spisok">'.remind_spisok(array('client_id'=>$client_id)).'</div>'.
					'<div id="comments">'._vkComment('client', $client_id).'</div>'.
					(RULES_HISTORYSHOW ? '<div id="histories">'.history_spisok(array('client_id'=>$client_id)).'</div>' : '').
				'<td class="right">'.
					'<div id="zayav_filter">'.
						//'<div id="zayav_result">'.zayav_count($zayavData['all'], 0).'</div>'.
						//'<div class="findHead">Статус заявки</div>'.
						//_rightLink('status', _zayavStatusName()).
					'</div>'.
		'</table>'.
	'</div>';
}//client_info()



// ---===! zayav !===--- Секция заявок

function _statusColor($id) {
	$arr = array(
		'0' => 'ffffff',
		'1' => 'E8E8FF',
		'2' => 'CCFFCC',
		'3' => 'FFDDDD'
	);
	return $arr[$id];
}//_statusColor()
function _zamerDataTest($dtime, $duration, $zayav_id=0) {//Проверка, чтобы дата замера не перекрыала другие даты
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
	$ids = array(); // идешники заявок
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
			$arr[$key]['zayav_link'] = '<a'.($r['deleted'] ? ' class="deleted" title="Заявка удалена"' : '').' href="'.URL.'&p=zayav&d=info&id='.$r['id'].'">'.$head.'</a>';
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
			'name' => 'Любой статус',
			'color' => 'ffffff'
		),
		'1' => array(
			'name' => 'Ожидает выполнения',
			'color' => 'E8E8FF'
		),
		'2' => array(
			'name' => 'Выполнено',
			'color' => 'CCFFCC'
		),
		'3' => array(
			'name' => 'Отменено',
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
function _zayavCategory($z, $i='type') {// Определение категории заявки
	$dop = $z['nomer_vg'] ? ' ВГ'.$z['nomer_vg'] :
		  ($z['nomer_g'] ? ' Ж'.$z['nomer_g'] :
		  ($z['nomer_d'] ? ' Д'.$z['nomer_d'] : ' #'.$z['id']));
	if(!$z['dogovor_id'] && $z['dogovor_require'])
		$send = array(
			'type' => 'dog',
			'head' => 'Договор не заключен <span class="zayav-dog">('.($z['set_status'] ? 'установка' : ($z['zakaz_status'] ? 'заказ' : 'замер')).$dop.')</span>',
			'status_id' => $z['zamer_status'],
			'status_name' => $z['zamer_status'] ? _zamerStatus($z['zamer_status']) : ''
		);
	elseif($z['zakaz_status'])
		$send = array(
			'type' => 'zakaz',
			'head' => 'Заказ'.$dop,
			'status_id' => $z['zakaz_status'],
			'status_name' => _zakazStatus($z['zakaz_status'])
		);
	elseif($z['zamer_status'] == 1 || $z['zamer_status'] == 3)
		$send = array(
			'type' => 'zamer',
			'head' => 'Замер'.$dop,
			'status_id' => $z['zamer_status'],
			'status_name' => _zamerStatus($z['zamer_status'])
		);
	elseif($z['set_status'])
		$send = array(
			'type' => 'set',
			'head' => 'Установка'.$dop,
			'status_id' => $z['set_status'],
			'status_name' => _setStatus($z['set_status'])
		);
	if($i == 'unit') {
		$diff = $z['accrual_sum'] - $z['oplata_sum'];
		return
			'<div class="zayav_unit"'.($send['type'] != 'dog' ? ' style="background-color:#'._statusColor($send['status_id']) : '').'" val="'.$z['id'].'">'.
				($z['deleted'] ? '<div class="zdel">Заявка удалена</div>' : '').
				'<div class="dtime">'.
					'#'.(isset($z['find_id']) ? $z['find_id'] : $z['id']).'<br />'.
					FullData($z['dtime_add'], 1).
					(($send['type'] == 'zakaz' || $send['type'] == 'set') && ($z['accrual_sum'] || $z['oplata_sum']) ?
						'<div class="balans'.($z['accrual_sum'] != $z['oplata_sum'] ? ' diff' : '').'">'.
							'<span class="acc'._tooltip('Начислено', -39).$z['accrual_sum'].'</span>/'.
							'<span class="opl'._tooltip($diff ? 'Недоплата '.$diff.' руб.' : 'Оплачено', -17, 'l').$z['oplata_sum'].'</span>'.
						'</div>'
					: '').
					'</div>'.
				'<a class="name">'.$send['head'].($z['dogovor_id'] ? ' <span class="zayav-dog">(Договор '.$z['dogovor_nomer'].')</span>' : '').'</a>'.
				'<table class="ztab">'.
					(empty($z['no_client']) ?
						'<tr><td class="label">Клиент:<td>'.$z['client_link'].
						(isset($z['client_tel']) ? '<tr><td class="label">Телефон:<td>'.$z['client_tel'] : '')
					: '').
					($z['adres'] ? '<tr><td class="label top">Адрес:<td>'.$z['adres'] : '').
					'<tr><td class="label top">Изделия:<td>'.(isset($z['product']) ? zayav_product_spisok($z['product']) : '').$z['zakaz_txt'].
				'</table>'.
			'</div>';
	}
	$send['vg'] = $dop;
	return $send[$i];
}//_zayavCategory()
function _zayavBalansUpdate($zayav_id) {//Обновление начислений, суммы платежей, дохода заявки
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

function zayav_product_test($product) {// Проверка корректности данных изделий при внесении в базу
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
function zayav_product_array($arr) {//Добавление к элементам массива заявок массив product
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
			'<td>'.$r['count'].' шт.';
		$json[] = '['.$r['product_id'].','.$r['product_sub_id'].','.$r['count'].']';
		$array[] = array($r['product_id'], $r['product_sub_id'], $r['count']);
		$cash[] = _product($r['product_id']).($r['product_sub_id'] ? ' '._productSub($r['product_sub_id']) : '');
		$report[] = _product($r['product_id']).($r['product_sub_id'] ? ' '._productSub($r['product_sub_id']) : '').': '.$r['count'].' шт.';
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

function zayav_rashod_test($rashod) {// Проверка корректности данных расходов заявки при внесении в базу
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
function zayav_rashod_spisok($zayav_id, $type='html') {//Получение списка расходов заявки
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
					 '<td class="sum">'.$r['sum'].' р.';
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
		$send .= '<tr><td colspan="2" class="itog">Итог:<td class="sum"><b>'.$z['expense_sum'].'</b> р.'.
				 '<tr><td colspan="2" class="itog">Остаток:<td class="sum">'.$z['net_profit'].' р.';
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
				'<div id="buttonCreate" class="zakaz_add"><a>Новый заказ</a></div>';
			$data = zayav_spisok('zakaz');
			$status = '<div class="findHead">Статус заявки</div>'.
					  _rightLink('status', _zayavStatusName());
			break;
		case 'zamer':
			$right = '<div id="buttonCreate" class="zamer_add"><a>Новый замер</a></div>'.
					 '<a class="zamer_table">Таблица замеров</a>';
			$data = zayav_spisok('zamer');
			$st = _zayavStatusName();
			unset($st[2]);
			$status = '<div class="findHead">Статус заявки</div>'.
					  _rightLink('status', $st);

			break;
		case 'dog':
			$right = '';
			$data = zayav_spisok('dog');
			$status = '';
			break;
		case 'set':
			$right = '<div id="buttonCreate" class="set_add"><a>Новая заявка<br />на установку</a></div>';
			$data = zayav_spisok('set');
			$status = '<div class="findHead">Статус заявки</div>'.
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
			'<a class="link'.($_GET['d'] == 'zakaz' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=zakaz">Заказы'.($zakazCount ? ' ('.$zakazCount.')' : '').'</a>'.
			'<a class="link'.($_GET['d'] == 'zamer' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=zamer">Замеры'.($zamerCount ? ' ('.$zamerCount.')' : '').'</a>'.
			'<a class="link'.($_GET['d'] == 'dog' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=dog">Договора'.($dogovorCount ? ' ('.$dogovorCount.')' : '').'</a>'.
			'<a class="link'.($_GET['d'] == 'set' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=set">Установки'.($setCount ? ' ('.$setCount.')' : '').'</a>'.
		'</div>'.
		'<div class="result">'.$result.'</div>'.
		'<table class="tabLR">'.
			'<tr><td id="spisok">'.$spisok.
				'<td class="right">'.
					$right.
					'<div class="find-hide">'.
						$status.
						'<div class="findHead">Изделия</div>'.
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
		default: return 'Неизвестная категория заявок';
	}

	if($filter['client'])
		$cond .= " AND `client_id`=".$filter['client'];
	if($filter['product'])
		$cond .= " AND `id` IN (".query_ids("SELECT `zayav_id` FROM `zayav_product` WHERE `product_id`=".$filter['product']).")";

	$clear = '<a class="filter_clear">Очисить условия поиска</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'Заявок не найдено',
			'spisok' => '<div class="_empty">Заявок не найдено.</div>',
			'product_ids' => ''
		);

	$send['result'] = $clear.'Показан'._end($send['all'], 'а', 'о').' '.$send['all'].' заяв'._end($send['all'], 'ка', 'ки', 'ок');

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
				'<span>Показать ещё '.$c.' заяв'._end($c, 'ка', 'ки', 'ок').'</span>'.
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

	//Поиск заявок, если есть совпадения с фио и телефоном клиентов
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

	$clear = '<a class="filter_clear">Очисить условия поиска</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'Заявок не найдено',
			'spisok' => '<div class="_empty">Заявок не найдено.</div>'
		);

	$send['result'] = $clear.'Найден'._end($send['all'], 'а', 'о').' '.$send['all'].' заяв'._end($send['all'], 'ка', 'ки', 'ок');

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
			$r['dogovor_nomer'] = '№<em>'.$r['dogovor_n'].'</em>';
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
				'<span>Показать ещё '.$c.' заяв'._end($send['all'], 'ка', 'ки', 'ок').'</span>'.
			'</div>';
	}
	return $send;
}//zayav_findfast()

function _zakazStatus($id) {
	$arr = array(
		'0' => 'Любой статус',
		'1' => 'Заказ ожидает выполнения',
		'2' => 'Заказ выполнен',
		'3' => 'Заказ отменён'
	);
	return $arr[$id];
}//_zakazStatus()

function zamer_table($mon=false, $zayav_id=0) {
	if(!$mon)
		$mon = strftime('%Y-%m');

	//Количество дней в месяце
	$monDaysCount = date('t', strtotime($mon));

	//Длина блока в пикселях на основании длительности замера
	$zd = array(
		'30' => 18,
		'60' => 36,
		'90' => 54,
		'120' => 72,
		'150' => 90,
		'180' => 108
	);
	//Отступ для тултипа слева
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
							'<div><b>Замер №'.$r['nomer'].'</b></div>'.
							'<div><span>Дата:</span> '.$r['dtime'].'</div>'.
							'<div><span>Длительность:</span> '.$r['dur'].'</div>'.
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

	//Формирование месяцев для перелистывания
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
		'0' => 'Любой статус',
		'1' => 'Ожидает выполнения замера',
		'2' => 'Замер выполнен',
		'3' => 'Замер отменён'
	);
	return $arr[$id];
}//_zakazStatus()

function _dogNomer($arr) {//Добавление к списку данный по договору, получаемого по dogovor_id
	$ids = array(); // идешники договоров
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
			$arr[$id]['dogovor_nomer'] = '№'.$r['nomer'];
			$arr[$id]['dogovor_n'] = $r['nomer'];
			$arr[$id]['dogovor_data'] = dogovorData($r['data_create']);
			$arr[$id]['dogovor_sum'] = round($r['sum'], 2);
			$arr[$id]['dogovor_avans'] = round($r['avans'], 2);
		}
	return $arr;
}//_dogNomer()

function _setStatus($id) {
	$arr = array(
		'0' => 'Любой статус',
		'1' => 'Ожидает установку',
		'2' => 'Установка выполнена',
		'3' => 'Установка отменена'
	);
	return $arr[$id];
}//_zakazStatus()

function zayavDogovorList($zayav_id) {//Список договоров для заявки
	$sql = "SELECT * FROM `zayav_dogovor` WHERE `zayav_id`=".$zayav_id;
	$q = query($sql);
	$send = '';
	while($r = mysql_fetch_assoc($q)) {
		$d = explode('-', $r['data_create']);
		$data = $d[2].'/'.$d[1].'/'.$d[0];
		$reason = $r['reason'] ? "\n".$r['reason'] : '';
		$title = 'от '.$data.' г. на сумму '.round($r['sum'], 2).' руб.'.$reason;
		$del = $r['deleted'] ? ' d' : '';
		$send .= '<b class="dogn'.$del._tooltip($title, -7, 'l').'№'.$r['nomer'].'</b> '.
			'<a href="'.LINK_DOGOVOR.$r['link'].'.doc" class="img_word'._tooltip('Распечатать', -41).'</a>';
	}
	return $send;
}//zayavDogovorList()
function zayav_info($zayav_id) {
	$sql = "SELECT * FROM `zayav` WHERE `id`=".$zayav_id." LIMIT 1";
	if(!$z = mysql_fetch_assoc(query($sql)))
		return _noauth('Заявки не существует.');

	if(!VIEWER_ADMIN && $z['deleted'])
		return _noauth('Заявка удалёна');


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
			'<a class="link sel zinfo">Информация</a>'.
(!$z['deleted'] ?
			'<a class="link '.$type.'_edit">Редактирование</a>'.
	(ZAKAZ || SET ?
			'<a class="link acc-add">Начислить</a>'.
			'<a class="link income-add">Внести платёж</a>'.
			'<a class="delete">Удалить заявку</a>'
	: '')
: '').
			(RULES_HISTORYSHOW ? '<a class="link hist">История</a>' : '').
		'</div>'.
		($z['deleted'] ? '<div class="_info">Заявка удалёна</div>' : '').
		'<div class="content">'.
			'<TABLE class="tabmain"><TR>'.
				'<TD class="mainleft">'.
					'<div class="headName">'.
						_zayavCategory($z, 'head').
						'<div class="zid">#'.$z['id'].'</div>'.
						(ZAKAZ && !$z['deleted'] ? '<a class="zakaz-to-set">Перенести в Установки</a>' : '').
					'</div>'.
					'<table class="tabInfo">'.
						'<tr><td class="label">Клиент:<td>'._clientLink($z['client_id']).
						'<tr><td class="label top">Изделия:<td>'.zayav_product_spisok($z['id']).$z['zakaz_txt'].
			   (ZAMER ? '<tr><td class="label">Адрес замера:<td><b>'.$z['adres'].'</b>'.
						'<tr><td class="label">Дата замера:'.
							'<td><span class="zamer-dtime" title="'._zamerDuration($z['zamer_duration']).'">'.
									FullDataTime($z['zamer_dtime']).
								'</span>'.
								($z['zamer_status'] == 1 ? '<span class="zamer-left">'.remindDayLeft(1, $z['zamer_dtime']).'</span>' : '').
								'<a class="zamer_table" val="'.$z['id'].'">Таблица замеров</a>'
		       : '').

((DOG || SET) && $z['adres'] ?
						'<tr><td class="label">Адрес установки:<td><b>'.$z['adres'].'</b>'
: '').

(ZAKAZ || SET ?
						'<tr><td class="label">Договор:<td>'.$dogSpisok.
	  ($z['nomer_vg'] ? '<tr><td class="label top">Номер ВГ:<td>'._attach('vg', $z['id'], 'Прикрепить документ', $z['nomer_vg']) : '').
	   ($z['nomer_g'] ? '<tr><td class="label top">Номер Ж:<td>'._attach('g', $z['id'], 'Прикрепить документ', $z['nomer_g']) : '').
	   ($z['nomer_d'] ? '<tr><td class="label top">Номер Д:<td>'._attach('d', $z['id'], 'Прикрепить документ', $z['nomer_d']) : '').
						'<tr><td class="label top">Файлы:<td>'._attach('files', $z['id'], 'Загрузить')
: '').
					(_zayavCategory($z, 'status_name') ?
						'<tr><td class="label">Статус'.($type == 'dog' ? ' замера' : '').':'.
							'<td><div style="background-color:#'._statusColor($z[($type == 'dog' ? 'zamer' : $type).'_status']).'" class="status '.$type.'_status">'.
									_zayavCategory($z, 'status_name').
									(_zayavCategory($z, 'status_id') == 2  && !DOG ? ' '.FullData($z['status_day'], 1) : '').
								'</div>'
					: '').
					'</table>'.
	(ZAKAZ || SET ?
				'<TD class="mainright">'.
					'<div class="headBlue">Расходы по заявке<a class="add rashod-edit">изменить</a></div>'.
					'<div class="acc-sum">'.
						($accSum != 0 ? 'Общая сумма начислений: <b>'._sumSpace($accSum).'</b> руб.' : 'Начислений нет.').
					'</div>'.
					'<div class="zrashod">'.$rashod['html'].'</div>'
	: '').
			'</TABLE>'.
	(DOG ?  '<div class="vkButton dogovor_create"><button>Заключить договор</button></div>'.
				'<a class="dogovor_no_require">Договор не требуется</a>'
	: '').

			'<div class="dtime_add">Заявку вн'.(_viewer($z['viewer_id_add'], 'sex') == 1 ? 'есла' : 'ёс').' '.
				_viewer($z['viewer_id_add'], 'name').' '.
				FullDataTime($z['dtime_add']).
			'</div>'.


			'<div class="headBlue">Напоминания<a class="add remind-add">Новое напоминание</a></div>'.
			'<div class="remind_spisok">'.remind_spisok(array('zayav_id'=>$zayav_id)).'</div>'.

	(!DOG ?	'<div class="headBlue mon">Начисления и платежи'.
		(!$z['deleted'] ? '<a class="add income-add">Внести платёж</a>'.
						  '<em>::</em>'.
						  '<a class="add acc-add">Начислить</a>'
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
				'<td class="sum acc'._tooltip('Начисление', -5).'<b>'._sumSpace($r['sum']).'</b>'.
				'<td>'.$r['prim'].
				'<td class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -40).FullDataTime($r['dtime_add']).
				'<td class="ed" align="right">'.
					(!$r['dogovor_id'] ? '<div class="img_del accrual-del'._tooltip('Удалить начисление', -116, 'r').'</div>' : '');

	if(empty($money))
		return '';
	ksort($money);
	return '<table class="_spisok _money">'.implode('', $money).'</table>';
}//zayav_money()
function _attach($type, $zayav_id, $name='Обзор...', $num='') {
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
function wordSm($sm) {//Перевод сантиметров в твипсы
	$twips = round($sm * 567);
	return $twips;
}//wordSm()
*/
function dogovorData($v) {//Преобразование даты для договора
	$d = explode('-', $v);
	return $d[2].'/'.$d[1].'/'.$d[0].' г.';
}//dogovorData()
function dogovorFilter($v) {
	if(!preg_match(REGEXP_NUMERIC, $v['id']))
		return 'Ошибка: некорректный идентификатор договора.';
	if(!preg_match(REGEXP_NUMERIC, $v['zayav_id']) && !$v['zayav_id'])
		return 'Ошибка: неверный номер заявки.';
	if(!preg_match(REGEXP_NUMERIC, $v['nomer']) && !$v['nomer'])
		return 'Ошибка: некорректно указан номер договора.';
	if(!preg_match(REGEXP_DATE, $v['data_create']))
		return 'Ошибка: некорректно указана дата заключения договора.';
	if(!preg_match(REGEXP_CENA, $v['sum']) || $v['sum'] == 0)
		return 'Ошибка: некорректно указана сумма по договору.';
	if(!empty($v['avans']) && !preg_match(REGEXP_CENA, $v['avans']))
		return 'Ошибка: некорректно указан авансовый платёж.';
	if(!empty($v['cut']))
		foreach(explode(',', $v['cut']) as $r) {
			$ex = explode(':', $r);
			if(!preg_match(REGEXP_CENA, $v['sum']) || $ex[0] == 0 || !preg_match(REGEXP_DATE, $ex[1]))
				return 'Ошибка: некорректные данные при разбивке платежа.';
			if(strtotime($ex[1]) < TODAY_UNIXTIME)
				return 'Ошибка: в разбивке платежа указан устаревший день.';
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
		return 'Ошибка: договор с номером <b>'.$send['nomer'].'</b> уже был заключен.';

	if(empty($send['fio']))
		return 'Ошибка: не указаны ФИО клиента.';

	if($send['sum'] < $send['avans'])
		return 'Ошибка: авансовый платёж не может быть больше суммы договора.';

	if(!$send['client_id'] = query_value("SELECT `client_id` FROM `zayav` WHERE `deleted`=0 AND `id`=".$send['zayav_id']))
		return 'Ошибка: заявки id = '.$send['zayav_id'].' не существует, либо она была удалена.';

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
	$section->addText(utf8('ДОГОВОР №').$v['nomer'], $headNameStyle, $headNamePar);

	$table = $section->addTable();
	$table->addRow();
	$table->addCell(wordSm(10))->addText(utf8('Город Няндома'), $b);
	$table->addCell(wordSm(8))->addText(utf8(dogovorData($v['data_create'])), $b, $r);

	$section->addText(utf8(
		'Общество с ограниченной ответственностью «Территория Комфорта», '.
		'в лице менеджера по продажам, Билоченко Юлия Александровна, действующей на основании доверенности, '.
		'с одной стороны, и '.$v['fio'].($adres ? ', '.$adres : '').', именуемый в дальнейшем «Заказчик», с другой стороны, '.
		'заключили настоящий договор, далее «Договор», о нижеследующем:'
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
	'<div class="head-name">ДОГОВОР №'.$v['nomer'].'</div>'.
	'<table class="city_data"><tr><td>Город Няндома<th>'.dogovorData($v['data_create']).'</table>'.
	'<div class="paragraph">'.
		'<p>Общество с ограниченной ответственностью «Территория Комфорта», '.
		'в лице менеджера по продажам, Билоченко Юлия Александровна, действующей на основании доверенности, '.
		'с одной стороны, и '.$v['fio'].($adres ? ', '.$adres : '').', именуемый в дальнейшем «Заказчик», с другой стороны, '.
		'заключили настоящий договор, далее «Договор», о нижеследующем:'.
	'</div>'.
	'<div class="p-head">1. Предмет договора</div>'.
	'<div class="paragraph">'.
		'<p>1.1. Поставщик принимает на себя обязательство по исполнению ЗАКАЗА на изготовление и доставку изделий (оконных блоков, дверных блоков, защитных роллет, гаражных и промышленных ворот) в соответствии с индивидуальными характеристиками объекта и требованиями Заказчика (далее «Товар»). Работы по установке изделий и конструкций из них по адресу заказчика.'.
		'<p>1.2. Полная характеристика Заказа содержится в Спецификации, являющейся неотъемлемой частью настоящего договора.'.
	'</div>'.
	'<div class="p-head">2. Обязанности сторон</div>'.
	'<div class="paragraph">'.
		'<p>2.1. Поставщик обязуется исполнить заказ с соблюдением условий настоящего договора и требований, предъявляемых к продукции данного типа и указанных в ГОСТах №23166-99 «Блоки оконные ТУ», №30970-2002 «Блоки оконные из ПВХ» для оконных блоков, в рабочей документации разработчиков систем профилей для дверных блоков, в «Инструкции по изготовлению роллет», «Инструкции по изготовлению ворот», в ГОСТах №111-2001 «Стекло листовое», №24866-99 «Стеклопакеты клееные строительного назначения ».'.
		'<p>2.2. Предварительный согласованный срок поставки товара и выполнения предусмотренных работ составляет 20 рабочих дней. Окончательный срок выполнения договора не более тридцати рабочих дней с момента поступления от Заказчика полной оплаты по договору и обеспечения Заказчиком условий пунктов 2.3. и 2.4. Данные сроки предусмотрены по стандартным изделиям. В случае заказа сложных и цветных изделий, срок договора увеличивается на количество дополнительных дней на изготовление сложной конструкции, указанное в Спецификации.'.
		'<p>2.3. Заказчик обязуется обеспечить доступ монтажников и подвод электропитания к оконным проемам, защиту личного имущества, напольных покрытий от пыли и повреждений, если договор предполагает выполнение работ по установке продукции по адресу заказчика. '.
		'Поставщик не отвечает за сохранность стеновых покрытий в зоне работ. Поставщик не несёт ответственности за нарушение элементов конструкций фасадов зданий при выполнении монтажных и отделочных работ, возникших в следствии ветхости строений и наличия скрытых строительных дефектов. Восстановительные работы проводятся по желанию и за счёт заказчика. В стоимость отделки откосов не входит герметизация наружного шва до 4 см шириной.'.
		'<p>2.4. Заказчик обязуется принять меры по обеспечению отсутствия автотранспорта на тротуаре под оконными проемами. В случае, если Заказчик не принял данные меры и монтаж осуществить невозможно, Заказчик оплачивает дополнительный выезд монтажной бригады из расчета 1000 руб./выезд, при этом окончательный срок выполнения договора составит 10 рабочих дней с момента обеспечения надлежащего доступа к месту установки.'.
		'<p>2.5. Все необходимые материалы доставляются Поставщиком на адрес Заказчика к моменту установки. В случае, отсутствия заказчика или его представителя на объекте в согласованный день поставки, повторная доставка оплачивается из расчёта 1000 руб./заказ, при этом окончательный срок выполнения договора составит 10 рабочих дней с момента обеспечения надлежащего доступа к месту установки.'.
		'<p>2.6. Поставщик обязуется собрать строительный мусор в мешки, если они присутствуют на объекте. Поставщик не несет ответственности за вывоз строительного мусора, образованного после выполнения работ по установке продукции. Заказчик обязуется осуществить вывоз и размещение мусора согласно действующим нормам. Поставщик обязуется вывезти строительный мусор на специализированную площадку (в соответствии с законом №239-29 от 29.05.2003), только в случае, если данная услуга была заказана Заказчиком и указана в Спецификации.'.
		'<p>2.7. Заказчик обязуется оплатить полную стоимость Заказа до начала установки изделий и конструкций из них в соответствии со Спецификацией.'.
		'<p>2.8. Право собственности на товар переходит к Заказчику в момент подписания им товаросопроводительных документов. В случае разногласия по количеству, комплектности и внешнему виду, суть разногласий отмечается в товаросопроводительных документах.'.
		'<p>2.9. Заказчик обязуется осуществить приёмку выполненных работ по монтажу изделий, по количеству, качеству, комплектности, внешнему виду и качеству отделки и заполнить две идентичные части «Приложения» к Акту сдачи – приёмки заказа и две идентичные части Акта сдачи - приёмки заказа. Отрывная часть №1 «Приложения» остаётся у заказчика, а часть №2 данного «Приложения» передается бригадиру установщиков. Акт приёма-передачи передаётся бригадиру мастеров по восстановлению откосов или бригадиру установщиков в случае, если отделка производится унифицированной бригадой. «Приложение» необходимо для оперативного обоснования претензии Заказчиком по качеству выполнения Поставщиком условий договора. В случае, если Заказчик отказывается подписывать «Акт сдачи-приемки заказа» и/или «Приложение», заказ считается автоматически выполненным.'.
		'<p>2.10. В случае подписания заказчиком товаросопроводительных документов или Акта выполненных работ с разногласиями, Поставщик обязуется рассмотреть данные разногласия в течение 5 дней, при этом срок выполнения договора автоматически продлевается на указанный срок.'.
		'<p>2.11. В случае согласия с претензией Заказчика Поставщик обязан заменить соответствующую часть товара или выполнить иные действия, предусмотренные настоящим договором для таких случаев в течение 14 рабочих дней, следующих за днем получения претензии Заказчика.'.
	'</div>'.
	'<div class="p-head">3. Цена товара и порядок расчетов</div>'.
	'<div class="paragraph">'.
		'<p>3.1. Полная стоимость заказа составляет: '.$v['sum'].' ('.numberToWord($v['sum']).' рубл'._end($v['sum'], 'ь', 'я', 'ей').') указанные в спецификации, являются твердыми, и изменению без обоюдного согласия сторон не подлежат.'.
		($v['avans'] ?
		'<p>3.2. Оплата по настоящему договору осуществляется в следующем порядке:'.
		'<p>3.2.1. Авансовый платёж в размере '.$v['avans'].' ('.numberToWord($v['avans']).' рубл'._end($v['avans'], 'ь', 'я', 'ей').') вносится Заказчиком в день заключения настоящего договора. В случае отсутствия работ по договору, авансовый платёж составляет 100% суммы договора.'.
		($dopl ? '<p>3.2.2. Доплата по договору, в сумме '.$dopl.' ('.numberToWord($dopl).' рубл'._end($dopl, 'ь', 'я', 'ей').'), оплачивается в кассу до установки изделий: ______________________________________.' : '')
		: '').
	'</div>'.
	'<div class="p-head">4. Качество и гарантийные обязательства</div>'.
	'<div class="paragraph">'.
		'<p>4.1. Гарантийный срок на оконные блоки – три года, на монтажные и отделочные работы по оконным блокам – один год. Гарантийный срок на дверные блоки, роллетные системы и ворота - один год. На монтажные и отделочные работы по установке дверных блоков, роллетных систем и ворот – один год. Гарантийный срок действует с момента подписания сторонами отгрузочных документов (Акт сдачи – приемки заказа). Для климатических условий Северо-западного и Центрального региона России рекомендовано использовать двухкамерные стеклопакеты. Заказчик предупреждается, что при установке однокамерного стеклопакета, возможно образование конденсата и промерзание стеклопакета в зимний период при более высокой температуре окружающей среды, чем при установке двухкамерного стеклопакета. Заказчик предупреждён, что для исключения возможности выпадения конденсата и образования наледи на стеклопакетах, необходимо поддержание уровня температуры и влажности рекомендованного для жилого помещения.'.
		'<p>4.2. Поставщик обязуется заменить входящие в состав товара комплектующие за свой счёт, в случае выхода их из строя в течение Гарантийного срока. Срок выполнения гарантийных работ составляет не более 20 рабочих дней с момента поступления письменной претензии. Письменная претензия принимается в центральном офисе компании либо по почте.'.
		'<p>4.3. Гарантия не распространяется на случаи, когда товар (или его комплектующие) утратили свои качественные характеристики вследствие неправильной эксплуатации Товара, действий третьих лиц или в случае возникновения обстоятельств непреодолимой силы.'.
	'</div>'.
	'<div class="p-head">5. Ответственность сторон, форс-мажорные обстоятельства и ответственность сторон</div>'.
	'<div class="paragraph">'.
		'<p>5.1. Стороны освобождаются от ответственности за частичное или полное неисполнение обязательств по настоящему Договору, если это явилось следствием обстоятельств непреодолимой силы (форс-мажор), т.е. пожара, стихийных бедствий, войны, блокад, введение правительственных ограничений постфактум, объявления карантина и эпидемий. При этом срок исполнения обязательств по Договору продлевается на период действия указанных обстоятельств.'.
		'<p>5.2. За неисполнение или ненадлежащее исполнение обязательств стороны несут ответственность в соответствии с действующим законодательством Российской Федерации. В случае нарушения сроков выполнения договора поставщик выплачивает Заказчику неустойку в соответствии с Законом РФ "О защите прав потребителей" размере 3% в день от суммы недопоставленных комплектующих Заказа указанных в Спецификации и от суммы не оказанных услуг и работ, указанных в Спецификации.'.
	'</div>'.
	'<div class="p-head">6. Изменение условий договора и порядок разрешения споров</div>'.
	'<div class="paragraph">'.
		'<p>6.1. Все изменения и дополнения к настоящему договору действительны лишь в том случае, если они оформлены в письменном виде и подписаны обеими сторонами.'.
		'<p>6.2. Все споры и разногласия, которые могут возникнуть из настоящего договора будут по возможности разрешаться путём двусторонних переговоров.'.
		'<p>6.3. Споры, не получившие разрешения в результате переговоров, подлежат разрешению в соответствии с действующим законодательством РФ.'.
	'</div>'.
	'<div class="p-head">7. Срок действия договора</div>'.
	'<div class="paragraph">'.
		'<p>7.1. Настоящий договор вступает в силу с момента его подписания и действует до полного выполнения обязательств обеими сторонами.'.
	'</div>'.
	'<div class="p-head">8. Заключительные положения</div>'.
	'<div class="paragraph">'.
		'<p>8.1. Настоящий договор составлен в двух экземплярах по одному для каждой из сторон, имеющих равную юридическую силу.'.
	'</div>'.
	'<div class="p-head">9. Юридические адреса и банковские реквизиты сторон</div>'.
	'<table class="rekvisit">'.
		'<tr><td><b>Поставщик:</b><br />'.
				'ООО «'.$g['org_name'].'»<br />'.
				'ОГРН '.$g['ogrn'].'<br />'.
				'ИНН '.$g['inn'].'<br />'.
				'КПП '.$g['kpp'].'<br />'.
				str_replace("\n", '<br />', $g['yur_adres']).'<br />'.
				'Тел. '.$g['telefon'].'<br /><br />'.
				'Адрес офиса: '.$g['ofice_adres'].
			'<td><b>Заказчик:</b><br />'.
				$v['fio'].'<br />'.
				'Паспорт серии '.$v['pasp_seria'].' '.$v['pasp_nomer'].'<br />'.
				'выдан '.$v['pasp_ovd'].' '.$v['pasp_data'].'<br /><br />'.
				$adres.
	'</table>'.
	'<div class="podpis-head">Подписи сторон:</div>'.
	'<table class="podpis">'.
		'<tr><td>Поставщик ________________ Билоченко Ю.А.'.
			'<td>Заказчик ________________ '.$fioPodpis.
	'</table>'.
	'<div class="mp">М.П.</div>');

	$doc->newPage();

	$doc->addParagraph(
	'<div class="ekz">Экземпляр заказчика</div>'.
	'<div class="act-head">АКТ сдачи-приёмки заказа</div>'.
	'<table class="act-tab">'.
		'<tr><td class="label">По адресу:<td class="title">'.$v['adres'].'<td><td>'.
		'<tr><td class="label">Заказ:<td class="title">'.$v['nomer'].'<td class="label">Заказчик:<td>'.$fioPodpis.
	'</table>'.
	'<div class="act-inf">Экземпляр Заказчика является основанием для направления претензии.</div>'.
	'<div class="act-p">'.
		'<p>1. Оконные блоки принял без замечаний, со следующими замечаниями (ненужное зачеркнуть) по количеству, качеству, комплектности и внешнему виду:'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
	'</div>'.
	'<div class="act-p">'.
		'<p>2. Выполненные работы принял без замечаний, со следующими замечаниями (ненужное зачеркнуть):'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
	'</div>'.
	'<div class="act-p">От заказчика ___________________________________</div>'.
	'<div class="act-p">От поставщика /Бригадир монтажников/ ____________________________________</div>'.
	'<div class="act-p">Дата _______________</div>'.
	'<div class="cut-line">отрезать</div>'.
	'<div class="ekz">Экземпляр бригадира монтажников</div>'.
	'<div class="act-head">АКТ сдачи-приёмки заказа</div>'.
	'<table class="act-tab">'.
		'<tr><td class="label">По адресу:<td class="title">'.$v['adres'].'<td><td>'.
		'<tr><td class="label">Заказ:<td class="title">'.$v['nomer'].'<td class="label">Заказчик:<td>'.$fioPodpis.
	'</table>'.
	'<div class="time-dost">Время доставки _____________________</div>'.
	'<div class="act-p">'.
		'<p>1. Оконные блоки принял без замечаний, со следующими замечаниями (ненужное зачеркнуть) по количеству, качеству, комплектности и внешнему виду:'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
	'</div>'.
	'<div class="act-p">'.
		'<p>2. Выполненные работы принял без замечаний, со следующими замечаниями (ненужное зачеркнуть):'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
		'<p>__________________________________________________________________________'.
	'</div>'.
	'<div class="act-p">От заказчика ___________________________________</div>'.
	'<div class="act-p">От поставщика /Бригадир монтажников/ ____________________________________</div>'.
	'<div class="act-p">Дата _______________</div>'
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
	'<div class="org-name">Общество с ограниченной ответственностью <b>«'.$g['org_name'].'»</b></div>'.
	'<div class="cash-rekvisit">'.
		'ИНН '.$g['inn'].'<br />'.
		'ОГРН '.$g['ogrn'].'<br />'.
		'КПП '.$g['kpp'].'<br />'.
		str_replace("\n", '<br />', $g['yur_adres']).'<br />'.
		'Тел.: '.$g['telefon'].
	'</div>'.
	'<div class="head">Товарный чек №'.$money['id'].'</div>'.
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
				($money['zayav_id'] ? zayav_product_spisok($money['zayav_id'], 'cash') : '"'.$money['prim'].'"').
			'<td class="count">1.00'.
			'<td class="sum">'.$money['sum'].
			'<td class="summa">'.$money['sum'].
		'</table>'.
	'<div class="summa-propis">'.numberToWord($money['sum'], 1).' рубл'._end($money['sum'], 'ь', 'я', 'ей').'</div>'.
	'<div class="shop-about">(сумма прописью)</div>'.
	'<table class="cash-podpis">'.
		'<tr><td>Продавец ______________________<div class="prod-bot">(подпись)</div>'.
			'<td><u>/Билоченко Ю.А./</u><div class="r-bot">(расшифровка подписи)</div>'.
	'</table>';
}//cashmemoParagraph()




// ---===! remind !===--- Секция напоминаний

function remindDayLeft($status, $d) {
	if($status == 2)
		return 'Выполнено';
	if($status == 0)
		return 'Отменено';
	$dayLeft = floor((strtotime($d) - TODAY_UNIXTIME) / 3600 / 24);
	if($dayLeft < 0)
		return 'Просрочен'._end($dayLeft * -1, ' ', 'о ').($dayLeft * -1)._end($dayLeft * -1, ' день', ' дня', ' дней');
	if($dayLeft > 2)
		return 'Остал'._end($dayLeft, 'ся ', 'ось ').$dayLeft._end($dayLeft, ' день', ' дня', ' дней').
			   '<span class="oday">('.FullData($d, 1).')</span>';
	switch($dayLeft) {
		default:
		case 0: return 'Выполнить сегодня';
		case 1: return 'Выполнить завтра';
		case 2: return 'Выполнить послезавтра';
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
		1 => 'Активные',
		2 => 'Выполнены',
		0 => 'Отменены'
	);

	return
	'<div id="remind">'.
		'<table class="tabLR">'.
			'<tr><td class="left remind_spisok">'.remind_spisok().
				'<td class="right">'.
					'<div id="buttonCreate" class="remind-add"><a>Новое напоминание</a></div>'.
					_calendarFilter($data).
					'<a class="goyear">Календарь на год</a>'.
					'<div class="findHead">Статус</div>'.
					_radio('status', $status, 1, 1).
		'</table>'.
		'<div class="full"><div class="fhead">Календарь напоминаний: 2014 </div>'.$fullCalendar.'</div>'.
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
				CONCAT('Заявка на замер №',`id`) AS `txt`,
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
		return 'Напоминаний нет.';
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
				($r['private'] ? '<span class="private">Личное:</span> ' : '').
				($r['zayav_id'] && !$filter['zayav_id'] ? '<a href="'.URL.'&p=zayav&d=info&id='.$r['zayav_id'].'">'._zayavCategory($z, 'head').'</a>: ' : '').
				($r['cut'] ? 'Платёж <b>'.$r['txt'].'</b> руб. Дог.'.$z['dogovor_n'].'. ' : '').
				(!$r['cut'] ? '<b>'.$r['txt'].'</b>' : '').
			'</div>'.
			'<table class="to">'.
		($r['action'] == 'zamer_status' ?
				'<tr><td class="label">Дата:'.
					'<td>'.FullDataTime($r['zamer_dtime']).
						'<span class="dur">'._zamerDuration($r['zamer_duration']).'</span>'
		: '').
				($z['client_id'] ? '<tr><td class="label">Клиент:<td>'.$z['client_link'].($z['client_tel'] ? ', '.$z['client_tel'] : '') : '').
			'</table>'.
			'<div class="day_left">'.
				remindDayLeft($r['status'], $r['day']).
				'<a class="remind_history" val="'.$r['id'].'">История</a>'.
				($filter['status'] == 1 ? '<tt> :: </tt><a class="action '.$r['action'].'" val="'.$r['id'].'">Действие</a>' : '').
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
		return 'Истории нет.';
	$send = '<table>';
	while($r = mysql_fetch_assoc($q)) {
		$about = '';
		$count--;
		if($r['status'] == 1 && !$count)
			$about = 'Создание напоминания. День: '.FullData($r['day']).'.';
		else
			switch($r['status']) {
				case 1:
					$about = 'Указан новый день: '.FullData($r['day']).'.'.
						($r['txt'] ? '<br />Причина: '.$r['txt'].'.' : '');
					break;
				case 2: $about = 'Напоминание выполнено.'; break;
				case 0: $about = 'Напоминание отменено.'; break;
			}
		$send .=
			'<tr><td>'.FullDataTime($r['dtime_add'], 1).
				'<td>'._viewer($r['viewer_id_add'], 'name').
				'<td>'.$about;
	}
	$send .= '</table>';
	return $send;
}//remind_history()


// ---===! report !===--- Секция отчётов

function report() {
	$def = 'history';
	$pages = array(
		'history' => 'История действий',
		'money' => 'Деньги'.(TRANSFER_CONFIRM ? ' (<b>'.TRANSFER_CONFIRM.'</b>)' : ''),
		'month' => 'Полный отчёт по месяцам',
		'salary' => 'Зарплата сотрудников'
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
								$left = 'Указан некорректный год.';
								break;
							}
							$left = income_year(intval($_GET['year']));
							break;
						case 'month':
							if(empty($_GET['mon']) || !preg_match(REGEXP_YEARMONTH, $_GET['mon'])) {
								$left = 'Указан некорректный месяц.';
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
					'<a class="link'.($d1 == 'income' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=income">Платежи</a>'.
					'<a class="link'.($d1 == 'expense' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=expense">Расходы</a>'.
					'<a class="link'.($d1 == 'invoice' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=invoice">Счета'.(TRANSFER_CONFIRM ? ' (<b>'.TRANSFER_CONFIRM.'</b>)' : '').'</a>'.
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
		1 => 'Клиенты',
		2 => 'Заявки',
		3 => 'Договора',
		4 => 'Файлы',
		5 => 'Деньги',
		6 => 'Расходы организации',
		7 => 'Настройки'
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
		case 1: return 'Внесение нового клиента '.$v['client_link'].'.';
		case 2: return 'Изменение данных клиента '.$v['client_link'].':<div class="changes">'.$v['value'].'</div>';
		case 3: return 'Удаление клиента '.$v['client_link'].'.';

		case 4: return 'Внесение новой заявки  <em>(замер)</em> '.$v['zayav_link'].' для клиента '.$v['client_link'].'.';
		case 5: return 'Изменение данных заявки <em>(замер)</em> '.$v['zayav_link'].':<div class="changes">'.$v['value1'].'</div>';
		case 6: return 'Удаление заявки '.$v['zayav_link'].' у клиента '.$v['client_link'].'.';

		case 7: return 'Начисление на сумму <b>'.$v['value'].'</b> руб.'.
						($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
						' по заявке '.$v['zayav_link'].'.';
		case 8: return 'Удаление начисления на сумму <b>'.round($v['value'], 2).'</b> руб.'.
						($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
						' у заявки '.$v['zayav_link'].'.';
		case 9: return 'Восстановление начисления на сумму <b>'.round($v['value'], 2).'</b> руб.'.
						($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
						' у заявки '.$v['zayav_link'].'.';

		case 10: return
			'Платёж <span class="oplata">'._income($v['value2']).'</span> '.
			'на сумму <b>'.$v['value'].'</b> руб.'.
			($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' по заявке '.$v['zayav_link'] : '').
			($v['dogovor_id'] ? '. Авансовый платёж по договору '.$v['dogovor_nomer'] : '').
			'.';
		case 11: return
			'Удаление платежа <span class="oplata">'._income($v['value2']).'</span> '.
			'на сумму <b>'.round($v['value'], 2).'</b> руб.'.
			($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' у заявки '.$v['zayav_link'] : '').
			'.';
		case 12: return
			'Восстановление платежа <span class="oplata">'._income($v['value2']).'</span> '.
			'на сумму <b>'.round($v['value'], 2).'</b> руб.'.
			($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' у заявки '.$v['zayav_link'] : '').
			'.';

		case 13: return 'В настройках: добавление нового сотрудника <u>'._viewer($v['value'], 'name').'</u>.';
		case 14: return 'В настройках: удаление сотрудника <u>'._viewer($v['value'], 'name').'</u>.';

		case 15: return 'Изменение информации о дате или продолжительности замера '.$v['zayav_link'].':<div class="changes">'.$v['value1'].'</div>';
		case 16: return 'Замер '.$v['zayav_link'].' выполнен и отправлен на заключение договора.';
		case 17: return 'Замер '.$v['zayav_link'].' отменён.';
		case 18: return 'Замер '.$v['zayav_link'].' восстановлен.';
		case 19: return
			($v['value'] ? 'Пере' : 'З').'аключение договора '.$v['dogovor_nomer'].
			' от '.$v['dogovor_data'].
			' на сумму <b>'.$v['dogovor_sum'].'</b> руб.'.
			' для заявки '.$v['zayav_link'].'.'.
			($v['value'] ? ' <em>(Причина: '.$v['value'].'.)</em>' : '');
		case 20: return
			'Внесение авансового платежа на  на сумму <b>'.$v['dogovor_avans'].'</b> руб.'.
			' для заявки '.$v['zayav_link'].
			' при заключении договора '.$v['dogovor_nomer'].'.';

		case 21: return 'Внесение новой заявки '.$v['zayav_link'].' <em>(установка)</em> для клиента '.$v['client_link'].'.';
		case 22: return 'Изменение данных заявки '.$v['zayav_link'].' <em>(установка)</em>:<div class="changes">'.$v['value'].'</div>';

		case 23: return 'Внесение новой заявки '.$v['zayav_link'].' <em>(заказ)</em> для клиента '.$v['client_link'].'.';
		case 24: return 'Изменение данных заявки '.$v['zayav_link'].' <em>(заказ)</em>:<div class="changes">'.$v['value1'].'</div>';
		case 25: return 'Изменение статуса заявки '.$v['zayav_link'].' <em>(заказ)</em>:<br />'.
						'<span style="background-color:#'._statusColor($v['value']).'" class="zstatus">'._zakazStatus($v['value']).'</span>'.
						' » '.
						'<span style="background-color:#'._statusColor($v['value1']).'" class="zstatus">'._zakazStatus($v['value1']).'</span>.'.
						($v['value2'] ? ' Дата выполнения: <u>'.FullData($v['value2']).'</u>.' : '');
		case 26: return 'Изменение статуса заявки '.$v['zayav_link'].' <em>(установка)</em>:<br />'.
						'<span style="background-color:#'._statusColor($v['value']).'" class="zstatus">'._setStatus($v['value']).'</span>'.
						' » '.
						'<span style="background-color:#'._statusColor($v['value1']).'" class="zstatus">'._setStatus($v['value1']).'</span>'.
						($v['value2'] ? ' Дата выполнения: <u>'.FullData($v['value2']).'</u>.' : '');

		case 27: return 'Загрузка файла '.$v['value'].' для заявки '.$v['zayav_link'].'.';
		case 28: return 'Удаление файла '.$v['value'].' у заявки '.$v['zayav_link'].'.';

		case 29: return 'Изменение расходов по заявке '.$v['zayav_link'].':<div class="changes">'.$v['value'].'</div>';
		case 30: return 'Заявка '.$v['zayav_link'].' перенесена из <u>Заказов</u> в <u>Установки</u>. Указан адрес "'.$v['value'].'"';

		case 31: return 'Указана новая дата выполнения заявки '.$v['zayav_link'].': <u>'.FullData($v['value']).'</u>.';

		case 32: return 'Внесение расхода организации: '.
			($v['value1'] ? '<span class="oplata">'._expense($v['value1']).'</span> ' : '').
			($v['value2'] ? '<em>('.$v['value2'].')</em> ' : '').
			($v['value3'] ? '<u>'._viewer($v['value3'], 'name').'</u> ' : '').
			'на сумму <b>'.$v['value'].'</b> руб.';
		case 33: return 'Удаление расхода организации: '.
			($v['value1'] ? '<span class="oplata">'._expense($v['value1']).'</span> ' : '').
			($v['value2'] ? '<em>('.$v['value2'].')</em> ' : '').
			($v['value3'] ? 'для сотрудника <u>'._viewer($v['value3'], 'name').'</u> ' : '').
			'на сумму <b>'.$v['value'].'</b> руб.';
		case 34: return 'Восстановление расхода организации: '.
			($v['value1'] ? '<span class="oplata">'._expense($v['value1']).'</span> ' : '').
			($v['value2'] ? '<em>('.$v['value2'].')</em> ' : '').
			($v['value3'] ? 'для сотрудника <u>'._viewer($v['value3'], 'name').'</u> ' : '').
			'на сумму <b>'.$v['value'].'</b> руб.';
		case 35: return 'Изменение данных расхода от '.FullDataTime($v['value2']).':<div class="changes">'.$v['value'].'</div>';

		case 36: return
			'Внесение начисления з/п на сумму <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'для сотрудника <u>'._viewer($v['value2'], 'name').'</u>.';
		case 37: return
			'Выдача з/п на сумму <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'для сотрудника <u>'._viewer($v['value2'], 'name').'</u>.';
		case 38: return 'Установка текущей суммы для счёта <span class="oplata">'._invoice($v['value1']).'</span>: <b>'.$v['value'].'</b> руб.'.
						($v['value2'] ? '<br /><div class="changes">'.$v['value2'].'</div>' : '');
		case 39:
			return 'Перевод со счёта <span class="oplata">'._invoice($v['value1'] > 100 ? 1 : $v['value1']).'</span> '.
					($v['value1'] > 100 ? '<u>'._viewer($v['value1'], 'name').'</u> ' : '').
				   'на счёт <span class="oplata">'._invoice($v['value2'] > 100 ? 1 : $v['value2']).'</span> '.
					($v['value2'] > 100 ? '<u>'._viewer($v['value2'], 'name').'</u> ' : '').
				   'в сумме <b>'.$v['value'].'</b> руб.'.
				   ($v['value3'] ? ' <em>('.$v['value3'].')</em> ' : '');
		case 40:
			return 'Установка ставки з/п в сумме <b>'.$v['value1'].'</b> руб. '.
				   'для сотрудника <u>'._viewer($v['value'], 'name').'</u>. '.
				   'Начисление '.$v['value2'].'-го числа каждого месяца.';
		case 41: return 'Удаление ставки з/п у сотрудника <u>'._viewer($v['value'], 'name').'</u>.';

		case 42: return 'Изменение данных договора '.$v['dogovor_nomer'].' '.
						'у заявки '.$v['zayav_link'].':'.
						'<div class="changes">'.$v['value'].'</div>';

		case 43: return 'Подтверждение поступления на счёт: <a class="income-show" val="'.$v['value1'].'">'.$v['value'].' платеж'._end($v['value'], '', 'а', 'ей').'</a>.';

		case 44: return
			'Внесение вычета из з/п на сумму <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'у сотрудника <u>'._viewer($v['value2'], 'name').'</u>.';
		case 45: return 'Установка баланса з/п в сумме <b>'.$v['value1'].'</b> руб. '.
				        'для сотрудника <u>'._viewer($v['value'], 'name').'</u>. ';

		case 46: return 'Автоматическое начисление з/п сотруднику <u>'._viewer($v['value1'], 'name').'</u> '.
						'в размере <b>'.$v['value'].'</b> руб. <em>('.$v['value2'].')</em>.';
		case 47: return 'Зафиксирован отчёт за <a href="'.$v['value1'].'">'.$v['value'].'</a>.';

		case 50: return 'Удаление начисления з/п в сумме <b>'.$v['value'].'</b> руб. у сотрудника <u>'._viewer($v['value1'], 'name').'</u>.';
		case 51: return 'Удаление вычета з/п в сумме <b>'.$v['value'].'</b> руб. у сотрудника <u>'._viewer($v['value1'], 'name').'</u>.';

		case 52: return 'Подтвержден'._end($v['value'], '', 'ы').' '.
						'<a class="transfer-show" val="'.$v['value1'].'">'.$v['value'].' перевод'._end($v['value'], '', 'а', 'ов').'</a>'.
						($v['value2'] ? ' <em>('.$v['value2'].')</em>' : '').
						'.';

		case 501: return 'В настройках: внесение нового наименования изделия "'.$v['value'].'".';
		case 502: return 'В настройках: изменение данных изделия "'.$v['value1'].'":<div class="changes">'.$v['value'].'</div>';
		case 503: return 'В настройках: удаление наименования изделия "'.$v['value'].'".';

		case 510: return 'В настройках: изменение реквизитов организации:<div class="changes">'.$v['value'].'</div>';

		case 504: return 'В настройках: внесение нового подвида для изделия "'.$v['value'].'": '.$v['value1'].'.';
		case 505: return 'В настройках: изменение подвида у изделия "'.$v['value'].'":<div class="changes">'.$v['value1'].'</div>';
		case 506: return 'В настройках: удаление подвида у изделия "'.$v['value'].'": '.$v['value1'].'.';

		case 507: return 'В настройках: внесение нового вида платежа "'.$v['value'].'".';
		case 508: return 'В настройках: изменение вида платежа "'.$v['value'].'":<div class="changes">'.$v['value1'].'</div>';
		case 509: return 'В настройках: удаление вида платежа "'.$v['value'].'".';

		case 511: return 'В настройках: внесение новой категории расходов заявки <u>'.$v['value'].'</u>.';
		case 512: return 'В настройках: изменение данных категории расходов заявки <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 513: return 'В настройках: удаление данных категории расходов заявки <u>'.$v['value'].'</u>.';

		case 514: return 'В настройках: изменение данных сотрудника <u>'._viewer($v['value'], 'name').'</u>:<div class="changes">'.$v['value1'].'</div>';

		case 515: return 'В настройках: внесение нового счёта <u>'.$v['value'].'</u>.';
		case 516: return 'В настройках: изменение данных счёта <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 517: return 'В настройках: удаление счёта <u>'.$v['value'].'</u>.';

		case 518: return 'В настройках: внесение новой категории расходов организации <u>'.$v['value'].'</u>.';
		case 519: return 'В настройках: изменение данных категории расходов организации <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 520: return 'В настройках: удаление данных категории расходов организации <u>'.$v['value'].'</u>.';

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
		return $send.'Истории по указанным условиям нет.';

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
		$send .= '<div class="_next" id="history_next" val="'.($page + 1).'"><span>Показать более ранние записи...</span></div>';
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
		'<div class="findHead">Сотрудник</div>'.
		'<input type="hidden" id="worker_id">'.
		'<div class="findHead">Категория</div>'.
		'<input type="hidden" id="cat_id">';
}//history_right()

function _invoiceBalans($invoice_id, $start=false) {// Получение текущего баланса счёта
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
			'Счета'.
			'<a class="add transfer">Перевод между счетами</a>'.
			'<span>::</span>'.
			'<a href="'.URL.'&p=setup&d=invoice" class="add">Управление счетами</a>'.
		'</div>'.
		'<div id="confirm-info">'.income_confirm_info().'</div>'.
	(TRANSFER_CONFIRM ? //Подтверждение переводов руководителю
		'<div class="_info">'.
			'Есть переводы, требующие подтверждения: <b>'.TRANSFER_CONFIRM.'</b>. '.
			'<a class="transfer-confirm">Подтвердить</a>'.
		'</div>'
	: '').
		'<div id="cash-spisok">'.$data['spisok'].'</div>'.
		'<div id="invoice-spisok">'.invoice_spisok().'</div>'.
		'<div class="headName">История переводов</div>'.
		'<div class="transfer-spisok">'.transfer_spisok().'</div>';
}//invoice()
function income_confirm_info() {
	if(!$confirm = query_value("SELECT COUNT(`id`) FROM `money` WHERE !`deleted` AND `confirm`"))
		return '';
	return
	'<div class="_info">'.
		'<b>'.$confirm.' платеж'._end($confirm, '', 'а', 'ей').'</b> ожида'._end($confirm, 'е', 'ю').'т подтверждения поступления на счёт. '.
		'<a class="income-confirm">Подтвердить</a>'.
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
			'<td><span>Наличные:</span> '._viewer($r['viewer_id'], 'name').
			'<td class="r">'.$sum.($r['cash'] == -1 ? '' : ' руб.').
			'<td><div val="'.$r['viewer_id'].'" class="img_note'._tooltip('Посмотреть историю операций', -95).'</div>';
		$send['cash'][] = '{'.
				'id:'.$r['viewer_id'].','.
				'name:"'.addslashes(_viewer($r['viewer_id'], 'name')).'",'.
				'sum:"'.$sum.'"'.
			'}';
		$send['cash_spisok'][] = '{'.
				'uid:'.$r['viewer_id'].','.
				'title:"Наличные: '.addslashes(_viewer($r['viewer_id'], 'name')).'"'.
			'}';
	}
	$send['spisok'] .= '</table>';
	return $send;
}//cash_spisok()
function invoice_spisok() {
	$invoice = _invoice();
	if(empty($invoice))
		return 'Счета не определены.';

	$send = '<table class="_spisok">';
	foreach($invoice as $r)
		$send .= '<tr>'.
			'<td class="name"><b>'.$r['name'].'</b><pre>'.$r['about'].'</pre>'.
			'<td class="balans">'.
			($r['start'] != -1 ? '<b>'._sumSpace(_invoiceBalans($r['id'])).'</b> руб.' : (VIEWER_ADMIN ? '' : '<a class="invoice_set" val="'.$r['id'].'">Установить текущую сумму</a>')).
			'<td><div val="'.$r['id'].'" class="img_note'._tooltip('Посмотреть историю операций', -95).'</div>'.
			(VIEWER_ADMIN ? '<td><a class="invoice_set" val="'.$r['id'].'">Установить текущую сумму</a>' : '');
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
			'<th>Cумма'.
			'<th>Со счёта'.
			'<th>На счёт'.
			'<th>Подробно'.
			'<th>Дата';
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
					   (!$r['invoice_to'] && $r['worker_to'] ? '<br /><span class="confirm'.($r['confirm'] ? '' : ' no').'">'.($r['confirm'] ? '' : 'не ').'подтверждено</span>' : '').
				'<td class="about">'.
					($r['income_count'] ? '<a class="income-show" val="'.$r['income_ids'].'">'.$r['income_count'].' платеж'._end($r['income_count'], '', 'а', 'ей').'</a>' : '').
					(VIEWER_ADMIN && $r['confirm'] && $r['about'] ? ($r['income_count'] ? '<br />' : '').$r['about'] : '').
				'<td class="dtime">'.FullDataTime($r['dtime_add'], 1);
	$send .= '</table>';
	return $send;
}//transfer_spisok()
function invoiceHistoryAction($id, $i='name') {//Варианты действий в истории счетов
	$action = array(
		1 => array(
			'name' => 'Внесение платежа',
			'znak' => '',
			'cash' => 1 //Учитывать внутренние счета при внесении
		),
		2 => array(
			'name' => 'Удаление платежа',
			'znak' => '-',
			'cash' => 1
		),
		3 => array(
			'name' => 'Восстановление платежа',
			'znak' => '',
			'cash' => 1
		),
		4 => array(
			'name' => 'Перевод между счетами',
			'znak' => '',
			'cash' => 0
		),
		5 => array(
			'name' => 'Установка текущей суммы',
			'znak' => '',
			'cash' => 0
		),
		6 => array(
			'name' => 'Внесение расхода',
			'znak' => '-',
			'cash' => 1
		),
		7 => array(
			'name' => 'Удаление расхода',
			'znak' => '',
			'cash' => 1
		),
		8 => array(
			'name' => 'Восстановление расхода',
			'znak' => '-',
			'cash' => 1
		),
		9 => array(
			'name' => 'Редактирование расхода',
			'znak' => '',
			'cash' => 0
		),
		10 => array(
			'name' => 'Изменение платежа',
			'znak' => '',
			'cash' => 1
		),
		11 => array(
			'name' => 'Подтверждение платежа',
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
	$invoice = $v['invoice_id'] > 100 ? 'Наличные '._viewer($v['invoice_id'], 'name') : _invoice($v['invoice_id']);
	$send = '';
	if($v['page'] == 1)
		$send = '<div>Счёт <u>'.$invoice.'</u>:</div>'.
				'<input type="hidden" id="invoice_history_id" value="'.$v['invoice_id'].'" />';

	$all = query_value("SELECT COUNT(*) FROM `invoice_history` WHERE `invoice_id`=".$v['invoice_id']);
	if(!$all)
		return $send.'<br />Истории нет.';

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
					'<tr><th>Действие'.
						'<th>Сумма'.
						'<th>Баланс'.
						'<th>Описание'.
						'<th>Дата';
	foreach($history as $r) {
		$about = '';
		if($r['zayav_id'])
			$about = $r['zayav_link'].
					 ($r['dogovor_id'] ? '. '.'Авансовый платёж (договор '.$r['dogovor_nomer'].')' : '').
					 ' ';
		$about .= $r['prim'].' ';
		$worker = $r['worker_id'] ? '<u>'._viewer($r['worker_id'], 'name').'</u> ' : '';
		$expense = $r['expense_id'] ? '<span class="type">'._expense($r['expense_id']).(!trim($about) && !$worker ? '' : ': ').'</span> ' : '';
		//$income = $r['income_id'] ? '<div class="type">'._income($r['income_id']).(empty($about) ? '' : ': ').'</div>' : '';
		if($r['invoice_from'] != $r['invoice_to']) {//Счета не равны, перевод внешний
			if(!$r['invoice_to'])//Деньги были переданы руководителю
				$about .= 'Передача сотруднику '._viewer($r['worker_to'], 'name');
			elseif(!$r['invoice_from'])//Деньги были получены от руководителя
				$about .= 'Получение от сотрудника '._viewer($r['worker_from'], 'name');
			elseif($r['invoice_id'] == $r['invoice_from'])//Просматриваемый счёт общий - оправитель
				$about .= 'Отправление на счёт <span class="type">'._invoice($r['invoice_to']).'</span>'.
						 ($r['worker_to'] ? ' '._viewer($r['worker_to'], 'name') : '').
						 ($r['worker_from'] ? ' со счёта <span class="type">'._invoice($r['invoice_from']).'</span> '._viewer($r['worker_from'], 'name') : '');
			elseif($r['invoice_id'] == $r['invoice_to'])//Просматриваемый счёт общий - получатель
				$about .= 'Поступление со счёта <span class="type">'._invoice($r['invoice_from']).'</span>'.
					($r['worker_from'] ? ' '._viewer($r['worker_from'], 'name') : '').
					($r['worker_to'] ? ' на счёт <span class="type">'._invoice($r['invoice_to']).'</span> '._viewer($r['worker_to'], 'name') : '');
			elseif($r['invoice_id'] == $r['worker_from'])//Просматриваемый счёт сотрудника - оправитель
				$about .= 'Отправление на счёт <span class="type">'._invoice($r['invoice_to']).'</span>';
			elseif($r['invoice_id'] == $r['worker_to'])//Просматриваемый счёт сотрудника - оправитель
				$about .= 'Поступление со счёта <span class="type">'._invoice($r['invoice_from']).'</span>';
		} else {//Счета равны, перевод внутренний
			if($r['invoice_id'] == $r['worker_from'])//Просматриваемый счёт сотрудника - оправитель
				$about .= 'Отправление на счёт <span class="type">'._invoice($r['invoice_to']).'</span> '._viewer($r['worker_to'], 'name');
			if($r['invoice_id'] == $r['worker_to'])//Просматриваемый счёт сотрудника - получатель
				$about .= 'Поступление со счёта <span class="type">'._invoice($r['invoice_from']).'</span> '._viewer($r['worker_from'], 'name');
		}
		$about .=
			($r['income_count'] ?
					' <a class="income-show" val="'.$r['income_ids'].'">'.
						$r['income_count'].' платеж'._end($r['income_count'], '', 'а', 'ей').
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
				'<span>Показать ещё '.$c.' запис'._end($c, 'ь', 'и', 'ей').'</span>';
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
	if($v['worker_id'] && _viewerRules($v['worker_id'], 'RULES_CASH')) //Если существует сотрудник и есть личный счёт, то он является счётом
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
				if($r['invoice_from'] && $r['invoice_to'] && $r['invoice_from'] == $r['invoice_to']) {//внутренний перевод
					$v['invoice_id'] = $r['worker_from'];
					invoice_history_insert_sql($r['worker_to'], $v);
					$v['sum'] *= -1;
					break;
				}
				if(!$r['invoice_from'] && !$r['invoice_to'])
					return;
				if(!$r['invoice_from']) {//взятие средств у руководителя
					$v['invoice_id'] = $r['invoice_to'];
					if($r['worker_to'])
						invoice_history_insert_sql($r['worker_to'], $v);
					break;
				}
				if(!$r['invoice_to']) {//передача средств руководителю
					$v['invoice_id'] = $r['invoice_from'];
					$v['sum'] *= -1;
					if($r['worker_from'])
						invoice_history_insert_sql($r['worker_from'], $v);
					break;
				}
				//Передача из банка в наличные и на счета сотрудников
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
	'<a href="'.URL.'&p=report&d=money&d1=income&d2=all">Год</a> » '.(YEAR ? '' : '<b>За всё время</b>').
	(MON ? '<a href="'.URL.'&p=report&d=money&d1=income&d2=year&year='.YEAR.'">'.YEAR.'</a> » ' : '<b>'.YEAR.'</b>').
	(DAY ? '<a href="'.URL.'&p=report&d=money&d1=income&d2=month&mon='.YEAR.'-'.MON.'">'._monthDef(MON, 1).'</a> » ' : (MON ? '<b>'._monthDef(MON, 1).'</b>' : '')).
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
	'<div class="headName">Суммы платежей по годам</div>'.
	'<table class="_spisok sums">'.
		'<tr><th>Год'.
			'<th>Всего'.
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
	'<div class="headName">Суммы платежей по месяцам за '.$year.' год</div>'.
	'<div class="inc-path">'.income_path($year).'</div>'.
	'<table class="_spisok sums">'.
		'<tr><th>Месяц'.
			'<th>Всего'.
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
	'<div class="headName">Суммы платежей по дням за '._monthDef(MON, 1).' '.YEAR.'</div>'.
	'<div class="inc-path">'.$path.'</div>'.
	'<table class="_spisok sums">'.
		'<tr><th>Месяц'.
			'<th>Всего'.
			$th.
			implode('', $spisok).
	'</table>';
}//income_month()
function income_day($day) {
	$data = income_spisok(array('day' => $day));
	return
	'<script type="text/javascript">var OPL={from:"income"};</script>'.
	'<div class="headName">Список платежей<a class="add income-add">Внести платёж</a></div>'.
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
		'<div class="findHead">Виды платежей</div>'.
		'<input type="hidden" id="income_id">'.
	(RULES_MONEY ?
		'<script type="text/javascript">var WORKERS='.$workers.';</script>'.
		'<div class="findHead">Вносил сотрудник</div>'.
		'<input type="hidden" id="worker_id">'
	: '').
		_check('deleted', 'Удалённые платежи');
}//income_right()

function income_insert($v) {//Внесение платежа
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
			'spisok' => '<div class="_empty">Платежей нет.</div>'
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
				'Показан'._end($send['all'], '', 'о').
				' <b>'.$send['all'].'</b> платеж'._end($send['all'], '', 'а', 'ей').
				' на сумму <b>'._sumSpace($send['sum']).'</b> руб.'.
			'</div>' : '').
			'<table class="_spisok inc _money">'.
		(!$filter['zayav_id'] ?
				'<tr>'.
					($filter['owner_id'] || $filter['confirm'] ? '<th>'._check('money_all') : '').
					'<th>Сумма'.
					'<th>Описание'.
					'<th>Дата'.
					(!$filter['owner_id'] && !$filter['ids'] && !$filter['confirm'] ? '<th>' : '')
		: '');
	foreach($money as $r)
		$send['spisok'] .= income_unit($r, $filter);
	if($start + $filter['limit'] < $send['all']) {
		$c = $send['all'] - $start - $filter['limit'];
		$c = $c > $filter['limit'] ? $filter['limit'] : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'" id="income_next"><td colspan="5">'.
				'<span>Показать ещё '.$c.' платеж'._end($c, '', 'а', 'ей').'</span>';
	}
	if($page == 1)
		$send['spisok'] .= '</table>';
	return $send;
}//income_spisok()
function income_unit($r, $filter=array()) {
	$about = '';
	if($r['dogovor_id'])
		$about .= 'Авансовый платеж '.
			(!$filter['zayav_id'] ? 'по заявке '.$r['zayav_link'].' ' : '').
			'(договор '.$r['dogovor_nomer'].').';
	elseif($r['zayav_id'] && !$filter['zayav_id'])
		$about .= $r['zayav_link'].'. ';
	$about .= $r['prim'];
	if($r['confirm'])
		$about .= '<br /><span class="red">Ожидает подтверждения</span>';
	$sumTitle = $filter['zayav_id'] ? _tooltip('Платёж', 5) : '">';
	return
		'<tr val="'.$r['id'].'"'.($r['deleted'] ? ' class="deleted"' : '').'>'.
			(!empty($filter['owner_id']) || !empty($filter['confirm']) ? '<td class="choice">'._check('money_'.$r['id'], '', isset($filter['ids_ass'][$r['id']])) : '').
			'<td class="sum opl'.$sumTitle.''._sumSpace($r['sum']).
			'<td><span class="type">'._income($r['income_id']).(empty($about) ? '' : ':').'</span> '.$about.
			'<td class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -40).FullDataTime($r['dtime_add']).
		(empty($filter['owner_id']) && empty($filter['ids']) && empty($filter['confirm']) ?
			'<td class="ed"><a href="'.SITE.'/view/cashmemo.php?'.VALUES.'&id='.$r['id'].'" target="_blank" class="img_doc'._tooltip('Распечатать квитанцию', -140, 'r').'</a>'.
				(!$r['dogovor_id'] ?
					'<div class="img_del income-del'._tooltip('Удалить платёж', -95, 'r').'</div>'.
					'<div class="img_rest income-rest'._tooltip('Восстановить платёж', -125, 'r').'</div>'
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
	$invoice = array(0=>'Любой счёт');
	foreach(_invoice() as $id => $r)
		$invoice[$id] = $r['name'];
	return '<script type="text/javascript">var WORKERS='.$workers.';</script>'.
	'<div class="findHead">Категория</div>'.
	'<input type="hidden" id="category">'.
	'<div class="findHead">Сотрудник</div>'.
	'<input type="hidden" id="worker">'.
	'<div class="findHead">Счёт</div>'.
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
	'<div class="headName">Список расходов организации<a class="add">Новый расход</a></div>'.
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
		return $send + array('spisok' => '<div class="_empty">Расходов нет.</div>');

	$all = $send['all'];
	$page = $filter['page'];
	$limit = $filter['limit'];
	$start = ($page - 1) * $limit;

	$send['spisok'] = '';
	if($page == 1) {
		$send['spisok'] =
		'<div class="_moneysum">'.
			'Показан'._end($all, 'а', 'о').' <b>'.$all.'</b> запис'._end($all, 'ь', 'и', 'ей').
			' на сумму <b>'.abs($send['sum']).'</b> руб.'.
			(empty($dtime) ? ' за всё время.' : '').
		'</div>'.
		'<table class="_spisok _money">'.
			'<tr><th>Сумма'.
				'<th>Описание'.
				'<th>Дата'.
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
			//$dtimeTitle .= "\n".'Удалил: '.$r['viewer_del']."\n".FullDataTime($r['dtime_del']);
		$send['spisok'] .= '<tr'.($r['deleted'] ? ' class="deleted"' : '').' val="'.$r['id'].'">'.
			'<td class="sum"><b>'._sumSpace(abs($r['sum'])).'</b>'.
			'<td>'.($r['expense_id'] ? '<span class="type">'._expense($r['expense_id']).($r['prim'] || $r['worker_id'] ? ':' : '').'</span> ' : '').
				($r['worker_id'] ? '<u>'._viewer($r['worker_id'], 'name').'</u>' : '').
				($r['prim'] && $r['worker_id'] ? ', ' : '').$r['prim'].
			'<td class="dtime'.$dtimeTitle.FullDataTime($r['dtime_add']).
			'<td class="ed r">'.
				//'<div class="img_edit" title="Редактировать"></div>'.
				'<div class="img_del'._tooltip('Удалить расход', -52).'</div>'.
				'<div class="img_rest'._tooltip('Восстановить расход', -67).'</div>';
	}
	if($start + $limit < $all)
		$send['spisok'] .= '<tr class="_next" val="'.($page + 1).'"><td colspan="4"><span>Показать далее...</span>';
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
			$dtime = $s ? '<div class="dtime'._tooltip('Дата создания', -20).FullDataTime($savedDtime[$y.'-'.$mon], 1).'</div>' : '';
			if($y == 2014 && $mon == 1)
				$td = '<span class="grey">'.$mName.'</span>';
			elseif($s)
				$td = '<a href="'.$saved[$y.'-'.$mon].'">'.$mName.': фиксированный отчёт</a>';
			else
				$td = '<a href="'.SITE.'/view/report_month.php?'.VALUES.'">'.$mName.': текущий отчёт</a>';
			$months .= '<tr><td>'.$td.'<td>'.$dtime;
		}
		$spisok .= '<a class="yr">'.$y.'</a>'.
				   '<table class="_spisok'.($curYear != $y ? ' dn' : '').'">'.$months.'</table>';
	}
	return
	'<div id="report_month">'.
		'<div class="headName">Формирование отчётов за месяц</div>'.
		'<div class="_info">'.
			'Отчёты автоматически формируются 1-го числа каждого месяца и становятся фиксированными (неизменяемыми). '.
			'Если месяц ещё не закончился, есть возможность посмотреть текущий отчёт.'.
		'</div>'.
		$spisok.
	'</div>';
}//report_month()

function salary() {
	return
		'<div class="headName">Начисления зарплаты сотрудников</div>'.
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

	//Начисления с заявками
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

	//Начисления без заявок
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
				'<tr><th>Фио'.
					'<th>Ставка'.
					'<th>Баланс';
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

	//Получение сумм автоматичиских и ручных начислений
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

	//Получение сумм начислений по заявкам
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

	//Получение сумм зп
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
		return 'Сотрудника не существует.';
	if(_viewerRules($worker_id, 'RULES_NOSALARY'))
		return 'У сотрудника <u>'._viewer($worker_id, 'name').'</u> не начисляется зарплата.';
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
		'<div class="headName">'._viewer($worker_id, 'name').': история з/п за <em>'._monthDef(strftime('%m'), 1).' '.strftime('%Y').'</em>.</div>'.
		'<div id="spisok">'.salary_worker_spisok(array('worker_id'=>$worker_id)).'</div>';
}//salary_worker()
function salary_worker_spisok($v) {
	$filter = array(
		'worker_id' => !empty($v['worker_id']) && preg_match(REGEXP_NUMERIC, $v['worker_id']) ? intval($v['worker_id']) : 0,
		'mon' => empty($v['mon']) ? strftime('%Y-%m') : $v['mon']
	);

	if(!$filter['worker_id'])
		return 'Некорректный id сотрудника';

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
		$balans = '<b style="color:#'.($balans < 0 ? 'A00' : '090').'">'.$balans.'</b> руб.';
	} else
		$balans = '<a class="start-set">установить</a>';
	$rate = _viewer($filter['worker_id'], 'rate');
	$send =
		'<div class="uhead">'.
			'<h1>'.
				'Ставка: '.($rate != 0 ? '<b>'.round($rate, 2).'</b> руб.<span>('._viewer($filter['worker_id'], 'rate_day').'-е число месяца)</span>' : 'нет').
				'<a class="rate-set">Изменить ставку</a>'.
			'</h1>'.
			'Баланс: '.$balans.
			'<div class="a">'.
				'<a class="up">Начислить</a> :: '.
				'<a class="down">Выдать з/п</a> :: '.
				'<a class="deduct">Внести вычет</a>'.
			'</div>'.
		'</div>'.
		'<div id="salary-sel"></div>';

	$sql = "(SELECT
				'З/п' AS `type`,
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
				'Начисление' AS `type`,
				`e`.`id`,
			    `e`.`sum`,
				'".(BONUS ? 'от' : 'уст:')."' AS `about`,
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
				'Начисление' AS `type`,
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
				'Вычет' AS `type`,
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
		return $send.'<div class="_empty">Список пуст.</div>';
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
			'<th>Вид'.
			'<th>Сумма'.
			'<th>Описание'.
			'<th>';


	krsort($spisok);
	$toAll = ' to-all';
	foreach($spisok as $r) {
		$about = $r['zayav_id'] ? $r['zayav_link'].' '.$r['about'].' '.FullData($r['mon'], 1) : $r['about'];
		if($r['type'] == 'З/п') //если встречается платёж, то дальнейшие начисления общей галочкой не выбираются
			$toAll = '';
		$send .=
			'<tr val="'.$r['id'].'">'.
				'<td class="ch'.$toAll.'">'.($r['type'] != 'З/п' ? _check('s'.$r['id']) : '').
				'<td class="type">'.$r['type'].
				'<td class="sum">'.round($r['sum'], 2).
				'<td class="about">'.$about.
				'<td class="ed">'.
					($r['del'] ? '<div class="img_del'.$r['del']._tooltip('Удалить', -29).'</div>' : '');
	}
	$send .= '</table>';
	return $send;
}//salary_worker_spisok()




// ---===! setup !===--- Секция настроек

function setup() {
	$pages = array(
		'my' => 'Мои настройки',
		'worker' => 'Сотрудники',
		'rekvisit' => 'Реквизиты организации',
		'product' => 'Виды изделий',
		'invoice' => 'Счета',
		'income' => 'Виды платежей',
		'expense' => 'Категории расходов',
		'zayavrashod' => 'Расходы по заявке'
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
		'<div class="headName">Пин-код</div>'.
		'<div class="_info">'.
			'<p>Пин-код необходим для дополнительного подтверждения вашей личности, '.
			'если другой пользователь получит доступ к вашей странице ВКонтакте.'.
			'<br />'.
			'<p>Пин-код нужно будет вводить каждом новом входе в приложение, '.
			'а также при отсутсвии действий в программе в течение 3-х часов.'.
			'<br />'.
			'<p>Если вы забудете пин-код, обратитесь к руководителю, чтобы сбросить его.'.
		'</div>'.
	(PIN ?
		'<div class="vkButton pinchange"><button>Изменить пин-код</button></div>'.
		'<div class="vkButton pindel"><button>Удалить пин-код</button></div>'
		 :
		'<div class="vkButton pinset"><button>Установить пин-код</button></div>'
	).
	'</div>';
}//setup_my()

function setup_worker() {
	if(!RULES_WORKER)
		return _norules('Управление сотрудниками');
	return
	'<div id="setup_worker">'.
		'<div class="headName">Управление сотрудниками<a class="add">Новый сотрудник</a></div>'.
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
					  ($r['enter_last'] != '0000-00-00 00:00:00' ? '<div class="activity">Заходил'.($r['sex'] == 1 ? 'a' : '').' в приложение '.FullDataTime($r['enter_last']).'</div>' : '').
		'</table>';
	}
	return $send;
}//setup_worker_spisok()
function setup_worker_rules($viewer_id) {
	$u = _viewer($viewer_id);
	if(!RULES_WORKER)
		return _norules('Настройка прав для сотрудника '.$u['name']);
	if(!isset($u['worker']))
		return 'Сотрудника не существует.';
	$rule = _viewerRules($viewer_id);
	return
	'<script type="text/javascript">var RULES_VIEWER_ID='.$viewer_id.';</script>'.
	'<div id="setup_rules">'.

		'<table class="utab">'.
			'<tr><td>'.$u['photo'].
				'<td><div class="name">'.$u['name'].'</div>'.
					 ($viewer_id < VIEWER_MAX ? '<a href="http://vk.com/id'.$viewer_id.'" class="vklink" target="_blank">Перейти на страницу VK</a>' : '').
		'</table>'.

		'<div class="headName">Общее</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">Имя:<td><input type="text" id="first_name" value="'.$u['first_name'].'" />'.
			'<tr><td class="lab">Фамилия:<td><input type="text" id="last_name" value="'.$u['last_name'].'" />'.
			'<tr><td class="lab">Должность:<td><input type="text" id="post" value="'.$u['post'].'" />'.
			'<tr><td><td><div class="vkButton g-save"><button>Сохранить</button></div>'.
		'</table>'.

	(!$u['admin'] && $u['pin'] ?
		'<div class="headName">Пин-код</div>'.
		'<div class="vkButton pin-clear"><button>Сбросить пин-код</button></div>'
	: '').

	'<div class="headName">Дополнительно</div>'.
	'<table class="rtab">'.
		'<tr><td class="lab">Начисление бонусов:<td><input type="hidden" id="rules_bonus" value="'.$rule['RULES_BONUS'].'" />'.
		'<tr><td class="lab">Внутренний наличный счёт:<td>'._check('rules_cash', '', $rule['RULES_CASH']).
		'<tr><td class="lab">Может принимать<br />и передавать деньги:<td>'._check('rules_getmoney', '', $rule['RULES_GETMONEY']).
		'<tr><td class="lab">Не отображать<br />в начислениях з/п:<td>'._check('rules_nosalary', '', $rule['RULES_NOSALARY']).
		'<tr><td><td><div class="vkButton dop-save"><button>Сохранить</button></div>'.
	'</table>'.

	(!$u['admin'] && $viewer_id < VIEWER_MAX && RULES_RULES?
		'<div class="headName">Права</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">Разрешать вход<br />в приложение:<td>'._check('rules_appenter', '', $rule['RULES_APPENTER']).
		'</table>'.
		'<div class="app-div'.($rule['RULES_APPENTER'] ? '' : ' dn').'">'.
			'<table class="rtab">'.
				'<tr><td class="lab top">Управление установками:'.
					'<td class="setup-div">'.
						_check('rules_worker', 'Сотрудники', $rule['RULES_WORKER']).
						_check('rules_rules', 'Настройка прав сотрудников', $rule['RULES_RULES']).
						_check('rules_rekvisit', 'Реквизиты организации', $rule['RULES_REKVISIT']).
						_check('rules_product', 'Виды изделий', $rule['RULES_PRODUCT']).
						_check('rules_income', 'Счета и виды платежей', $rule['RULES_INCOME']).
						_check('rules_zayavrashod', 'Расходы по заявке', $rule['RULES_ZAYAVRASHOD']).
				'<tr><td class="lab">Видит историю действий:<td>'._check('rules_historyshow', '', $rule['RULES_HISTORYSHOW']).
				'<tr><td class="lab">Может видеть платежи:<td><input type="hidden" id="rules_money" value="'.$rule['RULES_MONEY'].'" />'.
			'</table>'.
		'</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab"><td><div class="vkButton rules-save"><button>Сохранить</button></div>'.
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
		return _norules('Реквизиты организации');
	$sql = "SELECT * FROM `setup_global`";
	$g = mysql_fetch_assoc(query($sql));
	return
	'<div id="setup_rekvisit">'.
		'<div class="headName">Реквизиты организации</div>'.
		'<table class="t">'.
			'<tr><td class="label">Название организации:<td><input type="text" id="org_name" maxlength="100" value="'.$g['org_name'].'">'.
			'<tr><td class="label">ОГРН:<td><input type="text" id="ogrn" maxlength="100" value="'.$g['ogrn'].'">'.
			'<tr><td class="label">ИНН:<td><input type="text" id="inn" maxlength="100" value="'.$g['inn'].'">'.
			'<tr><td class="label">КПП:<td><input type="text" id="kpp" maxlength="100" value="'.$g['kpp'].'">'.
			'<tr><td class="label top">Юридический адрес:<td><textarea id="yur_adres">'.$g['yur_adres'].'</textarea>'.
			'<tr><td class="label">Телефоны:<td><input type="text" id="telefon" maxlength="100" value="'.$g['telefon'].'">'.
			'<tr><td class="label">Адрес офиса:<td><input type="text" maxlength="100" id="ofice_adres" value="'.$g['ofice_adres'].'">'.
			'<tr><td><td><div class="vkButton"><button>Сохранить</button></div>'.
		'</table>'.
	'</div>';
}//setup_rekvisit()

function setup_product() {
	if(!RULES_PRODUCT)
		return _norules('Настройки видов изделий');
	return
	'<div id="setup_product">'.
		'<div class="headName">Настройки видов изделий<a class="add">Добавить</a></div>'.
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
		return 'Список пуст.';

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
				'<tr><th>Наименование'.
					'<th>Подвиды'.
					'<th>Кол-во<br />заявок'.
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
		return _norules('Настройки подвидов изделий');
	$sql = "SELECT * FROM `setup_product` WHERE `id`=".$product_id;
	if(!$pr = mysql_fetch_assoc(query($sql)))
		return 'Изделия id = '.$product_id.' не существует.';
	return
	'<script type="text/javascript">var PRODUCT_ID='.$product_id.';</script>'.
	'<div id="setup_product_sub">'.
		'<a href="'.URL.'&p=setup&d=product"><< назад к видам изделий</a>'.
		'<div class="headName">Список подвидов изделий для "'.$pr['name'].'"<a class="add">Добавить</a></div>'.
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
		return 'Список пуст.';

	$send = '<table class="_spisok">'.
				 '<tr><th>Наименование'.
					 '<th>Кол-во<br />заявок'.
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
		return _norules('Настройки счетов или видов платежей');
	return
	'<div id="setup_invoice">'.
		'<div class="headName">Управление счетами<a class="add">Новый счёт</a></div>'.
		'<div class="spisok">'.setup_invoice_spisok().'</div>'.
	'</div>';
}//setup_invoice()
function setup_invoice_spisok() {
	$sql = "SELECT * FROM `invoice` ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return 'Список пуст.';

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
		'<tr><th class="name">Наименование'.
			'<th class="type">Виды платежей'.
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
		return _norules('Настройки счетов или видов платежей');
	return
	'<div id="setup_income">'.
		'<div class="headName">Настройки видов платежей<a class="add">Добавить</a></div>'.
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
		return 'Список пуст.';

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
		'<tr><th class="name">Наименование'.
			'<th class="confirm">Подтверждение<br />поступления<br />на счёт'.
			'<th class="money">Кол-во<br />платежей'.
			'<th class="set">'.
	'</table>'.
	'<dl class="_sort" val="setup_income">';
	foreach($prihod as $id => $r) {
		$money = $r['money'] ? '<b>'.$r['money'].'</b>' : '';
		$money .= isset($r['del']) ? ' <span class="del" title="В том числе удалённые">('.$r['del'].')</span>' : '';
		$send .='<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="confirm">'.($r['confirm'] ? 'да' : '').
					'<td class="money">'.$money.
					'<td class="set">'.
						'<div class="img_edit'._tooltip('Изменить', -33).'</div>'.
						(!$r['money'] && $id > 1 ? '<div class="img_del'._tooltip('Удалить', -29).'</div>' : '').
			'</table>';
	}
	$send .= '</dl>';
	return $send;
}//setup_income_spisok()

function setup_expense() {
	return
	'<div id="setup_expense">'.
		'<div class="headName">Категории расходов организации<a class="add">Добавить</a></div>'.
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
		return 'Список пуст.';

	$rashod = array();
	while($r = mysql_fetch_assoc($q))
		$rashod[$r['id']] = $r;

	$send =
		'<table class="_spisok">'.
			'<tr><th class="name">Наименование'.
				'<th class="worker">Показывать<br />список<br />сотрудников'.
				'<th class="use">Кол-во<br />записей'.
				'<th class="set">'.
		'</table>'.
		'<dl class="_sort" val="setup_expense">';
	foreach($rashod as $id => $r) {
		$send .='<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="worker">'.($r['show_worker'] ? 'да' : '').
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
		return _norules('Настройки расходов по заявке');
	return
	'<div id="setup_zayavexpense">'.
		'<div class="headName">Настройки категорий расходов по заявке<a class="add">Добавить</a></div>'.
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
		return 'Список пуст.';

	$rashod = array();
	while($r = mysql_fetch_assoc($q))
		$rashod[$r['id']] = $r;

	$send =
	'<table class="_spisok">'.
		'<tr><th class="name">Наименование'.
			'<th class="txt">Показывать<br />текстовое<br />поле'.
			'<th class="worker">Показывать<br />список<br />сотрудников'.
			'<th class="use">Кол-во<br />записей'.
			'<th class="set">'.
	'</table>'.
	'<dl class="_sort" val="setup_zayavexpense">';
	foreach($rashod as $id => $r) {
		$send .='<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="txt">'.($r['show_txt'] ? 'да' : '').
					'<td class="worker">'.($r['show_worker'] ? 'да' : '').
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
function c1() {// Дописывание даты к расходам заявок
	$sql = "SELECT `zayav_id`,`dtime_add` FROM `history` WHERE `type`=29 GROUP BY `zayav_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		query("UPDATE `zayav_expense` SET `dtime_add`='".$r['dtime_add']."' WHERE `zayav_id`=".$r['zayav_id']);
}

// обновление балансов заявок
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