<?php
function pageSetup() {
	global $book, $sheet;
	//Глобальные стили для ячеек
	$book->getDefaultStyle()->getFont()->setName('Arial')
									   ->setSize(6);

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
}
function colWidth() {// Установка размеров колонок
	global $sheet;
	$sheet->getColumnDimension('A')->setWidth(7);   // дата
	$sheet->getColumnDimension('B')->setWidth(7);   // № дог.
	$sheet->getColumnDimension('C')->setWidth(5);   // ВГ
	$sheet->getColumnDimension('D')->setWidth(25);  // ФИО
	$sheet->getColumnDimension('E')->setWidth(10);  // сумма дог.
	$sheet->getColumnDimension('F')->setWidth(11);  // № счёта
	$sheet->getColumnDimension('G')->setWidth(10);  // сумма
	$sheet->getColumnDimension('H')->setWidth(10);  // предоплата
	$sheet->getColumnDimension('I');
	$sheet->getColumnDimension('J');
	$sheet->getColumnDimension('K');
	$sheet->getColumnDimension('L')->setWidth(20);  // изделия
	$sheet->getColumnDimension('M')->setWidth(8);   // зар.плата дев.
	$sheet->getColumnDimension('N')->setWidth(8);   // зар.плата мал.
	$sheet->getColumnDimension('O')->setWidth(8);   // долг
}
function aboutShow() {
	global $sheet, $line, $colLast;
	$ex = explode('-', $_GET['mon']);
	$mon = '.'.$ex[1].'.'.$ex[0];
	$sheet->mergeCells('A'.$line.':'.$colLast.$line);
	$sheet->setCellValue('A'.$line, 'ОТЧЁТ за период с 01'.$mon.' по '.date('t', strtotime($_GET['mon'].'-01')).$mon.' г.');
	$sheet->getStyle('A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);
	$line++;

	$sheet->mergeCells('A'.$line.':'.$colLast.$line);
	$sheet->setCellValue('A'.$line, 'маг. "Евроокна"');
	$sheet->getStyle('A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);
	$line++;
}//aboutShow()
function headShow() {// Рисование заголовка
	global $sheet, $line, $colLast;
	//Объединение ячеек в заголовке
	$sheet->mergeCells('A'.$line.':A'.($line + 1))
		->mergeCells('B'.$line.':B'.($line + 1))
		->mergeCells('C'.$line.':C'.($line + 1))
		->mergeCells('D'.$line.':D'.($line + 1))
		->mergeCells('E'.$line.':E'.($line + 1))
		->mergeCells('F'.$line.':F'.($line + 1))
		->mergeCells('G'.$line.':G'.($line + 1))
		->mergeCells('H'.$line.':I'.$line)
		->mergeCells('J'.$line.':K'.$line)
		->mergeCells('L'.$line.':L'.($line + 1))
		->mergeCells('M'.$line.':N'.$line)
		->mergeCells('O'.$line.':O'.($line + 1));

	$sheet->setCellValue('A'.$line, 'дата')
		->setCellValue('B'.$line, '№ дог.')
		->setCellValue('C'.$line, 'ВГ')
		->setCellValue('D'.$line, 'ФИО')
		->setCellValue('E'.$line, 'сумма дог.')
		->setCellValue('F'.$line, '№ счёта')
		->setCellValue('G'.$line, 'сумма')
		->setCellValue('H'.$line, 'взнос нал.')
		->setCellValue('H'.($line + 1), 'предо плата')
		->setCellValue('I'.($line + 1), 'долг')
		->setCellValue('J'.$line, 'долги')
		->setCellValue('J'.($line + 1), 'сумма')
		->setCellValue('K'.($line + 1), 'дата гашения')
		->setCellValue('L'.$line, 'изделия')
		->setCellValue('M'.$line, 'зар.плата')
		->setCellValue('M'.($line + 1), 'дев.')
		->setCellValue('N'.($line + 1), 'мал.')
		->setCellValue('O'.$line, 'долг');

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

	$sheet->setSharedStyle($styleHead, 'A'.$line.':'.$colLast.($line + 1));
	$line += 2;
}
function contentShow() {
	global $sheet, $line, $colLast, $zayav_ids;
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
		),
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID
		)
	));
	$sheet->setSharedStyle($styleContent, 'A'.$line.':'.$colLast.($line + count($zayav)));


	$start = $line;
	foreach($zayav as $r) {
		$ex = explode(' ', $r['dtime_add']);
		$d = explode('-', $ex[0]);
		$nDog = isset($r['dogovor_n']) ? $r['dogovor_n'] : '';
		if(!$nDog && $r['nomer_g'])
			$nDog = 'Ж-'.$r['nomer_g'];
		if(!$nDog && $r['nomer_d'])
			$nDog = 'Д-'.$r['nomer_d'];

		$sheet->setCellValueByColumnAndRow(0, $line, $d[2].'.'.$d[1].'.')
			->setCellValueByColumnAndRow(1, $line, $nDog)
			->setCellValueByColumnAndRow(2, $line, $r['nomer_vg'])
			->setCellValueByColumnAndRow(3, $line, utf8(htmlspecialchars_decode($r['client_fio'])))
			->setCellValueByColumnAndRow(4, $line, $r['accrual'])
			->setCellValueByColumnAndRow(5, $line, implode(', ', $r['invoice_nomer']))
			->setCellValueByColumnAndRow(6, $line, $r['invoice_sum'])
			->setCellValueByColumnAndRow(7, $line, $r['predoplata'])
			->setCellValueByColumnAndRow(8, $line, ($r['accrual'] - $r['predoplata']))

			->setCellValueByColumnAndRow(11, $line, utf8(zayav_product_spisok($r['product'], 'report')))
			->setCellValueByColumnAndRow(12, $line, $r['zp_women'])
			->setCellValueByColumnAndRow(13, $line, $r['zp_men']);

		if(!$r['dogovor_id'] && $r['dogovor_require'])
			$bg = 'FFFFFF';
		elseif($r['zakaz_status'])
			$bg = _zayavStatusColor($r['zakaz_status']);
		elseif($r['zamer_status'] == 1 || $r['zamer_status'] == 3)
			$bg = _zayavStatusColor($r['zamer_status']);
		elseif($r['set_status'])
			$bg = _zayavStatusColor($r['set_status']);
		else $bg = 'FFFFFF';

		$sheet->getStyle('A'.$line.':A'.$line)->getFill()->getStartColor()->setRGB($bg);
		$sheet->getCellByColumnAndRow(0, $line)->getHyperlink()->setUrl((LOCAL ? URL.'&p=zayav&d=info&&id=' : API_URL.'#zayav_').$r['id']);     //Вставка ссылки для даты на заявку
		$sheet->getCellByColumnAndRow(3, $line)->getHyperlink()->setUrl((LOCAL ? URL.'&p=client&d=info&&id=' : API_URL.'#client_').$r['client_id']);//Вставка ссылки для клиента

		$line++;
	}

	//Стили для колонок содержимого
	$sheet->getStyle('A'.$start.':A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle('A'.$start.':A'.$line)->getFont()->getColor()->setRGB('000088');
	$sheet->getStyle('B'.$start.':B'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('C'.$start.':C'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('D'.$start.':D'.$line)->getFont()->getColor()->setRGB('000088');
	$sheet->getStyle('E'.$start.':E'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->getStyle('F'.$start.':F'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
	$sheet->getStyle('G'.$start.':G'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->getStyle('H'.$start.':H'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->getStyle('M'.$start.':M'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->getStyle('N'.$start.':N'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->getStyle('O'.$start.':O'.$line)->getNumberFormat()->setFormatCode('#,#');

	$sheet->setCellValue('E'.$line, '=SUM(E'.$start.':E'.($line - 1).')');
	$sheet->setCellValue('G'.$line, '=SUM(G'.$start.':G'.($line - 1).')');
	$sheet->setCellValue('H'.$line, '=SUM(H'.$start.':H'.($line - 1).')');
	$sheet->setCellValue('I'.$line, '=SUM(I'.$start.':I'.($line - 1).')');
	$sheet->setCellValue('M'.$line, '=SUM(M'.$start.':M'.($line - 1).')');
	$sheet->setCellValue('N'.$line, '=SUM(N'.$start.':N'.($line - 1).')');

	$line += 2;
}


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

$line = 1;      // Текущая линия
$colLast = 'O'; // Последняя колонка
$zayav_ids = 0; // Идентификаторы заявок текущего месяца

pageSetup();
colWidth();
aboutShow();
headShow();
contentShow();


//Список зп сотрудников. Берётся из расходов по заявке.
$sql = "SELECT * FROM `zayav_rashod` WHERE `zayav_id` IN (".$zayav_ids.") AND `category_id`=2";
$q = query($sql);
$zp = array();
while($r = mysql_fetch_assoc($q))
	if($r['worker_id']) {
		if(empty($zp[$r['worker_id']]))
			$zp[$r['worker_id']] = 0;
		$zp[$r['worker_id']] += $r['sum'];
	}

$start = $line;
foreach($zp as $id => $sum) {
	$sheet->setCellValueByColumnAndRow(0, $line, utf8(_viewer($id, 'name')));
	$sheet->setCellValueByColumnAndRow(3, $line, $sum);
	$line++;
}


header('Content-Type:application/vnd.ms-excel');
header('Content-Disposition:attachment;filename="report.xls"');
$writer = PHPExcel_IOFactory::createWriter($book, 'Excel5');
$writer->save('php://output');

mysql_close();
exit;
