<?php
function _vkUserUpdate($uid=VIEWER_ID) {//Обновление пользователя из Контакта
    require_once(DOCUMENT_ROOT.'/include/vkapi.class.php');
    $VKAPI = new vkapi($_GET['api_id'], SECRET);
    $res = $VKAPI->api('users.get',array('uids' => $uid, 'fields' => 'photo,sex,country,city'));
    $u = $res['response'][0];
    $u['first_name'] = win1251($u['first_name']);
    $u['last_name'] = win1251($u['last_name']);
    $u['country_id'] = isset($u['country']) ? $u['country'] : 0;
    $u['city_id'] = isset($u['city']) ? $u['city'] : 0;
    $u['menu_left_set'] = 0;

    // установил ли приложение
    $app = $VKAPI->api('isAppUser', array('uid'=>$uid));
    $u['app_setup'] = $app['response'];

    // поместил ли в левое меню
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
}//ens of _cacheClear()

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

        //Подключение API VK
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
}//end of _footer()

function _checkbox($id, $txt='', $value=0) {
    return '<input type="hidden" id="'.$id.'" value="'.$value.'" />'.
    '<div class="check'.$value.'" id="'.$id.'_check">'.$txt.'</div>';
}//end of _checkbox()
function _radio($id, $list, $value=0, $light=false) {
    $spisok = '';
    foreach($list as $uid => $title)
        $spisok .= '<div class="'.($uid == $value ? 'on' : 'off').($light ? ' l' : '').'" val="'.$uid.'">'.$title.'</div>';
    return
        '<div class="_radio" id="'.$id.'_radio">'.
        '<input type="hidden" id="'.$id.'" value="'.$value.'">'.
        $spisok.
        '</div>';
}//end of _radio()

function _end($count, $o1, $o2, $o5=false) {
    if($o5 === false) $o5 = $o2;
    if($count / 10 % 10 == 1)
        return $o5;
    else
        switch($count % 10) {
            case 1: return $o1;
            case 2: return $o2;
            case 3: return $o2;
            case 4: return $o2;
        }
    return $o5;
}//end of _end()

function _monthFull($n=0) {
    $mon = array(
        1 => 'января',
        2 => 'февраля',
        3 => 'марта',
        4 => 'апреля',
        5 => 'мая',
        6 => 'июня',
        7 => 'июля',
        8 => 'августа',
        9 => 'сентября',
        10 => 'октября',
        11 => 'ноября',
        12 => 'декабря'
    );
    return $n ? $mon[intval($n)] : $mon;
}//end of _monthFull
function _monthDef($n=0) {
    $mon = array(
        1 => 'январь',
        2 => 'февраль',
        3 => 'март',
        4 => 'апрель',
        5 => 'май',
        6 => 'июнь',
        7 => 'июль',
        8 => 'август',
        9 => 'сентябрь',
        10 => 'октябрь',
        11 => 'ноябрь',
        12 => 'декабрь'
    );
    return $n ? $mon[intval($n)] : $mon;
}//end of _monthFull
function _monthCut($n) {
    $mon = array(
        1 => 'янв',
        2 => 'фев',
        3 => 'мар',
        4 => 'апр',
        5 => 'май',
        6 => 'июн',
        7 => 'июл',
        8 => 'авг',
        9 => 'сен',
        10 => 'окт',
        11 => 'ноя',
        12 => 'дек'
    );
    return $mon[intval($n)];
}//end of _monthCut
function FullData($value, $noyear=false) {//14 апреля 2010
    $d = explode('-', $value);
    return
        abs($d[2]).' '.
        _monthFull($d[1]).
        (!$noyear || date('Y') != $d[0] ? ' '.$d[0] : '');
}//end of FullData()
function FullDataTime($value, $cut=false) {//14 апреля 2010 в 12:45
    $arr = explode(' ',$value);
    $d = explode('-',$arr[0]);
    $t = explode(':',$arr[1]);
    return
        abs($d[2]).' '.
        ($cut ? _monthCut($d[1]) : _monthFull($d[1])).
        (date('Y') == $d[0] ? '' : ' '.$d[0]).
        ' в '.$t[0].':'.$t[1];
}//end of FullDataTime()

function _vkComment($table, $id=0) {
    $sql = "SELECT *
            FROM `vk_comment`
            WHERE `status`=1
              AND `table_name`='".$table."'
              AND `table_id`=".intval($id)."
            ORDER BY `dtime_add` ASC";
    $count = 'Заметок нет';
    $units = '';
    $q = query($sql);
    if(mysql_num_rows($q)) {
        $comm = array();
        $v = array();
        while($r = mysql_fetch_assoc($q)) {
            if(!$r['parent_id'])
                $comm[$r['id']] = $r;
            elseif(isset($comm[$r['parent_id']]))
                $comm[$r['parent_id']]['childs'][] = $r;
            $v[$r['viewer_id_add']] = $r['viewer_id_add'];
        }
        $count = count($comm);
        $count = 'Всего '.$count.' замет'._end($count, 'ка', 'ки','ок');
        $v = _viewersInfo($v);
        $comm = array_reverse($comm);
        foreach($comm as $n => $r) {
            $childs = array();
            if(!empty($r['childs']))
                foreach($r['childs'] as $c)
                    $childs[] = _vkCommentChild($c['id'], $v[$c['viewer_id_add']], $c['txt'], $c['dtime_add']);
            $units .= _vkCommentUnit($r['id'], $v[$r['viewer_id_add']], $r['txt'], $r['dtime_add'], $childs, ($n+1));
        }
    }
    return '<div class="vkComment" val="'.$table.'_'.$id.'">'.
    '<div class=headBlue><div class="count">'.$count.'</div>Заметки</div>'.
    '<div class="add">'.
    '<textarea>Добавить заметку...</textarea>'.
    '<div class="vkButton"><button>Добавить</button></div>'.
    '</div>'.
    $units.
    '</div>';
}//end of _vkComment
function _vkCommentUnit($id, $viewer, $txt, $dtime, $childs=array(), $n=0) {
    return '<div class="cunit" val="'.$id.'">'.
    '<table class="t">'.
    '<tr><td class="ava">'.$viewer['photo'].
    '<td class="i">'.$viewer['link'].
    ($viewer['id'] == VIEWER_ID || VIEWER_ADMIN ? '<div class="img_del unit_del" title="Удалить заметку"></div>' : '').
    '<div class="ctxt">'.$txt.'</div>'.
    '<div class="cdat">'.FullDataTime($dtime, 1).
    '<SPAN'.($n == 1  && !empty($childs) ? ' class="hide"' : '').'> | '.
    '<a>'.(empty($childs) ? 'Комментировать' : 'Комментарии ('.count($childs).')').'</a>'.
    '</SPAN>'.
    '</div>'.
    '<div class="cdop'.(empty($childs) ? ' empty' : '').($n == 1 && !empty($childs) ? '' : ' hide').'">'.
    implode('', $childs).
    '<div class="cadd">'.
    '<textarea>Комментировать...</textarea>'.
    '<div class="vkButton"><button>Добавить</button></div>'.
    '</div>'.
    '</div>'.
    '</table></div>';
}//end of _vkCommentUnit()
function _vkCommentChild($id, $viewer, $txt, $dtime) {
    return '<div class="child" val="'.$id.'">'.
    '<table class="t">'.
    '<tr><td class="dava">'.$viewer['photo'].
    '<td class="di">'.$viewer['link'].
    ($viewer['id'] == VIEWER_ID || VIEWER_ADMIN ? '<div class="img_del child_del" title="Удалить комментарий"></div>' : '').
    '<div class="dtxt">'.$txt.'</div>'.
    '<div class="ddat">'.FullDataTime($dtime, 1).'</div>'.
    '</table></div>';
}//end of _vkCommentChild()

function _curMonday() { //Понедельник в текущей неделе
    // Номер текущего дня недели
    $time = time();
    $curDay = date("w", $time);
    if($curDay == 0) $curDay = 7;
    // Приведение дня к понедельнику
    $time -= 86400 * ($curDay - 1);
    return strftime('%Y-%m-%d', $time);
}//end of _curMonday()
function _curSunday() { //Воскресенье в текущей неделе
    $time = time();
    $curDay = date("w", $time);
    if($curDay == 0) $curDay = 7;
    $time += 86400 * (7 - $curDay);
    return strftime('%Y-%m-%d', $time);

}//end of _curSunday()

function _engRusChar($word) { //Перевод символов раскладки с английского на русский
    $char = array(
        'q' => 'й',
        'w' => 'ц',
        'e' => 'у',
        'r' => 'к',
        't' => 'е',
        'y' => 'н',
        'u' => 'г',
        'i' => 'ш',
        'o' => 'щ',
        'p' => 'з',
        '[' => 'х',
        ']' => 'ъ',
        'a' => 'ф',
        's' => 'ы',
        'd' => 'в',
        'f' => 'а',
        'g' => 'п',
        'h' => 'р',
        'j' => 'о',
        'k' => 'л',
        'l' => 'д',
        ';' => 'ж',
        "'" => 'э',
        'z' => 'я',
        'x' => 'ч',
        'c' => 'с',
        'v' => 'м',
        'b' => 'и',
        'n' => 'т',
        'm' => 'ь',
        ',' => 'б',
        '.' => 'ю',
        '0' => '0',
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
        '6' => '6',
        '7' => '7',
        '8' => '8',
        '9' => '9'
    );
    $send = '';
    for($n = 0; $n < strlen($word); $n++)
        if(isset($char[$word[$n]]))
            $send .= $char[$word[$n]];
    return $send;
}
function unescape($str){
    $escape_chars = '0410 0430 0411 0431 0412 0432 0413 0433 0490 0491 0414 0434 0415 0435 0401 0451 0404 0454 '.
        '0416 0436 0417 0437 0418 0438 0406 0456 0419 0439 041A 043A 041B 043B 041C 043C 041D 043D '.
        '041E 043E 041F 043F 0420 0440 0421 0441 0422 0442 0423 0443 0424 0444 0425 0445 0426 0446 '.
        '0427 0447 0428 0448 0429 0449 042A 044A 042B 044B 042C 044C 042D 044D 042E 044E 042F 044F';
    $russian_chars = 'А а Б б В в Г г Ґ ґ Д д Е е Ё ё Є є Ж ж З з И и І і Й й К к Л л М м Н н О о П п Р р С с Т т У у Ф ф Х х Ц ц Ч ч Ш ш Щ щ Ъ ъ Ы ы Ь ь Э э Ю ю Я я';
    $e = explode(' ', $escape_chars);
    $r = explode(' ', $russian_chars);
    $rus_array = explode('%u', $str);
    $new_word = str_replace($e, $r, $rus_array);
    $new_word = str_replace('%20', ' ', $new_word);
    return implode($new_word);
}

function win1251($txt) { return iconv('UTF-8','WINDOWS-1251',$txt); }
function utf8($txt) { return iconv('WINDOWS-1251','UTF-8',$txt); }
function curTime() { return strftime('%Y-%m-%d %H:%M:%S',time()); }

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

function _product($product_id=false, $type='array') {//Список изделий для заявок
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
}//end of _colorName()


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


// ---===! client !===--- Секция клиентов

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
function clientFilter($v) {
    if(!preg_match(REGEXP_WORDFIND, win1251($v['fast'])))
        $v['fast'] = '';
    if(!preg_match(REGEXP_BOOL, $v['dolg']))
        $v['dolg'] = 0;
    if(!preg_match(REGEXP_BOOL, $v['active']))
        $v['active'] = 0;
    $filter = array(
        'fast' => win1251(htmlspecialchars(trim($v['fast']))),
        'dolg' => intval($v['dolg']),
        'active' => intval($v['active'])
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
        /*if(isset($filter['active']) && $filter['active'] == 1) {
            $sql = "SELECT DISTINCT `client_id`
				FROM `zayavki`
				WHERE `zayav_status`=1";
            $q = query($sql);
            $ids = array();
            while($r = mysql_fetch_assoc($q))
                $ids[] = $r['client_id'];
            $cond .= " AND `id` IN (".(empty($ids) ? 0 : implode(',', $ids)).")";
        }*/
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

    /*   $sql = "SELECT
                   `client_id` AS `id`,
                   COUNT(`id`) AS `count`
               FROM `zayavki`
               WHERE `ws_id`=".WS_ID."
                 AND `zayav_status`>0
                 AND `client_id` IN (".implode(',', array_keys($spisok)).")
               GROUP BY `client_id`";
       $q = query($sql);
       while($r = mysql_fetch_assoc($q))
           $spisok[$r['id']]['zayav_count'] = $r['count'];*/
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
}//end of client_data()
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
                        _checkbox('dolg', 'Должники').
                        _checkbox('active', 'С активными заявками').
                    '</div>'.
        '</table>'.
    '</div>';
}//end of client_list()
function client_count($count, $dolg=0) {
    if($dolg)
        $dolg = abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE `balans`<0 LIMIT 1"));
    return ($count > 0 ?
        'Найден'._end($count, ' ', 'о ').$count.' клиент'._end($count, '', 'а', 'ов').
        ($dolg ? '<em>(Общая сумма долга = '.$dolg.' руб.)</em>' : '')
        :
        'Клиентов не найдено');
}//end of client_count()

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
            $money .= '<tr><td class="sum"><b>'.$r['summa'].'</b>'.
                '<td>'.$about.
                '<td class="dtime" title="Внёс: '._viewerName($r['viewer_id_add']).'">'.FullDataTime($r['dtime_add']);
        }
        $money .= '</table>';
    }
   // $remindData = remind_data(1, array('client'=>$client_id));

    return
    '<script type="text/javascript">'.
        'CLIENT={'.
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
                        '<div class="dtime">Клиента внёс '._viewerName($client['viewer_id_add']).' '.FullData($client['dtime_add'], 1).'</div>'.
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
                    '<div class="rightLinks">'.
                        '<a class="sel">Информация</a>'.
                        '<a class="cedit">Редактировать</a>'.
                        '<a href="'.URL.'&p=zayav&d=add&back=client&id='.$client_id.'"><b>Новая заявка</b></a>'.
                        '<a class="remind_add">Новое задание</a>'.
                        '<a class="cdel">Удалить клиента</a>'.
                    '</div>'.
                    '<div id="zayav_filter">'.
                        '<div id="zayav_result">'.zayav_count($zayavData['all'], 0).'</div>'.
                        '<div class="findHead">Статус заявки</div><div id="zayav_status"></div>'.
                    '</div>'.
        '</table>'.
    '</div>';
}//end of client_info()



// ---===! zayav !===--- Секция заявок
function _zayavStatus($id=false) {
    $arr = array(
        '1' => array(
            'name' => 'Ожидает выполнения',
            'color' => 'E8E8FF'
        ),
        '2' => array(
            'name' => 'Выполнено!',
            'color' => 'CCFFCC'
        ),
        '3' => array(
            'name' => 'Отказ',
            'color' => 'FFDDDD'
        )
    );
    return $id ? $arr[$id] : $arr;
}//end of _zayavStatus()

function zayav_add($v=array()) {
    return
    '<script type="text/javascript">var product='._product(false, 'json').';</script>'.
    '<div id="zayavAdd">'.
        '<div class="headName">Внесение новой заявки</div>'.
        '<table style="border-spacing:8px">'.
            '<tr><td class="label">Клиент:         <td><INPUT TYPE="hidden" id="client_id" value="'.$v['client_id'].'" />'.
            '<tr><td class="label">Номер договора: <td><INPUT type="text" id="nomer_dog" maxlength="30" />'.
            '<tr><td class="label">Номер ВГ:       <td><INPUT type="text" id="nomer_vg" maxlength="30" />'.
            '<tr><td class="label">Изделие:        <td><INPUT type="hidden" id="product_id" value="0" />'.
                '<a href="'.URL.'&p=setup&d=product" class="img_edit product_edit" title="Настроить список изделий"></a>'.
            '<tr><td class="label">Адрес установки:<td><INPUT type="text" id="adres_set" maxlength="100" />'.
            '<tr><td class="label top">Заметка:    <td><textarea id="comm"></textarea>'.
        '</table>'.
        '<div class="vkButton"><button>Внести</button></div>'.
        '<div class="vkCancel" val="'.$v['back'].'"><button>Отмена</button></div>'.
    '</div>';
}//end of zayav_add()

function zayavFilter($v) {
    if(empty($v['status']) || !preg_match(REGEXP_NUMERIC, $v['status']))
        $v['status'] = 0;
    if(empty($v['client']) || !preg_match(REGEXP_NUMERIC, $v['client']))
        $v['client'] = 0;

    $filter = array();
    $filter['find'] = htmlspecialchars(trim(@$v['find']));
    $filter['status'] = intval($v['status']);
    if($v['client'] > 0)
        $filter['client'] = intval($v['client']);
    return $filter;
}//end of zayavFilter()
function zayav_data($page=1, $filter=array(), $limit=20) {
    $cond = "`status`>0";

    if(!empty($filter['find'])) {
        $cond .= " AND `find` LIKE '%".$filter['find']."%'";
        if($page ==1 && preg_match(REGEXP_NUMERIC, $filter['find']))
            $nomer = intval($filter['find']);
        $reg = '/('.$filter['find'].')/i';
    } else {
        if(isset($filter['status']) && $filter['status'] > 0)
            $cond .= " AND `zayav_status`=".$filter['status'];
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
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
    $q = query($sql);
    while($r = mysql_fetch_assoc($q)) {
        if(isset($nomer) && $nomer == $r['nomer'])
            continue;
        $zayav[$r['id']] = $r;
        $client[$r['client_id']] = $r['client_id'];
    }

    if(empty($filter['client']))
        $client = _clientLink($client);
    $status = _zayavStatus();

    foreach($zayav as $id => $r) {
        $unit = array(
            'status_color' => $status[$r['status']]['color'],
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
        ($filter_break_show ? '<a id="filter_break">Сбросить условия поиска</a>' : '').
        ($count > 0 ?
            'Показан'._end($count, 'а', 'о').' '.$count.' заяв'._end($count, 'ка', 'ки', 'ок')
            :
            'Заявок не найдено');
}//end of zayav_count()
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
//                _checkbox('desc', 'Обратный порядок', $values['desc']).
                '<div class="condLost'.(!empty($values['find']) ? ' hide' : '').'">'.
                    '<div class="findHead">Статус заявки</div><div id="status"></div>'.
                '</div>'.
        '</table>'.
    '</div>'.
    '<script type="text/javascript">'.
        'var zayav = {'.
            'find:"'.unescape($values['find']).'",'.
            'status:'.$values['status'].
        '};'.
    '</script>';
}//end of zayav_list()
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
}//end of zayav_spisok()

function zayav_info() {
    return '';
}//end of zayav_info()




function setup() {
    switch(@$_GET['d']) {
        default: $_GET['d'] = 'worker';
        case 'worker': $left = setup_worker(); break;
        case 'product': $left = setup_product(); break;
    }
    $right = '<div class="rightLinks">'.
        '<a href="'.URL.'&p=setup&d=worker"'.(@$_GET['d'] == 'worker' ? ' class="sel"' : '').'>Сотрудники</a>'.
        '<a href="'.URL.'&p=setup&d=product"'.(@$_GET['d'] == 'product' ? ' class="sel"' : '').'>Изделия</a>'.
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
        '<div class="headName">Управление сотрудниками</div>'.
    '</div>';
}//end of setup_worker()
function setup_product() {
    return
    '<div id="setup_product">'.
        '<div class="headName">Настройки видов изделий<a class="add">Добавить</a></div>'.
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
            '<tr><th class="name">Наименование'.
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
    return $send ? $send : 'Список пуст.';
}//end of setup_product_spisok()