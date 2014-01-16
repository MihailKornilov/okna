<?php
require_once '../config.php';
require_once VKPATH.'excel/PHPExcel.php';
set_time_limit(10);

/*
//Установка кодировки. Не заработало.
$locale = 'ru';
if(!$validLocale = PHPExcel_Settings::setLocale($locale))
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
*/

$book = new PHPExcel();
$book->setActiveSheetIndex(0);
$sheet = $book->getActiveSheet();

//Ориентация страницы и  размер листа
$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT)
	->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Поля документа
$sheet->getPageMargins()->setTop(0.2)
	->setRight(0.2)
	->setLeft(0.2)
	->setBottom(0.2);

//Масштаб страницы
$sheet->getSheetView()->setZoomScale(140);

//Название страницы
$sheet->setTitle('Отчёт');

//Настройки шрифта
$book->getDefaultStyle()->getFont()->setName('Arial')
								   ->setSize(6);

//Установка размеров колонок
$sheet->getColumnDimension('A')->setWidth(7);   // дата
$sheet->getColumnDimension('B')->setWidth(7);   // № дог.
$sheet->getColumnDimension('C')->setWidth(5);   // ВГ
$sheet->getColumnDimension('D')->setWidth(25);  // ФИО
$sheet->getColumnDimension('E')->setWidth(8);   // сумма дог.
$sheet->getColumnDimension('F')->setWidth(7);   // № счёта
$sheet->getColumnDimension('G')->setWidth(7);   // сумма
$sheet->getColumnDimension('H');
$sheet->getColumnDimension('I');
$sheet->getColumnDimension('J');
$sheet->getColumnDimension('K');
$sheet->getColumnDimension('L')->setWidth(20);  // изделия

//Объединение ячеек в заголовке
$sheet->mergeCells('A1:A2')
	->mergeCells('B1:B2')
	->mergeCells('C1:C2')
	->mergeCells('D1:D2')
	->mergeCells('E1:E2')
	->mergeCells('F1:F2')
	->mergeCells('G1:G2')
	->mergeCells('H1:I1')
	->mergeCells('J1:K1')
	->mergeCells('L1:L2');

$sheet->setCellValue('A1', 'дата')
	->setCellValue('B1', '№ дог.')
	->setCellValue('C1', 'ВГ')
	->setCellValue('D1', 'ФИО')
	->setCellValue('E1', 'сумма дог.')
	->setCellValue('F1', '№ счёта')
	->setCellValue('G1', 'сумма')
	->setCellValue('H1', 'взнос нал.')
	->setCellValue('H2', 'предо плата')
	->setCellValue('I2', 'долг')
	->setCellValue('J1', 'долги')
	->setCellValue('J2', 'сумма')
	->setCellValue('K2', 'дата гашения')
	->setCellValue('L1', 'изделия');

$styleHead = new PHPExcel_Style();
$styleHead->applyFromArray(array(
	'borders' => array(
		'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
	)
));

$sheet->setSharedStyle($styleHead, 'A1:L2');
$sheet->getStyle('A1:L2')->getFont()->setName('Tahoma')
	->setSize(5)
	->setBold(true);
$sheet->getStyle('A1:L2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
										 ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
										 ->setWrapText(true);


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

	$sheet->setCellValueByColumnAndRow(0, $row, $d[2].'.'.$d[1].'.')
		  ->setCellValueByColumnAndRow(1, $row, $nDog)
		  ->setCellValueByColumnAndRow(2, $row, $r['nomer_vg'])
		  ->setCellValueByColumnAndRow(3, $row, utf8(htmlspecialchars_decode($r['client_fio'])))
		  ->setCellValueByColumnAndRow(4, $row, $r['dogovor_id'] ? $r['dogovor_sum'] : '')

		  ->setCellValueByColumnAndRow(11, $row, utf8(zayav_product_spisok($r['product'], 'report')))
		  ->getCellByColumnAndRow(3, $row)->getHyperlink()->setUrl(API_URL.'#client_'.$r['client_id']);//Вставка ссылки для клиента

	$row++;
}

//Рисование рамки для содержимого
$styleContent = new PHPExcel_Style();
$styleContent->applyFromArray(array(
	'borders' => array(
		'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
	),
	'font' => array(
		'name' => 'Tahoma',
		'size' => 6
	)
));
$sheet->setSharedStyle($styleContent, 'A3:L'.$row);

//Выравнивание колонок содержимого
$sheet->getStyle('A3:A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('B3:B'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('C3:C'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('D3:D'.$row)->getFont()->getColor()->setRGB('000088');
$sheet->getStyle('E3:E'.$row)->getNumberFormat()->setFormatCode('#,#');



header('Content-Type:application/vnd.ms-excel');
header('Content-Disposition:attachment;filename="report.xls"');
$writer = PHPExcel_IOFactory::createWriter($book, 'Excel5');
$writer->save('php://output');

mysql_close();
exit;
