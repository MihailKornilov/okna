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

	case 'oplata_add':
		$v = array(
			'from' => trim($_POST['from']),
			'prim' => win1251(htmlspecialchars(trim($_POST['prim'])))
		);
		if(!preg_match(REGEXP_NUMERIC, $_POST['type']) || $_POST['type'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['kassa']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) || $_POST['sum'] == 0)
			jsonError();
		if(preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
			$v['zayav_id'] = intval($_POST['zayav_id']);
		if(preg_match(REGEXP_NUMERIC, $_POST['client_id']))
			$v['client_id'] = intval($_POST['client_id']);

		$v['type'] = intval($_POST['type']);
		$v['kassa'] = intval($_POST['kassa']);
		$v['sum'] = intval($_POST['sum']);

		$send = money_insert($v);
		if(empty($send))
			jsonError();
		$send['spisok'] = utf8($send['spisok']);
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
		if($r['dogovor_nomer'])
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
			'value' => $r['sum'],
			'value1' => $r['prim'],
			'value2' => $r['prihod_type']
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
			'value' => $r['sum'],
			'value1' => $r['prim'],
			'value2' => $r['prihod_type']
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
			if($r['telefon'] || $r['adres'])
				$unit['content'] = utf8($r['fio'].'<div class="pole2">'.
					$r['telefon'].
					($r['adres'] ? '<br />'.$r['adres'] : '').
				'</div>');
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
		query("UPDATE `zayav` SET `status`=0 WHERE `client_id`=".$client_id);
		query("UPDATE `money` SET `deleted`=1 WHERE `client_id`=".$client_id);
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

		$product = product_spisok_test($_POST['product']);
		if(!$product)
			jsonError();

		$client_id = intval($_POST['client_id']);
		$zamer_dtime = $_POST['zamer_day'].' '.$_POST['zamer_hour'].':'.$_POST['zamer_min'].':00';
		$zamer_duration = intval($_POST['zamer_duration']);
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$comm = win1251(htmlspecialchars(trim($_POST['comm'])));

		if(empty($adres))
			jsonError();

		$nomer = _maxSql('zayav', 'zamer_nomer');
		$sql = "INSERT INTO `zayav` (
					`client_id`,
					`zamer_nomer`,
					`zamer_status`,
					`zamer_dtime`,
					`zamer_duration`,
					`adres`,
					`viewer_id_add`
				) VALUES (
					".$client_id.",
					".$nomer.",
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
			'zayav_id' => $send['id'],
			'value' => $nomer
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
						'zayav_id' => $zayav_id,
						'value' => $zayav['zamer_nomer'],
					));
				if($zayav['zamer_dtime'] != $zamer_dtime || $zayav['zamer_duration'] != $zamer_duration)
					history_insert(array(
						'type' => 15,
						'client_id' => $zayav['client_id'],
						'zayav_id' => $zayav_id,
						'value' => $zayav['zamer_nomer'],
						'value1' => '<table>'.
										'<tr><td>'.FullDataTime($zayav['zamer_dtime']).', '._zamerDuration($zayav['zamer_duration']).
											'<td>»'.
											'<td>'.FullDataTime($zamer_dtime).', '._zamerDuration($zamer_duration).
									'</table>'
					));
				break;
			case 2:
				if($zayav['zamer_status'] != 2) {
					$sql = "UPDATE `zayav` SET `zamer_status`=2,`dogovor_require`=1 WHERE `id`=".$zayav_id;
					query($sql);
					history_insert(array(
						'type' => 16,
						'client_id' => $zayav['client_id'],
						'zayav_id' => $zayav_id,
						'value' => $zayav['zamer_nomer']
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
						'zayav_id' => $zayav_id,
						'value' => $zayav['zamer_nomer']
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
		$product = product_spisok_test($_POST['product']);
		if(!$product)
			jsonError();
		if(empty($adres))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

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
				'value' => $zayav['zamer_nomer'],
				'value1' => '<table>'.$changes.'</table>'
			));
		jsonSuccess();
		break;
	case 'dog_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$product = product_spisok_test($_POST['product']);
		if(!$product)
			jsonError();
		if(empty($adres))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `dogovor_nomer`=0 AND `id`=".$zayav_id." LIMIT 1";
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
				'value' => $zayav['set_nomer'],
				'value1' => '<table>'.$changes.'</table>'
			));
		jsonSuccess();
		break;
	case 'set_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) || $_POST['client_id'] == 0)
			jsonError();
		$product = product_spisok_test($_POST['product']);
		if(!$product)
			jsonError();

		$client_id = intval($_POST['client_id']);
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$comm = win1251(htmlspecialchars(trim($_POST['comm'])));

		if(empty($adres))
			jsonError();

		$nomer = _maxSql('zayav', 'set_nomer');
		$sql = "INSERT INTO `zayav` (
					`client_id`,
					`set_nomer`,
					`set_status`,
					`adres`,
					`viewer_id_add`
				) VALUES (
					".$client_id.",
					".$nomer.",
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
			'zayav_id' => $send['id'],
			'value' => $nomer
		));
		jsonSuccess($send);
		break;
	case 'set_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$nomer_vg = win1251(htmlspecialchars(trim($_POST['nomer_vg'])));
		$product = product_spisok_test($_POST['product']);
		if(!$product)
			jsonError();
		if(empty($adres))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `set_nomer`>0 AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `zayav`
		        SET `adres`='".addslashes($adres)."',
		            `nomer_vg`='".addslashes($nomer_vg)."'
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
		if($changes)
			history_insert(array(
				'type' => 22,
				'zayav_id' => $zayav_id,
				'value' => $zayav['set_nomer'],
				'value1' => '<table>'.$changes.'</table>'
			));
		jsonSuccess();
		break;
	case 'zayav_spisok_load':
		$_POST['find'] = win1251($_POST['find']);
		$data = zayav_data(1, zayavfilter($_POST));
		$send['all'] = utf8(zayav_count($data['all']));
		$send['html'] = utf8(zayav_spisok($data));
		jsonSuccess($send);
		break;
	case 'zayav_next':
		$_POST['find'] = win1251($_POST['find']);
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$send['html'] = utf8(zayav_spisok(zayav_data(intval($_POST['page']), zayavfilter($_POST))));
		jsonSuccess($send);
		break;
	case 'zayav_delete':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
			jsonError();
		$zayav_id = intval($_POST['zayav_id']);
		$sql = "SELECT * FROM `zayav` WHERE `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "SELECT IFNULL(SUM(`sum`),0) AS `acc`
				FROM `accrual`
				WHERE `status`=1
				  AND `zayav_id`=".$zayav_id."
				LIMIT 1";
		if(query_value($sql) != 0)
			jsonError();

		$sql = "SELECT IFNULL(SUM(`sum`),0) AS `opl`
				FROM `money`
				WHERE `deleted`=0
				  AND `sum`>0
				  AND `zayav_id`=".$zayav_id."
				LIMIT 1";
		if(query_value($sql) != 0)
			jsonError();

		$sql = "UPDATE `zayav` SET `status`=0 WHERE `id`=".$zayav_id;
		query($sql);

		history_insert(array(
			'type' => 6,
			'zayav_id' => $zayav_id
		));

		$send['client_id'] = $zayav['client_id'];
		jsonSuccess($send);
		break;
	case 'zayav_money_update':
		//Получение разницы между начислениями и платежами и их обновление
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT IFNULL(SUM(`sum`),0) AS `acc`
				FROM `accrual`
				WHERE `status`=1
				  AND `zayav_id`=".$id;
		$send = mysql_fetch_assoc(query($sql));
		$sql = "SELECT IFNULL(SUM(`sum`),0) AS `opl`
				FROM `money`
				WHERE `deleted`=0
				  AND `sum`>0
				  AND `zayav_id`=".$id;
		$r = mysql_fetch_assoc(query($sql));
		$send['opl'] = $r['opl'];
		$send['dopl'] = $send['acc'] - $r['opl'];
		jsonSuccess($send);
		break;
	case 'zayav_accrual_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) || $_POST['sum'] == 0)
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$sum = intval($_POST['sum']);
		$prim = win1251(htmlspecialchars(trim($_POST['prim'])));

		$sql = "SELECT *
				FROM `zayav`
				WHERE `status`>0
				  AND `id`=".$zayav_id;
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
			'value' => $sum,
			'value1' => $prim
		));

		$send['html'] = utf8(zayav_accrual_unit(array(
			'id' => mysql_insert_id(),
			'sum' => $sum,
			'prim' => $prim,
		)));

		jsonSuccess($send);
		break;
	case 'zayav_accrual_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT *
				FROM `accrual`
				WHERE `status`>0
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();


		$sql = "UPDATE `accrual` SET
					`status`=0,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($r['client_id']);

		history_insert(array(
			'type' => 8,
			'value' => $r['sum'],
			'value1' => $r['prim'],
			'zayav_id' => $r['zayav_id']
		));
		jsonSuccess();
		break;
	case 'zayav_accrual_rest':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT *
				FROM `accrual`
				WHERE `status`=0
				  AND `id`=".$id;
		if(!$acc = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `accrual` SET
					`status`=1,
					`viewer_id_del`=0,
					`dtime_del`='0000-00-00 00:00:00'
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($acc['client_id']);

		history_insert(array(
			'type' => 9,
			'value' => $acc['sum'],
			'value1' => $acc['prim'],
			'zayav_id' => $acc['zayav_id']
		));
		$send['html'] = utf8(zayav_accrual_unit($acc));
		jsonSuccess($send);
		break;
	case 'dogovor_preview':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0) {
			echo 'Ошибка: неверный номер заявки.';
			exit;
		}
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) && $_POST['sum'] == 0) {
			echo 'Ошибка: некорректно указана сумма по договору.';
			exit;
		}
		if(!empty($_POST['avans']) && !preg_match(REGEXP_NUMERIC, $_POST['avans'])) {
			echo 'Ошибка: некорректно указан авансовый платёж.';
			exit;
		}
		$zayav_id = intval($_POST['zayav_id']);
		$v = array(
			'id' => _maxSql('zayav_dogovor', 'id'),
			'fio' => htmlspecialchars(trim($_POST['fio'])),
			'adres' => htmlspecialchars(trim($_POST['adres'])),
			'sum' => intval($_POST['sum']),
			'avans' => intval($_POST['avans']),
			'dtime_add' => curTime()
		);
		$pasp = array(
			'pasp_seria' => htmlspecialchars(trim($_POST['pasp_seria'])),
			'pasp_nomer' => htmlspecialchars(trim($_POST['pasp_nomer'])),
			'pasp_adres' => htmlspecialchars(trim($_POST['pasp_adres'])),
			'pasp_ovd' => htmlspecialchars(trim($_POST['pasp_ovd'])),
			'pasp_data' => htmlspecialchars(trim($_POST['pasp_data'])),
			'pasp_empty' => 0
		);
		foreach($pasp as $k => $p)
			if(empty($p) && $k != 'pasp_empty') {
				$pasp['pasp_empty'] = 1;
				break;
			}
		$v += $pasp;
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
		dogovor_print($v);
		exit;
	case 'dogovor_create':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) && $_POST['sum'] == 0)
			jsonError();
		if(!empty($_POST['avans']) && !preg_match(REGEXP_NUMERIC, $_POST['avans']))
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$fio = win1251(htmlspecialchars(trim($_POST['fio'])));
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		$reason = win1251(htmlspecialchars(trim($_POST['reason'])));
		$pasp = array(
			'pasp_seria' => win1251(htmlspecialchars(trim($_POST['pasp_seria']))),
			'pasp_nomer' => win1251(htmlspecialchars(trim($_POST['pasp_nomer']))),
			'pasp_adres' => win1251(htmlspecialchars(trim($_POST['pasp_adres']))),
			'pasp_ovd' => win1251(htmlspecialchars(trim($_POST['pasp_ovd']))),
			'pasp_data' => win1251(htmlspecialchars(trim($_POST['pasp_data']))),
			'pasp_empty' => 0
		);
		$sum = intval($_POST['sum']);
		$avans = intval($_POST['avans']);
		if(empty($fio) || empty($adres))
			jsonError();

		foreach($pasp as $k => $p)
			if(empty($p) && $k != 'pasp_empty') {
				$pasp['pasp_empty'] = 1;
				break;
			}

		if($sum < $avans)
			jsonError();

		$sql = "SELECT *
				FROM `zayav`
				WHERE `id`=".$zayav_id."
				  AND `zamer_status`!=1
				  AND `zamer_status`!=3
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "SELECT * FROM `zayav_dogovor` WHERE `deleted`=0 AND `zayav_id`=".$zayav_id;
		if($dog = mysql_fetch_assoc(query($sql))) {
			query("UPDATE `zayav_dogovor` SET `deleted`=1 WHERE `id`=".$dog['id']);
			query("UPDATE `money` SET `deleted`=1 WHERE `dogovor_nomer`=".$dog['id']);
		}

		$sql = "INSERT INTO `zayav_dogovor` (
					`zayav_id`,
					`client_id`,
					`fio`,
					`adres`,
					`pasp_empty`,
					`pasp_seria`,
					`pasp_nomer`,
					`pasp_adres`,
					`pasp_ovd`,
					`pasp_data`,
					`sum`,
					`avans`,
					`reason`,
					`viewer_id_add`
				) VALUES (
					".$zayav_id.",
					".$zayav['client_id'].",
					'".addslashes($fio)."',
					'".addslashes($adres)."',
					".$pasp['pasp_empty'].",
					'".addslashes($pasp['pasp_seria'])."',
					'".addslashes($pasp['pasp_nomer'])."',
					'".addslashes($pasp['pasp_adres'])."',
					'".addslashes($pasp['pasp_ovd'])."',
					'".addslashes($pasp['pasp_data'])."',
					".$sum.",
					".$avans.",
					'".addslashes($reason)."',
					".VIEWER_ID."
				)";
		query($sql);

		$dog_id = mysql_insert_id();

		// Перевод заявки в режим "Установка"
		$sql = "UPDATE `zayav`
		        SET `dogovor_nomer`=".$dog_id.",
					`adres`='".addslashes($adres)."',
		          ".(!$zayav['dogovor_nomer'] ? "`set_nomer`="._maxSql('zayav', 'set_nomer')."," : '')."
					`set_status`=1
		        WHERE `id`=".$zayav_id;
		query($sql);

		// Обновление паспортных данных клиента
		$sql = "UPDATE `client`
		        SET ".($pasp['pasp_empty'] ? '' : "
		            `pasp_seria`='".addslashes($pasp['pasp_seria'])."',
					`pasp_nomer`='".addslashes($pasp['pasp_nomer'])."',
					`pasp_adres`='".addslashes($pasp['pasp_adres'])."',
					`pasp_ovd`='".addslashes($pasp['pasp_ovd'])."',
					`pasp_data`='".addslashes($pasp['pasp_data'])."',")."
					`fio`='".$fio."'
		        WHERE `id`=".$zayav['client_id'];
		query($sql);

		dogovor_print($dog_id);

		history_insert(array(
			'type' => 19,
			'client_id' => $zayav['client_id'],
			'zayav_id' => $zayav_id,
			'value' => $zayav['zamer_nomer'],
			'value1' => $dog_id,
			'value2' => strftime('%d/%m/%Y', time()),
			'value3' => $sum
		));

		// Внесение авансового платежа, если есть
		if($avans) {
			$sql = "INSERT INTO `money` (
						`zayav_id`,
						`client_id`,
						`dogovor_nomer`,
						`sum`,
						`prihod_type`,
						`kassa`,
						`viewer_id_add`
					) VALUES (
						".$zayav_id.",
						".$zayav['client_id'].",
						".$dog_id.",
						".$avans.",
						1,
						1,
						".VIEWER_ID."
					)";
			query($sql);
			history_insert(array(
				'type' => 20,
				'client_id' => $zayav['client_id'],
				'zayav_id' => $zayav_id,
				'value' => $zayav['zamer_nomer'],
				'value1' => $dog_id,
				'value2' => $avans
			));
		}

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

		query("UPDATE `zayav`
			   SET
			    ".(!$zayav['set_nomer'] ? "`set_status`=1,`set_nomer`="._maxSql('zayav', 'set_nomer')."," : '')."
			      `dogovor_require`=0
			   WHERE `id`=".$zayav_id);
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

		query("UPDATE `zayav`
			   SET `dogovor_require`=1
			   WHERE `id`=".$zayav_id);
		jsonSuccess();
		break;

	case 'remind_day':
		if(!preg_match(REGEXP_DATE, $_POST['day']))
			jsonError();
		$send['html'] = utf8(remind_spisok(1, $_POST));
		jsonSuccess($send);
		break;

	case 'report_history_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
/*		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			$_POST['worker'] = 0;
		if(!preg_match(REGEXP_NUMERIC, $_POST['action']))
			$_POST['action'] = 0;*/
		$page = intval($_POST['page']);
		$send['html'] = utf8(report_history_spisok($page));
		jsonSuccess($send);
		break;

	case 'money_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$page = intval($_POST['page']);
		$data = money_spisok($page, $_POST);
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;

	case 'setup_worker_add':
		if(!RULES_WORKER)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$viewer_id = intval($_POST['id']);
		$sql = "SELECT `worker` FROM `vk_user` WHERE `viewer_id`=".$viewer_id." LIMIT 1";
		if(query_value($sql))
			jsonError('Этот пользователь уже является</br >сотрудником.');
		_viewer($viewer_id);
		query("UPDATE `vk_user` SET `worker`=1 WHERE `viewer_id`=".$viewer_id);
		xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);

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
			unset($rules['RULES_PRIHODTYPE']);
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
	case 'setup_prihodtype_add':
		if(!RULES_PRIHODTYPE)
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['kassa_put']))
			jsonError();
		$kassa_put = intval($_POST['kassa_put']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();
		$sort = query_value("SELECT IFNULL(MAX(`sort`)+1,0) FROM `setup_prihodtype`");
		$sql = "INSERT INTO `setup_prihodtype` (
					`name`,
					`kassa_put`,
					`sort`,
					`viewer_id_add`
				) VALUES (
					'".addslashes($name)."',
					".$kassa_put.",
					".$sort.",
					".VIEWER_ID."
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'prihodtype');
		GvaluesCreate();

		history_insert(array(
			'type' => 507,
			'value' => $name
		));


		$send['html'] = utf8(setup_prihodtype_spisok());
		jsonSuccess($send);
		break;
	case 'setup_prihodtype_edit':
		if(!RULES_PRIHODTYPE)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['kassa_put']))
			jsonError();
		$id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$kassa_put = intval($_POST['kassa_put']);
		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `setup_prihodtype` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `setup_prihodtype`
				SET `name`='".addslashes($name)."',
					`kassa_put`=".$kassa_put."
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'prihodtype');
		GvaluesCreate();

		$changes = '';
		if($r['name'] != $name)
			$changes .= '<tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name;
		if($r['kassa_put'] != $kassa_put)
			$changes .= '<tr><th>Возможность внесения в кассу:<td>'.($r['kassa_put'] ? 'да' : 'нет').'<td>»<td>'.($kassa_put ? 'да' : 'нет');
		if($changes)
			history_insert(array(
				'type' => 508,
				'value' => $name,
				'value1' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(setup_prihodtype_spisok());
		jsonSuccess($send);
		break;
	case 'setup_prihodtype_del':
		if(!RULES_PRIHODTYPE)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		// Нельзя удалить наличный платёж
		if($id == 1)
			jsonError();

		$sql = "SELECT * FROM `setup_prihodtype` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `money` WHERE `prihod_type`=".$id))
			jsonError();
		$sql = "DELETE FROM `setup_prihodtype` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'prihodtype');
		GvaluesCreate();

		history_insert(array(
			'type' => 509,
			'value' => $r['name']
		));

		$send['html'] = utf8(setup_prihodtype_spisok());
		jsonSuccess($send);
		break;
}

jsonError();