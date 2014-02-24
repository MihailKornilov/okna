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
	$line = 1;

	$sheet->getColumnDimension('A')->setWidth(70);
	$sheet->getColumnDimension('B')->setWidth(12);

	$sheet->setCellValue('A'.$line, 'Выдача зп - '.utf8(_viewer(WORKER_ID, 'name')).':');
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);
	$line++;
	$line++;

	$sql = "SELECT
				`e`.*,
				`z`.`dtime_add` `z_add`,
				`z`.`adres` `adres`,
				`z`.`status_day`
			FROM `zayav_expense` `e`
				LEFT JOIN `zayav` `z`
				ON `z`.`id`=`e`.`zayav_id`
			WHERE `e`.`id` IN (".IDS.")";
	$q = query($sql);
	$zp = array();
	$sum = 0;
	while($r = mysql_fetch_assoc($q)) {
		$sum += $r['sum'];
		$key = $r['dtime_add'];
		if($r['zayav_id'])
			if(BONUS) {
				$key = $r['z_add'];
				$r['z_add'] = substr($r['z_add'], 0, 10);
			} else {
				$key = $r['status_day'];
				if($key == '0000-00-00')
					continue;
			}
		$key = strtotime($key);
		while(isset($zp[$key]))
			$key--;
		$zp[$key] = $r;
	}

	$zp = _zayavLink($zp);

//	print_r($zp); exit;

	ksort($zp);
	$start = $line;
	foreach($zp as $r) {
		$about = '';
		if($r['zayav_id']) {
			$d = explode('-', BONUS ? $r['z_add'] : $r['status_day']);
			$about = utf8($r['zayav_head']).', '.
					(BONUS ? 'внесено' : 'выполнено').' '.$d[2].'.'.$d[1].'.'.$d[0].
					($r['adres'] ? ': '.utf8($r['adres']) : '');
		} elseif($r['sum'] < 0)
			$about = 'Вычет';
		if($r['txt'])
			$about .= ($about ? ': ' : '').utf8($r['txt']);
		$sheet->setCellValueByColumnAndRow(0, $line, $about);
		$sheet->setCellValueByColumnAndRow(1, $line, $r['sum']);
		$line++;
	}
	$sheet->setSharedStyle(styleContent(), 'A'.$start.':B'.$line);
	$sheet->setCellValue('A'.$line, 'Итог:');
	$sheet->getStyle('A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->setCellValue('B'.$line, $sum);
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
define('BONUS', _viewerRules(WORKER_ID, 'RULES_BONUS'));


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
