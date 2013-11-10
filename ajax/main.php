<?php
require_once('config.php');

switch(@$_POST['op']) {
    case 'cache_clear':
        if(!SA)
            jsonError();
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
        $send['html'] = utf8(_vkCommentUnit(mysql_insert_id(), _viewersInfo(), $txt, curTime()));
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
        $send['html'] = utf8(_vkCommentChild(mysql_insert_id(), _viewersInfo(), $txt, curTime()));
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
                WHERE `id`".
                    (!empty($val) ? " AND (`fio` LIKE '%".$val."%' OR `telefon` LIKE '%".$val."%' OR `adres` LIKE '%".$val."%')" : '').
                    ($client_id > 0 ? " AND `id`<=".$client_id : '')."
                ORDER BY `id` DESC
                LIMIT 50";
        $q = query($sql);
        $send['spisok'] = array();
        while($r = mysql_fetch_assoc($q)) {
            $unit = array(
                'uid' => $r['id'],
                'title' => utf8($r['fio'])
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
        /*history_insert(array(
            'type' => 3,
            'client_id' => $send['uid']
        ));*/
        jsonSuccess($send);
        break;
    case 'client_spisok_load':
        $filter = clientFilter($_POST);
        $send = client_data(1, $filter);
        $send['all'] = client_count($send['all'], $filter['dolg']);
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
        query("UPDATE `client` SET
                `fio`='".$fio."',
                `telefon`='".$telefon."',
                `adres`='".$adres."'
               WHERE `id`=".$client_id);
/*        history_insert(array(
            'type' => $join ? 11 : 10,
            'client_id' => $client_id
        ));*/
        jsonSuccess();
        break;
    case 'client_del':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        if(!query_value("SELECT COUNT(`id`) FROM `client` WHERE `status`=1 AND `id`=".$id))
            jsonError();
        query("UPDATE `client` SET `status`=0 WHERE `id`=".$id);
        query("UPDATE `zayav` SET `status`=0 WHERE `client_id`=".$id);
        query("UPDATE `money` SET `status`=0 WHERE `client_id`=".$id);
        jsonSuccess();
        break;

    case 'zayav_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) || $_POST['client_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['product_id']))
            jsonError();
        $client_id = intval($_POST['client_id']);
        $nomer_dog = win1251(htmlspecialchars(trim($_POST['nomer_dog'])));
        $nomer_vg = win1251(htmlspecialchars(trim($_POST['nomer_vg'])));
        $product_id = intval($_POST['product_id']);
        $adres_set = win1251(htmlspecialchars(trim($_POST['adres_set'])));
        $comm = win1251(htmlspecialchars(trim($_POST['comm'])));

        $sql = "INSERT INTO `zayav` (
                    `client_id`,
                    `nomer_dog`,
                    `nomer_vg`,
                    `product_id`,
                    `adres_set`,
                    `status_dtime`,
                    `viewer_id_add`
                ) VALUES (
                    ".$client_id.",
                    '".$nomer_dog."',
                    '".$nomer_vg."',
                    ".$product_id.",
                    '".$adres_set."',
                    '".curTime()."',
                    ".VIEWER_ID."
                )";
        query($sql);
        $send['id'] = mysql_insert_id();

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
        jsonSuccess($send);
        break;

    case 'setup_product_add':
        $name = win1251(htmlspecialchars(trim($_POST['name'])));
        if(empty($name))
            jsonError();
        $sort = query_value("SELECT IFNULL(MAX(`sort`)+1,0) FROM `setup_product`");
        $sql = "INSERT INTO `setup_product` (
                    `name`,
                    `sort`,
                    `viewer_id_add`
                ) VALUES (
                    '".addslashes($name)."',
                    ".$sort.",
                    ".VIEWER_ID."
                )";
        query($sql);
        xcache_unset(CACHE_PREFIX.'product_name');
        $send['html'] = utf8(setup_product_spisok());
        jsonSuccess($send);
        break;
    case 'setup_product_edit':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $name = win1251(htmlspecialchars(trim($_POST['name'])));
        if(empty($name))
            jsonError();
        $sql = "UPDATE `setup_product` SET `name`='".addslashes($name)."' WHERE `id`=".$id;
        query($sql);
        xcache_unset(CACHE_PREFIX.'product_name');
        $send['html'] = utf8(setup_product_spisok());
        jsonSuccess($send);
        break;
    case 'setup_product_del':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        if(query_value("SELECT COUNT(`id`) FROM `zayav` WHERE `product_id`=".$id))
            jsonError();
        $sql = "DELETE FROM `setup_product` WHERE `id`=".$id;
        query($sql);
        xcache_unset(CACHE_PREFIX.'product_name');
        $send['html'] = utf8(setup_product_spisok());
        jsonSuccess($send);
        break;

    case 'setup_prihodtype_add':
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
        $send['html'] = utf8(setup_prihodtype_spisok());
        jsonSuccess($send);
        break;
    case 'setup_prihodtype_edit':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['kassa_put']))
            jsonError();
        $id = intval($_POST['id']);
        $name = win1251(htmlspecialchars(trim($_POST['name'])));
        $kassa_put = intval($_POST['kassa_put']);
        if(empty($name))
            jsonError();
        $sql = "UPDATE `setup_prihodtype`
                SET `name`='".addslashes($name)."',
                    `kassa_put`=".$kassa_put."
                WHERE `id`=".$id;
        query($sql);
        xcache_unset(CACHE_PREFIX.'prihodtype');
        $send['html'] = utf8(setup_prihodtype_spisok());
        jsonSuccess($send);
        break;
    case 'setup_prihodtype_del':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        if(query_value("SELECT COUNT(`id`) FROM `money` WHERE `status`=1 AND `prihod_type`=".$id))
            jsonError();
        $sql = "DELETE FROM `setup_prihodtype` WHERE `id`=".$id;
        query($sql);
        xcache_unset(CACHE_PREFIX.'prihodtype');
        $send['html'] = utf8(setup_prihodtype_spisok());
        jsonSuccess($send);
        break;
}

jsonError();