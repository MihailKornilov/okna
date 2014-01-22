<?php
require_once('../config.php');

if(PIN && PIN_TIME + 10800 < time() && $_POST['op'] != 'pin_enter')
	jsonError();
if($_POST['op'] != 'pin_enter')
	xcache_set(PIN_TIME_KEY, time(), 10800);

function jsonError($values=null) {
	$send['error'] = 1;
	if(empty($values))
		$send['text'] = utf8('Произошла неизвестная ошибка.<br />Попробуйте позднее.');
	elseif(is_array($values))
		$send += $values;
	else
		$send['text'] = utf8($values);
	die(json_encode($send));
}//end of jsonError()
function jsonSuccess($send=array()) {
	$send['success'] = 1;
	die(json_encode($send));
}//end of jsonSuccess()