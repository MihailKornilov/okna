<?php
require_once('config.php');
require_once(VKPATH.'/vk_ajax.php');


switch(@$_POST['op']) {
	case 'cache_clear':
		if(!SA)
			jsonError();
		$sql = "SELECT `viewer_id` FROM `vk_user` WHERE `worker`=1";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			xcache_unset(CACHE_PREFIX.'viewer_'.$r['viewer_id']);
		query("UPDATE `vk_user` SET `rules`='".implode(',', array_keys(rulesList()))."' WHERE `admin`=1");
		query("UPDATE `setup_global` SET `version`=`version`+1");
		_cacheClear();
		jsonSuccess();
		break;

	case 'attach_upload':
		/*
			Прикрепление файлов
			1 - успешно
			2 - некорректный type
			3 - неверный формат
			4 - загрузить не удалось
			5 - несуществующая заявка
		*/
		if(!preg_match(REGEXP_WORD, $_POST['type'])) {
			setcookie('_attached', 2, time() + 3600, '/');
			exit;
		}
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || !$_POST['zayav_id']) {
			setcookie('_attached', 5, time() + 3600, '/');
			exit;
		}
		$type = htmlspecialchars(trim($_POST['type']));
		$zayav_id = intval($_POST['zayav_id']);

		//Проверка наличия заявки
		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$zayav_id;
		if(!$zayav = mysql_fetch_assoc(query($sql))) {
			setcookie('_attached', 5, time() + 3600, '/');
			exit;
		}

		$f = $_FILES['f1']['name'] ? $_FILES['f1'] : $_FILES['f2'];
		switch($f['type']) {
			case 'application/rtf': break;
			case 'application/msword': break;
			case 'application/vnd.ms-excel': break;
			default: setcookie('_attached', 3, time() + 3600, '/'); exit;
		}
		$dir = PATH.'files/'.$type.'/'.$type.$zayav_id;
		if(!is_dir($dir))
			mkdir($dir, 0777, true);
		$fname = time().'_'.translit($f["name"]);
		if(move_uploaded_file($f['tmp_name'], $dir.'/'.$fname)) {
			$name = htmlspecialchars(trim($f["name"]));
			$link = SITE.'/files/'.$type.'/'.$type.$zayav_id.'/'.$fname;
			$sql = "INSERT INTO `attach` (
						`type`,
						`zayav_id`,
						`name`,
						`link`,
						`viewer_id_add`
					) VALUES (
						'".$type."',
						".$zayav_id.",
						'".$name."',
						'".$link."',
						".VIEWER_ID."
					)";
			query($sql);

			history_insert(array(
				'type' => 27,
				'zayav_id' => $zayav_id,
				'client_id' => $zayav['client_id'],
				'value' => '<a href="'.$link.'">'.$name.'</a>'
			));
			setcookie('_attached', 1, time() + 3600, '/');
			exit;
		}
		setcookie('_attached', 4, time() + 3600, '/');
		exit;
	case 'attach_get':
		if(!preg_match(REGEXP_WORD, $_POST['type']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
			jsonError();
		$type = htmlspecialchars(trim($_POST['type']));
		$zayav_id = intval($_POST['zayav_id']);
		$send = array(
			'files' => utf8(_attach_files($type, $zayav_id)),
			'form' => _attach_form($type, $zayav_id)
		);
		jsonSuccess($send);
		break;
	case 'attach_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']) || !$_POST['id'])
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT * FROM `attach` WHERE `deleted`=0 AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();
		$sql = "UPDATE `attach` SET `deleted`=1 WHERE `id`=".$id;
		query($sql);

		history_insert(array(
			'type' => 28,
			'zayav_id' => $r['zayav_id'],
			'client_id' => query_value("SELECT `client_id` FROM `zayav` WHERE `id`=".$r['zayav_id']),
			'value' => '<a href="'.$r['link'].'">'.$r['name'].'</a>'
		));

		jsonSuccess();
		break;

	case 'oplata_add':
		$v = array(
			'from' => trim($_POST['from']),
			'prim' => win1251(htmlspecialchars(trim($_POST['prim'])))
		);
		if(!preg_match(REGEXP_NUMERIC, $_POST['type']) || $_POST['type'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) || $_POST['sum'] == 0)
			jsonError();
		if(preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
			$v['zayav_id'] = intval($_POST['zayav_id']);
		if(preg_match(REGEXP_NUMERIC, $_POST['client_id']))
			$v['client_id'] = intval($_POST['client_id']);

		$v['type'] = intval($_POST['type']);
		$v['sum'] = intval($_POST['sum']);

		$send['html'] = utf8(income_insert($v));
		if(empty($send))
			jsonError();
		if($v['from'] == 'client') {
			$sql = "SELECT * FROM `client` WHERE `id`=".$v['client_id'];
			$r = mysql_fetch_assoc(query($sql));
			$send['balans'] = utf8(clientInfoGet($r));
		}
		jsonSuccess($send);
		break;
	case 'oplata_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT *
				FROM `money`
				WHERE `deleted`=0
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();
		if($r['dogovor_id'])
			jsonError();

		$sql = "UPDATE `money` SET
					`deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($r['client_id']);

		history_insert(array(
			'type' => 11,
			'zayav_id' => $r['zayav_id'],
			'client_id' => $r['client_id'],
			'value' => $r['sum'],
			'value1' => $r['prim'],
			'value2' => $r['income_id']
		));

		jsonSuccess();
		break;
	case 'oplata_rest':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT *
				FROM `money`
				WHERE `deleted`=1
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `money` SET
					`deleted`=0,
					`viewer_id_del`=0,
					`dtime_del`='0000-00-00 00:00:00'
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($r['client_id']);

		history_insert(array(
			'type' => 12,
			'zayav_id' => $r['zayav_id'],
			'client_id' => $r['client_id'],
			'value' => $r['sum'],
			'value1' => $r['prim'],
			'value2' => $r['income_id']
		));

		jsonSuccess();
		break;

	case 'client_sel':
		if(!preg_match(REGEXP_WORDFIND, win1251($_POST['val'])))
			$_POST['val'] = '';
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
			$_POST['client_id'] = 0;
		$val = win1251($_POST['val']);
		$client_id = intval($_POST['client_id']);
		$sql = "SELECT *
				FROM `client`
				WHERE `deleted`=0".
					(!empty($val) ? " AND (`fio` LIKE '%".$val."%' OR `telefon` LIKE '%".$val."%' OR `adres` LIKE '%".$val."%')" : '').
					($client_id > 0 ? " AND `id`<=".$client_id : '')."
				ORDER BY `id` DESC
				LIMIT 50";
		$q = query($sql);
		$send['spisok'] = array();
		while($r = mysql_fetch_assoc($q)) {
			$unit = array(
				'uid' => $r['id'],
				'title' => utf8($r['fio']),
				'adres' => utf8($r['adres'])
			);
			$pole2 = array();
			if($r['telefon'])
				$pole2[] = $r['telefon'];
			if($r['adres'])
				$pole2[] = $r['adres'];
			if(!empty($pole2))
				$unit['content'] = utf8($r['fio'].'<div class="pole2">'.implode('<br />', $pole2).'</div>');
			$send['spisok'][] = $unit;
		}
		jsonSuccess($send);
		break;
	case 'client_add':
		$fio = win1251(htmlspecialchars(trim($_POST['fio'])));
		$telefon = win1251(htmlspecialchars(trim($_POST['telefon'])));
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$pasp_seria = win1251(htmlspecialchars(trim($_POST['pasp_seria'])));
		$pasp_nomer = win1251(htmlspecialchars(trim($_POST['pasp_nomer'])));
		$pasp_adres = win1251(htmlspecialchars(trim($_POST['pasp_adres'])));
		$pasp_ovd = win1251(htmlspecialchars(trim($_POST['pasp_ovd'])));
		$pasp_data = win1251(htmlspecialchars(trim($_POST['pasp_data'])));
		if(empty($fio))
			jsonError();
		$sql = "INSERT INTO `client` (
					`fio`,
					`telefon`,
					`adres`,
					`pasp_seria`,
					`pasp_nomer`,
					`pasp_adres`,
					`pasp_ovd`,
					`pasp_data`,
					`viewer_id_add`
				) VALUES (
					'".addslashes($fio)."',
					'".addslashes($telefon)."',
					'".addslashes($adres)."',
					'".addslashes($pasp_seria)."',
					'".addslashes($pasp_nomer)."',
					'".addslashes($pasp_adres)."',
					'".addslashes($pasp_ovd)."',
					'".addslashes($pasp_data)."',
					".VIEWER_ID."
				)";
		query($sql);
		$send = array(
			'uid' => mysql_insert_id(),
			'title' => $fio
		);
		history_insert(array(
			'type' => 1,
			'client_id' => $send['uid']
		));
		jsonSuccess($send);
		break;
	case 'client_spisok_load':
		$filter = clientFilter($_POST);
		$send = client_data(1, $filter);
		$send['all'] = utf8(client_count($send['all'], $filter['dolg']));
		$send['spisok'] = utf8($send['spisok']);
		jsonSuccess($send);
		break;
	case 'client_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$send = client_data(intval($_POST['page']), clientFilter($_POST));
		$send['spisok'] = utf8($send['spisok']);
		jsonSuccess($send);
		break;
	case 'client_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) || $_POST['client_id'] == 0)
			jsonError();
		$client_id = intval($_POST['client_id']);
		$fio = win1251(htmlspecialchars(trim($_POST['fio'])));
		$telefon = win1251(htmlspecialchars(trim($_POST['telefon'])));
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$pasp_seria = win1251(htmlspecialchars(trim($_POST['pasp_seria'])));
		$pasp_nomer = win1251(htmlspecialchars(trim($_POST['pasp_nomer'])));
		$pasp_adres = win1251(htmlspecialchars(trim($_POST['pasp_adres'])));
		$pasp_ovd = win1251(htmlspecialchars(trim($_POST['pasp_ovd'])));
		$pasp_data = win1251(htmlspecialchars(trim($_POST['pasp_data'])));
		if(empty($fio))
			jsonError();
		$sql = "SELECT * FROM `client` WHERE `deleted`=0 AND `id`=".$client_id;
		if(!$client = mysql_fetch_assoc(query($sql)))
			jsonError();
		query("UPDATE `client` SET
				`fio`='".$fio."',
				`telefon`='".$telefon."',
				`adres`='".$adres."',
				`pasp_seria`='".$pasp_seria."',
				`pasp_nomer`='".$pasp_nomer."',
				`pasp_adres`='".$pasp_adres."',
				`pasp_ovd`='".$pasp_ovd."',
				`pasp_data`='".$pasp_data."'
			   WHERE `id`=".$client_id);
		$changes = '';
		if($client['fio'] != $fio)
			$changes .= '<tr><th>Фио:<td>'.$client['fio'].'<td>»<td>'.$fio;
		if($client['telefon'] != $telefon)
			$changes .= '<tr><th>Телефон.:<td>'.$client['telefon'].'<td>»<td>'.$telefon;
		if($client['adres'] != $adres)
			$changes .= '<tr><th>Адрес:<td>'.$client['adres'].'<td>»<td>'.$adres;
		if($client['pasp_seria'] != $pasp_seria)
			$changes .= '<tr><th>Паспорт серия:<td>'.$client['pasp_seria'].'<td>»<td>'.$pasp_seria;
		if($client['pasp_nomer'] != $pasp_nomer)
			$changes .= '<tr><th>Паспорт номер:<td>'.$client['pasp_nomer'].'<td>»<td>'.$pasp_nomer;
		if($client['pasp_adres'] != $pasp_adres)
			$changes .= '<tr><th>Паспорт прописка:<td>'.$client['pasp_adres'].'<td>»<td>'.$pasp_adres;
		if($client['pasp_ovd'] != $pasp_ovd)
			$changes .= '<tr><th>Паспорт кем выдан:<td>'.$client['pasp_ovd'].'<td>»<td>'.$pasp_ovd;
		if($client['pasp_data'] != $pasp_data)
			$changes .= '<tr><th>Паспорт когда выдан:<td>'.$client['pasp_data'].'<td>»<td>'.$pasp_data;
		if($changes)
			history_insert(array(
				'type' => 2,
				'client_id' => $client_id,
				'value' => '<table>'.$changes.'</table>'
			));
		clientBalansUpdate($client_id);
		$send = array(
			'id' => $client_id,
			'fio' => $fio,
			'telefon' => $telefon,
			'adres' => $adres,
			'pasp_seria' => $pasp_seria,
			'pasp_nomer' => $pasp_nomer,
			'pasp_adres' => $pasp_adres,
			'pasp_ovd' => $pasp_ovd,
			'pasp_data' => $pasp_data,

			'balans' => clientBalansUpdate($client_id),
			'viewer_id_add' => $client['viewer_id_add'],
			'dtime_add' => $client['dtime_add']
		);
		$send['html'] = clientInfoGet($send);
		foreach($send as $i => $v)
			$send[$i] = utf8($v);
		jsonSuccess($send);
		break;
	case 'client_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$client_id = intval($_POST['id']);
		if(!query_value("SELECT COUNT(`id`) FROM `client` WHERE `deleted`=0 AND `id`=".$client_id))
			jsonError();
		query("UPDATE `client` SET `deleted`=1 WHERE `id`=".$client_id);
		query("UPDATE `zayav` SET `deleted`=1 WHERE `client_id`=".$client_id);
		query("UPDATE `money` SET `deleted`=1,`viewer_id_del`=".VIEWER_ID.",`dtime_del`=CURRENT_TIMESTAMP WHERE `client_id`=".$client_id);
		query("UPDATE `accrual` SET `deleted`=1,`viewer_id_del`=".VIEWER_ID.",`dtime_del`=CURRENT_TIMESTAMP WHERE `client_id`=".$client_id);
		history_insert(array(
			'type' => 3,
			'client_id' => $client_id
		));
		jsonSuccess();
		break;
	case 'client_zayav_load':
		$data = zayav_data(1, zayavfilter($_POST), 10);
		$send['all'] = utf8(zayav_count($data['all'], 0));
		$send['html'] = utf8(zayav_spisok($data));
		jsonSuccess($send);
		break;
	case 'client_zayav_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$send['html'] = utf8(zayav_spisok(zayav_data(intval($_POST['page']), zayavfilter($_POST), 10)));
		jsonSuccess($send);
		break;

	case 'zakaz_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) || $_POST['client_id'] == 0)
			jsonError();

		$client_id = intval($_POST['client_id']);
		$zakaz_txt = win1251(htmlspecialchars(trim($_POST['zakaz_txt'])));
		$comm = win1251(htmlspecialchars(trim($_POST['comm'])));

		$product = zayav_product_test($_POST['product']);
		if(!$product && empty($zakaz_txt))
			jsonError();

		$sql = "INSERT INTO `zayav` (
					`client_id`,
					`zakaz_txt`,
					`zakaz_status`,
					`viewer_id_add`
				) VALUES (
					".$client_id.",
					'".addslashes($zakaz_txt)."',
					1,
					".VIEWER_ID."
				)";
		query($sql);
		$send['id'] = mysql_insert_id();

		if($product)
			foreach($product as $r) {
				$sql = "INSERT INTO `zayav_product` (
						`zayav_id`,
						`product_id`,
						`product_sub_id`,
						`count`
					) VALUES (
						".$send['id'].",
						".$r[0].",
						".$r[1].",
						".$r[2]."
					)";
				query($sql);
			}

		_vkCommentAdd('zayav', $send['id'], $comm);

		history_insert(array(
			'type' => 23,
			'client_id' => $client_id,
			'zayav_id' => $send['id']
		));
		jsonSuccess($send);
		break;
	case 'zakaz_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$zakaz_txt = win1251(htmlspecialchars(trim($_POST['zakaz_txt'])));
		$nomer_vg = win1251(htmlspecialchars(trim($_POST['nomer_vg'])));
		$nomer_g = win1251(htmlspecialchars(trim($_POST['nomer_g'])));
		$nomer_d = win1251(htmlspecialchars(trim($_POST['nomer_d'])));
		$product = zayav_product_test($_POST['product']);
		if(!$product && empty($zakaz_txt))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `zakaz_status`>0 AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `zayav`
		        SET `zakaz_txt`='".addslashes($zakaz_txt)."',
		            `nomer_vg`='".addslashes($nomer_vg)."',
		            `nomer_g`='".addslashes($nomer_g)."',
		            `nomer_d`='".addslashes($nomer_d)."'
				WHERE `id`=".$zayav_id;
		query($sql);

		$changes = '';
		$productOld = zayav_product_spisok($zayav_id, 'array');
		if($product != $productOld || $zayav['zakaz_txt'] != $zakaz_txt) {
			$sql = "DELETE FROM `zayav_product` WHERE `zayav_id`=".$zayav_id;
			query($sql);
			if($product)
				foreach($product as $r) {
					$sql = "INSERT INTO `zayav_product` (
						`zayav_id`,
						`product_id`,
						`product_sub_id`,
						`count`
					) VALUES (
						".$zayav_id.",
						".$r[0].",
						".$r[1].",
						".$r[2]."
					)";
					query($sql);
				}
			$old = array();
			if($productOld)
				foreach($productOld as $r)
					$old[] = _product($r[0]).($r[1] ? ' '._productSub($r[1]) : '').': '.$r[2].' шт.';
			$new = array();
			if($product)
				foreach($product as $r)
					$new[] = _product($r[0]).($r[1] ? ' '._productSub($r[1]) : '').': '.$r[2].' шт.';
			$changes .= '<tr><th>Изделия:<td>'.implode('<br />', $old).($zayav['zakaz_txt'] ? '<br />' : '').$zayav['zakaz_txt'].
							'<td>»'.
							'<td>'.implode('<br />', $new).($zakaz_txt ? '<br />' : '').$zakaz_txt;
		}
		if($zayav['nomer_vg'] != $nomer_vg)
			$changes .= '<tr><th>Номер ВГ:<td>'.$zayav['nomer_vg'].'<td>»<td>'.$nomer_vg;
		if($zayav['nomer_g'] != $nomer_g)
			$changes .= '<tr><th>Номер Ж:<td>'.$zayav['nomer_g'].'<td>»<td>'.$nomer_g;
		if($zayav['nomer_d'] != $nomer_d)
			$changes .= '<tr><th>Номер Д:<td>'.$zayav['nomer_d'].'<td>»<td>'.$nomer_d;
		if($changes)
			history_insert(array(
				'type' => 24,
				'zayav_id' => $zayav_id,
				'value1' => '<table>'.$changes.'</table>'
			));
		jsonSuccess();
		break;
	case 'zakaz_status':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']) || $_POST['status'] == 0)
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$status = intval($_POST['status']);

		$sql = "SELECT *
				FROM `zayav`
				WHERE `id`=".$zayav_id."
				  AND `zakaz_status`>0
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($zayav['zakaz_status'] != $status) {
			$sql = "UPDATE `zayav` SET `zakaz_status`=".$status." WHERE `id`=".$zayav_id;
			query($sql);
			history_insert(array(
				'type' => 25,
				'client_id' => $zayav['client_id'],
				'zayav_id' => $zayav_id,
				'value' => $zayav['zakaz_status'],
				'value1' => $status
			));
		}

		jsonSuccess();
		break;
	case 'zakaz_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$data = zakaz_spisok(intval($_POST['page']), zakazFilter($_POST));
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'zamer_table_get':
		if(!empty($_POST['mon']) && preg_match(REGEXP_DATE, $_POST['mon'].'-01'))
			$send['html'] = utf8(zamer_table($_POST['mon']));
		else {
			if(!preg_match(REGEXP_NUMERIC, $_POST['val']))
				jsonError();
			$zayav_id = intval($_POST['val']);
			$mon = false;
			if($zayav_id)
				$mon = query_value("
					SELECT DATE_FORMAT(`zamer_dtime`, '%Y-%m')
					FROM `zayav`
				    WHERE `deleted`=0
				      AND `zamer_status`=1
				      AND `id`=".$zayav_id);
			$send['html'] = utf8(zamer_table($mon, $zayav_id));
		}
		jsonSuccess($send);
		break;
	case 'zamer_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) || $_POST['client_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_DATE, $_POST['zamer_day']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zamer_hour']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zamer_min']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zamer_duration']) || $_POST['zamer_duration'] == 0)
			jsonError();

		$product = zayav_product_test($_POST['product']);
		if(!$product)
			jsonError();

		$client_id = intval($_POST['client_id']);
		$zamer_dtime = $_POST['zamer_day'].' '.$_POST['zamer_hour'].':'.$_POST['zamer_min'].':00';
		$zamer_duration = intval($_POST['zamer_duration']);
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$comm = win1251(htmlspecialchars(trim($_POST['comm'])));

		if(empty($adres))
			jsonError();

		if(_zamerDataTest($zamer_dtime, $zamer_duration))
			jsonError('Время замера накладывается на другие замеры.');

		$sql = "INSERT INTO `zayav` (
					`client_id`,
					`zamer_status`,
					`zamer_dtime`,
					`zamer_duration`,
					`adres`,
					`viewer_id_add`
				) VALUES (
					".$client_id.",
					1,
					'".$zamer_dtime."',
					".$zamer_duration.",
					'".$adres."',
					".VIEWER_ID."
				)";
		query($sql);
		$send['id'] = mysql_insert_id();

		foreach($product as $r) {
			$sql = "INSERT INTO `zayav_product` (
						`zayav_id`,
						`product_id`,
						`product_sub_id`,
						`count`
					) VALUES (
						".$send['id'].",
						".$r[0].",
						".$r[1].",
						".$r[2]."
					)";
			query($sql);
		}

		_vkCommentAdd('zayav', $send['id'], $comm);

		history_insert(array(
			'type' => 4,
			'client_id' => $client_id,
			'zayav_id' => $send['id']
		));
		jsonSuccess($send);
		break;
	case 'zamer_info_get':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);

		$sql = "SELECT *
				FROM `zayav`
				WHERE `id`=".$zayav_id."
				  AND (`zamer_status`=1 OR `zamer_status`=3)
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$ex = explode(' ', $zayav['zamer_dtime']);
		$time = explode(':', $ex[1]);
		$send['day'] = $ex[0];
		$send['hour'] = intval($time[0]);
		$send['min'] = intval($time[1]);
		$send['dur'] = $zayav['zamer_duration'];
		jsonSuccess($send);
		break;
	case 'zamer_status':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']) || $_POST['status'] == 0)
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$status = intval($_POST['status']);
		$prim = win1251(htmlspecialchars(trim($_POST['prim'])));

		$sql = "SELECT *
				FROM `zayav`
				WHERE `id`=".$zayav_id."
				  AND (`zamer_status`=1 OR `zamer_status`=3)
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		switch($status) {
			case 1:
				if(!preg_match(REGEXP_DATE, $_POST['zamer_day']))
					jsonError();
				if(!preg_match(REGEXP_NUMERIC, $_POST['zamer_hour']))
					jsonError();
				if(!preg_match(REGEXP_NUMERIC, $_POST['zamer_min']))
					jsonError();
				if(!preg_match(REGEXP_NUMERIC, $_POST['zamer_duration']) || $_POST['zamer_duration'] == 0)
					jsonError();
				$zamer_dtime = $_POST['zamer_day'].' '.
							  ($_POST['zamer_hour'] < 10 ? '0' : '').$_POST['zamer_hour'].':'.
							  ($_POST['zamer_min'] < 10 ? '0' : '').$_POST['zamer_min'].':00';
				$zamer_duration = intval($_POST['zamer_duration']);

				if(_zamerDataTest($zamer_dtime, $zamer_duration, $zayav_id))
					jsonError('Время замера накладывается на другие замеры.');

				$sql = "UPDATE `zayav`
				        SET `zamer_status`=1,
				            `zamer_dtime`='".$zamer_dtime."',
				            `zamer_duration`=".$zamer_duration."
				        WHERE `id`=".$zayav_id;
				query($sql);
				if($zayav['zamer_status'] == 3)
					history_insert(array(
						'type' => 18,
						'client_id' => $zayav['client_id'],
						'zayav_id' => $zayav_id
					));
				if($zayav['zamer_dtime'] != $zamer_dtime || $zayav['zamer_duration'] != $zamer_duration)
					history_insert(array(
						'type' => 15,
						'client_id' => $zayav['client_id'],
						'zayav_id' => $zayav_id,
						'value1' => '<table>'.
										'<tr><td>'.FullDataTime($zayav['zamer_dtime']).', '._zamerDuration($zayav['zamer_duration']).
											'<td>»'.
											'<td>'.FullDataTime($zamer_dtime).', '._zamerDuration($zamer_duration).
									'</table>'
					));
				break;
			case 2:
				if($zayav['zamer_status'] != 2) {
					$sql = "UPDATE `zayav` SET `zamer_status`=2,`set_status`=1,`dogovor_require`=1 WHERE `id`=".$zayav_id;
					query($sql);
					history_insert(array(
						'type' => 16,
						'client_id' => $zayav['client_id'],
						'zayav_id' => $zayav_id
					));
				}
				break;
			case 3:
				if($zayav['zamer_status'] != 3) {
					$sql = "UPDATE `zayav` SET `zamer_status`=3 WHERE `id`=".$zayav_id;
					query($sql);
					history_insert(array(
						'type' => 17,
						'client_id' => $zayav['client_id'],
						'zayav_id' => $zayav_id
					));
				}
				break;
			default:
				jsonError();
		}

		_vkCommentAdd('zayav', $zayav_id, $prim);

		jsonSuccess();
		break;
	case 'zamer_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();
		if(!preg_match(REGEXP_DATE, $_POST['zamer_day']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zamer_hour']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zamer_min']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zamer_duration']) || !$_POST['zamer_duration'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$zamer_dtime = $_POST['zamer_day'].' '.
					   ($_POST['zamer_hour'] < 10 ? '0' : '').$_POST['zamer_hour'].':'.
					   ($_POST['zamer_min'] < 10 ? '0' : '').$_POST['zamer_min'].':00';
		$zamer_duration = intval($_POST['zamer_duration']);
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$product = zayav_product_test($_POST['product']);
		if(!$product)
			jsonError();
		if(empty($adres))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(_zamerDataTest($zamer_dtime, $zamer_duration, $zayav_id))
			jsonError('Время замера накладывается на другие замеры.');

		$sql = "UPDATE `zayav`
		        SET `adres`='".addslashes($adres)."',
					`zamer_dtime`='".$zamer_dtime."',
					`zamer_duration`=".$zamer_duration."
				WHERE `id`=".$zayav_id;
		query($sql);

		$changes = '';
		$productOld = zayav_product_spisok($zayav_id, 'array');
		if($product != $productOld) {
			$sql = "DELETE FROM `zayav_product` WHERE `zayav_id`=".$zayav_id;
			query($sql);
			foreach($product as $r) {
				$sql = "INSERT INTO `zayav_product` (
						`zayav_id`,
						`product_id`,
						`product_sub_id`,
						`count`
					) VALUES (
						".$zayav_id.",
						".$r[0].",
						".$r[1].",
						".$r[2]."
					)";
				query($sql);
			}
			$old = array();
			foreach($productOld as $r)
				$old[] = _product($r[0]).($r[1] ? ' '._productSub($r[1]) : '').': '.$r[2].' шт.';
			$new = array();
			foreach($product as $r)
				$new[] = _product($r[0]).($r[1] ? ' '._productSub($r[1]) : '').': '.$r[2].' шт.';
			$changes .= '<tr><th>Изделия:<td>'.implode('<br />', $old).'<td>»<td>'.implode('<br />', $new);
		}
		if($zayav['adres'] != $adres)
			$changes .= '<tr><th>Адрес замера:<td>'.$zayav['adres'].'<td>»<td>'.$adres;
		if($zayav['zamer_dtime'] != $zamer_dtime || $zayav['zamer_duration'] != $zamer_duration)
			$changes .= '<tr><th>Время замера'.
							'<td>'.FullDataTime($zayav['zamer_dtime']).', '._zamerDuration($zayav['zamer_duration']).
							'<td>»'.
							'<td>'.FullDataTime($zamer_dtime).', '._zamerDuration($zamer_duration);
		if($changes)
			history_insert(array(
				'type' => 5,
				'zayav_id' => $zayav_id,
				'value1' => '<table>'.$changes.'</table>'
			));
		jsonSuccess();
		break;
	case 'zamer_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$data = zamer_spisok(intval($_POST['page']), zamerFilter($_POST));
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'dog_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$product = zayav_product_test($_POST['product']);
		if(!$product)
			jsonError();
		if(empty($adres))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `dogovor_id`=0 AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `zayav`
		        SET `adres`='".addslashes($adres)."'
				WHERE `id`=".$zayav_id;
		query($sql);

		$changes = '';
		$productOld = zayav_product_spisok($zayav_id, 'array');
		if($product != $productOld) {
			$sql = "DELETE FROM `zayav_product` WHERE `zayav_id`=".$zayav_id;
			query($sql);
			foreach($product as $r) {
				$sql = "INSERT INTO `zayav_product` (
						`zayav_id`,
						`product_id`,
						`product_sub_id`,
						`count`
					) VALUES (
						".$zayav_id.",
						".$r[0].",
						".$r[1].",
						".$r[2]."
					)";
				query($sql);
			}
			$old = array();
			foreach($productOld as $r)
				$old[] = _product($r[0]).($r[1] ? ' '._productSub($r[1]) : '').': '.$r[2].' шт.';
			$new = array();
			foreach($product as $r)
				$new[] = _product($r[0]).($r[1] ? ' '._productSub($r[1]) : '').': '.$r[2].' шт.';
			$changes .= '<tr><th>Изделия:<td>'.implode('<br />', $old).'<td>»<td>'.implode('<br />', $new);
		}
		if($zayav['adres'] != $adres)
			$changes .= '<tr><th>Адрес установки:<td>'.$zayav['adres'].'<td>»<td>'.$adres;
		if($changes)
			history_insert(array(
				'type' => 22,
				'zayav_id' => $zayav_id,
				'value' => '<table>'.$changes.'</table>'
			));
		jsonSuccess();
		break;
	case 'dog_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$data = dogovor_spisok(intval($_POST['page']), dogovorFilter($_POST));
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'set_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) || $_POST['client_id'] == 0)
			jsonError();
		$product = zayav_product_test($_POST['product']);
		if(!$product)
			jsonError();

		$client_id = intval($_POST['client_id']);
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$comm = win1251(htmlspecialchars(trim($_POST['comm'])));

		if(empty($adres))
			jsonError();

		$sql = "INSERT INTO `zayav` (
					`client_id`,
					`set_status`,
					`adres`,
					`viewer_id_add`
				) VALUES (
					".$client_id.",
					1,
					'".$adres."',
					".VIEWER_ID."
				)";
		query($sql);
		$send['id'] = mysql_insert_id();

		foreach($product as $r) {
			$sql = "INSERT INTO `zayav_product` (
						`zayav_id`,
						`product_id`,
						`product_sub_id`,
						`count`
					) VALUES (
						".$send['id'].",
						".$r[0].",
						".$r[1].",
						".$r[2]."
					)";
			query($sql);
		}

		_vkCommentAdd('zayav', $send['id'], $comm);

		history_insert(array(
			'type' => 21,
			'client_id' => $client_id,
			'zayav_id' => $send['id']
		));
		jsonSuccess($send);
		break;
	case 'set_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$nomer_vg = win1251(htmlspecialchars(trim($_POST['nomer_vg'])));
		$nomer_g = win1251(htmlspecialchars(trim($_POST['nomer_g'])));
		$nomer_d = win1251(htmlspecialchars(trim($_POST['nomer_d'])));
		$product = zayav_product_test($_POST['product']);
		if(!$product)
			jsonError();
		if(empty($adres))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `set_status`>0 AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `zayav`
		        SET `adres`='".addslashes($adres)."',
		            `nomer_vg`='".addslashes($nomer_vg)."',
		            `nomer_g`='".addslashes($nomer_g)."',
		            `nomer_d`='".addslashes($nomer_d)."'
				WHERE `id`=".$zayav_id;
		query($sql);

		$changes = '';
		$productOld = zayav_product_spisok($zayav_id, 'array');
		if($product != $productOld) {
			$sql = "DELETE FROM `zayav_product` WHERE `zayav_id`=".$zayav_id;
			query($sql);
			foreach($product as $r) {
				$sql = "INSERT INTO `zayav_product` (
						`zayav_id`,
						`product_id`,
						`product_sub_id`,
						`count`
					) VALUES (
						".$zayav_id.",
						".$r[0].",
						".$r[1].",
						".$r[2]."
					)";
				query($sql);
			}
			$old = array();
			foreach($productOld as $r)
				$old[] = _product($r[0]).($r[1] ? ' '._productSub($r[1]) : '').': '.$r[2].' шт.';
			$new = array();
			foreach($product as $r)
				$new[] = _product($r[0]).($r[1] ? ' '._productSub($r[1]) : '').': '.$r[2].' шт.';
			$changes .= '<tr><th>Изделия:<td>'.implode('<br />', $old).'<td>»<td>'.implode('<br />', $new);
		}
		if($zayav['adres'] != $adres)
			$changes .= '<tr><th>Адрес установки:<td>'.$zayav['adres'].'<td>»<td>'.$adres;
		if($zayav['nomer_vg'] != $nomer_vg)
			$changes .= '<tr><th>Номер ВГ:<td>'.$zayav['nomer_vg'].'<td>»<td>'.$nomer_vg;
		if($zayav['nomer_g'] != $nomer_g)
			$changes .= '<tr><th>Номер Ж:<td>'.$zayav['nomer_g'].'<td>»<td>'.$nomer_g;
		if($zayav['nomer_d'] != $nomer_d)
			$changes .= '<tr><th>Номер Д:<td>'.$zayav['nomer_d'].'<td>»<td>'.$nomer_d;
		if($changes)
			history_insert(array(
				'type' => 22,
				'zayav_id' => $zayav_id,
				'value' => '<table>'.$changes.'</table>'
			));
		jsonSuccess();
		break;
	case 'set_status':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']) || $_POST['status'] == 0)
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$status = intval($_POST['status']);

		$sql = "SELECT *
				FROM `zayav`
				WHERE `id`=".$zayav_id."
				  AND `set_status`>0
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($zayav['set_status'] != $status) {
			$sql = "UPDATE `zayav` SET `set_status`=".$status." WHERE `id`=".$zayav_id;
			query($sql);
			history_insert(array(
				'type' => 26,
				'client_id' => $zayav['client_id'],
				'zayav_id' => $zayav_id,
				'value' => $zayav['set_status'],
				'value1' => $status
			));
		}

		jsonSuccess();
		break;
	case 'set_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$data = set_spisok(intval($_POST['page']), setFilter($_POST));
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'zayav_rashod_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$rashod = zayav_rashod_test($_POST['rashod']);
		if($rashod === false)
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$rashodOld = zayav_rashod_spisok($zayav_id, 'array');
		if($rashod != $rashodOld) {
			$old = zayav_rashod_spisok($zayav_id);
			$sql = "DELETE FROM `zayav_rashod` WHERE `zayav_id`=".$zayav_id;
			query($sql);
			foreach($rashod as $r) {
				$sql = "INSERT INTO `zayav_rashod` (
							`zayav_id`,
							`category_id`,
							`txt`,
							`worker_id`,
							`sum`
						) VALUES (
							".$zayav_id.",
							".$r[0].",
							'".(_zayavRashod($r[0], 'txt') ? addslashes($r[1]) : '')."',
							".(_zayavRashod($r[0], 'worker') ? intval($r[1]) : 0).",
							".$r[2]."
						)";
				query($sql);
			}
			$changes = '<tr><td>'.$old.'<td>»<td>'.zayav_rashod_spisok($zayav_id);
			history_insert(array(
				'type' => 29,
				'zayav_id' => $zayav_id,
				'value' => '<table>'.$changes.'</table>'
			));
		}
		$send['html'] = utf8(zayav_rashod_spisok($zayav_id));
		jsonSuccess($send);
		break;
	case 'zayav_spisok_load':
		$_POST['find'] = win1251($_POST['find']);
		$data = zayav_data(1, zayavfilter($_POST));
		$send['all'] = utf8(zayav_count($data['all']));
		$send['html'] = utf8(zayav_spisok($data));
		jsonSuccess($send);
		break;
	case 'zayav_delete':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();
		$zayav_id = intval($_POST['zayav_id']);
		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		query("UPDATE `zayav` SET `deleted`=1 WHERE `id`=".$zayav_id);
		query("UPDATE `zayav_dogovor` SET `deleted`=1 WHERE `zayav_id`=".$zayav_id);
		query("UPDATE `accrual` SET `deleted`=1,`viewer_id_del`=".VIEWER_ID.",`dtime_del`=CURRENT_TIMESTAMP WHERE `zayav_id`=".$zayav_id);
		query("UPDATE `money` SET `deleted`=1,`viewer_id_del`=".VIEWER_ID.",`dtime_del`=CURRENT_TIMESTAMP WHERE `zayav_id`=".$zayav_id);

		clientBalansUpdate($zayav['client_id']);

		history_insert(array(
			'type' => 6,
			'client_id' => $zayav['client_id'],
			'zayav_id' => $zayav_id
		));

		$send['client_id'] = $zayav['client_id'];
		jsonSuccess($send);
		break;
	case 'accrual_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) || $_POST['sum'] == 0)
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$sum = intval($_POST['sum']);
		$prim = win1251(htmlspecialchars(trim($_POST['prim'])));

		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$zayav_id;
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "INSERT INTO `accrual` (
					`zayav_id`,
					`client_id`,
					`sum`,
					`prim`,
					`viewer_id_add`
				) VALUES (
					".$zayav_id.",
					".$zayav['client_id'].",
					".$sum.",
					'".addslashes($prim)."',
					".VIEWER_ID."
				)";
		query($sql);

		clientBalansUpdate($zayav['client_id']);

		history_insert(array(
			'type' => 7,
			'zayav_id' => $zayav_id,
			'client_id' => $zayav['client_id'],
			'value' => $sum,
			'value1' => $prim
		));

		$send['html'] = utf8(zayav_money($zayav_id));

		jsonSuccess($send);
		break;
	case 'accrual_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT *
				FROM `accrual`
				WHERE `deleted`=0
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($r['dogovor_id'])
			jsonError();

		$sql = "UPDATE `accrual` SET
					`deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($r['client_id']);

		history_insert(array(
			'type' => 8,
			'value' => $r['sum'],
			'value1' => $r['prim'],
			'zayav_id' => $r['zayav_id'],
			'client_id' => $r['client_id']
		));
		jsonSuccess();
		break;
	case 'accrual_rest':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT *
				FROM `accrual`
				WHERE `deleted`=1
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `accrual` SET
					`deleted`=0,
					`viewer_id_del`=0,
					`dtime_del`='0000-00-00 00:00:00'
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($r['client_id']);

		history_insert(array(
			'type' => 9,
			'value' => $r['sum'],
			'value1' => $r['prim'],
			'zayav_id' => $r['zayav_id'],
			'client_id' => $r['client_id']
		));
		jsonSuccess();
		break;
	case 'dogovor_preview':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id'])) {
			echo 'Ошибка: некорректный идентификатор договора.';
			exit;
		}
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id']) {
			echo 'Ошибка: неверный номер заявки.';
			exit;
		}
		if(!preg_match(REGEXP_NUMERIC, $_POST['nomer']) && !$_POST['nomer']) {
			echo 'Ошибка: некорректно указан номер договора.';
			exit;
		}
		if(!preg_match(REGEXP_DATE, $_POST['data_create'])) {
			echo 'Ошибка: некорректно указана дата заключения договора.';
			exit;
		}
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) && !$_POST['sum']) {
			echo 'Ошибка: некорректно указана сумма по договору.';
			exit;
		}
		if(!empty($_POST['avans']) && !preg_match(REGEXP_NUMERIC, $_POST['avans'])) {
			echo 'Ошибка: некорректно указан авансовый платёж.';
			exit;
		}
		$id = intval($_POST['id']);
		$zayav_id = intval($_POST['zayav_id']);
		$v = array(
			'nomer' => intval($_POST['nomer']),
			'fio' => htmlspecialchars(trim($_POST['fio'])),
			'adres' => htmlspecialchars(trim($_POST['adres'])),
			'sum' => intval($_POST['sum']),
			'avans' => intval($_POST['avans']),
			'data_create' => $_POST['data_create'],
			'link' => time().'_dogovor_'.intval($_POST['nomer']).'_'.$_POST['data_create'],
			'pasp_seria' => htmlspecialchars(trim($_POST['pasp_seria'])),
			'pasp_nomer' => htmlspecialchars(trim($_POST['pasp_nomer'])),
			'pasp_adres' => htmlspecialchars(trim($_POST['pasp_adres'])),
			'pasp_ovd' => htmlspecialchars(trim($_POST['pasp_ovd'])),
			'pasp_data' => htmlspecialchars(trim($_POST['pasp_data']))
		);

		if(query_value("SELECT COUNT(`id`) FROM `zayav_dogovor` WHERE `deleted`=0 AND `id`!=".$id." AND `nomer`=".$v['nomer'])) {
			echo 'Ошибка: договор с номером <b>'.$v['nomer'].'</b> уже был заключен.';
			exit;
		}
		if(empty($v['fio'])) {
			echo 'Ошибка: не указано Фио клиента.';
			exit;
		}
		if(empty($v['adres'])) {
			echo 'Ошибка: не указан адрес.';
			exit;
		}
		if($v['sum'] < $v['avans']) {
			echo 'Ошибка: авансовый платёж не может быть больше суммы договора.';
			exit;
		}

		$sql = "SELECT *
				FROM `zayav`
				WHERE `id`=".$zayav_id."
				  AND `zamer_status`!=1
				  AND `zamer_status`!=3
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql))) {
			echo 'Ошибка: заявки id = '.$zayav_id.' не существует.';
			exit;
		}
		$v['zayav_id'] = $zayav_id;
		dogovor_print($v);
		exit;
	case 'dogovor_create':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['nomer']) && !$_POST['nomer'])
			jsonError();
		if(!preg_match(REGEXP_DATE, $_POST['data_create']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) && !$_POST['sum'])
			jsonError();
		if(!empty($_POST['avans']) && !preg_match(REGEXP_NUMERIC, $_POST['avans']))
			jsonError();

		$id = intval($_POST['id']);
		$zayav_id = intval($_POST['zayav_id']);
		$nomer = intval($_POST['nomer']);
		$data_create = $_POST['data_create'];
		$fio = win1251(htmlspecialchars(trim($_POST['fio'])));
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$reason = win1251(htmlspecialchars(trim($_POST['reason'])));
		$pasp_seria = win1251(htmlspecialchars(trim($_POST['pasp_seria'])));
		$pasp_nomer = win1251(htmlspecialchars(trim($_POST['pasp_nomer'])));
		$pasp_adres = win1251(htmlspecialchars(trim($_POST['pasp_adres'])));
		$pasp_ovd = win1251(htmlspecialchars(trim($_POST['pasp_ovd'])));
		$pasp_data = win1251(htmlspecialchars(trim($_POST['pasp_data'])));
		$sum = intval($_POST['sum']);
		$avans = intval($_POST['avans']);
		if(empty($fio) || empty($adres))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `zayav_dogovor` WHERE `deleted`=0 AND `id`!=".$id." AND `nomer`=".$nomer))
			jsonError('Договор с номером <b>'.$nomer.'</b> уже был заключен.');

		if($sum < $avans)
			jsonError();

		$sql = "SELECT *
				FROM `zayav`
				WHERE `id`=".$zayav_id."
				  AND `deleted`=0
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "SELECT * FROM `zayav_dogovor` WHERE `deleted`=0 AND `zayav_id`=".$zayav_id;
		if($dog = mysql_fetch_assoc(query($sql))) {
			query("UPDATE `zayav_dogovor` SET `deleted`=1 WHERE `id`=".$dog['id']);
			query("UPDATE `money` SET `deleted`=1 WHERE `dogovor_id`=".$dog['id']);
		}
		$sql = "INSERT INTO `zayav_dogovor` (
					`nomer`,
					`data_create`,
					`zayav_id`,
					`client_id`,
					`fio`,
					`adres`,
					`pasp_seria`,
					`pasp_nomer`,
					`pasp_adres`,
					`pasp_ovd`,
					`pasp_data`,
					`sum`,
					`avans`,
					`link`,
					`reason`,
					`viewer_id_add`
				) VALUES (
					".$nomer.",
					'".$data_create."',
					".$zayav_id.",
					".$zayav['client_id'].",
					'".addslashes($fio)."',
					'".addslashes($adres)."',
					'".addslashes($pasp_seria)."',
					'".addslashes($pasp_nomer)."',
					'".addslashes($pasp_adres)."',
					'".addslashes($pasp_ovd)."',
					'".addslashes($pasp_data)."',
					".$sum.",
					".$avans.",
					'".time().'_dogovor_'.$nomer.'_'.$data_create."',
					'".addslashes($reason)."',
					".VIEWER_ID."
				)";
		query($sql);

		$dog_id = mysql_insert_id();

		//Удаление начислений и платежей по предыдущим договорам
		$sql = "UPDATE `accrual`
				SET `deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `deleted`=0
				  AND `dogovor_id`>0
				  AND `zayav_id`=".$zayav_id;
		query($sql);
		$sql = "UPDATE `money`
				SET `deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `deleted`=0
				  AND `dogovor_id`>0
				  AND `zayav_id`=".$zayav_id;
		query($sql);

		//Внесение начисления по договору
		$sql = "INSERT INTO `accrual` (
					`zayav_id`,
					`client_id`,
					`dogovor_id`,
					`sum`,
					`prim`,
					`viewer_id_add`
				) VALUES (
					".$zayav_id.",
					".$zayav['client_id'].",
					".$dog_id.",
					".$sum.",
					'Заключение договора №".$nomer.".',
					".VIEWER_ID."
				)";
		query($sql);

		//Присвоение заявке номера договора
		$sql = "UPDATE `zayav`
		        SET `dogovor_id`=".$dog_id.",
		            `dogovor_require`=0,
					`adres`='".addslashes($adres)."'
		        WHERE `id`=".$zayav_id;
		query($sql);

		// Обновление паспортных данных клиента
		$sql = "UPDATE `client`
		        SET `pasp_seria`='".addslashes($pasp_seria)."',
					`pasp_nomer`='".addslashes($pasp_nomer)."',
					`pasp_adres`='".addslashes($pasp_adres)."',
					`pasp_ovd`='".addslashes($pasp_ovd)."',
					`pasp_data`='".addslashes($pasp_data)."',
					`fio`='".$fio."'
		        WHERE `id`=".$zayav['client_id'];
		query($sql);

		history_insert(array(
			'type' => 19,
			'client_id' => $zayav['client_id'],
			'zayav_id' => $zayav_id,
			'dogovor_id' => $dog_id,
			'value' => addslashes($reason)
		));

		// Внесение авансового платежа, если есть
		if($avans) {
			$sql = "INSERT INTO `money` (
						`zayav_id`,
						`client_id`,
						`dogovor_id`,
						`sum`,
						`income_id`,
						`viewer_id_add`
					) VALUES (
						".$zayav_id.",
						".$zayav['client_id'].",
						".$dog_id.",
						".$avans.",
						1,
						".VIEWER_ID."
					)";
			query($sql);
			history_insert(array(
				'type' => 20,
				'client_id' => $zayav['client_id'],
				'zayav_id' => $zayav_id,
				'dogovor_id' => $dog_id
			));
		}

		dogovor_print($dog_id);

		clientBalansUpdate($zayav['client_id']);

		jsonSuccess();
		break;
	case 'dogovor_no_require':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);

		$sql = "SELECT *
				FROM `zayav`
				WHERE `deleted`=0
				  AND `dogovor_require`=1
				  AND `id`=".$zayav_id."
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		query("UPDATE `zayav` SET `dogovor_require`=0 WHERE `id`=".$zayav_id);
		jsonSuccess();
		break;
	case 'dogovor_require':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);

		$sql = "SELECT *
				FROM `zayav`
				WHERE `deleted`=0
				  AND `dogovor_require`=0
				  AND `id`=".$zayav_id."
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		query("UPDATE `zayav` SET `dogovor_require`=1 WHERE `id`=".$zayav_id);
		jsonSuccess();
		break;

	case 'remind_day':
		if(!preg_match(REGEXP_DATE, $_POST['day']))
			jsonError();
		$send['html'] = utf8(remind_spisok(1, $_POST));
		jsonSuccess($send);
		break;

	case 'history_next':
		if(!RULES_HISTORYSHOW)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
/*		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			$_POST['worker'] = 0;
		if(!preg_match(REGEXP_NUMERIC, $_POST['action']))
			$_POST['action'] = 0;*/
		$page = intval($_POST['page']);
		$send['html'] = utf8(history_spisok($page));
		jsonSuccess($send);
		break;

	case 'invoice_set':
		if(!preg_match(REGEXP_NUMERIC, $_POST['invoice_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
			jsonError();
		$invoice_id = intval($_POST['invoice_id']);
		$sum = intval($_POST['sum']);
		$sql = "SELECT * FROM `invoice` WHERE `id`=".$invoice_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();
		$income = query_value("SELECT SUM(`sum`) FROM `money` WHERE `deleted`=0 AND `invoice_id`=".$invoice_id);
		query("UPDATE `invoice` SET `start`=".($income - $sum)." WHERE `id`=".$invoice_id);
		jsonSuccess();
		break;

	case 'money_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$page = intval($_POST['page']);
		$data = income_spisok($page, $_POST);
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;

	case 'setup_worker_add':
		if(!RULES_WORKER)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();
		$viewer_id = intval($_POST['viewer_id']);
		if($viewer_id) {
			$sql = "SELECT `worker` FROM `vk_user` WHERE `viewer_id`=".$viewer_id." LIMIT 1";
			if(query_value($sql))
				jsonError('Этот пользователь уже является</br >сотрудником.');
			_viewer($viewer_id);
			query("UPDATE `vk_user` SET `worker`=1 WHERE `viewer_id`=".$viewer_id);
			xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
		} else {
			if(!preg_match(REGEXP_NUMERIC, $_POST['sex']) || !$_POST['sex'])
				jsonError();
			$first_name = win1251(htmlspecialchars(trim($_POST['first_name'])));
			$last_name = win1251(htmlspecialchars(trim($_POST['last_name'])));
			$post = win1251(htmlspecialchars(trim($_POST['post'])));
			$sex = intval($_POST['sex']);
			if(!$first_name || !$last_name)
				jsonError();
			$viewer_id = _maxSql('vk_user', 'viewer_id');
			if($viewer_id < VIEWER_MAX)
				$viewer_id = VIEWER_MAX;
			$sql = "INSERT INTO `vk_user` (
				`viewer_id`,
				`first_name`,
				`last_name`,
				`sex`,
				`photo`,
				`worker`,
				`post`
			) VALUES (
				".$viewer_id.",
				'".addslashes($first_name)."',
				'".addslashes($last_name)."',
				".$sex.",
				'http://vk.com/images/camera_c.gif',
				1,
				'".addslashes($post)."'
			)";
			query($sql);
		}

		history_insert(array(
			'type' => 13,
			'value' => $viewer_id
		));

		$send['html'] = utf8(setup_worker_spisok());
		jsonSuccess($send);
		break;
	case 'setup_worker_del':
		if(!RULES_WORKER)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();
		$viewer_id = intval($_POST['viewer_id']);
		$sql = "SELECT * FROM `vk_user` WHERE `viewer_id`=".$viewer_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();
		if($r['admin'])
			jsonError();
		if(!$r['worker'])
			jsonError();
		query("UPDATE `vk_user` SET `worker`=0,`rules`='' WHERE `viewer_id`=".$viewer_id);
		xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);

		history_insert(array(
			'type' => 14,
			'value' => $viewer_id
		));

		$send['html'] = utf8(setup_worker_spisok());
		jsonSuccess($send);
		break;
	case 'setup_worker_save':
		if(!RULES_WORKER)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();

		$viewer_id = intval($_POST['viewer_id']);
		$first_name = win1251(htmlspecialchars(trim($_POST['first_name'])));
		$last_name = win1251(htmlspecialchars(trim($_POST['last_name'])));
		$post = win1251(htmlspecialchars(trim($_POST['post'])));

		if(!$first_name || !$last_name)
			jsonError();

		$sql = "SELECT * FROM `vk_user` WHERE `worker`=1 AND `viewer_id`=".$viewer_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		query("UPDATE `vk_user`
		       SET `first_name`='".addslashes($first_name)."',
		           `last_name`='".addslashes($last_name)."',
		           `post`='".addslashes($post)."'
		       WHERE `viewer_id`=".$viewer_id);
		xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);

		$changes = '';
		if($r['first_name'] != $first_name)
			$changes .= '<tr><th>Имя:<td>'.$r['first_name'].'<td>»<td>'.$first_name;
		if($r['last_name'] != $last_name)
			$changes .= '<tr><th>Фамилия:<td>'.$r['last_name'].'<td>»<td>'.$last_name;
		if($r['post'] != $post)
			$changes .= '<tr><th>Должность:<td>'.$r['post'].'<td>»<td>'.$post;
		if($changes)
			history_insert(array(
				'type' => 514,
				'value' => $viewer_id,
				'value1' => '<table>'.$changes.'</table>'
			));

		jsonSuccess();
		break;
	case 'setup_rules_set':
		if(!RULES_WORKER)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['action']))
			jsonError();

		$viewer_id = intval($_POST['viewer_id']);
		$value = strtoupper($_POST['value']);
		$action = intval($_POST['action']);

		if(!rulesList($value))
			jsonError();

		$u = _viewer($viewer_id);
		if($u['admin'])
			jsonError();
		if(!$u['worker'])
			jsonError();

		$rules = workerRulesArray($u['rules'], true);
		unset($rules[$value]);
		if($value == 'RULES_APPENTER')
			$rules = array();
		if($value == 'RULES_SETUP') {
			unset($rules['RULES_WORKER']);
			unset($rules['RULES_PRODUCT']);
			unset($rules['RULES_INCOME']);
			unset($rules['RULES_ZAYAVRASHOD']);
		}
		if($action)
			$rules[$value] = 1;
		$sql = "UPDATE `vk_user` SET `rules`='".implode(',', array_keys($rules))."' WHERE `viewer_id`=".$viewer_id;
		query($sql);
		xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
		jsonSuccess();
		break;
	case 'setup_rekvisit':
		if(!RULES_REKVISIT)
			jsonError();
		$org_name = win1251(htmlspecialchars(trim($_POST['org_name'])));
		$ogrn = win1251(htmlspecialchars(trim($_POST['ogrn'])));
		$inn = win1251(htmlspecialchars(trim($_POST['inn'])));
		$kpp = win1251(htmlspecialchars(trim($_POST['kpp'])));
		$yur_adres = win1251(htmlspecialchars(trim($_POST['yur_adres'])));
		$telefon = win1251(htmlspecialchars(trim($_POST['telefon'])));
		$ofice_adres = win1251(htmlspecialchars(trim($_POST['ofice_adres'])));

		$sql = "SELECT * FROM `setup_global`";
		$g = mysql_fetch_assoc(query($sql));

		$sql = "UPDATE `setup_global`
				SET `org_name`='".addslashes($org_name)."',
					`ogrn`='".addslashes($ogrn)."',
					`inn`='".addslashes($inn)."',
					`kpp`='".addslashes($kpp)."',
					`yur_adres`='".addslashes($yur_adres)."',
					`telefon`='".addslashes($telefon)."',
					`ofice_adres`='".addslashes($ofice_adres)."'";
		query($sql);

		$changes = '';
		if($g['org_name'] != $org_name)
			$changes .= '<tr><th>Название организации:<td>'.$g['org_name'].'<td>»<td>'.$org_name;
		if($g['ogrn'] != $ogrn)
			$changes .= '<tr><th>ОГРН:<td>'.$g['ogrn'].'<td>»<td>'.$ogrn;
		if($g['inn'] != $inn)
			$changes .= '<tr><th>ИНН:<td>'.$g['inn'].'<td>»<td>'.$inn;
		if($g['kpp'] != $kpp)
			$changes .= '<tr><th>КПП:<td>'.$g['kpp'].'<td>»<td>'.$kpp;
		if($g['yur_adres'] != $yur_adres)
			$changes .= '<tr><th>Юридический адрес:<td>'.$g['yur_adres'].'<td>»<td>'.$yur_adres;
		if($g['telefon'] != $telefon)
			$changes .= '<tr><th>Телефоны:<td>'.$g['telefon'].'<td>»<td>'.$telefon;
		if($g['ofice_adres'] != $ofice_adres)
			$changes .= '<tr><th>Адрес офиса:<td>'.$g['ofice_adres'].'<td>»<td>'.$ofice_adres;
		if($changes)
			history_insert(array(
				'type' => 510,
				'value' => '<table>'.$changes.'</table>'
			));

		jsonSuccess();
		break;
	case 'setup_product_add':
		if(!RULES_PRODUCT)
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();
		$sql = "INSERT INTO `setup_product` (
					`name`
				) VALUES (
					'".addslashes($name)."'
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'product');
		GvaluesCreate();

		history_insert(array(
			'type' => 501,
			'value' => $name
		));

		$send['html'] = utf8(setup_product_spisok());
		jsonSuccess($send);
		break;
	case 'setup_product_edit':
		if(!RULES_PRODUCT)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();

		$product_id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `setup_product` WHERE `id`=".$product_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `setup_product`
		        SET `name`='".addslashes($name)."'
		        WHERE `id`=".$product_id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'product');
		GvaluesCreate();

		$changes = '';
		if($r['name'] != $name)
			$changes = '<tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name;
		if($changes)
			history_insert(array(
				'type' => 502,
				'value' => '<table>'.$changes.'</table>',
				'value1' => $name
			));

		$send['html'] = utf8(setup_product_spisok());
		jsonSuccess($send);
		break;
	case 'setup_product_del':
		if(!RULES_PRODUCT)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$product_id = intval($_POST['id']);

		$sql = "SELECT * FROM `setup_product` WHERE `id`=".$product_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `setup_product_sub` WHERE `product_id`=".$product_id))
			jsonError();
		if(query_value("SELECT COUNT(`id`) FROM `zayav_product` WHERE `product_id`=".$product_id))
			jsonError();

		$sql = "DELETE FROM `setup_product` WHERE `id`=".$product_id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'product');
		GvaluesCreate();

		history_insert(array(
			'type' => 503,
			'value' => $r['name']
		));

		jsonSuccess();
		break;
	case 'setup_product_sub_add':
		if(!RULES_PRODUCT)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['product_id']))
			jsonError();

		$product_id = intval($_POST['product_id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		if(!query_value("SELECT COUNT(`id`) FROM `setup_product` WHERE `id`=".$product_id))
			jsonError();

		$sql = "INSERT INTO `setup_product_sub` (
					`product_id`,
					`name`,
					`viewer_id_add`
				) VALUES (
					".$product_id.",
					'".addslashes($name)."',
					".VIEWER_ID."
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'product_sub');
		GvaluesCreate();

		history_insert(array(
			'type' => 504,
			'value' => _product($product_id),
			'value1' => $name
		));

		$send['html'] = utf8(setup_product_sub_spisok($product_id));
		jsonSuccess($send);
		break;
	case 'setup_product_sub_edit':
		if(!RULES_PRODUCT)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `setup_product_sub` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `setup_product_sub` SET `name`='".addslashes($name)."' WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'product_sub');
		GvaluesCreate();

		if($r['name'] != $name)
			history_insert(array(
				'type' => 505,
				'value' => _product($r['product_id']),
				'value1' => '<table><tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name.'</table>'
			));

		jsonSuccess();
		break;
	case 'setup_product_sub_del':
		if(!RULES_PRODUCT)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT * FROM `setup_product_sub` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `zayav_product` WHERE `product_sub_id`=".$id))
			jsonError();
		$sql = "DELETE FROM `setup_product_sub` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'product_sub');
		GvaluesCreate();

		history_insert(array(
			'type' => 506,
			'value' => _product($r['product_id']),
			'value1' => $r['name']
		));

		jsonSuccess();
		break;
	case 'setup_invoice_add':
//		if(!RULES_INCOME)
//			jsonError();
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		$types = trim($_POST['types']);
		if(empty($name))
			jsonError();

		if(!empty($types)) {
			foreach(explode(',', $types) as $id)
				if(!preg_match(REGEXP_NUMERIC, $id))
					jsonError();
			$prihod = query_value("SELECT `name` FROM `setup_income` WHERE `id` IN (".$types.") AND `invoice_id`>0 LIMIT 1");
			if($prihod)
				jsonError('Вид платежа <u>'.$prihod.'</u> задействован в другом счёте');
		}
		$sql = "INSERT INTO `invoice` (
					`name`,
					`about`
				) VALUES (
					'".addslashes($name)."',
					'".addslashes($about)."'
				)";
		query($sql);

		if(!empty($types))
			query("UPDATE `setup_income` SET `invoice_id`=".mysql_insert_id()." WHERE `id` IN (".$types.")");

		//xcache_unset(CACHE_PREFIX.'income');
		GvaluesCreate();

		history_insert(array(
			'type' => 515,
			'value' => $name
		));


		$send['html'] = utf8(setup_invoice_spisok());
		jsonSuccess($send);
		break;
	case 'setup_invoice_edit':
//		if(!RULES_INCOME)
//			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$invoice_id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		$types = trim($_POST['types']);
		if(empty($name))
			jsonError();

		if(!empty($types)) {
			foreach(explode(',', $types) as $id)
				if(!preg_match(REGEXP_NUMERIC, $id))
					jsonError();
			$prihod = query_value("SELECT `name`
								   FROM `setup_income`
								   WHERE `id` IN (".$types.")
								     AND `invoice_id`>0
								     AND `invoice_id`!=".$invoice_id."
								   LIMIT 1");
			if($prihod)
				jsonError('Вид платежа <u>'.$prihod.'</u> задействован в другом счёте');
		}

		$sql = "SELECT * FROM `invoice` WHERE `id`=".$invoice_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `invoice`
				SET `name`='".addslashes($name)."',
					`about`='".addslashes($about)."'
				WHERE `id`=".$invoice_id;
		query($sql);

		query("UPDATE `setup_income` SET `invoice_id`=0 WHERE `invoice_id`=".$invoice_id);
		if(!empty($types))
			query("UPDATE `setup_income` SET `invoice_id`=".$invoice_id." WHERE `id` IN (".$types.")");


		//xcache_unset(CACHE_PREFIX.'income');
		GvaluesCreate();

		$changes = '';
		if($r['name'] != $name)
			$changes .= '<tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name;
		if($r['about'] != $about)
			$changes .= '<tr><th>Описание:<td>'.str_replace("\n", '<br />', $r['about']).'<td>»<td>'.str_replace("\n", '<br />', $about);
		if($changes)
			history_insert(array(
				'type' => 516,
				'value' => $name,
				'value1' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(setup_invoice_spisok());
		jsonSuccess($send);
		break;
	case 'setup_invoice_del':
//		if(!RULES_INCOME)
//			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$invoice_id = intval($_POST['id']);

		$sql = "SELECT * FROM `invoice` WHERE `id`=".$invoice_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		query("DELETE FROM `invoice` WHERE `id`=".$invoice_id);
		query("UPDATE `setup_income` SET `invoice_id`=0 WHERE `invoice_id`=".$invoice_id);

//		xcache_unset(CACHE_PREFIX.'income');
		GvaluesCreate();

		history_insert(array(
			'type' => 517,
			'value' => $r['name']
		));

		$send['html'] = utf8(setup_invoice_spisok());
		jsonSuccess($send);
		break;
	case 'setup_income_add':
		if(!RULES_INCOME)
			jsonError();
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();
		$sql = "INSERT INTO `setup_income` (
					`name`,
					`sort`
				) VALUES (
					'".addslashes($name)."',
					"._maxSql('setup_income', 'sort')."
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'income');
		GvaluesCreate();

		history_insert(array(
			'type' => 507,
			'value' => $name
		));


		$send['html'] = utf8(setup_income_spisok());
		jsonSuccess($send);
		break;
	case 'setup_income_edit':
		if(!RULES_INCOME)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `setup_income` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `setup_income`
				SET `name`='".addslashes($name)."'
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'income');
		GvaluesCreate();

		$changes = '';
		if($r['name'] != $name)
			$changes .= '<tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name;
		if($changes)
			history_insert(array(
				'type' => 508,
				'value' => $name,
				'value1' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(setup_income_spisok());
		jsonSuccess($send);
		break;
	case 'setup_income_del':
		if(!RULES_INCOME)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		// Нельзя удалить наличный платёж
		if($id == 1)
			jsonError();

		$sql = "SELECT * FROM `setup_income` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `money` WHERE `income_id`=".$id))
			jsonError();
		$sql = "DELETE FROM `setup_income` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'income');
		GvaluesCreate();

		history_insert(array(
			'type' => 509,
			'value' => $r['name']
		));

		$send['html'] = utf8(setup_income_spisok());
		jsonSuccess($send);
		break;
	case 'setup_zayavrashod_add':
		if(!RULES_ZAYAVRASHOD)
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['show_txt']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['show_worker']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$show_txt = intval($_POST['show_txt']);
		$show_worker = intval($_POST['show_worker']);

		if($show_txt)
			$show_worker = 0;

		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `setup_zayavrashod` (
					`name`,
					`show_txt`,
					`show_worker`,
					`sort`
				) VALUES (
					'".addslashes($name)."',
					".$show_txt.",
					".$show_worker.",
					"._maxSql('setup_zayavrashod', 'sort')."
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'zayavrashod');
		GvaluesCreate();

		history_insert(array(
			'type' => 511,
			'value' => $name
		));


		$send['html'] = utf8(setup_zayavrashod_spisok());
		jsonSuccess($send);
		break;
	case 'setup_zayavrashod_edit':
		if(!RULES_ZAYAVRASHOD)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['show_txt']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['show_worker']))
			jsonError();

		$id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$show_txt = intval($_POST['show_txt']);
		$show_worker = intval($_POST['show_worker']);

		if($show_txt)
			$show_worker = 0;

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `setup_zayavrashod` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `setup_zayavrashod`
				SET `name`='".addslashes($name)."',
					`show_txt`=".$show_txt.",
					`show_worker`=".$show_worker."
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'zayavrashod');
		GvaluesCreate();

		$changes = '';
		if($r['name'] != $name)
			$changes .= '<tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name;
		if($r['show_txt'] != $show_txt)
			$changes .= '<tr><th>Текстовое поле:<td>'.($r['show_txt'] ? 'да' : 'нет').'<td>»<td>'.($show_txt ? 'да' : 'нет');
		if($r['show_worker'] != $show_worker)
			$changes .= '<tr><th>Список сотрудников:<td>'.($r['show_worker'] ? 'да' : 'нет').'<td>»<td>'.($show_worker ? 'да' : 'нет');
		if($changes)
			history_insert(array(
				'type' => 512,
				'value' => $name,
				'value1' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(setup_zayavrashod_spisok());
		jsonSuccess($send);
		break;
	case 'setup_zayavrashod_del':
		if(!RULES_ZAYAVRASHOD)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT * FROM `setup_zayavrashod` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `zayav_rashod` WHERE `category_id`=".$id))
			jsonError();
		$sql = "DELETE FROM `setup_zayavrashod` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'zayavrashod');
		GvaluesCreate();

		history_insert(array(
			'type' => 513,
			'value' => $r['name']
		));

		$send['html'] = utf8(setup_zayavrashod_spisok());
		jsonSuccess($send);
		break;
}

jsonError();