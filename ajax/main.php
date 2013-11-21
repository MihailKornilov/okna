<?php
require_once('config.php');

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

    case 'sort':
        if(!preg_match(REGEXP_MYSQLTABLE, $_POST['table']))
            jsonError();
        $table = htmlspecialchars(trim($_POST['table']));
        $sql = "SHOW TABLES LIKE '".$table."'";
        if(!mysql_num_rows(query($sql)))
            jsonError();

        $sort = explode(',', $_POST['ids']);
        if(empty($sort))
            jsonError();
        for($n = 0; $n < count($sort); $n++)
            if(!preg_match(REGEXP_NUMERIC, $sort[$n]))
                jsonError();

        for($n = 0; $n < count($sort); $n++)
            query("UPDATE `".$table."` SET `sort`=".$n." WHERE `id`=".intval($sort[$n]));
        _cacheClear();
        jsonSuccess();
        break;

    case 'vkcomment_add':
        $table = htmlspecialchars(trim($_POST['table']));
        if(strlen($table) > 20)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        if(empty($_POST['txt']))
            jsonError();
        $txt = win1251(htmlspecialchars(trim($_POST['txt'])));
        $sql = "INSERT INTO `vk_comment` (
                    `table_name`,
                    `table_id`,
                    `txt`,
                    `viewer_id_add`
                ) VALUES (
                    '".$table."',
                    ".intval($_POST['id']).",
                    '".addslashes($txt)."',
                    ".VIEWER_ID."
                )";
        query($sql);
        $send['html'] = utf8(_vkCommentUnit(mysql_insert_id(), _viewer(), $txt, curTime()));
        jsonSuccess($send);
        break;
    case 'vkcomment_add_child':
        $table = htmlspecialchars(trim($_POST['table']));
        if(strlen($table) > 20)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['parent']))
            jsonError();
        if(empty($_POST['txt']))
            jsonError();
        $txt = win1251(htmlspecialchars(trim($_POST['txt'])));
        $sql = "INSERT INTO `vk_comment` (
                    `table_name`,
                    `table_id`,
                    `txt`,
                    `parent_id`,
                    `viewer_id_add`
                ) VALUES (
                    '".$table."',
                    ".intval($_POST['id']).",
                    '".addslashes($txt)."',
                    ".intval($_POST['parent']).",
                    ".VIEWER_ID."
                )";
        query($sql);
        $send['html'] = utf8(_vkCommentChild(mysql_insert_id(), _viewer(), $txt, curTime()));
        jsonSuccess($send);
        break;
    case 'vkcomment_del':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        if(!VIEWER_ADMIN) {
            $sql = "SELECT `viewer_id_add` FROM `vk_comment` WHERE `status`=1 AND `id`=".$id;
            if(!$r = mysql_fetch_assoc(query($sql)))
                jsonError();
            if($r['viewer_id_add'] != VIEWER_ID)
                jsonError();
        }

        $childs = array();

        $sql = "SELECT `id` FROM `vk_comment` WHERE `status`=1 AND `parent_id`=".$id;
        $q = query($sql);
        if(mysql_num_rows($q)) {
            while($r = mysql_fetch_assoc($q))
                $childs[] = $r['id'];
            $sql = "UPDATE `vk_comment` SET
                    `status`=0,
                    `viewer_id_del`=".VIEWER_ID.",
                    `dtime_del`=CURRENT_TIMESTAMP
               WHERE `parent_id`=".$id;
            query($sql);
        }

        $sql = "UPDATE `vk_comment` SET
                    `status`=0,
                    `viewer_id_del`=".VIEWER_ID.",
                    `dtime_del`=CURRENT_TIMESTAMP,
                    `child_del`=".(!empty($childs) ? "'".implode(',', $childs)."'" : 'NULL')."
               WHERE `id`=".$id;
        query($sql);
        jsonSuccess();
        break;
    case 'vkcomment_rest':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);

        $sql = "SELECT `child_del` FROM `vk_comment` WHERE `id`=".$id;
        $r = mysql_fetch_assoc(query($sql));
        if($r['child_del']) {
            $sql = "UPDATE `vk_comment` SET
                    `status`=1,
                    `viewer_id_del`=0,
                    `dtime_del`='0000-00-00 00:00:00'
               WHERE `id` IN (".$r['child_del'].")";
            query($sql);
        }

        $sql = "UPDATE `vk_comment` SET
                    `status`=1,
                    `viewer_id_del`=0,
                    `dtime_del`='0000-00-00 00:00:00',
                    `child_del`=NULL
               WHERE `id`=".$id;
        query($sql);
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
                WHERE `status`=1".
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
        if(empty($fio))
            jsonError();
        $sql = "INSERT INTO `client` (
                    `fio`,
                    `telefon`,
                    `adres`,
                    `viewer_id_add`
                ) VALUES (
                    '".addslashes($fio)."',
                    '".addslashes($telefon)."',
                    '".addslashes($adres)."',
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
        if(empty($fio))
            jsonError();
        $sql = "SELECT * FROM `client` WHERE `status`=1 AND `id`=".$client_id;
        if(!$client = mysql_fetch_assoc(query($sql)))
            jsonError();
        query("UPDATE `client` SET
                `fio`='".$fio."',
                `telefon`='".$telefon."',
                `adres`='".$adres."'
               WHERE `id`=".$client_id);
        $changes = '';
        if($client['fio'] != $fio)
            $changes .= '<tr><th>Фио:<td>'.$client['fio'].'<td>»<td>'.$fio;
        if($client['telefon'] != $telefon)
            $changes .= '<tr><th>Тел.:<td>'.$client['telefon'].'<td>»<td>'.$telefon;
        if($client['adres'] != $adres)
            $changes .= '<tr><th>Адрес:<td>'.$client['adres'].'<td>»<td>'.$adres;
        if($changes)
            history_insert(array(
                'type' => 2,
                'client_id' => $client_id,
                'value' => '<table>'.$changes.'</table>'
            ));
        jsonSuccess();
        break;
    case 'client_del':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $client_id = intval($_POST['id']);
        if(!query_value("SELECT COUNT(`id`) FROM `client` WHERE `status`=1 AND `id`=".$client_id))
            jsonError();
        query("UPDATE `client` SET `status`=0 WHERE `id`=".$client_id);
        query("UPDATE `zayav` SET `status`=0 WHERE `client_id`=".$client_id);
        query("UPDATE `money` SET `status`=0 WHERE `client_id`=".$client_id);
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

    case 'zayav_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) || $_POST['client_id'] == 0)
            jsonError();

        if(empty($_POST['product']))
            jsonError();
        $product = array();
        $ex = explode(',', $_POST['product']);
        foreach($ex as $r) {
            $ids = explode(':', $r);
            foreach($ids as $id)
                if(!preg_match(REGEXP_NUMERIC, $id))
                    jsonError();
            if($ids[0] == 0 || $ids[2] == 0)
                jsonError();
            $product[] = $ids;
        }
        if(empty($product))
            jsonError();

        $client_id = intval($_POST['client_id']);
        $adres_set = win1251(htmlspecialchars(trim($_POST['adres_set'])));
        $comm = win1251(htmlspecialchars(trim($_POST['comm'])));

        $sql = "INSERT INTO `zayav` (
                    `client_id`,
                    `adres_set`,
                    `status_dtime`,
                    `viewer_id_add`
                ) VALUES (
                    ".$client_id.",
                    '".$adres_set."',
                    '".curTime()."',
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

        if($comm) {
            $sql = "INSERT INTO `vk_comment` (
                        `table_name`,
                        `table_id`,
                        `txt`,
                        `viewer_id_add`
                    ) VALUES (
                        'zayav',
                        ".$send['id'].",
                        '".$comm."',
                        ".VIEWER_ID."
                    )";
            query($sql);
        }
        history_insert(array(
            'type' => 4,
            'zayav_id' => $send['id'],
            'client_id' => $client_id
        ));
        jsonSuccess($send);
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
    case 'zayav_edit':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) && $_POST['client_id'] == 0)
            jsonError();
        $zayav_id = intval($_POST['zayav_id']);
        $client_id = intval($_POST['client_id']);
//        $nomer_dog = win1251(htmlspecialchars(trim($_POST['nomer_dog'])));
        $nomer_vg = win1251(htmlspecialchars(trim($_POST['nomer_vg'])));
        $adres_set = win1251(htmlspecialchars(trim($_POST['adres_set'])));

        if(empty($_POST['product']))
            jsonError();
        $product = array();
        $ex = explode(',', $_POST['product']);
        foreach($ex as $r) {
            $ids = explode(':', $r);
            foreach($ids as $id)
                if(!preg_match(REGEXP_NUMERIC, $id))
                    jsonError();
            if($ids[0] == 0 || $ids[2] == 0)
                jsonError();
            $product[] = $ids;
        }
        if(empty($product))
            jsonError();

        $sql = "SELECT * FROM `zayav` WHERE `id`=".$zayav_id." LIMIT 1";
        if(!$zayav = mysql_fetch_assoc(query($sql)))
            jsonError();

//     `nomer_dog`='".addslashes($nomer_dog)."',

        $sql = "UPDATE `zayav` SET
                    `client_id`=".$client_id.",
                    `nomer_vg`='".addslashes($nomer_vg)."',
                    `client_id`=".$client_id.",
                    `adres_set`='".addslashes($adres_set)."'
                WHERE `id`=".$zayav_id;
        query($sql);

        if($zayav['client_id'] != $client_id) {
            $sql = "UPDATE `accrual`
                    SET `client_id`=".$client_id."
                    WHERE `zayav_id`=".$zayav_id."
                      AND `client_id`=".$zayav['client_id'];
            query($sql);
            $sql = "UPDATE `money`
                    SET `client_id`=".$client_id."
                    WHERE `zayav_id`=".$zayav_id."
                      AND `client_id`=".$zayav['client_id'];
            query($sql);
            clientBalansUpdate($zayav['client_id']);
            clientBalansUpdate($client_id);
        }

        $changes = '';
        if($zayav['client_id'] != $client_id)
            $changes .= '<tr><th>Клиент:<td>'._clientLink($zayav['client_id']).'<td>»<td>'._clientLink($client_id);
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
//        if($zayav['nomer_dog'] != $nomer_dog)
//            $changes .= '<tr><th>Номер договора:<td>'.$zayav['nomer_dog'].'<td>»<td>'.$nomer_dog;
        if($zayav['nomer_vg'] != $nomer_vg)
            $changes .= '<tr><th>Номер ВГ:<td>'.$zayav['nomer_vg'].'<td>»<td>'.$nomer_vg;
        if($zayav['adres_set'] != $adres_set)
            $changes .= '<tr><th>Адрес установки:<td>'.$zayav['adres_set'].'<td>»<td>'.$adres_set;
        if($changes)
            history_insert(array(
                'type' => 5,
                'zayav_id' => $zayav_id,
                'value' => '<table>'.$changes.'</table>'
            ));
        jsonSuccess();
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
                WHERE `status`=1
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
                WHERE `status`=1
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
    case 'zayav_oplata_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['type']) || $_POST['type'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) || $_POST['sum'] == 0)
            jsonError();
        $type = intval($_POST['type']);
        $prihodArr = _prihodType();
        if($prihodArr[$type]['kassa'] && !preg_match(REGEXP_BOOL, $_POST['kassa']))
            jsonError();
        $zayav_id = intval($_POST['zayav_id']);
        $sum = intval($_POST['sum']);
        $kassa = $prihodArr[$type]['kassa'] ? intval($_POST['kassa']) : 0;
        $prim = win1251(htmlspecialchars(trim($_POST['prim'])));

        $sql = "SELECT *
                FROM `zayav`
                WHERE `status`>0
                  AND `id`=".$zayav_id;
        if(!$zayav = mysql_fetch_assoc(query($sql)))
            jsonError();

        $sql = "INSERT INTO `money` (
                    `zayav_id`,
                    `client_id`,
                    `prihod_type`,
                    `sum`,
                    `kassa`,
                    `prim`,
                    `viewer_id_add`
                ) VALUES (
                    ".$zayav_id.",
                    ".$zayav['client_id'].",
                    ".$type.",
                    ".$sum.",
                    ".$kassa.",
                    '".addslashes($prim)."',
                    ".VIEWER_ID."
                )";
        query($sql);
        $send['html'] = utf8(zayav_oplata_unit(array(
            'id' => mysql_insert_id(),
            'prihod_type' => $type,
            'sum' => $sum,
            'prim' => $prim
        )));
        clientBalansUpdate($zayav['client_id']);
        history_insert(array(
            'type' => 10,
            'zayav_id' => $zayav_id,
            'value' => $sum,
            'value1' => $prim,
            'value2' => $type
        ));
        jsonSuccess($send);
        break;
    case 'zayav_oplata_del':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);

        $sql = "SELECT *
                FROM `money`
                WHERE `status`>0
                  AND `id`=".$id;
        if(!$r = mysql_fetch_assoc(query($sql)))
            jsonError();

        $sql = "UPDATE `money` SET
                    `status`=0,
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
    case 'zayav_oplata_rest':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "SELECT *
                FROM `money`
                WHERE `status`=0
                  AND `id`=".$id;
        if(!$r = mysql_fetch_assoc(query($sql)))
            jsonError();

        $sql = "UPDATE `money` SET
                    `status`=1,
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

        $send['html'] = utf8(zayav_oplata_unit($r));
        jsonSuccess($send);
        break;

    case 'report_history_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
/*        if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
            $_POST['worker'] = 0;
        if(!preg_match(REGEXP_NUMERIC, $_POST['action']))
            $_POST['action'] = 0;*/
        $page = intval($_POST['page']);
        $send['html'] = utf8(report_history_spisok($page));
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
    case 'setup_product_add':
        if(!RULES_PRODUCT)
            jsonError();
        $name = win1251(htmlspecialchars(trim($_POST['name'])));
        if(empty($name))
            jsonError();
        $sql = "INSERT INTO `setup_product` (
                    `name`,
                    `viewer_id_add`
                ) VALUES (
                    '".addslashes($name)."',
                    ".VIEWER_ID."
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

        $sql = "UPDATE `setup_product` SET `name`='".addslashes($name)."' WHERE `id`=".$product_id;
        query($sql);

        xcache_unset(CACHE_PREFIX.'product');
        GvaluesCreate();

        if($r['name'] != $name)
            history_insert(array(
                'type' => 502,
                'value' => '<table><tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name.'</table>'
            ));

        jsonSuccess();
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