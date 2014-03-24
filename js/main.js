var hashLoc,
	hashSet = function(hash) {
		if(!hash && !hash.p)
			return;
		hashLoc = hash.p;
		var s = true;
		switch(hash.p) {
			case 'client':
				if(hash.d == 'info')
					hashLoc += '_' + hash.id;
				break;
			case 'zayav':
				if(hash.d == 'info')
					hashLoc += '_' + hash.id;
				else if(hash.d == 'add')
					hashLoc += '_add' + (REGEXP_NUMERIC.test(hash.id) ? '_' + hash.id : '');
				else if(!hash.d)
					s = false;
				break;
			default:
				if(hash.d) {
					hashLoc += '_' + hash.d;
					if(hash.d1)
						hashLoc += '_' + hash.d1;
				}
		}
		if(s)
			VK.callMethod('setLocation', hashLoc);
	},
	pinEnter = function() {
		var send = {
			op:'pin_enter',
			pin: $.trim($('#pin').val())
		};
		if(send.pin && send.pin.length > 2) {
			$('.vkButton').addClass('busy');
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success)
					document.location.href = URL;
				else {
					$('.vkButton').removeClass('busy');
					$('#pin').val('');
					$('.red').html(res.text);
				}
			}, 'json');
		}
	},
	clientAdd = function(callback) {
		var html = '<table class="client-add">' +
				'<tr><td class="label">���:<td><input type="text" id="fio" maxlength="100">' +
				'<tr><td class="label">�������:<td><input type="text" id="telefon" maxlength="100">' +
				'<tr><td class="label">�����:<td><input type="text" id="adres" maxlength="100">' +
				'<tr class="tr_pasp"><td colspan="2"><a>��������� ���������� ������</a>' +
				'<tr class="dn"><td><td><b>���������� ������:</b>' +
				'<tr class="dn"><td class="label">�����:' +
							   '<td><input type="text" id="pasp_seria" maxlength="8">' +
								   '<span class="label">�����:</span><input type="text" id="pasp_nomer" maxlength="10">' +
				'<tr class="dn"><td class="label">��������:<td><input type="text" id="pasp_adres" maxlength="100">' +
				'<tr class="dn"><td class="label">��� �����:<td><input type="text" id="pasp_ovd" maxlength="100">' +
				'<tr class="dn"><td class="label">����� �����:<td><input type="text" id="pasp_data" maxlength="100">' +
			'</table>';
			dialog = _dialog({
				top:60,
				width:380,
				head:'���������� �o���� �������',
				content:html,
				submit:submit
			});
		$('#fio').focus();
		$('#fio,#telefon,#adres').keyEnter(submit);
		$('.tr_pasp a').click(function() {
			$('.tr_pasp').remove();
			$('.client-add .dn').removeClass('dn');
			$('#pasp_seria').focus();
		});
		function submit() {
			var send = {
				op:'client_add',
				fio:$('#fio').val(),
				telefon:$('#telefon').val(),
				adres:$('#adres').val(),
				pasp_seria:$('#pasp_seria').val(),
				pasp_nomer:$('#pasp_nomer').val(),
				pasp_adres:$('#pasp_adres').val(),
				pasp_ovd:$('#pasp_ovd').val(),
				pasp_data:$('#pasp_data').val()
			};
			if(!send.fio) {
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">�� ������� ��� �������.</SPAN>',
					top:-47,
					left:103,
					indent:40,
					show:1,
					remove:1
				});
				$('#fio').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����� ������ �����.');
						if(typeof callback == 'function')
							callback(res);
						else
							document.location.href = URL + '&p=client&d=info&id=' + res.uid;
					} else dialog.abort();
				}, 'json');
			}
		}
	},
	clientFilter = function() {
		var v = {
			op:'client_spisok',
			fast:cFind.inp(),
			dolg:$('#dolg').val(),
			note:$('#note').val(),
			zayav_cat:$('#zayav_cat').val(),
			product_id:$('#product_id').val()
		};
		$('.filter')[v.fast ? 'hide' : 'show']();
		return v;
	},
	clientSpisok = function() {
		var send = clientFilter(),
			result = $('.result');
		if(result.hasClass('busy'))
			return;
		result.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			result.removeClass('busy');
			if(res.success) {
				result.html(res.result);
				$('.left').html(res.spisok);
			}
		}, 'json');
	},

	zayavZamerDtime = function(v) {
		v = $.extend({
			day:'',
			hour:10,
			min:0
		}, v);
		var html =
		'<table><tr>' +
			'<td><INPUT TYPE="hidden" id="zamer_day" value="' + v.day + '" />' +
			'<td><INPUT TYPE="hidden" id="zamer_hour" value="' + v.hour + '" />' +
			'<td> : ' +
			'<td><INPUT TYPE="hidden" id="zamer_min" value="' + v.min + '" />' +
		'</table>';
		$('.zayav-zamer-dtime').html(html);
		$('#zamer_day')._calendar();
		$('#zamer_hour')._select({
			width:40,
			spisok:ZAMER_HOUR
		});
		$('#zamer_min')._select({
			width:40,
			spisok:ZAMER_MIN
		});
		$('#zamer_duration')._select({
			width:100,
			spisok:ZAMER_DURATION
		});

	},
	zayavFindFast = function() {
		var send = {
			op:'zayav_findfast',
			find:$.trim($('#find input').val())
		};
		$('.find-hide')[(send.find ? 'add' : 'remove') + 'Class']('dn');
		if(!send.find) {
			zayavSpisok();
			return;
		}
		$('#mainLinks').addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			$('#mainLinks').removeClass('busy');
			if(res.success) {
				$('#zayav .result').html(res.result);
				$('#zayav #spisok').html(res.spisok);
			}
		}, 'json');
	},
	zayavFilter = function() {
		return {
			op:'zayav_spisok',
			category:$('#zayav').attr('val'),
			product:$('#product_id').val(),
			status:$('#status').val()
		};
	},
	zayavSpisok = function() {
		$('#mainLinks').addClass('busy');
		$.post(AJAX_MAIN, zayavFilter(), function(res) {
			$('#mainLinks').removeClass('busy');
			if(res.success) {
				$('#zayav .result').html(res.result);
				$('#zayav #spisok').html(res.spisok);
			}
		}, 'json');
	},
	dogovorCreate = function(v) {
		var n,
			head = '����������',
			but = '��������� �������',
			cutHead = '������� ���� ��������� ��������',
			cutn, //����� ������� ��� ��� �������� �� �����
			cutd, //������ �������� �������
			cutArr;
		switch(v) {
			default: v = 'create';
			case 'create': break;
			case 'edit':
				head = '��������� ������';
				but = '���������';
				break;
			case 'reneg':
				head = '��������������';
				but = '������������� �������';
				break;
		}
		var html = '<table class="zayav-dogovor">' +
				'<tr><td class="label">��� �������:<td><input type="text" id="fio" value="' + DOG.fio + '" />' +
				'<tr><td class="label">�����:<td><input type="text" id="adres" value="' + DOG.adres + '" />' +
				'<tr><td class="label">�������:' +
					'<td>�����:<input type="text" id="pasp_seria" maxlength="8" value="' + DOG.pasp_seria + '" />' +
						'�����:<input type="text" id="pasp_nomer" maxlength="10" value="' + DOG.pasp_nomer + '" />' +
				'<tr><td><td><span class="l">��������:</span><input type="text" id="pasp_adres" maxlength="100" value="' + DOG.pasp_adres + '" />' +
				'<tr><td><td><span class="l">��� �����:</span><input type="text" id="pasp_ovd" maxlength="100" value="' + DOG.pasp_ovd + '" />' +
				'<tr><td><td><span class="l">����� �����:</span><input type="text" id="pasp_data" maxlength="100" value="' + DOG.pasp_data + '" />' +
				'<tr><td class="label">����� ��������:<td><input type="text" id="nomer" maxlength="6" value="' + DOG.nomer + '" />' +
				'<tr><td class="label">���� ����������:<td><input type="hidden" id="data_create" value="' + (DOG.data_create ? DOG.data_create : '') + '" />' +
				'<tr><td class="label">����� �� ��������:<td><input type="text" id="sum" class="money" maxlength="11" value="' + (DOG.sum ? DOG.sum : '') + '" /> ���.' +
				'<tr><td class="label">��������� �����:<td><input type="text" id="avans" class="money" maxlength="11" value="' + (DOG.avans ? DOG.avans : '') + '" /> ���. <span class="prim">(�� �����������)</span>' +
(v == 'reneg' ? '<tr><td class="label">������� ��������������:<td><input type="text" id="reason" />' : '') +
				'<tr><td colspan="2"><a id="cut"></a>' +
				'<tr><td colspan="2">' +
					'<a id="preview">��������������� �������� ��������</a>' +
					'<form action="' + AJAX_MAIN + '" method="post" id="preview-form" target="_blank"></form>' +
				'</table>',
			dialog = _dialog({
				width:480,
				top:10,
				head:head + ' ��������',
				content:html,
				butSubmit:but,
				submit:submit
			});
		$('#data_create')._calendar({lost:1});
		$('#cut')
			.click(cutCreate)
			.vkHint({
				width:180,
				msg:'������� ���������� ����� ������� �� ������ �� ����� � ������� �� ��� �����������.',
				delayShow:400,
				top:-82,
				left:118
			});
		cutHeadPrint();
		$('#preview').click(function() {
			var send = valuesTest('preview');
			if(send) {
				send.op = 'dogovor_preview';
				var form = '';
				for(var i in send)
					form += '<input type="hidden" name="' + i + '" value="' + send[i] + '">';
				$('#preview-form').html(form).submit();
			}
		});
		$('#reason').autosize();
		function valuesTest(type) {
			var send = {
				id:DOG.id,
				zayav_id:ZAYAV.id,
				fio:$('#fio').val(),
				adres:$('#adres').val(),
				pasp_seria:$('#pasp_seria').val(),
				pasp_nomer:$('#pasp_nomer').val(),
				pasp_adres:$('#pasp_adres').val(),
				pasp_ovd:$('#pasp_ovd').val(),
				pasp_data:$('#pasp_data').val(),
				nomer:$('#nomer').val(),
				data_create:$('#data_create').val(),
				sum:$('#sum').val(),
				avans:$('#avans').val(),
				cut:DOG.cut,
				reason:v == 'reneg' ? $('#reason').val() : ''
			};
			if(!send.fio) err('�� ������� ��� �������', 'fio', type);
			else if(!REGEXP_NUMERIC.test(send.nomer) || send.nomer == 0) err('����������� ������ ����� ��������', 'nomer', type);
			else if(!REGEXP_CENA.test(send.sum) || send.sum == 0) err('����������� ������� ����� �� ��������', 'sum', type);
			else if(send.avans && !REGEXP_CENA.test(send.avans)) err('����������� ������ ��������� �����', 'avans', type);
			else if(v == 'reneg' && !send.reason) err('�� ������� ������� �������������� ��������', 'reason', type);
			else return send;
			return false;
		}
		function cutCreate() {
			var sum = REGEXP_CENA.test($('#sum').val()) ? $('#sum').val() : 0,
				avans = REGEXP_CENA.test($('#avans').val()) ? $('#avans').val() : 0,
				s = sum - avans,
				html =
					'<table class="cut-money">' +
						'<tr><td class="label">�������� �����:<td><u>' + s + '</u> ���.' +
						'<tr id="cut-add"><td><td><a>�������� ����</a>' +
						'<tr><td class="label">�������� �����:<td><b id="cut-itog">' + s + '</b> ���.' +
					'</table>';
			cutd = _dialog({
				head:'�������� ������� �� �����',
				content:html,
				butSubmit:'���������',
				submit:cutSubmit
			});
			cutn = 1;
			cutArr = [];
			if(DOG.cut) {
				var arr = DOG.cut.split(',');
				for(n = 0; n < arr.length; n++) {
					var r = arr[n].split(':');
					cutArr.push([r[0],r[1]]);
					cutAdd();
				}
			} else
				cutAdd();
			cutItog();
			$('#cut-add a').click(cutAdd);
		}
		function cutAdd() {
			var arr = cutArr[cutn - 1],
				html = '<tr><td class="label">' + cutn + '-� �����:' +
						   '<td><input type="text" class="cutsum" id="i' + cutn + '" maxlength="7" value="' + (arr ? arr[0] : '') + '"> ���. ' +
							   '<input type="hidden" id="d' + cutn + '" value="' + (arr ? arr[1] : '') + '">';
			$('#cut-add').before(html);
			if(cutn == 1 && !cutArr[0])
				$('#i1').val($('#cut-itog').html());
			$('#i' + cutn).focus().keyup(cutItog);
			$('#d' + cutn)._calendar();
			cutn++;
		}
		function cutItog() {
			var inp = $('.cutsum'),
				sum = 0,
				val,
				arr = [];
			for(n = 0; n < inp.length; n++) {
				val = $.trim(inp.eq(n).val());
				if(!val || val == 0)
					continue;
				if(!REGEXP_CENA.test(val)) {
					sum = false;
					break;
				}
				sum += val * 1;
				arr.push((val * 1) + ':' + inp.eq(n).next().find('input').val());
			}
			if(sum === false)
				sum = Math.round(sum * 100) / 100;
			$('#cut-itog').html(sum);
			return sum === false ? 'error' : arr;
		}
		function cutSubmit() {
			var cut = cutItog();
			if(cut == 'error') {
				cutd.bottom.vkHint({
					msg:'<span class="red">����������� ��������� ���� �������</span>',
					top:-47,
					left:84,
					indent:50,
					show:1,
					remove:1
				});
				return;
			}
			DOG.cut = cut.join();
			var len = cut.length;
			cutHeadPrint();
			cutd.close();
		}
		function cutHeadPrint() {
			var len = DOG.cut ? DOG.cut.split(',').length : 0;
			$('#cut').html(!len ? cutHead :
				'������' + _end(len, ['�', '�']) + ' ���' + _end(len, ['�', '�']) + ' ��� ' + len + _end(len, ['-��', '-�', '-�']) +
				' ������' + _end(len, ['�', '��']) + '. <u>��������</u><div class="img_del' + _tooltip('�������� ��������', -61) + '</div>');
			if(len)
				$('#cut .img_del').click(function(e) {
					e.stopPropagation();
					DOG.cut = '';
					$('#cut').html(cutHead);
				});
		}
		function err(msg, id, type) {
			dialog.bottom.vkHint({
				msg:'<span class="red">' + msg + '</span>',
				top:type ? -86 : -47,
				left:type ? 173 : 142,
				indent:50,
				show:1,
				remove:1
			});
			if(id)
				$('#' + id).focus();
		}
		function submit() {
			var send = valuesTest();
			if(send) {
				send.op = 'dogovor_' + v;
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������� ��������.');
						document.location.reload();
					} else {
						dialog.abort();
						err(res.text);
					}
				}, 'json');
			}
		}
	},

	remindSpisok = function(day) {
		var y = $('#remind').hasClass('y'),
			cal = $('#remind .right ._calendarFilter'),
			send = {
				op:'remind_spisok',
				day:cal.find('.selected').val(),
				status:$('#status').val()
			};
		if(y)
			$('#remind').removeClass('y');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				$('.remind_spisok').html(res.html);
				if(y)
					cal.html(res.cal);
			}
		}, 'json');
	},

	historyFilter = function() {
		return {
			op:'history_spisok',
			limit:$('#history_limit').val(),
			worker_id:$('#history_worker_id').val(),
			cat_id:$('#history_cat_id').val(),
			client_id:$('#history_client_id').val(),
			zayav_id:$('#history_zayav_id').val()
		};
	},
	historySpisok = function(v, id) {
		var send = historyFilter();
		send[id] = v;
		$('#mainLinks').addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			$('#mainLinks').removeClass('busy');
			if(res.success)
				$('.left').html(res.html);
		}, 'json');
	},
	incomeSpisok = function() {
		var send = {
			op:'income_spisok',
			day:$('.selected').val(),
			income_id:$('#income_id').val(),
			worker_id:$('#worker_id').val()
		};
		$('.inc-path').addClass('_busy');
		$.post(AJAX_MAIN, send, function(res) {
			$('.inc-path').removeClass('_busy');
			if(res.success) {
				$('.inc-path').html(res.path);
				$('#spisok').html(res.html);
			}
		}, 'json');
	},
	incomeChoiceSum = function() {
		var n,
			mc = $('._money .choice'),
			c = 0,
			sum = 0,
			all = true,
			ids = [];
		for(n = 0; n < mc.length; n++) {
			var eq = mc.eq(n);
			if(eq.find('input').val() == 1) {
				c++;
				sum += eq.parent().find('.sum').html().replace(' ', '') * 1;
				ids.push(eq.parent().attr('val'));
			} else
				all = false;
		}
		$('.income_choice_sum').html(c ? '������' + _end(c, ['', '�']) + ' <b>' + c + '</b> ������' + _end(c, ['', '�', '��']) + ' �� ����� <b>' + sum + '</b> ���.' : '');
		$('#money_all')._check(all);
		return {
			count:c,
			ids:ids.join(),
			sum:sum
		};
	},
	expenseFilter = function() {
		var arr = [],
			inp = $('#monthList input');
		for(var n = 1; n <= 12; n++)
			if(inp.eq(n - 1).val() == 1)
				arr.push(n);
		return {
			op:'expense_spisok',
			category:$('#category').val(),
			worker:$('#worker').val(),
			invoice_id:$('#invoice_id').val(),
			year:$('#year').val(),
			month:arr.join()
		};
	},
	expenseSpisok = function() {
		$('#mainLinks').addClass('busy');
		$.post(AJAX_MAIN, expenseFilter(), function(res) {
			$('#mainLinks').removeClass('busy');
			if(res.success) {
				$('#spisok').html(res.html);
				$('#monthList').html(res.mon);
			}
		}, 'json');
	},
	salaryCheck = function() {
		var check = $('._check'),
			n,
			sp,
			tr,
			html = '',
			count = 0,
			sum = 0,
			ids = [];
		for(n = 0; n < check.length; n++) {
			sp = check.eq(n);
			if(sp.find('input').val() == 0 || sp.attr('id') == 'salary_all_check')
				continue;
			count++;
			tr = sp.parent().parent();
			ids.push(tr.attr('val'));
			sum += tr.find('.sum').html() * 1;
		}
		if(count)
			html = '������' + _end(count, ['�', '�']) + ' <b>' + count + '</b> �����' + _end(count, ['�', '�', '��']) +
				' �� ����� <b>' + sum + '</b> ���.' +
				'<a class="salary-list" val="' + ids.join() + '">����������� ���� ������ �/�</a>';
		$('#salary-sel').html(html);
	},
	salarySpisok = function() {
		if($('.headName').hasClass('_busy'))
			return;
		MON = $('#salmon').val() * 1;
		YEAR = $('#year').val();
		var send = {
			op:'salary_spisok',
			worker_id:WORKER_ID,
			year:YEAR,
			mon:MON
		};
		$('.headName').addClass('_busy');
		$.post(AJAX_MAIN, send, function (res) {
			$('.headName').removeClass('_busy');
			if(res.success) {
				$('.headName em').html(MONTH_DEF[MON] + ' ' + YEAR);
				$('#spisok').html(res.html);
				$('#monthList').html(res.month);
			}
		}, 'json');
	};

$.fn.clientSel = function(o) {
	var t = $(this);
	o = $.extend({
		width:270,
		add:null,
		client_id:t.val() || 0,
		func:function() {}
	}, o);

	if(o.add)
		o.add = function() {
			clientAdd(function(res) {
				var arr = [];
				arr.push(res);
				t._select(arr);
				t._select(res.uid);
			});
		};

	t._select({
		width:o.width,
		title0:'������� ������� ������ �������...',
		spisok:[],
		write:1,
		nofind:'�������� �� �������',
		func:o.func,
		funcAdd:o.add,
		funcKeyup:clientsGet
	});
	clientsGet();

	function clientsGet(val) {
		var send = {
			op:'client_sel',
			val:val || '',
			client_id:o.client_id
		};
		t._select('process');
		$.post(AJAX_MAIN, send, function(res) {
			t._select('cancel');
			if(res.success) {
				t._select(res.spisok);
				if(o.client_id) {
					t._select(o.client_id);
					o.client_id = 0;
				}
			}
		}, 'json');
	}
	return t;
};
$.fn.productList = function(o) {
	var t = $(this),
		id = t.attr('id'),
		num = 1,
		n;

	if(typeof o == 'string') {
		if(o == 'get') {
			var units = t.find('.ptab'),
				send = [];
			for(n = 0; n < units.length; n++) {
				var u = units.eq(n),
					attr = id + u.attr('val'),
					pr = $('#' + attr + 'id').val(),
					prsub = $('#' + attr + 'subid').val(),
					count = $('#' + attr + 'count').val();
				if(pr == 0)
					continue;
				if(!REGEXP_NUMERIC.test(count) || count == 0)
					return 'count_error';
				send.push(pr + ':' + prsub + ':' + count);
			}
			return send.length == 0 ? false : send.join();
		}
	}

	t.html('<div class="_product-list"><a class="add">�������� ����</a></div>');
	var add = t.find('.add');
	add.click(itemAdd);

	if(typeof o == 'object')
		for(n = 0; n < o.length; n++)
			itemAdd(o[n])
	else
		itemAdd([]);

	function itemAdd(v) {
		var attr = id + num,
			attr_id = attr + 'id',
			attr_subid = attr + 'subid',
			attr_count = attr + 'count',
			html = '<table id="ptab'+ num + '" class="ptab" val="'+ num + '"><tr>' +
					'<td class="td"><input type="hidden" id="' + attr_id + '" value="' + (v[0] || 0) + '" />' +
								   '<input type="hidden" id="' + attr_subid + '" value="' + (v[1] || 0) + '" />' +
					'<td class="td"><input type="text" id="' + attr_count + '" value="' + (v[2] || '') + '" class="count" maxlength="3" /> ��.' +
									(num > 1 ? '<div class="img_del"></div>' : '') +
				'</table>';
		add.before(html);
		var ptab = $('#ptab' + num);
		ptab.find('.img_del').click(function() {
			ptab.remove();
		});
		$('#' + attr_id)._select({
			width:119,
			title0:'�� �������',
			spisok:PRODUCT_SPISOK,
			func:function(id) {
				$('#' + attr_subid)
					._select('remove')
					.val(0);
				if(id > 0 && PRODUCT_SUB_SPISOK[id])
					subSel(id, attr_subid, attr_count);
				$('#' + attr_count).val(id > 0 ? 1 : '').focus();
			}
		});
		subSel(v[0] || 0, attr_subid, attr_count);
		num++;
	}
	function subSel(id, attr_subid, attr_count) {
		if(id == 0 || !PRODUCT_SUB_SPISOK[id])
			return;
		$('#' + attr_subid)._select({
			width:120,
			title0:'������ �� ������',
			spisok:PRODUCT_SUB_SPISOK[id],
			func:function() {
				$('#' + attr_count).focus();
			}
		});
	}
	return t;
};
$.fn.zayavRashod = function(o) {
	var t = $(this),
		id = t.attr('id'),
		num = 1,
		n;

	if(typeof o == 'string') {
		if(o == 'get') {
			var units = t.find('.ptab'),
				send = [];
			for(n = 0; n < units.length; n++) {
				var u = units.eq(n),
					attr = id + u.attr('val'),
					cat_id = $('#' + attr + 'cat').val(),
					worker = $('#' + attr + 'worker').val(),
					sum = u.find('.zrsum').val(),
					dop = '';
				if(cat_id == 0)
					continue;
				if(!REGEXP_NUMERIC.test(sum) || sum == 0)
					return 'sum_error';
				if(ZAYAVEXPENSE_TXT[cat_id])
					dop = u.find('.zrtxt').val();
				else if(ZAYAVEXPENSE_WORKER[cat_id])
					dop = $('#' + attr + 'worker').val();
				send.push(cat_id + ':' + dop + ':' + sum);
			}
			return send.join();
		}
	}

	t.html('<div class="_zayav-rashod"></div>');
	var zr = t.find('._zayav-rashod');

	if(typeof o == 'object')
		for(n = 0; n < o.length; n++)
			itemAdd(o[n])

	itemAdd([]);

	function itemAdd(v) {
		var attr = id + num,
			attr_cat = attr + 'cat',
			attr_worker = attr + 'worker',
			html = '<table id="ptab'+ num + '" class="ptab" val="' + num + '"><tr>' +
						'<td><input type="hidden" id="' + attr_cat + '" value="' + (v[0] || 0) + '" />' +
						'<td class="tddop">' +
							(v[0] && ZAYAVEXPENSE_TXT[v[0]] ? '<input type="text" class="zrtxt" placeholder="�������� �� �������" tabindex="' + (num * 10 - 1) + '" value="' + v[1] + '" />' : '') +
							(v[0] && ZAYAVEXPENSE_WORKER[v[0]] ? '<input type="hidden" id="' + attr_worker + '" value="' + v[1] + '" />' : '') +
						'<td class="tdsum' + (v[0] ? '' : ' dn') + '"><input type="text" class="zrsum" maxlength="6" tabindex="' + (num * 10) + '" value="' + (v[2] || '') + '" />���.' +
					'</table>';
		zr.append(html);
		var ptab = $('#ptab' + num),
			tddop = ptab.find('.tddop'),
			zrsum = ptab.find('.zrsum');
		if(v[0] && ZAYAVEXPENSE_WORKER[v[0]])
			$('#' + attr_worker)._select({
				width:150,
				title0:'���������',
				spisok:WORKER_SPISOK,
				func:function() {
					zrsum.focus();
				}
			});
		$('#' + attr_cat)._select({
			width:120,
			title0:'���������',
			spisok:ZAYAVEXPENSE_SPISOK,
			func:function(id) {
				ptab.find('.tdsum')[(id > 0 ? 'remove' : 'add') + 'Class']('dn');
				if(ZAYAVEXPENSE_TXT[id]) {
					tddop.html('<input type="text" class="zrtxt" placeholder="�������� �� �������" tabindex="' + (num * 10 - 11) + '" />');
					tddop.find('.zrtxt').focus();
				} else if(ZAYAVEXPENSE_WORKER[id]) {
					tddop.html('<input type="hidden" id="' + attr_worker + '" />');
					$('#' + attr_worker)._select({
						width:150,
						title0:'���������',
						spisok:WORKER_SPISOK,
						func:function() {
							zrsum.focus();
						}
					});
					zrsum.focus();
				} else {
					tddop.html('');
					zrsum.focus();
				}
				zrsum.val('');
				if(id > 0 && !ptab.next().hasClass('ptab'))
					itemAdd([]);
			}
		});
		num++;
	}
	return t;
};

$(document)
	.on('change', '._attach input', function() {
		setCookie('_attached', 0);
		var t = $(this), att = t;
		while(!att.hasClass('_attach'))
			att = att.parent();
		var form = att.find('form'),
			f = att.find('.form'),
			timer = setInterval(start, 500);
		f.addClass('_busy');
		form.submit();
		function start() {
			var c = getCookie('_attached');
			if(c > 0)
				clearInterval(timer);
			else return;
			if(c == 1) {
				var send = {
					op:'attach_get',
					type:form.find('.type').val(),
					zayav_id:form.find('.zayav_id').val()
				};
				$.post(AJAX_MAIN, send, function(res) {
					f.removeClass('_busy');
					if(res.success) {
						att.find('.files').html(res.files);
						form.html(res.form);
					}
				}, 'json');
				return;
			}
			f.removeClass('_busy');
			f.next('.red').remove();
			f.after('<div class="red">������������ ����.</div>');
			f.next('.red').fadeOut(4000);
		}
	})
	.on('click', '._attach .img_minidel', function() {
		var t = $(this),
			send = {
				op:'attach_del',
				id:t.attr('val')
			};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				t.prev().remove();
				t.remove();
			}
		}, 'json');
	})

	.on('click', '.accrual-del', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		if(t.hasClass('deleting'))
			return;
		t.addClass('deleting');
		var send = {
			op:'accrual_del',
			id:t.attr('val')
		};
		$.post(AJAX_MAIN, send, function(res) {
			t.removeClass('deleting');
			if(res.success) {
				t.after('<tr class="deleted" val="' + send.id + '">' +
					'<td colspan="4"><div>���������� �������. <a class="accrual-rest">������������</a></div>');
				t.addClass('dn');
			}
		}, 'json');
	})
	.on('click', '.accrual-rest', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var send = {
				op:'accrual_rest',
				id:t.attr('val')
			},
			div = t.find('div');
		if(div.hasClass('_busy'))
			return;
		div.addClass('_busy');
		$.post(AJAX_MAIN, send, function(res) {
			div.removeClass('busy');
			if(res.success) {
				t.prev().removeClass('dn');
				t.remove();
			}
		}, 'json');
	})

	.on('click', '#client ._next', function() {
		var t = $(this),
			send = clientFilter();
		if(t.hasClass('busy'))
			return;
		send.page = t.attr('val');
		t.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				t.after(res.spisok).remove();
			else
				t.removeClass('busy');
		}, 'json');
	})

	.on('click', '.zayav_add', function() {
		var html =
			'<div class="zayav-add">' +
				'<div class="item zakaz_add"><b>�����</b>����� ����� ��� ������� ������� ��� ���������.<br />��� ������������� � ������� ����� ����� ����� ��������� � ���������.</div>' +
				'<div class="item zamer_add"><b>�����</b>����� ������ �� �����. ����������� ���� � ����� ������. ������ ������������� �������� � �����������. �������� ����� ����������� �� ���������� ��������.</div>' +
				'<div class="item set_add"><b>���������</b>����� ������ �� ��������� �������.' +
			'</div>',
			dialog = _dialog({
				width:370,
				top:30,
				head:'�������� ��������� ������',
				content:html,
				butSubmit:''
			});
	})
	.on('click', '.zayav_unit', function() {
		document.location.href = URL + '&p=zayav&d=info&id=' + $(this).attr('val');
	})
	.on('click', '#zayav ._next', function() {
		var next = $(this);
		if(next.hasClass('busy'))
			return;
		var send = zayavFilter();
		send.page = next.attr('val');
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				next.after(res.spisok).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#zayav .filter_clear', function() {
		$('.find-hide').removeClass('dn');
		$('#find')._search('clear');
		$('#product_id')._select(0);
		zayavSpisok();
	})

	.on('click', '.zakaz_add', function() {
		if(!window.CLIENT)
			CLIENT = {
				id:0,
				fio:'',
				adres:''
			};
		var html =
				'<table class="zayav-add">' +
					'<tr><td class="label">������:' +
						'<td><INPUT type="hidden" id="client_id" value="' + CLIENT.id + '">' +
							'<b>' + CLIENT.fio + '</b>' +
					'<tr><td class="label topi">�������:<td id="product">' +
					'<tr><td><td><input type="text" id="zakaz_txt" placeholder="���� ������� ���������� ������ �������.." maxlength="300">' +
					'<tr><td class="label top">�������:	<td><textarea id="comm"></textarea>' +
					'</table>',
			dialog = _dialog({
				width:550,
				top:30,
				head:'�������� ������ ������',
				content:html,
				submit:submit
			});
		if(!CLIENT.id)
			$('#client_id').clientSel({add:1});
		$('#product').productList();
		$('#comm').autosize();
		function submit() {
			var msg,
				send = {
					op:'zakaz_add',
					client_id:$('#client_id').val(),
					product:$('#product').productList('get'),
					zakaz_txt:$('#zakaz_txt').val(),
					comm:$('#comm').val()
				};
			if(send.client_id == 0) msg = '�� ������ ������';
			else if(!send.product && !send.zakaz_txt) msg = '���������� ������� ������� ��� ������� ����� �������';
			else if(send.product == 'count_error') msg = '����������� ������� ���������� �������';
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ �������');
						location.href = URL + '&p=zayav&d=info&id=' + res.id;
					} else
						dialog.abort();
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					top:-48,
					left:171,
					indent:50,
					show:1,
					remove:1
				});
		}
	})
	.on('click', '.zakaz_status', function() {
		var t = $(this),
			html =
				'<div class="zayav-status">' +
					(ZAYAV.status != 1 ?
						'<div class="st c1" val="1">' +
							'����� ������� ����������' +
							'<div class="about">������������� ������ �� ������.</div>' +
						'</div>'
					: '') +
						'<div class="st c2" val="2">' +
							'����� ��������' +
							'<div class="about">��� ���������� �����������, ������� �������� �������.</div>' +
							'<div class="label">�������� ���� ���������� ������:</div>' +
							'<input type="hidden" id="day" value="' + (ZAYAV.status_day || '') + '">' +
						'</div>' +
					(ZAYAV.status != 3 ?
						'<div class="st c3" val="3">' +
							'����� ������' +
							'<div class="about">������ ������ �� �����-���� �������.</div>' +
						'</div>'
					: '') +
				'</div>',
			dialog = _dialog({
				top:30,
				width:300,
				head:'��������� ������� ������',
				content:html,
				butSubmit:'',
				butCancel:'�������'
			});
		$('#day')._calendar({lost:1});
		$('.st').click(function() {
			var	t = $(this),
				send = {
					op:'zakaz_status',
					zayav_id:ZAYAV.id,
					status:t.attr('val'),
					day:$('#day').val() || ''
				},
				p = t.parent();
			if(p.hasClass('busy'))
				return;
			p.addClass('busy');
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('������ ��������!');
					document.location.reload();
				} else
					p.removeClass('busy');
			}, 'json');
		});
	})

	.on('click', '.zamer_table', function() {
		var dialog = _dialog({
				width:600,
				top:10,
				head:'������� �������',
				load:1,
				butSubmit:'',
				butCancel:'�������'
			}),
			send = {
				op:'zamer_table_get',
				val:$(this).attr('val') || 0
			};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				dialog.content.html(res.html);
			} else
				dialog.loadError();
		}, 'json');
	})
	.on('click', '#zamer-table .ztu', function() {
		document.location.href = URL + '&p=zayav&d=info&id=' + $(this).attr('val');
	})
	.on('click', '#zamer-table .mon a', function() {
		var t = $(this),
			p = t.parent(),
			send = {
				op:'zamer_table_get',
				mon:t.attr('val')
			};
		if(p.hasClass('_busy'))
			return;
		p.addClass('_busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				$('#zamer-table').parent().html(res.html);
			else
				p.removeClass('_busy');
		}, 'json');
	})
	.on('click', '.zamer_add', function() {
		if(!window.CLIENT)
			CLIENT = {
				id:0,
				fio:'',
				adres:''
			};
		var HOMEADRES = CLIENT.adres,
			html =
			'<table class="zayav-add">' +
				'<tr><td class="label">������:' +
					'<td><INPUT type="hidden" id="client_id" value="' + CLIENT.id + '">' +
						'<b>' + CLIENT.fio + '</b>' +
				'<tr><td class="label topi">�������:<td id="product">' +
				'<tr><td class="label">����� ���������� ������:' +
					'<td><INPUT type="text" id="adres" maxlength="100" />' +
						'<INPUT type="hidden" id="homeadres" />' +
				'<tr><td class="label">���� � ����� ������:<td class="zayav-zamer-dtime">' +
				'<tr><td class="label">������������ ������:<td><INPUT TYPE="hidden" id="zamer_duration" value="30" />' +
				'<tr><td class="label top">�������:	<td><textarea id="comm"></textarea>' +
			'</table>',
			dialog = _dialog({
				width:550,
				top:30,
				head:'�������� ����� ������ �� �����',
				content:html,
				submit:submit
			});
		if(!CLIENT.id)
			$('#client_id').clientSel({
				add:1,
				func:function(uid, id, item) {
					HOMEADRES = uid ? item.adres : '';
					if($('#homeadres').val() == 1)
						$('#adres').val(HOMEADRES);
				}
			});
		$('#product').productList();
		$('#homeadres')._check({
			func:function() {
				$('#adres').val(HOMEADRES);
			}
		});
		$('#homeadres_check').vkHint({
			msg:'��������� � ������� ����������',
			top:-75,
			left:193,
			indent:60,
			delayShow:700
		});
		zayavZamerDtime({});
		$('#comm').autosize();
		function submit() {
			var send = {
				op:'zamer_add',
				client_id:$('#client_id').val(),
				product:$('#product').productList('get'),
				adres:$('#adres').val(),
				zamer_day:$('#zamer_day').val(),
				zamer_hour:$('#zamer_hour').val(),
				zamer_min:$('#zamer_min').val(),
				zamer_duration:$('#zamer_duration').val(),
				comm:$('#comm').val()
			};
			if(send.client_id == 0) err('�� ������ ������');
			else if(!send.product) err('�� ������� �������');
			else if(send.product == 'count_error') err('����������� ������� ���������� �������');
			else if(!send.adres) err('�� ������ �����');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ �������');
						location.href = URL + '&p=zayav&d=info&id=' + res.id;
					} else {
						dialog.abort();
						err(res.text);
					}
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				top:-48,
				left:185,
				indent:40,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '.zamer_status', function() {
		var t = $(this),
			id = typeof ZAYAV != 'undefined' ? ZAYAV.id : t.attr('val'),
			dialog = _dialog({
				width:400,
				top:30,
				head:'��������� ������� ������',
				load:1,
				butSubmit:'',
				butCancel:'�������'
			});
		if(typeof ZAYAV == 'undefined')
			$.post(AJAX_MAIN, {op:'zamer_info_get',zayav_id:id}, function(res) {
				if(res.success)
					info_get(res);
				else
					dialog.loadError();
			}, 'json');
		else
			info_get(ZAYAV);
		function info_get(res) {
			var html =
				'<div class="zayav-status">' +
					'<div class="st c1" val="1">' +
						'������� ����� �����' +
						'<div class="about">����� ����� �������� �� ������ �����.</div>' +
					'</div>' +
					'<div class="st c2" val="2">' +
						'����� ��������' +
						'<div class="about">����� �������� �������. ������ ����� ���������� �� ���������� ��������.</div>' +
					'</div>' +
					(res.status != 3 ?
						'<div class="st c3" val="3">' +
							'������' +
							'<div class="about">������ ������ �� �����-���� �������.</div>' +
						'</div>'
					: '') +
					'<table class="zstab">' +
						'<tr><td class="label">����� �����:<td class="zayav-zamer-dtime">' +
						'<tr><td class="label">������������:' +
							'<td><INPUT TYPE="hidden" id="zamer_duration" value="' + res.dur + '" />' +
								'<a class="zamer_table" val="' + id + '">������� �������</a>' +
						'<tr><td><td><div class="vkButton"><button>���������</button></div>' +
					'</table>' +
				'</div>';
			dialog.content.html(html);
			zayavZamerDtime(res);
			$('.st').click(function() {
				var	t = $(this),
					v = t.attr('val');
				if(v == 1) {
					$('.st').hide();
					$('.zstab').show();
				} else
					submit(v);
			});
			$('.zayav-status .vkButton').click(function() {
				var	t = $(this);
				t.addClass('busy');
				submit(1);
			});
		}
		function submit(status) {
			var send = {
				op:'zamer_status',
				zayav_id:id,
				status:status,
				zamer_day:$('#zamer_day').val(),
				zamer_hour:$('#zamer_hour').val(),
				zamer_min:$('#zamer_min').val(),
				zamer_duration:$('#zamer_duration').val()
			};
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('������ ��������!');
					document.location.reload();
				} else
					$('.zayav-status .vkButton')
						.removeClass('busy')
						.vkHint({
							msg:'<SPAN class="red">' + res.text + '</SPAN>',
							top:-58,
							left:-5,
							indent:40,
							remove:1,
							show:1
						});
			}, 'json');
		}
	})

	.on('click', '.set_add', function() {
		if(!window.CLIENT)
			CLIENT = {
				id:0,
				fio:'',
				adres:''
			};
		var HOMEADRES = CLIENT.adres,
			html =
				'<table class="zayav-add">' +
					'<tr><td class="label">������:' +
						'<td><INPUT type="hidden" id="client_id" value="' + CLIENT.id + '">' +
						'<b>' + CLIENT.fio + '</b>' +
					'<tr><td class="label topi">�������:<td id="product">' +
					'<tr><td class="label">����� ���������:' +
						'<td><INPUT type="text" id="adres" maxlength="100" />' +
							'<INPUT type="hidden" id="homeadres" />' +
					'<tr><td class="label top">�������:	<td><textarea id="comm"></textarea>' +
				'</table>',
			dialog = _dialog({
				width:550,
				top:30,
				head:'�������� ����� ������ �� ���������',
				content:html,
				submit:submit
			});
		if(!CLIENT.id)
			$('#client_id').clientSel({
				add:1,
				func:function(uid, id, item) {
					HOMEADRES = uid ? item.adres : '';
					if($('#homeadres').val() == 1)
						$('#adres').val(HOMEADRES);
				}
			});
		$('#product').productList();
		$('#homeadres')._check({
			func:function() {
				$('#adres').val(HOMEADRES);
			}
		});
		$('#homeadres_check').vkHint({
			msg:'��������� � ������� ����������',
			top:-75,
			left:193,
			indent:60,
			delayShow:700
		});
		$('#comm').autosize();
		function submit() {
			var msg,
				send = {
					op:'set_add',
					client_id:$('#client_id').val(),
					product:$('#product').productList('get'),
					adres:$('#adres').val(),
					comm:$('#comm').val()
				};
			if(send.client_id == 0) msg = '�� ������ ������';
			else if(!send.product) msg = '�� ������� �������';
			else if(send.product == 'count_error') msg = '����������� ������� ���������� �������';
			else if(!send.adres) msg = '�� ������ �����';
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ �������');
						location.href = URL + '&p=zayav&d=info&id=' + res.id;
					} else
						dialog.abort();
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					top:-48,
					left:171,
					indent:50,
					show:1,
					remove:1
				});
		}
	})
	.on('click', '.set_status', function() {
		var t = $(this),
			html =
				'<div class="zayav-status">' +
					(ZAYAV.status != 1 ?
						'<div class="st c1" val="1">' +
							'������� ���������' +
							'<div class="about">������������� ������ �� ������.</div>' +
						'</div>'
					: '') +
						'<div class="st c2" val="2">' +
							'��������� ���������' +
							'<div class="about">����������� ��������� ���� �������. �� �������� ��������� ������� �� ������ � ��������� ����������.</div>' +
							'<div class="label">�������� ���� ���������� ���������:</div>' +
							'<input type="hidden" id="day" value="' + (ZAYAV.status_day || '') + '">' +
						'</div>' +
					(ZAYAV.status != 3 ?
						'<div class="st c3" val="3">' +
							'������ ��������' +
							'<div class="about">������ ������ �� �����-���� �������.</div>' +
						'</div>'
					: '') +
					'</div>',

			dialog = _dialog({
				top:30,
				width:360,
				head:'��������� ������� ���������',
				content:html,
				butSubmit:'',
				butCancel:'�������'
			});
		$('#day')._calendar({lost:1});
		$('.st').click(function() {
			var	t = $(this),
				send = {
					op:'set_status',
					zayav_id:ZAYAV.id,
					status:t.attr('val'),
					day:$('#day').val() || ''
				},
				p = t.parent();
			if(p.hasClass('busy'))
				return;
			p.addClass('busy');
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('������ ��������!');
					document.location.reload();
				} else
					p.removeClass('busy');
			}, 'json');
		});
	})

	.on('click', '.remind-add', function() {
		var html =
				'<table class="remind-add-tab">' +
					(window.ZAYAV ? '<tr><td class="label">������:<td><b>' + ZAYAV.head + '</b>' : '') +
					(window.CLIENT ? '<tr><td class="label">������:<td>' + CLIENT.fio : '') +
					'<tr><td class="label top">��������:<td><textarea id="txt"></textarea>' +
					'<tr><td class="label">���� ����������:<TD><INPUT type="hidden" id="day" />' +
					'<tr><td class="label">������:<TD><INPUT type="hidden" id="private" />' +
				'</table>' +
				'<input type="hidden" id="client_id" value="' + (window.CLIENT ? CLIENT.id : 0) + '">' +
				'<input type="hidden" id="zayav_id" value="' + (window.ZAYAV ? ZAYAV.id : 0) + '">',
			dialog = _dialog({
				top:40,
				width:420,
				head:'�������� ������ �����������',
				content:html,
				submit:submit
			});

		$('#txt').autosize().focus();
		$('#day')._calendar();
		$('#private')._check();
		$('#private_check').vkHint({
			msg:'����������� �������<br />������ ������ ��.',
			top:-71,
			left:-11,
			indent:'left',
			delayShow:1000
		});

		function submit() {
			var send = {
				op:'remind_add',
				from:window.ZAYAV ? 'zayav' : (window.CLIENT ? 'client' : ''),
				client_id:$('#client_id').val(),
				zayav_id:$('#zayav_id').val(),
				txt:$.trim($('#txt').val()),
				day:$('#day').val(),
				private:$('#private').val()
			};
			if(!send.txt) {
				err('�� ������� ��������');
				$('#txt').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����������� �������');
						$('.remind_spisok').html(res.html);
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				top:-48,
				left:126,
				indent:40,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '.remind_status', function() {
		var t = $(this),
			html =
			'<div class="zayav-status remind-status">' +
				'<div class="st c1" val="1">' +
					'������� ������ ����' +
					'<div class="about">��������� ����������� �� ������ ����.</div>' +
				'</div>' +
				'<div class="st c2" val="2">' +
					'���������' +
					'<div class="about">������� ��������� �������.</div>' +
				'</div>' +
				'<div class="st c0" val="0">' +
					'������' +
					'<div class="about">������ ����������� �� �����-���� �������.</div>' +
				'</div>' +
				'<table class="zstab">' +
					'<tr><td class="label">����� ����:<td><input type="hidden" id="remind_day" />' +
					'<tr><td class="label">�������:<td><input type="text" id="reason" />' +
					'<tr><td><td><div class="vkButton"><button>���������</button></div>' +
				'</table>' +
			'</div>',
			dialog = _dialog({
				top:30,
				head:'��������� ������� �����������',
				content:html,
				butSubmit:'',
				butCancel:'�������'
			});
		$('#remind_day')._calendar();
		$('.st').click(function() {
			var	v = $(this).attr('val');
			if(v == 1) {
				$('.c2,.c0').hide();
				$('.zstab').show();
			} else
				submit(v);
		});
		$('.remind-status .vkButton').click(function() {
			$(this).addClass('busy');
			submit(1);
		});
		function submit(status) {
			var send = {
				op:'remind_status',
				from:window.ZAYAV ? 'zayav' : (window.CLIENT ? 'client' : ''),
				id:t.attr('val'),
				status:status,
				day:$('#remind_day').val(),
				reason:$('#reason').val()
			};
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('������ ��������!');
					$('.remind_spisok').html(res.html);
				}
			}, 'json');
		}
	})
	.on('click', '.remind_history', function() {
		var t = $(this),
			send = {
				op:'remind_history',
				id:t.attr('val')
			},
			hist = $('#ru' + send.id + ' .hist');
		if(hist.hasClass('_busy'))
			return;
		if(hist.html()) {
			hist.slideToggle(300);
			return;
		}
		hist.html('&nbsp;').addClass('_busy');
		$.post(AJAX_MAIN, send, function(res) {
			hist.removeClass('_busy');
			if(res.success)
				hist.html(res.html);
		}, 'json');
	})

	.on('click', '#history_next', function() {
		var t = $(this),
			send = historyFilter();
		if(t.hasClass('busy'))
			return;
		send.page = $(this).attr('val');
		t.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				t.after(res.html).remove();
			else
				t.removeClass('busy');
		}, 'json');
	})

	.on('click', '.invoice_set', function() {
		if(!window.CASH)
			window.CASH = [];
		var t = $(this),
			invoice_id = t.attr('val'),
			html = '<table class="_dialog-tab">',
			len = CASH.length,
			nal = invoice_id == '1' && !!len;

		if(nal) {
			for(n = 0; n < len; n++)
				html +=
					'<tr><td class="label">' + CASH[n].name + ':' +
						'<td><INPUT type="text" class="money" maxlength="11" value="' + CASH[n].sum + '"> ���.';
			html += '<tr><td class="label">���� <b>' + INVOICE_NAME + '</b>:<td><b id="summa">0</b> ���.';
		} else
			html += '<tr><td class="label">�����:<td><INPUT type="text" class="money" id="sum" maxlength="11"> ���.';

		html += '</table>';
		var dialog = _dialog({
				width:320,
				head:'��������� ������� ����� �����',
				content:html,
				butSubmit:'����������',
				submit:submit
			}),
			inp = dialog.content.find('input');

		inp.eq(0).focus();
		inp.keyEnter(submit);
		if(nal) {
			inp.keyup(function() {
				var summa = 0;
				for(n = 0; n < len; n++) {
					var sum = inp.eq(n).val();
					if(REGEXP_CENA.test(sum))
						summa += sum.replace(',', '.') * 1;
					else {
						summa = 0;
						break;
					}
				}
				$('#summa').html(summa);
			});
		}
		function submit() {
			var cash = [];
			if(nal)
				for(n = 0; n < len; n++) {
					var sum = inp.eq(n).val();
					if(REGEXP_CENA.test(sum))
						cash.push(CASH[n].id + '=' + sum.replace(',', '.') * 1);
					else {
						err('����������� ������� �����');
						inp.eq(n).focus();
						return;
					}
				}
			var send = {
				op:'invoice_set',
				invoice_id:invoice_id,
				sum:nal ? 0 : $('#sum').val(),
				cash:cash.join(':')
			};
			if(!nal && !REGEXP_CENA.test(send.sum)) {
				err('����������� ������� �����');
				$('#sum').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('#cash-spisok').html(res.c);
						$('#invoice-spisok').html(res.i);
						dialog.close();
						_msg('������ ����������.');
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-48,
				left:72
			});
		}
	})
	.on('click', '#report.invoice .img_note', function() {
		var dialog = _dialog({
			top:20,
			width:570,
			head:'������� �������� �� ������',
			load:1,
			butSubmit:'',
			butCancel:'�������'
		});
		var send = {
			op:'invoice_history',
			invoice_id:$(this).attr('val')
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				dialog.content.html(res.html);
			else
				dialog.loadError();
		}, 'json');
	})
	.on('click', '.invoice-history ._next', function() {
		var next = $(this),
			send = {
				op:'invoice_history',
				page:next.attr('val'),
				invoice_id:$('#invoice_history_id').val()
			};
		if(next.hasClass('busy'))
			return;
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#money_all_check', function() {
		var t = $(this),
			n,
			v = t.find('input').val();
		while(!t.hasClass('_money'))
			t = t.parent();
		var tr = t.find('tr');
		for(n = 1; n < tr.length; n++)
			tr.eq(n).find('input:first')._check(v);
	})
	.on('click', '.inc ._check', incomeChoiceSum)
	.on('click', '.transfer-show', function() {
		var dialog = _dialog({
			top:20,
			width:480,
			head:'�������� ������������� ���������',
			load:1,
			butSubmit:'',
			butCancel:'�������'
		});
		var send = {
			op:'transfer_show',
			ids:$(this).attr('val')
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				dialog.content.html(res.html);
			else
				dialog.loadError();
		}, 'json');
	})
	.on('click', '.income-confirm', function() {
		var dialog = _dialog({
			top:20,
			width:480,
			head:'������������� ��������',
			load:1,
			butSubmit:'�����������',
			submit:submit
		});
		var send = {
			op:'income_confirm_get'
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				dialog.content.html(res.html + '<div class="income_choice_sum"></div>');
			else
				dialog.loadError();
		}, 'json');
		function submit() {
			var send = {
				op:'income_confirm',
				ids:incomeChoiceSum().ids
			};
			if(!send.ids)
				err('������� �� �������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('#confirm-info').html(res.confirm);
						$('#cash-spisok').html(res.c);
						$('#invoice-spisok').html(res.i);
						dialog.close();
						_msg('������� ������������.');
					}
					else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:50,
				show:1,
				top:-48,
				left:143
			});
		}
	})
	.on('click', '.transfer-confirm', function() {
		var dialog = _dialog({
			top:20,
			width:520,
			head:'������������� ���������',
			load:1,
			butSubmit:'�����������',
			submit:submit
		});
		var send = {
			op:'transfer_confirm_get'
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				var html = res.html + '<div class="transfer-about">��������: <input type="text" id="about" /></div>';
				dialog.content.html(html);
			} else
				dialog.loadError();
		}, 'json');
		function submit() {
			var ch = dialog.content.find('._check'),
				ids = [];
			for(var n = 0; n < ch.length; n++) {
				var inp = ch.eq(n).find('input');
				if(inp.val() == 1)
					ids.push(inp.attr('id').split('_')[0]);
			}
			if(!ids.length) {
				err('������� �� �������');
				return;
			}
			var send = {
				op:'transfer_confirm',
				ids:ids.join(),
				about:$('#about').val()
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					$('#invoice-spisok').html(res.i);
					dialog.close();
					_msg('�������� ������������.');
				}
				else
					dialog.abort();
			}, 'json');
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:50,
				show:1,
				top:-48,
				left:163
			});
		}
	})
	.on('click', '.income-show', function() {
		var dialog = _dialog({
			top:20,
			width:480,
			head:'�������� ��������',
			load:1,
			butSubmit:'',
			butCancel:'�������'
		});
		var send = {
			op:'income_transfer_show',
			ids:$(this).attr('val')
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				dialog.content.html(res.html);
			else
				dialog.loadError();
		}, 'json');
	})

	.on('click', '.income-add', function() {
		var html =
			'<table class="income-add-tab">' +
  (OPL.from != 'income' ?
	            '<tr><td class="label">������:<td>' + OPL.client_fio +
				'<tr><td class="label">������:' +
					'<td><input type="hidden" id="zayav_id" value="' + (OPL.zayav_id ? OPL.zayav_id : 0) + '">' +
						(OPL.zayav_id ? '<b>' + OPL.zayav_head + '</b>' : '')
  : '') +
				'<tr><td class="label">��� �������:<td><input type="hidden" id="income_opl">' +
					'<a href="' + URL + '&p=setup&d=income" class="img_edit' + _tooltip('��������� ����� ��������', -85) + '</a>' +
				'<tr class="tr_confirm dn"><td class="label">�������������:<td><input type="hidden" id="confirm">' +
				'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="11"> ���.' +
				'<tr><td class="label">�����������:<td><input type="text" id="prim" maxlength="100">' +
			'</table>';
		var dialog = _dialog({
			top:60,
			width:440,
			head:'�������� �������',
			content:html,
			submit:submit
		});
		$('#sum').focus();
		$('#sum,#prim').keyEnter(submit);
		if(OPL.zayav_spisok)
			$('#zayav_id')._select({
				width:210,
				title0:'�� �������',
				spisok:OPL.zayav_spisok
			});
		$('#income_opl')._select({
			width:180,
			title0:'�� ������',
			spisok:INCOME_SPISOK,
			func:function(uid) {
				$('#sum').focus();
				$('.tr_confirm')[(INCOME_CONFIRM[uid] ? 'remove' : 'add') + 'Class']('dn');
				$('#confirm')._check(0);
			}
		});
		$('#confirm')._check();
		$('#confirm_check').vkHint({
			width:210,
			msg:'���������� �������, ���� ����� ����� ������, �� ��������� ������������� � ��� ����������� �� ����.',
			top:-96,
			left:-100
		});
		function submit() {
			var send = {
				op:'income_add',
				from:OPL.from,
				type:$('#income_opl').val(),
				confirm:$('#confirm').val(),
				sum:$('#sum').val(),
				zayav_id:$('#zayav_id').val() || 0,
				client_id:OPL.client_id || 0,
				prim:$.trim($('#prim').val())
			};
			if(send.type == 0) err('�� ������ ��� �������');
			else if(!REGEXP_CENA.test(send.sum) || send.sum == 0) {
				err('����������� ������� �����.');
				$('#sum').focus();
			} else if($('#zayav_id').length && send.zayav_id == 0 && !send.prim) {
				err('���� �� ������� ������, ���������� ������� �����������');
				$('#prim').focus();
			} else if(!$('#zayav_id').length && !send.prim) {
				err('���������� ������� �����������');
				$('#prim').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����� ������� �����!');
						switch(OPL.from) {
							case 'client':
								$('#income_spisok').html(res.html);
								$('.left:first').html(res.balans);
								break;
							case 'zayav':
								$('#income_spisok').html(res.html);
								break;
							case 'income':
								incomeSpisok();
								break;
							default: break;
						}
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-48,
				left:135
			});
		}
	})
	.on('click', '.income-del', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		if(t.hasClass('deleting'))
			return;
		t.addClass('deleting');
		var send = {
			op:'income_del',
			id:t.attr('val')
		};
		$.post(AJAX_MAIN, send, function(res) {
			t.removeClass('deleting');
			if(res.success)
				t.addClass('deleted');
		}, 'json');
	})
	.on('click', '.income-rest', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var send = {
			op:'income_rest',
			id:t.attr('val')
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				t.removeClass('deleted');
		}, 'json');
	})
	.on('click', '#income_next', function() {
		var next = $(this),
			send = {
				op:'income_next',
				page:$(this).attr('val'),
				limit:$('#money_limit').val(),
				client_id:$('#money_client_id').val(),
				zayav_id:$('#money_zayav_id').val(),
				day:$('.selected').val() || ''
			};
		if(next.hasClass('busy'))
			return;
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})

	.on('click', '.expense #monthList div', expenseSpisok)
	.on('click', '.expense ._next', function() {
		var next = $(this),
			send = expenseFilter();
		send.page = next.attr('val');
		if(next.hasClass('busy'))
			return;
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.expense .img_del', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		if(t.hasClass('deleting'))
			return;
		t.addClass('deleting');
		var send = {
			op:'expense_del',
			id:t.attr('val')
		};
		$.post(AJAX_MAIN, send, function(res) {
			t.removeClass('deleting');
			if(res.success)
				t.addClass('deleted');
		}, 'json');
	})
	.on('click', '.expense .img_rest', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var send = {
			op:'expense_rest',
			id:t.attr('val')
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				t.removeClass('deleted');
		}, 'json');
	})
	.on('click', '.expense .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var dialog = _dialog({
				width:380,
				head:'�������������� �������',
				load:1,
				butSubmit:'���������',
				submit:submit
			}),
			id = t.attr('val'),
			send = {
				op:'expense_get',
				id:id
			};
		$.post(AJAX_MAIN, send, function(res) {
			var html = '<table id="expense-add-tab">' +
				'<tr><td class="label">���������:<td><input type="hidden" id="cat" value="' + res.category + '">' +
				'<tr class="tr-work ' + (EXPENSE_WORKER[res.category] ? '' : 'dn') + '">' +
					'<td class="label">���������:' +
					'<td><input type="hidden" id="work" value="' + res.worker + '">' +
				'<tr><td class="label">��������:<td><input type="text" id="about" maxlength="150" value="' + res.about + '">' +
				'<tr><td class="label">�� �����:<td><input type="hidden" id="invoice" value="' + res.invoice + '">' +
				'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="8" value="' + res.sum + '"> ���.' +
				'</table>';
			dialog.content.html(html);

			$('#cat')._select({
				width:180,
				title0:'�� �������',
				spisok:EXPENSE_SPISOK,
				func:function(id) {
					$('#work')._select(0);
					$('.tr-work')[(EXPENSE_WORKER[id] ? 'remove' : 'add') + 'Class']('dn');
				}
			});
			$('#about').focus();
			$('#work')._select({
				title0:'�� ������',
				spisok:WORKER_SPISOK
			});
			$('#invoice')._select({
				title0:'�� ������',
				spisok:INVOICE_SPISOK,
				func:function() {
					$('#sum').focus();
				}
			});
		}, 'json');

		function submit() {
			var send = {
				id:id,
				op:'expense_edit',
				category:$('#cat').val() * 1,
				about:$('#about').val(),
				worker:$('#work').val(),
				invoice:$('#invoice').val() * 1,
				sum:$('#sum').val()
			};
			if(!send.category && !send.about) { err('�������� ��������� ��� ������� ��������.'); $('#about').focus(); }
			else if(!send.invoice) err('������� � ������ ����� ������������ ������.');
			else if(!REGEXP_NUMERIC.test(send.sum)) { err('����������� ������� �����.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ ������.');
						expenseSpisok();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:101
			});
		}
	})

	.on('click', '#report_month .yr', function() {
		$(this).next().toggle();
	})

	.on('click', '.salary .rate-set', function() {
		var html =
				'<div class="_info">' +
					'����� ��������� ������ ���������� ��������� ����� ����� ������������� ����������� ' +
					'�� ��� ������ ���������� � ����������� ����. ' +
					'���� ���������� ����� ���� ������ � ���������� �� 1-�� �� 28 �����.' +
				'</div>' +
				'<table class="salary-tab">' +
					'<tr><td class="label">�����:<TD><INPUT type="text" id="sum" class="money" maxlength="11" value="' + (RATE ? RATE : '') + '" /> ���.' +
					'<tr><td class="label">���� ����������:<TD><INPUT type="text" id="day" maxlength="2" value="' + (RATE ? RATE_DAY : '') + '" />' +
				'</table>',
			dialog = _dialog({
				top:30,
				width:320,
				head:'��������� ������ �/� ��� ����������',
				content:html,
				butSubmit:'����������',
				submit:submit
			});

		$('#sum').focus();
		$('#sum,#day').keyEnter(submit);
		function submit() {
			var send = {
				op:'salary_rate_set',
				worker:WORKER_ID,
				sum:$('#sum').val(),
				day:$('#day').val() * 1
			};
			if(!REGEXP_CENA.test(send.sum)) { err('����������� ������� �����.'); $('#sum').focus(); }
			else if(!REGEXP_NUMERIC.test(send.day) || !send.day || send.day > 28) { err('����������� ������ ����.'); $('#day').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						RATE = send.sum;
						RATE_DAY = send.day;
						dialog.close();
						_msg('��������� ������ �����������.');
						$('#spisok').html(res.html);
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:74
			});
		}
	})
	.on('click', '.salary .up', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�����:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> ���.' +
					'<tr><td class="label">��������:<TD><INPUT type="text" id="about" maxlength="50">' +
					'<tr><td class="label">�����:<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
													'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
				'</table>',
			dialog = _dialog({
				head:'�������� ���������� ��� ����������',
				content:html,
				submit:submit
			});

		$('#sum').focus();
		$('#sum,#about').keyEnter(submit);
		$('#tabmon')._select({
			width:80,
			spisok:MON_SPISOK
		});
		$('#tabyear')._select({
			width:60,
			spisok:YEAR_SPISOK
		});
		function submit() {
			var send = {
				op:'salary_up',
				worker:WORKER_ID,
				sum:$('#sum').val(),
				about:$('#about').val(),
				mon:$('#tabmon').val(),
				year:$('#tabyear').val()
			};
			if(!REGEXP_NUMERIC.test(send.sum)) { err('����������� ������� �����.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('���������� �����������.');
						$('#spisok').html(res.html);
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:93
			});
		}
	})
	.on('click', '.salary .down', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�� �����:<TD><input type="hidden" id="invoice">' +
						'<a href="' + URL + '&p=setup&d=invoice" class="img_edit' + _tooltip('��������� ������', -56) + '</a>' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="8"> ���.' +
					'<tr><td class="label">��������:<td><input type="text" id="about" maxlength="100">' +
					'<tr><td class="label">�����:<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
													'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
				'</table>',
			dialog = _dialog({
				head:'������ �������� ����������',
				content:html,
				submit:submit
			});

		$('#sum').focus();
		$('#invoice')._select({
			title0:'�� ������',
			spisok:INVOICE_SPISOK,
			func:function() {
				$('#sum').focus();
			}
		});
		$('#sum,#about').keyEnter(submit);
		$('#tabmon')._select({
			width:80,
			spisok:MON_SPISOK
		});
		$('#tabyear')._select({
			width:60,
			spisok:YEAR_SPISOK
		});

		function submit() {
			var send = {
				op:'salary_down',
				worker:WORKER_ID,
				invoice:$('#invoice').val() * 1,
				sum:$('#sum').val(),
				about:$('#about').val(),
				mon:$('#tabmon').val(),
				year:$('#tabyear').val()
			};
			if(!send.invoice) err('������� � ������ ����� ������������ ������.');
			else if(!REGEXP_NUMERIC.test(send.sum)) { err('����������� ������� �����.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('������ �������� �����������.');
						$('#spisok').html(res.html);
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:93
			});
		}
	})
	.on('click', '.salary .deduct', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�����:<TD><input type="text" id="sum" class="money" maxlength="8"> ���.' +
					'<tr><td class="label">��������:<TD><input type="text" id="about" maxlength="100">' +
					'<tr><td class="label">�����:<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
																'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
				'</table>',
			dialog = _dialog({
				head:'�������� ������ �� ��������',
				content:html,
				submit:submit
			});

		$('#sum').focus();
		$('#sum,#about').keyEnter(submit);
		$('#tabmon')._select({
			width:80,
			spisok:MON_SPISOK
		});
		$('#tabyear')._select({
			width:60,
			spisok:YEAR_SPISOK
		});
		function submit() {
			var send = {
				op:'salary_deduct',
				worker:WORKER_ID,
				sum:$('#sum').val(),
				about:$('#about').val(),
				mon:$('#tabmon').val(),
				year:$('#tabyear').val()
			};
			if(!REGEXP_NUMERIC.test(send.sum)) { err('����������� ������� �����.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('����� ���������.');
						$('#spisok').html(res.html);
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:93
			});
		}
	})
	.on('click', '.salary .start-set', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�����:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> ���.' +
				'</table>',
			dialog = _dialog({
				head:'��������� ������� �� �������� ����������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});

		$('#sum').focus().keyEnter(submit);

		function submit() {
			var send = {
				op:'salary_start_set',
				worker:WORKER_ID,
				sum:$('#sum').val()
			};
			if(!REGEXP_CENA.test(send.sum)) { err('����������� ������� �����.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('��������� ����������.');
						$('#spisok').html(res.html);
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:93
			});
		}
	})
	.on('click', '.salary ._check:not(#salary_all_check)', salaryCheck)
	.on('click', '.salary #salary_all_check', function() {
		var t = $(this),
			n,
			v = t.find('input').val();
		var all = $('.to-all');
		for(n = 0; n < all.length; n++)
			all.eq(n).find('._check input:first')._check(v);
		salaryCheck();
	})
	.on('click', '.salary-list', function() {
		var ids = $(this).attr('val');
		if(!ids)
			return;
		location.href = SITE + '/view/salary_list.php?' + VALUES + '&worker_id=' + WORKER_ID + '&ids=' + ids;
	})
	.on('click', '.salary .zp_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:110,
				width:250,
				head:'�������� �/�',
				content:'<CENTER>����������� �������� ������.</CENTER>',
				butSubmit:'�������',
				submit:submit
			});
		while(t[0].tagName != 'TR')
			t = t.parent();
		function submit() {
			var send = {
				op:'expense_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('�������.');
					t.remove();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary .ze_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:110,
				width:250,
				head:'��������',
				content:'<CENTER>����������� �������� ������.</CENTER>',
				butSubmit:'�������',
				submit:submit
			});
		while(t[0].tagName != 'TR')
			t = t.parent();
		function submit() {
			var send = {
				op:'salary_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('�������.');
					t.remove();
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.ready(function() {
		if($('#pin-enter').length) {
			$('#pin')
				.focus()
				.keydown(function() {
					$('.red').html('&nbsp;');
				})
				.keyEnter(pinEnter);
			$('.vkButton').click(pinEnter);
		}
		if($('#client').length) {
			window.cFind = $('#find')._search({
				width:602,
				focus:1,
				enter:1,
				txt:'������� ������� ������ �������',
				func:clientSpisok
			});
			$('#buttonCreate').vkHint({
				msg:'<B>�������� ������ ������� � ����.</B><br /><br />' +
					'����� �������� �� ��������� �� �������� � ����������� � ������� ��� ���������� ��������.<br /><br />' +
					'�������� ����� ����� ��������� ��� <A href="' + URL + '&p=zayav&d=add&back=client">�������� ����� ������</A>.',
				ugol:'right',
				width:215,
				top:-38,
				left:-250,
				indent:40,
				delayShow:1000,
				correct:0
			}).click(clientAdd);
			$('#dolg')._check(clientSpisok);
			$('#dolg_check').vkHint({
				msg:'<b>������ ���������.</b><br /><br />' +
					'��������� �������, � ������� ������ ����� 0. ����� � ���������� ������������ ����� ����� �����.',
				ugol:'right',
				width:150,
				top:-6,
				left:-185,
				indent:20,
				delayShow:1000,
				correct:0
			});
			$('#note')._check(clientSpisok);
			$('#zayav_cat')._select({
				width:140,
				title0:'����� ������',
				spisok:[
					{uid:1,title:'������'},
					{uid:2,title:'������'},
					{uid:3,title:'��������'},
					{uid:4,title:'���������'}
				],
				func:clientSpisok
			});
			$('#product_id')._select({
				width:140,
				title0:'����� �������',
				spisok:PRODUCT_SPISOK,
				func:clientSpisok
			});
		}
		if($('#clientInfo').length) {
			$('#dopLinks .link').click(function() {
				$('#dopLinks .link').removeClass('sel');
				$(this).addClass('sel');
				var val = $(this).attr('val');
				$('.res').css('display', val == 'zayav' ? 'block' : 'none');
				$('#zayav_filter').css('display', val == 'zayav' ? 'block' : 'none');
				$('#zayav_spisok').css('display', val == 'zayav' ? 'block' : 'none');
				$('#income_spisok').css('display', val == 'money' ? 'block' : 'none');
				$('.remind_spisok').css('display', val == 'remind' ? 'block' : 'none');
				$('#comments').css('display', val == 'comm' ? 'block' : 'none');
				$('#histories').css('display', val == 'hist' ? 'block' : 'none');
			});
			$('.cedit').click(function() {
				var html = '<table class="client-add">' +
					'<tr><td class="label">���:<td><input type="text" id="fio" maxlength="100" value="' + CLIENT.fio + '">' +
					'<tr><td class="label">�������:<td><input type="text" id="telefon" maxlength="100" value="' + CLIENT.telefon + '">' +
					'<tr><td class="label">�����:<td><input type="text" id="adres" maxlength="100" value="' + CLIENT.adres + '">' +
					'<tr><td><td><b>���������� ������:</b>' +
					'<tr><td class="label">�����:' +
						'<td><input type="text" id="pasp_seria" maxlength="8" value="' + CLIENT.pasp_seria + '">' +
							'<span class="label">�����:</span><input type="text" id="pasp_nomer" maxlength="10" value="' + CLIENT.pasp_nomer + '">' +
					'<tr><td class="label">��������:<td><input type="text" id="pasp_adres" maxlength="100" value="' + CLIENT.pasp_adres + '">' +
					'<tr><td class="label">��� �����:<td><input type="text" id="pasp_ovd" maxlength="100" value="' + CLIENT.pasp_ovd + '">' +
					'<tr><td class="label">����� �����:<td><input type="text" id="pasp_data" maxlength="100" value="' + CLIENT.pasp_data + '">' +
				'</table>';
				var dialog = _dialog({
					head:'�������������� ������ �������',
					top:30,
					width:380,
					content:html,
					butSubmit:'���������',
					submit:submit
				});
				$('#fio,#telefon,#adres,#pasp_seria,#pasp_nomer,#pasp_adres,#pasp_ovd,#pasp_data').keyEnter(submit);
				function submit() {
					var send = {
							op:'client_edit',
							client_id:CLIENT.id,
							fio:$('#fio').val(),
							telefon:$('#telefon').val(),
							adres:$('#adres').val(),
							pasp_seria:$('#pasp_seria').val(),
							pasp_nomer:$('#pasp_nomer').val(),
							pasp_adres:$('#pasp_adres').val(),
							pasp_ovd:$('#pasp_ovd').val(),
							pasp_data:$('#pasp_data').val()
						};
					if(!send.fio) {
						dialog.bottom.vkHint({
							msg:'<span class="red">�� ������� ��� �������.</span>',
							top:-47,
							left:100,
							indent:50,
							show:1,
							remove:1
						});
						$("#fio").focus();
					} else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								CLIENT = res;
								$('.left:first').html(res.html);
								dialog.close();
								_msg('������ ������� ��������.');
							} else
								dialog.abort();
						}, 'json');
					}
				}
			});
			$('.cdel').click(function() {
				var dialog = _dialog({
					top:90,
					width:300,
					head:'�������� �������',
					content:'<center>��������!<br />����� ������� ��� ������ � �������,<br />��� ������, ������� � ������.<br /><b>����������� ��������.</b></center>',
					butSubmit:'�������',
					submit:submit
				});
				function submit() {
					var send = {
						op:'client_del',
						id:CLIENT.id
					};
					dialog.process();
					$.post(AJAX_MAIN, send, function(res) {
						if(res.success) {
							dialog.close();
							_msg('������ ������!');
							location.href = URL + '&p=client';
						} else
							dialog.abort();
					}, 'json');
				}
			});
		}

		if($('#zayav').length) {
			$('#find')
				._search({
					width:153,
					focus:1,
					txt:'������� �����...',
					enter:1,
					func:zayavFindFast
				})
				.vkHint({
					width:220,
					msg:'<b>������� �����</b> ������������ ������������ �� ���� ���������� ������. ' +
						'����������� ����: ����� ��������, ������ ��, �, �, ����� ���������, ' +
						'��� � �������� �������� � ���������� ����� ������. ' +
						'������ ������� ������� ������������.',
					ugol:'top',
					indent:'right',
					delayShow:1000,
					top:45,
					left:384
				});
			$('#status').rightLink(zayavSpisok);
			var spisok = [];
			for(var n = 0; n < PRODUCT_IDS.length; n++) {
				var uid = PRODUCT_IDS[n];
				spisok.push({uid:uid, title:PRODUCT_ASS[uid]});
			}
			$('#product_id')._select({
				width:155,
				title0:'����� �������',
				spisok:spisok,
				func:zayavSpisok
			});
		}
		if($('.zayav-info').length) {
			$('.zinfo').click(function() {
				$(this).parent().find('.sel').removeClass('sel');
				$(this).addClass('sel');
				$('.zayav-info').removeClass('h');
			});
			$('.hist').click(function() {
				$(this).parent().find('.sel').removeClass('sel');
				$(this).addClass('sel');
				$('.zayav-info').addClass('h');
			});
			$('.dogovor_create').click(dogovorCreate);
			$('.dogovor_no_require').click(function() {
				var send = {
					op:'dogovor_no_require',
					zayav_id:ZAYAV.id
				};
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success)
						document.location.reload();
				}, 'json');
			});
			$('#dogovor_action')._dropdown({
				head:'�� ��������',
				spisok:[
					{uid:1, title:'��������� �������'},
					{uid:2, title:'��������� ������ � ��������� "��������� �������"'}
				],
				nosel:1,
				func:function(v) {
					if(v == 1)
						dogovorCreate();
					if(v == 2) {
						var send = {
							op:'dogovor_require',
							zayav_id:ZAYAV.id
						};
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success)
								document.location.reload();
						}, 'json');
					}
				}
			});
			$('#dogovor_reaction')._dropdown({
				head:'��������',
				headgrey:1,
				spisok:[
					{uid:1, title:'��������� ������ ��������'},
					{uid:2, title:'��������������'},
					{uid:3, title:'�����������'}
				],
				nosel:1,
				func:function(v) {
					if(v == 1)
						dogovorCreate('edit');
					//if(v == 2)
					//	dogovorCreate('reneg');
				}
			});

			$('.zakaz_edit').click(function() {
				var html = '<table class="zayav-info-edit">' +
						'<tr><td class="label">������:      <td>' + ZAYAV.client_fio +
						'<tr><td class="label topi">�������:<td id="product">' +
						'<tr><td><td><input type="text" id="zakaz_txt" placeholder="���� ������� ���������� ������ �������.." maxlength="300" value="' + ZAYAV.zakaz_txt + '">' +
						'<tr><td class="label">����� ��:	   <td><INPUT type="text" id="nomer_vg" maxlength="30" value="' + ZAYAV.nomer_vg + '" />' +
						'<tr><td class="label">����� �: 	   <td><INPUT type="text" id="nomer_g" maxlength="30" value="' + ZAYAV.nomer_g + '" />' +
						'<tr><td class="label">����� �: 	   <td><INPUT type="text" id="nomer_d" maxlength="30" value="' + ZAYAV.nomer_d + '" />' +
						'</table>',
					dialog = _dialog({
						width:500,
						top:30,
						head:ZAYAV.head + ' - ��������������',
						content:html,
						butSubmit:'���������',
						submit:submit
					});
				$('#product').productList(ZAYAV.product);
				$('#zakaz_txt').keyEnter(submit);
				function submit() {
					var msg,
						send = {
							op:'zakaz_edit',
							zayav_id:ZAYAV.id,
							product:$('#product').productList('get'),
							zakaz_txt:$('#zakaz_txt').val(),
							nomer_vg:$('#nomer_vg').val(),
							nomer_g:$('#nomer_g').val(),
							nomer_d:$('#nomer_d').val()
						};
					if(!send.product && !send.zakaz_txt) msg = '���������� ������� ������� ��� ������� ����� �������';
					else if(send.product == 'count_error') msg = '����������� ������� ���������� �������';
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								dialog.close();
								_msg('������ ��������!');
								document.location.reload();
							} else
								dialog.abort();
						}, 'json');
					}
					if(msg)
						dialog.bottom.vkHint({
							msg:'<SPAN class="red">' + msg + '</SPAN>',
							top:-47,
							left:161,
							indent:40,
							show:1,
							remove:1
						});
				}
			});
			$('.zamer_edit').click(function() {
				var html = '<table class="zayav-info-edit">' +
						'<tr><td class="label">������:        <td>' + ZAYAV.client_fio +
						'<tr><td class="label top">�������:	<td id="product">' +
						'<tr><td class="label">����� ������:  <td><INPUT type="text" id="adres" maxlength="100" value="' + ZAYAV.adres + '" />' +
						'<tr><td class="label">���� � ����� ������:<td class="zayav-zamer-dtime">' +
						'<tr><td class="label">������������ ������:' +
							'<td><INPUT TYPE="hidden" id="zamer_duration" value="' + ZAYAV.dur + '" />' +
								'<a class="zamer_table" val="' + ZAYAV.id + '">������� �������</a>' +
						'</table>',
					dialog = _dialog({
						width:500,
						top:30,
						head:ZAYAV.head + ' - ��������������',
						content:html,
						butSubmit:'���������',
						submit:submit
					});
				$('#product').productList(ZAYAV.product);
				zayavZamerDtime(ZAYAV);
				function submit() {
					var send = {
						op:'zamer_edit',
						zayav_id:ZAYAV.id,
						product:$('#product').productList('get'),
						adres:$('#adres').val(),
						zamer_day:$('#zamer_day').val(),
						zamer_hour:$('#zamer_hour').val(),
						zamer_min:$('#zamer_min').val(),
						zamer_duration:$('#zamer_duration').val()
					};
					if(!send.product) err('�� ������� �������');
					else if(send.product == 'count_error') err('����������� ������� ���������� �������');
					else if(!send.adres) err('�� ������ �����');
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								dialog.close();
								_msg('������ ��������!');
								document.location.reload();
							} else {
								dialog.abort();
								err(res.text);
							}
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						top:-47,
						left:161,
						indent:40,
						show:1,
						remove:1
					});
				}
			});
			$('.dog_edit').click(function() {
				var html = '<table class="zayav-info-edit">' +
						'<tr><td class="label">������:      <td>' + ZAYAV.client_fio +
						'<tr><td class="label top">�������:	<td id="product">' +
						'<tr><td class="label">����� ���������:<td><INPUT type="text" id="adres" maxlength="100" value="' + ZAYAV.adres + '" />' +
						'</table>',
					dialog = _dialog({
						width:500,
						top:30,
						head:'��������������',
						content:html,
						butSubmit:'���������',
						submit:submit
					});
				$('#product').productList(ZAYAV.product);
				function submit() {
					var msg,
						send = {
							op:'dog_edit',
							zayav_id:ZAYAV.id,
							product:$('#product').productList('get'),
							adres:$('#adres').val()
						};
					if(!send.product) msg = '�� ������� �������';
					else if(send.product == 'count_error') msg = '����������� ������� ���������� �������';
					else if(!send.adres) msg = '�� ������ �����';
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								dialog.close();
								_msg('������ ��������!');
								document.location.reload();
							} else
								dialog.abort();
						}, 'json');
					}
					if(msg)
						dialog.bottom.vkHint({
							msg:'<SPAN class="red">' + msg + '</SPAN>',
							top:-47,
							left:161,
							indent:40,
							show:1,
							remove:1
						});
				}
			});
			$('.set_edit').click(function() {
				var html = '<table class="zayav-info-edit">' +
						'<tr><td class="label">������:      <td>' + ZAYAV.client_fio +
						'<tr><td class="label top">�������:	<td id="product">' +
						'<tr><td class="label">����� ���������:<td><INPUT type="text" id="adres" maxlength="100" value="' + ZAYAV.adres + '" />' +
						'<tr><td class="label">����� ��:	   <td><INPUT type="text" id="nomer_vg" maxlength="30" value="' + ZAYAV.nomer_vg + '" />' +
						'<tr><td class="label">����� �: 	   <td><INPUT type="text" id="nomer_g" maxlength="30" value="' + ZAYAV.nomer_g + '" />' +
						'<tr><td class="label">����� �: 	   <td><INPUT type="text" id="nomer_d" maxlength="30" value="' + ZAYAV.nomer_d + '" />' +
						'</table>',
					dialog = _dialog({
						width:500,
						top:30,
						head:ZAYAV.head + ' - ��������������',
						content:html,
						butSubmit:'���������',
						submit:submit
					});
				$('#product').productList(ZAYAV.product);
				$('#adres,#nomer_vg,#nomer_g,#nomer_d').keyEnter(submit);
				function submit() {
					var msg,
						send = {
							op:'set_edit',
							zayav_id:ZAYAV.id,
							product:$('#product').productList('get'),
							adres:$('#adres').val(),
							nomer_vg:$('#nomer_vg').val(),
							nomer_g:$('#nomer_g').val(),
							nomer_d:$('#nomer_d').val()
						};
					if(!send.product) msg = '�� ������� �������';
					else if(send.product == 'count_error') msg = '����������� ������� ���������� �������';
					else if(!send.adres) msg = '�� ������ �����';
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								dialog.close();
								_msg('������ ��������!');
								document.location.reload();
							} else
								dialog.abort();
						}, 'json');
					}
					if(msg)
						dialog.bottom.vkHint({
							msg:'<SPAN class="red">' + msg + '</SPAN>',
							top:-47,
							left:161,
							indent:40,
							show:1,
							remove:1
						});
				}
			});
			$('.acc-add').click(function() {
				var html = '<TABLE class="accrual-add">' +
					'<tr><td class="label">�����: <td><input type="text" id="sum" class="money" maxlength="6" /> ���.' +
					'<tr><td class="label">����������:<em>(�� �����������)</em><td><input type="text" id="prim" maxlength="100" />' +
					'</TABLE>';
				var dialog = _dialog({
					top:60,
					width:420,
					head:'����������',
					content:html,
					submit:submit
				});
				$('#sum').focus();
				$('#sum,#prim').keyEnter(submit);

				function submit() {
					var msg,
						send = {
							op:'accrual_add',
							zayav_id:ZAYAV.id,
							sum:$('#sum').val(),
							prim:$('#prim').val()
						};
					if(!REGEXP_CENA.test(send.sum)) {
						msg = '����������� ������� �����.';
						$('#sum').focus();
					} else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							dialog.abort();
							if(res.success) {
								dialog.close();
								_msg('���������� ������� �����������.');
								$('#income_spisok').html(res.html);
							}
						}, 'json');
					}

					if(msg)
						dialog.bottom.vkHint({
							msg:'<SPAN class="red">' + msg + '</SPAN>',
							top:-48,
							left:123,
							indent:40,
							remove:1,
							show:1,
							correct:0
						});
				}
			});
			$('.delete').click(function() {
				var dialog = _dialog({
					top:110,
					width:250,
					head:'�������� ������',
					content:'<CENTER>����������� �������� ������.</CENTER>',
					butSubmit:'�������',
					submit:function() {
						var send = {
							op:'zayav_delete',
							zayav_id:ZAYAV.id
						};
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success)
								location.href = URL + '&p=client&d=info&id=' + res.client_id;
						}, 'json');
					}
				});
			});
			$('.rashod-edit').click(function() {
				var html = '<table class="zayav-rashod-edit">' +
						'<tr><td class="label">������: <td><b>' + ZAYAV.head + '</b>' +
						'<tr><td class="label topi">�������:<td id="zrs">' +
						'</table>',
					dialog = _dialog({
						width:470,
						top:30,
						head:'��������� �������� ������',
						content:html,
						butSubmit:'���������',
						submit:submit
					});
				$('#zrs').zayavRashod(ZAYAV.rashod);
				function submit() {
					var send = {
						op:'zayav_expense_edit',
						zayav_id:ZAYAV.id,
						rashod:$('#zrs').zayavRashod('get')
					};
					if(send.rashod == 'sum_error') err('����������� ������� �����');
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								$('.zrashod').html(res.html);
								ZAYAV.rashod = res.array;
								dialog.close();
								_msg('���������.');
							} else
								dialog.abort();
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						top:-47,
						left:147,
						indent:40,
						show:1,
						remove:1
					});
				}
			});
			$('.zakaz-to-set').click(function() {
				var html = '<table class="_dialog-tab">' +
					'<tr><td class="label">������:<td>' + ZAYAV.client_fio +
					'<tr><td class="label">����� ���������:' +
						'<td><INPUT type="text" id="adres" maxlength="100" value="' + ZAYAV.adres + '" />' +
							'<INPUT type="hidden" id="homeadres" />' +
					'</table>';
				var dialog = _dialog({
					top:60,
					width:400,
					head:'������� ������ � ���������',
					content:html,
					butSubmit:'���������',
					submit:submit
				});
				$('#adres').focus().keyEnter(submit);
				$('#homeadres')._check({
					func:function() {
						$('#adres').val(ZAYAV.client_adres);
					}
				});
				$('#homeadres_check').vkHint({
					msg:'��������� � ������� ���������� �������',
					top:-75,
					left:163,
					indent:60,
					delayShow:400
				});
				function submit() {
					var send = {
						op:'zakaz_to_set',
						zayav_id:ZAYAV.id,
						adres:$('#adres').val()
					};
					if(!send.adres) {
						err('�� ������ ����� ���������');
						$('#adres').focus();
					} else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							dialog.abort();
							if(res.success) {
								dialog.close();
								_msg('���������.');
								document.location.reload();
							}
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						top:-48,
						left:113,
						indent:40,
						remove:1,
						show:1,
						correct:0
					});
				}
			});
		}

		if($('#remind').length) {
			$('.goyear').click(function() {
				$('#remind').addClass('y');
			});
			window._calendarFilter = remindSpisok;
			$('#status')._radio(remindSpisok);
		}

		if($('#report.history').length) {
			$('#worker_id')._select({
				width:160,
				title0:'��� ����������',
				spisok:WORKERS,
				func:historySpisok
			});
			$('#cat_id')._select({
				width:160,
				title0:'����� ���������',
				spisok:HISTORY_GROUP,
				func:historySpisok
			});
		}
		if($('#report.income').length) {
			window._calendarFilter = incomeSpisok;
			$('#income_id')._select({
				width:160,
				title0:'����� �������',
				spisok:INCOME_SPISOK,
				func:incomeSpisok
			});
			if(window.WORKERS)
				$('#worker_id')._select({
					width:160,
					title0:'��� ����������',
					spisok:WORKERS,
					func:incomeSpisok
				});
		}
		if($('#report.expense').length) {
			$('.add').click(function() {
				var html =
						'<table id="expense-add-tab">' +
							'<tr><td class="label">���������:<TD><INPUT type="hidden" id="cat">' +
								'<a href="' + URL + '&p=setup&d=expense" class="img_edit' + _tooltip('��������� ��������� ��������', -95) + '</a>' +
							'<tr class="tr-work dn"><td class="label">���������:<TD><INPUT type="hidden" id="work">' +
							'<tr class="tr-work dn"><td class="label">�����:' +
													'<td><input type="hidden" id="tabmon" value="' + ((new Date()).getMonth() + 1) + '" /> ' +
														'<input type="hidden" id="tabyear" value="' + ((new Date()).getFullYear()) + '" />' +
							'<tr><td class="label">��������:<TD><INPUT type="text" id="about" maxlength="100">' +
							'<tr><td class="label">�� �����:<TD><INPUT type="hidden" id="invoice">' +
								'<a href="' + URL + '&p=setup&d=invoice" class="img_edit' + _tooltip('��������� ������', -56) + '</a>' +
							'<tr><td class="label">�����:<TD><INPUT type="text" id="sum" class="money" maxlength="11"> ���.' +
						'</table>',
					dialog = _dialog({
						width:380,
						head:'�������� �������',
						content:html,
						submit:submit
					});

				$('#cat')._select({
					width:180,
					title0:'�� �������',
					spisok:EXPENSE_SPISOK,
					func:function(id) {
						$('#work')._select(0);
						$('.tr-work')[(EXPENSE_WORKER[id] ? 'remove' : 'add') + 'Class']('dn');
					}
				});
				$('#about').focus();
				$('#work')._select({
					title0:'�� ������',
					spisok:WORKER_SPISOK
				});
				$('#invoice')._select({
					title0:'�� ������',
					spisok:INVOICE_SPISOK,
					func:function() {
						$('#sum').focus();
					}
				});
				$('#sum,#about').keyEnter(submit);
				$('#tabmon')._select({
					width:80,
					spisok:MON_SPISOK
				});
				$('#tabyear')._select({
					width:60,
					spisok:YEAR_SPISOK
				});
				function submit() {
					var send = {
						op:'expense_add',
						category:$('#cat').val() * 1,
						about:$('#about').val(),
						worker:$('#work').val(),
						invoice:$('#invoice').val() * 1,
						sum:$('#sum').val(),
						mon:$('#tabmon').val(),
						year:$('#tabyear').val()
					};
					if(!send.category && !send.about) { err('�������� ��������� ��� ������� ��������.'); $('#about').focus(); }
					else if(!send.invoice) err('������� � ������ ����� ������������ ������.');
					else if(!REGEXP_CENA.test(send.sum) || send.sum == 0) { err('����������� ������� �����.'); $('#sum').focus(); }
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function (res) {
							if(res.success) {
								dialog.close();
								_msg('����� ������ �����.');
								expenseSpisok();
							} else
								dialog.abort();
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						remove:1,
						indent:40,
						show:1,
						top:-47,
						left:101
					});
				}
			});
			$('#category')._select({
				width:160,
				title0:'����� ���������',
				spisok:EXPENSE_SPISOK,
				func:expenseSpisok
			});
			$('#worker')._select({
				width:160,
				title0:'��� ����������',
				spisok:WORKERS,
				func:expenseSpisok
			});
			$('#invoice_id')._radio(expenseSpisok);
			$('#year').years({
				func:expenseSpisok,
				center:function() {
					var arr = [],
						inp = $('#monthList input'),
						all = 0;
					for(n = 1; n <= 12; n++)
						if(inp.eq(n - 1).val() == 0) {
							all = 1;
							break;
						}
					for(n = 1; n <= 12; n++)
						$('#c' + n)._check(all);
					expenseSpisok();
				}
			});
		}
		if($('#report.invoice').length) {
			$('.transfer').click(function() {
				var t = $(this),
					html = '<table class="invoice-transfer">' +
						'<tr><td class="label">�� �����:<td><input type="hidden" id="from" />' +
						'<tr><td class="label">�� ����:<td><input type="hidden" id="to" />' +
						'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" /> ���. ' +
							'<a class="income-choice">������� �������</a>' +
							'<input type="hidden" id="ids" />' +
						'</table>',
					dialog = _dialog({
						width:380,
						head:'������� ����� �������',
						content:html,
						butSubmit:'���������',
						submit:submit
					});
				if(window.CASH_SPISOK && !window.CSMOVE) {//�������� ����������� �� ��� ���� ������
					for(n = 0; n < INVOICE_SPISOK.length; n++)
						if(INVOICE_SPISOK[n].uid != 1)
							CASH_SPISOK.push(INVOICE_SPISOK[n]);
					INVOICE_SPISOK = CASH_SPISOK;
					window.CSMOVE = 1;
				}
				$('#from')._select({
					width:250,
					title0:'�� ������',
					spisok:INVOICE_SPISOK
				});
				$('#to')._select({
					width:250,
					title0:'�� ������',
					spisok:INVOICE_SPISOK
				});
				$('#sum').keyEnter(submit);
				$('#sum').keyup(function() {
					if($('#ids').val()) {
						$('.income-choice').html('������� �������');
						$('#ids').val('');
						$('#sum').val('');
					}
				});
				$('.income-choice').click(function() {
					if($('#from').val() == 0) {
						err('�� ������ ����-�����������', 1);
						return;
					}
					var choice = _dialog({
						top:20,
						width:480,
						head:'����� �������� ��� ��������',
						load:1,
						butSubmit:'������',
						submit:chioceSubmit
					});
					var send = {
						op:'income_choice',
						owner_id:$('#from').val(),
						ids:$('#ids').val()
					};
					$.post(AJAX_MAIN, send, function(res) {
						if(res.success) {
							choice.content.html(res.html + '<div class="income_choice_sum"></div>');
							incomeChoiceSum();
						}
						else
							choice.loadError();
					}, 'json');
					function chioceSubmit() {
						var res = incomeChoiceSum();
						$('#ids').val(res.ids);
						$('.income-choice').html(res.count + ' ������' + _end(res.count, ['', '�', '��']));
						$('#sum').val(res.sum);
						choice.close();
					}
				});
				function submit() {
					var send = {
						op:'invoice_transfer',
						from:$('#from').val() * 1,
						to:$('#to').val() * 1,
						sum:$('#sum').val(),
						ids:$('#ids').val()
					};
					if(!send.from) err('�������� ����-�����������');
					else if(!send.to) err('�������� ����-����������');
					else if(send.from == send.to) err('�������� ������ ����');
					else if(!REGEXP_CENA.test(send.sum) || send.sum == 0) { err('����������� ������� �����'); $('#sum').focus(); }
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								$('#cash-spisok').html(res.c);
								$('#invoice-spisok').html(res.i);
								$('.transfer-spisok').html(res.t);
								dialog.close();
								_msg('������� ���������.');
							} else
								dialog.abort();
						}, 'json');
					}
				}
				function err(msg, ch) {
					dialog.bottom.vkHint({
						msg:'<span class="red">' + msg + '</span>',
						top:ch ? -95 : -47,
						left:ch ? 197 : 92,
						indent:50,
						show:1,
						remove:1
					});
				}
			});
		}
		if($('#report.salary').length) {
			$('#year').years({
				func:salarySpisok
			});
			$('#salmon')._radio({
				func:salarySpisok
			});
		}
	});