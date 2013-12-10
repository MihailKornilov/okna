<?php
function _hashRead() {
	$_GET['p'] = isset($_GET['p']) ? $_GET['p'] : 'zayav';
	if(empty($_GET['hash'])) {
		define('HASH_VALUES', false);
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
		'<title>Пластиковые окна - Приложение '.API_ID.'</title>'.

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
			'<a class="debug_toggle'.(DEBUG ? ' on' : '').'">В'.(DEBUG ? 'ы' : '').'ключить Debug</a> :: '.
			'<a id="cache_clear">Очисить кэш ('.VERSION.')</a> :: '.
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

function GvaluesCreate() {//Составление файла G_values.js
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
function _prihodType($type_id=false, $i='name') {//Список изделий для заявок
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
		'30' => '30 мин.',
		'60' => '1 час',
		'90' => '1 час 30 мин.',
		'120' => '2 часа',
		'150' => '2 часа 30 мин.',
		'180' => '3 часа'
	);
	return $v ? $arr[$v] : $arr;
}//_zamerDuration()
function _dogLink($nomer, $text, $class='') {
	return '<a'.($class ? ' class="'.$class.'"' : '').' href="'.SITE.'/files/dogovor/dogovor_'.$nomer.'.doc" title="Распечатать">'.$text.'</a>';
}

function _mainLinks() {
	global $html;
//	_remindActiveSet();
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
			'name' => 'Напоминания',
			'page' => 'remind',
			'show' => 1
		),
		array(
			'name' => 'Отчёты',
			'page' => 'report',
			'show' => 1
		),
		array(
			'name' => 'Установки',
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
		'RULES_APPENTER' => 1,  // Разрешать вход в приложение
		'RULES_SETUP' => 1,     // Управление установками
		'RULES_WORKER' => 1,	// Сотрудники
		'RULES_REKVISIT' => 1,	// Реквизиты организации
		'RULES_PRODUCT' => 1,	// Виды изделий
		'RULES_PRIHODTYPE' => 1 // Виды платежей
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
	return '<div class="norules">'.($txt ? '<b>'.$txt.'</b>: н' : 'Н').'едостаточно прав.</div>';
}//_norules()

function numberToWord($num, $firstSymbolUp=false) {
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




// ---===! client !===--- Секция клиентов

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
function clientBalansUpdate($client_id) {//Обновление баланса клиента
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
		$send['spisok'] = '<div class="_empty">Клиентов не найдено.</div>';
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
			($r['balans'] ? '<div class="balans">Баланс: <b style=color:#'.($r['balans'] < 0 ? 'A00' : '090').'>'.$r['balans'].'</b></div>' : '').
			'<table>'.
				'<tr><td class="label">Имя:<td><a href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>'.
				($r['telefon'] ? '<tr><td class="label">Телефон:<td>'.$r['telefon'] : '').
				(isset($r['adres']) ? '<tr><td class="label">Адрес:<td>'.$r['adres'] : '').
				(isset($r['zayav_count']) ? '<tr><td class="label">Заявки:<td>'.$r['zayav_count'] : '').
			'</table>'.
		'</div>';
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .= '<div class="ajaxNext" val="'.($page + 1).'"><span>Показать ещё '.$c.' клиент'._end($c, 'а', 'а', 'ов').'</span></div>';
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
					'<div id="buttonCreate"><a>Новый клиент</a></div>'.
					'<div class="filter">'.
						_check('dolg', 'Должники').
					'</div>'.
		'</table>'.
	'</div>';
}//client_list()
function client_count($count, $dolg=0) {
	if($dolg)
		$dolg = abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE `balans`<0 LIMIT 1"));
	return ($count > 0 ?
		'Найден'._end($count, ' ', 'о ').$count.' клиент'._end($count, '', 'а', 'ов').
		($dolg ? '<em>(Общая сумма долга = '.$dolg.' руб.)</em>' : '')
		:
		'Клиентов не найдено');
}//client_count()

function clientInfoGet($client) {
	return
		'<div class="fio">'.$client['fio'].'</div>'.
		'<table class="cinf">'.
			'<tr><td class="label">Телефон:<td>'.$client['telefon'].
			'<tr><td class="label">Адрес:  <td>'.$client['adres'].
			'<tr><td class="label">Баланс: <td><b style=color:#'.($client['balans'] < 0 ? 'A00' : '090').'>'.$client['balans'].'</b>'.
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
	$sql = "SELECT * FROM `client` WHERE `status`=1 AND `id`=".$client_id;
	if(!$client = mysql_fetch_assoc(query($sql)))
		return _noauth('Клиента не существует');

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
			'title' => 'Заявка №'.$r['id'],
			'content' => 'Заявка №'.$r['id'].
						($r['dogovor_nomer'] ? '<div class="pole2">Договор №'.$r['dogovor_nomer'].'</div>' : '')
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
						'<a class="sel">Информация</a>'.
						'<a class="cedit">Редактировать</a>'.
						'<a class="oplata-add">Внести платёж</a>'.
						'<a class="zamer_add"><b>Новый замер</b></a>'.
						'<a class="cdel">Удалить клиента</a>'.
					'</div>'.
		'</table>'.

		'<div id="dopLinks">'.
			'<a class="link sel" val="zayav">Заявки'.($zamer['all'] ? ' ('.$zamer['all'].')' : '').'</a>'.
			'<a class="link" val="money">Платежи'.($money['all'] ? ' ('.$money['all'].')' : '').'</a>'.
			//'<a class="link" val="remind">Задания'.(!empty($remindData) ? ' ('.$remindData['all'].')' : '').'</a>'.
			'<a class="link" val="comm">Заметки'.($commCount ? ' ('.$commCount.')' : '').'</a>'.
			'<a class="link" val="hist">История'.($histCount ? ' ('.$histCount.')' : '').'</a>'.
		'</div>'.

		'<table class="tabLR">'.
			'<tr><td class="left">'.
					'<div id="zayav_spisok">'.$zamer['spisok'].'</div>'.
					'<div id="money_spisok">'.$money['spisok'].'</div>'.
					'<div id="remind_spisok">'.(!empty($remindData) ? report_remind_spisok($remindData) : '<div class="_empty">Заданий нет.</div>').'</div>'.
					'<div id="comments">'._vkComment('client', $client_id).'</div>'.
					'<div id="histories">'.report_history_spisok(1, array('client_id'=>$client_id)).'</div>'.
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

function _zamerLink($zayav_id, $zamer_nomer) {
	return '<a href="'.URL.'&p=zayav&d=info&id='.$zayav_id.'">№'.$zamer_nomer.'</a>';
}//_zamerLink()
function _zayavStatus($id=false) {
	$arr = array(
		'0' => array(
			'name' => 'Любой статус',
			'color' => 'ffffff'
		),
		'1' => array(
			'name' => 'В процессе',
			'color' => 'E8E8FF'
		),
		'2' => array(
			'name' => 'Выполнено',
			'color' => 'CCFFCC'
		),
		'3' => array(
			'name' => 'Отмена',
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

function product_spisok_test($product) {// Проверка корректности данных изделий при внесении в базу
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
			$right = '<div id="buttonCreate" class="zamer_add"><a>Новый замер</a></div>';
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
			$right = '<div id="buttonCreate"><a>Новая заявка<br />на установку</a></div>';
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
			'<a class="link'.($_GET['d'] == 'zamer' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=zamer">Замер'.($zamerCount ? ' ('.$zamerCount.')' : '').'</a>'.
			'<a class="link'.($_GET['d'] == 'dog' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=dog">Договор'.($dogovorCount ? ' ('.$dogovorCount.')' : '').'</a>'.
			'<a class="link'.($_GET['d'] == 'set' ? ' sel' : '').'" href="'.URL.'&p=zayav&d=set">Установка'.($setCount ? ' ('.$setCount.')' : '').'</a>'.
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

	$clear = '<a class="filter_clear">Очисить условия поиска</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'Замеров не найдено',
			'spisok' => '<div class="_empty">Замеров не найдено.</div>'
		);

	$send['result'] = $clear.'Показан'._end($send['all'], '', 'о').' '.$send['all'].' замер'._end($send['all'], '', 'а', 'ов');

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
				'<a class="name">Замер №'.$r['zamer_nomer'].'</a>'.
				'<table class="ztab">'.
					(empty($filter['client']) ? '<tr><td class="label">Клиент:<td>'.$client[$r['client_id']] : '').
					'<tr><td class="label top">Адрес:<td>'.$r['adres'].
				'</table>'.
			'</div>';
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="ajaxNext" val="'.($page + 1).'">'.
				'<span>Показать ещё '.$c.' замер'._end($c, '', 'а', 'ов').'</span>'.
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

	$clear = '<a class="filter_clear">Очисить условия поиска</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'Заявок на заключение договора не найдено',
			'spisok' => '<div class="_empty">Заявок на заключение договора не найдено.</div>'
		);

	$send['result'] = $clear.'Показан'._end($send['all'], '', 'о').' '.$send['all'].' заяв'._end($send['all'], 'ка', 'ки', 'ок');

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
			'<a class="name">Договор не заключен <span>(замер №'.$r['zamer_nomer'].')</span></a>'.
			'<table class="ztab">'.
				(empty($filter['client']) ? '<tr><td class="label">Клиент:<td>'.$client[$r['client_id']] : '').
				'<tr><td class="label top">Адрес:<td>'.$r['adres'].
			'</table>'.
		'</div>';
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="ajaxNext" val="'.($page + 1).'">'.
				'<span>Показать ещё '.$c.' заяв'._end($c, 'ку', 'ки', 'ок').'</span>'.
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

	$clear = '<a class="filter_clear">Очисить условия поиска</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'Установок не найдено',
			'spisok' => '<div class="_empty">Установок не найдено.</div>'
		);

	$send['result'] = $clear.'Показан'._end($send['all'], '', 'о').' '.$send['all'].' заяв'._end($send['all'], 'ка', 'ки', 'ок');

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
			'<a class="name">Установка №'.$r['set_nomer'].'</a>'.
			'<table class="ztab">'.
				(empty($filter['client']) ? '<tr><td class="label">Клиент:<td>'.$client[$r['client_id']] : '').
				'<tr><td class="label top">Адрес:<td>'.$r['adres'].
			'</table>'.
		'</div>';
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="ajaxNext" val="'.($page + 1).'">'.
				'<span>Показать ещё '.$c.' заяв'._end($c, 'ку', 'ки', 'ок').'</span>'.
			'</div>';
	}
	return $send;
}//set_spisok()

function zayav_info($zayav_id) {
	$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$zayav_id." LIMIT 1";
	if(!$r = mysql_fetch_assoc(query($sql)))
		return _noauth('Заявки не существует.');

	if($r['zamer_status'] == 1 || $r['zamer_status'] == 3)
		return zamer_info($r);
	if($r['dogovor_nomer'] == 0)
		return dogovor_info($r);
	if($r['dogovor_nomer'] > 0 && $r['set_status'] > 0)
		return set_info($r);
	return _noauth('Неизвестная заявка');
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
				'<a class="delete">Удалить замер</a>'.
				'<a class="link sel zinfo">Информация</a>'.
				'<a class="link zedit">Редактирование</a>'.
				'<a class="link hist">История</a>'.
			'</div>'.
			'<div class="headName">Заявка на замер №'.$z['zamer_nomer'].'</div>'.
			'<div class="content">'.
				'<table class="tabInfo">'.
					'<tr><td class="label">Клиент:<td>'._clientLink($z['client_id']).
					'<tr><td class="label">Дата приёма:'.
						'<td class="dtime_add" title="Заявку внёс '._viewer($z['viewer_id_add'], 'name').'">'.FullDataTime($z['dtime_add']).
					'<tr><td class="label top">Изделия:<td>'.zayav_product_spisok($z['id']).
					'<tr><td class="label">Адрес замера:<td>'.$z['adres'].
					'<tr><td class="label">Дата замера:'.
						'<td><span class="zamer-dtime" title="'._zamerDuration($z['zamer_duration']).'">'.FullDataTime($z['zamer_dtime']).'</span>'.
							($z['zamer_status'] == 1 ? '<span class="zamer-left">'.remindDayLeft($z['zamer_dtime']).'</span>' : '').
					'<tr><td class="label">Статус:'.
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
			'<a class="delete">Удалить заявку</a>'.
			'<a class="link sel zinfo">Информация</a>'.
			'<a class="link zedit">Редактирование</a>'.
			'<a class="link hist">История</a>'.
		'</div>'.
		'<div class="headName">Замер №'.$z['zamer_nomer'].' - ожидание решения клиента</div>'.
		'<div class="content">'.
			'<table class="tabInfo">'.
				'<tr><td class="label">Клиент:<td>'._clientLink($z['client_id']).
//				'<tr><td class="label">Дата приёма:'.
//					'<td class="dtime_add" title="Заявку внёс '._viewer($z['viewer_id_add'], 'name').'">'.FullDataTime($z['dtime_add']).
				'<tr><td class="label top">Изделия:<td>'.zayav_product_spisok($z['id']).
				'<tr><td class="label">Адрес:<td>'.$z['adres'].
				'<tr><td class="label">Статус замера:'.
					'<td><div style="background-color:#'._zayavStatusColor(2).'" class="status">'._zayavStatusName(2).'</div>'.
			'</table>'.
			'<div class="vkButton dogovor_create"><button>Заключить договор</button></div>'.
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
			'zayav_name:"<b>№'.$z['id'].'</b>"'.
		'};'.
	'</script>'.
	'<div class="zayav-info">'.
		'<div id="dopLinks">'.
//			'<a class="delete">Удалить заявку</a>'.
			'<a class="link sel zinfo">Информация</a>'.
			'<a class="link zedit">Редактирование</a>'.
			'<a class="link">Начислить</a>'.
			'<a class="link oplata-add">Внести платёж</a>'.
			'<a class="link hist">История</a>'.
		'</div>'.
		'<div class="headName">Установка №'.$z['set_nomer'].'</div>'.
		'<div class="content">'.
			'<table class="tabInfo">'.
				'<tr><td class="label">Клиент:<td>'._clientLink($z['client_id']).
//				'<tr><td class="label">Дата приёма:'.
//					'<td class="dtime_add" title="Заявку внёс '._viewer($z['viewer_id_add'], 'name').'">'.FullDataTime($z['dtime_add']).
				'<tr><td class="label">Договор:'.
					'<td><b class="dogn" title="от '.$d[2].'/'.$d[1].'/'.$d[0].' г. на сумму '.$dog['sum'].' руб. ">№'.$z['dogovor_nomer'].'</b> '.
						 _dogLink($z['dogovor_nomer'], '', 'img_word').
						'<a class="reneg">Перезаключить</a>'.
				'<tr><td class="label top">Изделия:<td>'.zayav_product_spisok($z['id']).
				'<tr><td class="label">Адрес установки:<td>'.$z['adres'].
				'<tr><td class="label">Статус:'.
					'<td><div style="background-color:#'._zayavStatusColor($z['set_status']).'" class="status">'._zayavStatusName($z['set_status']).'</div>'.
			'</table>'.
			'<div class="headBlue'.($money['all'] ? '' : ' dn').'">Платежи<a class="add oplata-add">Внести платёж</a></div>'.
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
	'<div class="head-name">ДОГОВОР №'.$v['id'].'</div>'.
	'<table class="city_data"><tr><td>Город Няндома<th>'.$d[2].'/'.$d[1].'/'.$d[0].' г.</table>'.
	'<div class="paragraph">'.
		'<p>Общество с ограниченной ответственностью «Территория Комфорта», '.
		'в лице менеджера по продажам, Билоченко Юлия Александровна, действующей на основании доверенности, '.
		'с одной стороны, и '.$v['fio'].', '.($v['pasp_empty'] ? $v['adres'] : $v['pasp_adres']).', именуемый в дальнейшем «Заказчик», с другой стороны, '.
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
	($v['pasp_empty'] ? '<br />'.$v['adres'] :
				'Паспорт серии '.$v['pasp_seria'].' '.$v['pasp_nomer'].'<br />'.
				'выдан '.$v['pasp_ovd'].' '.$v['pasp_data'].'<br /><br />'.
				$v['pasp_adres']).
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
		'<tr><td class="label">Заказ:<td class="title">'.$v['id'].'<td class="label">Заказчик:<td>'.$fioPodpis.
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
		'<tr><td class="label">Заказ:<td class="title">'.$v['id'].'<td class="label">Заказчик:<td>'.$fioPodpis.
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
			'<td>'.$r['count'].' шт.';
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
		($filter_break_show ? '<a id="filter_break">Сбросить условия поиска</a>' : '').
		($count > 0 ?
			'Показан'._end($count, 'а', 'о').' '.$count.' заяв'._end($count, 'ка', 'ки', 'ок')
			:
			'Заявок не найдено');
}//zayav_count()
function zayav_list($data, $values) {
	return
	'<div id="zayav">'.
		'<div class="result">'.zayav_count($data['all']).'</div>'.
		'<table class="tabLR">'.
			'<tr><td id="spisok">'.zayav_spisok($data).
				'<td class="right">'.
				'<div id="buttonCreate"><a HREF="'.URL.'&p=zayav&d=add&back=zayav">Новый замер</a></div>'.
				'<div id="find"></div>'.
				'<div class="findHead">Порядок</div>'.
//				_radio('sort', array(1=>'По дате добавления',2=>'По обновлению статуса'), $values['sort']).
				_check('desc', 'Обратный порядок', $values['desc']).
				'<div class="condLost'.(!empty($values['find']) ? ' hide' : '').'">'.
				//	'<div class="findHead">Категория</div>'.
				//	_radio('category', array(0=>'Все заявки',1=>'Замер',2=>'Установка'), $values['category'], 1).
					'<div class="findHead">Статус</div>'.
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
		return '<div class="_empty">Заявок не найдено.</div>';
	$send = '';
	foreach($data['spisok'] as $id => $r)
		$send .=
		'<div class="zayav_unit" style="background-color:#'.$r['status_color'].'" val="'.$id.'">'.
			'<div class="dtime">'.$r['dtime'].'</div>'.
			'<a class="name">'.($r['zamer'] ? 'Замер' : 'Установка').' №'.$id.'</a>'.
			'<table class="ztab">'.
				(isset($r['client']) ? '<tr><td class="label">Клиент:<td>'.$r['client'] : '').
				'<tr><td class="label top">Изделия:<td>'.zayav_product_spisok($r['product']).
			'</table>'.
		'</div>';

	if(isset($data['next']))
		$send .= '<div class="ajaxNext" val="'.($data['next']).'"><span>Следующие '.$data['limit'].' заявок</span></div>';
	return $send;
}//zayav_spisok()

function zayav_info_($zayav_id) {
	$sql = "SELECT * FROM `zayav` WHERE `status`>0 AND `id`=".$zayav_id." LIMIT 1";
	if(!$zayav = mysql_fetch_assoc(query($sql)))
		return _noauth('Заявки не существует.');

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
			'<a class="delete'.(!empty($money) ?  ' dn': '').'">Удалить заявку</a>'.
			'<a class="link sel zinfo">Информация</a>'.
			'<a class="link zedit">Редактирование</a>'.
			(!$zayav['zamer'] ? '<a class="link acc_add">Начислить</a>' : '').
			(!$zayav['zamer'] ? '<a class="link op_add">Принять платёж</a>' : '').
			'<a class="link hist">История</a>'.
		'</div>'.
		'<div class="headName">'.($zayav['zamer'] ? 'Заявка на замер' : 'Установка изделий').' №'.$zayav_id.'</div>'.
		'<div class="content">'.
			'<table class="tabInfo">'.
				'<tr><td class="label">Клиент:<td>'._clientLink($zayav['client_id']).
				'<tr><td class="label">Дата приёма:'.
					'<td class="dtime_add" title="Заявку внёс '._viewer($zayav['viewer_id_add'], 'name').'">'.FullDataTime($zayav['dtime_add']).
				(!$zayav['zamer'] ? '<tr><td class="label">Номер договора:<td><a>Заключить договор</a>' : '').
				($zayav['nomer_vg'] ? '<tr><td class="label">Номер ВГ:<td>'.$zayav['nomer_vg'] : '').
				'<tr><td class="label top">Изделия:<td>'.zayav_product_spisok($zayav_id).
				'<tr><td class="label">Адрес '.($zayav['zamer'] ? 'замера' : 'установки').':<td>'.$zayav['adres_set'].
				($zayav['zamer'] ?
					'<tr><td class="label">Дата замера:'.
					    '<td><span class="zamer-dtime" title="'._zamerDuration($zayav['zamer_duration']).'">'.FullDataTime($zayav['zamer_dtime']).'</span>'.
							'<span class="zamer-left">'.remindDayLeft($zayav['zamer_dtime']).'</span>' : '').
				'<tr><td class="label">Статус:'.
					'<td><div style="background-color:#'._zayavStatusColor($zayav['status']).'" class="status zamer_'.($zayav['zamer'] ? 'status' : 'set').'">'.
							_zayavStatusName($zayav['status']).
						'</div>'.
						'<div class="status_dtime">от '.FullDataTime($zayav['status_dtime'], 1).'</div>'.
				'<tr class="acc_tr'.($accSum > 0 ? '' : ' dn').'"><td class="label">Начислено: <td><b class="acc">'.$accSum.'</b> руб.'.
				'<tr class="op_tr'.($opSum > 0 ? '' : ' dn').'"><td class="label">Оплачено:	<td><b class="op">'.$opSum.'</b> руб.'.
					'<span class="dopl'.($dopl == 0 ? ' dn' : '').'" title="Необходимая доплата'."\n".'Если значение отрицательное, то это переплата">'.
						($dopl > 0 ? '+' : '').$dopl.
					'</span>'.
			'</table>'.
	//		'<div class="headBlue">Задания<a class="add remind_add">Добавить задание</a></div>'.
	//		'<div id="remind_spisok">'.report_remind_spisok(remind_data(1, array('zayav'=>$zayav['id']))).'</div>'.
			_vkComment('zayav', $zayav_id).

			(!$zayav['zamer'] ?
				'<div class="headBlue mon">Начисления и платежи'.
					'<a class="add op_add">Принять платёж</a>'.
					'<em>::</em>'.
					'<a class="add acc_add">Начислить</a>'.
				'</div>'.
				'<table class="_spisok _money">'.implode($money).'</table>' : '').
		'</div>'.
		'<div class="histories">'.report_history_spisok(1, array('zayav_id'=>$zayav_id)).'</div>'.
	'</div>';
}//zayav_info()
function zayav_accrual_unit($acc) {
	return
	'<tr><td class="sum acc" title="Начисление">'.$acc['sum'].'</td>'.
		'<td>'.$acc['prim'].'</td>'.
		'<td class="dtime" title="Начислил '._viewer(isset($acc['viewer_id_add']) ? $acc['viewer_id_add'] : VIEWER_ID, 'name').'">'.
			FullDataTime(isset($acc['dtime_add']) ? $acc['dtime_add'] : curTime()).
		'</td>'.
		'<td class="del"><div class="img_del acc_del" title="Удалить начисление" val="'.$acc['id'].'"></div></td>'.
	'</tr>';
}//zayav_accrual_unit()
function zayav_oplata_unit($op) {
	return
	'<tr><td class="sum op" title="Платёж">'.$op['sum'].'</td>'.
		'<td><em>'._prihodType($op['prihod_type']).($op['prim'] ? ':' : '').'</em>'.$op['prim'].'</td>'.
		'<td class="dtime" title="Платёж внёс '._viewer(isset($op['viewer_id_add']) ? $op['viewer_id_add'] : VIEWER_ID, 'name').'">'.
			FullDataTime(isset($op['dtime_add']) ? $op['dtime_add'] : curTime()).
		'</td>'.
		'<td class="del"><div class="img_del op_del" title="Удалить платёж" val="'.$op['id'].'"></div></td>'.
	'</tr>';
}//zayav_oplata_unit()
*/




// ---===! remind !===--- Секция напоминаний

function remindDayLeft($d) {
	$dayLeft = floor((strtotime($d) - TODAY_UNIXTIME) / 3600 / 24);
	if($dayLeft < 0)
		return 'Просрочен'._end($dayLeft * -1, ' ', 'о ').($dayLeft * -1)._end($dayLeft * -1, ' день', ' дня', ' дней');
	if($dayLeft > 2)
		return 'Остал'._end($dayLeft, 'ся ', 'ось ').$dayLeft._end($dayLeft, ' день', ' дня', ' дней');
	switch($dayLeft) {
		default:
		case 0: return 'Выполнить сегодня';
		case 1: return 'Выполнить завтра';
		case 2: return 'Выполнить послезавтра';
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
			'<tr class="week-name"><td>пн<td>вт<td>ср<td>чт<td>пт<td>сб<td>вс';

	$unix = strtotime($year.'-'.$month.'-01');
	$dayCount = date('t', $unix);   // Количество дней в месяце
	$week = date('w', $unix);       // Номер первого дня недели
	if(!$week)
		$week = 7;

	$curUnix = strtotime(strftime('%Y-%m-%d')); // Текущий день для выделения прошедших дней

	$curMonth = $year == strftime('%Y') && $month == strftime('%m');
	$curDay = round(strftime('%d'));

	$send .= '<tr>';
	for($n = $week; $n > 1; $n--, $send .= '<td>'); // Вставка пустых полей, если первый день недели не понедельник
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
		'<div class="full"><div class="fhead">Календарь напоминаний: 2013 </div>'.$fullCalendar.'</div>'.
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
		return 'Напоминаний нет.';
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
					'Заявка на замер №'.$r['id'].
			'</a>'.
			'<div class="to">Дата: '.FullDataTime($r['zamer_dtime']).'<span class="dur">'._zamerDuration($r['zamer_duration']).'</span></div>'.
			'<div class="day_left">'.remindDayLeft($r['zamer_dtime']).'<a class="action zamer_status" val="'.$r['id'].'">Действие</a></div>'.
		'</div>';
	}

	return $send;
}//remind_spisok()


// ---===! report !===--- Секция отчётов

function report() {
	$def = 'history';
	$pages = array(
		'history' => 'История действий',
		'money' => 'Платежи'
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
			$left = '<div class="headName">Список платежей</div>'.$data['spisok'];
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
		case 1: return 'Внесение нового клиента '.$v['client'].'.';
		case 2: return 'Изменение данных клиента '.$v['client'].':<div class="changes">'.$v['value'].'</div>';
		case 3: return 'Удаление клиента '.$v['client'].'.';

		case 4: return 'Внесение новой заявки на замер '._zamerLink($v['zayav_id'], $v['value']).' для клиента '.$v['client'].'.';
		case 5: return 'Изменение данных заявки '.$v['zayav'].':<div class="changes">'.$v['value'].'</div>';
		case 6: return 'Удаление заявки '.$v['zayav'].'.';

		case 7: return 'Начисление на сумму <b>'.$v['value'].'</b> руб.'.($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').' по заявке '.$v['zayav'].'.';
		case 8: return 'Удаление начисления на сумму <b>'.$v['value'].'</b> руб.'.($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').' у заявки '.$v['zayav'].'.';
		case 9: return 'Восстановление начисления на сумму <b>'.$v['value'].'</b> руб.'.($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').' у заявки '.$v['zayav'].'.';

		case 10: return
			'Платёж <span class="oplata">'._prihodType($v['value2']).'</span> '.
			'на сумму <b>'.$v['value'].'</b> руб.'.
			($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').
			' по заявке '._zamerLink($v['zayav_id'], $v['zayav_id']).'.';
		case 11: return
			'Удаление платежа <span class="oplata">'._prihodType($v['value2']).'</span> '.
			'на сумму <b>'.$v['value'].'</b> руб.'.
			($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').
			' у заявки '._zamerLink($v['zayav_id'], $v['zayav_id']).'.';
		case 12: return
			'Восстановление платежа <span class="oplata">'._prihodType($v['value2']).'</span> '.
			'на сумму <b>'.$v['value'].'</b> руб.'.
			($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').
			' у заявки '._zamerLink($v['zayav_id'], $v['zayav_id']).'.';

		case 13: return 'Добавление нового сотрудника '._viewer($v['value'], 'link').'.';
		case 14: return 'Удаление сотрудника '._viewer($v['value'], 'link').'.';

		case 15: return 'Изменение информации о дате или продолжительности замера '._zamerLink($v['zayav_id'], $v['value']).':<div class="changes">'.$v['value1'].'</div>';
		case 16: return 'Замер '._zamerLink($v['zayav_id'], $v['value']).' выполнен и отправлен на заключение договора.';
		case 17: return 'Замер '._zamerLink($v['zayav_id'], $v['value']).' отменён.';
		case 18: return 'Замер '._zamerLink($v['zayav_id'], $v['value']).' восстановлен.';
		case 19: return
			'Заключение '._dogLink($v['value1'], 'договора №'.$v['value1']).
			' от '.$v['value2'].' г.'.
			' на сумму <b>'.$v['value3'].'</b> руб.'.
			' для замера '._zamerLink($v['zayav_id'], $v['value']).'.';
		case 20: return
			'Внесение авансового платежа на  на сумму <b>'.$v['value2'].'</b> руб.'.
			' для замера '._zamerLink($v['zayav_id'], $v['value']).
			' при заключении '._dogLink($v['value1'], 'договора №'.$v['value1']).'.';

		case 501: return 'В установках: внесение нового наименования изделия "'.$v['value'].'".';
		case 502: return 'В установках: изменение данных изделия "'.$v['value1'].'":<div class="changes">'.$v['value'].'</div>';
		case 503: return 'В установках: удаление наименования изделия "'.$v['value'].'".';

		case 510: return 'В установках: изменение реквизитов организации:<div class="changes">'.$v['value'].'</div>';

		case 504: return 'В установках: внесение нового подвида для изделия "'.$v['value'].'": '.$v['value1'].'.';
		case 505: return 'В установках: изменение подвида у изделия "'.$v['value'].'":<div class="changes">'.$v['value1'].'</div>';
		case 506: return 'В установках: удаление подвида у изделия "'.$v['value'].'": '.$v['value1'].'.';

		case 507: return 'В установках: внесение нового наименования платежа "'.$v['value'].'".';
		case 508: return 'В установках: изменение данных платежа "'.$v['value'].'":<div class="changes">'.$v['value1'].'</div>';
		case 509: return 'В установках: удаление данных платежа "'.$v['value'].'".';
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
		return 'Истории по указанным условиям нет.';
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
		$send .= '<div class="ajaxNext" id="report_history_next" val="'.($page + 1).'"><span>Далее...</span></div>';
	return $send;
}//report_history_spisok()


function _zamerNomer($arr) {//Добавление к списку 'zamer_nomer', получаемого по zayav_id
	if(empty($arr))
		return array();
	$ids = array(); // идешники заявок
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

function money_insert($v) {//Внесение платежа
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
			'spisok' => '<div class="_empty">Платежей нет.</div>'
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
				'Показан'._end($send['all'], '', 'о').
				' <b>'.$send['all'].'</b> платеж'._end($send['all'], '', 'а', 'ей').
				' на сумму <b>'.$send['sum'].'</b> руб.'.
			'</div>' : '').
			'<table class="_spisok _money">'.
		(!$filter['zayav_id'] ?
				'<tr><th class="sum">Сумма'.
					'<th>Описание'.
					'<th class="data">Дата'.
					'<th>'
		: '');
	foreach($money as $r) {
		$about = '<span class="type">'._prihodType($r['prihod_type']).':</span> ';
		if($r['dogovor_nomer'] > 0)
			$about .= 'Авансовый платеж'.
				(!$filter['zayav_id'] ?
				' для замера '._zamerLink($r['zayav_id'], $r['zamer_nomer']).
				' при заключении '._dogLink($r['dogovor_nomer'], 'договора №'.$r['dogovor_nomer']).'.' : '');
		elseif($r['zayav_id'] > 0)
			$about .= 'Заявка '.$r['zayav_id'].'.';

		$about .= ' '.$r['prim'];
		$send['spisok'] .=
			'<tr val="'.$r['id'].'"><td class="sum"><b>'.$r['sum'].'</b>'.
				'<td>'.$about.
				'<td class="dtime" title="Вн'.(_viewer($r['viewer_id_add'], 'sex') == 1 ? 'есла' : 'ёс').' '._viewer($r['viewer_id_add'], 'name').'">'.FullDataTime($r['dtime_add']).
				'<td class="ed"><a href="'.URL.'&p=cashmemo&id='.$r['id'].'" class="img_doc" target="_blank"></a>'.
					(!$r['dogovor_nomer'] ? '<div class="img_del"></div>' : '');
	}
	if($start + $filter['limit'] < $send['all']) {
		$c = $send['all'] - $start - $filter['limit'];
		$c = $c > $filter['limit'] ? $filter['limit'] : $c;
		$send['spisok'] .=
			'<tr class="ajaxNext" val="'.($page + 1).'" id="money_next"><td colspan="4">'.
				'<span>Показать ещё '.$c.' платеж'._end($c, '', 'а', 'ей').'</span>';
	}
	$send['spisok'] .= '</table>';
	return $send;
}//money_spisok()
function cash_memo() {
	if(!preg_match(REGEXP_NUMERIC, @$_GET['id'])) {
		echo 'Некорректный id.';
		exit;
	}
	$id = intval($_GET['id']);
	$sql = "SELECT *
			FROM `money`
			WHERE `deleted`=0
			  AND `id`=".$id;
	if(!$r = mysql_fetch_assoc(query($sql))) {
		echo 'Платежа id = '.$id.' не существует.';
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
				($zayav['dogovor_nomer'] ? ' по договору №'.$zayav['dogovor_nomer'] : '').
				' за '.zayav_product_spisok($r['zayav_id'], 'cash').
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
	exit;
}//cash_memo()


// ---===! setup !===--- Секция настроек

function setup() {
	$pageDef = 'worker';
	$pages = array(
		'worker' => 'Сотрудники',
		'rekvisit' => 'Реквизиты организации',
		'product' => 'Виды изделий',
		'prihodtype' => 'Виды платежей'
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
		return _norules('Управление сотрудниками');
	return
	'<div id="setup_worker">'.
		'<div class="headName">Управление сотрудниками<a class="add">Новый сотрудник</a></div>'.
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
					($r['admin'] ? '' : '<a href="'.URL.'&p=setup&d=worker&id='.$r['viewer_id'].'" class="rules_set">Настроить права</a>').
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
	if($u['admin'])
		return 'Невозможно изменять права сотрудника <b>'.$u['name'].'</b>.';
//	print_r(workerRulesArray($u['rules']));
	$rule = workerRulesArray($u['rules']);
	return
	'<script type="text/javascript">var RULES_VIEWER_ID='.$viewer_id.';</script>'.
	'<div id="setup_rules">'.
		'<div class="headName">Настройка прав для сотрудника '.$u['name'].'</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">Разрешать вход в приложение:<td>'._check('rules_appenter', '', $rule['RULES_APPENTER']).
		'</table>'.
		'<div class="app-div'.($rule['RULES_APPENTER'] ? '' : ' dn').'">'.
			'<table class="rtab">'.
				'<tr><td class="lab">Управление установками:<td>'._check('rules_setup', '', $rule['RULES_SETUP']).
				'<tr><td class="lab"><td>'.
					'<div class="setup-div'.($rule['RULES_SETUP'] ? '' : ' dn').'">'.
						_check('rules_worker', 'Сотрудники', $rule['RULES_WORKER']).
						_check('rules_rekvisit', 'Реквизиты организации', $rule['RULES_REKVISIT']).
						_check('rules_product', 'Виды изделий', $rule['RULES_PRODUCT']).
						_check('rules_prihodtype', 'Виды платежей', $rule['RULES_PRIHODTYPE']).
					'</div>'.
			'</table>'.
		'</div>'.
	'</div>';
}//setup_worker_rules()

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
					'<th>Требуется<br />заключение<br />договора'.
					'<th>Подвиды'.
					'<th>Кол-во<br />заявок'.
					'<th>';
	foreach($product as $id => $r)
		$send .= '<tr val="'.$id.'">'.
					'<td class="name"><a href="'.URL.'&p=setup&d=product&id='.$id.'">'.$r['name'].'</a>'.
					'<td class="dog">'.($r['dogovor'] ? 'да' : '').
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

function setup_prihodtype() {
	if(!RULES_PRIHODTYPE)
		return _norules('Настройки видов платежей');
	return
	'<div id="setup_prihodtype">'.
		'<div class="headName">Настройки видов платежей<a class="add">Добавить</a></div>'.
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
		return 'Список пуст.';

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
		'<tr><th class="name">Наименование'.
			'<th class="kassa">Возможность<br />внесения<br />в кассу'.
			'<th class="money">Кол-во<br />платежей'.
			'<th class="set">'.
	'</table>'.
	'<dl class="_sort" val="setup_prihodtype">';
	foreach($prihod as $id => $r) {
		$money = $r['money'] ? '<b>'.$r['money'].'</b>' : '';
		$money .= isset($r['del']) ? ' <span class="del" title="В том числе удалённые">('.$r['del'].')</span>' : '';
		$send .='<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="kassa">'.($r['kassa_put'] ? 'да' : '').
					'<td class="money">'.$money.
					'<td class="set">'.
						'<div class="img_edit"></div>'.
						(!$r['money'] && $id > 1 ? '<div class="img_del"></div>' : '').
			'</table>';
	}
	$send .= '</dl>';
	return $send;
}//setup_prihodtype_spisok()