<?php

// ---===! setup !===--- Секция настроек

function setup() {
	$pages = array(
		'my' => 'Мои настройки',
		'worker' => 'Сотрудники',
		'rekvisit' => 'Реквизиты организации',
		'product' => 'Виды изделий',
		'invoice' => 'Счета',
		'expense' => 'Категории расходов',
		'zayavexpense' => 'Расходы по заявке'
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
		'<div class="headName">Пин-код</div>'.
		'<div class="_info">'.
			'<p>Пин-код необходим для дополнительного подтверждения вашей личности, '.
			'если другой пользователь получит доступ к вашей странице ВКонтакте.'.
			'<br />'.
			'<p>Пин-код нужно будет вводить каждом новом входе в приложение, '.
			'а также при отсутсвии действий в программе в течение 1-го часа.'.
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
	$client_id = query_value("SELECT `id` FROM `client` WHERE `worker_id`=".$viewer_id);
	return
	'<script type="text/javascript">var RULES_VIEWER_ID='.$viewer_id.';</script>'.
	'<div id="setup_rules">'.

		'<table class="utab">'.
			'<tr><td>'.$u['photo'].
				'<td><div class="name">'.$u['name'].'</div>'.
					 ($viewer_id < VIEWER_MAX ? '<a href="http://vk.com/id'.$viewer_id.'" class="vklink" target="_blank">Страница VK</a>' : '').
					 '<a href="'.URL.'&p=report&d=salary&id='.$viewer_id.'" class="vklink">Страница з/п</a>'.
	   ($client_id ? '<a href="'.URL.'&p=client&d=info&id='.$client_id.'" class="vklink">Клиентская страница</a>' : '').
		'</table>'.

		'<div class="headName">Общее</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">Фамилия:<td><input type="text" id="last_name" value="'.$u['last_name'].'" />'.
			'<tr><td class="lab">Имя:<td><input type="text" id="first_name" value="'.$u['first_name'].'" />'.
			'<tr><td class="lab">Отчество:<td><input type="text" id="middle_name" value="'.$u['middle_name'].'" />'.
			'<tr><td class="lab">Должность:<td><input type="text" id="post" value="'.$u['post'].'" />'.
			'<tr><td><td><div class="vkButton g-save"><button>Сохранить</button></div>'.
		'</table>'.

	(!$u['admin'] && $u['pin'] ?
		'<div class="headName">Пин-код</div>'.
		'<div class="vkButton pin-clear"><button>Сбросить пин-код</button></div>'
	: '').

	'<div class="headName">Дополнительно</div>'.
	'<table class="rtab">'.
		'<tr><td class="lab">Не отображать<br />в начислениях з/п:<td>'._check('rules_nosalary', '', $rule['RULES_NOSALARY']).
		'<tr><td class="lab">Начислять бонус при<br />отсутствии долга по заявке:<td>'._check('rules_zpzayavauto', '', $rule['RULES_ZPZAYAVAUTO']).
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
						_check('rules_invoice', 'Счета', $rule['RULES_INVOICE']).
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
	if(!RULES_INVOICE)
		return _norules('Настройки счетов или видов платежей');
	return
	'<div id="setup_invoice">'.
		'<div class="headName">Управление счетами<a class="add">Новый счёт</a></div>'.
		'<div class="spisok">'.setup_invoice_spisok().'</div>'.
	'</div>';
}//setup_invoice()
function setup_invoice_spisok() {
	$sql = "SELECT * FROM `invoice` WHERE  !`deleted` ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return 'Список пуст.';

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
		'<tr><th>Наименование'.
			'<th>Подтверждение'.
			'<th>Видимость<br />для сотрудников'.
			'<th>';
	foreach($spisok as $id => $r)
		$send .=
		'<tr val="'.$id.'">'.
			'<td class="name">'.
				'<div>'.$r['name'].'</div>'.
				'<pre>'.$r['about'].'</pre>'.
			'<td class="confirm">'.
				($r['confirm_income'] ? 'поступления на счёт' : '').
				($r['confirm_transfer'] ? ($r['confirm_income'] ? ',<br />' : '').'переводы' : '').
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
