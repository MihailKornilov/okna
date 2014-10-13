<?php
require_once('../config.php');

$nopin = array(
	'pin_enter' => 1,
	'cache_clear' => 1,
	'cookie_clear' => 1
);
if(empty($nopin[$_POST['op']])) {
	if(PIN && (PIN_TIME - time() < 0))
		jsonError($_POST + array('pin'=>1));
	$_SESSION[PIN_TIME_KEY] = time() + PIN_TIME_LEN;
}

