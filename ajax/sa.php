<?php
require_once('config.php');
if(!SA) jsonError();
require_once(DOCUMENT_ROOT.'/view/main.php');
require_once(DOCUMENT_ROOT.'/view/sa.php');

switch(@$_POST['op']) {
	case 'client_balans':
		$sql = "SELECT
				  `c`.`id`,
				  `c`.`balans`,
				  IFNULL(SUM(`m`.`sum`),0) AS `money`
				FROM `client` AS `c`
				  LEFT JOIN `money` AS `m`
				  ON !`m`.`deleted`
					AND `c`.`id`=`m`.`client_id`
					AND `m`.`sum`>0
				WHERE !`c`.`deleted`
				GROUP BY `c`.`id`
				ORDER BY `c`.`id`";
		$q = query($sql);
		$client = array();
		while($r = mysql_fetch_assoc($q))
			$client[$r['id']] = $r;
		$sql = "SELECT
				  `c`.`id`,
				  IFNULL(SUM(`a`.`sum`),0) AS `acc`
				FROM `client` AS `c`
				  LEFT JOIN `accrual` AS `a`
				  ON !`a`.`deleted`
					AND `c`.`id`=`a`.`client_id`
				WHERE !`c`.`deleted`
				GROUP BY `c`.`id`
				ORDER BY `c`.`id`";
		$q = query($sql);
		$send['count'] = 0;
		$upd = array();
		while($r = mysql_fetch_assoc($q)) {
			$balans = round($client[$r['id']]['money'] - $r['acc'], 2);
			if(round($client[$r['id']]['balans'], 2) != $balans) {
				$upd[] = '('.$r['id'].','.$balans.')';
				$send['count']++;
			}
		}
		if(!empty($upd)) {
			$sql = "INSERT INTO `client`
						(`id`,`balans`)
					VALUES ".implode(',', $upd)."
					ON DUPLICATE KEY UPDATE `balans`=VALUES(`balans`)";
			query($sql);
		}
		$send['time'] = round(microtime(true) - TIME, 3);
		jsonSuccess($send);
		break;
	case 'zayav_balans':
		$sql = "SELECT
				  `z`.`id`,
				  `z`.`accrual_sum`,
				  IFNULL(SUM(`a`.`sum`),0) AS `acc`
				FROM `zayav` AS `z`
				  LEFT JOIN `accrual` AS `a`
				  ON `z`.`id`=`a`.`zayav_id`
					AND !`a`.`deleted`
				GROUP BY `z`.`id`
				ORDER BY `z`.`id`";
		$q = query($sql);
		$zayav = array();
		while($r = mysql_fetch_assoc($q))
			$zayav[$r['id']] = $r;
		$sql = "SELECT
				  `z`.`id`,
				  `z`.`oplata_sum`,
				  IFNULL(SUM(`m`.`sum`),0) AS `opl`
				FROM `zayav` AS `z`
				  LEFT JOIN `money` AS `m`
				  ON `z`.`id`=`m`.`zayav_id`
					AND !`m`.`deleted`
					AND `m`.`sum`>0
				GROUP BY `z`.`id`
				ORDER BY `z`.`id`";
		$q = query($sql);
		$send['count'] = 0;
		$upd = array();
		while($r = mysql_fetch_assoc($q)) {
			$z = $zayav[$r['id']];
			if(round($z['accrual_sum']) != round($z['acc']) || round($r['oplata_sum']) != round($r['opl'])) {
				$upd[] = '('.$r['id'].','.$z['acc'].','.$r['opl'].')';
				$send['count']++;
			}
		}
		if(!empty($upd)) {
			$sql = "INSERT INTO `zayav`
						(`id`,`accrual_sum`, `oplata_sum`)
					VALUES ".implode(',', $upd)."
					ON DUPLICATE KEY UPDATE
						`accrual_sum`=VALUES(`accrual_sum`),
						`oplata_sum`=VALUES(`oplata_sum`)";
			query($sql);
		}
		$send['time'] = round(microtime(true) - TIME, 3);
		jsonSuccess($send);
		break;
}

jsonError();