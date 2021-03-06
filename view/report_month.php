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
function pageSetup($title, $zoom=140) {
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
	$sheet->getSheetView()->setZoomScale($zoom);

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
	$ex = explode('-', MON);
	$mon = '.'.$ex[1].'.'.$ex[0];
	$sheet->mergeCells('A'.$line.':'.$colLast.$line);
	$sheet->setCellValue('A'.$line, 'ОТЧЁТ за период с 01'.$mon.' по '.date('t', strtotime(MON.'-01')).$mon.' г.');
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
	global $sheet, $line, $colLast;
	$sql = "SELECT *
        FROM `zayav`
        WHERE !`deleted`
          AND `dtime_add` LIKE '".MON."-%'
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

	if(empty($zayav))
		return;

	$zayav = _dogNomer($zayav);
	$zayav = _clientLink($zayav);
	$zayav = zayav_product_array($zayav);
	$zayav_ids = implode(',', array_keys($zayav));

	//Номер договора и сумма. Берутся из расходов по заявке.
	$sql = "SELECT * FROM `zayav_expense` WHERE `zayav_id` IN (".$zayav_ids.")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$r['sum'] = _cena($r['sum']);
		switch ($r['category_id']) {
			case 1:
				$zayav[$r['zayav_id']]['invoice_nomer'][] = utf8($r['txt']);
				$zayav[$r['zayav_id']]['invoice_sum'] += $r['sum'];
				break;
			case 2:
				if($r['worker_id'] && substr($r['mon'], 0, 7) == MON)
					$zayav[$r['zayav_id']]['zp_'.(_viewer($r['worker_id'], 'sex') == 1 ? 'wo' : '').'men'] += $r['sum'];
				break;
		}
	}

	//Начисления (вставляются в сумму договора)
	$sql = "SELECT `z`.`id`,
			   SUM(`acc`.`sum`) AS `sum`
			FROM `accrual` AS `acc`,
			     `zayav` AS `z`
			WHERE `acc`.`zayav_id`=`z`.`id`
			  AND `z`.`id` IN (".$zayav_ids.")
			  AND !`acc`.`deleted`
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
		  AND !`m`.`deleted`
		  AND `m`.`sum`>0
		GROUP BY `z`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zayav[$r['id']]['predoplata'] = $r['sum'];

	$start = $line;
	$sheet->setSharedStyle(styleContent(), 'A'.$start.':'.$colLast.($start + count($zayav)));
	foreach($zayav as $r) {
		$nDog = isset($r['dogovor_n']) ? $r['dogovor_n'] : '';
		if(!$nDog && $r['nomer_g'])
			$nDog = 'Ж-'.$r['nomer_g'];
		if(!$nDog && $r['nomer_d'])
			$nDog = 'Д-'.$r['nomer_d'];
		if(!$nDog && $r['nomer_t'])
			$nDog = 'T-'.$r['nomer_t'];

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
		$sheet->getCellByColumnAndRow(0, $line)->getHyperlink()->setUrl((LOCAL ? URL.'&p=zayav&d=info&&id=' : APP_URL.'#zayav_').$r['id']);     //Вставка ссылки для даты на заявку
		$sheet->getCellByColumnAndRow(3, $line)->getHyperlink()->setUrl((LOCAL ? URL.'&p=client&d=info&&id=' : APP_URL.'#client_').$r['client_id']);//Вставка ссылки для клиента

		$line++;
	}

	//Стили для колонок содержимого
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
function zp($sex=2) {
	global $book, $index;

	$book->createSheet();
	$book->setActiveSheetIndex($index++);
	$sheet = $book->getActiveSheet();
	pageSetup('Зарплата '.($sex == 2 ? 'мал.' : 'дев.'), 110);
	$line = 1;

	$sheet->getColumnDimension('A')->setWidth(130);
	$sheet->getColumnDimension('B')->setWidth(12);

	$sheet->setCellValue('A'.$line, 'Начисление зарплаты для '.($sex == 2 ? 'установщиков' : 'менеджеров').' за '.utf8(MONTH).':');
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);
	$line += 2;

	//Список зп сотрудников. Берётся из расходов по заявке.
	$sql = "(
			SELECT
			    `e`.`sum`,
			    `e`.`zayav_id`,
			    `e`.`worker_id`,
			    `e`.`txt`
			FROM `zayav_expense` `e`,
				 `zayav` `z`
			WHERE !`z`.`deleted`
			  AND `e`.`zayav_id`=`z`.`id`
			  AND `e`.`mon` LIKE '".MON."%'
			  AND `e`.`acc`
			  AND `e`.`sum`>0
			GROUP BY `e`.`id`
		) UNION (
			SELECT
			    `sum`,
			    `zayav_id`,
			    `worker_id`,
			    `txt`
			FROM `zayav_expense`
			WHERE !`zayav_id`
			  AND `sum`>0
			  AND `mon` LIKE '".MON."%'
		)";
	$q = query($sql);
	$zp = array();
	$worker = array();
	$zayav = array();
	while($r = mysql_fetch_assoc($q))
		if(_viewer($r['worker_id'], 'sex') == $sex) {
			if(empty($zp[$r['worker_id']]))
				$zp[$r['worker_id']] = 0;
			$zp[$r['worker_id']] += $r['sum'];
			$worker[$r['worker_id']][] = $r;
			if($r['zayav_id'])
				$zayav[$r['zayav_id']] = $r['zayav_id'];
		}

	if(!empty($zayav)) {
		$sql = "SELECT * FROM `zayav` WHERE `id` IN (".implode(',', array_keys($zayav)).")";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q)) {
			$d = explode('-', $r['status_day']);
			$r['status_day'] = $d[0] != '0000' ? $d[2].'.'.$d[1].'.'.$d[0] : '';
			$zayav[$r['id']] = $r;
		}
		if(!empty($zayav))
			$zayav = zayav_product_array($zayav);
	}



	//Подробно по каждому сотруднику
	foreach($worker as $id => $arr) {
		$sheet->setCellValueByColumnAndRow(0, $line, utf8(_viewer($id, 'name')));
		$sheet->getStyle('A'.$line)->getFont()->setBold(true);
		$sheet->setSharedStyle(styleHead(), 'A'.$line.':B'.$line);
		$line++;
		$start = $line;
		foreach($arr as $r) {
			$z = $r['zayav_id'] ? $zayav[$r['zayav_id']] : false;
			$head = $z ? _zayavCategory($z, 'head') : $r['txt'];
			$adres = $z && $z['adres'] ? htmlspecialchars_decode($z['adres']).', ' : '';
			$exec = $z && $z['status_day'] ? ', выполнена '.$z['status_day'] : '';
			$product = $z ? zayav_product_spisok($z['product'], 'report') : '';
			$sheet->setCellValueByColumnAndRow(0, $line, utf8($head).$exec.($z ? ': ' : '').utf8($adres.$product));
			if($z)
				$sheet->getCellByColumnAndRow(0, $line)->getHyperlink()->setUrl((LOCAL ? URL.'&p=zayav&d=info&&id=' : APP_URL.'#zayav_').$z['id']);
			$sheet->setCellValueByColumnAndRow(1, $line, $r['sum']);
			$line++;
		}
		$sheet->setCellValueByColumnAndRow(0, $line, 'Сумма:');
		$sheet->setCellValueByColumnAndRow(1, $line, round($zp[$id], 2));
		$sheet->setSharedStyle(styleContent(), 'A'.$start.':B'.$line);
		$sheet->getStyle('A'.$start.':A'.($line - 1))->getFont()->getColor()->setRGB('000088');
		$sheet->getStyle('A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyle('B'.$line)->getFont()->setBold(true);
		$line += 2;
	}

	//Общий список
	$sheet->setCellValue('A'.$line, 'Сотрудник');
	$sheet->setCellValue('B'.$line, 'Сумма');
	$sheet->setSharedStyle(styleHead(), 'A'.$line.':B'.$line);
	$line++;
	$start = $line;
	$summa = 0;
	foreach($zp as $id => $sum) {
		$sheet->setCellValueByColumnAndRow(0, $line, utf8(_viewer($id, 'name')));
		$sheet->setCellValueByColumnAndRow(1, $line, $sum);
		$summa += $sum;
		$line++;
	}
	$sheet->setSharedStyle(styleContent(), 'A'.$start.':B'.$line);
	$sheet->setSharedStyle(styleResult(), 'A'.$line.':B'.$line);
	$sheet->getStyle('B'.$start.':B'.$line)->getNumberFormat()->setFormatCode('#,#');
	$sheet->setCellValue('B'.$line, $summa);
	$sheet->setCellValue('A'.$line, 'Итог:');

	$sheet->getStyle('A1:B'.$line)->getFont()->setSize(8);

	freeLine($line);
}
function incomes() {
	global $book, $index;

	$book->createSheet();
	$book->setActiveSheetIndex($index++);
	$sheet = $book->getActiveSheet();
	pageSetup('Платежи', 110);
	$line = 1;

	$sheet->getColumnDimension('A')->setWidth(11);
	$sheet->getColumnDimension('B')->setWidth(48);
	$sheet->getColumnDimension('C')->setWidth(23);
	$sheet->getColumnDimension('D')->setWidth(27);
	$sheet->getColumnDimension('E')->setWidth(57);

	$sheet->setCellValue('A'.$line, 'Платежи за '.utf8(MONTH).':');
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);
	$line += 2;

	$sheet->setCellValue('A'.$line, 'Дата');
	$sheet->setCellValue('B'.$line, 'Клиент');
	$sheet->setCellValue('C'.$line, 'Сумма');
	$sheet->setCellValue('D'.$line, 'Счёт');
	$sheet->setCellValue('E'.$line, 'Описание');
	$sheet->setSharedStyle(styleHead(), 'A'.$line.':E'.$line);
	$line++;

	$sql = "SELECT *
	        FROM `money`
	        WHERE !`deleted`
	          AND `sum`>0
			  AND `dtime_add` LIKE '".MON."%'
	        ORDER BY `id`";
	$q = query($sql);
	$money = array();
	while($r = mysql_fetch_assoc($q))
		$money[$r['id']] = $r;

	$money = _clientLink($money);
	$money = _dogNomer($money);
	$money = _zayavLink($money);

	$start = $line;
	$sum = 0;
	foreach($money as $r) {
		$sheet->getCell('A'.$line)->setValue(reportData($r['dtime_add']));
		if($r['client_id'])
			$sheet->getCell('B'.$line)->setValue(utf8(htmlspecialchars_decode($r['client_fio'])));
		$sheet->getCell('C'.$line)->setValue(_sumSpace($r['sum']));
		$sheet->getCell('D'.$line)->setValue(utf8(_invoice($r['invoice_id'])));
		$head = isset($r['zayav_head']) ? utf8($r['zayav_head']).'. ': '';
		$avans = $r['dogovor_id'] ? 'Авансовый платеж (договор '.utf8($r['dogovor_nomer']).'). ' : '';
		$sheet->getCell('E'.$line)->setValue($head.$avans.utf8(htmlspecialchars_decode($r['prim'])).' ');
		$line++;
		$sum += $r['sum'];
	}

	$sheet->setSharedStyle(styleContent(), 'A'.$start.':E'.$line);
	$sheet->setSharedStyle(styleResult(), 'A'.$line.':E'.$line);
	$sheet->getStyle('A'.$start.':A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle('C'.$start.':C'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->setCellValue('C'.$line, _sumSpace($sum));
	$sheet->getStyle('C'.$line)->getFont()->setBold(true);
	$sheet->getStyle('D'.$start.':D'.$line)->getAlignment()->setWrapText(true);
	$sheet->getStyle('E'.$start.':E'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->getStyle('E'.$start.':E'.$line)->getAlignment()->setWrapText(true);
	$sheet->setCellValue('B'.$line, 'Итог:');

	$line++;
	$sql = "SELECT `i`.*,
					IFNULL(SUM(`m`.`sum`),0) AS `sum`
			FROM `invoice` AS `i`
				LEFT JOIN `money` AS `m`
				ON `i`.`id`=`m`.`invoice_id`
				 AND !`m`.`deleted`
				 AND `m`.`sum`>0
				 AND `m`.`dtime_add` LIKE '".MON."%'
			GROUP BY `i`.`id`
			ORDER BY `i`.`id`";
	$q = query($sql);
	$start = $line;
	while($r = mysql_fetch_assoc($q)) {
		$sheet->getCell('B'.$line)->setValue(utf8($r['name']));
		$sheet->getCell('C'.$line)->setValue($r['sum']);
		$line++;
	}
	$sheet->setSharedStyle(styleContent(), 'B'.$start.':C'.($line - 1));
	$sheet->setSharedStyle(styleContent(), 'B'.$start.':C'.($line - 1));
	$sheet->getStyle('B'.$start.':B'.($line - 1))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
	$sheet->getStyle('C'.$start.':C'.($line - 1))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
	$sheet->getStyle('B'.($start - 1).':C'.($start - 1))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle('B'.($line - 1).':C'.($line - 1))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
	$sheet->getStyle('B'.$start.':B'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

	$sheet->getStyle('A1:E'.$line)->getFont()->setSize(8);

	freeLine($line);
}
function debtors() {
	global $book, $index;

	$book->createSheet();
	$book->setActiveSheetIndex($index++);
	$sheet = $book->getActiveSheet();
	pageSetup('Должники', 110);
	$line = 1;

	$sheet->getColumnDimension('A')->setWidth(8);
	$sheet->getColumnDimension('B')->setWidth(60);
	$sheet->getColumnDimension('C')->setWidth(60);
	$sheet->getColumnDimension('D')->setWidth(30);
	$sheet->getColumnDimension('E')->setWidth(25);

	$sheet->setCellValue('A'.$line, 'Должники на '.utf8(FullData(curTime())).':');
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);
	$line += 2;

	$sheet->setCellValue('B'.$line, 'ФИО');
	$sheet->setCellValue('C'.$line, 'Телефон');
	$sheet->setCellValue('D'.$line, 'Договор');
	$sheet->setCellValue('E'.$line, 'Сумма');
	$sheet->setSharedStyle(styleHead(), 'A'.$line.':E'.$line);
	$line++;

	$sql = "SELECT *
			FROM `client`
			WHERE !`deleted`
			  AND `balans`<0
			ORDER BY `fio`";
	$q = query($sql);
	$client = array();
	while($r = mysql_fetch_assoc($q)) {
		$r['dog'] = array();
		$client[$r['id']] = $r;
	}

	if(empty($client))
		return;

	// составление списка договоров, по которым есть долги
	$dog = query_ass("SELECT `id`,`nomer` FROM `zayav_dogovor` WHERE !`deleted` AND `client_id` IN (".implode(',', array_keys($client)).")");
	$sql = "SELECT *
			FROM `zayav`
			WHERE !`deleted`
			  AND `dogovor_id`
			  AND `client_id` IN (".implode(',', array_keys($client)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		if($r['accrual_sum'] - $r['oplata_sum'] > 0)
			$client[$r['client_id']]['dog'][] = $dog[$r['dogovor_id']];

	// составление списка ВГ, по которым есть долги
	$sql = "SELECT *
			FROM `zayav`
			WHERE !`deleted`
			  AND (`nomer_vg` OR `nomer_g` OR `nomer_d` OR `nomer_t`)
			  AND `client_id` IN (".implode(',', array_keys($client)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		if($r['accrual_sum'] - $r['oplata_sum'] <= 0)
			continue;
		if(!empty($client[$r['client_id']]['dog']))
			continue;
		$nomer = '';
		if($r['nomer_vg'])
			$nomer = 'ВГ'.$r['nomer_vg'];
		elseif($r['nomer_g'])
			$nomer = 'Ж'.$r['nomer_g'];
		elseif($r['nomer_d'])
			$nomer = 'Д'.$r['nomer_d'];
		elseif($r['nomer_t'])
			$nomer = 'Т'.$r['nomer_t'];
		if($nomer)
			$client[$r['client_id']]['dog'][] = $nomer;
	}

	$start = $line;
	$sum = 0;
	$n = 1;

	foreach($client as $id => $r) {
		$balans = abs($r['balans']);
		$sum += $balans;
		$sheet->getCell('A'.$line)->setValue($n++);
		$sheet->getCell('B'.$line)->setValue(utf8(htmlspecialchars_decode($r['fio'])));
		$sheet->getCell('C'.$line)->setValue(utf8(htmlspecialchars_decode($r['telefon'])));
//		if(isset($dog[$id]))
//			$sheet->getCell('D'.$line)->setValue($dog[$id]);
		//if(isset($r['dog']))
			$sheet->getCell('D'.$line)->setValue(implode(', ', $r['dog']));
		$sheet->getCell('E'.$line)->setValue($balans);
		$line++;
	}
	$sheet->setSharedStyle(styleContent(), 'A'.$start.':E'.$line);
	$sheet->setSharedStyle(styleResult(), 'A'.$line.':E'.$line);
	$sheet->getStyle('B'.$start.':B'.$line)->getAlignment()->setWrapText(true);
	$sheet->getStyle('C'.$start.':C'.($line - 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->setCellValue('D'.$line, 'Итог:');
	$sheet->getStyle('D'.$start.':D'.($line - 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->setCellValue('E'.$line, _sumSpace($sum));

	$sheet->getStyle('A1:E'.$line)->getFont()->setSize(8);

	freeLine($line);
}
function xls_expense() {
	global $book, $index;

	$book->createSheet();
	$book->setActiveSheetIndex($index++);
	$sheet = $book->getActiveSheet();
	pageSetup('Расходы', 110);
	$line = 1;

	$sheet->getColumnDimension('A')->setWidth(14);
	$sheet->getColumnDimension('B')->setWidth(25);
	$sheet->getColumnDimension('C')->setWidth(120);

	$sheet->setCellValue('A'.$line, 'Расходы за '.utf8(MONTH).':');
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);
	$line += 2;

	$sql = "SELECT *
	        FROM `money`
	        WHERE !`deleted`
	          AND `sum`<0
			  AND `dtime_add` LIKE '".MON."%'
	        ORDER BY `id`";
	$q = query($sql);
	$money = array();
	while($r = mysql_fetch_assoc($q))
		$money[$r['invoice_id']][] = $r;

	foreach($money as $id => $invoice) {
		$sheet->getCell('A'.$line)->setValue('Счёт '.utf8(_invoice($id)).':');
		$line++;
		$sheet->setCellValue('A'.$line, 'Дата');
		$sheet->setCellValue('B'.$line, 'Сумма');
		$sheet->setCellValue('C'.$line, 'Описание');
		$sheet->setSharedStyle(styleHead(), 'A'.$line.':C'.$line);
		$line++;
		$start = $line;
		$sum = 0;
		foreach($invoice as $r) {
			$sheet->getCell('A'.$line)->setValue(reportData($r['dtime_add']));
			$sheet->getCell('B'.$line)->setValue(abs($r['sum']));
			$expense = utf8(htmlspecialchars_decode(_expense($r['expense_id'])));
			$worker = $r['worker_id'] ? ($r['expense_id'] ? ': ' : '').utf8(_viewer($r['worker_id'], 'name')).'. ' : '';
			$prim = !empty($r['prim']) ? ($r['expense_id'] && !$worker ? ': ' : '').utf8(htmlspecialchars_decode($r['prim'])) : '';
			$sheet->getCell('C'.$line)->setValue($expense.$worker.$prim);
			$line++;
			$sum += $r['sum'];
		}
		$sheet->setSharedStyle(styleContent(), 'A'.$start.':C'.$line);
		$sheet->setSharedStyle(styleResult(), 'A'.$line.':C'.$line);
		$sheet->getStyle('A'.$start.':A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->setCellValue('A'.$line, 'Итог:');
		$sheet->setCellValue('B'.$line, _sumSpace(abs($sum)));
		$line += 2;
	}

	$sheet->getStyle('A1:C'.$line)->getFont()->setSize(8);

	freeLine($line);
}
function revenue() {
	global $book, $index;

	$book->createSheet();
	$book->setActiveSheetIndex($index++);
	$sheet = $book->getActiveSheet();
	pageSetup('Выручка', 110);
	$line = 1;

	$sheet->getColumnDimension('A')->setWidth(14);
	$sheet->getColumnDimension('B')->setWidth(30);

	$sheet->setCellValue('A'.$line, 'Выручка наличными за '.utf8(MONTH).':');
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);
	$line += 2;

	$sheet->setCellValue('A'.$line, 'Дата');
	$sheet->setCellValue('B'.$line, 'Сумма');
	$sheet->setSharedStyle(styleHead(), 'A'.$line.':B'.$line);
	$line++;

	$sql = "SELECT *
			FROM `invoice_transfer`
			WHERE !`deleted`
			  AND `invoice_from`=1
			  AND (`invoice_to`=4 OR `invoice_to`=5)
			  AND `dtime_add` LIKE '".MON."%'
	        ORDER BY `id`";
	$q = query($sql);
	$money = array();
	while($r = mysql_fetch_assoc($q))
		$money[$r['id']] = $r;

	$start = $line;
	$sum = 0;
	foreach($money as $r) {
		$sheet->getCell('A'.$line)->setValue(reportData($r['dtime_add']));
		$sheet->getCell('B'.$line)->setValue(abs($r['sum']));
		$sum += $r['sum'];
		$line++;
	}
	$sheet->setSharedStyle(styleContent(), 'A'.$start.':B'.$line);
	$sheet->setSharedStyle(styleResult(), 'A'.$line.':B'.$line);
	$sheet->getStyle('A'.$start.':A'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->setCellValue('A'.$line, 'Итог:');
	$sheet->setCellValue('B'.$line, _sumSpace(abs($sum)));

	$sheet->getStyle('A1:B'.$line)->getFont()->setSize(8);

	freeLine($line);
}

function toMailSend() {
	mail(CRON_MAIL, 'Cron Evrookna: report_month.php', ob_get_contents());
}
function countCronTime() {
	echo "\n\n----\nExecution time: ".round(microtime(true) - TIME, 3);
}

set_time_limit(2500);
define('CRON', !empty($_GET['cron'])); //Если обращение через cron, то сохранение в файл

if(CRON) {
	ob_start();
	set_error_handler('toMailSend');
	register_shutdown_function('countCronTime');
	register_shutdown_function('toMailSend');
}

require_once dirname(dirname(__FILE__)).'/config.php';
require_once API_PATH.'/excel/PHPExcel.php';

define('MON', strftime('%Y-%m', time() - (CRON ? 86400 : 0)));
$ex = explode('-', MON);
define('MONTH', _monthDef($ex[1]).' '.$ex[0]);
define('MON_FULL', utf8(_monthFull($ex[1])));
define('YEAR', $ex[0]);

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
$colLast = 'L'; // Последняя колонка
$index = 1;     // Номер создаваемой страницы

pageSetup('Заявки');
colWidth();
aboutShow();
headShow();
contentShow();

zp(2);//мальчики
zp(1);//девочки
incomes();
xls_expense();
debtors();
revenue();

$book->setActiveSheetIndex(0);


if(!CRON) {
	header('Content-Type:application/vnd.ms-excel');
	header('Content-Disposition:attachment;filename="report.xls"');
}
$writer = PHPExcel_IOFactory::createWriter($book, 'Excel5');
$writer->save(CRON ? APP_PATH.'/files/report/report_month_'.MON.'.xls' : 'php://output');

if(CRON) {
	$link = APP_HTML.'/files/report/report_month_'.MON.'.xls';
	$sql = "INSERT INTO `attach` (
				`type`,
				`name`,
				`link`
			) VALUES (
				'report',
				'".MON."',
				'".$link."'
			)";
	query($sql);
	_historyInsert(
		47,
		array(
			'value' => MONTH,
			'value1' => $link
		)
	);
}

mysql_close();
exit;
