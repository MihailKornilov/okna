<?php

// ---===! report !===--- ������ �������

function report() {
	$def = 'history';
	$pages = array(
		'history' => '������� ��������',
		'money' => '������'.(TRANSFER_CONFIRM ? ' (<b>'.TRANSFER_CONFIRM.'</b>)' : ''),
		'month' => '������ ����� �� �������',
		'salary' => '�������� �����������'
	);

	if(!RULES_HISTORYSHOW)
		unset($pages['history']);

	$d = empty($_GET['d']) ? $def : $_GET['d'];
	if(empty($_GET['d']) && !empty($pages) && empty($pages[$d]))
		foreach($pages as $p => $name) {
			$d = $p;
			break;
		}

	$d1 = '';
	$right = '';
	switch($d) {
		default: $d = $def;
		case 'history':
			if(RULES_HISTORYSHOW) {
				$data = history();
				$left = $data['spisok'];
				$right = history_right();
			} else
				_norules();
			break;
		case 'money':
			$d1 = empty($_GET['d1']) ? 'income' : $_GET['d1'];
			switch($d1) {
				default: $d1 = 'income';
				case 'income':
					switch(@$_GET['d2']) {
						case 'all': $left = income_all(); break;
						case 'year':
							if(empty($_GET['year']) || !preg_match(REGEXP_YEAR, $_GET['year'])) {
								$left = '������ ������������ ���.';
								break;
							}
							$left = income_year(intval($_GET['year']));
							break;
						case 'month':
							if(empty($_GET['mon']) || !preg_match(REGEXP_YEARMONTH, $_GET['mon'])) {
								$left = '������ ������������ �����.';
								break;
							}
							$left = income_month($_GET['mon']);
							break;
						default:
							if(!_calendarDataCheck(@$_GET['day']))
								$_GET['day'] = strftime('%Y-%m-%d', time());
							$left = income_day($_GET['day']);
							$right = income_right($_GET['day']);
					}
					break;
				case 'expense':
					$left = expense();
					$right = expense_right();
					break;
				case 'invoice': $left = invoice(); break;
			}
			$left =
				'<div id="dopLinks">'.
					'<a class="link'.($d1 == 'income' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=income">�������</a>'.
					'<a class="link'.($d1 == 'expense' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=expense">�������</a>'.
					'<a class="link'.($d1 == 'invoice' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=invoice">�����'.(TRANSFER_CONFIRM ? ' (<b>'.TRANSFER_CONFIRM.'</b>)' : '').'</a>'.
				'</div>'.
				$left;
			break;
		case 'month': $left = report_month(); break;
		case 'salary':
			if(!empty($_GET['id']) && preg_match(REGEXP_NUMERIC, $_GET['id'])) {
				$v = salaryFilter(array(
					'worker_id' => intval($_GET['id']),
					'mon' => @$_GET['mon'],
					'acc_id' => intval(@$_GET['acc_id']),
				));
				$left = salary_worker($v);
				if(defined('WORKER_OK'))
					$right = '<input type="hidden" id="year" value="'.$v['y'].'" />'.
							 '<div id="monthList">'.salary_monthList($v).'</div>';
			} else
				$left = salary();
			break;
	}

	$links = '';
	if($pages)
		foreach($pages as $p => $name)
			$links .= '<a href="'.URL.'&p=report&d='.$p.'"'.($d == $p ? ' class="sel"' : '').'>'.$name.'</a>';

	return
	'<table class="tabLR '.($d1 ? $d1 : $d).'" id="report">'.
		'<tr><td class="left">'.$left.
			'<td class="right">'.
				'<div class="rightLink">'.$links.'</div>'.
				$right.
	'</table>';
}//report()

function history($v=array()) {
	return _history(
		'history_types',
		array('_clientLink', '_zayavLink', '_dogNomer'),
		$v,
		array(
			'client_id' => !empty($v['client_id']) && _num($v['client_id']) ? intval($v['client_id']) : 0,
			'zayav_id' => !empty($v['zayav_id']) && _num($v['zayav_id']) ? intval($v['zayav_id']) : 0,
			'dogovor_id' => !empty($v['dogovor_id']) && _num($v['dogovor_id']) ? intval($v['dogovor_id']) : 0
		)
	);
}//history()
function history_group_name() {
	return array(
		1 => '�������',
		2 => '������',
		3 => '��������',
		4 => '�����',
		5 => '������',
		6 => '������� �����������',
		7 => '���������'
	);
}//history_group_name()
function history_group($v) {
	$ids = array(
		1 => '1,2,3',
		2 => '4,5,6,7,8,9,15,16,17,18,21,22,23,24,25,26,29,30,31',
		3 => '19,20,42',
		4 => '27,28',

		5 => '10,11,12,20,36,37,38,39,40,41,43,52,53,56,57',

		6 => '32,33,34,35,37',
		7 => '13,14,501,502,503,504,505,506,507,508,509,510,511,512,513,514,515,516,517,518,519,520'
	);
	return $ids[$v];
}//history_group()
function history_types($v) {
	switch($v['type']) {
		case 1: return '�������� ������ ������� '.$v['client_link'].'.';
		case 2: return '��������� ������ ������� '.$v['client_link'].':<div class="changes">'.$v['value'].'</div>';
		case 3: return '�������� ������� '.$v['client_link'].'.';

		case 4: return '�������� ����� ������  <em>(�����)</em> '.$v['zayav_link'].' ��� ������� '.$v['client_link'].'.';
		case 5: return '��������� ������ ������ <em>(�����)</em> '.$v['zayav_link'].':<div class="changes">'.$v['value1'].'</div>';
		case 6: return '�������� ������ '.$v['zayav_link'].' � ������� '.$v['client_link'].'.';

		case 7: return '���������� �� ����� <b>'.$v['value'].'</b> ���.'.
						($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
						' �� ������ '.$v['zayav_link'].'.';
		case 8: return '�������� ���������� �� ����� <b>'.round($v['value'], 2).'</b> ���.'.
						($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
						' � ������ '.$v['zayav_link'].'.';
		case 9: return '�������������� ���������� �� ����� <b>'.round($v['value'], 2).'</b> ���.'.
						($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
						' � ������ '.$v['zayav_link'].'.';

		case 10: return
			'����� <span class="oplata">'._invoice($v['value2']).'</span> '.
			'�� ����� <b>'.$v['value'].'</b> ���.'.
			($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' �� ������ '.$v['zayav_link'] : '').
			($v['dogovor_id'] ? '. ��������� ����� �� �������� '.$v['dogovor_nomer'] : '').
			'.';
		case 11: return
			'�������� ������� <span class="oplata">'._invoice($v['value2']).'</span> '.
			'�� ����� <b>'.round($v['value'], 2).'</b> ���.'.
			($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' � ������ '.$v['zayav_link'] : '').
			'.';
		case 12: return
			'�������������� ������� <span class="oplata">'._invoice($v['value2']).'</span> '.
			'�� ����� <b>'.round($v['value'], 2).'</b> ���.'.
			($v['value1'] ? ' <em>('.$v['value1'].')</em>' : '').
			($v['zayav_id'] ? ' � ������ '.$v['zayav_link'] : '').
			'.';

		case 13: return '<a href="'.URL.'&p=setup&d=worker">� ����������</a>: ���������� ������ ���������� <u>'._viewer($v['value'], 'name').'</u>.';
		case 14: return '<a href="'.URL.'&p=setup&d=worker">� ����������</a>: �������� ���������� <u>'._viewer($v['value'], 'name').'</u>.';

		case 15: return '��������� ���������� � ���� ��� ����������������� ������ '.$v['zayav_link'].':<div class="changes">'.$v['value1'].'</div>';
		case 16: return '����� '.$v['zayav_link'].' �������� � ��������� �� ���������� ��������.';
		case 17: return '����� '.$v['zayav_link'].' ������.';
		case 18: return '����� '.$v['zayav_link'].' ������������.';
		case 19: return
			($v['value'] ? '����' : '�').'��������� �������� '.$v['dogovor_nomer'].
			' �� '.$v['dogovor_data'].
			' �� ����� <b>'.$v['dogovor_sum'].'</b> ���.'.
			' ��� ������ '.$v['zayav_link'].'.'.
			($v['value'] ? ' <em>(�������: '.$v['value'].'.)</em>' : '');
		case 20: return
			'�������� ���������� ������� ��  �� ����� <b>'.$v['dogovor_avans'].'</b> ���.'.
			' ��� ������ '.$v['zayav_link'].
			' ��� ���������� �������� '.$v['dogovor_nomer'].'.';

		case 21: return '�������� ����� ������ '.$v['zayav_link'].' <em>(���������)</em> ��� ������� '.$v['client_link'].'.';
		case 22: return '��������� ������ ������ '.$v['zayav_link'].' <em>(���������)</em>:<div class="changes">'.$v['value'].'</div>';

		case 23: return '�������� ����� ������ '.$v['zayav_link'].' <em>(�����)</em> ��� ������� '.$v['client_link'].'.';
		case 24: return '��������� ������ ������ '.$v['zayav_link'].' <em>(�����)</em>:<div class="changes">'.$v['value1'].'</div>';
		case 25: return '��������� ������� ������ '.$v['zayav_link'].' <em>(�����)</em>:<br />'.
						'<span style="background-color:#'._statusColor($v['value']).'" class="zstatus">'._zakazStatus($v['value']).'</span>'.
						' � '.
						'<span style="background-color:#'._statusColor($v['value1']).'" class="zstatus">'._zakazStatus($v['value1']).'</span>.'.
						($v['value2'] ? ' ���� ����������: <u>'.FullData($v['value2']).'</u>.' : '');
		case 26: return '��������� ������� ������ '.$v['zayav_link'].' <em>(���������)</em>:<br />'.
						'<span style="background-color:#'._statusColor($v['value']).'" class="zstatus">'._setStatus($v['value']).'</span>'.
						' � '.
						'<span style="background-color:#'._statusColor($v['value1']).'" class="zstatus">'._setStatus($v['value1']).'</span>'.
						($v['value2'] ? ' ���� ����������: <u>'.FullData($v['value2']).'</u>.' : '');

		case 27: return '�������� ����� '.$v['value'].' ��� ������ '.$v['zayav_link'].'.';
		case 28: return '�������� ����� '.$v['value'].' � ������ '.$v['zayav_link'].'.';

		case 29: return '��������� �������� �� ������ '.$v['zayav_link'].':<div class="changes z">'.$v['value'].'</div>';
		case 30: return '������ '.$v['zayav_link'].' ���������� �� <u>�������</u> � <u>���������</u>. ������ ����� "'.$v['value'].'"';

		case 31: return '������� ����� ���� ���������� ������ '.$v['zayav_link'].': <u>'.FullData($v['value']).'</u>.';

		case 32: return '�������� ������� �����������: '.
			($v['value1'] ? '<span class="oplata">'._expense($v['value1']).'</span> ' : '').
			($v['value2'] ? '<em>('.$v['value2'].')</em> ' : '').
			($v['value3'] ? '<u>'._viewer($v['value3'], 'name').'</u> ' : '').
			'�� ����� <b>'.$v['value'].'</b> ���.';
		case 33: return '�������� ������� �����������: '.
			($v['value1'] ? '<span class="oplata">'._expense($v['value1']).'</span> ' : '').
			($v['value2'] ? '<em>('.$v['value2'].')</em> ' : '').
			($v['value3'] ? '��� ���������� <u>'._viewer($v['value3'], 'name').'</u> ' : '').
			'�� ����� <b>'.$v['value'].'</b> ���.';
		case 34: return '�������������� ������� �����������: '.
			($v['value1'] ? '<span class="oplata">'._expense($v['value1']).'</span> ' : '').
			($v['value2'] ? '<em>('.$v['value2'].')</em> ' : '').
			($v['value3'] ? '��� ���������� <u>'._viewer($v['value3'], 'name').'</u> ' : '').
			'�� ����� <b>'.$v['value'].'</b> ���.';
		case 35: return '��������� ������ ������� �� '.FullDataTime($v['value2']).':<div class="changes">'.$v['value'].'</div>';

		case 36: return
			'�������� ���������� �/� �� ����� <b>'.$v['value'].'</b> '.
			($v['value1'] || $v['value4'] ?
				'<em>('.
					($v['value4'] ? '<a class="salary-days" val="'.$v['value3'].'">'.$v['value4'].' �'._end($v['value4'], '���', '��', '���').'</a>' : '').
					($v['value1'] && $v['value4'] ? '. ' : '').$v['value1'].
				')</em> '
			: '').
			'��� ���������� <u>'._viewer($v['value2'], 'name').'</u>.';
		case 37: return
			'������ �/� �� ����� <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'��� ���������� <u>'._viewer($v['value2'], 'name').'</u>.';
		case 38: return '��������� ������� ����� ��� ����� <span class="oplata">'._invoice($v['value1']).'</span>: <b>'.$v['value'].'</b> ���.'.
						($v['value2'] ? '<br /><div class="changes">'.$v['value2'].'</div>' : '');
		case 39:
			return '������� �� ����� <span class="oplata">'._invoice($v['value1']).'</span> '.
				   '�� ���� <span class="oplata">'._invoice($v['value2']).'</span> '.
				   '� ����� <b>'.$v['value'].'</b> ���.'.
				   ($v['value3'] ? ' <em>('.$v['value3'].')</em> ' : '');
		case 40:
			return '��������� ������ �/� � ����� <b>'.$v['value1'].'</b> ���. '.
				   '��� ���������� <u>'._viewer($v['value'], 'name').'</u>. '.
				   '���������� '.$v['value2'].'-�� ����� ������� ������.';
		case 41: return '�������� ������ �/� � ���������� <u>'._viewer($v['value'], 'name').'</u>.';

		case 42: return '��������� ������ �������� '.$v['dogovor_nomer'].' '.
						'� ������ '.$v['zayav_link'].':'.
						'<div class="changes">'.$v['value'].'</div>';

		case 43: return '������������� ����������� �� ����: <a class="income-show" val="'.$v['value1'].'">'.$v['value'].' ������'._end($v['value'], '', '�', '��').'</a>.';

		case 44: return
			'�������� ������ �� �/� �� ����� <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'� ���������� <u>'._viewer($v['value2'], 'name').'</u>.';
		case 45: return '��������� ������� �/� � ����� <b>'.$v['value1'].'</b> ���. '.
				        '��� ���������� <u>'._viewer($v['value'], 'name').'</u>. ';

		case 46: return '�������������� ���������� �/� ���������� <u>'._viewer($v['value1'], 'name').'</u> '.
						'� ������� <b>'.$v['value'].'</b> ���. <em>('.$v['value2'].')</em>.';
		case 47: return '������������ ����� �� <a href="'.$v['value1'].'">'.$v['value'].'</a>.';

		case 50: return '�������� ���������� �/� � ����� <b>'.$v['value'].'</b> ���. � ���������� <u>'._viewer($v['value1'], 'name').'</u>.';
		case 51: return '�������� ������ �/� � ����� <b>'.$v['value'].'</b> ���. � ���������� <u>'._viewer($v['value1'], 'name').'</u>.';

		case 52: return '�����������'._end($v['value'], '', '�').' '.
						'<a class="transfer-show" val="'.$v['value1'].'">'.$v['value'].' �������'._end($v['value'], '', '�', '��').'</a>'.
						($v['value2'] ? ' <em>('.$v['value2'].')</em>' : '').
						'.';

		case 53: return '����� ������� ����� ������� �� ����� <b>'.$v['value'].'</b> ���.';

		case 54: return '����������� <a href="'.URL.'&p=report&d=salary&id='.$v['value1'].'&mon='.$v['value2'].'">���� ������ �/�</a> '.
						'�� ����� <b>'.$v['value'].'</b> ���.<br />'.
						'���������: <u>'._viewer($v['value1'], 'name').'</u>. '.
						'�����: '.$v['value3'].'.';
		case 55: return '����� <a href="'.URL.'&p=report&d=salary&id='.$v['value1'].'&mon='.$v['value2'].'">���� ������ �/�</a> '.
						'�� ����� <b>'.$v['value'].'</b> ���.<br />'.
						'���������: <u>'._viewer($v['value1'], 'name').'</u>. '.
						'�����: '.$v['value3'].'.';

		case 56: return '������� �� ����� <b>'.$v['value'].'</b> ���. '.
						($v['value1'] ? ' <em>('.$v['value1'].')</em> ' : '').
						'�� ������ '.$v['zayav_link'].'.';
		case 57: return '�������� �������� �� ����� <b>'.$v['value'].'</b> ���. '.
						($v['value1'] ? ' <em>('.$v['value1'].')</em> ' : '').
						'�� ������ '.$v['zayav_link'].'.';

		case 58: return '������ '.$v['zayav_link'].' ���������� �� <u>���������</u> � <u>������</u>.';

		case 59: return
			'����������� �������� '.$v['dogovor_nomer'].
			' �� '.$v['dogovor_data'].
			' �� ����� <b>'.$v['dogovor_sum'].'</b> ���.'.
			' ��� ������ '.$v['zayav_link'].'.'.
			($v['value'] ? ' <em>(�������: '.$v['value'].'.)</em>' : '');

		case 60: return '������ ���� <span class="oplata">'._invoice($v['value']).'</span>.';


		case 501: return '<a href="'.URL.'&p=setup&d=product">� ����������</a>: �������� ������ ������������ ������� "'.$v['value'].'".';
		case 502: return '<a href="'.URL.'&p=setup&d=product">� ����������</a>: ��������� ������ ������� "'.$v['value1'].'":<div class="changes">'.$v['value'].'</div>';
		case 503: return '<a href="'.URL.'&p=setup&d=product">� ����������</a>: �������� ������������ ������� "'.$v['value'].'".';

		case 510: return '<a href="'.URL.'&p=setup&d=rekvisit">� ����������</a>: ��������� ���������� �����������:<div class="changes">'.$v['value'].'</div>';

		case 504: return '<a href="'.URL.'&p=setup&d=product">� ����������</a>: �������� ������ ������� ��� ������� "'.$v['value'].'": '.$v['value1'].'.';
		case 505: return '<a href="'.URL.'&p=setup&d=product">� ����������</a>: ��������� ������� � ������� "'.$v['value'].'":<div class="changes">'.$v['value1'].'</div>';
		case 506: return '<a href="'.URL.'&p=setup&d=product">� ����������</a>: �������� ������� � ������� "'.$v['value'].'": '.$v['value1'].'.';

		case 507: return '<a href="'.URL.'&p=setup&d=">� ����������</a>: �������� ������ ���� ������� "'.$v['value'].'".';
		case 508: return '<a href="'.URL.'&p=setup&d=">� ����������</a>: ��������� ���� ������� "'.$v['value'].'":<div class="changes">'.$v['value1'].'</div>';
		case 509: return '<a href="'.URL.'&p=setup&d=">� ����������</a>: �������� ���� ������� "'.$v['value'].'".';

		case 511: return '<a href="'.URL.'&p=setup&d=zayavexpense">� ����������</a>: �������� ����� ��������� �������� ������ <u>'.$v['value'].'</u>.';
		case 512: return '<a href="'.URL.'&p=setup&d=zayavexpense">� ����������</a>: ��������� ������ ��������� �������� ������ <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 513: return '<a href="'.URL.'&p=setup&d=zayavexpense">� ����������</a>: �������� ������ ��������� �������� ������ <u>'.$v['value'].'</u>.';

		case 514: return '<a href="'.URL.'&p=setup&d=worker">� ����������</a>: ��������� ������ ���������� <u>'._viewer($v['value'], 'name').'</u>:<div class="changes">'.$v['value1'].'</div>';

		case 515: return '<a href="'.URL.'&p=setup&d=invoice">� ����������</a>: �������� ������ ����� <u>'.$v['value'].'</u>.';
		case 516: return '<a href="'.URL.'&p=setup&d=invoice">� ����������</a>: ��������� ������ ����� <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 517: return '<a href="'.URL.'&p=setup&d=invoice">� ����������</a>: �������� ����� <u>'.$v['value'].'</u>.';

		case 518: return '<a href="'.URL.'&p=setup&d=expense">� ����������</a>: �������� ����� ��������� �������� ����������� <u>'.$v['value'].'</u>.';
		case 519: return '<a href="'.URL.'&p=setup&d=expense">� ����������</a>: ��������� ������ ��������� �������� ����������� <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 520: return '<a href="'.URL.'&p=setup&d=expense">� ����������</a>: �������� ������ ��������� �������� ����������� <u>'.$v['value'].'</u>.';

		default: return $v['type'];
	}
}//history_types()
function history_right() {
	$workers = query_selJson("
		SELECT
			DISTINCT `h`.`viewer_id_add`,
			CONCAT(`u`.`first_name`,' ',`u`.`last_name`)
        FROM `history` `h`,`vk_user` `u`
        WHERE `h`.`viewer_id_add`=`u`.`viewer_id`");
	return
		'<script type="text/javascript">var WORKERS='.$workers.';</script>'.
		'<div class="findHead">���������</div>'.
		'<input type="hidden" id="viewer_id_add">'.
		'<div class="findHead">���������</div>'.
		'<input type="hidden" id="action">';
}//history_right()

function _invoiceBalans($invoice_id, $start=false) {// ��������� �������� ������� �����
	if($start === false) {
		$start = round(_invoice($invoice_id, 'start'), 2);
		if($start == -1)
			return false;
	}
	$income = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `money` WHERE !`deleted` AND !`confirm` AND `invoice_id`=".$invoice_id);
	$from = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `invoice_transfer` WHERE !`deleted` AND `invoice_from`=".$invoice_id);
	$to = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `invoice_transfer` WHERE !`deleted` AND `invoice_to`=".$invoice_id);
	return round($income - $start - $from + $to, 2);
}//_invoiceBalans()
function invoice() {
	$iCashSum = array(); //������� ����� ��� ������� �����
	foreach(_invoice() as $r)
		$iCashSum[$r['id']] = _invoiceBalans($r['id']);
	return
		'<script type="text/javascript">'.
			'var CASH_SUM='._assJson($iCashSum).','.
				'MON_SPISOK='._selJson(_monthDef(0, 1)).';'.
		'</script>'.
		'<div class="headName">'.
			'�����'.
			'<a class="add transfer">������� ����� �������</a>'.
			'<span>::</span>'.
			'<a href="'.URL.'&p=setup&d=invoice" class="add">���������� �������</a>'.
		'</div>'.
		'<div id="confirm-info">'.income_confirm_info().'</div>'.
		invoice_transfer_confirm().
		'<div id="invoice-spisok">'.invoice_spisok().'</div>'.
		'<div class="headName">������� ���������</div>'.
		'<div class="transfer-spisok">'.transfer_spisok().'</div>';
}//invoice()
function income_confirm_info() {
	if(!$confirm = query_value("SELECT COUNT(`id`) FROM `money` WHERE !`deleted` AND `confirm`"))
		return '';
	return
	'<div class="_info">'.
		'<b>'.$confirm.' ������'._end($confirm, '', '�', '��').'</b> �����'._end($confirm, '�', '�').'� ������������� ����������� �� ����. '.
		'<a class="income-confirm">�����������</a>'.
	'</div>';
}
function invoice_transfer_confirm() {//������������� ��������� ��� ������������
	if(TRANSFER_CONFIRM)
		return
			'<div class="_info">'.
				'���� ��������, ��������� �������������: <b>'.TRANSFER_CONFIRM.'</b>. '.
				'<a class="transfer-confirm">�����������</a>'.
			'</div>';
	return '';
}//invoice_transfer_confirm()
function invoice_transfer_sql($invoice_from, $invoice_to, $sum) {//�������� �������� ����� �������
	$sql = "INSERT INTO `invoice_transfer` (
				`invoice_from`,
				`invoice_to`,
				`sum`,
				`confirm`,
				`viewer_id_add`
			) VALUES (
				".$invoice_from.",
				".$invoice_to.",
				".$sum.",
				".(_invoice($invoice_from, 'confirm_transfer') || _invoice($invoice_to, 'confirm_transfer') ? 1 : 0).",
				".VIEWER_ID."
			)";
	query($sql);

	invoice_history_insert(array(
		'action' => 4,
		'table' => 'invoice_transfer',
		'id' => mysql_insert_id()
	));

	_historyInsert(
		39,
		array(
			'value' => $sum,
			'value1' => $invoice_from,
			'value2' => $invoice_to
		)
	);
}//invoice_transfer_sql()
function invoice_spisok() {
	$invoice = _invoice();
	if(empty($invoice))
		return '����� �� ����������.';

	$send = '<table class="_spisok">';
	foreach($invoice as $r) {
		$continue = 1;
		if($r['visible'])
			foreach(explode(',', $r['visible']) as $i)
				if(VIEWER_ID == $i) {
					$continue = 0;
					break;
				}

		if($r['deleted'] || !VIEWER_ADMIN && $continue)
			continue;

		$send .= '<tr>'.
			'<td class="name"><b>'.$r['name'].'</b><pre>'.$r['about'].'</pre>'.
			'<td class="balans">'.
				($r['start'] != -1 ?
					'<b>'._sumSpace(_invoiceBalans($r['id'])).'</b> ���.' :
					(VIEWER_ADMIN ? '' : '<a class="invoice_set" val="'.$r['id'].'">����������<br />�������<br />�����</a>')
				).
			'<td><div val="'.$r['id'].'" class="img_note'._tooltip('���������� ������� ��������', -95).'</div>'.
			(VIEWER_ADMIN ?
				'<td><a class="invoice_set" val="'.$r['id'].'">����������<br />�������<br />�����</a>'.
				'<td><a class="invoice_close" val="'.$r['id'].'">�������<br />����</a>'
				: ''
			);
	}
	$send .= '</table>';
	return $send;
}//invoice_spisok()
function transfer_spisok($v=array()) {
	$v = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 20,
		'confirm' => _num(@$v['confirm']),
		'ids' => !empty($v['ids']) ? $v['ids'] : ''
	);

	$cond = "!`deleted`".
	        ($v['confirm'] ? " AND `confirm`=".$v['confirm'] : '').
	        ($v['ids'] ? " AND `id` IN (".$v['ids'].")" : '');

	$all = query_value("SELECT COUNT(*) FROM `invoice_transfer` WHERE ".$cond);
	if(!$all)
		return '';

	$start = ($v['page'] - 1) * $v['limit'];
	$sql = "SELECT *
	        FROM `invoice_transfer`
	        WHERE ".$cond."
	        ORDER BY `id` DESC
	        LIMIT ".$start.",".$v['limit'];
	$q = query($sql);
	$send = '';
	if($v['page'] == 1)
		$send = '<table class="_spisok _money">'.
			'<tr>'.
				($v['confirm'] ? '<th>' : '').
				'<th>C����'.
				'<th>�� �����'.
				'<th>�� ����'.
				'<th>��������'.
				'<th>����'.
				(VIEWER_ADMIN ? '<th>' : '');
	while($r = mysql_fetch_assoc($q))
		$send .=
			'<tr>'.
				($v['confirm'] ? '<td>'._check($r['id'].'_') : '').
				'<td class="sum">'._sumSpace($r['sum']).
				'<td><span class="type">'._invoice($r['invoice_from']).'</span>'.
				'<td><span class="type">'._invoice($r['invoice_to']).'</span>'.
				   ($r['confirm'] ? '<br /><span class="confirm'.($r['confirm'] == 2 ? '' : ' no').'">'.($r['confirm'] == 2 ? '' : '�� ').'������������</span>' : '').
				'<td class="about">'.
					($r['income_count'] ? '<a class="income-show" val="'.$r['income_ids'].'">'.$r['income_count'].' ������'._end($r['income_count'], '', '�', '��').'</a>' : '').
					(VIEWER_ADMIN && $r['confirm'] && $r['about'] ? ($r['income_count'] ? '<br />' : '').$r['about'] : '').
				'<td class="dtime">'.FullDataTime($r['dtime_add'], 1).
				(VIEWER_ADMIN ? '<td><div val="'.$r['id'].'" class="img_del'._tooltip('�������', -30).'</div>' : '');
	if($start + $v['limit'] < $all) {
		$c = $all - $start - $v['limit'];
		$c = $c > $v['limit'] ? $v['limit'] : $c;
		$send .=
			'<tr class="_next" val="'.($v['page'] + 1).'"><td colspan="7">'.
				'<span>�������� ��� '.$c.' �����'._end($c, '�', '�', '��').'</span>';
	}
	if($v['page'] == 1)
		$send .= '</table>';
	return $send;
}//transfer_spisok()
function invoiceHistoryAction($id, $i='name') {//�������� �������� � ������� ������
	$action = array(
		1 => array(
			'name' => '�����',
			'znak' => ''
		),
		2 => array(
			'name' => '�������� �������',
			'znak' => '-'
		),
		3 => array(
			'name' => '�������������� �������',
			'znak' => ''
		),
		4 => array(
			'name' => '������� ����� �������',
			'znak' => ''
		),
		5 => array(
			'name' => '��������� ������� �����',
			'znak' => ''
		),
		6 => array(
			'name' => '������',
			'znak' => '-'
		),
		7 => array(
			'name' => '�������� �������',
			'znak' => ''
		),
		8 => array(
			'name' => '�������������� �������',
			'znak' => '-'
		),
		9 => array(
			'name' => '�������������� �������',
			'znak' => ''
		),
		10 => array(
			'name' => '��������� �������',
			'znak' => ''
		),
		11 => array(
			'name' => '������������� �������',
			'znak' => ''
		),
		12 => array(
			'name' => '�������� ��������',
			'znak' => '-'
		),
		13 => array(
			'name' => '�������',
			'znak' => '-'
		),
		14 => array(
			'name' => '�������� ��������',
			'znak' => ''
		),
		15 => array(
			'name' => '�������� �����',
			'znak' => ''
		)
	);
	return $action[$id][$i];
}//invoiceHistoryAction()
function invoice_history($invoice_id) {
	$invoice = $invoice_id > 100 ? '�������� ' . _viewer($invoice_id, 'name') : _invoice($invoice_id);
	return
		'<input type="hidden" id="invoice_history_id" value="'.$invoice_id.'" />'.
		'<div id="dopLinks">' .
			'���� <u>'.$invoice.'</u> ' .
			'<a class="link sel full">��������</a>' .
			'<a class="link ostatok">�� ����</a>' .
		'</div>'.
		'<div id="ih-data" class="dn">'.
			'<input type="hidden" id="ih-year" value="'.strftime('%Y').'" />'.
			'<input type="hidden" id="ih-mon" value="'.intval(strftime('%m')).'" />'.
		'</div>'.
		'<div id="ih-spisok">'.invoice_history_full(array('invoice_id'=>$invoice_id)).'</div>';
}//invoice_history()
function invoice_history_full($v) {
	$v = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 15,
		'invoice_id' => intval($v['invoice_id']),
		'day' => !empty($v['day']) && preg_match(REGEXP_DATE, $v['day']) ? $v['day'] : TODAY
	);

	$cond = "`h`.`invoice_id`=".$v['invoice_id'];
	if($v['day'] != TODAY)
		$cond .= " AND `h`.`dtime_add` LIKE '".$v['day']."%'";

	$all = query_value("SELECT COUNT(*) FROM `invoice_history` `h` WHERE ".$cond);
	if(!$all)
		return '������� ���.';

	$send = '';
	$start = ($v['page'] - 1) * $v['limit'];
	$sql = "SELECT `h`.*,
				   IFNULL(`m`.`zayav_id`,0) AS `zayav_id`,
				   IFNULL(`m`.`expense_id`,0) AS `expense_id`,
				   IFNULL(`m`.`worker_id`,0) AS `worker_id`,
				   IFNULL(`m`.`dogovor_id`,0) AS `dogovor_id`,
				   IFNULL(`m`.`prim`,'') AS `prim`,
				   IFNULL(`i`.`invoice_from`,0) AS `invoice_from`,
				   IFNULL(`i`.`invoice_to`,0) AS `invoice_to`,
				   IFNULL(`i`.`income_count`,0) AS `income_count`,
				   IFNULL(`i`.`income_ids`,'') AS `income_ids`
			FROM `invoice_history` `h`
				LEFT JOIN `money` `m`
				ON `h`.`table`='money' AND `h`.`table_id`=`m`.`id`
				LEFT JOIN `invoice_transfer` `i`
				ON `h`.`table`='invoice_transfer' AND `h`.`table_id`=`i`.`id`
			WHERE ".$cond."
			ORDER BY `h`.`id` DESC
			LIMIT ".$start.",".$v['limit'];
	$q = query($sql);
	$history = array();
	while($r = mysql_fetch_assoc($q))
		$history[$r['id']] = $r;

	$history = _zayavLink($history);
	$history = _dogNomer($history);

	if($v['page'] == 1)
		$send .=
			($v['day'] != TODAY ?
				'<div>'.
					'������� �� <b>'.FullData($v['day'], 1).'</b>:'.
					'<a class="ih-clear">��������</a>'.
				'</div>'
			: '').
			'<table class="_spisok _money">'.
				'<tr><th>��������'.
					'<th>�����'.
					'<th>������'.
					'<th>��������'.
					'<th>����';
	foreach($history as $r) {
		$about = '';
		if($r['zayav_id'])
			$about = $r['zayav_link'].
					 ($r['dogovor_id'] ? '. '.'��������� ����� (������� '.$r['dogovor_nomer'].')' : '').
					 ' ';
		$about .= $r['prim'].' ';
		$worker = $r['worker_id'] ? '<u>'._viewer($r['worker_id'], 'name').'</u> ' : '';
		$expense = $r['expense_id'] ? '<span class="type">'._expense($r['expense_id']).(!trim($about) && !$worker ? '' : ': ').'</span> ' : '';
		if($r['invoice_id'] == $r['invoice_from'])//��������������� ���� ����� - ����������
			$about .= '����������� �� ���� <span class="type">'._invoice($r['invoice_to']).'</span>';
		elseif($r['invoice_id'] == $r['invoice_to'])//��������������� ���� ����� - ����������
			$about .= '����������� �� ����� <span class="type">'._invoice($r['invoice_from']).'</span>';

		$about .=
			($r['income_count'] ?
					' <a class="income-show" val="'.$r['income_ids'].'">'.
						$r['income_count'].' ������'._end($r['income_count'], '', '�', '��').
					'</a>'
			: '');
		$sum = '';
		if($r['sum_prev'] != 0)
			$sum = _sumSpace($r['sum'] - $r['sum_prev']).
				   '<div class="sum-change">('.round($r['sum_prev'], 2).' &raquo; '.round($r['sum'], 2).')</div>';
		elseif($r['sum'] != 0)
			$sum = _sumSpace($r['sum']);
		$send .=
			'<tr><td class="action">'.invoiceHistoryAction($r['action']).
				'<td class="sum">'.$sum.
				'<td class="balans">'._sumSpace($r['balans']).
				'<td>'.$expense.$worker.$about.
				'<td class="dtime">'.FullDataTime($r['dtime_add']);
	}

	if($start + $v['limit'] < $all) {
		$c = $all - $start - $v['limit'];
		$c = $c > $v['limit'] ? $v['limit'] : $c;
		$send .=
			'<tr class="_next" val="'.($v['page'] + 1).'"><td colspan="5">'.
				'<span>�������� ��� '.$c.' �����'._end($c, '�', '�', '��').'</span>';
	}
	if($v['page'] == 1)
		$send .= '</table>';
	return $send;
}//invoice_history_full()
function invoice_history_ostatok($v) {
	$v = array(
		'invoice_id' => intval($v['invoice_id']),
		'year' => !_num(@$v['year']) ? strftime('%Y') : $v['year'],
		'mon' => !_num(@$v['mon']) ? strftime('%m') : ($v['mon'] < 10 ? 0 : '').$v['mon']
	);
	$month = $v['year'].'-'.$v['mon'];
	$send = '<table class="_spisok">'.
		'<tr><th>����'.
			'<th>������ ���'.
			'<th>������'.
			'<th>������'.
			'<th>�������';

	$ass = array();

	// ������� �� ����� ���
	$sql = "SELECT
				DATE_FORMAT(`dtime_add`,'%d') AS `day`,
				`balans`
			FROM `invoice_history`
			WHERE `id` IN (
				SELECT
					MAX(`id`)
				FROM `invoice_history`
				WHERE `invoice_id`=".$v['invoice_id']."
				  AND `dtime_add` LIKE '".$month."%'
				GROUP BY DATE_FORMAT(`dtime_add`,'%d')
				ORDER BY `id`
			)";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ass[intval($r['day'])]['balans'] = round($r['balans'], 2);

	// ����� ��������
	$sql = "SELECT
				DATE_FORMAT(`dtime_add`,'%d') AS `day`,
				SUM(`sum`) AS `sum`
			FROM `invoice_history`
			WHERE `invoice_id`=".$v['invoice_id']."
			  AND `sum`>0
			  AND `dtime_add` LIKE '".$month."%'
			GROUP BY DATE_FORMAT(`dtime_add`,'%d')
			ORDER BY `id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ass[intval($r['day'])]['inc'] = round($r['sum'], 2);

	// ����� ��������
	$sql = "SELECT
				DATE_FORMAT(`dtime_add`,'%d') AS `day`,
				SUM(`sum`) AS `sum`
			FROM `invoice_history`
			WHERE `invoice_id`=".$v['invoice_id']."
			  AND `sum`<0
			  AND `dtime_add` LIKE '".$month."%'
			GROUP BY DATE_FORMAT(`dtime_add`,'%d')
			ORDER BY `id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ass[intval($r['day'])]['dec'] = round($r['sum'], 2);

	$unix = strtotime($month.'-01');
	$prev_month = strftime('%Y-%m', $unix - 86400);

	// ������ �� ��������� ����
	$sql = "SELECT `balans`
			FROM `invoice_history`
			WHERE `invoice_id`=".$v['invoice_id']."
			  AND `dtime_add` LIKE '".$prev_month."%'
			ORDER BY `id` DESC
			LIMIT 1";
	$balans = query_value($sql);

	$balans = $balans ? $balans : 0;
	for($d = 1; $d <= date('t', $unix); $d++) {
		if(strtotime($month.'-'.$d) > TODAY_UNIXTIME)
			break;
		$day = FullData($month.'-'.$d, 1, 1, 1);
		$balans = isset($ass[$d]) ? $ass[$d]['balans'] : $balans;
		$inc = isset($ass[$d]['inc']) ? $ass[$d]['inc'] : 0;
		$dec = isset($ass[$d]['dec']) ? $ass[$d]['dec'] : 0;
		$start = $balans - $inc - $dec;
		$send .= '<tr'.(isset($ass[$d]) ? '' : ' class="emp"').'>'.
			'<td class="ost-data">'.(isset($ass[$d]) ? '<a class="to-day" val="'.$month.'-'.($d < 10 ? 0 : '').$d.'">'.$day.'</a>' : $day).
			'<td class="r">'._sumSpace($start).
			'<td class="ost-inc">'.($inc ? _sumSpace($inc) : '').
			'<td class="ost-dec">'.($dec ? _sumSpace($dec) : '').
			'<td class="ost-balans">'._sumSpace($balans);
	}
	$send .= '</table>';
	return $send;
}//invoice_history_ostatok()
function invoice_history_insert($v) {
	$v = array(
		'action' => $v['action'],
		'table' => empty($v['table']) ? '' : $v['table'],
		'id' => _num(@$v['id']),
		'sum' => empty($v['sum']) ? 0 : $v['sum'],
		'sum_prev' => empty($v['sum_prev']) ? 0 : $v['sum_prev'],
		'invoice_id' => _num(@$v['invoice_id'])
	);
	if($v['table']) {
		$r = query_assoc("SELECT * FROM `".$v['table']."` WHERE `id`=".$v['id']);
		$v['sum'] = abs($r['sum']);
		switch($v['table']) {
			case 'money':
				if($r['confirm'])
					return;
				$v['invoice_id'] = $r['invoice_id'];
				$v['sum'] = invoiceHistoryAction($v['action'], 'znak').$v['sum'];
				break;
			case 'invoice_transfer':
				$v['sum'] = invoiceHistoryAction($v['action'], 'znak').$v['sum'];
				$v['invoice_id'] = $r['invoice_from'];
				invoice_history_insert_sql($r['invoice_to'], $v);
				$v['sum'] *= -1;
				break;
			}
	}
	invoice_history_insert_sql($v['invoice_id'], $v);
}//invoice_history_insert()
function invoice_history_insert_sql($invoice_id, $v) {
	$balans = _invoiceBalans($invoice_id);
	if($balans === false)
		return;
	$sql = "INSERT INTO `invoice_history` (
				`action`,
				`table`,
				`table_id`,
				`invoice_id`,
				`sum`,
				`sum_prev`,
				`balans`,
				`viewer_id_add`
			) VALUES (
				".$v['action'].",
				'".$v['table']."',
				".$v['id'].",
				".$invoice_id.",
				".$v['sum'].",
				".$v['sum_prev'].",
				".$balans.",
				".VIEWER_ID."
			)";
	query($sql);
}//invoice_history_insert_sql()

function income_path($data) {
	$ex = explode(':', $data);
	$d = explode('-', $ex[0]);
	define('YEAR', $d[0]);
	define('MON', @$d[1]);
	define('DAY', @$d[2]);
	$to = '';
	if(!empty($ex[1])) {
		$d = explode('-', $ex[1]);
		$to = ' - '.intval($d[2]).
			($d[1] != MON ? ' '._monthDef($d[1]) : '').
			($d[0] != YEAR ? ' '.$d[0] : '');
	}
	return
	'<a href="'.URL.'&p=report&d=money&d1=income&d2=all">���</a> � '.(YEAR ? '' : '<b>�� �� �����</b>').
	(MON ? '<a href="'.URL.'&p=report&d=money&d1=income&d2=year&year='.YEAR.'">'.YEAR.'</a> � ' : '<b>'.YEAR.'</b>').
	(DAY ? '<a href="'.URL.'&p=report&d=money&d1=income&d2=month&mon='.YEAR.'-'.MON.'">'._monthDef(MON, 1).'</a> � ' : (MON ? '<b>'._monthDef(MON, 1).'</b>' : '')).
	(DAY ? '<b>'.intval(DAY).$to.'</b>' : '');

}//income_path()
function income_all() {
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y') AS `year`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE !`deleted`
			  AND `sum`>0
			  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%Y')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['year']] = '<tr>'.
			'<td><a href="'.URL.'&p=report&d=money&d1=income&d2=year&year='.$r['year'].'">'.$r['year'].'</a>'.
			'<td class="r"><b>'._sumSpace($r['sum']).'</b>';

	$th = '';
	foreach(_invoice() as $invoice_id => $i) {
		$th .= '<th>'.$i['name'];
		foreach($spisok as $y => $r)
			$spisok[$y] .= '<td class="r">';
		$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y') AS `year`,
					   SUM(`sum`) AS `sum`
				FROM `money`
				WHERE !`deleted`
				  AND `sum`>0
				  AND `invoice_id`=".$invoice_id."
				  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
				GROUP BY DATE_FORMAT(`dtime_add`,'%Y')
				ORDER BY `dtime_add` ASC";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$spisok[$r['year']] .= _sumSpace($r['sum']);
	}

	return
	'<div class="headName">����� �������� �� �����</div>'.
	'<table class="_spisok sums">'.
		'<tr><th>���'.
			'<th>�����'.
			$th.
			implode('', $spisok).
	'</table>';
}//income_all()
function income_year($year) {
	$spisok = array();
	for($n = 1; $n <= (strftime('%Y', time()) == $year ? intval(strftime('%m', time())) : 12); $n++)
		$spisok[$n] =
			'<tr><td class="r grey">'._monthDef($n, 1).
				'<td class="r">';
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%m') AS `mon`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE !`deleted`
			  AND `sum`>0
			  AND `dtime_add` LIKE '".$year."%'
			  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%m')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[intval($r['mon'])] =
			'<tr><td class="r"><a href="'.URL.'&p=report&d=money&d1=income&d2=month&mon='.$year.'-'.$r['mon'].'">'._monthDef($r['mon'], 1).'</a>'.
				'<td class="r"><b>'._sumSpace($r['sum']).'</b>';

	$th = '';
	foreach(_invoice() as $invoice_id => $i) {
		$th .= '<th>'.$i['name'];
		foreach($spisok as $y => $r)
			$spisok[$y] .= '<td class="r">';
		$sql = "SELECT DATE_FORMAT(`dtime_add`,'%m') AS `mon`,
					   SUM(`sum`) AS `sum`
				FROM `money`
				WHERE !`deleted`
				  AND `sum`>0
				  AND `dtime_add` LIKE '".$year."%'
				  AND `invoice_id`=".$invoice_id."
				  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
				GROUP BY DATE_FORMAT(`dtime_add`,'%m')
				ORDER BY `dtime_add` ASC";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$spisok[intval($r['mon'])] .= _sumSpace($r['sum']);
	}
	return
	'<div class="headName">����� �������� �� ������� �� '.$year.' ���</div>'.
	'<div class="inc-path">'.income_path($year).'</div>'.
	'<table class="_spisok sums">'.
		'<tr><th>�����'.
			'<th>�����'.
			$th.
			implode('', $spisok).
	'</table>';
}//income_year()
function income_month($mon) {
	$path = income_path($mon);
	$spisok = array();
	for($n = 1; $n <= (strftime('%Y%m', time()) == YEAR.MON ? intval(strftime('%d', time())) : date('t', strtotime($mon.'-01'))); $n++)
		$spisok[$n] =
			'<tr><td class="r grey">'.$n.'.'.MON.'.'.YEAR.
				'<td class="r">';
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%d') AS `day`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE !`deleted`
			  AND `sum`>0
			  AND `dtime_add` LIKE '".$mon."%'".
			  (!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%d')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$sum = 0;
	while($r = mysql_fetch_assoc($q)) {
		$spisok[intval($r['day'])] =
			'<tr><td class="r"><a href="'.URL.'&p=report&d=money&d1=income&day='.$mon.'-'.$r['day'].'">'.intval($r['day']).'.'.MON.'.'.YEAR.'</a>' .
				'<td class="r"><b>'._sumSpace($r['sum']).'</b>';
		$sum += $r['sum'];
	}
	$summ = '<td><b>'._sumSpace($sum).'</b>';//����� � ������� � �������

	$th = '';
	foreach(_invoice() as $invoice_id => $i) {
		$th .= '<th>'.$i['name'];
		foreach ($spisok as $y => $r)
			$spisok[$y] .= '<td class="r">';
		$sql = "SELECT DATE_FORMAT(`dtime_add`,'%d') AS `day`,
					   SUM(`sum`) AS `sum`
				FROM `money`
				WHERE !`deleted`
				  AND `sum`>0
				  AND `dtime_add` LIKE '".$mon."%'
				  AND `invoice_id`=".$invoice_id."
				  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
				GROUP BY DATE_FORMAT(`dtime_add`,'%d')
				ORDER BY `dtime_add` ASC";
		$q = query($sql);
		$sum = 0;
		while ($r = mysql_fetch_assoc($q)) {
			$spisok[intval($r['day'])] .= _sumSpace($r['sum']);
			$sum += $r['sum'];
		}
		$summ .= '<td class="r">'.($sum ? '<b>'._sumSpace($sum).'</b>' : '');
	}
	return
	'<div class="headName">����� �������� �� ���� �� '._monthDef(MON, 1).' '.YEAR.'</div>'.
	'<div class="inc-path">'.$path.'</div>'.
	'<table class="_spisok sums">'.
		'<tr><th>�����'.
			'<th>�����'.
			$th.
			implode('', $spisok).
		'<tr><td class="r"><b>�����:</b>'.
			$summ.
	'</table>';
}//income_month()
function income_day($day) {
	$data = income_spisok(array('day' => $day));
	return
	'<script type="text/javascript">var OPL={from:"income"};</script>'.
	'<div class="headName">������ ��������<a class="add income-add">������ �����</a></div>'.
	'<div class="inc-path">'.income_path($day).'</div>'.
	'<div id="spisok">'.$data['spisok'].'</div>';
}//income_day()
function income_days($month=0) {
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y-%m-%d') AS `day`
			FROM `money`
			WHERE `deleted`=0
			  AND `sum`>0
			  AND `dtime_add` LIKE ('".($month ? $month : strftime('%Y-%m'))."%')
			  ".(!RULES_MONEY ? " AND `viewer_id_add`=".VIEWER_ID : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%d')";
	$q = query($sql);
	$days = array();
	while($r = mysql_fetch_assoc($q))
		$days[$r['day']] = 1;
	return $days;
}//income_days()
function income_right($sel) {
	if(RULES_MONEY)
		$workers = query_selJson("
			SELECT
				DISTINCT `m`.`viewer_id_add`,
				CONCAT(`u`.`first_name`,' ',`u`.`last_name`)
	        FROM `money` `m`,`vk_user` `u`
	        WHERE `m`.`viewer_id_add`=`u`.`viewer_id`
	          AND !`m`.`deleted`
	          AND `m`.`sum`>0");
	return
		_calendarFilter(array(
			'days' => income_days(),
			'func' => 'income_days',
			'sel' => $sel
		)).
		'<div class="findHead">�����</div>'.
		'<input type="hidden" id="filter_invoice_id">'.
	(RULES_MONEY ?
		'<script type="text/javascript">var WORKERS='.$workers.';</script>'.
		'<div class="findHead">������ ���������</div>'.
		'<input type="hidden" id="worker_id">'
	: '').
		_check('deleted', '�������� �������');
}//income_right()

function income_insert($v) {//�������� �������
	$v = array(
		'from' => empty($v['from']) ? '' : $v['from'],
		'invoice_id' => $v['invoice_id'],
		'confirm' => _bool(@$v['confirm']),
		'zayav_id' => _num(@$v['zayav_id']),
		'client_id' => _num(@$v['client_id']),
		'dogovor_id' => _num(@$v['dogovor_id']),
		'sum' => _cena($v['sum']),
		'prim' => _txt(@$v['prim'])
	);
	if($v['zayav_id']) {
		$sql = "SELECT *
				FROM `zayav`
				WHERE !`deleted`
				  AND `id`=".$v['zayav_id'];
		if(!$z = query_assoc($sql))
			return false;
		if($v['client_id'] && $v['client_id'] != $z['client_id'])
			return false;
		$v['client_id'] = $z['client_id'];
	}

	$sql = "INSERT INTO `money` (
				`zayav_id`,
				`client_id`,
				`invoice_id`,
				`confirm`,
				`owner_id`,
				`dogovor_id`,
				`sum`,
				`prim`,
				`viewer_id_add`
			) VALUES (
				".$v['zayav_id'].",
				".$v['client_id'].",
				".$v['invoice_id'].",
				".$v['confirm'].",
				".VIEWER_ID.",
				".$v['dogovor_id'].",
				".$v['sum'].",
				'".addslashes($v['prim'])."',
				".VIEWER_ID."
			)";
	query($sql);
	$insert_id = mysql_insert_id();

	invoice_history_insert(array(
		'action' => 1,
		'table' => 'money',
		'id' => $insert_id
	));
	clientBalansUpdate($v['client_id']);
	$zu = _zayavBalansUpdate($v['zayav_id']);

	_historyInsert(
		10,
		array(
			'zayav_id' => $v['zayav_id'],
			'client_id' => $v['client_id'],
			'dogovor_id' => $v['dogovor_id'],
			'value' => $v['sum'],
			'value1' => $v['prim'],
			'value2' => $v['invoice_id']
		)
	);

	switch($v['from']) {
		case 'client':
			$data = income_spisok(array('client_id'=>$v['client_id'],'limit'=>15));
			return $data['spisok'];
		case 'zayav':
			if($zu['dolg'] >= 0) {
				$sql = "UPDATE `zayav_expense`
						SET `acc`=1,
							`mon`=CURRENT_TIMESTAMP
						WHERE `zayav_id`=".$v['zayav_id']."
						  AND !`acc`
						  AND `worker_id`";
				query($sql);
			}
			return zayav_money($v['zayav_id']);
		default: return $insert_id;
	}
}//income_insert()
function incomeFilter($v) {
	$send = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 30,
		'invoice_id' => _num(@$v['invoice_id']),
		'confirm' => _bool(@$v['confirm']),
		'worker_id' => _num(@$v['worker_id']),
		'deleted' => _bool(@$v['deleted']),
		'owner_id' => !empty($v['owner_id']) && preg_match(REGEXP_NUMERIC, $v['owner_id']) && $v['owner_id'] > 100 ? $v['owner_id'] : 0,
		'client_id' => !empty($v['client_id']) && preg_match(REGEXP_NUMERIC, $v['client_id']) ? $v['client_id'] : 0,
		'zayav_id' => !empty($v['zayav_id']) && preg_match(REGEXP_NUMERIC, $v['zayav_id']) ? $v['zayav_id'] : 0,
		'day' => '',
		'from' => '',
		'to' => '',
		'ids' => !empty($v['ids']) ? $v['ids'] : '',
		'ids_ass' => array()
	);
	$send = _calendarPeriod(@$v['day']) + $send;
	if($send['ids'])
		foreach(explode(',', $send['ids']) as $id)
			$send['ids_ass'][$id] = $id;
	return $send;
}//incomeFilter()
function income_spisok($filter=array()) {
	$filter = incomeFilter($filter);

	$cond = "";
	$deleted = 0;

	if(!RULES_MONEY && !$filter['client_id'])
		$cond .= " AND `viewer_id_add`=".VIEWER_ID;
	if(RULES_MONEY && $filter['worker_id'])
		$cond .= " AND `viewer_id_add`=".$filter['worker_id'];
	if($filter['invoice_id'])
		$cond .= " AND `invoice_id`=".$filter['invoice_id'];
	if($filter['confirm'])
		$cond .= " AND `confirm`";
	if($filter['owner_id'])
		$cond .= " AND `owner_id`=".$filter['owner_id'];
	if($filter['client_id'])
		$cond .= " AND `client_id`=".$filter['client_id'];
	if($filter['zayav_id'])
		$cond .= " AND `zayav_id`=".$filter['zayav_id'];
	if($filter['day'])
		$cond .= " AND `dtime_add` LIKE '".$filter['day']."%'";
	if($filter['from'])
		$cond .= " AND `dtime_add`>='".$filter['from']." 00:00:00' AND `dtime_add`<='".$filter['to']." 23:59:59'";
	if(!$filter['owner_id'] && $filter['ids']) {
		$cond .= " AND `id` IN (".$filter['ids'].")";
		$deleted = 1;
	}
	if(!$deleted && !$filter['deleted'])
		$cond .=" AND !`deleted`";

	$cond = "(`sum`>0 ".$cond. ") OR (`sum`<0 AND `refund` ".$cond. ")";

	$sql = "SELECT
	            COUNT(`id`) AS `all`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE ".$cond."
			LIMIT 1";
	$send = mysql_fetch_assoc(query($sql));
	if(!$send['all'])
		return array(
			'all' => 0,
			'spisok' => '<div class="_empty">�������� ���.</div>'
		);

	$page = $filter['page'];
	$start = ($page - 1) * $filter['limit'];
	$sql = "SELECT *
			FROM `money`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$filter['limit'];
	$q = query($sql);
	$money = array();
	$refund = 0;
	while($r = mysql_fetch_assoc($q)) {
		$money[$r['id']] = $r;
		if($r['refund'])
			$refund += abs($r['sum']);
	}

	$money = _dogNomer($money);
	if(!$filter['zayav_id']) {
		$money = _zayavLink($money);
		if(!$filter['client_id'])
			$money = _clientLink($money);
	}

	$send['spisok'] = '';
	if($page == 1)
		$send['spisok'] =
			'<input type="hidden" id="money_limit" value="'.$filter['limit'].'" />'.
			'<input type="hidden" id="money_client_id" value="'.$filter['client_id'].'" />'.
			'<input type="hidden" id="money_zayav_id" value="'.$filter['zayav_id'].'" />'.
			'<input type="hidden" id="money_deleted" value="'.$filter['deleted'].'" />'.
			'<input type="hidden" id="money_invoice_id" value="'.$filter['invoice_id'].'" />'.
			'<input type="hidden" id="money_owner_id" value="'.$filter['owner_id'].'" />'.
			'<input type="hidden" id="money_worker_id" value="'.$filter['worker_id'].'" />'.
		(!$filter['zayav_id'] ?
			'<div class="_moneysum">'.
				'�������'._end($send['all'], '', '�').
				' <b>'.$send['all'].'</b> ������'._end($send['all'], '', '�', '��').
				' �� ����� <b>'._sumSpace($send['sum']).'</b> ���.'.
				($refund ? '<br />� ������ ��������: <b>'._sumSpace($refund).'</b> ���.' : '').
			'</div>' : '').
			'<table class="_spisok inc _money">'.
		(!$filter['zayav_id'] ?
				'<tr>'.
					($filter['owner_id'] || $filter['confirm'] ? '<th>'._check('money_all') : '').
					'<th>�����'.
					'<th>��������'.
					'<th>����'.
					(!$filter['owner_id'] && !$filter['ids'] && !$filter['confirm'] ? '<th>' : '')
		: '');
	foreach($money as $r)
		$send['spisok'] .= income_unit($r, $filter);
	if($start + $filter['limit'] < $send['all']) {
		$c = $send['all'] - $start - $filter['limit'];
		$c = $c > $filter['limit'] ? $filter['limit'] : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'" id="income_next"><td colspan="5">'.
				'<span>�������� ��� '.$c.' ������'._end($c, '', '�', '��').'</span>';
	}
	if($page == 1)
		$send['spisok'] .= '</table>';
	return $send;
}//income_spisok()
function income_unit($r, $filter=array()) {
	$about = '';
	if($r['dogovor_id'] && !$r['refund'])
		$about .= '��������� ������ '.
			(!$filter['zayav_id'] ? '�� ������ '.$r['zayav_link'].' ' : '').
			'(������� '.$r['dogovor_nomer'].').';
	elseif($r['zayav_id'] && !$filter['zayav_id'])
		$about .= $r['zayav_link'].'. ';
	$about .= $r['prim'];
	if(empty($filter['client_id']) && !$filter['zayav_id'] && $r['client_id'])
		$about .= '<br /><span class="income_client">������: '.$r['client_link'].'<span>';
	if($r['confirm'])
		$about .= '<br /><span class="red">������� �������������</span>';

	$sumTitle = $filter['zayav_id'] ? _tooltip('�����', 5) : '">';
	return
		'<tr val="'.$r['id'].'"'.($r['deleted'] ? ' class="deleted"' : '').'>'.
			(!empty($filter['owner_id']) || !empty($filter['confirm']) ? '<td class="choice">'._check('money_'.$r['id'], '', isset($filter['ids_ass'][$r['id']])) : '').
			'<td class="sum opl'.$sumTitle._sumSpace($r['sum']).
			'<td>'.
				($r['refund'] ? '<span class="red">�������</span>. ' : '').
				'<span class="type">'.
					_invoice($r['invoice_id']).
					(empty($about) ? '' : ':').
				'</span> '.
				$about.
				($r['confirm_dtime'] != '0000-00-00 00:00:00' ? '<div class="confirmed">���������� '.FullDataTime($r['confirm_dtime']).'</div>' : '').
			'<td class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -40).FullDataTime($r['dtime_add']).
		(empty($filter['owner_id']) && empty($filter['ids']) && empty($filter['confirm']) ?
			'<td class="ed"><a href="'.APP_HTML.'/view/cashmemo.php?'.VALUES.'&id='.$r['id'].'" target="_blank" class="img_doc'._tooltip('����������� ���������', -140, 'r').'</a>'.
				((!$r['dogovor_id'] && TODAY == substr($r['dtime_add'], 0, 10) || $r['confirm']) && VIEWER_ID == $r['owner_id'] ?
					'<div class="img_del income-del'._tooltip('������� �����', -95, 'r').'</div>'.
					'<div class="img_rest income-rest'._tooltip('������������ �����', -125, 'r').'</div>'
				: '')
		: '');
}//income_unit()

function expenseFilter($v) {
	$send = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 30,
		'category' => !empty($v['category']) && preg_match(REGEXP_NUMERIC, $v['category']) ? $v['category'] : 0,
		'worker' => !empty($v['worker']) && preg_match(REGEXP_NUMERIC, $v['worker']) ? $v['worker'] : 0,
		'invoice_id' => !empty($v['invoice_id']) && preg_match(REGEXP_NUMERIC, $v['invoice_id']) ? $v['invoice_id'] : 0,
		'year' => !empty($v['year']) && preg_match(REGEXP_NUMERIC, $v['year']) ? $v['year'] : strftime('%Y'),
		'month' => isset($v['month']) ? $v['month'] : intval(strftime('%m')),
		'del' => isset($v['del']) && preg_match(REGEXP_BOOL, $v['del']) ? $v['del'] : 0
	);
	$mon = array();
	if(!empty($send['month']))
		foreach(explode(',', $send['month']) as $r)
			$mon[$r] = 1;
	$send['month'] = $mon;
	return $send;
}//expenseFilter()
function expense_right() {
	$workers = query_selJson("
		SELECT
			DISTINCT `m`.`worker_id`,
			CONCAT(`u`.`first_name`,' ',`u`.`last_name`)
	    FROM `money` `m`,`vk_user` `u`
	    WHERE `m`.`worker_id`=`u`.`viewer_id`
	      AND `m`.`worker_id`
	      AND !`m`.`refund`
	      AND !`m`.`deleted`
	      AND `m`.`sum`<0
	    ORDER BY `u`.`dtime_add`");
	$invoice = array(0=>'����� ����');
	foreach(_invoice() as $id => $r)
		$invoice[$id] = $r['name'];
	return '<script type="text/javascript">var WORKERS='.$workers.';</script>'.
	'<div class="findHead">���������</div>'.
	'<input type="hidden" id="category">'.
	'<div class="findHead">���������</div>'.
	'<input type="hidden" id="worker">'.
	'<div class="findHead">����</div>'.
	_radio('invoice_id', $invoice, 0, 1).
	'<input type="hidden" id="year">'.
	'<div id="monthList">'.expenseMonthSum().'</div>';
}//expense_right()
function expenseMonthSum($v=array()) {
	$filter = expenseFilter($v);
	$sql = "SELECT
				DISTINCT(DATE_FORMAT(`dtime_add`,'%m')) AS `month`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE !`deleted`
			  AND !`refund`
			  AND `sum`<0
			  AND `dtime_add` LIKE '".$filter['year']."%'".
			  ($filter['category'] ? " AND `expense_id`=".$filter['category'] : '').
			  ($filter['worker'] ? " AND `worker_id`=".$filter['worker'] : '').
			  ($filter['invoice_id'] ? " AND `invoice_id`=".$filter['invoice_id'] : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%m')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$res = array();
	while($r = mysql_fetch_assoc($q))
		$res[intval($r['month'])] = abs($r['sum']);
	$send = '';
	for($n = 1; $n <= 12; $n++)
		$send .= _check(
			'c'.$n,
			_monthDef($n).(isset($res[$n]) ? '<span class="sum">'.$res[$n].'</span>' : ''),
			isset($filter['month'][$n]),
			1
		);
	return $send;
}//expenseMonthSum()
function expense() {
	$data = expense_spisok();
	$year = array();
	for($n = 2014; $n <= strftime('%Y'); $n++)
		$year[$n] = $n;

	$invoices_sum = array();
	foreach(_invoice() as $id => $r)
		$invoices_sum[$id] = _invoiceBalans($id);

	return
	'<script type="text/javascript">'.
		'var MON_SPISOK='._selJson(_monthDef(0, 1)).','.
			'YEAR_SPISOK='._selJson($year). ','.
			'ISUM='._assJson($invoices_sum).';'.
	'</script>'.
	'<div class="headName">������ �������� �����������<a class="add">����� ������</a></div>'.
	'<div id="spisok">'.$data['spisok'].'</div>';
}//expense()
function expense_spisok($filter=array()) {
	$filter = expenseFilter($filter);
	$dtime = array();
	foreach($filter['month'] as $mon => $k)
		$dtime[] = "`dtime_add` LIKE '".$filter['year']."-".($mon < 10 ? 0 : '').$mon."%'";
	$cond = "!`deleted`
		AND !`refund`
		AND `sum`<0".
		(!empty($dtime) ? " AND (".implode(' OR ', $dtime).")" : '').
		($filter['category'] ? ' AND `expense_id`='.$filter['category'] : '').
		($filter['worker'] ? " AND `worker_id`=".$filter['worker'] : '').
		($filter['invoice_id'] ? " AND `invoice_id`=".$filter['invoice_id'] : '');


	$sql = "SELECT
				COUNT(`id`) AS `all`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE ".$cond;
	$send = mysql_fetch_assoc(query($sql));
	$send['filter'] = $filter;
	if(!$send['all'])
		return $send + array('spisok' => '<div class="_empty">�������� ���.</div>');

	$all = $send['all'];
	$page = $filter['page'];
	$limit = $filter['limit'];
	$start = ($page - 1) * $limit;

	$send['spisok'] = '';
	if($page == 1) {
		$send['spisok'] =
		'<div class="_moneysum">'.
			'�������'._end($all, '�', '�').' <b>'.$all.'</b> �����'._end($all, '�', '�', '��').
			' �� ����� <b>'.abs($send['sum']).'</b> ���.'.
			(empty($dtime) ? ' �� �� �����.' : '').
		'</div>'.
		'<table class="_spisok _money">'.
			'<tr><th>�����'.
				'<th>��������'.
				'<th>����'.
				'<th>';
	}
	$sql = "SELECT *
			FROM `money`
			WHERE ".$cond."
			ORDER BY `dtime_add` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$rashod = array();
	while($r = mysql_fetch_assoc($q))
		$rashod[$r['id']] = $r;
	$rashod = _viewer($rashod);
	foreach($rashod as $r) {
		$dtimeTitle = _tooltip(viewerAdded($r['viewer_id_add']), -40);
		//if($r['deleted'])
			//$dtimeTitle .= "\n".'������: '.$r['viewer_del']."\n".FullDataTime($r['dtime_del']);
		$send['spisok'] .= '<tr'.($r['deleted'] ? ' class="deleted"' : '').' val="'.$r['id'].'">'.
			'<td class="sum"><b>'._sumSpace(abs($r['sum'])).'</b>'.
			'<td><span class="type">'._invoice($r['invoice_id']).': </span>'.
				($r['expense_id'] ? '<span class="cat">'._expense($r['expense_id']).($r['prim'] || $r['worker_id'] ? ':' : '').'</span> ' : '').
				($r['worker_id'] ? '<u>'._viewer($r['worker_id'], 'name').'</u>' : '').
				($r['prim'] && $r['worker_id'] ? ', ' : '').$r['prim'].
			'<td class="dtime'.$dtimeTitle.FullDataTime($r['dtime_add']).
			'<td class="ed r">'.
				//'<div class="img_edit" title="�������������"></div>'.
				'<div class="img_del'._tooltip('������� ������', -52).'</div>'.
				'<div class="img_rest'._tooltip('������������ ������', -67).'</div>';
	}
	if($start + $limit < $all)
		$send['spisok'] .= '<tr class="_next" val="'.($page + 1).'"><td colspan="4"><span>�������� �����...</span>';
	if($page == 1)
		$send['spisok'] .= '</table>';
	return $send;
}//expense_spisok()

function report_month() {
	$sql = "SELECT SUBSTR(`dtime_add`, 1, 4) AS `year`,
				   SUBSTR(`dtime_add`, 6, 2) AS `mon`
	        FROM `zayav`
	        GROUP BY SUBSTR(`dtime_add`, 1, 7)
	        ORDER BY `dtime_add`";
	$q = query($sql);
	$years = array();
	while($r = mysql_fetch_assoc($q))
		$years[$r['year']][] = $r['mon'];

	$saved = query_ass("SELECT `name`,`link` FROM `attach` WHERE `type`='report'");
	$savedDtime = query_ass("SELECT `name`,`dtime_add` FROM `attach` WHERE `type`='report'");

	$curYear = intval(strftime('%Y'));
	$curMon = strftime('%m');
	if(empty($years[$curYear]) || end($years[$curYear]) != $curMon)
		$years[$curYear][] = $curMon;
	$spisok = '';
	foreach($years as $y => $r) {
		if($y < 2014)
			continue;
		$months = '';
		foreach($r as $mon) {
			$mName = _monthDef($mon, 1);
			$s = isset($saved[$y.'-'.$mon]);
			$dtime = $s ? '<div class="dtime'._tooltip('���� ��������', -20).FullDataTime($savedDtime[$y.'-'.$mon], 1).'</div>' : '';
			if($y == 2014 && $mon == 1)
				$td = '<span class="grey">'.$mName.'</span>';
			elseif($s)
				$td = '<a href="'.$saved[$y.'-'.$mon].'">'.$mName.': ������������� �����</a>';
			else
				$td = '<a href="'.APP_HTML.'/view/report_month.php?'.VALUES.'">'.$mName.': ������� �����</a>';
			$months .= '<tr><td>'.$td.'<td>'.$dtime;
		}
		$spisok .= '<a class="yr">'.$y.'</a>'.
				   '<table class="_spisok'.($curYear != $y ? ' dn' : '').'">'.$months.'</table>';
	}
	return
	'<div id="report_month">'.
		'<div class="headName">������������ ������� �� �����</div>'.
		'<div class="_info">'.
			'������ ������������� ����������� 1-�� ����� ������� ������ � ���������� �������������� (�������������). '.
			'���� ����� ��� �� ����������, ���� ����������� ���������� ������� �����.'.
		'</div>'.
		$spisok.
	'</div>';
}//report_month()

function salary() {
	$year = array();
	for($n = 2014; $n <= strftime('%Y'); $n++)
		$year[] = '{uid:'.$n.',title:'.$n.'}';
	return
		'<div class="headName">���������� �������� �����������</div>'.
		'<div id="spisok">'.salary_spisok().'</div>'.
	(VIEWER_ADMIN ?
		'<script type="text/javascript">var YEARS=['.implode(',', $year).'];</script>'.
		'<table id="rep">'.
			'<tr><td>�����: '.
				'<td><input type="hidden" id="rmon" value="'.intval(strftime('%m')).'" />'.
				'<td><input type="hidden" id="ryear" value="'.intval(strftime('%Y')).'" />'.
				'<td><div class="vkButton"><button>����������� �����</button></div>'.
		'</table>'
	: '');
}//salary()
function salary_spisok() {
	$sql = "SELECT
				`u`.`viewer_id`,
				`u`.`rate`,
				CONCAT(`u`.`first_name`,' ',`u`.`last_name`) AS `name`,
				IFNULL(SUM(`m`.`sum`),0) AS `zp`,
				0 AS `client_id`,
				'' AS `dolg`
			FROM `vk_user` AS `u`
				LEFT JOIN `money` AS `m`
				ON `u`.`viewer_id`=`m`.`worker_id`
					AND !`m`.`deleted`
					AND `m`.`worker_id`
					AND `m`.`sum`<0
			WHERE `u`.`worker`
			  AND `u`.`viewer_id`!=982006
			GROUP BY `u`.`viewer_id`
			ORDER BY `u`.`dtime_add`";
	$q = query($sql);
	$worker = array();
	while($r = mysql_fetch_assoc($q))
		$worker[$r['viewer_id']] = $r;

	//���������� ����
	$sql = "SELECT
 				`v`.`viewer_id`,
				`c`.`id`,
				`c`.`balans`
			FROM `client` AS `c`,
			 	 `vk_user` AS `v`
			WHERE `v`.`viewer_id`=`c`.`worker_id`
			  AND `v`.`viewer_id`!=982006
			  AND `v`.`worker`
			  AND `c`.`balans`<0
			  AND !`c`.`deleted`
			GROUP BY `v`.`viewer_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$worker[$r['viewer_id']]['client_id'] = $r['id'];
		$worker[$r['viewer_id']]['dolg'] = round($r['balans'], 2);
	}

	//���������� � ��������
	$sql = "SELECT
 				`e`.`worker_id`,
				IFNULL(SUM(`e`.`sum`),0) AS `sum`
			FROM `zayav_expense` AS `e`,
			 	 `zayav` AS `z`
			WHERE `e`.`worker_id`!=982006
			  AND `e`.`worker_id`
			  AND `e`.`zayav_id`
			  AND `e`.`acc`
			  AND `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			GROUP BY `e`.`worker_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		if(isset($worker[$r['worker_id']]))
			$worker[$r['worker_id']]['zp'] += $r['sum'];

	//���������� ��� ������
	$sql = "SELECT
 				`u`.`viewer_id`,
				IFNULL(SUM(`e`.`sum`),0) AS `ze`
			FROM `vk_user` AS `u`
				LEFT JOIN `zayav_expense` AS `e`
				ON `u`.`viewer_id`=`e`.`worker_id`
					AND `e`.`worker_id`
					AND !`e`.`zayav_id`
			WHERE `u`.`worker`
			  AND `u`.`viewer_id`!=982006
			GROUP BY `u`.`viewer_id`
			ORDER BY `u`.`dtime_add`";
	$q = query($sql);

	$send = '<table class="_spisok">'.
				'<tr><th>���'.
					'<th>������'.
					'<th>������'.
					'<th>����������<br />����'.
					(VIEWER_ADMIN ? '<th>�����<br />�� �/�<br />'._check('uall') : '');
	while($r = mysql_fetch_assoc($q))
		if(!_viewerRules($r['viewer_id'], 'RULES_NOSALARY')) {
			$w = $worker[$r['viewer_id']];
			$start = _viewer($r['viewer_id'], 'salary_balans_start');
			$balans = $start == -1 ? '' : round($w['zp'] + $r['ze'] + $start, 2);
			if($w['dolg'])
				$w['dolg'] = '<a href="'.URL.'&p=client&d=info&id='.$w['client_id'].'" class="'._tooltip('������� �� ���������� ��������', -90).$w['dolg'].'</a>';
			$send .=
			'<tr><td class="fio"><a href="'.URL.'&p=report&d=salary&id='.$r['viewer_id'].'" class="name">'.$w['name'].'</a>'.
				'<td class="rate">'.($w['rate'] == 0 ? '' : round($w['rate'], 2)).
				'<td class="balans" style="color:#'.($balans < 0 ? 'A00' : '090').'">'.$balans.
				'<td class="dolg">'.$w['dolg'].
				(VIEWER_ADMIN ? '<td class="uch">'._check('u'.$r['viewer_id']) : '');
		}
	$send .= '</table>';
	return $send;
}//salary_spisok()
function salary_monthList($v) {
	$filter = salaryFilter($v);

	$acc = array();
	$zp = array();
	for($n = 1; $n <= 12; $n++) {
		$acc[$n] = 0;
		$zp[$n] = 0;
	}

	//��������� ���� ��������������, ������ ���������� � �� �������
	$sql = "SELECT
	            DISTINCT(DATE_FORMAT(`mon`,'%m')) AS `mon`,
				SUM(`sum`) AS `sum`
			FROM `zayav_expense`
			WHERE `worker_id`=".$filter['worker_id']."
			  AND `mon` LIKE '".$filter['y']."%'
			GROUP BY DATE_FORMAT(`mon`,'%m')";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$acc[intval($r['mon'])] = _cena($r['sum']);

	//��������� ���� ��
	$sql = "SELECT
	            DISTINCT(DATE_FORMAT(`mon`,'%m')) AS `mon`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE !`deleted`
			  AND `worker_id`=".$filter['worker_id']."
			  AND `mon` LIKE '".$filter['y']."%'
			GROUP BY DATE_FORMAT(`mon`,'%m')";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zp[intval($r['mon'])] = abs(round($r['sum'], 2));

	$mon = array();
	foreach(_monthDef(0, 1) as $i => $r)
		$mon[$i] = $r.($acc[$i] || $zp[$i]? '<em>'.$acc[$i].'/'.$zp[$i].'</em>' : '');
	return _radio('salmon', $mon, $filter['m'], 1);
}//salary_monthList()
function salaryFilter($v) {
	$v = array(
		'worker_id' => !empty($v['worker_id']) && preg_match(REGEXP_NUMERIC, $v['worker_id']) ? intval($v['worker_id']) : 0,
		'mon' => !empty($v['mon']) && preg_match(REGEXP_YEARMONTH, $v['mon']) && $v['mon'] != '0000-00' ? $v['mon'] : strftime('%Y-%m'),
		'acc_id' => !empty($v['acc_id']) && preg_match(REGEXP_NUMERIC, $v['acc_id']) ? intval($v['acc_id']) : 0
	);
	$ex = explode('-', $v['mon']);
	$v['y'] = intval($ex[0]);
	$v['m'] = intval($ex[1]);
	$v['month'] = _monthDef($v['m'], 1).' '.$v['y'];
	return $v;
}//salaryFilter()
function salary_worker($v) {
	$filter = salaryFilter($v);
	if(!query_value("SELECT COUNT(*) FROM `vk_user` WHERE `worker` AND `viewer_id`=".$filter['worker_id']))
		return '<h2>���������� �� ����������.</h2>';
	if(_viewerRules($filter['worker_id'], 'RULES_NOSALARY'))
		return '<h2>� ���������� <u>'._viewer($filter['worker_id'], 'name').'</u> �� ����������� ��������.</h2>';
	define('WORKER_OK', true);
	$year = array();
	for($n = 2014; $n <= $filter['y']; $n++)
		$year[$n] = $n;

	$invoices_sum = array();
	foreach(_invoice() as $id => $r)
		$invoices_sum[$id] = _invoiceBalans($id);

	return
		'<script type="text/javascript">'.
			'var WORKER_ID='.$filter['worker_id'].','.
				'MON='.$filter['m'].','.
				'MON_SPISOK='._selJson(_monthDef(0, 1)).','.
				'YEAR='.$filter['y'].','.
				'YEAR_SPISOK='._selJson($year).','.
				'RATE='.round(_viewer($filter['worker_id'], 'rate'), 2).','.
				'RATE_DAY='._viewer($filter['worker_id'], 'rate_day'). ','.
				'ISUM='._assJson($invoices_sum).';'.
		'</script>'.
		'<div class="headName">'._viewer($filter['worker_id'], 'name').': ������� �/� �� <em>'.$filter['month'].'</em>.</div>'.
		'<div id="spisok">'.salary_worker_spisok($filter).'</div>';
}//salary_worker()
function salary_worker_spisok($v) {
	$filter = salaryFilter($v);

	if(!$filter['worker_id'])
		return '������������ id ����������';

	$start = _viewer($filter['worker_id'], 'salary_balans_start');
	if($start != -1) {
		$sMoney = query_value("
			SELECT IFNULL(SUM(`sum`),0)
			FROM `money`
			WHERE `worker_id`=".$filter['worker_id']."
			  AND `sum`<0
			  AND !`deleted`");
		$sExpense = query_value("
			SELECT IFNULL(SUM(`sum`),0)
			FROM `zayav_expense`
			WHERE `mon`!='0000-00-00'
			  AND `worker_id`=".$filter['worker_id']);
		$balans = round($sMoney + $sExpense + $start, 2);
		$balans = '<b style="color:#'.($balans < 0 ? 'A00' : '090').'">'.$balans.'</b> ���.';
	} else
		$balans = '<a class="start-set">����������</a>';

	$client = query_assoc("SELECT * FROM `client` WHERE !`deleted` AND `worker_id`=".$filter['worker_id']);
	$rate = _viewer($filter['worker_id'], 'rate');
	$send =
		'<div class="uhead">'.
			'<h1>'.
				'������: '.($rate != 0 ? '<b>'.round($rate, 2).'</b> ���.<span>('._viewer($filter['worker_id'], 'rate_day').'-� ����� ������)</span>' : '���').
				'<a class="rate-set">�������� ������</a>'.
			'</h1>'.
			'������: '.$balans.
			'<div class="a">'.
				'<a class="up">���������</a> :: '.
				'<a class="down">������ �/�</a> :: '.
				'<a class="deduct">������ �����</a>'.
			'</div>'.
		'</div>'.
	($client ?
		'<div class="_info">'.
		($client['balans'] < 0 ?
			'������������ ���������� ���� � ������� '.
			'<a href="'.URL.'&p=client&d=info&id='.$client['id'].'" class="dolg '._tooltip('������� �� ���������� ��������', -85).
				round($client['balans'], 2).
			'</a> ���.'
			: '��������� �������� � ������� <a href="'.URL.'&p=client&d=info&id='.$client['id'].'">'.$client['fio'].'</a>'
		).
		'</div>'
	: '').
		'<div id="salary-sel">&nbsp;</div>';

	$send .= salary_worker_acc($filter);
	$send .= salary_worker_noacc($filter);
	$send .= salary_worker_zp($filter);
	$send .= salary_worker_list($filter);
	return $send;
}//salary_worker_spisok()
function salary_worker_acc($v) {
	$sql = "(SELECT
				'����������' AS `type`,
				`e`.`id`,
				`e`.`category_id`,
			    `e`.`sum`,
				'' AS `about`,
				`e`.`zayav_id`,
				`e`.`salary_list_id`,
				`e`.`mon`,
				0 AS `days_count`
			FROM `zayav_expense` `e`,
				 `zayav` `z`
			WHERE `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			  AND `e`.`acc`
			  AND `e`.`mon` LIKE '".$v['mon']."%'
			  AND `e`.`worker_id`=".$v['worker_id']."
			  AND `e`.`sum`>0
			GROUP BY `e`.`id`
		) UNION (
			SELECT
				'����������' AS `type`,
				`id`,
				`category_id`,
			    `sum`,
				`txt` AS `about`,
				0 AS `zayav_id`,
				`salary_list_id`,
				`mon`,
				`days_count`
			FROM `zayav_expense`
			WHERE !`zayav_id`
			  AND `worker_id`=".$v['worker_id']."
			  AND `sum`>0
			  AND `mon` LIKE '".$v['mon']."%'
		) UNION (
			SELECT
				'�����' AS `type`,
				`id`,
				0 AS `category_id`,
			    `sum`,
				`txt` AS `about`,
				0 AS `zayav_id`,
				`salary_list_id`,
				`mon`,
				0 AS `days_count`
			FROM `zayav_expense`
			WHERE `worker_id`=".$v['worker_id']."
			  AND `sum`<0
			  AND `mon` LIKE '".$v['mon']."%'
		)
		ORDER BY `mon` DESC,`id` ASC";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '';
	$spisok = array();
	$chechAll = 0;
	while($r = mysql_fetch_assoc($q)) {
		$key = strtotime($r['mon']);
		while(isset($spisok[$key]))
			$key++;
		$spisok[$key] = $r;
		if(!$r['salary_list_id'])
			$chechAll = 1;
	}

	$spisok = _zayavLink($spisok);
	krsort($spisok);

	$send = '<table class="_spisok _money">'.
		'<tr>'.
			($chechAll ? '<th>'._check('salary_all') : '').
			'<th>���'.
			'<th>�����'.
			'<th>��������'.
			'<th>';

	foreach($spisok as $r) {
		$about = $r['zayav_id'] ?
					'<span style="background-color:#'.$r['zayav_status_color'].'">'.$r['zayav_link'].'</span> '.
					'<u>'._zayavExpense($r['category_id']).'</u>'.
					'<tt>�� '.FullData($r['zayav_add'], 1).'</tt>'.
					($r['zayav_dolg'] ? '<span class="z-dolg'._tooltip('���� �� ������', -40).$r['zayav_dolg'].'</span>' : '')
					:
					$r['about'];
		if($r['days_count'])
			$about = '<a class="salary-days" val="'.$r['id'].'">'.$r['days_count'].' �'._end($r['days_count'], '���', '��', '���').'</a>. '.$about;
		$send .=
			'<tr val="'.$r['id'].'" class="'.($r['salary_list_id'] ? 'lost' : '').($v['acc_id'] == $r['id'] ? ' show' : '').'">'.
   ($chechAll ? '<td class="ch">'.(!$r['salary_list_id'] && $r['type'] != '�/�' ? _check('s'.$r['id']) : '') : '').
				'<td class="type">'.$r['type'].
				'<td class="sum">'.round($r['sum'], 2).
				'<td class="about">'.$about.
				'<td class="ed">'.
					(!$r['zayav_id'] &&!$r['salary_list_id'] ? '<div class="img_del ze_del'._tooltip('�������', -29).'</div>' : '');
	}
	$send .= '</table>';
	return $send;
}//salary_worker_acc()
function salary_worker_noacc($v) {
	if($v['mon'] != strftime('%Y-%m'))
		return '';
	$sql = "SELECT
			    `e`.`id`,
			    `e`.`sum`,
				`e`.`zayav_id`
			FROM `zayav_expense` `e`,
				 `zayav` `z`
			WHERE `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			  AND !`e`.`acc`
			  AND `e`.`worker_id`=".$v['worker_id']."
			  AND `e`.`sum`>0
			GROUP BY `e`.`id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '';
	$spisok = array();
	$sum = 0;
	while($r = mysql_fetch_assoc($q)) {
		$spisok[$r['id']] = $r;
		$sum += $r['sum'];
	}
	$spisok = _zayavLink($spisok);
	$send = '<div class="list-head"><b>�� ���������:</b><em>�����: <b>'.$sum.'</b></em></div>'.
		'<table class="_spisok _money">'.
		'<tr>'.
			'<th>���'.
			'<th>�����'.
			'<th>��������';
	foreach($spisok as $r) {
		$about =
			'<span style="background-color:#'.$r['zayav_status_color'].'">'.$r['zayav_link'].'</span>'.
			'<tt>�� '.FullData($r['zayav_add'], 1).'</tt>'.
			($r['zayav_dolg'] ? '<span class="z-dolg'._tooltip('���� �� ������', -40).$r['zayav_dolg'].'</span>' : '');
		$send .=
			'<tr val="'.$r['id'].'" class="noacc">'.
				'<td class="type">����������'.
				'<td class="sum">'.round($r['sum'], 2).
				'<td class="about">'.$about;
	}
	$send .= '</table>';
	return $send;
}//salary_worker_noacc()
function salary_worker_zp($v) {
	$sql = "SELECT *
			FROM `money`
			WHERE !`deleted`
			  AND `worker_id`=".$v['worker_id']."
			  AND `sum`<0
			  AND `mon` LIKE '".$v['mon']."%'
			ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '';
	$zp = '';
	$summa = 0;
	while($r = mysql_fetch_assoc($q)) {
		$sum = abs(round($r['sum'], 2));
		$summa += $sum;
		$zp .= '<tr>'.
			'<td class="sum">'.$sum.
			'<td class="about"><span class="type">'._invoice($r['invoice_id']).(empty($r['prim']) ? '' : ':').'</span> '.$r['prim'].
			'<td class="dtime">'.FullDataTime($r['dtime_add']).
			'<td class="ed"><div val="'.$r['id'].'" class="img_del zp_del'._tooltip('�������', -29).'</div>';
	}
	$send =
		'<div class="zp-head">'.
			'<b>�/� �� '.$v['month'].'</b>:'.
			'<span><a class="down">������ �/�</a> :: �����: <b>'.$summa.'</b> ���.</span>'.
		'</div>'.
		'<table class="_spisok _money">'.
			'<tr><th>�����'.
				'<th>��������'.
				'<th>����'.
				'<th>'.
			$zp.
		'</table>';

	return $send;
}//salary_worker_zp()
function salary_worker_list($v) {
	$sql = "SELECT *
			FROM `salary_list`
			WHERE `worker_id`=".$v['worker_id']."
			  AND `mon` LIKE '".$v['mon']."%'";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '';
	$send =
		'<div class="list-head"><b>����� ������ �/�</b></div>'.
		'<table class="_spisok _money">'.
		'<tr><th>������������'.
			'<th>����� ����������'.
			'<th>���� ��������'.
			'<th>';
			//(VIEWER_ADMIN ? '<th>' : '');
	$n = 1;
	while($r = mysql_fetch_assoc($q))
		$send .=
			'<tr><td class="about"><a class="salary-list" val="'.$r['ids'].'">���� ������ �/� '.($n++).'</a>'.
				'<td class="sum">'.$r['sum'].
				'<td class="dtime">'.FullData($r['dtime_add']).
				'<td class="ed"><div val="'.$r['id'].'" class="img_del list_del'._tooltip('�������', -29).'</div>';
				//(VIEWER_ADMIN ? '<td class="ed"><div val="'.$r['id'].'" class="img_del list_del'._tooltip('�������', -29).'</div>' : '');
	$send .= '</table>';
	return $send;
}//salary_worker_list()
