<?php
if(!empty($_SERVER["SERVER_NAME"]))
	exit;

define('CRON', true);
require_once dirname(dirname(__FILE__)).'/config.php';

set_time_limit(1800);

define('PATH_DUMP', PATH.'_sxdump/backup/');

$filename = '';

foreach(scandir(PATH_DUMP) as $r)
	if(preg_match('/sql.gz$/', $r))
		$filename = $r;

if(!$filename)
	die('No files exists.');

$file = fopen(PATH_DUMP.$filename, 'r');
$text = fread($file, filesize(PATH_DUMP.$filename));
fclose($file);

$from = 'cron@okna.nyandoma.ru';
$subject = 'Evrookna base archive'; //Тема
$message = 'Created '.curTime(); //Текст письма
$boundary = '---'; //Разделитель

$headers = "From: $from\nReply-To: $from\n".
		   'Content-Type: multipart/mixed; boundary="'.$boundary.'"';
$body =
	"--$boundary\n".
	"Content-type: text/html; charset='windows-1251'\n".
	"Content-Transfer-Encoding: quoted-printablenn".
	"Content-Disposition: attachment;filename==?windows-1251?B?".base64_encode($filename)."?=\n\n".
	$message."\n".
	"--$boundary\n".
	"Content-Type: application/octet-stream;name==?windows-1251?B?".base64_encode($filename)."?=\n".
	"Content-Transfer-Encoding: base64\n".
	"Content-Disposition: attachment;filename==?windows-1251?B?".base64_encode($filename)."?=\n\n".
	chunk_split(base64_encode($text))."\n".
	'--'.$boundary ."--\n";
mail(CRON_MAIL, $subject, $body, $headers);