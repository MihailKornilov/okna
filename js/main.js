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
		$.post(AJAX_MAIN, send, function (res) {
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
		$.post(AJAX_MAIN, send, function (res) {
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
	zayavFilter = function () {
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
		$.post(AJAX_MAIN, send, function (res) {
			$('#zayav .result').html(res.all);
			$('#zayav #spisok').html(res.html);
			$('#mainLinks').removeClass('busy');
		}, 'json');
	},
	zayavInfoMoneyUpdate = function() {
		var send = {
			op:'zayav_money_update',
			id:ZAYAV.id
		};
		$.post(AJAX_MAIN, send, function (res) {
			if(res.success) {
				$('b.acc').html(res.acc);
				$('.acc_tr')[(res.acc == 0 ? 'add' : 'remove') + 'Class']('dn');
				$('b.op').html(res.opl);
				$('.op_tr')[(res.opl == 0 ? 'add' : 'remove') + 'Class']('dn');
				$('.dopl')
					[(res.dopl == 0 ? 'add' : 'remove') + 'Class']('dn')
					.html((res.dopl > 0 ? '+' : '') + res.dopl);
				var del = res.acc == 0 && res.opl == 0;
				$('.delete')[(del ? 'remove' : 'add') + 'Class']('dn');
			}
		}, 'json');
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
			html = '<table id="ptab'+ num + '" class="ptab" val="' + num + '"><tr>' +
					'<td class="td"><input type="hidden" id="' + attr_id + '" value="' + (v[0] || 0) + '" />' +
								   '<input type="hidden" id="' + attr_subid + '" value="' + (v[1] || 0) + '" />' +
					'<td class="td"><input type="text" id="' + attr_count + '" value="' + (v[2] || '') + '" class="count" maxlength="3" /> шт.' +
					(num > 1 ? '<div class="img_del" val="' + num + '"></div>' : '') +
				'</table>';
		add.before(html);
		$('#ptab' + num).find('.img_del').click(function() {
			$('#ptab' + $(this).attr('val')).remove();
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
			width:150,
			display:'inline-block',
			title0:'Подвид не указан',
			spisok:PRODUCT_SUB_SPISOK[id],
			func:function() {
				$('#' + attr_count).focus();
			}
		});
	}
};

$(document)
	.on('click', '#client .ajaxNext', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = clientFilter();
		send.op = 'client_next';
		send.page = next.attr('val');
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function (res) {
			if(res.success) {
				next.remove();
				$('#client .left').append(res.spisok);
			} else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#clientInfo .ajaxNext', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = clientZayavFilter();
		send.op = 'client_zayav_next';
		send.page = $(this).attr('val');
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})

	.on('click', '#zayav #filter_break', function() {
		zFind.clear();
		//$('#sort')._radio(1);
		$('#desc')._check(0);
		$('#status').rightLink(0);
		zayavSpisokLoad();
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
			var msg,
				send = {
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
			if(send.client_id == 0) msg = 'Не выбран клиент';
			else if(!send.product) msg = 'Не указано изделие';
			else if(send.product == 'count_error') msg = 'Некорректно введено количество изделий';
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function (res) {
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
		$.post(AJAX_MAIN, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.zayav_unit', function() {
		document.location.href = URL + '&p=zayav&d=info&id=' + $(this).attr('val');
	})
	.on('click', '#zayavInfo .delete', function() {
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
	})
	.on('click', '#zayavInfo .acc_del', function() {
		var send = {
			op:'zayav_accrual_del',
			id:$(this).attr('val')
		};
		var tr = $(this).parent().parent();
		tr.html('<td colspan="4" class="deleting">Удаление... <img src=/img/upload.gif></td>');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				tr.find('.deleting').html('Начисление удалено. <a class="acc_rest" val="' + send.id + '">Восстановить</a>');
				zayavInfoMoneyUpdate();
			}
		}, 'json');
	})
	.on('click', '#zayavInfo .acc_rest', function() {
		var send = {
				op:'zayav_accrual_rest',
				id:$(this).attr('val')
			},
			t = $(this),
			tr = t.parent().parent();
		t.after('<img src=/img/upload.gif>').remove();
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				tr.after(res.html).remove();
				zayavInfoMoneyUpdate();
			}
		}, 'json');
	})
	.on('click', '#zayavInfo .op_del', function() {
		var send = {
			op:'zayav_oplata_del',
			id:$(this).attr('val')
		};
		var tr = $(this).parent().parent();
		tr.html('<td colspan="4" class="deleting">Удаление... <img src=/img/upload.gif></td>');
		$.post(AJAX_MAIN, send, function (res) {
			if(res.success) {
				tr.find('.deleting').html('Платёж удалён. <a class="op_rest" val="' + send.id + '">Восстановить</a>');
				zayavInfoMoneyUpdate();
			}
		}, 'json');
	})
	.on('click', '#zayavInfo .op_rest', function() {
		var send = {
				op:'zayav_oplata_rest',
				id:$(this).attr('val')
			},
			t = $(this),
			tr = t.parent().parent();
		t.after('<img src=/img/upload.gif>').remove();
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				tr.after(res.html).remove();
				zayavInfoMoneyUpdate();
			}
		}, 'json');
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
			$.post(AJAX_MAIN, {op:'zamer_info_get',zayav_id:id}, function (res) {
				if(res.success)
					info_get(res);
				else
					dialog.loadError();
			}, 'json');
		else
			info_get(ZAYAV);
		function info_get(res) {
			dialog.content.html('<table class="zamer-status-edit">' +
				'<tr><td class="label top">Результат замера:<td><INPUT type="hidden" id="edit_zamer" value="-1">' +
				'<tr class="tr_data dn"><td class="label">Новое время:<td class="zayav-zamer-dtime">' +
				'<tr class="tr_data dn"><td class="label">Длительность:<td><INPUT TYPE="hidden" id="zamer_duration" value="' + res.dur + '" />' +
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
			var msg,
				send = {
					op:'zamer_status',
					zayav_id:id,
					status:$('#edit_zamer').val(),
					zamer_day:$('#zamer_day').val(),
					zamer_hour:$('#zamer_hour').val(),
					zamer_min:$('#zamer_min').val(),
					zamer_duration:$('#zamer_duration').val(),
					prim:$('#prim').val()
				};
			if(send.status == -1) msg = 'Выберите вариант.';
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('Данные изменены!');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
			if(msg)
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
		$.post(AJAX_MAIN, send, function (res) {
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

	.on('click', '#report_history_next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = {
				op:'report_history_next',
				page:$(this).attr('val')
//				worker:$('#report_history_worker').val(),
//				action:$('#report_history_action').val(),
			};
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function (res) {
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
							'<TR><TD class="photo"><IMG src=' + u.photo_50 + '>' +
							'<TD class="name">' + u.first_name + ' ' + u.last_name +
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
				'<tr><td class="label r">Требуется заключение договора:<td><input id="dogovor" type="hidden" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'Добавление нового наименования изделия',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#dogovor')._check();
		function submit() {
			var send = {
				op:'setup_product_add',
				name:$('#name').val(),
				dogovor:$('#dogovor').val()
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
				'<tr><td class="label r">Требуется заключение договора:<td><input id="dogovor" type="hidden" value="' + dog + '" />' +
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
		$('#dogovor')._check();
		function submit() {
			var send = {
				op:'setup_product_edit',
				id:id,
				name:$('#name').val(),
				dogovor:$('#dogovor').val()
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
								$('#clientInfo .left:first').html(res.html);
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
			$('.dogovor_create').click(function() {
				var html = '<table class="zayav-dogovor">' +
						'<tr><td class="label">Фио клиента:<td><INPUT type="text" id="fio" value="' + ZAYAV.fio + '">' +
						'<tr><td class="label">Адрес установки:<td><INPUT type="text" id="adres" value="' + ZAYAV.adres + '">' +
						'<tr><td class="label">Паспорт:' +
							'<td>Серия:<input type="text" id="pasp_seria" maxlength="8" value="' + ZAYAV.pasp_seria + '">' +
								'Номер:<input type="text" id="pasp_nomer" maxlength="10" value="' + ZAYAV.pasp_nomer + '">' +
						'<tr><td><td><span class="l">Прописка:</span><input type="text" id="pasp_adres" maxlength="100" value="' + ZAYAV.pasp_adres + '">' +
						'<tr><td><td><span class="l">Кем выдан:</span><input type="text" id="pasp_ovd" maxlength="100" value="' + ZAYAV.pasp_ovd + '">' +
						'<tr><td><td><span class="l">Когда выдан:</span><input type="text" id="pasp_data" maxlength="100" value="' + ZAYAV.pasp_data + '">' +
						'<tr><td colspan="2">' +
								'<div class="i">' +
									'<b>Внимание!</b>' +
									'Все поля обязательны для заполнения. ' +
									'Внимательно проверьте правильность всех введённых данных. ' +
									'После нажатия кнопки "Заключить договор" операцию отменить будет невозможно.' +
								'</div>' +
						'<tr><td colspan="2">' +
							'<a id="preview">Предварительный просмотр</a>' +
							'<form action="' + AJAX_MAIN + '" method="post" id="preview-form" target="_blank"></form>' +
						'</table>',
					dialog = _dialog({
						width:416,
						top:10,
						head:'Заключение договора',
						content:html,
						butSubmit:'Заключить договор',
						submit:submit
					});
				$('#preview').click(function() {
					var send = valuesTest();
					if(send) {
						send.op = 'dogovor_preview';
						var form = '';
						for(var i in send)
							form += '<input type="hidden" name="' + i + '" value="' + send[i] + '">';
						$('#preview-form').html(form).submit();
					}
				});
				function valuesTest() {
					var msg,
						send = {
							zayav_id:ZAYAV.id,
							fio:$('#fio').val(),
							adres:$('#adres').val(),
							pasp_seria:$('#pasp_seria').val(),
							pasp_nomer:$('#pasp_nomer').val(),
							pasp_adres:$('#pasp_adres').val(),
							pasp_ovd:$('#pasp_ovd').val(),
							pasp_data:$('#pasp_data').val()
						};
					if(!send.fio) { msg = 'Не указано Фио клиента'; $('#fio').focus(); }
					else if(!send.adres) { msg = 'Не указан адрес'; $('#adres').focus(); }
					else if(!send.pasp_seria) { msg = 'Не указана серия паспорта'; $('#pasp_seria').focus(); }
					else if(!send.pasp_nomer) { msg = 'Не указан номер паспорта'; $('#pasp_nomer').focus(); }
					else if(!send.pasp_adres) { msg = 'Не указана прописка'; $('#pasp_adres').focus(); }
					else if(!send.pasp_ovd) { msg = 'Не указана организация, выдавшая паспорт'; $('#pasp_ovd').focus(); }
					else if(!send.pasp_data) { msg = 'Не указана дата выдачи паспорта'; $('#pasp_data').focus(); }
					else return send;

					dialog.bottom.vkHint({
						msg:'<span class="red">' + msg + '</span>',
						top:-47,
						left:100,
						indent:50,
						show:1,
						remove:1
					});
					return false;
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
							} else
								dialog.abort();
						}, 'json');
					}
				}
			});
/*			$('.zedit').click(function() {
				var html = '<table class="zayav-info-edit">' +
						'<tr><td class="label r">Клиент:		 <td><INPUT type="hidden" id="client_id" value="' + ZAYAV.client_id + '">' +
						'<tr><td class="label r top">Изделие:	<td id="product">' +
						'<tr><td class="label r">Адрес установки:<td><INPUT type="text" id="adres_set" maxlength="100" value="' + ZAYAV.adres_set + '" />' +
						'<tr><td class="label r">Номер ВГ:	   <td><INPUT type="text" id="nomer_vg" maxlength="30" value="' + ZAYAV.nomer_vg + '" />' +
					'</table>',
					dialog = _dialog({
						width:500,
						top:30,
						head:'Заявка №' + ZAYAV.id + ' - Редактирование',
						content:html,
						butSubmit:'Сохранить',
						submit:submit
					});
				$('#client_id').clientSel();
				$('#product').productList(ZAYAV.product);
				$('#vkSel_client_id').vkHint({
					msg:'Если изменяется клиент, то начисления и платежи заявки применяются на нового клиента.',
					width:200,
					top:-83,
					left:-2,
					delayShow:1500
				});
				$('#product_id').vkSel({
					width:142,
					display:'inline-block',
					title0:'Изделие не указано',
					spisok:PRODUCT_SPISOK
				});

				function submit() {
					var msg,
						send = {
							op:'zayav_edit',
							zayav_id:ZAYAV.id,
							client_id:$('#client_id').val(),
							//nomer_dog:$('#nomer_dog').val(),
							nomer_vg:$('#nomer_vg').val(),
							product:$('#product').productList('get'),
							adres_set:$('#adres_set').val()
						};
					if(send.client_id == 0) msg = 'Не выбран клиент';
					else if(!send.product) msg = 'Не указано изделие';
					else if(send.product == 'count_error') msg = 'Некорректно введено количество изделий';
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function (res) {
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
							left:141,
							indent:50,
							show:1,
							remove:1
						});
				}
			});
			$('.op_add').click(function() {
				var html =
					'<TABLE class="zayav_oplata_add">' +
						'<TR><TD class="label">Вид платежа:<TD><input type="hidden" id="prihod_type" value="0">' +
							'<a href="' + URL + '&p=setup&d=prihodtype" class="img_edit" title="Перейти к настройке видов платежей"></a>' +
						'<TR><TD class="label">Сумма:<TD><input type="text" id="sum" class="money" maxlength="5"> руб.' +
						'<TR class="tr_kassa dn"><TD class="label">Деньги поступили в кассу?:<TD><input type="hidden" id="kassa" value="-1">' +
						'<TR><TD class="label">Примечание:<em>(не обязательно)</em><TD><input type="text" id="prim">' +
					'</TABLE>';
				var dialog = _dialog({
					top:60,
					width:440,
					head:'Заявка №' + ZAYAV.id + ' - Внесение платежа',
					content:html,
					submit:submit
				});
				$('#sum').focus();
				$('#sum,#prim').keyEnter(submit);
				$('#prihod_type').vkSel({
					display:'inline-block',
					width:180,
					title0:'Не указан',
					spisok:PRIHOD_SPISOK,
					func:function(uid) {
						$('#kassa')._radio(-1);
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
					var msg,
						send = {
							op:'zayav_oplata_add',
							zayav_id:ZAYAV.id,
							type:$('#prihod_type').val(),
							sum:$('#sum').val(),
							kassa:$('#kassa').val(),
							prim:$.trim($('#prim').val())
						};
					if(send.type == 0) msg = 'Не указан вид платежа.';
					else if(!REGEXP_NUMERIC.test(send.sum)) {
						msg = 'Некорректно указана сумма.';
						$('#sum').focus();
					} else if(PRIHODKASSA_ASS[send.type] && send.kassa == -1) msg = 'Укажите, деньги поступили в кассу или нет.';
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function (res) {
							if(res.success) {
								dialog.close();
								_msg('Платёж успешно внесён!');
								$('._spisok._money').append(res.html);
								zayavInfoMoneyUpdate();
							} else
								dialog.abort();
						}, 'json');
					}

					if(msg)
						dialog.bottom.vkHint({
							msg:'<SPAN class="red">' + msg + '</SPAN>',
							remove:1,
							indent:40,
							show:1,
							top:-48,
							left:135
						});
				}
			});
			$('.acc_add').click(function() {
				var html = '<TABLE class="zayav_accrual_add">' +
					'<tr><td class="label">Сумма: <TD><input type="text" id="sum" class="money" maxlength="6" /> руб.' +
					'<tr><td class="label">Примечание:<em>(не обязательно)</em><TD><input type="text" id="prim" maxlength="100" />' +
					'</TABLE>';
				var dialog = _dialog({
					top:60,
					width:420,
					head:'Заявка №' + ZAYAV.id + ' - Начисление за выполненную работу',
					content:html,
					submit:submit
				});
				$('#sum').focus();
				$('#sum,#prim').keyEnter(submit);

				function submit() {
					var msg,
						send = {
							op:'zayav_accrual_add',
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
								_msg('Начисление успешно произведено!');
								$('._spisok._money').append(res.html);
								zayavInfoMoneyUpdate();
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
*/
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
			});
			$('#rules_setup')._check(function(v, id) {
				$('.setup-div')[(v == 0 ? 'add' : 'remove') + 'Class']('dn');
				setupRulesSet(v, id);
				$('#rules_worker')._check(0);
				$('#rules_product')._check(0);
				$('#rules_prihodtype')._check(0);
			});
			$('#rules_worker')._check(setupRulesSet);
			$('#rules_rekvisit')._check(setupRulesSet);
			$('#rules_product')._check(setupRulesSet);
			$('#rules_prihodtype')._check(setupRulesSet);
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