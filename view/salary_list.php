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
			'size' => 9,
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
			'size' => 11
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
function stylePodpis() {//Рамки для содержимого
	$style = new PHPExcel_Style();
	$style->applyFromArray(array(
		'borders' => array(
			'bottom' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'font' => array(
			'name' => 'Tahoma',
			'size' => 11
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
function pageSetup($title) {
	global $book;

	$sheet = $book->getActiveSheet();

	//Глобальные стили для ячеек
	$book->getDefaultStyle()->getFont()->setName('Arial')
		->setSize(11);

	//Ориентация страницы и  размер листа
	$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT)
		->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

	//Поля документа
	$sheet->getPageMargins()->setTop(0.2)
		->setRight(0.2)
		->setLeft(0.2)
		->setBottom(0.2);

	//Масштаб страницы
	$sheet->getSheetView()->setZoomScale(100);

	//Название страницы
	$sheet->setTitle($title);
}
function zpPrint() {
	global $book;

	$sheet = $book->getActiveSheet();
	$line = 2;

	$sheet->getColumnDimension('A')->setWidth(12);
	$sheet->getColumnDimension('B')->setWidth(22);
	$sheet->getColumnDimension('C')->setWidth(20);
	$sheet->getColumnDimension('D')->setWidth(9);
	$sheet->getColumnDimension('E')->setWidth(8);
	$sheet->getColumnDimension('F')->setWidth(21);

	$abbr = _viewer(WORKER_ID, 'first_name');
	define('WORKER', utf8(_viewer(WORKER_ID, 'last_name').' '.$abbr[0].'.'));
	$sheet->setCellValue('A'.$line, WORKER);
	$sheet->setCellValue('F'.$line, strftime('Дата: %d.%m.%Yг.'));
	$sheet->getStyle('F'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle('A'.$line.':F'.$line)->getFont()->setBold(true);
	$line += 2;

	$sheet->setCellValue('A'.$line, '№ дог.');
	$sheet->setCellValue('B'.$line, 'Адрес');
	$sheet->setCellValue('C'.$line, 'Изделие');
	$sheet->setCellValue('D'.$line, 'Дата уст.');
	$sheet->setCellValue('E'.$line, 'Сумма');
	$sheet->setCellValue('F'.$line, 'Примечание');
	$sheet->setSharedStyle(styleHead(), 'A'.$line.':F'.$line);
	$line++;

	$sql = "SELECT
				`e`.*,
				`z`.`adres` `adres`,
				`z`.`status_day`,
				`z`.`dogovor_id`
			FROM `zayav_expense` `e`
				LEFT JOIN `zayav` `z`
				ON `z`.`id`=`e`.`zayav_id`
			WHERE `e`.`id` IN (".IDS.")";
	$q = query($sql);
	$zp = array();
	$zayav = array();
	$sum = 0;
	$deduct = 0;
	$deduct_about = array();
	while($r = mysql_fetch_assoc($q)) {
		if($r['sum'] < 0) {
			$deduct += abs($r['sum']);
			if($r['txt'])
				$deduct_about[] = $r['txt'];
			continue;
		}
		$sum += $r['sum'];
		$key = $r['dtime_add'];
		if($r['zayav_id']) {
			$zayav[$r['zayav_id']] = array();
			$key = $r['status_day'];
			if($key == '0000-00-00')
				$key = '2014-01-01';
		}
		$key = strtotime($key);
		while(isset($zp[$key]))
			$key--;
		$zp[$key] = $r;
	}

	$zp = _zayavLink($zp);
	$zp = _dogNomer($zp);
	$zayav = zayav_product_array($zayav);
//	print_r($zayav); exit;

	ksort($zp);
	$start = $line;
	foreach($zp as $r) {
		$sheet->setCellValue('A'.$line, utf8($r['zayav_id'] ? ($r['dogovor_id'] ? $r['dogovor_n'].' ' : '').$r['zayav_vg'] : ''));
		$sheet->setCellValue('B'.$line, $r['adres'] ? utf8(htmlspecialchars_decode($r['adres'])) : '');
		$sheet->setCellValue('C'.$line, $r['zayav_id'] ? utf8(zayav_product_spisok($zayav[$r['zayav_id']]['product'], 'report')) : '');
		$sheet->setCellValue('D'.$line, $r['zayav_id'] && $r['status_day'] != '0000-00-00' ? reportData($r['status_day']) : '');
		$sheet->setCellValue('E'.$line, $r['sum']);
		$sheet->setCellValue('F'.$line, utf8($r['txt']));
		$line++;
	}
	$line += 3;

	$sheet->setSharedStyle(styleContent(), 'A'.$start.':F'.($line + ($deduct ? 2 : 0)));

	//Выравнивание вправо дат
	$sheet->getStyle('D'.$start.':D'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

	if($deduct) {
		$sheet->mergeCells('A'.$line.':D'.$line);
		$sheet->setCellValue('A'.$line, 'Всего:');
		$sheet->getStyle('A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->setCellValue('E'.$line, $sum);
		$line++;

		$sheet->mergeCells('A'.$line.':D'.$line);
		$sheet->setCellValue('A'.$line, 'Вычеты:');
		$sheet->getStyle('A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->setCellValue('E'.$line, $deduct);
		$sheet->setCellValue('F'.$line, utf8(implode(', ', $deduct_about)));
		$line++;
	}

	$sheet->mergeCells('A'.$line.':D'.$line);
	$sheet->setCellValue('A'.$line, 'Итого к выдаче:');
	$sheet->getStyle('A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle('C'.$start.':C'.$line)->getAlignment()->setWrapText(true);
	$sheet->setCellValue('E'.$line, $sum - $deduct);
	$sheet->getStyle('F'.$start.':F'.$line)->getAlignment()->setWrapText(true);

	$line += 2;

	$sheet->setSharedStyle(stylePodpis(), 'A'.$line.':F'.($line + 2));
	$sheet->getRowDimension($line)->setRowHeight(21);
	$sheet->setCellValue('A'.$line, 'Утвердил:');
	$sheet->setCellValue('F'.$line, 'Губинский Р.Е.');
	$sheet->getStyle('F'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$line++;
	$sheet->getRowDimension($line)->setRowHeight(21);
	$sheet->setCellValue('A'.$line, 'Выдал:');
	$sheet->setCellValue('F'.$line, 'Богарева Н.А.');
	$sheet->getStyle('F'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$line++;
	$sheet->getRowDimension($line)->setRowHeight(21);
	$sheet->setCellValue('A'.$line, 'Получил:');
	$sheet->setCellValue('F'.$line, WORKER);
	$sheet->getStyle('F'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);



	$sheet->getStyle('B'.$line)->getFont()->setBold(true);

}

require_once '../config.php';
require_once VKPATH.'excel/PHPExcel.php';
set_time_limit(10);

if(empty($_GET['worker_id']) || !preg_match(REGEXP_NUMERIC, $_GET['worker_id']))
	die(win1251('Некорректный ID сотрудника.'));

define('WORKER_ID', intval($_GET['worker_id']));
if(!query_value("SELECT COUNT(*) FROM `vk_user` WHERE `viewer_id`=".WORKER_ID))
	die(win1251('Сотрудника не существует.'));

if(empty($_GET['ids']))
	die(win1251('Не выбраны начисления.'));

define('IDS', $_GET['ids']);
foreach(explode(',', IDS) as $id)
	if(!preg_match(REGEXP_NUMERIC, $id))
		die(win1251('Некорректный список ID.'));

if(!query_value("SELECT COUNT(*) FROM `zayav_expense` WHERE `worker_id`=".WORKER_ID." AND `id` IN (".IDS.")"))
	die(win1251('Нет данных для печати.'));


$book = new PHPExcel();
$book->setActiveSheetIndex(0);
$sheet = $book->getActiveSheet();

pageSetup('Лист зп');
zpPrint();

header('Content-Type:application/vnd.ms-excel');
header('Content-Disposition:attachment;filename="zp-list.xls"');
$writer = PHPExcel_IOFactory::createWriter($book, 'Excel5');
$writer->save('php://output');

mysql_close();
exit;
