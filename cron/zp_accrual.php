<?php
if(!empty($_SERVER["SERVER_NAME"]))
	exit;

define('CRON', true);
require_once dirname(dirname(__FILE__)).'/config.php';

set_time_limit(1800);
ob_start();
function toMailSend() {
	mail(CRON_MAIL, 'Cron Evrookna: zp_accrual.php', ob_get_contents());
}
function countCronTime() {
	echo "\n\n----\nExecution time: ".round(microtime(true) - TIME, 3);
}
register_shutdown_function('countCronTime');
register_shutdown_function('toMailSend');

define('YEAR', strftime('%Y'));
define('MON', _monthDef(strftime('%m')));
define('DAY', intval(strftime('%d')));

$sql = "SELECT * FROM `vk_user` WHERE `worker`=1 AND `rate`>0 AND `rate_day`";
$q = query($sql);
$about = 'Ставка за '.MON.' '.YEAR;
while($r = mysql_fetch_assoc($q)) {
	if($r['rate_day'] != DAY)
		continue;
	$sql = "INSERT INTO `zayav_expense` (
				`worker_id`,
				`sum`,
				`txt`
			) VALUES (
				".$r['viewer_id'].",
				".$r['rate'].",
				'".$about."'
			)";
	query($sql);
	history_insert(array(
		'type' => 46,
		'value' => round($r['rate'], 2),
		'value1' => $r['viewer_id'],
		'value2' => $about
	));
	echo 'Выполнен запрос: '.$about.' '._viewer($r['viewer_id'], 'name')."\n";
}
