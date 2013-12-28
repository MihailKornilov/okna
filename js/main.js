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
	clientAdd = function(callback) {
		var html = '<table class="client-add">' +
				'<tr><td class="label">Имя:<td><input type="text" id="fio" maxlength="100">' +
				'<tr><td class="label">Телефон:<td><input type="text" id="telefon" maxlength="100">' +
				'<tr><td class="label">Адрес:<td><input type="text" id="adres" maxlength="100">' +
				'<tr class="tr_pasp"><td colspan="2"><a>Заполнить паспортные данные</a>' +
				'<tr class="dn"><td><td><b>Паспортные данные:</b>' +
				'<tr class="dn"><td class="label">Серия:' +
							   '<td><input type="text" id="pasp_seria" maxlength="8">' +
								   '<span class="label">Номер:</span><input type="text" id="pasp_nomer" maxlength="10">' +
				'<tr class="dn"><td class="label">Прописка:<td><input type="text" id="pasp_adres" maxlength="100">' +
				'<tr class="dn"><td class="label">Кем выдан:<td><input type="text" id="pasp_ovd" maxlength="100">' +
				'<tr class="dn"><td class="label">Когда выдан:<td><input type="text" id="pasp_data" maxlength="100">' +
			'</table>';
			dialog = _dialog({
				top:60,
				width:380,
				head:'Добавление нoвого клиента',
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
					msg:'<SPAN class="red">Не указано имя клиента.</SPAN>',
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
			fast:cFind.inp(),
			dolg:$('#dolg').val()
		};
		$('.filter')[v.fast ? 'hide' : 'show']();
		return v;
	},
	clientSpisokLoad = function() {
		var send = clientFilter(),
			result = $('.result');
		send.op = 'client_spisok_load';
		if(result.hasClass('busy'))
			return;
		result.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			result.removeClass('busy');
			if(res.success) {
				result.html(res.all);
				$('.left').html(res.spisok);
			}
		}, 'json');
	},
	clientZayavFilter = function() {
		return {
			client:CLIENT.id,
			status:$('#status').val()
		};
	},
	clientZayavSpisokLoad = function() {
		var send = clientZayavFilter();
		send.op = 'client_zayav_load';
		$('#dopLinks').addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			$('#dopLinks').removeClass('busy');
			$('#zayav_result').html(res.all);
			$('#zayav_spisok').html(res.html);
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
		$('#zamer_hour').vkSel({
			width:40,
			display:'inline-block',
			spisok:ZAMER_HOUR
		});
		$('#zamer_min').vkSel({
			width:40,
			display:'inline-block',
			spisok:ZAMER_MIN
		});
		$('#zamer_duration').vkSel({
			width:100,
			display:'inline-block',
			spisok:ZAMER_DURATION
		});

	},
	zayavFilter = function() {
		var v = {
				find:$.trim($('#find input').val()),
//				sort:$('#sort').val(),
				desc:$('#desc').val(),
				category:$('#category').val(),
				status:$('#status').val()
			},
			loc = '';
//		if(v.sort != '1') loc += '.sort=' + v.sort;
		if(v.desc != '0') loc += '.desc=' + v.desc;
		if(v.find) loc += '.find=' + escape(v.find);
		else {
			if(v.category > 0) loc += '.category=' + v.category;
			if(v.status > 0) loc += '.status=' + v.status;
		}
		VK.callMethod('setLocation', hashLoc + loc);

		setCookie('zayav_find', escape(v.find));
  //	  setCookie('zayav_sort', v.sort);
		setCookie('zayav_desc', v.desc);
		setCookie('zayav_category', v.category);
		setCookie('zayav_status', v.status);

		return v;
	},
	zayavSpisokLoad = function() {
		var send = zayavFilter();
		$('.condLost')[(send.find ? 'add' : 'remove') + 'Class']('hide');
		send.op = 'zayav_spisok_load';

		$('#mainLinks').addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			$('#zayav .result').html(res.all);
			$('#zayav #spisok').html(res.html);
			$('#mainLinks').removeClass('busy');
		}, 'json');
	},
	dogovorCreate = function() {
		var html = '<table class="zayav-dogovor">' +
				'<tr><td colspan="2">' +
		(DOG.id ? '<div class="i per">' +
						'При <b>перезаключении договора</b> удаляются сумма старого договора и авансовый платёж. ' +
						'Применятся данные нового договора. Также будет обновлён баланс клиента.' +
					'</div>'
		: '') +
				'<tr><td class="label r">Фио клиента:<td><input type="text" id="fio" value="' + DOG.fio + '" />' +
				'<tr><td class="label r">Адрес установки:<td><input type="text" id="adres" value="' + DOG.adres + '" />' +
				'<tr><td class="label r">Паспорт:' +
					'<td>Серия:<input type="text" id="pasp_seria" maxlength="8" value="' + DOG.pasp_seria + '" />' +
						'Номер:<input type="text" id="pasp_nomer" maxlength="10" value="' + DOG.pasp_nomer + '" />' +
				'<tr><td><td><span class="l">Прописка:</span><input type="text" id="pasp_adres" maxlength="100" value="' + DOG.pasp_adres + '" />' +
				'<tr><td><td><span class="l">Кем выдан:</span><input type="text" id="pasp_ovd" maxlength="100" value="' + DOG.pasp_ovd + '" />' +
				'<tr><td><td><span class="l">Когда выдан:</span><input type="text" id="pasp_data" maxlength="100" value="' + DOG.pasp_data + '" />' +
				'<tr><td class="label r">Номер договора:<td><input type="text" id="nomer" maxlength="6" value="' + DOG.nomer + '" />' +
				'<tr><td class="label r">Дата заключения:<td><input type="hidden" id="data_create" value="' + (DOG.data_create ? DOG.data_create : '') + '" />' +
				'<tr><td class="label r">Сумма по договору:<td><input type="text" id="sum" class="money" maxlength="6" value="' + (DOG.sum ? DOG.sum : '') + '" /> руб.' +
				'<tr><td class="label r">Авансовый платёж:<td><input type="text" id="avans" class="money" maxlength="6" value="' + (DOG.avans ? DOG.avans : '') + '" /> руб. <span class="prim">(не обязательно)</span>' +
	  (DOG.id ? '<tr><td class="label" colspan="2">Причина перезаключения договора:<textarea id="reason"></textarea>' : '') +
				'<tr><td colspan="2">' +
					'<div class="i">' +
						'<h1>Внимание!</h1>' +
						'Внимательно проверьте правильность всех введённых данных. ' +
						'После нажатия кнопки "Заключить договор" операцию отменить будет невозможно.<br />' +
						'<b>Сумма по договору</b> является окончательной суммой и при заключении договора на эту сумму будет изменён баланс клиента в минус.<br />' +
						'<b>Авансовый платёж</b> указывать не обязательно. При указании авансового платёжа автоматически будет внесён платёж на данную заявку.' +
					'</div>' +
				'<tr><td colspan="2">' +
					'<a id="preview">Предварительный просмотр</a>' +
					'<form action="' + AJAX_MAIN + '" method="post" id="preview-form" target="_blank"></form>' +
				'</table>',
			dialog = _dialog({
				width:426,
				top:10,
				head:(DOG.id ? 'Перез' : 'З') + 'аключение договора',
				content:html,
				butSubmit:(DOG.id ? 'Перез' : 'З') + 'аключить договор',
				submit:submit
			});
		$('#data_create')._calendar({lost:1});
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
				reason:DOG.id ? $('#reason').val() : ''
			};
			if(!send.fio) err('Не указано Фио клиента', 'fio', type);
			else if(!send.adres) err('Не указан адрес', 'adres', type);
			else if(!REGEXP_NUMERIC.test(send.nomer) || send.nomer == 0) err('Некорректно указан номер договора', 'nomer', type);
			else if(!REGEXP_NUMERIC.test(send.sum) || send.sum == 0) err('Некорректно указана сумма по договору', 'sum', type);
			else if(send.avans && !REGEXP_NUMERIC.test(send.avans)) err('Некорректно указан авансовый платёж', 'avans', type);
			else if(DOG.id && !send.reason) err('Не указана причина перезаключения договора', 'reason', type);
			else return send;
			return false;
		}
		function err(msg, id, type) {
			dialog.bottom.vkHint({
				msg:'<span class="red">' + msg + '</span>',
				top:type ? -86 : -47,
				left:type ? 141 : 110,
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
				send.op = 'dogovor_create';
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

	setupRulesSet = function(action, value) {
		var send = {
			op:'setup_rules_set',
			viewer_id:RULES_VIEWER_ID,
			value:value,
			action:action
		};
		$.post(AJAX_MAIN, send, function() {}, 'json');
	};

$.fn.clientSel = function(obj) {
	var t = $(this);
	obj = $.extend({
		width:240,
		add:null,
		client_id:t.val() || 0,
		func:function() {}
	}, obj);

	if(obj.add)
		obj.add = function() {
			clientAdd(function(res) {
				sel.add(res).val(res.uid)
			});
		};

	var sel = t.vkSel({
		width:obj.width,
		title0:'Начните вводить данные клиента...',
		spisok:[],
		ro:0,
		nofind:'Клиентов не найдено',
		func:obj.func,
		funcAdd:obj.add,
		funcKeyup:clientsGet
	}).o;
	sel.process();
	clientsGet();

	function clientsGet(val) {
		var send = {
			op:'client_sel',
			val:val || '',
			client_id:obj.client_id
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				sel.spisok(res.spisok);
				if(obj.client_id > 0) {
					sel.val(obj.client_id);
					obj.client_id = 0;
				}
			}
		}, 'json');
	}
	t.o = sel;
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
		$('#' + attr_id).vkSel({
			width:119,
			display:'inline-block',
			title0:'Не указано',
			spisok:PRODUCT_SPISOK,
			func:function(id) {
				$('#vkSel_' + attr_subid).remove();
				$('#' + attr_subid).val(0);
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
		$('#' + attr_subid).vkSel({
			width:120,
			display:'inline-block',
			title0:'Подвид не указан',
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
				if(ZAYAVRASHOD_TXT_ASS[cat_id])
					dop = u.find('.zrtxt').val();
				else if(ZAYAVRASHOD_WORKER_ASS[cat_id])
					dop = $('#' + attr + 'worker').val();
				send.push(cat_id + ':' + dop + ':' + sum);
			}
			return send.join();
		}
	}

	t.html('<div class="_zayav-rashod"><a class="add">Добавить поле</a></div>');
	var add = t.find('.add');
	add.click(itemAdd);
	itemAdd();

	function itemAdd() {
		var attr = id + num,
			attr_cat = attr + 'cat',
			attr_worker = attr + 'worker',
			html = '<table id="ptab'+ num + '" class="ptab" val="' + num + '"><tr>' +
						'<td><input type="hidden" id="' + attr_cat + '" />' +
						'<td class="tddop">' +
						'<td class="tdsum dn"><input type="text" class="zrsum" maxlength="6" />руб.' +
						'<td>' + (num > 1 ? '<div class="img_del"></div>' : '') +
					'</table>';
		add.before(html);
		var ptab = $('#ptab' + num),
			tddop = ptab.find('.tddop'),
			zrsum = ptab.find('.zrsum');
		ptab.find('.img_del').click(function() {
			ptab.remove();
		});
		$('#' + attr_cat).vkSel({
			width:120,
			display:'inline-block',
			title0:'Категория',
			spisok:ZAYAVRASHOD_SPISOK,
			func:function(id) {
				ptab.find('.tdsum')[(id ? 'remove' : 'add') + 'Class']('dn');
				if(ZAYAVRASHOD_TXT_ASS[id]) {
					tddop.html('<input type="text" class="zrtxt" />');
					tddop.find('.zrtxt').focus();
				} else if(ZAYAVRASHOD_WORKER_ASS[id]) {
					tddop.html('<input type="hidden" id="' + attr_worker + '" />');
					$('#' + attr_worker).vkSel({
						width:150,
						display:'inline-block',
						title0:'Сотрудник',
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
			f.after('<span class="red">Некорректный файл.</span>');
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

	.on('click', '.oplata-add', function() {
		var html =
			'<table class="oplata-add-tab">' +
				'<tr><td class="label">Клиент:<td>' + OPL.client_fio +
				'<tr><td class="label">Заявка:<td><input type="hidden" id="zayav_id" value="' + (OPL.zayav_id ? OPL.zayav_id : 0) + '">' +
						(OPL.zayav_id ? '<b>№' + OPL.zayav_id + '</b>' : '') +
				'<tr><td class="label">Вид платежа:<td><input type="hidden" id="prihod_type" value="0">' +
						'<a href="' + URL + '&p=setup&d=prihodtype" class="img_edit" title="Перейти к настройке видов платежей"></a>' +
				'<tr><td class="label">Сумма:<td><input type="text" id="sum" class="money" maxlength="7"> руб.' +
				'<tr class="tr_kassa dn"><td class="label">Деньги поступили в кассу?:<td><input type="hidden" id="kassa" value="2">' +
				'<tr><td class="label">Примечание:<em>(не обязательно)</em><td><input type="text" id="prim">' +
				'</table>';
		var dialog = _dialog({
			top:60,
			width:440,
			head:'Внесение платежа',
			content:html,
			submit:submit
		});
		$('#sum').focus();
		$('#sum,#prim').keyEnter(submit);
		if(OPL.zayav_spisok)
			$('#zayav_id').vkSel({
				display:'inline-block',
				width:180,
				title0:'Не указана',
				spisok:OPL.zayav_spisok
			});
		$('#prihod_type').vkSel({
			display:'inline-block',
			width:180,
			title0:'Не указан',
			spisok:PRIHOD_SPISOK,
			func:function(uid) {
				$('#kassa')._radio(2);
				$('.tr_kassa')[(PRIHODKASSA_ASS[uid] ? 'remove' : 'add') + 'Class']('dn');
				$('#sum').focus();
			}
		});
		$('#kassa')._radio({
			spisok:[
				{uid:1, title:'да'},
				{uid:0, title:'нет'}
			],
			func:function() {
				$('#prim').focus();
			}
		});
		function submit() {
			var send = {
				op:'oplata_add',
				from:OPL.from,
				type:$('#prihod_type').val(),
				sum:$('#sum').val(),
				zayav_id:$('#zayav_id').val(),
				client_id:OPL.client_id,
				kassa:$('#kassa').val(),
				prim:$.trim($('#prim').val())
			};
			if(send.type == 0) err('Не указан вид платежа');
			else if(!REGEXP_NUMERIC.test(send.sum)) {
				err('Некорректно указана сумма.');
				$('#sum').focus();
			} else if(PRIHODKASSA_ASS[send.type] && send.kassa == 2)
				err('Укажите, деньги поступили в кассу или нет');
			else if(send.zayav_id == 0 && !send.prim)
				err('Если не выбрана заявка, необходимо указать примечание');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Платёж успешно внесён!');
						switch(OPL.from) {
							case 'client':
								$('#money_spisok').html(res.html);
								$('.left:first').html(res.balans);
								break;
							case 'zayav':
								$('#money_spisok').html(res.html);
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
	.on('click', '.oplata-del', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		if(t.hasClass('deleting'))
			return;
		t.addClass('deleting');
		var send = {
			op:'oplata_del',
			id:t.attr('val')
		};
		$.post(AJAX_MAIN, send, function(res) {
			t.removeClass('deleting');
			if(res.success) {
				t.after('<tr class="deleted" val="' + send.id + '">' +
							'<td colspan="4"><div>Платёж удалён. <a class="oplata-rest">Восстановить</a></div>');
				t.addClass('dn');
			}
		}, 'json');
	})
	.on('click', '.oplata-rest', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var send = {
				op:'oplata_rest',
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
				t.prev().removeClass('dn');
				t.remove();
			}
		}, 'json');
	})

	.on('click', '#client .ajaxNext', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = clientFilter();
		send.op = 'client_next';
		send.page = next.attr('val');
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				next.remove();
				$('#client .left').append(res.spisok);
			} else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.zayav_add', function() {
		var html =
			'<div class="zayav-add all">' +
				'<div class="vkButton zakaz_add"><button>Новый заказ</button></div><br />' +
				'<div class="vkButton zamer_add"><button>Новый замер</button></div><br />' +
				'<div class="vkButton set_add"><button>Новая установка</button></div>' +
			'</div>',
			dialog = _dialog({
				width:220,
				top:60,
				head:'Выберите категорию заявки',
				content:html,
				butSubmit:''
			});
	})

	.on('click', '#zayav #filter_break', function() {
		zFind.clear();
		//$('#sort')._radio(1);
		$('#desc')._check(0);
		$('#status').rightLink(0);
		zayavSpisokLoad();
	})
	.on('click', '.zakaz_add', function() {
		if(typeof CLIENT == 'undefined')
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
					'<tr><td class="label top">Изделие:<td id="product">' +
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
		if(CLIENT.id == 0)
			var client = $('#client_id').clientSel({
				add:1,
				func:function(uid) {
					HOMEADRES = client.item(uid).adres;
					if($('#homeadres').val() == 1)
						$('#adres').val(HOMEADRES);
				}
			}).o;
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
		if(typeof CLIENT == 'undefined')
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
				'<tr><td class="label top">Изделие:<td id="product">' +
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
		if(CLIENT.id == 0)
			var client = $('#client_id').clientSel({
				add:1,
				func:function(uid) {
					HOMEADRES = client.item(uid).adres;
					if($('#homeadres').val() == 1)
						$('#adres').val(HOMEADRES);
				}
			}).o;
		$('#product').productList();
		$('#homeadres')._check({
			func:function() {
				$('#adres').val(HOMEADRES);
			}
		});
		$('#homeadres_check').vkHint({
			msg:'Совпадает с адресом проживания',
			top:-74,
			left:196,
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
	.on('click', '.set_add', function() {
		if(typeof CLIENT == 'undefined')
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
					'<tr><td class="label top">Изделие:<td id="product">' +
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
		if(CLIENT.id == 0)
			var client = $('#client_id').clientSel({
				add:1,
				func:function(uid) {
					HOMEADRES = client.item(uid).adres;
					if($('#homeadres').val() == 1)
						$('#adres').val(HOMEADRES);
				}
			}).o;
		$('#product').productList();
		$('#homeadres')._check({
			func:function() {
				$('#adres').val(HOMEADRES);
			}
		});
		$('#homeadres_check').vkHint({
			msg:'Совпадает с адресом проживания',
			top:-74,
			left:196,
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
	.on('click', '#zayav .ajaxNext', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = zayavFilter();
		send.op = 'zayav_next';
		send.page = $(this).attr('val');
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.zayav_unit', function() {
		document.location.href = URL + '&p=zayav&d=info&id=' + $(this).attr('val');
	})
	.on('click', '.zakaz_status', function() {
		var t = $(this),
			html = '<table class="zamer-status-edit">' +
				'<tr><td class="label topi">Статус заказа:<td><INPUT type="hidden" id="edit_zakaz" value="' + ZAYAV.status + '">' +
				'</table>',
			dialog = _dialog({
				width:400,
				top:30,
				head:'Изменение статуса заказа',
				content:html,
				butSubmit:'Применить',
				submit:submit
			});
			$('#edit_zakaz')._radio({
				bottom:20,
				spisok:[
					{uid:1,title:'Ожидает выполнения'},
					{uid:2,title:'Выполнен'},
					{uid:3,title:'Отменён'}
				]
			});
		function submit() {
			var	send = {
				op:'zakaz_status',
				zayav_id:ZAYAV.id,
				status:$('#edit_zakaz').val()
			};
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
	})
	.on('click', '.zamer_status', function() {
		var t = $(this),
			id = typeof ZAYAV != 'undefined' ? ZAYAV.id : t.attr('val'),
			dialog = _dialog({
				width:400,
				top:30,
				head:'Изменение статуса замера',
				load:1,
				butSubmit:'Применить',
				submit:submit
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
			dialog.content.html('<table class="zamer-status-edit">' +
				'<tr><td class="label topi">Результат замера:<td><INPUT type="hidden" id="edit_zamer" value="-1">' +
				'<tr class="tr_data dn"><td class="label">Новое время:<td class="zayav-zamer-dtime">' +
				'<tr class="tr_data dn"><td class="label">Длительность:' +
				'   <td><INPUT TYPE="hidden" id="zamer_duration" value="' + res.dur + '" />' +
						'<a class="zamer_table" val="' + id + '">Таблица замеров</a>' +
				'<tr class="tr_prim dn"><td class="label top">Комментарий:<td><textarea id="prim"></textarea>' +
				'</table>');
			$('#edit_zamer')._radio({
				bottom:20,
				spisok:[
					{uid:1,title:'Указать другое время<span>Замер будет перенесён на другое время.</span>'},
					{uid:2,title:'Выполнен<span>Замер выполнен успешно. Заявка будет переведена на заключение договора.</span>'},
					{uid:3,title:'Отмена<span>Отмена заявки по какой-либо причине.</span>'}
				],
				func:function(v) {
					$('.tr_data')[(v == 1 ? 'remove' : 'add') + 'Class']('dn');
					$('.tr_prim').removeClass('dn');
				}
			});
			zayavZamerDtime(res);
		}
		function submit() {
			var send = {
				op:'zamer_status',
				zayav_id:id,
				status:$('#edit_zamer').val(),
				zamer_day:$('#zamer_day').val(),
				zamer_hour:$('#zamer_hour').val(),
				zamer_min:$('#zamer_min').val(),
				zamer_duration:$('#zamer_duration').val(),
				prim:$('#prim').val()
			};
			if(send.status == -1) err('Выберите вариант.');
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
			$(this).vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				top:-58,
				left:-5,
				indent:40,
				remove:1,
				show:1
			});
		}
	})
	.on('click', '.set_status', function() {
		var t = $(this),
			html = '<table class="zamer-status-edit">' +
				'<tr><td class="label topi">Статус установки:<td><INPUT type="hidden" id="edit_set" value="' + ZAYAV.status + '">' +
				'</table>',
			dialog = _dialog({
				width:400,
				top:30,
				head:'Изменение статуса установки',
				content:html,
				butSubmit:'Применить',
				submit:submit
			});
		$('#edit_set')._radio({
			bottom:20,
			spisok:[
				{uid:1,title:'Ожидает выполнения'},
				{uid:2,title:'Выполнена'},
				{uid:3,title:'Отменена'}
			]
		});
		function submit() {
			var	send = {
				op:'set_status',
				zayav_id:ZAYAV.id,
				status:$('#edit_set').val()
			};
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
	})

	.on('click', '.remind_calendar .on', function() {
		var t = $(this),
			cal = t.parent().parent(),
			send = {
				op:'remind_day',
				day:t.attr('val')
			};
		if(cal.hasClass('busy'))
			return;
		cal.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				$('#remind .left').html(res.html);
				$('#remind').removeClass('y');
			}
			cal.removeClass('busy');
		}, 'json');
	})
	.on('click', '#remind .fmon', function() {
		var t = $(this);
		$('.right .remind_calendar').html(t.next().html());
		$('.goyear span').html(t.html());
		$('#remind').removeClass('y');
	})

	.on('click', '#history_next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = {
				op:'history_next',
				page:$(this).attr('val')
//				worker:$('#report_history_worker').val(),
//				action:$('#report_history_action').val(),
			};
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})

	.on('click', '#money_next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = {
				op:'money_next',
				page:$(this).attr('val'),
				limit:$('#money_limit').val(),
				client_id:$('#money_client_id').val(),
				zayav_id:$('#money_zayav_id').val()
			};
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})

	.on('click', '#setup_worker .add', function() {
		var html = '<div id="setup_worker_add">' +
				'<h1>Ссылка на страницу или ID пользователя ВКонтакте:</h1>' +
				'<input type="text" />' +
				'<DIV class="vkButton"><BUTTON>Найти</BUTTON></DIV>' +
				'</div>',
			dialog = _dialog({
				top:50,
				width:360,
				head:'Добавление нового сотрудника',
				content:html,
				butSubmit:'Добавить',
				submit:submit
			}),
			user_id,
			input = dialog.content.find('input'),
			but = input.next();
		input.focus().keyEnter(user_find);
		but.click(user_find);

		function user_find() {
			if(but.hasClass('busy'))
				return;
			user_id = false;
			var send = {
				user_ids:$.trim(input.val()),
				fields:'photo_50',
				v:5.2
			};
			if(!send.user_ids)
				return;
			but.addClass('busy').next('.res').remove();
			VK.api('users.get', send, function(data) {
				but.removeClass('busy');
				if(data.response) {
					var u = data.response[0],
						html = '<TABLE class="res">' +
							'<tr><td class="photo"><IMG src=' + u.photo_50 + '>' +
							'<td class="name">' + u.first_name + ' ' + u.last_name +
						'</TABLE>';
					but.after(html);
					user_id = u.id;
				}
			});
		}
		function submit() {
			if(!user_id) {
				err('Не выбран пользователь', -47);
				return;
			}
			var send = {
				op:'setup_worker_add',
				id:user_id
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				dialog.abort();
				if(res.success) {
					dialog.close();
					_msg('Новый сотрудник успешно добавлен.');
					$('#spisok').html(res.html);
				} else
					err(res.text, -60);
			}, 'json');
		}
		function err(msg, top) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:top,
				left:92
			});
		}
	})
	.on('click', '#setup_worker .img_del', function() {
		var u = $(this);
		while(!u.hasClass('unit'))
			u = u.parent();
		var dialog = _dialog({
			top:110,
			width:250,
			head:'Удаление сотрудника',
			content:'<center>Подтвердите удаление сотрудника.</center>',
			butSubmit:'Удалить',
			submit:submit
		});
		function submit() {
			var send = {
				op:'setup_worker_del',
				viewer_id:u.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('Сотрудник удален.');
					$('#spisok').html(res.html);
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_product .add', function() {
		var t = $(this),
			html = '<table style="border-spacing:10px">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" style="width:250px" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'Добавление нового наименования изделия',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'setup_product_add',
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:99,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Внесено!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_product .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name a'),
			dog = t.find('.dog').html() ? 1 : 0,
			html = '<table style="border-spacing:10px">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" style="width:250px" value="' + name.html() + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'Редактирование наименования изделия',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'setup_product_edit',
				id:id,
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:99,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Сохранено!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_product .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление изделия',
				content:'<center><b>Подтвердите удаление изделия.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'TR')
				t = t.parent();
			var send = {
				op:'setup_product_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					t.remove();
					dialog.close();
					_msg('Удалено!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_product_sub .add', function() {
		var t = $(this),
			html = '<table style="border-spacing:10px">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" style="width:250px" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'Добавление нового подвида изделия',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'setup_product_sub_add',
				product_id:PRODUCT_ID,
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:99,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Внесено!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_product_sub .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var name = t.find('.name'),
			html = '<table style="border-spacing:10px">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" style="width:250px" value="' + name.html() + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'Редактирование подвида изделия',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'setup_product_sub_edit',
				id:t.attr('val'),
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:99,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						name.html(send.name);
						dialog.close();
						_msg('Сохранено!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_product_sub .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление подвида',
				content:'<center><b>Подтвердите удаление подвида изделия.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'TR')
				t = t.parent();
			var send = {
				op:'setup_product_sub_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					t.remove();
					dialog.close();
					_msg('Удалено!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_prihodtype .add', function() {
		var t = $(this),
			html = '<table style="border-spacing:10px">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" style="width:210px" />' +
				'<tr><td class="label r">Возможность внесения в кассу:<td><input id="kassa_put" type="hidden" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:440,
				head:'Добавление нового вида платежа',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#kassa_put')._check();
		$('#kassa_put_check').vkHint({
			msg:'При внесении платежа дополнительно<br />будет задаваться вопрос:<br />"Деньги поступили в кассу или нет?"',
			top:-83,
			left:-91,
			indent:90,
			delayShow:1000
		});
		function submit() {
			var send = {
				op:'setup_prihodtype_add',
				name:$('#name').val(),
				kassa_put:$('#kassa_put').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Внесено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_prihodtype .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'DD')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name').html(),
			kassa = t.find('.kassa').html() ? 1 : 0,
			html = '<table style="border-spacing:10px">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" style="width:210px" value="' + name + '" />' +
				'<tr><td class="label r">Возможность внесения в кассу:<td><input id="kassa_put" type="hidden" value="' + kassa + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:440,
				head:'Редактирование вида платежа',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#kassa_put')._check();
		$('#kassa_put_check').vkHint({
			msg:'При внесении платежа дополнительно<br />будет задаваться вопрос:<br />"Деньги поступили в кассу или нет?"',
			top:-83,
			left:-91,
			indent:90,
			delayShow:1000
		});
		function submit() {
			var send = {
				op:'setup_prihodtype_edit',
				id:id,
				name:$('#name').val(),
				kassa_put:$('#kassa_put').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Сохранено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_prihodtype .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление вида платежа',
				content:'<center><b>Подтвердите удаление вида платежа.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'setup_prihodtype_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('Удалено!');
					sortable();
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_zayavrashod .add', function() {
		var t = $(this),
			html = '<table style="border-spacing:10px">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="50" style="width:210px" />' +
				'<tr><td class="label r">Текстовое поле:<td><input id="show_txt" type="hidden" />' +
				'<tr><td class="label r">Список сотрудников:<td><input id="show_worker" type="hidden" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:440,
				head:'Добавление новой категории расхода заявки',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#show_txt')._check({func:checktest});
		$('#show_worker')._check({func:checktest});
		function submit() {
			var send = {
				op:'setup_zayavrashod_add',
				name:$('#name').val(),
				show_txt:$('#show_txt').val(),
				show_worker:$('#show_worker').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Внесено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function checktest(id, attr) {
			if(id == 1)
				if(attr == 'show_txt')
					$('#show_worker')._check(0);
				else
					$('#show_txt')._check(0);
		}
	})
	.on('click', '#setup_zayavrashod .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'DD')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name').html(),
			txt = t.find('.txt').html() ? 1 : 0,
			worker = t.find('.worker').html() ? 1 : 0,
			html = '<table style="border-spacing:10px">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="50" style="width:210px" value="' + name + '" />' +
				'<tr><td class="label r">Текстовое поле:<td><input id="show_txt" type="hidden" value="' + txt + '" />' +
				'<tr><td class="label r">Список сотрудников:<td><input id="show_worker" type="hidden" value="' + worker + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:440,
				head:'Редактирование категории расхода заявки',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#show_txt')._check({func:checktest});
		$('#show_worker')._check({func:checktest});
		function submit() {
			var send = {
				op:'setup_zayavrashod_edit',
				id:id,
				name:$('#name').val(),
				show_txt:$('#show_txt').val(),
				show_worker:$('#show_worker').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Сохранено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function checktest(id, attr) {
			if(id == 1)
				if(attr == 'show_txt')
					$('#show_worker')._check(0);
				else
					$('#show_txt')._check(0);
		}
	})
	.on('click', '#setup_zayavrashod .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление категории расхода заявки',
				content:'<center><b>Подтвердите удаление<br />категории расхода заявки.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'setup_zayavrashod_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('Удалено!');
					sortable();
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.ready(function() {
		if($('#client').length > 0) {
			window.cFind = $('#find')._search({
				width:602,
				focus:1,
				enter:1,
				txt:'Начните вводить данные клиента',
				func:clientSpisokLoad
			});
			$('#buttonCreate').vkHint({
				msg:'<B>Внесение нового клиента в базу.</B><br /><br />' +
					'После внесения Вы попадаете на страницу с информацией о клиенте для дальнейших действий.<br /><br />' +
					'Клиентов также можно добавлять при <A href="' + URL + '&p=zayav&d=add&back=client">создании новой заявки</A>.',
				ugol:'right',
				width:215,
				top:-38,
				left:-250,
				indent:40,
				delayShow:1000,
				correct:0
			}).click(clientAdd);
			$('#dolg')._check(clientSpisokLoad);
			$('#dolg_check').vkHint({
				msg:'<b>Список должников.</b><br /><br />' +
					'Выводятся клиенты, у которых баланс менее 0. Также в результате отображается общая сумма долга.',
				ugol:'right',
				width:150,
				top:-6,
				left:-185,
				indent:20,
				delayShow:1000,
				correct:0
			});
		}
		if($('#clientInfo').length > 0) {
			$('#dopLinks .link').click(function() {
				$('#dopLinks .link').removeClass('sel');
				$(this).addClass('sel');
				var val = $(this).attr('val');
				$('.res').css('display', val == 'zayav' ? 'block' : 'none');
				$('#zayav_filter').css('display', val == 'zayav' ? 'block' : 'none');
				$('#zayav_spisok').css('display', val == 'zayav' ? 'block' : 'none');
				$('#money_spisok').css('display', val == 'money' ? 'block' : 'none');
				$('#remind_spisok').css('display', val == 'remind' ? 'block' : 'none');
				$('#comments').css('display', val == 'comm' ? 'block' : 'none');
				$('#histories').css('display', val == 'hist' ? 'block' : 'none');
			});
			$('.cedit').click(function() {
				var html = '<table class="client-add">' +
					'<tr><td class="label">Имя:<td><input type="text" id="fio" maxlength="100" value="' + CLIENT.fio + '">' +
					'<tr><td class="label">Телефон:<td><input type="text" id="telefon" maxlength="100" value="' + CLIENT.telefon + '">' +
					'<tr><td class="label">Адрес:<td><input type="text" id="adres" maxlength="100" value="' + CLIENT.adres + '">' +
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
					width:380,
					content:html,
					butSubmit:'Сохранить',
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
							msg:'<span class="red">Не указано имя клиента.</span>',
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
								_msg('Данные клиента изменены.');
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
			$('#status').rightLink(clientZayavSpisokLoad);
		}

		if($('#zayav').length > 0) {
			window.zFind = $('#find')._search({
				width:153,
				focus:1,
				txt:'Быстрый поиск...',
				enter:1,
				func:zayavSpisokLoad
			});
//			zFind.inp(ZAYAV.find);
//			$('#desc')._check(zayavSpisokLoad);
//			$('#category')._radio(zayavSpisokLoad);
//			$('#status').rightLink(zayavSpisokLoad);
		}
		if($('.zayav-info').length > 0) {
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
			$('#dogovor_action').linkMenu({
				head:'Не заключен',
				spisok:[
					{uid:1, title:'Заключить договор'},
					{uid:2, title:'Перевести заявку в категорию "Требуется договор"'}
				],
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
				},
				nosel:1
			});
			$('.reneg').click(dogovorCreate);
			$('.zakaz_edit').click(function() {
				var html = '<table class="zayav-info-edit">' +
						'<tr><td class="label">Клиент:      <td>' + ZAYAV.client_fio +
						'<tr><td class="label topi">Изделие:<td id="product">' +
						'<tr><td><td><input type="text" id="zakaz_txt" placeholder="либо укажите содержание заказа вручную.." maxlength="300" value="' + ZAYAV.zakaz_txt + '">' +
						'<tr><td class="label">Номер ВГ:	   <td><INPUT type="text" id="nomer_vg" maxlength="30" value="' + ZAYAV.nomer_vg + '" />' +
						'<tr><td class="label">Номер Ж: 	   <td><INPUT type="text" id="nomer_g" maxlength="30" value="' + ZAYAV.nomer_g + '" />' +
						'<tr><td class="label">Номер Д: 	   <td><INPUT type="text" id="nomer_d" maxlength="30" value="' + ZAYAV.nomer_d + '" />' +
						'</table>',
					dialog = _dialog({
						width:500,
						top:30,
						head:'Заказ №' + ZAYAV.id + ' - Редактирование',
						content:html,
						butSubmit:'Сохранить',
						submit:submit
					});
				$('#product').productList(ZAYAV.product);
				$('#product_id').vkSel({
					width:142,
					display:'inline-block',
					title0:'Изделие не указано',
					spisok:PRODUCT_SPISOK
				});
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
						head:'Замер №' + ZAYAV.id + ' - Редактирование',
						content:html,
						butSubmit:'Сохранить',
						submit:submit
					});
				$('#product').productList(ZAYAV.product);
				$('#product_id').vkSel({
					width:142,
					display:'inline-block',
					title0:'Изделие не указано',
					spisok:PRODUCT_SPISOK
				});
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
				$('#product_id').vkSel({
					width:142,
					display:'inline-block',
					title0:'Изделие не указано',
					spisok:PRODUCT_SPISOK
				});
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
						'</table>',
					dialog = _dialog({
						width:500,
						top:30,
						head:'Установка №' + ZAYAV.id + ' - Редактирование',
						content:html,
						butSubmit:'Сохранить',
						submit:submit
					});
				$('#product').productList(ZAYAV.product);
				$('#product_id').vkSel({
					width:142,
					display:'inline-block',
					title0:'Изделие не указано',
					spisok:PRODUCT_SPISOK
				});
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
				var html = '<TABLE class="accrual-add">' +
					'<tr><td class="label">Сумма: <td><input type="text" id="sum" class="money" maxlength="6" /> руб.' +
					'<tr><td class="label">Примечание:<em>(не обязательно)</em><td><input type="text" id="prim" maxlength="100" />' +
					'</TABLE>';
				var dialog = _dialog({
					top:60,
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
					if(!REGEXP_NUMERIC.test(send.sum)) {
						msg = 'Некорректно указана сумма.';
						$('#sum').focus();
					} else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							dialog.abort();
							if(res.success) {
								dialog.close();
								_msg('Начисление успешно произведено.');
								$('#money_spisok').html(res.html);
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
			$('.rashod-edit').click(function() {
				var html = '<table class="zayav-rashod-edit">' +
						'<tr><td class="label">Заявка: <td><b>' + ZAYAV.head + '</b>' +
						'<tr><td class="label topi">Расходы:<td id="zrs">' +
						'</table>',
					dialog = _dialog({
						width:470,
						top:30,
						head:'Изменение расходов заявки',
						content:html,
						butSubmit:'Сохранить',
						submit:submit
					});
				$('#zrs').zayavRashod();
				function submit() {
					var send = {
						op:'zayav_rashod_edit',
						zayav_id:ZAYAV.id,
						rashod:$('#zrs').zayavRashod('get')
					};
					if(send.spisok == 'sum_error') err('Некорректно указана сумма');
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								$('.zrashod').html(res.html);
								dialog.close();
								_msg('Сохранено.');
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
		}

		if($('#remind').length > 0) {
			$('.goyear').click(function() {
				$('#remind').addClass('y');
			});
		}

		if($('#setup_rules').length > 0) {
			$('#rules_appenter')._check(function(v, id) {
				$('.app-div')[(v == 0 ? 'add' : 'remove') + 'Class']('dn');
				$('.setup-div').addClass('dn');
				setupRulesSet(v, id);
				$('#rules_setup')._check(0);
				$('#rules_worker')._check(0);
				$('#rules_product')._check(0);
				$('#rules_prihodtype')._check(0);
				$('#rules_zayavrashod')._check(0);
				$('#rules_historyshow')._check(0);
			});
			$('#rules_setup')._check(function(v, id) {
				$('.setup-div')[(v == 0 ? 'add' : 'remove') + 'Class']('dn');
				setupRulesSet(v, id);
				$('#rules_worker')._check(0);
				$('#rules_product')._check(0);
				$('#rules_prihodtype')._check(0);
				$('#rules_zayavrashod')._check(0);
			});
			$('#rules_worker')._check(setupRulesSet);
			$('#rules_rekvisit')._check(setupRulesSet);
			$('#rules_product')._check(setupRulesSet);
			$('#rules_prihodtype')._check(setupRulesSet);
			$('#rules_zayavrashod')._check(setupRulesSet);
			$('#rules_historyshow')._check(setupRulesSet);
		}
		if($('#setup_rekvisit').length > 0) {
			$('.vkButton').click(function() {
				var t = $(this),
					send = {
						op:'setup_rekvisit',
						org_name:$('#org_name').val(),
						ogrn:$('#ogrn').val(),
						inn:$('#inn').val(),
						kpp:$('#kpp').val(),
						yur_adres:$('#yur_adres').val(),
						telefon:$('#telefon').val(),
						ofice_adres:$('#ofice_adres').val()
					};
				t.addClass('busy');
				$.post(AJAX_MAIN, send, function(res) {
					t.removeClass('busy');
					if(res.success)
						_msg('Информация сохранена.');
				}, 'json');
			});
		}
	});