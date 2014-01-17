<?php
require_once '../config.php';

if(empty($_GET['mon']) || !preg_match(YEAR_MONTH, $_GET['mon']))
	die('Некорректный месяц');

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
$sheet->getColumnDimension('E')->setWidth(10);   // сумма дог.
$sheet->getColumnDimension('F')->setWidth(11);   // № счёта
$sheet->getColumnDimension('G')->setWidth(10);   // сумма
$sheet->getColumnDimension('H');
$sheet->getColumnDimension('I');
$sheet->getColumnDimension('J');
$sheet->getColumnDimension('K');
$sheet->getColumnDimension('L')->setWidth(20);  // изделия
$sheet->getColumnDimension('M')->setWidth(8);  // зар.плата дев.
$sheet->getColumnDimension('N')->setWidth(8);  // зар.плата мал.
$sheet->getColumnDimension('O')->setWidth(8);  // долг

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
	  ->mergeCells('L1:L2')
	  ->mergeCells('M1:N1')
	  ->mergeCells('O1:O2');

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
	  ->setCellValue('L1', 'изделия')
	  ->setCellValue('M1', 'зар.плата')
		->setCellValue('M2', 'дев.')
		->setCellValue('N2', 'мал.')
	  ->setCellValue('O1', 'долг');

$styleHead = new PHPExcel_Style();
$styleHead->applyFromArray(array(
	'borders' => array(
		'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
			'color' => array('rgb' => '444444')
		)
	),
	'font' => array(
		'name' => 'Tahoma',
		'size' => 5,
		'bold' => true
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		'wrap' => true
	)
));

$sheet->setSharedStyle($styleHead, 'A1:O2');

$sql = "SELECT *
        FROM `zayav`
        WHERE `deleted`=0
          AND `dtime_add` LIKE '".$_GET['mon']."-%'
        ORDER BY `id`";
$q = query($sql);
$zayav = array();
while($r = mysql_fetch_assoc($q)) {
	$r['accrual'] = '';
	$r['invoice_nomer'] = array();
	$r['invoice_sum'] = '';
	$r['predoplata'] = '';
	$r['zp_women'] = '';
	$r['zp_men'] = '';
	$zayav[$r['id']] = $r;
}

$zayav = _dogNomer($zayav);
$zayav = _clientLink($zayav);
$zayav = zayav_product_array($zayav);

$zayav_ids = implode(',', array_keys($zayav));

//Номер договора и сумма. Берутся из расходов по заявке.
$sql = "SELECT * FROM `zayav_rashod` WHERE `zayav_id` IN (".$zayav_ids.")";
$q = query($sql);
while($r = mysql_fetch_assoc($q))
	switch($r['category_id']) {
		case 1:
			$zayav[$r['zayav_id']]['invoice_nomer'][] = utf8($r['txt']);
			$zayav[$r['zayav_id']]['invoice_sum'] += $r['sum'];
			break;
		case 2:
			if($r['worker_id'])
				$zayav[$r['zayav_id']]['zp_'.(_viewer($r['worker_id'], 'sex') == 1 ? 'wo' : '').'men'] += $r['sum'];
			break;
	}

//Начисления (вставляются в сумму договора)
$sql = "SELECT `z`.`id`,
			   SUM(`acc`.`sum`) AS `sum`
		FROM `accrual` AS `acc`,
		 	 `zayav` AS `z`
		WHERE `acc`.`zayav_id`=`z`.`id`
		  AND `z`.`id` IN (".$zayav_ids.")
		  AND `acc`.`deleted`=0
		GROUP BY `z`.`id`";
$q = query($sql);
while($r = mysql_fetch_assoc($q))
	$zayav[$r['id']]['accrual'] = $r['sum'];

//Предоплата
$sql = "SELECT `z`.`id`,
			   SUM(`m`.`sum`) AS `sum`
		FROM `money` AS `m`,
		 	 `zayav` AS `z`
		WHERE `m`.`zayav_id`=`z`.`id`
		  AND `z`.`id` IN (".$zayav_ids.")
		  AND `m`.`deleted`=0
		  AND `m`.`sum`>0
		GROUP BY `z`.`id`";
$q = query($sql);
while($r = mysql_fetch_assoc($q))
	$zayav[$r['id']]['predoplata'] = $r['sum'];

$row = 2;
foreach($zayav as $r) {
	$row++;
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
		  ->setCellValueByColumnAndRow(4, $row, $r['accrual'])
		  ->setCellValueByColumnAndRow(5, $row, implode(', ', $r['invoice_nomer']))
		  ->setCellValueByColumnAndRow(6, $row, $r['invoice_sum'])
		  ->setCellValueByColumnAndRow(7, $row, $r['predoplata'])
		  ->setCellValueByColumnAndRow(8, $row, ($r['accrual'] - $r['predoplata']))

		  ->setCellValueByColumnAndRow(11, $row, utf8(zayav_product_spisok($r['product'], 'report')))
		  ->setCellValueByColumnAndRow(12, $row, $r['zp_women'])
		  ->setCellValueByColumnAndRow(13, $row, $r['zp_men']);
	$sheet->getCellByColumnAndRow(0, $row)->getHyperlink()->setUrl((LOCAL ? URL.'&p=zayav&d=info&&id=' : API_URL.'#zayav_').$r['id']);     //Вставка ссылки для даты на заявку
	$sheet->getCellByColumnAndRow(3, $row)->getHyperlink()->setUrl((LOCAL ? URL.'&p=client&d=info&&id=' : API_URL.'#client_').$r['client_id']);//Вставка ссылки для клиента
}

//Рисование рамки для содержимого
$styleContent = new PHPExcel_Style();
$styleContent->applyFromArray(array(
	'borders' => array(
		'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
			'color' => array('rgb' => '777777')
		)
	),
	'font' => array(
		'name' => 'Tahoma',
		'size' => 6
	),
	'alignment' => array(
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	)
));
$sheet->setSharedStyle($styleContent, 'A3:O'.$row);

//Стили для колонок содержимого
$sheet->getStyle('A3:A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('A3:A'.$row)->getFont()->getColor()->setRGB('000088');
$sheet->getStyle('B3:B'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('C3:C'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('D3:D'.$row)->getFont()->getColor()->setRGB('000088');
$sheet->getStyle('E3:E'.$row)->getNumberFormat()->setFormatCode('#,#');
$sheet->getStyle('F3:F'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
$sheet->getStyle('G3:G'.$row)->getNumberFormat()->setFormatCode('#,#');
$sheet->getStyle('H3:H'.$row)->getNumberFormat()->setFormatCode('#,#');
$sheet->getStyle('M3:M'.$row)->getNumberFormat()->setFormatCode('#,#');
$sheet->getStyle('N3:N'.$row)->getNumberFormat()->setFormatCode('#,#');
$sheet->getStyle('O3:O'.$row)->getNumberFormat()->setFormatCode('#,#');



header('Content-Type:application/vnd.ms-excel');
header('Content-Disposition:attachment;filename="report.xls"');
$writer = PHPExcel_IOFactory::createWriter($book, 'Excel5');
$writer->save('php://output');

mysql_close();
exit;
