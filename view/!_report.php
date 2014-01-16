<?php
require_once '../config.php';
require_once VKPATH.'phpexcel/class.writeexcel_workbook.inc.php';
require_once VKPATH.'phpexcel/class.writeexcel_worksheet.inc.php';

set_time_limit(10);

$fname = tempnam('/tmp', 'stocks.xls');
$book = new writeexcel_workbook($fname);
$sheet = $book->addworksheet();

$sheet->set_column(0, 0, 5);    // дата
$sheet->set_column(1, 1, 5);    // № дог.
$sheet->set_column(2, 2, 4);    // ВГ
$sheet->set_column(3, 3, 20);   // ФИО
$sheet->set_column(4, 4, 8);    // сумма дог.
$sheet->set_column(5, 5, 7);    // № счёта
$sheet->set_column(6, 6, 7);    // сумма

$sheet->set_column(11, 11, 16); // изделия

$head = $book->addformat(array(
	'align' => 'center',
	'valign' => 'vcenter',
	'bold' => 1,
	'size' => 8,
	'text_wrap' => 1,
	'merge' => 1
));

$f_row = $book->addformat(array(
	'align' => 'center',
	'valign' => 'vcenter',
	'size' => 8,
));

$f_data = $book->addformat(array(
	'align' => 'right',
	'valign' => 'vcenter',
	'size' => 8,
));

$f_fio = $book->addformat(array(
	'valign' => 'vcenter',
	'size' => 8,
//	'underline' => 1,
	'color' => 18
));

$f_dogsum = $book->addformat(array(
	'align' => 'right',
	'valign' => 'vcenter',
	'size' => 8,
	'num_format' => '#,#'
));

$f_product = $book->addformat(array(
	'valign' => 'vcenter',
	'size' => 8
));


$sheet->write(0, 0, 'дата',       $head);
$sheet->write_blank(0, 1, $head);
//$sheet->write(0, 1, '№ дог.',     $head);
$sheet->write(0, 2, 'ВГ',         $head);
$sheet->write(0, 3, 'ФИО',        $head);
$sheet->write(0, 4, 'сумма дог.', $head);
$sheet->write(0, 5, '№ счёта',    $head);

$sheet->write(0, 6, 'взнос нал.', $head);
$sheet->write_blank(0, 7, $head);

$sheet->write(0, 11, 'изделия',   $head);

$sql = "SELECT * FROM `zayav` WHERE `deleted`=0 AND `dtime_add` LIKE '2014-01-%' ORDER BY `id`";
$q = query($sql);
$zayav = array();
while($r = mysql_fetch_assoc($q))
	$zayav[$r['id']] = $r;

$zayav = _dogNomer($zayav);
$zayav = _clientLink($zayav);
$zayav = zayav_product_array($zayav);

$row = 2;
foreach($zayav as $r) {
	$ex = explode(' ', $r['dtime_add']);
	$d = explode('-', $ex[0]);
	$nDog = isset($r['dogovor_n']) ? $r['dogovor_n'] : '';
	if(!$nDog && $r['nomer_g'])
		$nDog = 'Ж-'.$r['nomer_g'];
	if(!$nDog && $r['nomer_d'])
		$nDog = 'Д-'.$r['nomer_d'];
	$sheet->write($row, 0, $d[2].'.'.$d[1].'.', $f_data);   // дата
	$sheet->write($row, 1, $nDog, $f_row);                  // № дог.
	$sheet->write($row, 2, $r['nomer_vg'], $f_row);         // ВГ
	$sheet->write($row, 3, API_URL.'#client_'.$r['client_id'], htmlspecialchars_decode($r['client_fio']), $f_fio);  // ФИО
	$sheet->write($row, 4, $r['dogovor_id'] ? $r['dogovor_sum'] : '', $f_dogsum);         // сумма дог.

	$sheet->write($row, 11, zayav_product_spisok($r['product'], 'report'), $f_product);         // изделия
	$row++;
}

//Вывод списка цветов
//for($n = 0; $n < 100; $n++) $sheet->write($n, 7, 'номер '.$n, $book->addformat(array('color'=>$n,'bold'=>1)));

$book->close();

header('Content-Type: application/x-msexcel; name="example-stocks.xls"');
header('Content-Disposition: inline; filename="example-stocks.xls"');
$fh = fopen($fname, 'rb');
fpassthru($fh);
unlink($fname);
