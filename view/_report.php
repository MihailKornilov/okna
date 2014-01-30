<?php
function styleHead() {//Рамка для заголовка таблицы
	$style = new PHPExcel_Style();
	$style->applyFromArray(array(
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
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap' => true
		)
	));
	return $style;
}
function styleContent() {//Рамки для содержимого
	$style = new PHPExcel_Style();
	$style->applyFromArray(array(
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
	return $style;
}
function styleResult() {//Рамка для заголовка таблицы
	$style = new PHPExcel_Style();
	$style->applyFromArray(array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
				'color' => array('rgb' => '444444')
			)
		),
		'font' => array(
			'name' => 'Tahoma',
			'size' => 6
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		),
		'numberformat' => array('code' => '#,#')
	));
	return $style;
}
function reportData($data) {
	$ex = explode(' ', $data);
	$d = explode('-', $ex[0]);
	return $d[2].'.'.$d[1].'.';
}
function freeLine($line) {
	global $book;
	$sheet = $book->getActiveSheet();
	$sheet->getStyle('A'.($line + 2).':A'.($line + 2));
}
function pageSetup($title) {
	global $book;

	$sheet = $book->getActiveSheet();

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
	$sheet->setTitle($title);
}
function colWidth() {// Установка размеров колонок
	global $sheet;
	$sheet->getColumnDimension('A')->setWidth(8);   // дата
	$sheet->getColumnDimension('B')->setWidth(8);   // № дог.
	$sheet->getColumnDimension('C')->setWidth(6);   // ВГ
	$sheet->getColumnDimension('D')->setWidth(40);  // ФИО
	$sheet->getColumnDimension('E')->setWidth(11);  // сумма дог.
	$sheet->getColumnDimension('F')->setWidth(13);  // № счёта
	$sheet->getColumnDimension('G')->setWidth(10);  // сумма
	$sheet->getColumnDimension('H')->setWidth(11);  // взнос нал. предоплата
	$sheet->getColumnDimension('I')->setWidth(11);  // взнос нал. долг
	$sheet->getColumnDimension('J')->setWidth(25);  // изделия
	$sheet->getColumnDimension('K')->setWidth(9);   // зар.плата дев.
	$sheet->getColumnDimension('L')->setWidth(9);   // зар.плата мал.
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
	$sheet->mergeCells('A'.$line.':A'.($line + 1))  // дата
		->mergeCells('B'.$line.':B'.($line + 1))    // № дог.
		->mergeCells('C'.$line.':C'.($line + 1))    // ВГ
		->mergeCells('D'.$line.':D'.($line + 1))    // ФИО
		->mergeCells('E'.$line.':E'.($line + 1))    // сумма дог.
		->mergeCells('F'.$line.':F'.($line + 1))    // № счёта
		->mergeCells('G'.$line.':G'.($line + 1))    // сумма
		->mergeCells('H'.$line.':I'.$line)          // взнос нал.
		->mergeCells('J'.$line.':J'.($line + 1))    // изделия
		->mergeCells('K'.$line.':L'.$line);         // зар.плата

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
		->setCellValue('J'.$line, 'изделия')
		->setCellValue('K'.$line, 'зар.плата')
		->setCellValue('K'.($line + 1), 'дев.')
		->setCellValue('L'.($line + 1), 'мал.');

	$sheet->setSharedStyle(styleHead(), 'A'.$line.':'.$colLast.($line + 1));
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

	$start = $line;
	foreach($zayav as $r) {
		$nDog = isset($r['dogovor_n']) ? $r['dogovor_n'] : '';
		if(!$nDog && $r['nomer_g'])
			$nDog = 'Ж-'.$r['nomer_g'];
		if(!$nDog && $r['nomer_d'])
			$nDog = 'Д-'.$r['nomer_d'];

		$dolg = $r['accrual'] - $r['predoplata'];

		$sheet->setCellValueByColumnAndRow(0, $line, reportData($r['dtime_add']))
			->setCellValueByColumnAndRow(1, $line, $nDog)
			->setCellValueByColumnAndRow(2, $line, $r['nomer_vg'])
			->setCellValueByColumnAndRow(3, $line, utf8(htmlspecialchars_decode($r['client_fio'])))
			->setCellValueByColumnAndRow(4, $line, $r['accrual'])
			->setCellValueByColumnAndRow(5, $line, implode(', ', $r['invoice_nomer']))
			->setCellValueByColumnAndRow(6, $line, $r['invoice_sum'])
			->setCellValueByColumnAndRow(7, $line, $r['predoplata'])
			->setCellValueByColumnAndRow(8, $line, $dolg ? $dolg : '')

			->setCellValueByColumnAndRow(9, $line, utf8(zayav_product_spisok($r['product'], 'report')))
			->setCellValueByColumnAndRow(10, $line, $r['zp_women'])
			->setCellValueByColumnAndRow(11, $line, $r['zp_men']);

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
	$sheet->setSharedStyle(styleContent(), 'A'.$start.':'.$colLast.$line);
	$sheet->setSharedStyle(styleResult(), 'A'.$line.':'.$colLast.$line);
	$sheet->getStyle('A'.$start.':A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle('A'.$start.':A'.$line)->getFont()->getColor()->setRGB('000088');
	$sheet->getStyle('B'.$start.':B'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('C'.$start.':C'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('D'.$start.':D'.$line)->getFont()->getColor()->setRGB('000088');
	$sheet->getStyle('E'.$start.':E'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->getStyle('F'.$start.':F'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
	$sheet->getStyle('G'.$start.':G'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->getStyle('H'.$start.':H'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->getStyle('I'.$start.':I'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->getStyle('J'.$start.':J'.$line)->getAlignment()->setWrapText(true);
	$sheet->getStyle('K'.$start.':K'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->getStyle('L'.$start.':L'.$line)->getNumberFormat()->setFormatCode('#,#');

	$sheet->setCellValue('E'.$line, '=SUM(E'.$start.':E'.($line - 1).')');
	$sheet->setCellValue('G'.$line, '=SUM(G'.$start.':G'.($line - 1).')');
	$sheet->setCellValue('H'.$line, '=SUM(H'.$start.':H'.($line - 1).')');
	$sheet->setCellValue('I'.$line, '=SUM(I'.$start.':I'.($line - 1).')');
	$sheet->setCellValue('K'.$line, '=SUM(K'.$start.':K'.($line - 1).')');
	$sheet->setCellValue('L'.$line, '=SUM(L'.$start.':L'.($line - 1).')');

	//$sheet->getStyle('A'.$line.':'.$colLast.$line)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	freeLine($line);
}
function zarplata() {
	global $book, $zayav_ids;

	$book->setActiveSheetIndex(1);
	$sheet = $book->getActiveSheet();
	pageSetup('Зарплата');
	$line = 1;

	$sheet->getColumnDimension('A')->setWidth(40);
	$sheet->getColumnDimension('B')->setWidth(10);

	$sheet->setCellValue('A'.$line, 'Начисление зарплаты для сотрудников:');
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);
	$line += 2;

	$sheet->setCellValue('A'.$line, 'Сотрудник');
	$sheet->setCellValue('B'.$line, 'Сумма');
	$sheet->setSharedStyle(styleHead(), 'A'.$line.':B'.$line);
	$line++;

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
		$sheet->setCellValueByColumnAndRow(1, $line, $sum);
		$line++;
	}
	$sheet->setSharedStyle(styleContent(), 'A'.$start.':B'.$line);
	$sheet->setSharedStyle(styleResult(), 'A'.$line.':B'.$line);
	$sheet->getStyle('B'.$start.':B'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->setCellValue('B'.$line, '=SUM(B'.$start.':B'.($line - 1).')');
	$sheet->setCellValue('A'.$line, 'Итог:');
	freeLine($line);
}
function incomes() {
	global $book;

	$book->setActiveSheetIndex(2);
	$sheet = $book->getActiveSheet();
	pageSetup('Платежи');
	$line = 1;

	$sheet->getColumnDimension('A')->setWidth(8);
	$sheet->getColumnDimension('B')->setWidth(40);
	$sheet->getColumnDimension('C')->setWidth(10);
	$sheet->getColumnDimension('D')->setWidth(25);
	$sheet->getColumnDimension('E')->setWidth(70);

	$sheet->setCellValue('A'.$line, 'Платежи:');
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);
	$line += 2;

	$sheet->setCellValue('A'.$line, 'Дата');
	$sheet->setCellValue('B'.$line, 'Клиент');
	$sheet->setCellValue('C'.$line, 'Сумма');
	$sheet->setCellValue('D'.$line, 'Вид');
	$sheet->setCellValue('E'.$line, 'Описание');
	$sheet->setSharedStyle(styleHead(), 'A'.$line.':E'.$line);
	$line++;

	$sql = "SELECT *
	        FROM `money`
	        WHERE `deleted`=0
	          AND `sum`>0
			  AND `dtime_add` LIKE '".$_GET['mon']."%'
	        ORDER BY `id`";
	$q = query($sql);
	$money = array();
	while($r = mysql_fetch_assoc($q))
		$money[$r['id']] = $r;

	$money = _clientLink($money);
	$money = _dogNomer($money);

	$start = $line;
	foreach($money as $r) {
		$sheet->getCell('A'.$line)->setValue(reportData($r['dtime_add']));
		$sheet->getCell('B'.$line)->setValue(utf8(htmlspecialchars_decode($r['client_fio'])));
		$sheet->getCell('C'.$line)->setValue($r['sum']);
		$sheet->getCell('D'.$line)->setValue(utf8(_income($r['income_id'])));
		$sheet->getCell('E'.$line)->setValue(($r['dogovor_id'] ? 'Авансовый платеж (договор '.utf8($r['dogovor_nomer']).'). ' : '').utf8(htmlspecialchars_decode($r['prim'])).' ');
		$line++;
	}

	$sheet->setSharedStyle(styleContent(), 'A'.$start.':E'.$line);
	$sheet->setSharedStyle(styleResult(), 'A'.$line.':E'.$line);
	$sheet->getStyle('A'.$start.':A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->setCellValue('C'.$line, '=SUM(C'.$start.':C'.($line - 1).')');
	$sheet->getStyle('C'.$start.':C'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->getStyle('E'.$start.':E'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->setCellValue('B'.$line, 'Итог:');

	freeLine($line);
}
function debtors() {
	global $book;

	$book->setActiveSheetIndex(3);
	$sheet = $book->getActiveSheet();
	pageSetup('Должники');
	$line = 1;

	$sheet->getColumnDimension('A')->setWidth(5);
	$sheet->getColumnDimension('B')->setWidth(60);
	$sheet->getColumnDimension('C')->setWidth(10);

	$sheet->setCellValue('A'.$line, 'Должники:');
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);
	$line += 2;

	$sheet->setCellValue('B'.$line, 'ФИО');
	$sheet->setCellValue('C'.$line, 'Сумма');
	$sheet->setSharedStyle(styleHead(), 'A'.$line.':C'.$line);
	$line++;

	$sql = "SELECT * FROM `client` WHERE `deleted`=0 AND `balans`<0 ORDER BY `fio`";
	$q = query($sql);
	$start = $line;
	$n = 1;
	while($r = mysql_fetch_assoc($q)) {
		$fio = new PHPExcel_RichText();
		$fio->createText(utf8(htmlspecialchars_decode($r['fio'])));
		if($r['telefon']) {
			$tel = $fio->createTextRun(utf8(' ('.htmlspecialchars_decode($r['telefon']).')'));
			$tel->getFont()->setName('tahoma')
						   ->setSize(6)
						   ->getColor()->setRGB('777777');
		}
		$sheet->getCell('A'.$line)->setValue($n++);
		$sheet->getCell('B'.$line)->setValue($fio);
		$sheet->getCell('C'.$line)->setValue(abs($r['balans']));
		$line++;
	}
	$sheet->setSharedStyle(styleContent(), 'A'.$start.':C'.$line);
	$sheet->setSharedStyle(styleResult(), 'A'.$line.':C'.$line);
	$sheet->getStyle('C'.$start.':C'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->setCellValue('C'.$line, '=SUM(B'.$start.':C'.($line - 1).')');
	$sheet->setCellValue('B'.$line, 'Итог:');
	freeLine($line);
}

require_once '../config.php';

if(empty($_GET['mon']) || !preg_match(REGEXP_YEARMONTH, $_GET['mon']))
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
$book->createSheet();
$book->createSheet();
$book->createSheet();

$book->setActiveSheetIndex(0);
$sheet = $book->getActiveSheet();
$line = 1;      // Текущая линия
$colLast = 'L'; // Последняя колонка
$zayav_ids = 0; // Идентификаторы заявок текущего месяца

pageSetup('Заявки');
colWidth();
aboutShow();
headShow();
contentShow();

zarplata();
incomes();
debtors();


$book->setActiveSheetIndex(0);

header('Content-Type:application/vnd.ms-excel');
header('Content-Disposition:attachment;filename="report.xls"');
$writer = PHPExcel_IOFactory::createWriter($book, 'Excel5');
$writer->save('php://output');

mysql_close();
exit;
