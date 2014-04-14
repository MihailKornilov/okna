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
function freeLine($line) {
	global $book;
	$sheet = $book->getActiveSheet();
	$sheet->getStyle('A'.($line + 2).':A'.($line + 2));
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

	$sheet->getColumnDimension('A')->setWidth(12);
	$sheet->getColumnDimension('B')->setWidth(22);
	$sheet->getColumnDimension('C')->setWidth(20);
	$sheet->getColumnDimension('D')->setWidth(9);
	$sheet->getColumnDimension('E')->setWidth(8);
	$sheet->getColumnDimension('F')->setWidth(21);

	$sql = "SELECT * FROM `vk_user` WHERE `viewer_id` IN (".IDS.")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$sheet->setCellValue('A'.$line, utf8(_viewer($r['viewer_id'], 'name')));
		$sheet->setCellValue('F'.$line, MONTH);
		$sheet->getStyle('F'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyle('A'.$line.':F'.$line)->getFont()->setBold(true);
		$line++;

		$sheet->setCellValue('A'.$line, '№ дог.');
		$sheet->setCellValue('B'.$line, 'Адрес');
		$sheet->setCellValue('C'.$line, 'Изделие');
		$sheet->setCellValue('D'.$line, 'Дата уст.');
		$sheet->setCellValue('E'.$line, 'Сумма');
		$sheet->setCellValue('F'.$line, 'Примечание');
		$sheet->setSharedStyle(styleHead(), 'A'.$line.':F'.$line);
		$line++;

		$sql = "(SELECT
				'zp' AS `type`,
				`id`,
				`sum`,
				`prim` AS `about`,
				0 AS `zayav_id`,
				`mon`,
				'' `adres`,
				0 `dogovor_id`,
				'' AS `status_day`
			FROM `money`
			WHERE !`deleted`
			  AND `worker_id`=".$r['viewer_id']."
			  AND `sum`<0
			  AND `mon` LIKE '".MON."%'
		) UNION (
			SELECT
				'acc' AS `type`,
				`e`.`id`,
			    `e`.`sum`,
				'' AS `about`,
				`e`.`zayav_id`,
				`e`.`mon`,
				`z`.`adres`,
				`z`.`dogovor_id`,
				`z`.`status_day`
			FROM `zayav_expense` `e`,
				 `zayav` `z`
			WHERE `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			  AND `e`.`acc`
			  AND `e`.`mon` LIKE '".MON."%'
			  AND `e`.`worker_id`=".$r['viewer_id']."
			  AND `e`.`sum`>0
			GROUP BY `e`.`id`
		) UNION (
			SELECT
				'acc' AS `type`,
				`id`,
			    `sum`,
				`txt` AS `about`,
				0 AS `zayav_id`,
				`mon`,
				'' `adres`,
				0 `dogovor_id`,
				'' AS `status_day`
			FROM `zayav_expense`
			WHERE !`zayav_id`
			  AND `worker_id`=".$r['viewer_id']."
			  AND `sum`>0
			  AND `mon` LIKE '".MON."%'
		) UNION (
			SELECT
				'vch' AS `type`,
				`id`,
			    `sum`,
				`txt` AS `about`,
				0 AS `zayav_id`,
				`mon`,
				'' `adres`,
				0 `dogovor_id`,
				'' AS `status_day`
			FROM `zayav_expense`
			WHERE `worker_id`=".$r['viewer_id']."
			  AND `sum`<0
			  AND `mon` LIKE '".MON."%'
		)
		ORDER BY `mon`";
		$spq = query($sql);
		if(!mysql_num_rows($spq)) {
			$line++;
			continue;
		}
		$spisok = array();
		$zayav = array();
		$zp = 0;
		$vch = 0;
		while($sp = mysql_fetch_assoc($spq)) {
			$key = $sp['mon'];
			$key = strtotime($key);
			while(isset($spisok[$key]))
				$key++;
			$spisok[$key] = $sp;
			if($sp['type'] == 'zp')
				$zp += abs($sp['sum']);
			if($sp['type'] == 'vch')
				$vch += abs($sp['sum']);
			if($sp['zayav_id'])
				$zayav[$sp['zayav_id']] = array();
		}
		$spisok = _zayavLink($spisok);
		$spisok = _dogNomer($spisok);
		$zayav = zayav_product_array($zayav);

		$start = $line;
		$acc = 0;
		foreach($spisok as $sp) {
			if($sp['type'] == 'acc') {
				$sheet->setCellValue('A'.$line, utf8($sp['zayav_id'] ? ($sp['dogovor_id'] ? $sp['dogovor_n'].' ' : '').$sp['zayav_vg'] : ''));
				$sheet->setCellValue('B'.$line, $sp['adres'] ? utf8(htmlspecialchars_decode($sp['adres'])) : '');
				$sheet->setCellValue('C'.$line, $sp['zayav_id'] ? utf8(zayav_product_spisok($zayav[$sp['zayav_id']]['product'], 'report')) : '');
				$sheet->setCellValue('D'.$line, $sp['zayav_id'] && $sp['status_day'] != '0000-00-00' ? reportData($sp['mon']) : '');
				$sheet->setCellValue('E'.$line, $sp['sum']);
				$sheet->setCellValue('F'.$line, utf8($sp['about']));
				$acc += $sp['sum'];
				$line++;
			}
		}

		$add = 2;
		if($vch) $add++;
		if($zp) $add++;
		$sheet->setSharedStyle(styleContent(), 'A'.$start.':F'.($line + $add));
		$sheet->getStyle('B'.$start.':B'.($line + $add))->getAlignment()->setWrapText(true);
		$sheet->getStyle('C'.$start.':C'.($line + $add))->getAlignment()->setWrapText(true);
		$sheet->getStyle('F'.$start.':F'.($line + $add))->getAlignment()->setWrapText(true);

		$line++;
		$sheet->setCellValue('D'.$line, 'Начислено:');
		$sheet->getStyle('D'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->setCellValue('E'.$line, $acc);

		if($vch) {
			$line++;
			$sheet->setCellValue('D'.$line, 'Вычеты:');
			$sheet->getStyle('D'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$sheet->setCellValue('E'.$line, $vch);
		}

		$line++;
		$sheet->setCellValue('D'.$line, 'К выдаче:');
		$sheet->getStyle('D'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->setCellValue('E'.$line, $acc - $vch);

		if($zp) {
			$line++;
			$sheet->setCellValue('D'.$line, 'Выдано:');
			$sheet->getStyle('D'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$sheet->setCellValue('E'.$line, $zp);
		}

		$line += 2;
	}
	freeLine($line);
}

require_once '../config.php';
require_once VKPATH.'excel/PHPExcel.php';
set_time_limit(10);

if(empty($_GET['ids']))
	die(win1251('Не выбраны сотрудники.'));

define('IDS', $_GET['ids']);
foreach(explode(',', IDS) as $id)
	if(!preg_match(REGEXP_NUMERIC, $id))
		die(win1251('Некорректный список ID сотрудников.'));

if(empty($_GET['mon']) || !preg_match(REGEXP_NUMERIC, $_GET['mon']))
	die(win1251('Некорректный номер месяца.'));

if(empty($_GET['year']) || !preg_match(REGEXP_NUMERIC, $_GET['year']))
	die(win1251('Некорректный номер года.'));

define('MON', $_GET['year'].'-'.($_GET['mon'] < 10 ? 0 : '').$_GET['mon']);
define('MONTH', utf8(_monthDef($_GET['mon']).' '.$_GET['year']));


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
