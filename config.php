<?php
define('DOCUMENT_ROOT', dirname(__FILE__));
define('NAMES', 'cp1251');
define('DOMAIN', defined('CRON') ? 'okna.nyandoma.ru' : $_SERVER['SERVER_NAME']);
define('LOCAL', DOMAIN == 'okna');

//$SA[166424274] = 1; // Тестовая запись

require_once(DOCUMENT_ROOT.'/syncro.php');
require_once(VKPATH.'/vk.php');
_appAuth();
require_once(DOCUMENT_ROOT.'/view/main.php');

define('API_URL', 'http://vk.com/app'.API_ID);
define('TODAY_UNIXTIME', strtotime(strftime('%Y-%m-%d')));
define('PATH_DOGOVOR', PATH.'files/dogovor/');
define('LINK_DOGOVOR', SITE.'/files/dogovor/');


_dbConnect();
_getSetupGlobal();
_getVkUser();

function _getSetupGlobal() {//Получение глобальных данных
	if(CRON)
		return;
	$key = CACHE_PREFIX.'setup_global';
	$g = xcache_get($key);
	if(empty($g)) {
		$sql = "SELECT * FROM `setup_global` LIMIT 1";
		$g = mysql_fetch_assoc(query($sql));
		xcache_set($key, $g, 86400);
	}
	define('VERSION', $g['version']);
	define('G_VALUES_VERSION', $g['g_values']);
}//end of _getSetupGlobal()
function _getVkUser() {//Получение данных о пользователе
	if(CRON)
		return;
	$u = _viewer();
	define('VIEWER_NAME', $u['name']);
	define('VIEWER_ADMIN', $u['admin']);
	define('AUTH', isset($u['worker']));
	if(AUTH) {
		define('PIN', !empty($u['pin']));
		define('PIN_TIME_KEY', CACHE_PREFIX.'pin_time'.VIEWER_ID);
		define('PIN_TIME', intval(xcache_get(PIN_TIME_KEY)));
		define('PIN_ENTER', PIN && APP_START || PIN && PIN_TIME + 10800 < time());
		foreach(_viewerRules() as $key => $value)
			define($key, $value);
	}
}//end of _getVkUser()
