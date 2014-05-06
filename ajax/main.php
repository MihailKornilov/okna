<?php
require_once('config.php');
require_once(VKPATH.'/vk_ajax.php');


switch(@$_POST['op']) {
	case 'cache_clear':
		if(!SA)
			jsonError();
		$sql = "SELECT `viewer_id` FROM `vk_user` WHERE `worker`=1";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q)) {
			xcache_unset(CACHE_PREFIX.'viewer_'.$r['viewer_id']);
			xcache_unset(CACHE_PREFIX.'viewer_rules_'.$r['viewer_id']);
			xcache_unset(CACHE_PREFIX.'pin_enter_count'.$r['viewer_id']);
		}
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
			case 'application/pdf': break;
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
				'title' => utf8(htmlspecialchars_decode($r['fio'])),
				'adres' => utf8(htmlspecialchars_decode($r['adres']))
			);
			$content = array();
			if($r['telefon'])
				$content[] = $r['telefon'];
			if($r['adres'])
				$content[] = $r['adres'];
			if(!empty($content))
				$unit['content'] = utf8($r['fio'].'<span>'.implode('<br />', $content).'</span>');
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

		$content = array();
		if($telefon)
			$content[] = $telefon;
		if($adres)
			$content[] = $adres;
		$send = array(
			'uid' => mysql_insert_id(),
			'title' => utf8($fio),
			'content' => utf8($fio.'<span>'.implode('<br />', $content).'</span>'),
			'adres' => utf8($adres)
		);
		history_insert(array(
			'type' => 1,
			'client_id' => $send['uid']
		));
		jsonSuccess($send);
		break;
	case 'client_spisok':
		$data = client_data($_POST);
		if(empty($_POST['page']))
			$send['result'] = utf8($data['result']);
		$send['spisok'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'client_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) || !$_POST['client_id'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker_id']))
			jsonError();
		$client_id = intval($_POST['client_id']);
		$fio = win1251(htmlspecialchars(trim($_POST['fio'])));
		$telefon = win1251(htmlspecialchars(trim($_POST['telefon'])));
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$worker_id = intval($_POST['worker_id']);
		$pasp_seria = win1251(htmlspecialchars(trim($_POST['pasp_seria'])));
		$pasp_nomer = win1251(htmlspecialchars(trim($_POST['pasp_nomer'])));
		$pasp_adres = win1251(htmlspecialchars(trim($_POST['pasp_adres'])));
		$pasp_ovd = win1251(htmlspecialchars(trim($_POST['pasp_ovd'])));
		$pasp_data = win1251(htmlspecialchars(trim($_POST['pasp_data'])));
		if(empty($fio))
			jsonError();
		$sql = "SELECT * FROM `client` WHERE !`deleted` AND `id`=".$client_id;
		if(!$client = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($worker_id) {
			$sql = "SELECT COUNT(`id`) FROM `client` WHERE `id`!=".$client_id." AND `worker_id`=".$worker_id;
			if(query_value($sql))
				jsonError('Этот сотрудник связан с другим клиентом');
		}

		query("UPDATE `client` SET
				`fio`='".$fio."',
				`telefon`='".$telefon."',
				`adres`='".$adres."',
				`worker_id`=".$worker_id.",
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
			$changes .= '<tr><th>Телефон:<td>'.$client['telefon'].'<td>»<td>'.$telefon;
		if($client['adres'] != $adres)
			$changes .= '<tr><th>Адрес:<td>'.$client['adres'].'<td>»<td>'.$adres;
		if($client['worker_id'] != $worker_id)
			$changes .= '<tr><th>Сотрудник:<td>'.($client['worker_id'] ? _viewer($client['worker_id'], 'name') : '').
							'<td>»'.
							'<td>'.($worker_id ? _viewer($worker_id, 'name') : '');
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
			'worker_id' => $worker_id,
			'pasp_seria' => $pasp_seria,
			'pasp_nomer' => $pasp_nomer,
			'pasp_adres' => $pasp_adres,
			'pasp_ovd' => $pasp_ovd,
			'pasp_data' => $pasp_data,

			'balans' => clientBalansUpdate($client_id),
			'viewer_id_add' => $client['viewer_id_add'],
			'dtime_add' => $client['dtime_add'],
			'deleted' => 0
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
		$nomer_t = win1251(htmlspecialchars(trim($_POST['nomer_t'])));
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
		            `nomer_d`='".addslashes($nomer_d)."',
		            `nomer_t`='".addslashes($nomer_t)."'
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
		if($zayav['nomer_t'] != $nomer_t)
			$changes .= '<tr><th>Номер T:<td>'.$zayav['nomer_t'].'<td>»<td>'.$nomer_t;
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
		$day = $_POST['day'];
		if($status == 2 && !preg_match(REGEXP_DATE, $day))
			jsonError();

		$sql = "SELECT *
				FROM `zayav`
				WHERE `id`=".$zayav_id."
				  AND `zakaz_status`>0
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($zayav['zakaz_status'] != $status) {
			$sql = "UPDATE `zayav`
			        SET `status_day`='".($status == 2 ? $day : '0000-00-00')."',
			            `zakaz_status`=".$status."
			        WHERE `id`=".$zayav_id;
			query($sql);
			history_insert(array(
				'type' => 25,
				'client_id' => $zayav['client_id'],
				'zayav_id' => $zayav_id,
				'value' => $zayav['zakaz_status'],
				'value1' => $status,
				'value2' => $status == 2 ? $day : ''
			));
		} elseif($status == 2 && $zayav['status_day'] != $day) {
			query("UPDATE `zayav` SET `status_day`='".$day."' WHERE `id`=".$zayav_id);
			history_insert(array(
				'type' => 31,
				'client_id' => $zayav['client_id'],
				'zayav_id' => $zayav_id,
				'value' => $day
			));
		}

		jsonSuccess();
		break;
	case 'zakaz_to_set':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));

		if(empty($adres))
			jsonError();

		$sql = "SELECT *
				FROM `zayav`
				WHERE `id`=".$zayav_id."
				  AND `zakaz_status`>0
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `zayav`
		        SET `zakaz_status`=0,
		            `set_status`=1,
		            `adres`='".addslashes($adres)."'
		        WHERE `id`=".$zayav_id;
		query($sql);
		history_insert(array(
			'type' => 30,
			'client_id' => $zayav['client_id'],
			'zayav_id' => $zayav_id,
			'value' => $adres
		));

		jsonSuccess();
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
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']) || !$_POST['status'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$status = intval($_POST['status']);

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
		$nomer_t = win1251(htmlspecialchars(trim($_POST['nomer_t'])));
		$product = zayav_product_test($_POST['product']);
		if(!$product)
			jsonError();
		if(empty($adres))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE !`deleted` AND `set_status` AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `zayav`
		        SET `adres`='".addslashes($adres)."',
		            `nomer_vg`='".addslashes($nomer_vg)."',
		            `nomer_g`='".addslashes($nomer_g)."',
		            `nomer_d`='".addslashes($nomer_d)."',
		            `nomer_t`='".addslashes($nomer_t)."'
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
		if($zayav['nomer_t'] != $nomer_t)
			$changes .= '<tr><th>Номер T:<td>'.$zayav['nomer_t'].'<td>»<td>'.$nomer_t;
		if($changes)
			history_insert(array(
				'type' => 22,
				'zayav_id' => $zayav_id,
				'value' => '<table>'.$changes.'</table>'
			));
		jsonSuccess();
		break;
	case 'set_status':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']) || !$_POST['status'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$status = intval($_POST['status']);
		$day = $_POST['day'];
		if($status == 2 && !preg_match(REGEXP_DATE, $day))
			jsonError();

		$sql = "SELECT *
				FROM `zayav`
				WHERE `id`=".$zayav_id."
				  AND `set_status`>0
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($zayav['set_status'] != $status) {
			$sql = "UPDATE `zayav`
					SET `status_day`='".($status == 2 ? $day : '0000-00-00')."',
						`set_status`=".$status."
					WHERE `id`=".$zayav_id;
			query($sql);
			history_insert(array(
				'type' => 26,
				'client_id' => $zayav['client_id'],
				'zayav_id' => $zayav_id,
				'value' => $zayav['set_status'],
				'value1' => $status,
				'value2' => $status == 2 ? $day : ''
			));
		} elseif($status == 2 && $zayav['status_day'] != $day) {
			query("UPDATE `zayav` SET `status_day`='".$day."' WHERE `id`=".$zayav_id);
			history_insert(array(
				'type' => 31,
				'client_id' => $zayav['client_id'],
				'zayav_id' => $zayav_id,
				'value' => $day
			));
		}

		jsonSuccess();
		break;
	case 'zayav_expense_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$expenseNew = zayav_expense_test($_POST['rashod']);
		if($expenseNew === false)
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE !`deleted` AND `id`=".$zayav_id." LIMIT 1";
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		$rashodOld = zayav_expense_spisok($zayav_id, 'array');
		if($expenseNew != $rashodOld) {
			$old = zayav_expense_spisok($zayav_id);
			$sql = "DELETE FROM `zayav_expense` WHERE !`salary_list_id` AND `zayav_id`=".$zayav_id;
			query($sql);
			foreach($expenseNew as $r) {
				if($r[6])
					continue;
				$sql = "INSERT INTO `zayav_expense` (
							`zayav_id`,
							`category_id`,
							`txt`,
							`worker_id`,
							`sum`,
							`acc`,
							`mon`
						) VALUES (
							".$zayav_id.",
							".$r[0].",
							'".(_zayavRashod($r[0], 'txt') ? addslashes($r[1]) : '')."',
							".(_zayavRashod($r[0], 'worker') ? intval($r[1]) : 0).",
							".$r[2].",
							".$r[3].",
							'".($r[3] ? $r[5].'-'.($r[4] < 10 ? 0 : '').$r[4].'-'.strftime('%d') : '0000-00-00')."'
						)";
				query($sql);
			}
			_zayavBalansUpdate($zayav_id);
			$changes = '<tr><td>'.$old.'<td>»<td>'.zayav_expense_spisok($zayav_id);
			history_insert(array(
				'type' => 29,
				'zayav_id' => $zayav_id,
				'value' => '<table>'.$changes.'</table>'
			));
		}
		$expense = zayav_expense_spisok($zayav_id, 'all');
		$send['html'] = utf8($expense['html']);
		foreach($expense['array'] as $n => $r)
			$expense['array'][$n][1] = utf8($expense['array'][$n][1]);
		$send['array'] = $expense['array'];
		jsonSuccess($send);
		break;
	case 'zayav_spisok':
		$data = zayav_spisok($_POST['category'], $_POST);
		$send['result'] = utf8($data['result']);
		$send['spisok'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'zayav_findfast':
		$find = win1251(htmlspecialchars(trim($_POST['find'])));
		if(empty($find))
			jsonError();
		$data = zayav_findfast(1, $find);
		$send['result'] = utf8($data['result']);
		$send['spisok'] = utf8($data['spisok']);
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
		if(!preg_match(REGEXP_CENA, $_POST['sum']) || $_POST['sum'] == 0)
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$sum = str_replace(',', '.', $_POST['sum']);
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
		_zayavBalansUpdate($zayav_id);

		history_insert(array(
			'type' => 7,
			'zayav_id' => $zayav_id,
			'client_id' => $zayav['client_id'],
			'value' => round($sum, 2),
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
		_zayavBalansUpdate($r['zayav_id']);

		history_insert(array(
			'type' => 8,
			'value' => round($r['sum'], 2),
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
		_zayavBalansUpdate($r['zayav_id']);

		history_insert(array(
			'type' => 9,
			'value' => round($r['sum'], 2),
			'value1' => $r['prim'],
			'zayav_id' => $r['zayav_id'],
			'client_id' => $r['client_id']
		));
		jsonSuccess();
		break;
	case 'refund_add':
		if(empty($_POST['zayav_id']) || !preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
			jsonError();
		if(empty($_POST['invoice_id']) || !preg_match(REGEXP_NUMERIC, $_POST['invoice_id']))
			jsonError();
		if(!preg_match(REGEXP_CENA, $_POST['sum']) || $_POST['sum'] == 0)
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$invoice_id = intval($_POST['invoice_id']);
		$sum = str_replace(',', '.', $_POST['sum']);
		$prim = win1251(htmlspecialchars(trim($_POST['prim'])));

		$sql = "SELECT * FROM `zayav` WHERE !`deleted` AND `id`=".$zayav_id;
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "INSERT INTO `money` (
					`zayav_id`,
					`client_id`,
					`invoice_id`,
					`sum`,
					`prim`,
					`refund`,
					`viewer_id_add`
				) VALUES (
					".$zayav_id.",
					".$z['client_id'].",
					".$invoice_id.",
					-".$sum.",
					'".addslashes($prim)."',
					1,
					".VIEWER_ID."
				)";
		query($sql);

		invoice_history_insert(array(
			'action' => 13,
			'table' => 'money',
			'id' => mysql_insert_id()
		));

//		clientBalansUpdate($z['client_id']);
		_zayavBalansUpdate($zayav_id);

		history_insert(array(
			'type' => 56,
			'zayav_id' => $zayav_id,
			'client_id' => $z['client_id'],
			'value' => round($sum, 2),
			'value1' => $prim
		));

		$send['html'] = utf8(zayav_money($zayav_id));

		jsonSuccess($send);
		break;
	case 'refund_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT *
				FROM `money`
				WHERE !`deleted`
				  AND `refund`
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `money` SET
					`deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);

		_zayavBalansUpdate($r['zayav_id']);

		invoice_history_insert(array(
			'action' => 14,
			'table' => 'money',
			'id' => $id
		));

		history_insert(array(
			'type' => 57,
			'zayav_id' => $r['zayav_id'],
			'client_id' => $r['client_id'],
			'value' => round($r['sum'], 2),
			'value1' => $r['prim']
		));

		$send['html'] = utf8(zayav_money($r['zayav_id']));
		jsonSuccess($send);
		break;
	case 'dogovor_preview':
		$v = dogovorFilter($_POST);
		if(!is_array($v))
			die($v);
		dogovor_print($v);
		exit;
	case 'dogovor_create':
		$v = dogovorFilter($_POST);
		if(!is_array($v))
			jsonError($v);

		if(query_value("SELECT COUNT(`id`) FROM `zayav_dogovor` WHERE `deleted`=0 AND `zayav_id`=".$v['zayav_id']))
			jsonError('Ошибка: на эту заявку уже заключён договор.');

		foreach($v as $k => $r)
			$v[$k] = win1251($r);

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
					`cut`,
					`link`,
					`viewer_id_add`
				) VALUES (
					".$v['nomer'].",
					'".$v['data_create']."',
					".$v['zayav_id'].",
					".$v['client_id'].",
					'".addslashes($v['fio'])."',
					'".addslashes($v['adres'])."',
					'".addslashes($v['pasp_seria'])."',
					'".addslashes($v['pasp_nomer'])."',
					'".addslashes($v['pasp_adres'])."',
					'".addslashes($v['pasp_ovd'])."',
					'".addslashes($v['pasp_data'])."',
					".$v['sum'].",
					".$v['avans'].",
					'".$v['cut']."',
					'".time().'_dogovor_'.$v['nomer'].'_'.$v['data_create']."',
					".VIEWER_ID."
				)";
		query($sql);

		$dog_id = mysql_insert_id();
/*
		//Удаление начислений и платежей по предыдущим договорам
		$sql = "UPDATE `accrual`
				SET `deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `deleted`=0
				  AND `dogovor_id`>0
				  AND `zayav_id`=".$v['zayav_id'];
		query($sql);
		$sql = "UPDATE `money`
				SET `deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `deleted`=0
				  AND `dogovor_id`>0
				  AND `zayav_id`=".$v['zayav_id'];
		query($sql);
*/
		//Внесение начисления по договору
		$sql = "INSERT INTO `accrual` (
					`zayav_id`,
					`client_id`,
					`dogovor_id`,
					`sum`,
					`prim`,
					`viewer_id_add`
				) VALUES (
					".$v['zayav_id'].",
					".$v['client_id'].",
					".$dog_id.",
					".$v['sum'].",
					'Заключение договора №".$v['nomer'].".',
					".VIEWER_ID."
				)";
		query($sql);

		//Присвоение заявке id договора и обновление адреса
		$sql = "UPDATE `zayav`
		        SET `dogovor_id`=".$dog_id.",
		            `dogovor_require`=0,
					`adres`='".addslashes($v['adres'])."'
		        WHERE `id`=".$v['zayav_id'];
		query($sql);

		// Обновление паспортных данных клиента
		$sql = "UPDATE `client`
		        SET `pasp_seria`='".addslashes($v['pasp_seria'])."',
					`pasp_nomer`='".addslashes($v['pasp_nomer'])."',
					`pasp_adres`='".addslashes($v['pasp_adres'])."',
					`pasp_ovd`='".addslashes($v['pasp_ovd'])."',
					`pasp_data`='".addslashes($v['pasp_data'])."',
					`fio`='".$v['fio']."'
		        WHERE `id`=".$v['client_id'];
		query($sql);

		history_insert(array(
			'type' => 19,
			'client_id' => $v['client_id'],
			'zayav_id' => $v['zayav_id'],
			'dogovor_id' => $dog_id
		));

		// Внесение авансового платежа, если есть
		if($v['avans'] > 0)
			income_insert(array(
				'zayav_id' => $v['zayav_id'],
				'dogovor_id' => $dog_id,
				'sum' => $v['avans'],
				'type' => 1
			));
		else {
			clientBalansUpdate($v['client_id']);
			_zayavBalansUpdate($v['zayav_id']);
		}

		if($v['cut'])
			foreach(explode(',', $v['cut']) as $r) {
				$ex = explode(':', $r);
				$sql = "INSERT INTO `remind` (
							`cut`,
							`client_id`,
							`zayav_id`,
							`txt`,
							`day`,
							`viewer_id_add`
						) VALUES (
							1,
							".$v['client_id'].",
							".$v['zayav_id'].",
							'".round(str_replace(',', '.', $ex[0]), 2)."',
							'".$ex[1]."',
							".VIEWER_ID."
						)";
				query($sql);
				remind_history_add(array(
					'remind_id' => mysql_insert_id(),
					'day' => $ex[1]
				));
			}

		dogovor_print($dog_id);

		jsonSuccess();
		break;
	case 'dogovor_edit':
		$v = dogovorFilter($_POST);
		if(!is_array($v))
			jsonError($v);

		foreach($v as $k => $r)
			$v[$k] = win1251($r);

		$sql = "SELECT * FROM `zayav_dogovor` WHERE `deleted`=0 AND `zayav_id`=".$v['zayav_id'];
		if(!$dog = mysql_fetch_assoc(query($sql)))
			jsonError('Ошибка: договора не существует.');

		$sql = "UPDATE `zayav_dogovor`
				SET `nomer`=".$v['nomer'].",
					`data_create`='".$v['data_create']."',
					`zayav_id`=".$v['zayav_id'].",
					`client_id`=".$v['client_id'].",
					`fio`='".addslashes($v['fio'])."',
					`adres`='".addslashes($v['adres'])."',
					`pasp_seria`='".addslashes($v['pasp_seria'])."',
					`pasp_nomer`='".addslashes($v['pasp_nomer'])."',
					`pasp_adres`='".addslashes($v['pasp_adres'])."',
					`pasp_ovd`='".addslashes($v['pasp_ovd'])."',
					`pasp_data`='".addslashes($v['pasp_data'])."',
					`sum`=".$v['sum'].",
					`avans`=".$v['avans'].",
					`cut`='".$v['cut']."',
					`link`='".time().'_dogovor_'.$v['nomer'].'_'.$v['data_create']."'
				WHERE `id`=".$dog['id'];
		query($sql);

		// Обновление начисления по договору
		$sql = "UPDATE `accrual`
				SET `sum`=".$v['sum'].",
					`prim`='Заключение договора №".$v['nomer'].".'
				WHERE `dogovor_id`=".$dog['id'];
		query($sql);

		// Обновление адреса
		$sql = "UPDATE `zayav`
		        SET `adres`='".addslashes($v['adres'])."'
		        WHERE `id`=".$v['zayav_id'];
		query($sql);

		// Обновление паспортных данных клиента
		$sql = "UPDATE `client`
		        SET `pasp_seria`='".addslashes($v['pasp_seria'])."',
					`pasp_nomer`='".addslashes($v['pasp_nomer'])."',
					`pasp_adres`='".addslashes($v['pasp_adres'])."',
					`pasp_ovd`='".addslashes($v['pasp_ovd'])."',
					`pasp_data`='".addslashes($v['pasp_data'])."',
					`fio`='".$v['fio']."'
		        WHERE `id`=".$v['client_id'];
		query($sql);

		// Внесение авансового платежа, если есть
		$avans = query_assoc("SELECT * FROM `money` WHERE `deleted`=0 AND `dogovor_id`=".$dog['id']);
		if($v['avans'] > 0) {
			if(empty($avans))
				income_insert(array(
					'zayav_id' => $v['zayav_id'],
					'dogovor_id' => $dog['id'],
					'sum' => $v['avans'],
					'type' => 1
				));
			elseif($v['avans'] != $avans['sum']) {
				query("UPDATE `money` SET `sum`=".$v['avans']." WHERE `id`=".$avans['id']);
				invoice_history_insert(array(
					'action' => 10,
					'table' => 'money',
					'id' => $avans['id'],
					'sum_prev' => $avans['sum']
				));
			}
		} elseif(!empty($avans)) {
			query("UPDATE `money` SET `deleted`=1 WHERE `deleted`=0 AND `id`=".$avans['id']);
			invoice_history_insert(array(
				'action' => 2,
				'table' => 'money',
				'id' => $avans['id']
			));
		}

		dogovor_print($dog['id']);

		clientBalansUpdate($v['client_id']);
		_zayavBalansUpdate($v['zayav_id']);

		unlink(PATH_DOGOVOR.$dog['link'].'.doc');


		if($v['cut'])
			foreach(explode(',', $v['cut']) as $r) {
				$ex = explode(':', $r);
				$sql = "INSERT INTO `remind` (
							`client_id`,
							`zayav_id`,
							`txt`,
							`day`
						) VALUES (
							".$v['client_id'].",
							".$v['zayav_id'].",
							'".round(str_replace(',', '.', $ex[0]), 2)."',
							'".$ex[1]."'
						)";
				query($sql);
			}
		else
			query("DELETE FROM `remind` WHERE `zayav_id`=".$v['zayav_id']);

		$changes = '';
		if($dog['fio'] != $v['fio'])
			$changes .= '<tr><th>ФИО:<td>'.$dog['fio'].'<td>»<td>'.$v['fio'];
		if($dog['adres'] != $v['adres'])
			$changes .= '<tr><th>Адрес:<td>'.$dog['adres'].'<td>»<td>'.$v['adres'];
		if($dog['pasp_seria'] != $v['pasp_seria'])
			$changes .= '<tr><th>Паспорт серия:<td>'.$dog['pasp_seria'].'<td>»<td>'.$v['pasp_seria'];
		if($dog['pasp_nomer'] != $v['pasp_nomer'])
			$changes .= '<tr><th>Паспорт номер:<td>'.$dog['pasp_nomer'].'<td>»<td>'.$v['pasp_nomer'];
		if($dog['pasp_adres'] != $v['pasp_adres'])
			$changes .= '<tr><th>Паспорт прописка:<td>'.$dog['pasp_adres'].'<td>»<td>'.$v['pasp_adres'];
		if($dog['pasp_ovd'] != $v['pasp_ovd'])
			$changes .= '<tr><th>Паспорт кем выдан:<td>'.$dog['pasp_ovd'].'<td>»<td>'.$v['pasp_ovd'];
		if($dog['pasp_data'] != $v['pasp_data'])
			$changes .= '<tr><th>Паспорт когда выдан:<td>'.$dog['pasp_data'].'<td>»<td>'.$v['pasp_data'];
		if($dog['nomer'] != $v['nomer'])
			$changes .= '<tr><th>Номер:<td>'.$dog['nomer'].'<td>»<td>'.$v['nomer'];
		if($dog['data_create'] != $v['data_create'])
			$changes .= '<tr><th>Дата заключения:<td>'.dogovorData($dog['data_create']).'<td>»<td>'.dogovorData($v['data_create']);
		if($dog['sum'] != $v['sum'])
			$changes .= '<tr><th>Сумма:<td>'.round($dog['sum'], 2).'<td>»<td>'.round($v['sum'], 2);
		if($dog['avans'] != $v['avans'])
			$changes .= '<tr><th>Авансовый платёж:<td>'.round($dog['avans'], 2).'<td>»<td>'.round($v['avans'], 2);
		if($changes)
			history_insert(array(
				'type' => 42,
				'client_id' => $v['client_id'],
				'zayav_id' => $v['zayav_id'],
				'dogovor_id' => $dog['id'],
				'value' => '<table>'.$changes.'</table>'
			));

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

	case 'remind_spisok':
		if(!empty($_POST['day']) && !_calendarDataCheck($_POST['day']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['private']))
			jsonError();
		$send['html'] = utf8(remind_spisok($_POST));
		$send['cal'] = utf8(_calendarFilter(array(
			'month' => $_POST['day'],
			'days' => remind_days(),
			'noweek' => 1,
			'func' => 'remind_days'
		)));
		jsonSuccess($send);
		break;
	case 'remind_status':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']) && !$_POST['id'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']))
			jsonError();
		if(!preg_match(REGEXP_DATE, $_POST['day']) && !$_POST['day'])
			jsonError();
		$id = intval($_POST['id']);
		$day = $_POST['day'];
		$status = intval($_POST['status']);

		if(!$r = query_assoc("SELECT * FROM `remind` WHERE `id`=".$id))
			jsonError();

		//Изменять можно только активные напоминания
		if($r['status'] != 1)
			jsonError();

		if($r['status'] != $status || $status == 1 && $r['day'] != $day) {
			$sql = "UPDATE `remind`
			        SET `status`=".$status."
						".($status == 1 ? ",`day`='".$day."'" : '')."
			        WHERE `id`=".$id;
			query($sql);
			remind_history_add(array(
				'remind_id' => $r['id'],
				'status' => $status,
				'day' => ($status == 1 ? $day : ''),
				'txt' => $_POST['reason']
			));
		}

		$v = array();
		if($_POST['from'] == 'client')
			$v['client_id'] = $r['client_id'];
		if($_POST['from'] == 'zayav')
			$v['zayav_id'] = $r['zayav_id'];
		$send['html'] = utf8(remind_spisok($v));

		jsonSuccess($send);
		break;
	case 'remind_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
			jsonError();
		if(!preg_match(REGEXP_DATE, $_POST['day']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['private']))
			jsonError();

		$client_id = intval($_POST['client_id']);
		$zayav_id = intval($_POST['zayav_id']);
		$txt = win1251(htmlspecialchars(trim($_POST['txt'])));
		$day = $_POST['day'];
		$private = intval($_POST['private']);

		if($zayav_id && !$client_id)
			$client_id = query_value("SELECT `client_id` FROM `zayav` WHERE `id`=".$zayav_id);

		$sql = "INSERT INTO `remind` (
					`client_id`,
					`zayav_id`,
					`txt`,
					`day`,
					`private`,
					`viewer_id_add`
				) VALUES (
					".$client_id.",
					".$zayav_id.",
					'".addslashes($txt)."',
					'".$day."',
					".$private.",
					".VIEWER_ID."
				)";
		query($sql);
		remind_history_add(array(
			'remind_id' => mysql_insert_id(),
			'day' => $day
		));

		$v = array();
		if($_POST['from'] == 'client')
			$v['client_id'] = $client_id;
		if($_POST['from'] == 'zayav')
			$v['zayav_id'] = $zayav_id;
		$send['html'] = utf8(remind_spisok($v));

		jsonSuccess($send);
		break;
	case 'remind_history':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']) && !$_POST['id'])
			jsonError();
		$id = intval($_POST['id']);

		if(!$r = query_assoc("SELECT * FROM `remind` WHERE `id`=".$id))
			jsonError();

		$send['html'] = utf8(remind_history($id));
		jsonSuccess($send);
		break;

	case 'history_spisok':
		if(!RULES_HISTORYSHOW)
			jsonError();
		$send['html'] = utf8(history_spisok($_POST));
		jsonSuccess($send);
		break;

	case 'invoice_set':
		if(!VIEWER_ADMIN)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['invoice_id']))
			jsonError();
		if(!preg_match(REGEXP_CENA, $_POST['sum']))
			jsonError();

		$invoice_id = intval($_POST['invoice_id']);

		$sql = "SELECT * FROM `invoice` WHERE `id`=".$invoice_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$cash = array();
		if($invoice_id != 1 || empty($_POST['cash']))
			$sum = str_replace(',', '.', $_POST['sum']);
		else {
			$sum = 0;
			$ex = explode(':', $_POST['cash']);
			foreach($ex as $x) {
				$r = explode('=', $x);
				if(!preg_match(REGEXP_NUMERIC, $r[0]))
					jsonError();
				if(!preg_match(REGEXP_CENA, $r[1]))
					jsonError();
				$id = intval($r[0]);
				$sql = "SELECT COUNT(`viewer_id`)
				        FROM `vk_user_rules`
				        WHERE `viewer_id`=".$id."
						  AND `rules`='RULES_CASH'
						  AND `value`=1";
				if(!query_value($sql))
					jsonError();
				$s = str_replace(',', '.', $r[1]);
				$sum += $s;
				$cash[$id] = $s;
			}
		}

		$cashHistory = '';
		if(!empty($cash))
			foreach($cash as $id => $s) {
				query("UPDATE `vk_user` SET `cash`="._invoiceBalans($id, $s)." WHERE `viewer_id`=".$id);
				xcache_unset(CACHE_PREFIX.'viewer_'.$id);
				invoice_history_insert(array(
					'action' => 5,
					'worker_id' => $id
				));
				$cashHistory .= '<tr><td>'._viewer($id, 'name').':<td>'.round($s, 2).' руб.';
			}

		query("UPDATE `invoice` SET `start`="._invoiceBalans($invoice_id, $sum)." WHERE `id`=".$invoice_id);
		xcache_unset(CACHE_PREFIX.'invoice');
		invoice_history_insert(array(
			'action' => 5,
			'invoice_id' => $invoice_id
		));

		history_insert(array(
			'type' => 38,
			'value' => $sum,
			'value1' => $invoice_id,
			'value2' => $cashHistory ? '<table>'.$cashHistory.'</table>' : ''
		));

		$cash = cash_spisok();
		$send['c'] = utf8($cash['spisok']);
		$send['i'] = utf8(invoice_spisok());
		jsonSuccess($send);
		break;
	case 'income_choice':
		if(empty($_POST['owner_id']) || !preg_match(REGEXP_NUMERIC, $_POST['owner_id']) || $_POST['owner_id'] < 100)
			jsonError();
		if(!empty($_POST['ids']))
			foreach(explode(',', $_POST['ids']) as $id)
				if(empty($id) || !preg_match(REGEXP_NUMERIC, $id))
					jsonError();
		$_POST['limit'] = 100;
		$_POST['income_id'] = 1;
		$data = income_spisok($_POST);
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'invoice_transfer':
		if(empty($_POST['from']) || !preg_match(REGEXP_NUMERIC, $_POST['from']))
			jsonError();
		if(empty($_POST['to']) || !preg_match(REGEXP_NUMERIC, $_POST['to']))
			jsonError();
		if(!preg_match(REGEXP_CENA, $_POST['sum']) || $_POST['sum'] == 0)
			jsonError();
		$income_count = 0;
		if(!empty($_POST['ids'])) {
			$ex = explode(',', $_POST['ids']);
			$income_count = count($ex);
			foreach($ex as $id)
				if(empty($id) || !preg_match(REGEXP_NUMERIC, $id))
					jsonError();
		}

		$from = intval($_POST['from']);
		$to = intval($_POST['to']);
		$sum = str_replace(',', '.', $_POST['sum']);
		$income_ids = $_POST['ids'];

		if($from == $to)
			jsonError();

		$invoice_from = $from > 100 ? (_viewerRules($from, 'RULES_CASH') ? 1 : 0) : $from;
		$invoice_to = $to > 100 ? (_viewerRules($to, 'RULES_CASH') ? 1 : 0) : $to;
		$sql = "INSERT INTO `invoice_transfer` (
					`invoice_from`,
					`invoice_to`,
					`worker_from`,
					`worker_to`,
					`sum`,
					`income_count`,
					`income_ids`,
					`viewer_id_add`
				) VALUES (
					".$invoice_from.",
					".$invoice_to.",
					".($from > 100 ? $from : 0).",
					".($to > 100  ? $to : 0).",
					".$sum.",
					".$income_count.",
					'".$income_ids."',
					".VIEWER_ID."
				)";
		query($sql);

		invoice_history_insert(array(
			'action' => 4,
			'table' => 'invoice_transfer',
			'id' => mysql_insert_id()
		));

		if($income_ids)
			query("UPDATE `money` SET `owner_id`=".($to > 100 ? $to : 0)." WHERE `id` IN (".$income_ids.")");

		history_insert(array(
			'type' => 39,
			'value' => $sum,
			'value1' => $from,
			'value2' => $to
		));

		$cash = cash_spisok();
		$send['c'] = utf8($cash['spisok']);
		$send['i'] = utf8(invoice_spisok());
		$send['t'] = utf8(transfer_spisok());
		jsonSuccess($send);
		break;
	case 'income_transfer_show':
		if(empty($_POST['ids']))
			jsonError();
		foreach(explode(',', $_POST['ids']) as $id)
			if(empty($id) || !preg_match(REGEXP_NUMERIC, $id))
				jsonError();
		$_POST['limit'] = 100;
		$data = income_spisok($_POST);
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'income_confirm_get':
		$data = income_spisok(array('confirm' => 1, 'limit' => 100));
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'income_confirm':
		if(empty($_POST['ids']))
			jsonError();
		$ids = $_POST['ids'];
		$ex = explode(',', $ids);
		$ass = array();
		foreach($ex as $id) {
			if(empty($id) || !preg_match(REGEXP_NUMERIC, $id))
				jsonError();
			$ass[$id] = 1;
		}
		$sql = "SELECT `id` FROM `money` WHERE !`deleted` AND `confirm` AND `id` IN (".$ids.")";
		$q = query($sql);
		if(count($ex) != mysql_num_rows($q))
			jsonError();
		while($r = mysql_fetch_assoc($q))
			if(!$ass[$r['id']])
				jsonError();

		foreach($ex as $id) {
			query("UPDATE `money` SET `confirm`=0 WHERE `id`=".$id);
			invoice_history_insert(array(
				'action' => 11,
				'table' => 'money',
				'id' => $id
			));
		}

		history_insert(array(
			'type' => 43,
			'value' => count($ex),
			'value1' => $ids
		));

		$send['confirm'] = utf8(income_confirm_info());
		$cash = cash_spisok();
		$send['c'] = utf8($cash['spisok']);
		$send['i'] = utf8(invoice_spisok());
		jsonSuccess($send);
		break;
	case 'transfer_confirm_get':
		if(!VIEWER_ADMIN)
			jsonError();
		$send['html'] = utf8('<div class="transfer-spisok">'.transfer_spisok(array('confirm'=>1)).'</div>');
		jsonSuccess($send);
		break;
	case 'transfer_confirm':
		if(!VIEWER_ADMIN)
			jsonError();
		if(empty($_POST['ids']))
			jsonError();
		$ids = $_POST['ids'];
		$ex = explode(',', $ids);
		$ass = array();
		foreach($ex as $id) {
			if(empty($id) || !preg_match(REGEXP_NUMERIC, $id))
				jsonError();
			$ass[$id] = 1;
		}

		$about = win1251(htmlspecialchars(trim($_POST['about'])));

		$sql = "SELECT `id` FROM `invoice_transfer` WHERE !`deleted` AND !`invoice_to` AND `worker_to` AND !`confirm` AND `id` IN (".$ids.")";
		$q = query($sql);
		if(count($ex) != mysql_num_rows($q))
			jsonError();
		while($r = mysql_fetch_assoc($q))
			if(!$ass[$r['id']])
				jsonError();
		foreach($ex as $id)
			query("UPDATE `invoice_transfer` SET `confirm`=1,`about`='".addslashes($about)."' WHERE `id`=".$id);

		history_insert(array(
			'type' => 52,
			'value' => count($ex),
			'value1' => $ids,
			'value2' => $about
		));

		$send['i'] = utf8(invoice_spisok());
		jsonSuccess($send);
		break;
	case 'transfer_show':
		if(empty($_POST['ids']))
			jsonError();
		foreach(explode(',', $_POST['ids']) as $id)
			if(empty($id) || !preg_match(REGEXP_NUMERIC, $id))
				jsonError();
		$send['html'] = utf8('<div class="transfer-spisok">'.transfer_spisok(array('ids'=>$_POST['ids'])).'</div>');
		jsonSuccess($send);
		break;
	case 'transfer_del':
		if(!VIEWER_ADMIN)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']) || !$_POST['id'])
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT * FROM `invoice_transfer` WHERE !`deleted` AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		query("UPDATE `invoice_transfer` SET `deleted`=1 WHERE `id`=".$id);

		invoice_history_insert(array(
			'action' => 12,
			'table' => 'invoice_transfer',
			'id' => $r['id']
		));

		history_insert(array(
			'type' => 53,
			'value' => round($r['sum'], 2)
		));

		$cash = cash_spisok();
		$send['c'] = utf8($cash['spisok']);
		$send['i'] = utf8(invoice_spisok());
		$send['t'] = utf8(transfer_spisok());
		jsonSuccess($send);
		break;
	case 'transfer_spisok':
		$send['html'] = utf8(transfer_spisok($_POST));
		jsonSuccess($send);
		break;
	case 'invoice_history':
		if(empty($_POST['invoice_id']) || !preg_match(REGEXP_NUMERIC, $_POST['invoice_id']))
			jsonError();
		$send['html'] = utf8(invoice_history($_POST));
		jsonSuccess($send);
		break;

	case 'income_spisok':
		$data = income_spisok($_POST);
		$send['path'] = utf8(income_path($_POST['day']));
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'income_next':
		$data = income_spisok($_POST);
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'income_add':
		$v = array(
			'from' => trim($_POST['from']),
			'prim' => win1251(htmlspecialchars(trim($_POST['prim'])))
		);
		if(!preg_match(REGEXP_NUMERIC, $_POST['type']) || !$_POST['type'])
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['confirm']))
			jsonError();
		if(!preg_match(REGEXP_CENA, $_POST['sum']) || $_POST['sum'] == 0)
			jsonError();
		if(preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
			$v['zayav_id'] = intval($_POST['zayav_id']);
		if(preg_match(REGEXP_NUMERIC, $_POST['client_id']))
			$v['client_id'] = intval($_POST['client_id']);

		$v['type'] = intval($_POST['type']);
		$v['confirm'] = _income($v['type'], 'confirm') ? intval($_POST['confirm']) : 0;
		$v['sum'] = str_replace(',', '.', $_POST['sum']);

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
	case 'income_del':
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

		invoice_history_insert(array(
			'action' => 2,
			'table' => 'money',
			'id' => $id
		));
		clientBalansUpdate($r['client_id']);
		_zayavBalansUpdate($r['zayav_id']);

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
	case 'income_rest':
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

		invoice_history_insert(array(
			'action' => 3,
			'table' => 'money',
			'id' => $id
		));
		clientBalansUpdate($r['client_id']);
		_zayavBalansUpdate($r['zayav_id']);

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

	case 'expense_spisok':
		$data = expense_spisok($_POST);
		$send['html'] = utf8($data['spisok']);
		$send['mon'] = utf8(expenseMonthSum($_POST));
		jsonSuccess($send);
		break;
	case 'expense_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['category']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			jsonError();
		if(empty($_POST['sum']) && !preg_match(REGEXP_CENA, $_POST['sum']))
			jsonError();
		if(empty($_POST['invoice']) || !preg_match(REGEXP_NUMERIC, $_POST['invoice']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['mon']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['year']))
			jsonError();
		$category = intval($_POST['category']);
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		if(!$category && empty($about))
			jsonError();
		$worker = intval($_POST['worker']);
		$invoice = intval($_POST['invoice']);
		$sum = str_replace(',', '.', $_POST['sum']);
		$mon = $category == 1 ? $_POST['year'].'-'.($_POST['mon'] < 10 ? 0 : '').intval($_POST['mon']).'-'.strftime('%d') : '0000-00-00';
		$about = ($category == 1 ? _monthDef($_POST['mon']).' '.$_POST['year'].($about ? ', ' : '') : '').$about;
		$sql = "INSERT INTO `money` (
					`sum`,
					`prim`,
					`invoice_id`,
					`expense_id`,
					`worker_id`,
					`mon`,
					`viewer_id_add`
				) VALUES (
					-".$sum.",
					'".addslashes($about)."',
					".$invoice.",
					".$category.",
					".$worker.",
					'".$mon."',
					".VIEWER_ID."
				)";
		query($sql);

		invoice_history_insert(array(
			'action' => 6,
			'table' => 'money',
			'id' => mysql_insert_id()
		));

		history_insert(array(
			'type' => 32,
			'value' => abs($sum),
			'value1' => $category,
			'value2' => $about,
			'value3' => $worker ? $worker : ''
		));
		jsonSuccess();
		break;
	case 'expense_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT *
				FROM `money`
				WHERE !`deleted`
				  AND `sum`<0
				  AND !`refund`
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `money` SET
					`deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);

		invoice_history_insert(array(
			'action' => 7,
			'table' => 'money',
			'id' => $id
		));

		history_insert(array(
			'type' => 33,
			'value' => round(abs($r['sum']), 2),
			'value1' => $r['expense_id'],
			'value2' => $r['prim'],
			'value3' => $r['worker_id'] ? $r['worker_id'] : ''
		));

		jsonSuccess();
		break;
	case 'expense_rest':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT *
				FROM `money`
				WHERE `deleted`
				  AND `sum`<0
				  AND !`refund`
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `money` SET
					`deleted`=0,
					`viewer_id_del`=0,
					`dtime_del`='0000-00-00 00:00:00'
				WHERE `id`=".$id;
		query($sql);

		invoice_history_insert(array(
			'action' => 8,
			'table' => 'money',
			'id' => $id
		));

		history_insert(array(
			'type' => 34,
			'value' => round(abs($r['sum']), 2),
			'value1' => $r['expense_id'],
			'value2' => $r['prim'],
			'value3' => $r['worker_id'] ? $r['worker_id'] : ''
		));

		jsonSuccess();
		break;
	case 'expense_get':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT
					`sum`*-1 AS `sum`,
					`prim` AS `about`,
					`expense_id` AS `category`,
					`invoice_id` AS `invoice`,
					`worker_id` AS `worker`
				FROM `money`
				WHERE !`deleted`
				  AND `id`=".$id."
				LIMIT 1";
		if(!$send = mysql_fetch_assoc(query($sql)))
			jsonError();
		$send['about'] = utf8($send['about']);
		$send['sum'] = round($send['sum'], 2);
		jsonSuccess($send);
		break;
	case 'expense_edit':
		jsonError();//todo
		if(empty($_POST['id']) && !preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['category']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			jsonError();
		if(empty($_POST['sum']) && !preg_match(REGEXP_NUMERIC, $_POST['sum']))
			jsonError();
		if(empty($_POST['invoice']) || !preg_match(REGEXP_NUMERIC, $_POST['invoice']))
			jsonError();

		$id = intval($_POST['id']);
		$sql = "SELECT *
				FROM `money`
				WHERE `deleted`=0
				  AND `sum`<0
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$category = intval($_POST['category']);
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		if(!$category && empty($about))
			jsonError();
		$worker = intval($_POST['worker']);
		$invoice = intval($_POST['invoice']);
		$sum = intval($_POST['sum']) * -1;


		if($r['expense_id'] != $category ||
		   $r['worker_id'] != $worker ||
		   $r['prim'] != $about ||
		   $r['invoice_id'] != $invoice ||
		   $r['sum'] != $sum) {
			$sql = "UPDATE `money` SET
						`sum`=".$sum.",
						`prim`='".addslashes($about)."',
						`expense_id`=".$category.",
						`invoice_id`=".$invoice.",
						`worker_id`=".$worker."
					WHERE `id`=".$id;
			query($sql);

/*
  Остался нерешённым вопрос когда изменяется счёт, внутренний счёт сотрудника тоже изменяется.
			if($r['sum'] != $sum)
				invoice_history_insert(array(
					'action' => 9,
					'table' => 'money',
					'id' => $id
				));

			if($r['invoice_id'] != $invoice)
				invoice_history_insert(array(
					'action' => 9,
					'invoice_id' => $invoice,
					'table' => 'money',
					'id' => $id
				));
*/
			history_insert(array(
				'type' => 35,
				'value' =>
					'<table>'.
						'<tr><th>Категория:<td>'._expense($r['expense_id']).'<td>»<td>'._expense($category).
						'<tr><th>Сотрудник:<td>'.($r['worker_id'] ? _viewer($r['worker_id'], 'name') : '').'<td>»<td>'.($worker ? _viewer($worker, 'name') : '').
						'<tr><th>Описание:<td>'.$r['prim'].'<td>»<td>'.$about.
						'<tr><th>Со счёта:<td>'._invoice($r['invoice_id']).'<td>»<td>'._invoice($invoice).
						'<tr><th>Сумма:<td>'.round(abs($r['sum']), 2).'<td>»<td>'.round(abs($sum), 2).
					'</table>',
				'value1' => $id,
				'value2' => $r['dtime_add']
			));
		}

		jsonSuccess();
		break;

	case 'salary_rate_set':
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			jsonError();
		if(!preg_match(REGEXP_CENA, $_POST['sum']))
			jsonError();
		if(empty($_POST['day']) || !preg_match(REGEXP_NUMERIC, $_POST['day']) || $_POST['day'] > 28)
			jsonError();

		$worker = intval($_POST['worker']);
		$sum = str_replace(',', '.', $_POST['sum']);
		$day = $sum == 0 ? 0 : intval($_POST['day']);

		$sql = "SELECT * FROM `vk_user` WHERE `worker`=1 AND `viewer_id`=".$worker;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($r['rate'] != $sum || $r['rate_day'] != $day) {
			$sql = "UPDATE `vk_user`
			        SET `rate`=".$sum.",
			            `rate_day`=".$day."
					WHERE `viewer_id`=".$worker;
			query($sql);

			xcache_unset(CACHE_PREFIX.'viewer_'.$worker);

			if($sum)
				history_insert(array(
					'type' => 40,
					'value' => $worker,
					'value1' => $sum,
					'value2' => $day
				));
			else
				history_insert(array(
					'type' => 41,
					'value' => $worker
				));
		}

		jsonSuccess();
		break;
	case 'salary_up':
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			jsonError();
		if(empty($_POST['sum']) || !preg_match(REGEXP_NUMERIC, $_POST['sum']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['mon']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['year']))
			jsonError();
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		$worker = intval($_POST['worker']);
		$sum = intval($_POST['sum']);
		$mon = $_POST['year'].'-'.($_POST['mon'] < 10 ? 0 : '').intval($_POST['mon']);
		$sql = "INSERT INTO `zayav_expense` (
					`worker_id`,
					`sum`,
					`txt`,
					`mon`
				) VALUES (
					".$worker.",
					".$sum.",
					'".addslashes($about)."',
					'".$mon.'-'.strftime('%d')."'
				)";
		query($sql);

		history_insert(array(
			'type' => 36,
			'value' => $sum,
			'value1' => $about,
			'value2' => $worker
		));

		jsonSuccess();
		break;
	case 'salary_down':
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			jsonError();
		if(empty($_POST['sum']) && !preg_match(REGEXP_NUMERIC, $_POST['sum']))
			jsonError();
		if(empty($_POST['invoice']) || !preg_match(REGEXP_NUMERIC, $_POST['invoice']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['mon']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['year']))
			jsonError();
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		$worker = intval($_POST['worker']);
		$invoice = intval($_POST['invoice']);
		$sum = intval($_POST['sum']) * -1;
		$mon = $_POST['year'].'-'.($_POST['mon'] < 10 ? 0 : '').intval($_POST['mon']);
		$about = _monthDef($_POST['mon']).' '.$_POST['year'].($about ? ', ' : '').$about;
		$sql = "INSERT INTO `money` (
					`sum`,
					`prim`,
					`invoice_id`,
					`expense_id`,
					`worker_id`,
					`mon`,
					`viewer_id_add`
				) VALUES (
					".$sum.",
					'".addslashes($about)."',
					".$invoice.",
					1,
					".$worker.",
					'".$mon.'-'.strftime('%d')."',
					".VIEWER_ID."
				)";
		query($sql);

		invoice_history_insert(array(
			'action' => 6,
			'table' => 'money',
			'id' => mysql_insert_id()
		));

		history_insert(array(
			'type' => 37,
			'value' => abs($sum),
			'value1' => $about,
			'value2' => $worker
		));

		jsonSuccess();
		break;
	case 'salary_deduct':
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			jsonError();
		if(empty($_POST['sum']) || !preg_match(REGEXP_NUMERIC, $_POST['sum']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['mon']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['year']))
			jsonError();
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		$worker = intval($_POST['worker']);
		$sum = intval($_POST['sum']);
		$mon = $_POST['year'].'-'.($_POST['mon'] < 10 ? 0 : '').intval($_POST['mon']);
		$sql = "INSERT INTO `zayav_expense` (
					`worker_id`,
					`sum`,
					`txt`,
					`mon`
				) VALUES (
					".$worker.",
					-".$sum.",
					'".addslashes($about)."',
					'".$mon.'-'.strftime('%d')."'
				)";
		query($sql);

		history_insert(array(
			'type' => 44,
			'value' => $sum,
			'value1' => $about,
			'value2' => $worker
		));

		jsonSuccess();
		break;
	case 'salary_start_set':
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			jsonError();
		if(!preg_match(REGEXP_CENA, $_POST['sum']))
			jsonError();

		$worker = intval($_POST['worker']);
		$sum = str_replace(',', '.', $_POST['sum']);

		$sql = "SELECT * FROM `vk_user` WHERE `worker` AND `viewer_id`=".$worker;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sMoney = query_value("
				SELECT IFNULL(SUM(`sum`),0)
				FROM `money`
				WHERE `worker_id`=".$worker."
				  AND `sum`<0
				  AND !`deleted`");
		$sExpense = query_value("
				SELECT IFNULL(SUM(`sum`),0)
				FROM `zayav_expense`
				WHERE `mon`!='0000-00-00'
			      AND `worker_id`=".$worker);
		$start = round($sum - $sMoney - $sExpense, 2);

		query("UPDATE `vk_user` SET `salary_balans_start`=".$start." WHERE `viewer_id`=".$worker);

		xcache_unset(CACHE_PREFIX.'viewer_'.$worker);

		history_insert(array(
			'type' => 45,
			'value' => $worker,
			'value1' => $sum
		));

		$send['html'] = utf8(salary_worker_spisok(array('worker_id'=>$worker)));
		jsonSuccess($send);
		break;
	case 'salary_spisok':
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['year']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['mon']))
			jsonError();

		$worker_id = intval($_POST['worker_id']);
		$year = intval($_POST['year']);
		$mon = intval($_POST['mon']);

		$_POST['mon'] = $year.'-'.($mon < 10 ? 0 : '').$mon;
		$send['html'] = utf8(salary_worker_spisok($_POST));
		$send['month'] = utf8(salary_monthList($_POST));
		jsonSuccess($send);
		break;
	case 'salary_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT *
				FROM `zayav_expense`
				WHERE !`salary_list_id`
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "DELETE FROM `zayav_expense` WHERE `id`=".$id;
		query($sql);

		history_insert(array(
			'type' => $r['sum'] > 0 ? 50 : 51,
			'value' => round(abs($r['sum']), 2),
			'value1' => $r['worker_id']
		));

		jsonSuccess();
		break;
	case 'salary_list_create':
		if(empty($_POST['worker_id']) || !preg_match(REGEXP_NUMERIC, $_POST['worker_id']))
			jsonError();
		if(empty($_POST['mon']) || !preg_match(REGEXP_NUMERIC, $_POST['mon']) || $_POST['mon'] > 12)
			jsonError();
		if(empty($_POST['year']) || !preg_match(REGEXP_NUMERIC, $_POST['year']))
			jsonError();
		if(empty($_POST['ids']))
			jsonError();
		foreach(explode(',', $_POST['ids']) as $id)
			if(empty($id) || !preg_match(REGEXP_NUMERIC, $id))
				jsonError();
		if(empty($_POST['sum']) || !preg_match(REGEXP_INTEGER, $_POST['sum']))
			jsonError();

		$worker_id = intval($_POST['worker_id']);
		$mon = intval($_POST['year']).'-'.($_POST['mon'] < 10 ? 0 : '').$_POST['mon'].'-01';
		$ids = $_POST['ids'];
		$sum = intval($_POST['sum']);

		$sql = "SELECT COUNT(*)
				FROM `zayav_expense`
				WHERE `salary_list_id`
				  AND `id` IN (".$ids.")";
		if(query_value($sql))
			jsonError();

		$sql = "INSERT INTO `salary_list` (
					`worker_id`,
					`ids`,
					`sum`,
					`mon`,
					`viewer_id_add`
				) VALUES (
					".$worker_id.",
					'".$ids."',
					".$sum.",
					'".$mon."',
					".VIEWER_ID."
				)";
		query($sql);

		$sql = "UPDATE `zayav_expense` SET `salary_list_id`=".mysql_insert_id()." WHERE `id` IN (".$ids.")";
		query($sql);

		$ex = explode('-', $mon);
		history_insert(array(
			'type' => 54,
			'value' => $sum,
			'value1' => $worker_id,
			'value2' => $ex[0].'-'.$ex[1],
			'value3' => _monthDef($ex[1], 1).' '.$ex[0]
		));

		jsonSuccess();
		break;
	case 'salary_list_del':
		if(!VIEWER_ADMIN)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT * FROM `salary_list` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		query("DELETE FROM `salary_list` WHERE `id`=".$id);
		query("UPDATE `zayav_expense` SET `salary_list_id`=0 WHERE `id` IN (".$r['ids'].")");

		$ex = explode('-', $r['mon']);
		history_insert(array(
			'type' => 55,
			'value' => $r['sum'],
			'value1' => $r['worker_id'],
			'value2' => $ex[0].'-'.$ex[1],
			'value3' => _monthDef($ex[1], 1).' '.$ex[0]
		));

		jsonSuccess();
		break;

	case 'pin_enter':
		xcache_unset(PIN_TIME_KEY);
		$key = CACHE_PREFIX.'pin_enter_count'.VIEWER_ID;
		$count = xcache_get($key);
		if(empty($count))
			$count = 0;
		if($count > 4) {
			$send = array(
				'max' => 1,
				'text' => utf8('Превышено максимальное количество попыток ввода.<br />'.
							   'Продолжить ввод можно будет через 30 минут.<br /><br />'.
							   'Если вы забыли свой пин-код, обратитесь к руководителю для его сброса.')
			);
			jsonError($send);
		}
		xcache_set($key, ++$count, 1800);
		$pin = win1251(htmlspecialchars(trim($_POST['pin'])));
		if(!$pin || strlen($pin) < 3 || strlen($pin) > 10)
			jsonError('Некорректный ввод пин-кода');
		if(!query_value("SELECT COUNT(*) FROM `vk_user` WHERE `pin`='".$pin."' AND `viewer_id`=".VIEWER_ID))
			jsonError('Неверный пин-код');
		xcache_unset($key);
		xcache_set(PIN_TIME_KEY, time(), 10800);
		jsonSuccess();
		break;
	case 'setup_my_pinset':
		$pin = win1251(htmlspecialchars(trim($_POST['pin'])));
		if(PIN || !$pin || strlen($pin) < 3 || strlen($pin) > 10)
			jsonError();
		query("UPDATE `vk_user` SET `pin`='".$pin."' WHERE `viewer_id`=".VIEWER_ID);
		xcache_unset(CACHE_PREFIX.'viewer_'.VIEWER_ID);
		xcache_unset(PIN_TIME_KEY);
		jsonSuccess();
		break;
	case 'setup_my_pinchange':
		if(!PIN)
			jsonError();
		$oldpin = win1251(htmlspecialchars(trim($_POST['oldpin'])));
		$pin = win1251(htmlspecialchars(trim($_POST['pin'])));
		if(!$oldpin || strlen($oldpin) < 3 || strlen($oldpin) > 10)
			jsonError();
		if(!$pin || strlen($pin) < 3 || strlen($pin) > 10)
			jsonError();
		if(_viewer(VIEWER_ID, 'pin') != $oldpin)
			jsonError('Неверный старый пин-код');
		query("UPDATE `vk_user` SET `pin`='".$pin."' WHERE `viewer_id`=".VIEWER_ID);
		xcache_unset(CACHE_PREFIX.'viewer_'.VIEWER_ID);
		xcache_unset(PIN_TIME_KEY);
		jsonSuccess();
		break;
	case 'setup_my_pindel':
		if(!PIN)
			jsonError();
		$oldpin = win1251(htmlspecialchars(trim($_POST['oldpin'])));
		if(!$oldpin || strlen($oldpin) < 3 || strlen($oldpin) > 10)
			jsonError();
		if(_viewer(VIEWER_ID, 'pin') != $oldpin)
			jsonError('Неверный старый пин-код');
		query("UPDATE `vk_user` SET `pin`='' WHERE `viewer_id`=".VIEWER_ID);
		xcache_unset(CACHE_PREFIX.'viewer_'.VIEWER_ID);
		xcache_unset(PIN_TIME_KEY);
		jsonSuccess();
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

		GvaluesCreate();

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
		GvaluesCreate();

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
		$middle_name = win1251(htmlspecialchars(trim($_POST['middle_name'])));
		$post = win1251(htmlspecialchars(trim($_POST['post'])));

		if(!$first_name || !$last_name)
			jsonError();

		$sql = "SELECT * FROM `vk_user` WHERE `worker`=1 AND `viewer_id`=".$viewer_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		query("UPDATE `vk_user`
		       SET `first_name`='".addslashes($first_name)."',
		           `last_name`='".addslashes($last_name)."',
		           `middle_name`='".addslashes($middle_name)."',
		           `post`='".addslashes($post)."'
		       WHERE `viewer_id`=".$viewer_id);
		xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
		GvaluesCreate();

		$changes = '';
		if($r['first_name'] != $first_name)
			$changes .= '<tr><th>Имя:<td>'.$r['first_name'].'<td>»<td>'.$first_name;
		if($r['last_name'] != $last_name)
			$changes .= '<tr><th>Фамилия:<td>'.$r['last_name'].'<td>»<td>'.$last_name;
		if($r['middle_name'] != $middle_name)
			$changes .= '<tr><th>Отчество:<td>'.$r['middle_name'].'<td>»<td>'.$middle_name;
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
	case 'setup_worker_pinclear':
		if(!VIEWER_ADMIN)
			jsonError();
		if(!RULES_WORKER)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();

		$viewer_id = intval($_POST['viewer_id']);
		$sql = "SELECT *
				FROM `vk_user`
				WHERE `worker`=1
				  AND `admin`=0
				  AND `pin`!=''
				  AND `viewer_id`=".$viewer_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		query("UPDATE `vk_user` SET `pin`='' WHERE `viewer_id`=".$viewer_id);
		xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
		jsonSuccess();
		break;
	case 'setup_worker_dop_save':
		if(!RULES_WORKER)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();

		$viewer_id = intval($_POST['viewer_id']);

		$u = _viewer($viewer_id);
		if(!$u['worker'])
			jsonError();

		setup_worker_rules_save($_POST, $viewer_id);
		jsonSuccess();
		break;
	case 'setup_worker_rules_save':
		if(!RULES_WORKER || !RULES_RULES)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();

		$viewer_id = intval($_POST['viewer_id']);

		$u = _viewer($viewer_id);
		if($u['admin'])
			jsonError();
		if(!$u['worker'])
			jsonError();

		setup_worker_rules_save($_POST, $viewer_id);
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
		if(!RULES_INCOME)
			jsonError();
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

		xcache_unset(CACHE_PREFIX.'invoice');
		GvaluesCreate();

		history_insert(array(
			'type' => 515,
			'value' => $name
		));


		$send['html'] = utf8(setup_invoice_spisok());
		jsonSuccess($send);
		break;
	case 'setup_invoice_edit':
		if(!RULES_INCOME)
			jsonError();
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


		xcache_unset(CACHE_PREFIX.'invoice');
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
		if(!RULES_INCOME)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$invoice_id = intval($_POST['id']);

		$sql = "SELECT * FROM `invoice` WHERE `id`=".$invoice_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		query("DELETE FROM `invoice` WHERE `id`=".$invoice_id);
		query("UPDATE `setup_income` SET `invoice_id`=0 WHERE `invoice_id`=".$invoice_id);

		xcache_unset(CACHE_PREFIX.'invoice');
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
		if(!preg_match(REGEXP_BOOL, $_POST['confirm']))
			jsonError();
		$confirm = intval($_POST['confirm']);

		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();
		$sql = "INSERT INTO `setup_income` (
					`name`,
					`confirm`,
					`sort`
				) VALUES (
					'".addslashes($name)."',
					".$confirm.",
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
		if(!preg_match(REGEXP_BOOL, $_POST['confirm']))
			jsonError();

		$id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$confirm = intval($_POST['confirm']);

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `setup_income` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `setup_income`
				SET `name`='".addslashes($name)."',
					`confirm`=".$confirm."
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'income');
		GvaluesCreate();

		$changes = '';
		if($r['name'] != $name)
			$changes .= '<tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name;
		if($r['confirm'] != $confirm)
			$changes .= '<tr><th>Подтверждение поступления на счёт:<td>'.($r['confirm'] ? 'да' : 'нет').'<td>»<td>'.($confirm ? 'да' : 'нет');
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
	case 'setup_expense_add':
//		if(!RULES_ZAYAVRASHOD)
//			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['show_worker']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$show_worker = intval($_POST['show_worker']);

		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `setup_expense` (
					`name`,
					`show_worker`,
					`sort`
				) VALUES (
					'".addslashes($name)."',
					".$show_worker.",
					"._maxSql('setup_expense', 'sort')."
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'expense');
		GvaluesCreate();

		history_insert(array(
			'type' => 518,
			'value' => $name
		));


		$send['html'] = utf8(setup_expense_spisok());
		jsonSuccess($send);
		break;
	case 'setup_expense_edit':
//		if(!RULES_ZAYAVRASHOD)
//			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['show_worker']))
			jsonError();

		$id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$show_worker = intval($_POST['show_worker']);

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `setup_expense` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `setup_expense`
				SET `name`='".addslashes($name)."',
					`show_worker`=".$show_worker."
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'expense');
		GvaluesCreate();

		$changes = '';
		if($r['name'] != $name)
			$changes .= '<tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name;
		if($r['show_worker'] != $show_worker)
			$changes .= '<tr><th>Список сотрудников:<td>'.($r['show_worker'] ? 'да' : 'нет').'<td>»<td>'.($show_worker ? 'да' : 'нет');
		if($changes)
			history_insert(array(
				'type' => 519,
				'value' => $name,
				'value1' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(setup_expense_spisok());
		jsonSuccess($send);
		break;
	case 'setup_expense_del':
//		if(!RULES_ZAYAVRASHOD)
//			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT * FROM `setup_expense` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `money` WHERE `expense_id`=".$id))
			jsonError();
		$sql = "DELETE FROM `setup_expense` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'expense');
		GvaluesCreate();

		history_insert(array(
			'type' => 520,
			'value' => $r['name']
		));

		$send['html'] = utf8(setup_expense_spisok());
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

		$sql = "INSERT INTO `setup_zayavexpense` (
					`name`,
					`show_txt`,
					`show_worker`,
					`sort`
				) VALUES (
					'".addslashes($name)."',
					".$show_txt.",
					".$show_worker.",
					"._maxSql('setup_zayavexpense', 'sort')."
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'zayavrashod');
		GvaluesCreate();

		history_insert(array(
			'type' => 511,
			'value' => $name
		));


		$send['html'] = utf8(setup_zayavexpense_spisok());
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

		$sql = "SELECT * FROM `setup_zayavexpense` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `setup_zayavexpense`
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

		$send['html'] = utf8(setup_zayavexpense_spisok());
		jsonSuccess($send);
		break;
	case 'setup_zayavrashod_del':
		if(!RULES_ZAYAVRASHOD)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT * FROM `setup_zayavexpense` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `zayav_expense` WHERE `category_id`=".$id))
			jsonError();
		$sql = "DELETE FROM `setup_zayavexpense` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'zayavrashod');
		GvaluesCreate();

		history_insert(array(
			'type' => 513,
			'value' => $r['name']
		));

		$send['html'] = utf8(setup_zayavexpense_spisok());
		jsonSuccess($send);
		break;
}

jsonError();