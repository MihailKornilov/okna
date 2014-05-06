<?php
require_once('../config.php');

$nopin = array(
	'pin_enter' => 1,
	'cache_clear' => 1,
	'cookie_clear' => 1
);
if(empty($nopin[$_POST['op']])) {
	if(PIN && PIN_TIME + 10800 < time())
		jsonError($_POST + array('pin'=>1));
	xcache_set(PIN_TIME_KEY, time(), 10800);
}

function jsonError($values=null) {
	$send['error'] = 1;
	if(empty($values))
		$send['text'] = utf8('Произошла неизвестная ошибка.');
	elseif(is_array($values))
		$send += $values;
	else
		$send['text'] = utf8($values);
	die(json_encode($send));
}//jsonError()
function jsonSuccess($send=array()) {
	$send['success'] = 1;
	die(json_encode($send));
}//jsonSuccess()
