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
				//else if(!hash.d)
				//	s = false;
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
					location.href = URL +
						'&p=' + _cookie('p') +
						'&d=' + _cookie('d') +
						'&id=' + _cookie('id');
				else {
					$('.vkButton').removeClass('busy');
					$('#pin').val('').focus();
					$('.red').html(res.text);
				}
			}, 'json');
		}
	},
	clientAdd = function(callback) {
		var html = '<table class="client-add">' +
				'<tr><td class="label">Имя:<td><input type="text" id="c-fio" maxlength="100">' +
				'<tr><td class="label">Телефон:<td><input type="text" id="c-telefon" maxlength="100">' +
				'<tr><td class="label">Адрес:<td><input type="text" id="c-adres" maxlength="100">' +
				'<tr class="tr_pasp"><td colspan="2"><a>Заполнить паспортные данные</a>' +
				'<tr class="dn"><td><td><b>Паспортные данные:</b>' +
				'<tr class="dn"><td class="label">Серия:' +
							   '<td><input type="text" id="pasp_seria" maxlength="8">' +
								   '<span class="label">Номер:</span><input type="text" id="pasp_nomer" maxlength="10">' +
				'<tr class="dn"><td class="label">Прописка:<td><input type="text" id="pasp_adres" maxlength="100">' +
				'<tr class="dn"><td class="label">Кем выдан:<td><input type="text" id="pasp_ovd" maxlength="100">' +
				'<tr class="dn"><td class="label">Когда выдан:<td><input type="text" id="pasp_data" maxlength="100">' +
			'</table>',
			dialog = _dialog({
				width:380,
				head:'Добавление нoвого клиента',
				content:html,
				submit:submit
			});
		$('#c-fio').focus();
		$('#c-fio,#c-telefon,#c-adres').keyEnter(submit);
		$('.tr_pasp a').click(function() {
			$('.tr_pasp').remove();
			$('.client-add .dn').removeClass('dn');
			$('#pasp_seria').focus();
		});
		function submit() {
			var send = {
				op:'client_add',
				fio:$('#c-fio').val(),
				telefon:$('#c-telefon').val(),
				adres:$('#c-adres').val(),
				pasp_seria:$('#pasp_seria').val(),
				pasp_nomer:$('#pasp_nomer').val(),
				pasp_adres:$('#pasp_adres').val(),
				pasp_ovd:$('#pasp_ovd').val(),
				pasp_data:$('#pasp_data').val()
			};
			if(!send.fio) {
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">Не указано имя клиента.</SPAN>',
					top:-47,
					left:103,
					indent:40,
					show:1,
					remove:1
				});
				$('#c-fio').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Новый клиент внесён.');
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
				find:$.trim($('#find')._search('val')),
				dolg:$('#dolg').val(),
				worker:$('#worker').val(),
				note:$('#note').val(),
				zayav_cat:$('#zayav_cat').val(),
				product_id:$('#product_id').val()
			},
			loc = '';
		$('.filter')[v.find ? 'hide' : 'show']();

		if(v.find) loc += '.find=' + escape(v.find);
		else {
			if(v.dolg > 0) loc += '.dolg=' + v.dolg;
			if(v.worker > 0) loc += '.worker=' + v.worker;
			if(v.note > 0) loc += '.note=' + v.note;
			if(v.zayav_cat > 0) loc += '.zayav_cat=' + v.zayav_cat;
			if(v.product_id > 0) loc += '.product_id=' + v.product_id;
		}
		VK.callMethod('setLocation', hashLoc + loc);

		_cookie('client_find', escape(v.find));
		_cookie('client_worker', v.worker);
		_cookie('client_dolg', v.dolg);
		_cookie('client_note', v.note);
		_cookie('client_zayav_cat', v.zayav_cat);
		_cookie('client_product_id', v.product_id);

		return v;
	},
	clientSpisok = function() {
		var result = $('.result');
		if(result.hasClass('busy'))
			return;
		result.addClass('busy');
		$.post(AJAX_MAIN, clientFilter(), function(res) {
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
			status:$('#status').length ? $('#status').val() : 0,
			zpe:$('#zp_expense').length ? $('#zp_expense').val() : 0,
			zpe_worker:$('#zpe_worker').length ? $('#zpe_worker').val() : 0,
			account:$('#account').length ? $('#account').val() : 0
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
			head = 'Заключение',
			but = 'Заключить договор',
			cutHead = 'Указать даты очередных платежей',
			cutn, //Номер платежа при его разбивке на части
			cutd, //Диалог разбивки платежа
			cutArr;
		switch(v) {
			default: v = 'create';
			case 'create': break;
			case 'edit':
				head = 'Изменение данных';
				but = 'Применить';
				break;
			case 'reneg':
				head = 'Перезаключение';
				but = 'Перезаключить договор';
				break;
		}
		var html = '<table class="zayav-dogovor">' +
				'<tr><td class="label">Фио клиента:<td><input type="text" id="fio" value="' + DOG.fio + '" />' +
				'<tr><td class="label">Адрес:<td><input type="text" id="adres" value="' + DOG.adres + '" />' +
				'<tr><td class="label">Паспорт:' +
					'<td>Серия:<input type="text" id="pasp_seria" maxlength="8" value="' + DOG.pasp_seria + '" />' +
						'Номер:<input type="text" id="pasp_nomer" maxlength="10" value="' + DOG.pasp_nomer + '" />' +
				'<tr><td><td><span class="l">Прописка:</span><input type="text" id="pasp_adres" maxlength="100" value="' + DOG.pasp_adres + '" />' +
				'<tr><td><td><span class="l">Кем выдан:</span><input type="text" id="pasp_ovd" maxlength="100" value="' + DOG.pasp_ovd + '" />' +
				'<tr><td><td><span class="l">Когда выдан:</span><input type="text" id="pasp_data" maxlength="100" value="' + DOG.pasp_data + '" />' +
				'<tr><td class="label">Номер договора:<td><input type="text" id="nomer" maxlength="6" value="' + DOG.nomer + '" />' +
				'<tr><td class="label">Дата заключения:<td><input type="hidden" id="data_create" value="' + (DOG.data_create ? DOG.data_create : '') + '" />' +
				'<tr><td class="label">Сумма по договору:<td><input type="text" id="sum" class="money" maxlength="11" value="' + (DOG.sum ? DOG.sum : '') + '" /> руб.' +
				'<tr><td class="label">Авансовый платёж:' +
					'<td><input type="text" id="avans" class="money" maxlength="11" value="' + (DOG.avans ? DOG.avans : '') + '"' + (DOG.avans_owner ? '' : ' disabled') + ' /> руб. ' +
						'<span class="prim">(не обязательно)</span>' +
(v == 'reneg' ? '<tr><td class="label">Причина перезаключения:<td><input type="text" id="reason" />' : '') +
				'<tr><td colspan="2"><a id="cut"></a>' +
				'<tr><td colspan="2">' +
					'<a id="preview">Предварительный просмотр договора</a>' +
					'<form action="' + AJAX_MAIN + '" method="post" id="preview-form" target="_blank"></form>' +
				'</table>',
			dialog = _dialog({
				width:480,
				top:10,
				head:head + ' договора',
				content:html,
				butSubmit:but,
				submit:submit
			});
		$('#data_create')._calendar({lost:1});
		$('#cut')
			.click(cutCreate)
			.vkHint({
				width:180,
				msg:'Разбить оставшуюся сумму платежа по заявке на части и создать по ним напоминания.',
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
			if(!send.fio) err('Не указано Фио клиента', 'fio', type);
			else if(!REGEXP_NUMERIC.test(send.nomer) || send.nomer == 0) err('Некорректно указан номер договора', 'nomer', type);
			else if(!REGEXP_CENA.test(send.sum) || send.sum == 0) err('Некорректно указана сумма по договору', 'sum', type);
			else if(send.avans && !REGEXP_CENA.test(send.avans)) err('Некорректно указан авансовый платёж', 'avans', type);
			else if(v == 'reneg' && !send.reason) err('Не указана причина перезаключения договора', 'reason', type);
			else return send;
			return false;
		}
		function cutCreate() {
			var sum = REGEXP_CENA.test($('#sum').val()) ? $('#sum').val() : 0,
				avans = REGEXP_CENA.test($('#avans').val()) ? $('#avans').val() : 0,
				s = sum - avans,
				html =
					'<table class="cut-money">' +
						'<tr><td class="label">Исходная сумма:<td><u>' + s + '</u> руб.' +
						'<tr id="cut-add"><td><td><a>Добавить поле</a>' +
						'<tr><td class="label">Итоговая сумма:<td><b id="cut-itog">' + s + '</b> руб.' +
					'</table>';
			cutd = _dialog({
				head:'Разбивка платежа на части',
				content:html,
				butSubmit:'Применить',
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
				html = '<tr><td class="label">' + cutn + '-й платёж:' +
						   '<td><input type="text" class="cutsum" id="i' + cutn + '" maxlength="7" value="' + (arr ? arr[0] : '') + '"> руб. ' +
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
					msg:'<span class="red">Некорректно заполнено поле платежа</span>',
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
				'Указан' + _end(len, ['а', 'ы']) + ' дат' + _end(len, ['а', 'ы']) + ' для ' + len + _end(len, ['-го', '-х', '-и']) +
				' платеж' + _end(len, ['а', 'ей']) + '. <u>Изменить</u><div class="img_del' + _tooltip('Отменить разбивку', -61) + '</div>');
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
						_msg('Договор заключен.');
						document.location.reload();
					} else {
						dialog.abort();
						err(res.text);
					}
				}, 'json');
			}
		}
	},
	dogovorDestroy = function() {
		var html =
				'<div class="_info">' +
					'При расторжении договора авансовый платёж возвращается клиенту в виде возврата наличным платежём полной суммой. ' +
					'Начисление по договору удаляется.' +
				'</div>' +
				'Договор <b>№' + DOG.nomer + '</b> будет расторгнут.<br />' +
				(DOG.avans ? 'Авансовый платёж в сумме <b>' + DOG.avans + '</b> руб. будет возвращён клиенту.' : ''),
			dialog = _dialog({
				head:'Расторжение договора',
				butSubmit:'Применить',
				content:html,
				submit:submit
			});
		function submit() {
			var send = {
				op:'dogovor_terminate',
				zayav_id:ZAYAV.id
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('Договор был расторгнут.');
					location.reload();
				} else
					dialog.abort();
			}, 'json');
		}
	},
	zayavInfoMoneyUpdate = function() {
		var send = {
			op:'zayav_info_money_update',
			zayav_id:ZAYAV.id
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				$('b.acc').html(res.acc);
				$('.acc_tr')[(!res.acc ? 'add' : 'remove') + 'Class']('dn');
				$('b.opl').html(res.opl);
				$('.opl_tr')[(!res.opl ? 'add' : 'remove') + 'Class']('dn');
				$('.dopl')
					[(!res.dolg ? 'add' : 'remove') + 'Class']('dn')
					.html((res.dolg > 0 ? '+' : '') + res.dolg);
				$('.acc-sum').html(res.acc != 0 ? 'Общая сумма начислений: <b>' + res.acc + '</b> руб.' : 'Начислений нет.');
				$('.zrashod').html(res.expense);
			}
		}, 'json');
	},

	incomeSpisok = function() {
		var send = {
			op:'income_spisok',
			day:$('.selected').val(),
			invoice_id:$('#filter_invoice_id').val(),
			worker_id:window.WORKERS ? $('#worker_id').val() : 0,
			deleted:$('#deleted').val()
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
		$('.income_choice_sum').html(c ? 'Выбран' + _end(c, ['', 'о']) + ' <b>' + c + '</b> платеж' + _end(c, ['', 'а', 'ей']) + ' на сумму <b>' + sum + '</b> руб.' : '');
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
			html = '&nbsp;',
			count = 0,
			sum = 0,
			ids = [];
		for(n = 0; n < check.length; n++) {
			sp = check.eq(n);
			if(sp.attr('id') == 'salary_all_check' || sp.find('input').val() == 0)
				continue;
			count++;
			tr = sp.parent().parent();
			ids.push(tr.attr('val'));
			sum += tr.find('.sum').html() * 1;
		}
		if(count)
			html = 'Выбран' + _end(count, ['а', 'о']) + ' <b>' + count + '</b> запис' + _end(count, ['ь', 'и', 'ей']) +
				' на сумму <b id="salary-sum">' + sum + '</b> руб.' +
				'<a class="salary-list-create" val="' + ids.join() + '">Создать лист выдачи з/п</a>';
		$('#salary-sel').html(html);
	},
	salarySpisok = function() {
		if($('.headName').hasClass('_busy'))
			return;
		var send = {
			op:'salary_spisok',
			worker_id:WORKER_ID,
			year:$('#year').val(),
			mon:$('#salmon').val()
		};
		$('.headName').addClass('_busy');
		$.post(AJAX_MAIN, send, function (res) {
			$('.headName').removeClass('_busy');
			if(res.success) {
				MON = send.mon * 1;
				YEAR = send.year;
				$('.headName em').html(MONTH_DEF[MON] + ' ' + YEAR);
				$('#spisok').html(res.html);
				$('#monthList').html(res.month);
			}
		}, 'json');
	},
	salaryDaysGet = function(m, y) {
		var send = {
			op:'salary_days_get',
			worker_id:WORKER_ID,
			mon:m,
			year:y
		};
		$('#days-sel').addClass('_busy');
		$.post(AJAX_MAIN, send, function(res) {
			$('#days-sel').removeClass('_busy');
			if(res.success)
				SALARY_DAYS = res.days;
		}, 'json');
	},

	dayFirst = function(year, mon) {//номер первой недели в месяце
		var first = new Date(year, mon - 1, 1).getDay();
		return first == 0 ? 7 : first;
	},
	dayCount = function(year, mon) {//количество дней в месяце
		mon--;
		if(mon == 0) {
			mon = 12;
			year--;
		}
		return 32 - new Date(year, mon, 32).getDate();
	};

$.fn.clientSel = function(o) {
	var t = $(this);
	o = $.extend({
		width:270,
		add:null,
		client_id:t.val() || 0,
		func:function() {},
		funcAdd:function() {}
	}, o);

	if(o.add)
		o.add = function() {
			clientAdd(function(res) {
				var arr = [];
				arr.push(res);
				t._select(arr);
				t._select(res.uid);
				o.funcAdd(res);
			});
		};

	t._select({
		width:o.width,
		title0:'Начните вводить данные клиента...',
		spisok:[],
		write:1,
		nofind:'Клиентов не найдено',
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

	t.html('<div class="_product-list"><a class="add">Добавить поле</a></div>');
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
					'<td class="td"><input type="text" id="' + attr_count + '" value="' + (v[2] || '') + '" class="count" maxlength="3" /> шт.' +
									(num > 1 ? '<div class="img_del"></div>' : '') +
				'</table>';
		add.before(html);
		var ptab = $('#ptab' + num);
		ptab.find('.img_del').click(function() {
			ptab.remove();
		});
		$('#' + attr_id)._select({
			width:119,
			title0:'Не указано',
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
			title0:'Подвид не указан',
			spisok:PRODUCT_SUB_SPISOK[id],
			func:function() {
				$('#' + attr_count).focus();
			}
		});
	}
	return t;
};
$.fn.zayavExpense = function(o) {
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
					sum = _cena(u.find('.zrsum').val()),
					dop = '';
				if(cat_id == 0)
					continue;
				if(!sum)
					return 'sum_error';
				if(ZAYAVEXPENSE_TXT[cat_id])
					dop = u.find('.zrtxt').val();
				else if(ZAYAVEXPENSE_WORKER[cat_id])
					dop = $('#' + attr + 'worker').val();
				send.push(cat_id + ':' +
						  dop + ':' +
						  sum + ':' +
						  $('#' + attr + 'list').val());
			}
			return send.join();
		}
	}

	t.html('<div class="_zayav-rashod"></div>');
	var zr = t.find('._zayav-rashod');

	if(typeof o == 'object')
		for(n = 0; n < o.length; n++)
			itemAdd(o[n])

	itemAdd();

	function itemAdd(v) {
		if(!v)
			v = [
				0, //0 - категория
				'',//1 - описаие или id сотрудника
				'',//2 - сумма
				0  //3 - лист зп
			];
		var attr = id + num,
			attr_cat = attr + 'cat',
			attr_worker = attr + 'worker',
			attr_list = attr + 'list',
			html = '<table id="ptab'+ num + '" class="ptab" val="' + num + '"><tr>' +
						'<td><input type="hidden" id="' + attr_cat + '" value="' + v[0] + '" />' +
						'<td class="tddop">' +
							(v[0] && ZAYAVEXPENSE_TXT[v[0]] ? '<input type="text" class="zrtxt" placeholder="описание не указано" tabindex="' + (num * 10 - 1) + '" value="' + v[1] + '" />' : '') +
							(v[0] && ZAYAVEXPENSE_WORKER[v[0]] ? '<input type="hidden" id="' + attr_worker + '" value="' + v[1] + '" />' : '') +
						'<td class="tdsum' + (v[0] ? '' : ' dn') + '">' +
							'<input type="text" class="zrsum" maxlength="9"' + (v[3] ? ' disabled' : '') + ' tabindex="' + (num * 10) + '" value="' + v[2] + '" />руб.' +
							'<input type="hidden" id="' + attr_list + '" value="' + v[3] + '" />' +
					'</table>';
		zr.append(html);
		var ptab = $('#ptab' + num),
			tddop = ptab.find('.tddop'),
			zrsum = ptab.find('.zrsum');
		$('#' + attr_cat)._select({
			width:120,
			disabled:v[3],
			title0:'Категория',
			spisok:ZAYAVEXPENSE_SPISOK,
			func:function(id) {
				ptab.find('.tdsum')[(id > 0 ? 'remove' : 'add') + 'Class']('dn');
				if(ZAYAVEXPENSE_TXT[id]) {
					tddop.html('<input type="text" class="zrtxt" placeholder="описание не указано" tabindex="' + (num * 10 - 11) + '" />');
					tddop.find('.zrtxt').focus();
				} else if(ZAYAVEXPENSE_WORKER[id]) {
					tddop.html('<input type="hidden" id="' + attr_worker + '" />');
					$('#' + attr_worker)._select({
						width:150,
						title0:'Сотрудник',
						spisok:WORKER_SPISOK,
						func:function(v) {
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
					itemAdd();
			}
		});
		if(v[0] && ZAYAVEXPENSE_WORKER[v[0]])
			$('#' + attr_worker)._select({
				width:150,
				disabled:v[3],
				title0:'Сотрудник',
				spisok:WORKER_SPISOK,
				func:function(v) {
					zrsum.focus();
				}
			});
		num++;
	}
	return t;
};

$(document)
	.ajaxSuccess(function(event, request, settings) {
		if(request.responseJSON.pin) {
			var html = '<table class="setup-tab">' +
					'<tr><td colspan="2"><div class="_info">Истекло время действия пин-кода. Требуется подтверждение.</div>' +
					'<tr><td class="label">Пин-код:<td><input id="tpin" type="password" maxlength="10" />' +
				'</table>',
				dialog = _dialog({
					width:250,
					head:'Подтверждение пин-кода',
					content:html,
					butSubmit:'Подтвердить',
					butCancel:'',
					submit:submit
				});
			$('#tpin').focus().keyEnter(submit);
		}
		function submit() {
			var send = {
				op:'pin_enter',
				pin:$.trim($('#tpin').val())
			};
			if(!send.pin) { err('Не заполнено поле'); $('#tpin').focus(); }
			else if(send.pin.length < 3) { err('Длина пин-кода от 3 до 10 символов'); $('#tpin').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success)
						dialog.close();
					else if(res.max)
						location.reload();
					else {
						dialog.abort();
						err(res.text);
						$('#tpin').val('').focus();
					}
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<span class="red">' + msg + '</span>',
				top:-47,
				left:62,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('change', '._attach input', function() {
		_cookie('_attached', 0);
		var t = $(this), att = t;
		while(!att.hasClass('_attach'))
			att = att.parent();
		var form = att.find('form'),
			f = att.find('.form'),
			timer = setInterval(start, 500);
		f.addClass('_busy');
		form.submit();
		function start() {
			var c = _cookie('_attached');
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
			f.after('<div class="red">Некорректный файл.</div>');
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
				zayavInfoMoneyUpdate();
				t.after('<tr class="deleted" val="' + send.id + '">' +
					'<td colspan="4"><div>Начисление удалено. <a class="accrual-rest">Восстановить</a></div>');
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
				zayavInfoMoneyUpdate();
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
	.on('click', '#client #filter_clear', function() {
		$('#find')._search('clear');
		$('#dolg')._check(0);
		$('#worker')._check(0);
		$('#note')._check(0);
		$('#zayav_cat')._select(0);
		$('#product_id')._select(0);
		clientSpisok();
	})

	.on('click', '.zayav_add', function() {
		var html =
			'<div class="zayav-add">' +
				'<div class="item zakaz_add"><b>Заказ</b>Новый заказ для продажи изделий без установки.<br />При необходимости в будущем заказ можно будет перевести в Установку.</div>' +
				'<div class="item zamer_add"><b>Замер</b>Новая заявка на замер. Указывается дата и время замера. Заявка автоматически попадает в напоминания. Успешный замер переносится на заключение договора.</div>' +
				'<div class="item set_add"><b>Установка</b>Новая заявка на установку изделий.' +
			'</div>',
			dialog = _dialog({
				width:370,
				top:30,
				head:'Выберите категорию заявки',
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
		if($('#status').length)
			$('#status').rightLink(0);
		$('#product_id')._select(0);
		if($('#zp_expense').length) {
			$('#zp_expense')._radio(0);
			$('#zpe_worker_select').addClass('dn');
			$('#zpe_worker')._select(0);
			$('#account')._check(0);
		}
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
					'<tr><td class="label">Клиент:' +
						'<td><INPUT type="hidden" id="client_id" value="' + CLIENT.id + '">' +
							'<b>' + CLIENT.fio + '</b>' +
					'<tr><td class="label topi">Изделие:<td id="product">' +
					'<tr><td><td><input type="text" id="zakaz_txt" placeholder="либо укажите содержание заказа вручную.." maxlength="300">' +
					'<tr><td class="label top">Заметка:	<td><textarea id="comm"></textarea>' +
					'</table>',
			dialog = _dialog({
				width:550,
				top:30,
				head:'Внесение нового заказа',
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
			if(send.client_id == 0) msg = 'Не выбран клиент';
			else if(!send.product && !send.zakaz_txt) msg = 'Необходимо выбрать изделие или вписать заказ вручную';
			else if(send.product == 'count_error') msg = 'Некорректно введено количество изделий';
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Заявка внесена');
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
							'Заказ ожидает выполнения' +
							'<div class="about">Возобновление работы по заказу.</div>' +
						'</div>'
					: '') +
						'<div class="st c2" val="2">' +
							'Заказ выполнен' +
							'<div class="about">Все начисления произведены, изделия переданы клиенту.</div>' +
							'<div class="label">Уточните день выполнения заказа:</div>' +
							'<input type="hidden" id="day" value="' + (ZAYAV.status_day || '') + '">' +
						'</div>' +
					(ZAYAV.status != 3 ?
						'<div class="st c3" val="3">' +
							'Заказ отменён' +
							'<div class="about">Отмена заказа по какой-либо причине.</div>' +
						'</div>'
					: '') +
				'</div>',
			dialog = _dialog({
				top:30,
				width:300,
				head:'Изменение статуса заказа',
				content:html,
				butSubmit:'',
				butCancel:'Закрыть'
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
					_msg('Данные изменены!');
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
				head:'Таблица замеров',
				load:1,
				butSubmit:'',
				butCancel:'Закрыть'
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
				'<tr><td class="label">Клиент:' +
					'<td><INPUT type="hidden" id="client_id" value="' + CLIENT.id + '">' +
						'<b>' + CLIENT.fio + '</b>' +
				'<tr><td class="label topi">Изделие:<td id="product">' +
				'<tr><td class="label">Адрес проведения замера:' +
					'<td><INPUT type="text" id="adres" maxlength="100" />' +
						'<INPUT type="hidden" id="homeadres" />' +
				'<tr><td class="label">Дата и время замера:<td class="zayav-zamer-dtime">' +
				'<tr><td class="label">Длительность замера:<td><INPUT TYPE="hidden" id="zamer_duration" value="30" />' +
				'<tr><td class="label top">Заметка:	<td><textarea id="comm"></textarea>' +
			'</table>',
			dialog = _dialog({
				width:550,
				top:30,
				head:'Внесение новой заявки на замер',
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
			msg:'Совпадает с адресом проживания',
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
			if(send.client_id == 0) err('Не выбран клиент');
			else if(!send.product) err('Не указано изделие');
			else if(send.product == 'count_error') err('Некорректно введено количество изделий');
			else if(!send.adres) err('Не указан адрес');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Заявка внесена');
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
				head:'Изменение статуса замера',
				load:1,
				butSubmit:'',
				butCancel:'Закрыть'
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
						'Указать новое время' +
						'<div class="about">Замер будет перенесён на другое время.</div>' +
					'</div>' +
					'<div class="st c2" val="2">' +
						'Замер выполнен' +
						'<div class="about">Замер выполнен успешно. Заявка будет переведена на заключение договора.</div>' +
					'</div>' +
					(res.status != 3 ?
						'<div class="st c3" val="3">' +
							'Отмена' +
							'<div class="about">Отмена заявки по какой-либо причине.</div>' +
						'</div>'
					: '') +
					'<table class="zstab">' +
						'<tr><td class="label">Новое время:<td class="zayav-zamer-dtime">' +
						'<tr><td class="label">Длительность:' +
							'<td><INPUT TYPE="hidden" id="zamer_duration" value="' + res.dur + '" />' +
								'<a class="zamer_table" val="' + id + '">Таблица замеров</a>' +
						'<tr><td><td><div class="vkButton"><button>Сохранить</button></div>' +
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
					_msg('Данные изменены!');
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
					'<tr><td class="label">Клиент:' +
						'<td><INPUT type="hidden" id="client_id" value="' + CLIENT.id + '">' +
						'<b>' + CLIENT.fio + '</b>' +
					'<tr><td class="label topi">Изделие:<td id="product">' +
					'<tr><td class="label">Адрес установки:' +
						'<td><INPUT type="text" id="adres" maxlength="100" />' +
							'<INPUT type="hidden" id="homeadres" />' +
					'<tr><td class="label top">Заметка:	<td><textarea id="comm"></textarea>' +
				'</table>',
			dialog = _dialog({
				width:550,
				top:30,
				head:'Внесение новой заявки на установку',
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
				},
				funcAdd:function(c) {
					HOMEADRES = c.adres;
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
			msg:'Совпадает с адресом проживания',
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
			if(send.client_id == 0) msg = 'Не выбран клиент';
			else if(!send.product) msg = 'Не указано изделие';
			else if(send.product == 'count_error') msg = 'Некорректно введено количество изделий';
			else if(!send.adres) msg = 'Не указан адрес';
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Заявка внесена');
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
							'Ожидает установку' +
							'<div class="about">Возобновление работы по заявке.</div>' +
						'</div>'
					: '') +
						'<div class="st c2" val="2">' +
							'Установка выполнена' +
							'<div class="about">Произведена установка всех изделий. Не забудьте расписать расходы по заявке и проверьте начисления.</div>' +
							'<div class="label">Уточните день выполнения установки:</div>' +
							'<input type="hidden" id="day" value="' + (ZAYAV.status_day || '') + '">' +
						'</div>' +
					(ZAYAV.status != 3 ?
						'<div class="st c3" val="3">' +
							'Заявка отменена' +
							'<div class="about">Отмена заявки по какой-либо причине.</div>' +
						'</div>'
					: '') +
					'</div>',

			dialog = _dialog({
				top:30,
				width:360,
				head:'Изменение статуса установки',
				content:html,
				butSubmit:'',
				butCancel:'Закрыть'
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
					_msg('Данные изменены!');
					document.location.reload();
				} else
					p.removeClass('busy');
			}, 'json');
		});
	})

	.on('click', '.refund-del', function() {
		var t = $(this),
			dialog = _dialog({
				top:110,
				width:250,
				head:'Удаление',
				content:'<CENTER>Подтвердите удаление возврата.</CENTER>',
				butSubmit:'Удалить',
				submit:submit
			});
		while(t[0].tagName != 'TR')
			t = t.parent();
		function submit() {
			var send = {
				op:'refund_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					zayavInfoMoneyUpdate();
					_msg('Удалено.');
					$('#income_spisok').html(res.html);
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '.invoice_set', function() {
		var t = $(this),
			invoice_id = t.attr('val'),
			html =
				'<table class="_dialog-tab">' +
					'<tr><td class="label">Счёт:<td><b>' + INVOICE_ASS[invoice_id] + '</b>' +
					'<tr><td class="label">Сумма:<td><input type="text" class="money" id="sum" maxlength="11" /> руб.' +
				'</table>',
			dialog = _dialog({
				width:320,
				head:'Установка текущей суммы счёта',
				content:html,
				butSubmit:'Установить',
				submit:submit
			});

		$('#sum').focus().keyEnter(submit);

		function submit() {
			var send = {
				op:'invoice_set',
				invoice_id:invoice_id,
				sum:$('#sum').val()
			};
			if(!REGEXP_CENA.test(send.sum)) {
				dialog.err('Некорректно указана сумма');
				$('#sum').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('#invoice-spisok').html(res.i);
						dialog.close();
						_msg('Текущая сумма счёта установлена');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '.invoice_close', function() {
		var t = $(this),
			invoice_id = t.attr('val'),
			ost = _cena(CASH_SUM[invoice_id]),
			html =
				'<table class="_dialog-tab">' +
					'<tr><td class="label">Счёт:<td><b>' + INVOICE_ASS[invoice_id] + '</b>' +
				(ost ?
					'<tr><td class="label">Остаток:<td><b>' + CASH_SUM[invoice_id] + '</b> руб.' +
					'<tr><td class="label">Перевести остаток на счёт:<td><input type="hidden" id="invoice_to" />'
				: '') +
				'</table>',
			dialog = _dialog({
				width:420,
				head:'Закрытие счёта',
				content:html,
				butSubmit:'Закрыть счёт',
				submit:submit
			});

		$('#invoice_to')._select({
			width:200,
			title0:'Счёт не выбран',
			spisok:INVOICE_SPISOK
		});


		function submit() {
			var send = {
				op:'invoice_close',
				invoice_id:invoice_id,
				invoice_to:ost ? _num($('#invoice_to').val()) : 0
			};
			if(ost && !send.invoice_to)
				dialog.err('Не указан номер счёта-получателя');
			else if(ost && send.invoice_to == invoice_id)
				dialog.err('Выберите другой счёт');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('#invoice-spisok').html(res.i);
						dialog.close();
						_msg('Счёт закрыт');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#report.invoice .img_note', function() {//просмотр операций со счётом
		var dialog = _dialog({
			top:20,
			width:570,
			head:'История операций со счётом',
			load:1,
			butSubmit:'',
			butCancel:'Закрыть'
		});
		var send = {
			op:'invoice_history',
			invoice_id:$(this).attr('val')
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				dialog.content.html('<div class="invoice-history">' + res.html + '</div>');
				$('#ih-year')._select({
					width:50,
					spisok:[{uid:2014,title:2014},{uid:2015,title:2015}],
					func:ostatokSpisok
				});
				$('#ih-mon')._select({
					width:80,
					spisok:MON_SPISOK,
					func:ostatokSpisok
				});
			} else
				dialog.loadError();
		}, 'json');
		function ostatokSpisok(v, id) {
			var p = $('.invoice-history #dopLinks'),
				send = {
					op:'invoice_history_ostatok',
					invoice_id:$('#invoice_history_id').val(),
					year:id == 'ih-year' ? v : $('#ih-year').val(),
					mon:id == 'ih-mon' ? v : $('#ih-mon').val()
				};
			if(p.hasClass('_busy'))
				return;
			p.addClass('_busy');
			$.post(AJAX_MAIN, send, function(res) {
				p.removeClass('_busy');
				if(res.success)
					$('#ih-spisok').html(res.html);
			}, 'json');
		}
	})
	.on('click', '.invoice-history .full,.invoice-history .ostatok', function() {//просмотр операций подробно либо по дням
		var t = $(this),
			p = t.parent();
		if(t.hasClass('sel'))
			return;
		p.find('.sel').removeClass('sel');
		t.addClass('sel');
		p.addClass('_busy');

		var but = t.hasClass('full') ? 'full' : 'ostatok',
			send = {
				op:'invoice_history_' + but,
				invoice_id:$('#invoice_history_id').val(),
				year:$('#ih-year').val(),
				mon:$('#ih-mon').val()
			};
		$.post(AJAX_MAIN, send, function(res) {
			p.removeClass('_busy');
			if(res.success) {
				$('#ih-spisok').html(res.html);
				$('#ih-data')[(but == 'full' ? 'add' : 'remove') + 'Class']('dn');
			}
		}, 'json');
	})
	.on('click', '.invoice-history .to-day', function() {
		var p = $('.invoice-history #dopLinks');
		if(p.hasClass('_busy'))
			return;
		p.find('a.sel').removeClass('sel');
		p.find('a.full').addClass('sel');
		p.addClass('_busy');

		var t = $(this),
			send = {
				op:'invoice_history_full',
				invoice_id:$('#invoice_history_id').val(),
				day:t.attr('val')
			};
		$.post(AJAX_MAIN, send, function(res) {
			p.removeClass('_busy');
			if(res.success) {
				$('#ih-spisok').html(res.html);
				$('#ih-data').addClass('dn');
			}
		}, 'json');
	})
	.on('click', '.invoice-history .ih-clear', function() {
		var p = $('.invoice-history #dopLinks');
		if(p.hasClass('_busy'))
			return;
		p.addClass('_busy');

		var t = $(this),
			send = {
				op:'invoice_history_full',
				invoice_id:$('#invoice_history_id').val()
			};
		$.post(AJAX_MAIN, send, function(res) {
			p.removeClass('_busy');
			if(res.success) {
				$('#ih-spisok').html(res.html);
				$('#ih-data').addClass('dn');
			}
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
			head:'Просмотр подтверждённых переводов',
			load:1,
			butSubmit:'',
			butCancel:'Закрыть'
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
	.on('click', '.transfer-spisok .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				head:'Удаление перевода',
				content:'<center>Подтвердите удаление перевода.</center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			var send = {
				op:'transfer_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					$('#invoice-spisok').html(res.i);
					$('.transfer-spisok').html(res.t);
					dialog.close();
					_msg('Перевод удалён.');
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.transfer-spisok ._next', function() {
		var next = $(this),
			send = {
				op:'transfer_spisok',
				page:next.attr('val')
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
	.on('click', '.income-confirm', function() {
		var dialog = _dialog({
			top:20,
			width:480,
			head:'Подтверждение платежей',
			load:1,
			butSubmit:'Подтвердить',
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
				err('Платежи не выбраны');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('#confirm-info').html(res.confirm);
						$('#invoice-spisok').html(res.i);
						dialog.close();
						_msg('Платежи подтверждены.');
					} else
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
			head:'Подтверждение переводов',
			load:1,
			butSubmit:'Подтвердить',
			submit:submit
		});
		var send = {
			op:'transfer_confirm_get'
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				var html = res.html + '<div class="transfer-about">Описание: <input type="text" id="about" /></div>';
				dialog.content.html(html);
				$('#about').keyEnter(submit);
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
				err('Платежи не выбраны');
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
					$('.transfer-spisok').html(res.t);
					dialog.close();
					_msg('Переводы подтверждены.');
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
			head:'Просмотр платежей',
			load:1,
			butSubmit:'',
			butCancel:'Закрыть'
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
	            '<tr><td class="label">Клиент:<td>' + OPL.client_fio +
				'<tr><td class="label">Заявка:' +
					'<td><input type="hidden" id="zayav_id" value="' + (OPL.zayav_id ? OPL.zayav_id : 0) + '">' +
						(OPL.zayav_id ? '<b>' + OPL.zayav_head + '</b>' : '')
  : '') +
				'<tr><td class="label">На счёт:<td><input type="hidden" id="invoice_id" />' +
					'<a href="' + URL + '&p=setup&d=invoice" class="img_edit' + _tooltip('Настройка счетов', -58) + '</a>' +
				'<tr class="tr_confirm dn"><td class="label">Подтверждение:<td><input type="hidden" id="confirm">' +
				'<tr><td class="label">Сумма:<td><input type="text" id="sum" class="money" maxlength="11"> руб.' +
				'<tr><td class="label">Комментарий:<td><input type="text" id="prim" maxlength="100">' +
			(window.ZAYAV && REMIND.active ?
				'<tr><td><td>' +
					'<div class="_info">' +
						'<b>Есть ' + REMIND.active + ' активн' + _end(REMIND.active, ['ое', 'ых']) + ' напоминани' + _end(REMIND.active, ['е', 'я', 'й']) + '!</b>' +
						'<br />' +
						'<br />' +
						'<input type="hidden" id="remind_active" value="0" />' +
					'</div>'
			: '') +
			'</table>';
		var dialog = _dialog({
			width:440,
			head:'Внесение платежа',
			content:html,
			submit:submit
		});
		$('#sum').focus();
		$('#sum,#prim').keyEnter(submit);
		if(OPL.zayav_spisok)
			$('#zayav_id')._select({
				width:210,
				title0:'Не указана',
				spisok:OPL.zayav_spisok
			});
		$('#invoice_id')._select({
			width:180,
			title0:'Не указан',
			spisok:INVOICE_SPISOK,
			func:function(uid) {
				$('#sum').focus();
				$('.tr_confirm')[(INVOICE_CONFIRM_INCOME[uid] ? 'remove' : 'add') + 'Class']('dn');
				$('#confirm')._check(0);
			}
		});
		$('#confirm')._check();
		$('#confirm_check').vkHint({
			width:210,
			msg:'Установите галочку, если платёж нужно внести, но требуется подтверждение о его поступлении на счёт.',
			top:-96,
			left:-100
		});
		if(window.ZAYAV && REMIND.active) {
			$('#remind_active')._check({
				name:'отметить выполненным' + _end(REMIND.active, ['', 'и'])
			});
		}
		function submit() {
			var send = {
				op:'income_add',
				from:OPL.from,
				invoice_id:_num($('#invoice_id').val()),
				confirm:$('#confirm').val(),
				sum:$('#sum').val(),
				zayav_id:$('#zayav_id').val() || 0,
				client_id:OPL.client_id || 0,
				prim:$.trim($('#prim').val()),
				remind_active:window.REMIND ? _num($('#remind_active').val()) : 0
			};
			if(!send.invoice_id)
				dialog.err('Не указан счёт');
			else if(!_cena(send.sum)) {
				dialog.err('Некорректно указана сумма.');
				$('#sum').focus();
			} else if($('#zayav_id').length && send.zayav_id == 0 && !send.prim) {
				dialog.err('Если не выбрана заявка, необходимо указать комментарий');
				$('#prim').focus();
			} else if(!$('#zayav_id').length && !send.prim) {
				dialog.err('Необходимо указать комментарий');
				$('#prim').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Платёж успешно внесён');
						switch(OPL.from) {
							case 'client':
								$('#income_spisok').html(res.html);
								$('.left:first').html(res.balans);
								break;
							case 'zayav':
								zayavInfoMoneyUpdate();
								$('#income_spisok').html(res.html);
								if(res.remind)
									$('#remind-spisok').html(res.remind);
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
			if(res.success) {
				if(window.ZAYAV)
					zayavInfoMoneyUpdate();
				t.addClass('deleted');
			}
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
			if(res.success) {
				if(window.ZAYAV)
					zayavInfoMoneyUpdate();
				t.removeClass('deleted');
			}
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
				deleted:$('#money_deleted').val(),
				invoice_id:$('#money_invoice_id').val(),
				owner_id:$('#money_owner_id').val(),
				worker_id:$('#money_worker_id').val(),
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
				head:'Редактирование расхода',
				load:1,
				butSubmit:'Сохранить',
				submit:submit
			}),
			id = t.attr('val'),
			send = {
				op:'expense_get',
				id:id
			};
		$.post(AJAX_MAIN, send, function(res) {
			var html = '<table id="expense-add-tab">' +
				'<tr><td class="label">Категория:<td><input type="hidden" id="cat" value="' + res.category + '">' +
				'<tr class="tr-work ' + (EXPENSE_WORKER[res.category] ? '' : 'dn') + '">' +
					'<td class="label">Сотрудник:' +
					'<td><input type="hidden" id="work" value="' + res.worker + '">' +
				'<tr><td class="label">Описание:<td><input type="text" id="about" maxlength="150" value="' + res.about + '">' +
				'<tr><td class="label">Со счёта:<td><input type="hidden" id="invoice" value="' + res.invoice + '">' +
				'<tr><td class="label">Сумма:<td><input type="text" id="sum" class="money" maxlength="8" value="' + res.sum + '"> руб.' +
				'</table>';
			dialog.content.html(html);

			$('#cat')._select({
				width:180,
				title0:'Не указана',
				spisok:EXPENSE_SPISOK,
				func:function(id) {
					$('#work')._select(0);
					$('.tr-work')[(EXPENSE_WORKER[id] ? 'remove' : 'add') + 'Class']('dn');
				}
			});
			$('#about').focus();
			$('#work')._select({
				title0:'Не выбран',
				spisok:WORKER_SPISOK
			});
			$('#invoice')._select({
				title0:'Не выбран',
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
			if(!send.category && !send.about) { err('Выберите категорию или укажите описание.'); $('#about').focus(); }
			else if(!send.invoice) err('Укажите с какого счёта производится оплата.');
			else if(!REGEXP_NUMERIC.test(send.sum)) { err('Некорректно указана сумма.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Расход изменён.');
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
					'После установки ставки сотруднику указанная сумма будет автоматически начисляться ' +
					'на его баланс ежемесячно в определённый день. ' +
					'День начисления может быть выбран в промежутке от 1-го до 28 числа.' +
				'</div>' +
				'<table class="salary-tab">' +
					'<tr><td class="label">Сумма:<td><input type="text" id="sum" class="money" maxlength="11" value="' + (RATE ? RATE : '') + '" /> руб.' +
					'<tr><td class="label">День начисления:<td><input type="text" id="day" maxlength="2" value="' + (RATE ? RATE_DAY : '') + '" />' +
				'</table>',
			dialog = _dialog({
				top:30,
				width:320,
				head:'Установка ставки з/п для сотрудника',
				content:html,
				butSubmit:'Установить',
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
			if(!REGEXP_CENA.test(send.sum)) { err('Некорректно указана сумма.'); $('#sum').focus(); }
			else if(!REGEXP_NUMERIC.test(send.day) || !send.day || send.day > 28) { err('Некорректно указан день.'); $('#day').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						RATE = send.sum;
						RATE_DAY = send.day;
						dialog.close();
						_msg('Установка ставки произведена.');
						salarySpisok();
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
		var n,
			SUMDAY = 300,
			SUMM,
			DAYSEL = '',
			DAYSELARR,
			ddays,
			day_sel_default = 'Выбрать дни',
			html =
				'<table class="salary-tab">' +
					'<tr><td class="label">Месяц:' +
						'<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
							'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
							'<a id="days-sel">' + day_sel_default + '</a>' +
					'<tr><td class="label">Сумма:<td><input type="text" id="sum" class="money" maxlength="8" /> руб.' +
					'<tr><td class="label">Описание:<td><input type="text" id="about" maxlength="50" />' +
				'</table>',
			dialog = _dialog({
				head:'Внесение начисления для сотрудника',
				content:html,
				submit:submit
			});

		salaryDaysGet(MON, YEAR);
		$('#sum').focus().keyup(function() {
			if($(this).val() == SUMM)
				return;
			$('#days-sel').html(day_sel_default);
			DAYSEL = '';
		});
		$('#sum,#about').keyEnter(submit);
		$('#tabmon')._select({
			width:80,
			spisok:MON_SPISOK,
			func:function(v) {
				salaryDaysGet(v, $('#tabyear').val());
				$('#days-sel').html(day_sel_default);
				DAYSEL = '';
				$('#sum').focus();
			}
		});
		$('#tabyear')._select({
			width:60,
			spisok:YEAR_SPISOK,
			func:function(v) {
				salaryDaysGet($('#tabmon').val(), v);
				$('#days-sel').html(day_sel_default);
				DAYSEL = '';
				$('#sum').focus();
			}
		});
		$('#days-sel').click(function() {
			var year = $('#tabyear').val(),
				mon = $('#tabmon').val(),
				df = dayFirst(year, mon),
				dc = dayCount(year, mon),
				html =
					'<div id="days-sel-tab">' +
						'<div id="head-mon">' + MONTH_DEF[mon] + ' ' + year + '</div>' +
						'<table id="days-cal">' +
							'<tr><th>Пн<th>Вт<th>Ср<th>Чт<th>Пт<th>Сб<th>Вс' +
							'<tr>';
			//установка пустых ячеек
			if(df > 1)
				for(n = 0; n < df - 1; n++)
					html += '<td>';
			if(!DAYSEL)
				DAYSELARR = {};
			for(n = 1; n <= dc; n++) {
				html +=
					'<td class="onday' + (SALARY_DAYS[n] ? ' all' : ' to') + (DAYSELARR[n] ? ' sel' : '') + '">' + n +
						(SALARY_DAYS[n] ?
							'<div class="dsum"><b>' + SALARY_DAYS[n] + '</b> руб.</div>'
							:
							'<br /><input type="text" maxlength="5" val="' + n + '" value="' + DAYSELARR[n] + '" />'
						);
				df++;
				if(df == 8 && n != dc) {
					html += "<tr>";
					df = 1;
				}
			}
			html +=	'</table>' +
					'<div id="days-selected"></div>' +
				'</div>';
			ddays = _dialog({
				top:40,
				width:495,
				head:'Выбор дней',
				butSubmit:'Готово',
				content:html,
				submit:daysSubmit
			});
			daysSum();
			$('#days-sel-tab .onday.to').click(function(e) {
				var t = $(this);
				if($(e.target)[0].tagName == 'INPUT')
					return;
				if(t.hasClass('sel'))
					t.removeClass('sel');
				else
					t.addClass('sel')
					 .find('input').focus().val(SUMDAY);
				daysSum();
			});
			$('#days-sel-tab input').keyup(function() {
				var v = $(this).val();
				if(REGEXP_NUMERIC.test(v))
					SUMDAY = v;
				daysSum();
			});
		});
		function daysSum() {
			var days = $('#days-sel-tab .sel'),
				len = days.length,
				inp,
				d,
				v;
			SUMM = 0;
			DAYSEL = [];
			DAYSELARR = {};
			for(n = 0; n < len; n++) {
				inp = days.eq(n).find('input');
				d = inp.attr('val');
				v = inp.val();
				if(!REGEXP_NUMERIC.test(v)) {
					len = 0;
					DAYSEL = '';
					break;
				}
				SUMM += v * 1;
				DAYSEL.push(d + ':' + v);
				DAYSELARR[d] = v;
			}
			DAYSEL = DAYSEL.join();
			$('#days-selected').html(len ? 'Выбран' + _end(len, ['','о']) + ' <b>' + len + '</b> д' + _end(len, ['ень','ня','ней']) +
										   ', сумма: <b>' + SUMM + '</b> руб.'
									 : '');
			return len;
		}
		function daysSubmit() {
			var count = daysSum();
			if(!count) {
				ddays.bottom.vkHint({
					msg:'<SPAN class="red">Не выбраны дни или некорректно заполнены суммы</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-47,
					left:162
				});
				return false;
			}
			$('#days-sel').html('Выбран' + _end(count, ['','о']) + ' ' + count + ' д' + _end(count, ['ень','ня','ней']));
			$('#sum').val(SUMM);
			ddays.close();
			return true;
		}
		function submit() {
			var send = {
				op:'salary_up',
				worker_id:WORKER_ID,
				daysel:DAYSEL,
				sum:$('#sum').val(),
				about:$('#about').val(),
				mon:$('#tabmon').val(),
				year:$('#tabyear').val()
			};
			if(!REGEXP_NUMERIC.test(send.sum)) {
				err('Некорректно указана сумма.');
				$('#sum').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Начисление произведено.');
						salarySpisok();
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
					'<tr><td class="label">Со счёта:<TD><input type="hidden" id="invoice">' +
						'<a href="' + URL + '&p=setup&d=invoice" class="img_edit' + _tooltip('Настройка счетов', -56) + '</a>' +
					'<tr><td class="label">Сумма:' +
						'<td><input type="text" id="sum" class="money" maxlength="8"> руб.' +
							'<span id="isum"></span>' +
					'<tr><td class="label">Описание:<td><input type="text" id="about" maxlength="100">' +
					'<tr><td class="label">Месяц:<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
													'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
				'</table>',
			dialog = _dialog({
				head:'Выдача зарплаты сотруднику',
				content:html,
				submit:submit
			});

		$('#sum').focus();
		$('#invoice')._select({
			title0:'Не выбран',
			spisok:INVOICE_SPISOK,
			func:function(v) {
				$('#sum').focus();
				$('#isum').html(ISUM[v] ? 'max: <b>' + ISUM[v] + '</b>' : '');
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
				sum:_cena($('#sum').val()),
				about:$('#about').val(),
				mon:$('#tabmon').val(),
				year:$('#tabyear').val()
			};
			if(!send.invoice) err('Укажите с какого счёта производится выдача.');
			else if(!send.sum) { err('Некорректно указана сумма.'); $('#sum').focus(); }
			else if(ISUM[send.invoice] && send.sum > ISUM[send.invoice]) { err('Сумма превышает максимально допустимую'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Выдача зарплаты произведена.');
						salarySpisok();
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
					'<tr><td class="label">Сумма:<TD><input type="text" id="sum" class="money" maxlength="8"> руб.' +
					'<tr><td class="label">Описание:<TD><input type="text" id="about" maxlength="100">' +
					'<tr><td class="label">Месяц:<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
																'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
				'</table>',
			dialog = _dialog({
				head:'Внесение вычета из зарплаты',
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
			if(!REGEXP_NUMERIC.test(send.sum)) { err('Некорректно указана сумма.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Вычет произведён.');
						salarySpisok();
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
					'<tr><td class="label">Сумма:<td><input type="text" id="sum" class="money" maxlength="8"> руб.' +
				'</table>',
			dialog = _dialog({
				head:'Установка баланса по зарплате сотрудника',
				content:html,
				butSubmit:'Применить',
				submit:submit
			});

		$('#sum').focus().keyEnter(submit);

		function submit() {
			var send = {
				op:'salary_start_set',
				worker:WORKER_ID,
				sum:$('#sum').val()
			};
			if(!REGEXP_CENA.test(send.sum)) { err('Некорректно указана сумма.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('Установка произведёна.');
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
		var ch = $('.ch');
		for(n = 0; n < ch.length; n++)
			ch.eq(n).find('._check input:first')._check(v);
		salaryCheck();
	})
	.on('click', '.salary-list-create', function() {
		var ids = $(this).attr('val');
		if(!ids)
			return;
		var sum = $('#salary-sum').html(),
			html =
				'<div class="salary-list-tab">' +
					'<div class="_info">' +
						'<b>Создание листа выдачи з/п</b>' +
						'После формирования листа выдачи з/п все ' +
						'выделенные галочками начисления и вычеты станут фиксированными, ' +
						'то есть их нельзя будет изменить.' +
					'</div>' +
					'<table>' +
						'<tr><td class="label r">Сотрудник:<TD>' + WORKER_ASS[WORKER_ID] +
						'<tr><td class="label r">Выбрано записей:<TD>' + ids.split(',').length +
						'<tr><td class="label r">Сумма:<TD>' + sum + ' руб.' +
						'<tr><td class="label r">Месяц:<td>' + MONTH_DEF[MON] + ' ' + YEAR +
					'</table>' +
					'<h1><a class="salary-list" val="' + ids + '">Предварительный просмотр</a></h1>' +
				'</div>',
			dialog = _dialog({
				width:330,
				top:30,
				head:'Создание листа выдачи з/п',
				content:html,
				butSubmit:'Сформировать',
				submit:submit
			});
		function submit() {
			var send = {
				op:'salary_list_create',
				worker_id:WORKER_ID,
				ids:ids,
				sum:sum,
				mon:MON,
				year:YEAR
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('Лист выдачи з/п создан.');
					salarySpisok();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary-list', function() {
		var ids = $(this).attr('val');
		if(!ids)
			return;
		location.href = APP_HTML + '/view/salary_list.php?' + VALUES + '&ids=' + ids;
	})
	.on('click', '.salary .zp_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:110,
				width:250,
				head:'Удаление з/п',
				content:'<CENTER>Подтвердите удаление записи.</CENTER>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			var send = {
				op:'expense_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('Удалено.');
					salarySpisok();
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
				head:'Удаление',
				content:'<CENTER>Подтвердите удаление записи.</CENTER>',
				butSubmit:'Удалить',
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
					_msg('Удалено.');
					salarySpisok();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary .list_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:110,
				width:250,
				head:'Удаление',
				content:'<CENTER>Подтвердите удаление листа выдачи з/п.</CENTER>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			var send = {
				op:'salary_list_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('Удалено.');
					salarySpisok();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary-days', function() {
		var dialog = _dialog({
				width:495,
				top:40,
				head:'Начисления за определённые дни',
				load:1,
				butSubmit:'',
				butCancel:'Закрыть'
			}),
			send = {
				op:'salary_days',
				id:$(this).attr('val')
			};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				var n,
					df = dayFirst(res.year, res.mon),
					dc = dayCount(res.year, res.mon),
					html =
					'<div id="days-sel-tab">' +
						'<div id="head-mon">' + MONTH_DEF[res.mon] + ' ' + res.year + '</div>' +
						'<table id="days-cal">' +
							'<tr><th>Пн<th>Вт<th>Ср<th>Чт<th>Пт<th>Сб<th>Вс' +
							'<tr>';
				//установка пустых ячеек
				if(df > 1)
					for(n = 0; n < df - 1; n++)
						html += '<td>';
				for(n = 1; n <= dc; n++) {
					html +=
						'<td class="onday' + (res.all[n] ? ' all' : '') + (res.sel[n] ? ' sel' : '') + '">' + n +
							(res.all[n] ? '<div class="dsum"><b>' + res.all[n] + '</b> руб.</div>' : '');
					df++;
					if(df == 8 && n != dc) {
						html += "<tr>";
						df = 1;
					}
				}
				html +=	'</table>' +
					'</div>';
				dialog.content.html(html);
			} else
				dialog.loadError();
		}, 'json');
	})
	.on('mouseenter', '.salary .show', function() {
		$(this).removeClass('show');
	})
	.on('click', '.go-report-salary', function() {
		var v = $(this).attr('val').split(':');
		location.href = URL + '&p=report&d=salary&id=' + v[0] + '&mon=' + v[1] + '&acc_id=' + v[2];
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
			$('#find')._search({
				width:602,
				focus:1,
				enter:1,
				txt:'Начните вводить данные клиента',
				func:clientSpisok
			}).inp(C.find);
			$('#buttonCreate').vkHint({
				msg:'<B>Внесение нового клиента в базу.</B><br /><br />' +
					'После внесения Вы попадаете на страницу с информацией о клиенте для дальнейших действий.<br /><br />' +
					'Клиентов также можно добавлять при <A href="' + URL + '&p=zayav&d=add&back=client">создании новой заявки</A>.',
				ugol:'right',
				width:215,
				top:-38,
				left:-250,
				indent:40,
				delayShow:1000
			}).click(clientAdd);
			$('#dolg')._check(clientSpisok);
			$('#dolg_check').vkHint({
				msg:'<b>Список должников.</b><br /><br />' +
					'Выводятся клиенты, у которых баланс менее 0. Также в результате отображается общая сумма долга.',
				ugol:'right',
				width:150,
				top:-6,
				left:-185,
				indent:20,
				delayShow:1000
			});
			$('#worker')._check(clientSpisok);
			$('#note')._check(clientSpisok);
			$('#zayav_cat')._select({
				width:140,
				title0:'Любые заявки',
				spisok:[
					{uid:1,title:'Заказы'},
					{uid:2,title:'Замеры'},
					{uid:3,title:'Договора'},
					{uid:4,title:'Установки'}
				],
				func:clientSpisok
			});
			$('#product_id')._select({
				width:140,
				title0:'Любые изделия',
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
				$('#remind-spisok').css('display', val == 'remind' ? 'block' : 'none');
				$('#comments').css('display', val == 'comm' ? 'block' : 'none');
				$('#histories').css('display', val == 'hist' ? 'block' : 'none');
			});
			$('.cedit').click(function() {
				var html = '<table class="client-add e">' +
					'<tr><td class="label">Имя:<td><input type="text" id="c-fio" maxlength="100" value="' + CLIENT.fio + '">' +
					'<tr><td class="label">Телефон:<td><input type="text" id="c-telefon" maxlength="100" value="' + CLIENT.telefon + '">' +
					'<tr><td class="label">Адрес:<td><input type="text" id="c-adres" maxlength="100" value="' + CLIENT.adres + '">' +
					'<tr><td class="label">Связан с сотрудником:<td><input type="hidden" id="worker_id" value="' + CLIENT.worker_id + '">' +
					'<tr><td><td><b>Паспортные данные:</b>' +
					'<tr><td class="label">Серия:' +
						'<td><input type="text" id="pasp_seria" maxlength="8" value="' + CLIENT.pasp_seria + '">' +
							'<span class="label">Номер:</span><input type="text" id="pasp_nomer" maxlength="10" value="' + CLIENT.pasp_nomer + '">' +
					'<tr><td class="label">Прописка:<td><input type="text" id="pasp_adres" maxlength="100" value="' + CLIENT.pasp_adres + '">' +
					'<tr><td class="label">Кем выдан:<td><input type="text" id="pasp_ovd" maxlength="100" value="' + CLIENT.pasp_ovd + '">' +
					'<tr><td class="label">Когда выдан:<td><input type="text" id="pasp_data" maxlength="100" value="' + CLIENT.pasp_data + '">' +
				'</table>';
				var dialog = _dialog({
					head:'Редактирование данных клиента',
					top:30,
					width:430,
					content:html,
					butSubmit:'Сохранить',
					submit:submit
				});
				$('#worker_id')._select({
					width:180,
					title0:'Не выбран',
					spisok:CLIENT.workers
				});
				$('#c-fio,#c-telefon,#c-adres,#pasp_seria,#pasp_nomer,#pasp_adres,#pasp_ovd,#pasp_data').keyEnter(submit);
				function submit() {
					var send = {
							op:'client_edit',
							client_id:CLIENT.id,
							fio:$('#c-fio').val(),
							telefon:$('#c-telefon').val(),
							adres:$('#c-adres').val(),
							worker_id:$('#worker_id').val(),
							pasp_seria:$('#pasp_seria').val(),
							pasp_nomer:$('#pasp_nomer').val(),
							pasp_adres:$('#pasp_adres').val(),
							pasp_ovd:$('#pasp_ovd').val(),
							pasp_data:$('#pasp_data').val()
						};
					if(!send.fio) {
						err('Не указано имя клиента');
						$('#c-fio').focus();
					} else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								res.workers = CLIENT.workers;
								CLIENT = res;
								$('.left:first').html(res.html);
								dialog.close();
								_msg('Данные клиента изменены.');
							} else {
								err(res.text);
								dialog.abort();
							}
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<span class="red">' + msg + '</span>',
						top:-47,
						left:120,
						indent:50,
						show:1,
						remove:1
					});
				}
			});
			$('.cdel').click(function() {
				var dialog = _dialog({
					top:90,
					width:300,
					head:'Удаление клиента',
					content:'<center>Внимание!<br />Будут удалены все данные о клиенте,<br />его заявки, платежи и задачи.<br /><b>Подтвердите удаление.</b></center>',
					butSubmit:'Удалить',
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
							_msg('Клиент удален!');
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
					txt:'Быстрый поиск...',
					enter:1,
					func:zayavFindFast
				})
				.vkHint({
					width:220,
					msg:'<b>Быстрый поиск</b> производится одновременно по всем категориям заявок. ' +
						'Учитываются поля: номер договора, номера ВГ, Ж, Д, адрес установки, ' +
						'Фио и телефоны клиентов и порядковый номер заявки. ' +
						'Другие условия фильтра игонрируются.',
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
				title0:'Любые изделия',
				spisok:spisok,
				func:zayavSpisok
			});
			$('#zp_expense')._radio(function(v) {
				$('#zpe_worker_select')[(v == 3 ? 'remove' : 'add') + 'Class']('dn');
				$('#zpe_worker')._select(0);
				zayavSpisok();
			});
			if($('#zpe_worker').length)
				$('#zpe_worker')._select({
					width:155,
					title0:'Сотрудник не выбран',
					spisok:ZPE_WORKER,
					func:zayavSpisok
				});
			$('#zpe_worker_select').addClass('dn');
			$('#account')._check(zayavSpisok);
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
				head:'Не заключен',
				spisok:[
					{uid:1, title:'Заключить договор'},
					{uid:2, title:'Перевести заявку в категорию "Требуется договор"'}
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
				head:'Действие',
				headgrey:1,
				spisok:[
					{uid:1, title:'Изменение данных договора'},
//					{uid:2, title:'Перезаключение'},
					{uid:3, title:'Расторжение'}
				],
				nosel:1,
				func:function(v) {
					if(v == 1)
						dogovorCreate('edit');
					if(v == 3)
						dogovorDestroy();
				}
			});
			$('.zakaz_edit').click(function() {
				var html = '<table class="zayav-info-edit">' +
						'<tr><td class="label">Клиент:      <td>' + ZAYAV.client_fio +
						'<tr><td class="label topi">Изделие:<td id="product">' +
						'<tr><td><td><input type="text" id="zakaz_txt" placeholder="либо укажите содержание заказа вручную.." maxlength="300" value="' + ZAYAV.zakaz_txt + '">' +
						'<tr><td class="label">Номер ВГ:	<td><INPUT type="text" id="nomer_vg" maxlength="30" value="' + ZAYAV.nomer_vg + '" />' +
						'<tr><td class="label">Номер Ж: 	<td><INPUT type="text" id="nomer_g" maxlength="30" value="' + ZAYAV.nomer_g + '" />' +
						'<tr><td class="label">Номер Д: 	<td><INPUT type="text" id="nomer_d" maxlength="30" value="' + ZAYAV.nomer_d + '" />' +
						'<tr><td class="label">Номер T: 	<td><INPUT type="text" id="nomer_t" maxlength="30" value="' + ZAYAV.nomer_t + '" />' +
						'</table>',
					dialog = _dialog({
						width:500,
						top:30,
						head:ZAYAV.head + ' - Редактирование',
						content:html,
						butSubmit:'Сохранить',
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
							nomer_d:$('#nomer_d').val(),
							nomer_t:$('#nomer_t').val()
						};
					if(!send.product && !send.zakaz_txt) msg = 'Необходимо выбрать изделие или вписать заказ вручную';
					else if(send.product == 'count_error') msg = 'Некорректно введено количество изделий';
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								dialog.close();
								_msg('Данные изменены!');
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
						'<tr><td class="label">Клиент:        <td>' + ZAYAV.client_fio +
						'<tr><td class="label top">Изделие:	<td id="product">' +
						'<tr><td class="label">Адрес замера:  <td><INPUT type="text" id="adres" maxlength="100" value="' + ZAYAV.adres + '" />' +
						'<tr><td class="label">Дата и время замера:<td class="zayav-zamer-dtime">' +
						'<tr><td class="label">Длительность замера:' +
							'<td><INPUT TYPE="hidden" id="zamer_duration" value="' + ZAYAV.dur + '" />' +
								'<a class="zamer_table" val="' + ZAYAV.id + '">Таблица замеров</a>' +
						'</table>',
					dialog = _dialog({
						width:500,
						top:30,
						head:ZAYAV.head + ' - Редактирование',
						content:html,
						butSubmit:'Сохранить',
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
					if(!send.product) err('Не указано изделие');
					else if(send.product == 'count_error') err('Некорректно введено количество изделий');
					else if(!send.adres) err('Не указан адрес');
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								dialog.close();
								_msg('Данные изменены!');
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
						'<tr><td class="label">Клиент:      <td>' + ZAYAV.client_fio +
						'<tr><td class="label top">Изделие:	<td id="product">' +
						'<tr><td class="label">Адрес установки:<td><INPUT type="text" id="adres" maxlength="100" value="' + ZAYAV.adres + '" />' +
						'</table>',
					dialog = _dialog({
						width:500,
						top:30,
						head:'Редактирование',
						content:html,
						butSubmit:'Сохранить',
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
					if(!send.product) msg = 'Не указано изделие';
					else if(send.product == 'count_error') msg = 'Некорректно введено количество изделий';
					else if(!send.adres) msg = 'Не указан адрес';
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								dialog.close();
								_msg('Данные изменены!');
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
						'<tr><td class="label">Клиент:      <td>' + ZAYAV.client_fio +
						'<tr><td class="label top">Изделие:	<td id="product">' +
						'<tr><td class="label">Адрес установки:<td><INPUT type="text" id="adres" maxlength="100" value="' + ZAYAV.adres + '" />' +
						'<tr><td class="label">Номер ВГ:	   <td><INPUT type="text" id="nomer_vg" maxlength="30" value="' + ZAYAV.nomer_vg + '" />' +
						'<tr><td class="label">Номер Ж: 	   <td><INPUT type="text" id="nomer_g" maxlength="30" value="' + ZAYAV.nomer_g + '" />' +
						'<tr><td class="label">Номер Д: 	   <td><INPUT type="text" id="nomer_d" maxlength="30" value="' + ZAYAV.nomer_d + '" />' +
						'<tr><td class="label">Номер T: 	   <td><INPUT type="text" id="nomer_t" maxlength="30" value="' + ZAYAV.nomer_t + '" />' +
						'</table>',
					dialog = _dialog({
						width:500,
						top:30,
						head:ZAYAV.head + ' - Редактирование',
						content:html,
						butSubmit:'Сохранить',
						submit:submit
					});
				$('#product').productList(ZAYAV.product);
				$('#adres,#nomer_vg,#nomer_g,#nomer_d,#nomer_t').keyEnter(submit);
				function submit() {
					var msg,
						send = {
							op:'set_edit',
							zayav_id:ZAYAV.id,
							product:$('#product').productList('get'),
							adres:$('#adres').val(),
							nomer_vg:$('#nomer_vg').val(),
							nomer_g:$('#nomer_g').val(),
							nomer_d:$('#nomer_d').val(),
							nomer_t:$('#nomer_t').val()
						};
					if(!send.product) msg = 'Не указано изделие';
					else if(send.product == 'count_error') msg = 'Некорректно введено количество изделий';
					else if(!send.adres) msg = 'Не указан адрес';
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								dialog.close();
								_msg('Данные изменены!');
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
				var html = '<table class="accrual-add">' +
					'<tr><td class="label">Сумма: <td><input type="text" id="sum" class="money" maxlength="11" /> руб.' +
					'<tr><td class="label">Примечание:<em>(не обязательно)</em><td><input type="text" id="prim" maxlength="100" />' +
					'</table>';
				var dialog = _dialog({
					width:420,
					head:'Начисление',
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
						msg = 'Некорректно указана сумма.';
						$('#sum').focus();
					} else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							dialog.abort();
							if(res.success) {
								zayavInfoMoneyUpdate();
								dialog.close();
								_msg('Начисление успешно произведено.');
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
							show:1
						});
				}
			});
			$('.delete').click(function() {
				var dialog = _dialog({
					top:110,
					width:250,
					head:'Удаление заявки',
					content:'<CENTER>Подтвердите удаление заявки.</CENTER>',
					butSubmit:'Удалить',
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
			$('.expense-edit').click(function() {
				var html = '<table class="zayav-expense-edit">' +
						'<tr><td class="label">Заявка: <td><b>' + ZAYAV.head + '</b>' +
						'<tr><td class="label topi">Расходы:<td>' +
						'<tr><td colspan="2" id="zrs">' +
						'</table>',
					dialog = _dialog({
						width:510,
						top:30,
						head:'Изменение расходов заявки',
						content:html,
						butSubmit:'Сохранить',
						submit:submit
					});
				$('#zrs').zayavExpense(ZAYAV.rashod);
				function submit() {
					var send = {
						op:'zayav_expense_edit',
						zayav_id:ZAYAV.id,
						rashod:$('#zrs').zayavExpense('get')
					};
					if(send.rashod == 'sum_error') dialog.err('Некорректно указана сумма');
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								$('.zrashod').html(res.html);
								$('#hspisok').html(res.history);
								ZAYAV.rashod = res.array;
								dialog.close();
								_msg('Сохранено.');
							} else
								dialog.abort();
						}, 'json');
					}
				}
			});
			$('.zakaz-to-set').click(function() {
				var html = '<table class="_dialog-tab">' +
					'<tr><td class="label">Клиент:<td>' + ZAYAV.client_fio +
					'<tr><td class="label">Адрес установки:' +
						'<td><INPUT type="text" id="adres" maxlength="100" value="' + ZAYAV.adres + '" />' +
							'<INPUT type="hidden" id="homeadres" />' +
					'</table>';
				var dialog = _dialog({
					width:400,
					head:'Перенос заказа в установку',
					content:html,
					butSubmit:'Перенести',
					submit:submit
				});
				$('#adres').focus().keyEnter(submit);
				$('#homeadres')._check({
					func:function() {
						$('#adres').val(ZAYAV.client_adres);
					}
				});
				$('#homeadres_check').vkHint({
					msg:'Совпадает с адресом проживания клиента',
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
						err('Не указан адрес установки');
						$('#adres').focus();
					} else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							dialog.abort();
							if(res.success) {
								dialog.close();
								_msg('Выполнено.');
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
						show:1
					});
				}
			});
			$('.set-to-zakaz').click(function() {
				var html = '<table class="_dialog-tab">' +
						'<tr><td class="label">Заявка:<td><b>' + ZAYAV.head + '</b>' +
						'<tr><td class="label">Клиент:<td>' + ZAYAV.client_fio +
					'</table>';
				var dialog = _dialog({
					head:'Перенос установки в заказы',
					content:html,
					butSubmit:'Перенести',
					submit:submit
				});
				function submit() {
					var send = {
						op:'set_to_zakaz',
						zayav_id:ZAYAV.id
					};
					dialog.process();
					$.post(AJAX_MAIN, send, function(res) {
						if(res.success) {
							dialog.close();
							_msg('Выполнено.');
							location.reload();
						} else
							dialog.abort();
					}, 'json');
				}
			});
			$('.refund-add').click(function() {
				var html = '<table class="refund-add-tab">' +
					'<tr><td class="label">Клиент:<td>' + OPL.client_fio +
					'<tr><td class="label">Со счёта:<td><input type="hidden" id="invoice_id">' +
						'<a href="' + URL + '&p=setup&d=invoice" class="img_edit' + _tooltip('Настройка счетов', -56) + '</a>' +
					'<tr><td class="label">Сумма:' +
						'<td><input type="text" id="sum" class="money" maxlength="11" /> руб.' +
							'<span id="isum"></span>' +
					'<tr><td class="label">Комментарий:<td><input type="text" id="prim" maxlength="100" />' +
					'</table>';
				var dialog = _dialog({
					width:370,
					head:'Возврат',
					content:html,
					submit:submit
				});
				$('#sum').focus();
				$('#sum,#prim').keyEnter(submit);
				$('#invoice_id')._select({
					width:200,
					title0:'Не выбран',
					spisok:INVOICE_SPISOK,
					func:function(v) {
						$('#sum').focus();
						$('#isum').html(ZAYAV.isum[v] ? 'max: <b>' + ZAYAV.isum[v] + '</b>' : '');
					}
				});

				function submit() {
					var send = {
						op:'refund_add',
						zayav_id:ZAYAV.id,
						invoice_id:$('#invoice_id').val() * 1,
						sum:_cena($('#sum').val()),
						prim:$.trim($('#prim').val())
					};
					if(!send.invoice_id) err('Не указан счёт');
					else if(!send.sum) { err('Некорректно указана сумма'); $('#sum').focus(); }
					//else if(send.sum.replace(',', '.') > ZAYAV.isum[send.invoice_id]) { err('Возврат не может превышать сумму на счёте'); $('#sum').focus(); }
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							dialog.abort();
							if(res.success) {
								dialog.close();
								zayavInfoMoneyUpdate();
								_msg('Возврат успешно произведён.');
								$('#income_spisok').html(res.html);
							}
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						top:-48,
						left:100,
						indent:40,
						remove:1,
						show:1
					});
				}

			});
		}

		if($('#report.history').length) {
			$('#viewer_id_add')._select({
				width:160,
				title0:'Все сотрудники',
				spisok:WORKERS,
				func:_history
			});
			$('#action')._select({
				width:160,
				title0:'Любая категория',
				spisok:HISTORY_GROUP,
				func:_history
			});
		}
		if($('#report.income').length) {
			window._calendarFilter = incomeSpisok;
			$('#filter_invoice_id')._select({
				width:160,
				title0:'Все счета',
				spisok:INVOICE_SPISOK,
				func:incomeSpisok
			});
			if(window.WORKERS)
				$('#worker_id')._select({
					width:160,
					title0:'Все сотрудники',
					spisok:WORKERS,
					func:incomeSpisok
				});
			$('#deleted')._check(incomeSpisok);
		}
		if($('#report.expense').length) {
			$('.add').click(function() {
				var html =
						'<table id="expense-add-tab">' +
							'<tr><td class="label">Категория:<td><input type="hidden" id="cat">' +
								'<a href="' + URL + '&p=setup&d=expense" class="img_edit' + _tooltip('Настройка категорий расходов', -95) + '</a>' +
							'<tr class="tr-work dn"><td class="label">Сотрудник:<td><input type="hidden" id="work">' +
							'<tr class="tr-work dn"><td class="label">Месяц:' +
													'<td><input type="hidden" id="tabmon" value="' + ((new Date()).getMonth() + 1) + '" /> ' +
														'<input type="hidden" id="tabyear" value="' + ((new Date()).getFullYear()) + '" />' +
							'<tr><td class="label">Описание:<td><input type="text" id="about" maxlength="100">' +
							'<tr><td class="label">Со счёта:<td><input type="hidden" id="invoice">' +
								'<a href="' + URL + '&p=setup&d=invoice" class="img_edit' + _tooltip('Настройка счетов', -56) + '</a>' +
							'<tr><td class="label">Сумма:' +
								'<td><input type="text" id="sum" class="money" maxlength="11"> руб.' +
									'<span id="isum"></span>' +
						'</table>',
					dialog = _dialog({
						width:380,
						head:'Внесение расхода',
						content:html,
						submit:submit
					});

				$('#cat')._select({
					width:180,
					title0:'Не указана',
					spisok:EXPENSE_SPISOK,
					func:function(id) {
						$('#work')._select(0);
						$('.tr-work')[(EXPENSE_WORKER[id] ? 'remove' : 'add') + 'Class']('dn');
					}
				});
				$('#about').focus();
				$('#work')._select({
					title0:'Не выбран',
					spisok:WORKER_SPISOK
				});
				$('#invoice')._select({
					title0:'Не выбран',
					spisok:INVOICE_SPISOK,
					func:function(v) {
						$('#sum').focus();
						$('#isum').html(ISUM[v] ? 'max: <b>' + ISUM[v] + '</b>' : '');
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
						sum:_cena($('#sum').val()),
						mon:$('#tabmon').val(),
						year:$('#tabyear').val()
					};
					if(!send.category && !send.about) { err('Выберите категорию или укажите описание.'); $('#about').focus(); }
					else if(!send.invoice) err('Укажите с какого счёта производится оплата.');
					else if(!send.sum) { err('Некорректно указана сумма.'); $('#sum').focus(); }
					else if(ISUM[send.invoice] && send.sum > ISUM[send.invoice]) { err('Сумма превышает максимально допустимую'); $('#sum').focus(); }
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function (res) {
							if(res.success) {
								dialog.close();
								_msg('Новый расход внесён.');
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
				title0:'Любая категория',
				spisok:EXPENSE_SPISOK,
				func:expenseSpisok
			});
			$('#worker')._select({
				width:160,
				title0:'Все сотрудники',
				spisok:WORKERS,
				func:expenseSpisok
			});
			$('#invoice_id')._radio(expenseSpisok);
			$('#year').years({
				func:expenseSpisok,
				center:function() {
					var inp = $('#monthList input'),
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
					sum_from = false,
					html =
						'<table id="invoice-transfer-tab">' +
							'<tr><td class="label">Со счёта:' +
								'<td><input type="hidden" id="from" /><span id="sum-from"></span>' +
							'<tr><td class="label">На счёт:' +
								'<td><input type="hidden" id="to" />' +
							'<tr><td class="label">Сумма:<td><input type="text" id="sum" class="money" /> руб. ' +
						'</table>',
					dialog = _dialog({
						width:400,
						head:'Перевод между счетами',
						content:html,
						butSubmit:'Применить',
						submit:submit
					});
				$('#from')._select({
					width:200,
					title0:'Не выбран',
					spisok:INVOICE_SPISOK,
					func:function(v) {
						sum_from = typeof CASH_SUM[v] == 'number';
						$('#sum-from').html(sum_from ? 'max: <b>' + CASH_SUM[v] + '</b>' : '');
						if(sum_from)
							sum_from = CASH_SUM[v];

					}
				});
				$('#to')._select({
					width:200,
					title0:'Не выбран',
					spisok:INVOICE_SPISOK
				});
				$('#sum').keyEnter(submit);
				function submit() {
					var send = {
						op:'invoice_transfer',
						from:_num($('#from').val()),
						to:_num($('#to').val()),
						sum:_cena($('#sum').val())
					};
					if(!send.from) dialog.err('Выберите счёт-отправитель');
					else if(!send.to) dialog.err('Выберите счёт-получатель');
					else if(send.from == send.to) dialog.err('Выберите другой счёт');
					else if(!send.sum) { dialog.err('Некорректно введена сумма'); $('#sum').focus(); }
					else if(sum_from !== false && sum_from - send.sum < 0) { dialog.err('Введённая сумма больше, чем на счёте-отправителе'); $('#sum').focus(); }
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								$('#invoice-spisok').html(res.i);
								$('.transfer-spisok').html(res.t);
								dialog.close();
								_msg('Перевод произведён');
							} else
								dialog.abort();
						}, 'json');
					}
				}
			});
		}
		if($('#report.salary').length) {
			if($('#uall').length) {
				$('#uall')._check(function(v) {
					var ch = $('._check');
					for(n = 1; n < ch.length; n++)
						ch.eq(n).find('input')._check(v);
				});
				var mon = [];
				for(var k in  MONTH_DEF)
					mon.push({uid:k,title:MONTH_DEF[k]});
				$('#rmon')._select({
					width:100,
					spisok:mon
				});
				$('#ryear')._select({
					width:60,
					spisok:YEARS
				});
				$('.vkButton').click(function() {
					var t = $(this),
						ch = $('._check'),
						s = [];
					for(n = 1; n < ch.length; n++) {
						var eq = ch.eq(n),
							inp = eq.find('input');
						if(inp.val() == 1)
							s.push(inp.attr('id').split('u')[1]);
					}
					var ids = s.join();
					if(!ids)
						t.vkHint({
							msg:'<span class="red">Не выбраны сотрудники</span>',
							top:-57,
							left:7,
							indent:50,
							show:1,
							remove:1
						});
					else
						document.location.href =
							APP_HTML + '/view/salary_report.php?' + VALUES +
								'&ids=' + ids +
								'&mon=' + $('#rmon').val() +
								'&year=' + $('#ryear').val();
				});
			} else {
				$('#year').years({func:salarySpisok});
				$('#salmon')._radio({func:salarySpisok});
			}
		}
	});
