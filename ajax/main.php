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
}

jsonError();