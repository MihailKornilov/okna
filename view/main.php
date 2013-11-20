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
}//_cacheClear()

function _header() {
    global $html;
    $html =
        '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
        '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.

        '<head>'.
        '<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
        '<title>Пластиковые окна - Приложение 3978722</title>'.

        //Отслеживание ошибок в скриптах
        (SA ? '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/errors-utf8.js?'.VERSION.'"></script>' : '').

        //Стороние скрипты
        '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/jquery-2.0.3.min.js"></script>'.
//        '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/highstock.js"></script>'.
        '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/'.(DEBUG ? '' : 'min/').'xd_connection.js"></script>'.

        //Установка начального значения таймера.
        (SA ? '<script type="text/javascript">var TIME=(new Date()).getTime();</script>' : '').

        '<script type="text/javascript">'.
            (LOCAL ? 'for(var i in VK)if(typeof VK[i]=="function")VK[i]=function(){return false};' : '').
            'var G={},'.
            'DOMAIN="'.DOMAIN.'",'.
            'VALUES="'.VALUES.'",'.
            'VIEWER_ID='.VIEWER_ID.';'.
        '</script>'.

        //Подключение стилей VK. Должны стоять до основных стилей сайта
        '<link href="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/'.(DEBUG ? '' : 'min/').'vk.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.

        '<link href="'.SITE.'/css/main.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
        '<script type="text/javascript" src="'.SITE.'/js/main.js?'.VERSION.'"></script>'.
        '<script type="text/javascript" src="'.SITE.'/js/G_values.js?'.G_VALUES_VERSION.'"></script>'.

        //Подключение API VK
        '<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/vk/'.(DEBUG ? '' : 'min/').'vk.js?'.VERSION.'"></script>'.

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
    $html .= '<script type="text/javascript">'.
        'hashSet({'.implode(',', $gValues).'});'.
        (SA ? '$("#admin EM").html(((new Date().getTime())-TIME)/1000);' : '').
        '</script>'.
        '</div></body></html>';
}//_footer()

function GvaluesCreate() {//Составление файла G_values.js
    $save = //'function _toSpisok(s){var a=[];for(k in s)a.push({uid:k,title:s[k]});return a}'.
        //'function _toAss(s){var a=[];for(var n=0;n<s.length;a[s[n].uid]=s[n].title,n++);return a}'.
        'var PRODUCT_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_product` ORDER BY `name`").
         //',PRODUCT_ASS=_toSpisok(PRODUCT_ASS)'.
           ',PRIHODTYPE_SPISOK='.query_selJson("SELECT `id`,`name` FROM `setup_prihodtype` ORDER BY `sort`").
           ',PRIHODKASSA_ASS='.query_ptpJson("SELECT `id`,`kassa_put` FROM `setup_prihodtype` WHERE `kassa_put`=1");

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
    $save .= 'PRODUCT_SUB_SPISOK={'.implode(',', $v).'}';
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
function _prihodType($type_id=false) {//Список изделий для заявок
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
    return $type_id !== false ? constant('PRIHODTYPE_'.$type_id) : $arr;
}//_prihodType()


function _mainLinks() {
    global $html;
//    _remindActiveSet();
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
            $send .= '<a href="'.URL.'&p='.$l['page'].'"'.($l['page'] == $_GET['p'] ? 'class="sel"' : '').'>'.$l['name'].'</a>';
    $send .= '</div>';
    $html .= $send;
}//_mainLinks()

function rulesList($v=false) {
    $rules = array(
        'RULES_APPENTER' => 1,   // Разрешать вход в приложение
        'RULES_SETUP' => 1,      // Управление установками
        'RULES_WORKER' => 1,     // Сотрудники
        'RULES_PRODUCT' => 1,    // Виды изделий
        'RULES_PRIHODTYPE' => 1  // Виды платежей
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
    $prihod = query_value("SELECT SUM(`sum`) FROM `money` WHERE `status`=1 AND `client_id`=".$client_id." AND `sum`>0");
    $acc = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE `status`=1 AND `client_id`=".$client_id);
    $balans = $prihod - $acc;
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
            WHERE `status`>0
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

function client_info($client_id) {
    $sql = "SELECT * FROM `client` WHERE `status`=1 AND `id`=".$client_id;
    if(!$client = mysql_fetch_assoc(query($sql)))
        return 'Клиента не существует';

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
    $money = '<div class="_empty">Платежей нет.</div>';
    if($moneyCount) {
        $money = '<table class="_spisok _money">'.
            '<tr><th class="sum">Сумма'.
            '<th>Описание'.
            '<th class="data">Дата';
        while($r = mysql_fetch_assoc($q)) {
            $about = '';
            if($r['zayav_id'] > 0)
                $about .= 'Заявка '.$r['zayav_id'].'. ';
            $about .= $r['prim'];
            $money .= '<tr><td class="sum"><b>'.$r['sum'].'</b>'.
                '<td>'.$about.
                '<td class="dtime" title="Внёс: '._viewer($r['viewer_id_add'], 'name').'">'.FullDataTime($r['dtime_add']);
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
                            '<tr><td class="label">Телефон:<td class="telefon">'.$client['telefon'].'</TD>'.
                            '<tr><td class="label">Адрес:  <td class="adres">'.$client['adres'].'</TD>'.
                            '<tr><td class="label">Баланс: <td><b style=color:#'.($client['balans'] < 0 ? 'A00' : '090').'>'.$client['balans'].'</b>'.
                        '</table>'.
                        '<div class="dtime">Клиента внёс '._viewer($client['viewer_id_add'], 'name').' '.FullData($client['dtime_add'], 1).'</div>'.
                    '</div>'.
                    '<div id="dopLinks">'.
                        '<a class="link sel" val="zayav">Заявки'.($zayavData['all'] ? ' ('.$zayavData['all'].')' : '').'</a>'.
                        '<a class="link" val="money">Платежи'.($moneyCount ? ' ('.$moneyCount.')' : '').'</a>'.
                        '<a class="link" val="remind">Задания'.(!empty($remindData) ? ' ('.$remindData['all'].')' : '').'</a>'.
                        '<a class="link" val="comm">Заметки'.($commCount ? ' ('.$commCount.')' : '').'</a>'.
                    '</div>'.
                    '<div id="zayav_spisok">'.zayav_spisok($zayavData).'</div>'.
                    '<div id="money_spisok">'.$money.'</div>'.
                    '<div id="remind_spisok">'.(!empty($remindData) ? report_remind_spisok($remindData) : '<div class="_empty">Заданий нет.</div>').'</div>'.
                    '<div id="comments">'._vkComment('client', $client_id).'</div>'.
                '<td class="right">'.
                    '<div class="rightLink">'.
                        '<a class="sel">Информация</a>'.
                        '<a class="cedit">Редактировать</a>'.
                        '<a href="'.URL.'&p=zayav&d=add&back=client&id='.$client_id.'"><b>Новая заявка</b></a>'.
                        '<a class="remind_add">Новое задание</a>'.
                        '<a class="cdel">Удалить клиента</a>'.
                    '</div>'.
                    '<div id="zayav_filter">'.
                        '<div id="zayav_result">'.zayav_count($zayavData['all'], 0).'</div>'.
                        '<div class="findHead">Статус заявки</div>'.
                        _rightLink('status', _zayavStatusName()).
                    '</div>'.
        '</table>'.
    '</div>';
}//client_info()



// ---===! zayav !===--- Секция заявок

function _zayavStatus($id=false) {
    $arr = array(
        '0' => array(
            'name' => 'Любой статус',
            'color' => 'ffffff'
        ),
        '1' => array(
            'name' => 'Ожидает выполнения',
            'color' => 'E8E8FF'
        ),
        '2' => array(
            'name' => 'Выполнено!',
            'color' => 'CCFFCC'
        ),
        '3' => array(
            'name' => 'Завершить не удалось',
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

function zayav_add($v=array()) {
    return
    '<div id="zayavAdd">'.
        '<div class="headName">Внесение новой заявки</div>'.
        '<table style="border-spacing:8px">'.
            '<tr><td class="label">Клиент:         <td><INPUT TYPE="hidden" id="client_id" value="'.$v['client_id'].'" />'.
            '<tr><td class="label">Номер договора: <td><INPUT type="text" id="nomer_dog" maxlength="30" />'.
            '<tr><td class="label">Номер ВГ:       <td><INPUT type="text" id="nomer_vg" maxlength="30" />'.
            '<tr><td class="label">Изделие:        <td><INPUT type="hidden" id="product_id" value="0" />'.
                (RULES_PRODUCT ? '<a href="'.URL.'&p=setup&d=product" class="img_edit product_edit" title="Настроить список изделий"></a>' : '').
            '<tr><td class="label">Адрес установки:<td><INPUT type="text" id="adres_set" maxlength="100" />'.
            '<tr><td class="label top">Заметка:    <td><textarea id="comm"></textarea>'.
        '</table>'.
        '<div class="vkButton"><button>Внести</button></div>'.
        '<div class="vkCancel" val="'.$v['back'].'"><button>Отмена</button></div>'.
    '</div>';
}//zayav_add()

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
                '<div id="buttonCreate"><a HREF="'.URL.'&p=zayav&d=add&back=zayav">Новая заявка</a></div>'.
                '<div id="find"></div>'.
                '<div class="findHead">Порядок</div>'.
//                _radio('sort', array(1=>'По дате добавления',2=>'По обновлению статуса'), $values['sort']).
                _check('desc', 'Обратный порядок', $values['desc']).
                '<div class="condLost'.(!empty($values['find']) ? ' hide' : '').'">'.
                    '<div class="findHead">Статус заявки</div>'.
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
            '<h2>#'.$id.'</h2>'.
//            '<a class="name">'..'</a>'.
            '<table style="border-spacing:2px">'.
                (isset($r['client']) ? '<tr><td class="label">Клиент:<td>'.$r['client'] : '').
                '<tr><td class="label">Изделие:<td>'._product($r['product_id']).
                '<tr><td class="label">Дата подачи:<td>'.$r['dtime'].
            '</table>'.
        '</div>';
    if(isset($data['next']))
        $send .= '<div class="ajaxNext" val="'.($data['next']).'"><span>Следующие '.$data['limit'].' заявок</span></div>';
    return $send;
}//zayav_spisok()

function zayav_info($zayav_id) {
    $sql = "SELECT * FROM `zayav` WHERE `status`>0 AND `id`=".$zayav_id." LIMIT 1";
    if(!$zayav = mysql_fetch_assoc(query($sql)))
        return 'Заявки не существует.';
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
            'adres_set:"'.$zayav['adres_set'].'"'.
        '};'.
    '</script>'.
    '<div id="zayavInfo">'.
        '<div id="dopLinks">'.
            '<a class="delete'.(!empty($money) ?  ' dn': '').'">Удалить заявку</a>'.
            '<a class="link sel">Информация</a>'.
            '<a class="link zedit">Редактирование</a>'.
            '<a class="link acc_add">Начислить</a>'.
            '<a class="link op_add">Принять платёж</a>'.
        '</div>'.
        '<div class="content">'.
            '<div class="headName">Заявка №'.$zayav_id.'</div>'.
            '<table class="tabInfo">'.
                '<tr><td class="label">Клиент:<td>'._clientLink($zayav['client_id']).
                '<tr><td class="label">Номер договора:<td>'.$zayav['nomer_dog'].
                '<tr><td class="label">Номер ВГ:<td>'.$zayav['nomer_vg'].
                '<tr><td class="label">Изделие:<td>'._product($zayav['product_id']).
                '<tr><td class="label">Адрес установки:<td>'.$zayav['adres_set'].
                '<tr><td class="label">Дата приёма:'.
                    '<td class="dtime_add" title="Заявку внёс '._viewer($zayav['viewer_id_add'], 'name').'">'.FullDataTime($zayav['dtime_add']).
                '<tr><td class="label">Статус:'.
                    '<td><div id="status" style="background-color:#'._zayavStatusColor($zayav['status']).'" class="status_place">'.
                            _zayavStatusName($zayav['status']).
                        '</div>'.
                        '<div id="status_dtime">от '.FullDataTime($zayav['status_dtime'], 1).'</div>'.
                '<tr class="acc_tr'.($accSum > 0 ? '' : ' dn').'"><td class="label">Начислено: <td><b class="acc">'.$accSum.'</b> руб.'.
                '<tr class="op_tr'.($opSum > 0 ? '' : ' dn').'"><td class="label">Оплачено:    <td><b class="op">'.$opSum.'</b> руб.'.
                    '<span class="dopl'.($dopl == 0 ? ' dn' : '').'" title="Необходимая доплата'."\n".'Если значение отрицательное, то это переплата">'.
                        ($dopl > 0 ? '+' : '').$dopl.
                    '</span>'.
            '</table>'.
    //        '<div class="headBlue">Задания<a class="add remind_add">Добавить задание</a></div>'.
    //        '<div id="remind_spisok">'.report_remind_spisok(remind_data(1, array('zayav'=>$zayav['id']))).'</div>'.
            _vkComment('zayav', $zayav_id).
            '<div class="headBlue mon">Начисления и платежи'.
                '<a class="add op_add">Принять платёж</a>'.
                '<em>::</em>'.
                '<a class="add acc_add">Начислить</a>'.
            '</div>'.
            '<table class="_spisok _money">'.implode($money).'</table>'.
        '</div>'.
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



// ---===! report !===--- Секция отчётов

function report() {
    $menu = '<div class="rightLink">'.
        '<a class="sel">История действий</a>'.
        '<a>Платежи</a>'.
    '</div>';
    switch(@$_GET['d']) {
        default:
        case 'history':
            $left = report_history_spisok();
            break;
    }
    return
    '<table class="tabLR" id="report">'.
        '<tr><td class="left">'.$left.
            '<td class="right">'.$menu.
    '</table>';
}//report()

function history_insert($arr) {
    $sql = "INSERT INTO `history` (
			   `type`,
			   `value`,
			   `value1`,
			   `value2`,
			   `client_id`,
			   `zayav_id`,
			   `viewer_id_add`
			) VALUES (
				".$arr['type'].",
				'".(isset($arr['value']) ? $arr['value'] : '')."',
				'".(isset($arr['value1']) ? $arr['value1'] : '')."',
				'".(isset($arr['value2']) ? $arr['value2'] : '')."',
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

        case 4: return 'Внесение новой заявки '.$v['zayav'].' для клиента '.$v['client'].'.';
        case 5: return 'Изменение данных заявки '.$v['zayav'].':<div class="changes">'.$v['value'].'</div>';
        case 6: return 'Удаление заявки '.$v['zayav'].'.';

        case 7: return 'Начисление на сумму <b>'.$v['value'].'</b> руб.'.($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').' по заявке '.$v['zayav'].'.';
        case 8: return 'Удаление начисления на сумму <b>'.$v['value'].'</b> руб.'.($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').' у заявки '.$v['zayav'].'.';
        case 9: return 'Восстановление начисления на сумму <b>'.$v['value'].'</b> руб.'.($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').' у заявки '.$v['zayav'].'.';

        case 10: return
            'Платёж "<span class="oplata">'._prihodType($v['value2']).'</span>" '.
            'на сумму <b>'.$v['value'].'</b> руб.'.
            ($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').
            ' по заявке '.$v['zayav'].'.';
        case 11: return
            'Удаление платежа "<span class="oplata">'._prihodType($v['value2']).'</span>" '.
            'на сумму <b>'.$v['value'].'</b> руб.'.
            ($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').
            ' у заявки '.$v['zayav'].'.';
        case 12: return
            'Восстановление платежа "<span class="oplata">'._prihodType($v['value2']).'</span>" '.
            'на сумму <b>'.$v['value'].'</b> руб.'.
            ($v['value1'] ? '<span class="prim">('.$v['value1'].')</span>' : '').
            ' у заявки '.$v['zayav'].'.';

        case 13: return 'Добавление нового сотрудника '._viewer($v['value'], 'link').'.';
        case 14: return 'Удаление сотрудника '._viewer($v['value'], 'link').'.';

        case 501: return 'В установках: внесение нового наименования изделия "'.$v['value'].'".';
        case 502: return 'В установках: изменение наименования изделия:<div class="changes">'.$v['value'].'</div>';
        case 503: return 'В установках: удаление наименования изделия "'.$v['value'].'".';
        default: return $v['type'];
    }
}//history_types()
function report_history_spisok($page=1) {
    $limit = 30;
    $cond = "";
    $sql = "SELECT COUNT(`id`) AS `all`
			FROM `history`";
    $all = query_value($sql);
    if(!$all)
        return 'Истории по указанным условиям нет.';
    $start = ($page - 1) * $limit;

    $sql = "SELECT *
			FROM `history`
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
        if($r['zayav_id'])
            $r['zayav'] = '<a href="'.URL.'&p=zayav&d=info&id='.$r['zayav_id'].'">№'.$r['zayav_id'].'</a>';
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




// ---===! setup !===--- Секция настроек

function setup() {
    $pageDef = 'worker';
    $pages = array(
        'worker' => 'Сотрудники',
        'product' => 'Виды изделий',
        'prihodtype' => 'Виды платежей'
    );

    if(!RULES_WORKER)
        unset($pages['worker']);
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
                $left = setup_rules(intval($_GET['id']));
                break;
            }
            $left = setup_worker();
            break;
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
function setup_rules($viewer_id) {
    $u = _viewer($viewer_id);
    if(!RULES_WORKER)
        return _norules('Настройка прав для сотрудника '.$u['name']);
    if(!isset($u['worker']))
        return 'Сотрудника не существует.';
    if($u['admin'])
        return 'Невозможно изменять права сотрудника <b>'.$u['name'].'</b>.';
//    print_r(workerRulesArray($u['rules']));
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
                        _check('rules_product', 'Виды изделий', $rule['RULES_PRODUCT']).
                        _check('rules_prihodtype', 'Виды платежей', $rule['RULES_PRIHODTYPE']).
                    '</div>'.
            '</table>'.
        '</div>'.
    '</div>';
}//setup_rules()


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
                 `zayav` AS `z`
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
    $sql = "SELECT * FROM `setup_product_sub` WHERE `product_id`=".$product_id." ORDER BY `name`";
    $q = query($sql);
    $send = '';
    if(mysql_num_rows($q)) {
        $send = '<table class="_spisok">'.
            '<tr><th>Наименование'.
            '<th>Кол-во<br />заявок'.
            '<th>';
        while($r = mysql_fetch_assoc($q))
            $send .= '<tr val="'.$r['id'].'">'.
                '<td class="name">'.$r['name'].
                '<td>'.
                '<td><div class="img_edit"></div><div class="img_del"></div>';
        $send .= '</table>';
    }
    return $send ? $send : 'Список пуст.';
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
    $sql = "SELECT * FROM `setup_prihodtype` ORDER BY `sort`";
    $q = query($sql);
    $send = '';
    if(mysql_num_rows($q)) {
        $send =
        '<table class="_spisok">'.
            '<tr><th class="name">Наименование'.
                '<th class="kassa">Возможность<br />внесения<br />в кассу'.
                '<th class="set">'.
        '</table>'.
        '<dl class="_sort" val="setup_prihodtype">';
        while($r = mysql_fetch_assoc($q))
            $send .='<dd val="'.$r['id'].'">'.
            '<table class="_spisok">'.
                '<tr><td class="name">'.$r['name'].
                    '<td class="kassa">'.($r['kassa_put'] ? 'да' : '').
                    '<td class="set"><div class="img_edit"></div><div class="img_del"></div>'.
            '</table>';
        $send .= '</dl>';
    }
    return $send ? $send : 'Список пуст.';
}//setup_prihodtype_spisok()