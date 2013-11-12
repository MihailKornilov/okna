<?php
function _vkUserUpdate($uid=VIEWER_ID) {//���������� ������������ �� ��������
    require_once(DOCUMENT_ROOT.'/include/vkapi.class.php');
    $VKAPI = new vkapi($_GET['api_id'], SECRET);
    $res = $VKAPI->api('users.get',array('uids' => $uid, 'fields' => 'photo,sex,country,city'));
    $u = $res['response'][0];
    $u['first_name'] = win1251($u['first_name']);
    $u['last_name'] = win1251($u['last_name']);
    $u['country_id'] = isset($u['country']) ? $u['country'] : 0;
    $u['city_id'] = isset($u['city']) ? $u['city'] : 0;
    $u['menu_left_set'] = 0;

    // ��������� �� ����������
    $app = $VKAPI->api('isAppUser', array('uid'=>$uid));
    $u['app_setup'] = $app['response'];

    // �������� �� � ����� ����
    //$mls = $VKAPI->api('getUserSettings', array('uid'=>$uid));
    $u['menu_left_set'] = 0;//($mls['response']&256) > 0 ? 1 : 0;

    $sql = 'INSERT INTO `vk_user` (
                `viewer_id`,
                `first_name`,
                `last_name`,
                `sex`,
                `photo`,
                `app_setup`,
                `menu_left_set`,
                `country_id`,
                `city_id`
            ) VALUES (
                '.$uid.',
                "'.$u['first_name'].'",
                "'.$u['last_name'].'",
                '.$u['sex'].',
                "'.$u['photo'].'",
                '.$u['app_setup'].',
                '.$u['menu_left_set'].',
                '.$u['country_id'].',
                '.$u['city_id'].'
            ) ON DUPLICATE KEY UPDATE
                `first_name`="'.$u['first_name'].'",
                `last_name`="'.$u['last_name'].'",
                `sex`='.$u['sex'].',
                `photo`="'.$u['photo'].'",
                `app_setup`='.$u['app_setup'].',
                `menu_left_set`='.$u['menu_left_set'].',
                `country_id`='.$u['country_id'].',
                `city_id`='.$u['city_id'];
    query($sql);
    return $u;
}//end of _vkUserUpdate()

function _hashRead() {
    $_GET['p'] = isset($_GET['p']) ? $_GET['p'] : 'zayav';
    if(empty($_GET['hash'])) {
        define('HASH_VALUES', false);
        if(isset($_GET['start'])) {// �������������� ��������� ���������� ��������
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
}//end of _hashRead()
function _hashCookieSet() {
    setcookie('p', $_GET['p'], time() + 2592000, '/');
    setcookie('d', isset($_GET['d']) ? $_GET['d'] : '', time() + 2592000, '/');
    setcookie('d1', isset($_GET['d1']) ? $_GET['d1'] : '', time() + 2592000, '/');
    setcookie('id', isset($_GET['id']) ? $_GET['id'] : '', time() + 2592000, '/');
}//end of _hashCookieSet()
function _cacheClear() {
    xcache_unset(CACHE_PREFIX.'setup_global');
    xcache_unset(CACHE_PREFIX.'viewer_'.VIEWER_ID);
    xcache_unset(CACHE_PREFIX.'product_name');
    xcache_unset(CACHE_PREFIX.'prihodtype');
}//ens of _cacheClear()

function _header() {
    global $html;
    $html =
        '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
        '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.

        '<head>'.
        '<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
        '<title>����������� ���� - ���������� 3978722</title>'.

        //������������ ������ � ��������
        (SA ? '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/errors-utf8.js?'.VERSION.'"></script>' : '').

        //�������� �������
        '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/jquery-2.0.3.min.js"></script>'.
//        '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/highstock.js"></script>'.
        '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/'.(DEBUG ? '' : 'min/').'xd_connection.js"></script>'.

        //��������� ���������� �������� �������.
        (SA ? '<script type="text/javascript">var TIME=(new Date()).getTime();</script>' : '').

        '<script type="text/javascript">'.
        (LOCAL ? 'for(var i in VK)if(typeof VK[i]=="function")VK[i]=function(){return false};' : '').
        'var G={},'.
        'DOMAIN="'.DOMAIN.'",'.
        'VALUES="'.VALUES.'",'.
        'VIEWER_ID='.VIEWER_ID.';'.
        '</script>'.

        //����������� ������ VK. ������ ������ �� �������� ������ �����
        '<link href="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/'.(DEBUG ? '' : 'min/').'vk.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.

        '<link href="'.SITE.'/css/main.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
        '<script type="text/javascript" src="'.SITE.'/js/main.js?'.VERSION.'"></script>'.

        //����������� API VK
        '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/'.(DEBUG ? '' : 'min/').'vk.js?'.VERSION.'"></script>'.

        '</head>'.
        '<body>'.
        '<div id="frameBody">'.
        '<iframe id="frameHidden" name="frameHidden"></iframe>';
}//end of _header()

function _footer() {
    global $html, $sqlQuery, $sqlCount, $sqlTime;
    if(SA) {
        $d = empty($_GET['d']) ? '' :'&pre_d='.$_GET['d'];
        $d1 = empty($_GET['d1']) ? '' :'&pre_d1='.$_GET['d1'];
        $id = empty($_GET['id']) ? '' :'&pre_id='.$_GET['id'];
        $html .= '<div id="admin">'.
          //  ($_GET['p'] != 'sa' && !SA_VIEWER_ID ? '<a href="'.URL.'&p=sa&pre_p='.$_GET['p'].$d.$d1.$id.'">Admin</a> :: ' : '').
            '<a class="debug_toggle'.(DEBUG ? ' on' : '').'">�'.(DEBUG ? '�' : '').'������� Debug</a> :: '.
            '<a id="cache_clear">������� ��� ('.VERSION.')</a> :: '.
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
    $html .= '<script type="text/javascript">'.
        'hashSet({'.implode(',', $gValues).'});'.
        (SA ? '$("#admin EM").html(((new Date().getTime())-TIME)/1000);' : '').
        '</script>'.
        '</div></body></html>';
}//end of _footer()

function _viewerName($id=VIEWER_ID, $link=false) {
    $key = CACHE_PREFIX.'viewer_name_'.$id;
    $name = xcache_get($key);
    if(empty($name)) {
        $sql = "SELECT CONCAT(`first_name`,' ',`last_name`) AS `name` FROM `vk_user` WHERE `viewer_id`=".$id." LIMIT 1";
        $r = mysql_fetch_assoc(query($sql));
        $name = $r['name'];
        xcache_set($key, $name, 86400);
    }
    return $link ? '<a href="http://vk.com/id'.$id.'" target="_blank">'.$name.'</a>' : $name;
}//end of _viewerName()
function _viewersInfo($arr=VIEWER_ID) {
    if(empty($arr))
        return array();
    $id = false;
    if(!is_array($arr)) {
        $id = $arr;
        $arr = array($arr);
    }
    $sql = "SELECT * FROM `vk_user` WHERE `viewer_id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['viewer_id']] = array(
            'id' => $r['viewer_id'],
            'name' => $r['first_name'].' '.$r['last_name'],
            'link' => '<a href="http://vk.com/id'.$r['viewer_id'].'" target="_blank" class="vlink">'.$r['first_name'].' '.$r['last_name'].'</a>',
            'photo' => '<img src="'.$r['photo'].'">'
        );
    return $id ? $send[$id] : $send;
}//end of _viewersInfo()

function _product($product_id=false, $type='array') {//������ ������� ��� ������
    if(!defined('PRODUCT_LOADED') || $product_id === false) {
        $key = CACHE_PREFIX.'product_name';
        $arr = xcache_get($key);
        if(empty($arr)) {
            $sql = "SELECT `id`,`name` FROM `setup_product` ORDER BY `sort`";
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
    if($product_id !== false)
        return constant('PRODUCT_'.$product_id);
    switch($type) {
        case 'json':
            $json = array();
            foreach($arr as $id => $name)
                $json[] = '{uid:'.$id.',title:"'.$name.'"}';
            return '['.implode(',', $json).']';
        default: return $arr;
    }
}//end of _product()
function _prihodType($type_id=false, $type='array') {//������ ������� ��� ������
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
            foreach($arr as $id => $r)
                define('PRIHODTYPE_'.$id, $r['name']);
            define('PRIHODTYPE_0', '');
            define('PRIHODTYPE_LOADED', true);
        }
    }
    if($type_id !== false)
        return constant('PRIHODTYPE_'.$type_id);
    switch($type) {
        case 'json':
            $json = array();
            foreach($arr as $id => $r)
                $json[] = '{uid:'.$id.',title:"'.$r['name'].'"}';
            return '['.implode(',', $json).']';
        default: return $arr;
    }
}//end of _prihodType()
function _prihodKassa() {
    $json = array();
    foreach(_prihodType() as $id => $r)
        if($r['kassa'])
            $json[] = $id.':'.$r['kassa'];
    return '{'.implode(',', $json).'}';
}//end of _prihodKassa()

function _mainLinks() {
    global $html;
//    _remindActiveSet();
    $links = array(
        array(
            'name' => '�������',
            'page' => 'client',
            'show' => 1
        ),
        array(
            'name' => '������',
            'page' => 'zayav',
            'show' => 1
        ),
        array(
            'name' => '������',
            'page' => 'report',
            'show' => 1
        ),
        array(
            'name' => '���������',
            'page' => 'setup',
            'show' => 1
        )
    );

    $send = '<div id="mainLinks">';
    foreach($links as $l)
        if($l['show'])
            $send .= '<a href="'.URL.'&p='.$l['page'].'"'.($l['page'] == $_GET['p'] ? 'class="sel"' : '').'>'.$l['name'].'</a>';
    $send .= '</div>';
    $html .= $send;
}//end of _mainLinks()


// ---===! client !===--- ������ ��������

function _clientLink($arr) {
    if(empty($arr))
        return array();
    $id = false;
    if(!is_array($arr)) {
        $id = $arr;
        $arr = array($arr);
    }
    $sql = "SELECT `id`,`fio` FROM `client` WHERE `id` IN (".implode(',', $arr).")";
    $q = query($sql);
    $send = array();
    while($r = mysql_fetch_assoc($q))
        $send[$r['id']] = '<a href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>';
    if($id)
        return $send[$id];
    return $send;
}//end of _clientsLink()
function clientBalansUpdate($client_id) {//���������� ������� �������
    $prihod = query_value("SELECT SUM(`sum`) FROM `money` WHERE `status`=1 AND `client_id`=".$client_id." AND `sum`>0");
    $acc = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE `status`=1 AND `client_id`=".$client_id);
    $balans = $prihod - $acc;
    query("UPDATE `client` SET `balans`=".$balans." WHERE `id`=".$client_id);
    return $balans;
}//end of clientBalansUpdate()

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
}//end of clientFilter()
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
        $send['spisok'] = '<div class="_empty">�������� �� �������.</div>';
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
            WHERE `status`>0
              AND `client_id` IN (".implode(',', array_keys($spisok)).")
            GROUP BY `client_id`";
    $q = query($sql);
    while($r = mysql_fetch_assoc($q))
        $spisok[$r['id']]['zayav_count'] = $r['count'];

    $send['spisok'] = '';
    foreach($spisok as $r)
        $send['spisok'] .= '<div class="unit">'.
            ($r['balans'] ? '<div class="balans">������: <b style=color:#'.($r['balans'] < 0 ? 'A00' : '090').'>'.$r['balans'].'</b></div>' : '').
            '<table>'.
                '<tr><td class="label">���:<td><a href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>'.
                ($r['telefon'] ? '<tr><td class="label">�������:<td>'.$r['telefon'] : '').
                (isset($r['adres']) ? '<tr><td class="label">�����:<td>'.$r['adres'] : '').
                (isset($r['zayav_count']) ? '<tr><td class="label">������:<td>'.$r['zayav_count'] : '').
            '</table>'.
        '</div>';
    if($start + $limit < $send['all']) {
        $c = $send['all'] - $start - $limit;
        $c = $c > $limit ? $limit : $c;
        $send['spisok'] .= '<div class="ajaxNext" val="'.($page + 1).'"><span>�������� ��� '.$c.' ������'._end($c, '�', '�', '��').'</span></div>';
    }
    return $send;
}//end of client_data()
function client_list($data) {
    return
    '<div id="client">'.
        '<div id="find"></div>'.
        '<div class="result">'.client_count($data['all']).'</div>'.
        '<table class="tabLR">'.
            '<tr><td class="left">'.$data['spisok'].
                '<td class="right">'.
                    '<div id="buttonCreate"><a>����� ������</a></div>'.
                    '<div class="filter">'.
                        _check('dolg', '��������').
                    '</div>'.
        '</table>'.
    '</div>';
}//end of client_list()
function client_count($count, $dolg=0) {
    if($dolg)
        $dolg = abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE `balans`<0 LIMIT 1"));
    return ($count > 0 ?
        '������'._end($count, ' ', '� ').$count.' ������'._end($count, '', '�', '��').
        ($dolg ? '<em>(����� ����� ����� = '.$dolg.' ���.)</em>' : '')
        :
        '�������� �� �������');
}//end of client_count()

function client_info($client_id) {
    $sql = "SELECT * FROM `client` WHERE `status`=1 AND `id`=".$client_id;
    if(!$client = mysql_fetch_assoc(query($sql)))
        return '������� �� ����������';

    $zayavData = zayav_data(1, array('client'=>$client_id), 10);
    $commCount = query_value("SELECT COUNT(`id`)
							  FROM `vk_comment`
							  WHERE `status`=1
								AND `parent_id`=0
								AND `table_name`='client'
								AND `table_id`=".$client_id);

    $sql = "SELECT * FROM `money` WHERE `status`=1 AND `client_id`=".$client_id;
    $q = query($sql);
    $moneyCount = mysql_num_rows($q);
    $money = '<div class="_empty">�������� ���.</div>';
    if($moneyCount) {
        $money = '<table class="_spisok _money">'.
            '<tr><th class="sum">�����'.
            '<th>��������'.
            '<th class="data">����';
        while($r = mysql_fetch_assoc($q)) {
            $about = '';
            if($r['zayav_id'] > 0)
                $about .= '������ '.$r['zayav_id'].'. ';
            $about .= $r['prim'];
            $money .= '<tr><td class="sum"><b>'.$r['sum'].'</b>'.
                '<td>'.$about.
                '<td class="dtime" title="����: '._viewerName($r['viewer_id_add']).'">'.FullDataTime($r['dtime_add']);
        }
        $money .= '</table>';
    }
   // $remindData = remind_data(1, array('client'=>$client_id));

    return
    '<script type="text/javascript">'.
        'var CLIENT={'.
            'id:'.$client_id.
        '};'.
    '</script>'.
    '<div id="clientInfo">'.
        '<table class="tabLR">'.
            '<tr><td class="left">'.
                    '<div class="fio">'.$client['fio'].'</div>'.
                    '<div class="cinf">'.
                        '<table style="border-spacing:2px">'.
                            '<tr><td class="label">�������:<td class="telefon">'.$client['telefon'].'</TD>'.
                            '<tr><td class="label">�����:  <td class="adres">'.$client['adres'].'</TD>'.
                            '<tr><td class="label">������: <td><b style=color:#'.($client['balans'] < 0 ? 'A00' : '090').'>'.$client['balans'].'</b>'.
                        '</table>'.
                        '<div class="dtime">������� ���� '._viewerName($client['viewer_id_add']).' '.FullData($client['dtime_add'], 1).'</div>'.
                    '</div>'.
                    '<div id="dopLinks">'.
                        '<a class="link sel" val="zayav">������'.($zayavData['all'] ? ' ('.$zayavData['all'].')' : '').'</a>'.
                        '<a class="link" val="money">�������'.($moneyCount ? ' ('.$moneyCount.')' : '').'</a>'.
                        '<a class="link" val="remind">�������'.(!empty($remindData) ? ' ('.$remindData['all'].')' : '').'</a>'.
                        '<a class="link" val="comm">�������'.($commCount ? ' ('.$commCount.')' : '').'</a>'.
                    '</div>'.
                    '<div id="zayav_spisok">'.zayav_spisok($zayavData).'</div>'.
                    '<div id="money_spisok">'.$money.'</div>'.
                    '<div id="remind_spisok">'.(!empty($remindData) ? report_remind_spisok($remindData) : '<div class="_empty">������� ���.</div>').'</div>'.
                    '<div id="comments">'._vkComment('client', $client_id).'</div>'.
                '<td class="right">'.
                    '<div class="rightLink">'.
                        '<a class="sel">����������</a>'.
                        '<a class="cedit">�������������</a>'.
                        '<a href="'.URL.'&p=zayav&d=add&back=client&id='.$client_id.'"><b>����� ������</b></a>'.
                        '<a class="remind_add">����� �������</a>'.
                        '<a class="cdel">������� �������</a>'.
                    '</div>'.
                    '<div id="zayav_filter">'.
                        '<div id="zayav_result">'.zayav_count($zayavData['all'], 0).'</div>'.
                        '<div class="findHead">������ ������</div>'.
                        _rightLink('status', _zayavStatusName()).
                    '</div>'.
        '</table>'.
    '</div>';
}//end of client_info()



// ---===! zayav !===--- ������ ������
function _zayavStatus($id=false) {
    $arr = array(
        '0' => array(
            'name' => '����� ������',
            'color' => 'ffffff'
        ),
        '1' => array(
            'name' => '������� ����������',
            'color' => 'E8E8FF'
        ),
        '2' => array(
            'name' => '���������!',
            'color' => 'CCFFCC'
        ),
        '3' => array(
            'name' => '��������� �� �������',
            'color' => 'FFDDDD'
        )
    );
    return $id ? $arr[$id] : $arr;
}//end of _zayavStatus()
function _zayavStatusName($id=false) {
    $status = _zayavStatus();
    if($id)
        return $status[$id]['name'];
    $send = array();
    foreach($status as $id => $r)
        $send[$id] = $r['name'];
    return $send;
}//end of _zayavStatusName()
function _zayavStatusColor($id=false) {
    $status = _zayavStatus();
    if($id)
        return $status[$id]['color'];
    $send = array();
    foreach($status as $id => $r)
        $send[$id] = $r['color'];
    return $send;
}//end of _zayavStatusColor()

function zayav_add($v=array()) {
    return
    '<script type="text/javascript">var product='._product(false, 'json').';</script>'.
    '<div id="zayavAdd">'.
        '<div class="headName">�������� ����� ������</div>'.
        '<table style="border-spacing:8px">'.
            '<tr><td class="label">������:         <td><INPUT TYPE="hidden" id="client_id" value="'.$v['client_id'].'" />'.
            '<tr><td class="label">����� ��������: <td><INPUT type="text" id="nomer_dog" maxlength="30" />'.
            '<tr><td class="label">����� ��:       <td><INPUT type="text" id="nomer_vg" maxlength="30" />'.
            '<tr><td class="label">�������:        <td><INPUT type="hidden" id="product_id" value="0" />'.
                '<a href="'.URL.'&p=setup&d=product" class="img_edit product_edit" title="��������� ������ �������"></a>'.
            '<tr><td class="label">����� ���������:<td><INPUT type="text" id="adres_set" maxlength="100" />'.
            '<tr><td class="label top">�������:    <td><textarea id="comm"></textarea>'.
        '</table>'.
        '<div class="vkButton"><button>������</button></div>'.
        '<div class="vkCancel" val="'.$v['back'].'"><button>������</button></div>'.
    '</div>';
}//end of zayav_add()

function zayavFilter($v) {
    if(empty($v['status']) || !preg_match(REGEXP_NUMERIC, $v['status']))
        $v['status'] = 0;
    if(empty($v['client']) || !preg_match(REGEXP_NUMERIC, $v['client']))
        $v['client'] = 0;

    $filter = array();
    $filter['find'] = htmlspecialchars(trim(@$v['find']));
    $filter['desc'] = intval(@$v['desc']) == 1 ? 'ASC' : 'DESC';
    $filter['status'] = intval($v['status']);
    if($v['client'] > 0)
        $filter['client'] = intval($v['client']);
    return $filter;
}//end of zayavFilter()
function zayav_data($page=1, $filter=array(), $limit=20) {
    $cond = "`status`>0";

    if(empty($filter['desc']))
        $filter['desc'] = 'DESC';
    if(!empty($filter['find'])) {
        $cond .= " AND `adres_set` LIKE '%".$filter['find']."%'";
        if($page ==1 && preg_match(REGEXP_NUMERIC, $filter['find']))
            $zayav_id = intval($filter['find']);
    } else {
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

    foreach($zayav as $id => $r) {
        $unit = array(
            'status_color' => _zayavStatusColor($r['status']),
            'product_id' => $r['product_id'],
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
}//end of zayav_data()
function zayav_count($count, $filter_break_show=true) {
    return
        ($filter_break_show ? '<a id="filter_break">�������� ������� ������</a>' : '').
        ($count > 0 ?
            '�������'._end($count, '�', '�').' '.$count.' ����'._end($count, '��', '��', '��')
            :
            '������ �� �������');
}//end of zayav_count()
function zayav_list($data, $values) {
    return
    '<div id="zayav">'.
        '<div class="result">'.zayav_count($data['all']).'</div>'.
        '<table class="tabLR">'.
            '<tr><td id="spisok">'.zayav_spisok($data).
                '<td class="right">'.
                '<div id="buttonCreate"><a HREF="'.URL.'&p=zayav&d=add&back=zayav">����� ������</a></div>'.
                '<div id="find"></div>'.
                '<div class="findHead">�������</div>'.
//                _radio('sort', array(1=>'�� ���� ����������',2=>'�� ���������� �������'), $values['sort']).
                _check('desc', '�������� �������', $values['desc']).
                '<div class="condLost'.(!empty($values['find']) ? ' hide' : '').'">'.
                    '<div class="findHead">������ ������</div>'.
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
}//end of zayav_list()
function zayav_spisok($data) {
    if(!isset($data['spisok']))
        return '<div class="_empty">������ �� �������.</div>';
    $send = '';
    foreach($data['spisok'] as $id => $r)
        $send .=
        '<div class="zayav_unit" style="background-color:#'.$r['status_color'].'" val="'.$id.'">'.
            '<h2>#'.$id.'</h2>'.
//            '<a class="name">'..'</a>'.
            '<table style="border-spacing:2px">'.
                (isset($r['client']) ? '<tr><td class="label">������:<td>'.$r['client'] : '').
                '<tr><td class="label">�������:<td>'._product($r['product_id']).
                '<tr><td class="label">���� ������:<td>'.$r['dtime'].
            '</table>'.
        '</div>';
    if(isset($data['next']))
        $send .= '<div class="ajaxNext" val="'.($data['next']).'"><span>��������� '.$data['limit'].' ������</span></div>';
    return $send;
}//end of zayav_spisok()

function zayav_info($zayav_id) {
    $sql = "SELECT * FROM `zayav` WHERE `status`>0 AND `id`=".$zayav_id." LIMIT 1";
    if(!$zayav = mysql_fetch_assoc(query($sql)))
        return '������ �� ����������.';
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
		WHERE `status`=1
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

    return
    '<script type="text/javascript">'.
        'var ZAYAV={'.
            'id:'.$zayav_id.','.
            'client_id:'.$zayav['client_id'].','.
            'nomer_dog:"'.$zayav['nomer_dog'].'",'.
            'nomer_vg:"'.$zayav['nomer_vg'].'",'.
            'product_id:'.$zayav['product_id'].','.
            'adres_set:"'.$zayav['adres_set'].'",'.
            'prihodtype:'._prihodType(false, 'json').','.
            'prihodkassa:'._prihodKassa().','.
            'product:'._product(false, 'json').
        '};'.
    '</script>'.
    '<div id="zayavInfo">'.
        '<div id="dopLinks">'.
            '<a class="delete'.(!empty($money) ?  ' dn': '').'">������� ������</a>'.
            '<a class="link sel">����������</a>'.
            '<a class="link zedit">��������������</a>'.
            '<a class="link acc_add">���������</a>'.
            '<a class="link op_add">������� �����</a>'.
        '</div>'.
        '<div class="content">'.
            '<div class="headName">������ �'.$zayav_id.'</div>'.
            '<table class="tabInfo">'.
                '<tr><td class="label">������:<td>'._clientLink($zayav['client_id']).
                '<tr><td class="label">����� ��������:<td>'.$zayav['nomer_dog'].
                '<tr><td class="label">����� ��:<td>'.$zayav['nomer_vg'].
                '<tr><td class="label">�������:<td>'._product($zayav['product_id']).
                '<tr><td class="label">����� ���������:<td>'.$zayav['adres_set'].
                '<tr><td class="label">���� �����:'.
                    '<td class="dtime_add" title="������ ���� '._viewerName($zayav['viewer_id_add']).'">'.FullDataTime($zayav['dtime_add']).
                '<tr><td class="label">������:'.
                    '<td><div id="status" style="background-color:#'._zayavStatusColor($zayav['status']).'" class="status_place">'.
                            _zayavStatusName($zayav['status']).
                        '</div>'.
                        '<div id="status_dtime">�� '.FullDataTime($zayav['status_dtime'], 1).'</div>'.
                '<tr class="acc_tr'.($accSum > 0 ? '' : ' dn').'"><td class="label">���������: <td><b class="acc">'.$accSum.'</b> ���.'.
                '<tr class="op_tr'.($opSum > 0 ? '' : ' dn').'"><td class="label">��������:    <td><b class="op">'.$opSum.'</b> ���.'.
                    '<span class="dopl'.($dopl == 0 ? ' dn' : '').'" title="����������� �������'."\n".'���� �������� �������������, �� ��� ���������">'.
                        ($dopl > 0 ? '+' : '').$dopl.
                    '</span>'.
            '</table>'.
    //        '<div class="headBlue">�������<a class="add remind_add">�������� �������</a></div>'.
    //        '<div id="remind_spisok">'.report_remind_spisok(remind_data(1, array('zayav'=>$zayav['id']))).'</div>'.
            _vkComment('zayav', $zayav_id).
            '<div class="headBlue mon">���������� � �������'.
                '<a class="add op_add">������� �����</a>'.
                '<em>::</em>'.
                '<a class="add acc_add">���������</a>'.
            '</div>'.
            '<table class="_spisok _money">'.implode($money).'</table>'.
        '</div>'.
    '</div>';
}//end of zayav_info()
function zayav_accrual_unit($acc) {
    return
    '<tr><td class="sum acc" title="����������">'.$acc['sum'].'</td>'.
        '<td>'.$acc['prim'].'</td>'.
        '<td class="dtime" title="�������� '._viewerName(isset($acc['viewer_id_add']) ? $acc['viewer_id_add'] : VIEWER_ID).'">'.
            FullDataTime(isset($acc['dtime_add']) ? $acc['dtime_add'] : curTime()).
        '</td>'.
        '<td class="del"><div class="img_del acc_del" title="������� ����������" val="'.$acc['id'].'"></div></td>'.
    '</tr>';
}//end of zayav_accrual_unit()
function zayav_oplata_unit($op) {
    return
    '<tr><td class="sum op" title="�����">'.$op['sum'].'</td>'.
        '<td><em>'._prihodType($op['prihod_type']).($op['prim'] ? ':' : '').'</em>'.$op['prim'].'</td>'.
        '<td class="dtime" title="����� ���� '._viewerName(isset($op['viewer_id_add']) ? $op['viewer_id_add'] : VIEWER_ID).'">'.
            FullDataTime(isset($op['dtime_add']) ? $op['dtime_add'] : curTime()).
        '</td>'.
        '<td class="del"><div class="img_del op_del" title="������� �����" val="'.$op['id'].'"></div></td>'.
    '</tr>';
}//end of zayav_oplata_unit()




function setup() {
    switch(@$_GET['d']) {
        default: $_GET['d'] = 'worker';
        case 'worker': $left = setup_worker(); break;
        case 'product': $left = setup_product(); break;
        case 'prihodtype': $left = setup_prihodtype(); break;
    }
    $right = '<div class="rightLink">'.
        '<a href="'.URL.'&p=setup&d=worker"'.(@$_GET['d'] == 'worker' ? ' class="sel"' : '').'>����������</a>'.
        '<a href="'.URL.'&p=setup&d=product"'.(@$_GET['d'] == 'product' ? ' class="sel"' : '').'>���� �������</a>'.
        '<a href="'.URL.'&p=setup&d=prihodtype"'.(@$_GET['d'] == 'prihodtype' ? ' class="sel"' : '').'>���� ��������</a>'.
    '</div>';
    return
    '<div id="setup">'.
        '<table class="tabLR">'.
            '<tr><td class="left">'.$left.
                '<td class="right">'.$right.
        '</table>'.
    '</div>';
}//end of setup()
function setup_worker() {
    return
    '<div id="setup_worker">'.
        '<div class="headName">���������� ������������</div>'.
    '</div>';
}//end of setup_worker()
function setup_product() {
    return
    '<div id="setup_product">'.
        '<div class="headName">��������� ����� �������<a class="add">��������</a></div>'.
        '<div class="spisok">'.setup_product_spisok().'</div>'.
    '</div>';
}//end of setup_product()
function setup_product_spisok() {
    $sql = "SELECT * FROM `setup_product` ORDER BY `sort`";
    $q = query($sql);
    $send = '';
    if(mysql_num_rows($q)) {
        $send =
        '<table class="_spisok">'.
            '<tr><th class="name">������������'.
                '<th class="set">'.
        '</table>'.
        '<dl class="_sort" val="setup_product">';
        while($r = mysql_fetch_assoc($q))
            $send .='<dd val="'.$r['id'].'">'.
                '<table class="_spisok">'.
                    '<tr><td class="name">'.$r['name'].
                        '<td class="set"><div class="img_edit"></div><div class="img_del"></div>'.
                '</table>';
        $send .= '</dl>';
    }
    return $send ? $send : '������ ����.';
}//end of setup_product_spisok()
function setup_prihodtype() {
    return
    '<div id="setup_prihodtype">'.
        '<div class="headName">��������� ����� ��������<a class="add">��������</a></div>'.
        '<div class="spisok">'.setup_prihodtype_spisok().'</div>'.
    '</div>';
}//end of setup_prihodtype()
function setup_prihodtype_spisok() {
    $sql = "SELECT * FROM `setup_prihodtype` ORDER BY `sort`";
    $q = query($sql);
    $send = '';
    if(mysql_num_rows($q)) {
        $send =
        '<table class="_spisok">'.
            '<tr><th class="name">������������'.
                '<th class="kassa">�����������<br />��������<br />� �����'.
                '<th class="set">'.
        '</table>'.
        '<dl class="_sort" val="setup_prihodtype">';
        while($r = mysql_fetch_assoc($q))
            $send .='<dd val="'.$r['id'].'">'.
            '<table class="_spisok">'.
                '<tr><td class="name">'.$r['name'].
                    '<td class="kassa">'.($r['kassa_put'] ? '��' : '').
                    '<td class="set"><div class="img_edit"></div><div class="img_del"></div>'.
            '</table>';
        $send .= '</dl>';
    }
    return $send ? $send : '������ ����.';
}//end of setup_prihodtype_spisok()