<?php
define('DOCUMENT_ROOT', dirname(__FILE__));
define('NAMES', 'cp1251');

//$SA[166424274] = 1; // “естова€ запись

require_once(DOCUMENT_ROOT.'/syncro.php');
require_once(API_PATH.'/vk.php');
_appAuth();
require_once(DOCUMENT_ROOT.'/view/main.php');
require_once(DOCUMENT_ROOT.'/view/setup.php');
require_once(DOCUMENT_ROOT.'/view/report.php');

define('PATH_DOGOVOR', APP_PATH.'/files/dogovor/');
define('LINK_DOGOVOR', APP_HTML.'/files/dogovor/');

session_name('evrookna');
session_start();

_getSetupGlobal();
_getVkUser();

function _getSetupGlobal() {//ѕолучение глобальных данных
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
function _getVkUser() {//ѕолучение данных о пользователе
	if(CRON)
		return;
	$u = _viewer();
	define('VIEWER_NAME', $u['name']);
	define('VIEWER_ADMIN', $u['admin']);
	define('AUTH', isset($u['worker']));
	if(AUTH) {
		define('PIN', !empty($u['pin']));
		define('PIN_TIME_KEY', 'pin_time_'.VIEWER_ID);
		define('PIN_TIME_LEN', 3600); // длительность в секундах действи€ пинкода
		define('PIN_TIME', empty($_SESSION[PIN_TIME_KEY]) ? 0 : $_SESSION[PIN_TIME_KEY]);
		define('PIN_ENTER', PIN && APP_START || PIN && (PIN_TIME - time() < 0));
		foreach(_viewerRules() as $key => $value)
			define($key, $value);
	}
}//end of _getVkUser()
