<?php
define('TIME', microtime(true));
define('DEBUG', @$_COOKIE['debug'] == 1);
define('DOCUMENT_ROOT', dirname(__FILE__));
define('NAMES', 'cp1251');
define('DOMAIN', $_SERVER["SERVER_NAME"]);
define('LOCAL', DOMAIN == 'okna');
define('VIEWER_ID', $_GET['viewer_id']);
define('VALUES', 'viewer_id='.VIEWER_ID.
    '&api_id='.@$_GET['api_id'].
    '&auth_key='.@$_GET['auth_key'].
    '&sid='.@$_GET['sid']);
define('SITE', 'http://'.DOMAIN);
define('URL', SITE.'/index.php?'.VALUES);

$SA[982006] = 1; // Корнилов Михаил
define('SA', isset($SA[VIEWER_ID]));
if(SA) { ini_set('display_errors',1); error_reporting(E_ALL); }

require_once(DOCUMENT_ROOT.'/syncro.php');
require_once(VKPATH.'/vk.php');
require_once(DOCUMENT_ROOT.'/view/main.php');

define('REGEXP_NUMERIC', '/^[0-9]{1,20}$/i');
define('REGEXP_CENA', '/^[0-9]{1,6}(.[0-9]{1,2})?$/i');
define('REGEXP_BOOL', '/^[0-1]$/');
define('REGEXP_DATE', '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/');
define('REGEXP_YEAR', '/^[0-9]{4}$/');
define('REGEXP_WORD', '/^[a-z0-9]{1,20}$/i');
define('REGEXP_MYSQLTABLE', '/^[a-z0-9_]{1,20}$/i');
define('REGEXP_WORDFIND', '/^[a-zA-Zа-яА-Я0-9,.;]{1,}$/i');

//Включает работу куков в IE через фрейм
header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

_dbConnect();
_getSetupGlobal();
_getVkUser();

function _getSetupGlobal() {//Получение глобальных данных
    $key = CACHE_PREFIX.'setup_global';
    $g = xcache_get($key);
    if(empty($g)) {
        $sql = "SELECT * FROM `setup_global` LIMIT 1";
        $g = mysql_fetch_assoc(query($sql));
        xcache_set($key, $g, 86400);
    }
    define('VERSION', $g['version']);
}//end of _getSetupGlobal()
function _getVkUser() {//Получение данных о пользователе
    $u = _viewer();
    define('VIEWER_NAME', $u['name']);
    define('VIEWER_ADMIN', $u['admin']);
    define('AUTH', isset($u['worker']));
    if(AUTH)
        foreach(workerRulesArray($u['rules']) as $name => $val)
            define($name, $val);
}//end of _getVkUser()