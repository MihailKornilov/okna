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
			category:$('#zayav').attr('val'),
			product:$('#product_id').val(),
			status:$('#status').val()
		};
	},
	zayavSpisok = function() {
		var send = zayavFilter();
		//$('.condLost')[(send.find ? 'add' : 'remove') + 'Class']('hide');
		send.op = 'zayav_spisok';
		$('#mainLinks').addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			$('#mainLinks').removeClass('busy');
			if(res.success) {
				$('#zayav .result').html(res.result);
				$('#zayav #spisok').html(res.spisok);
			}
		}, 'json');
	},
	dogovorCreate = function() {
		var html = '<table class="zayav-dogovor">' +
				'<tr><td colspan="2">' +
		(DOG.id ? '<div class="i per">' +
						'��� <b>�������������� ��������</b> ��������� ����� ������� �������� � ��������� �����. ' +
						'���������� ������ ������ ��������. ����� ����� ������� ������ �������.' +
					'</div>'
		: '') +
				'<tr><td class="label r">��� �������:<td><input type="text" id="fio" value="' + DOG.fio + '" />' +
				'<tr><td class="label r">�����:<td><input type="text" id="adres" value="' + DOG.adres + '" />' +
				'<tr><td class="label r">�������:' +
					'<td>�����:<input type="text" id="pasp_seria" maxlength="8" value="' + DOG.pasp_seria + '" />' +
						'�����:<input type="text" id="pasp_nomer" maxlength="10" value="' + DOG.pasp_nomer + '" />' +
				'<tr><td><td><span class="l">��������:</span><input type="text" id="pasp_adres" maxlength="100" value="' + DOG.pasp_adres + '" />' +
				'<tr><td><td><span class="l">��� �����:</span><input type="text" id="pasp_ovd" maxlength="100" value="' + DOG.pasp_ovd + '" />' +
				'<tr><td><td><span class="l">����� �����:</span><input type="text" id="pasp_data" maxlength="100" value="' + DOG.pasp_data + '" />' +
				'<tr><td class="label r">����� ��������:<td><input type="text" id="nomer" maxlength="6" value="' + DOG.nomer + '" />' +
				'<tr><td class="label r">���� ����������:<td><input type="hidden" id="data_create" value="' + (DOG.data_create ? DOG.data_create : '') + '" />' +
				'<tr><td class="label r">����� �� ��������:<td><input type="text" id="sum" class="money" maxlength="6" value="' + (DOG.sum ? DOG.sum : '') + '" /> ���.' +
				'<tr><td class="label r">��������� �����:<td><input type="text" id="avans" class="money" maxlength="6" value="' + (DOG.avans ? DOG.avans : '') + '" /> ���. <span class="prim">(�� �����������)</span>' +
	  (DOG.id ? '<tr><td class="label" colspan="2">������� �������������� ��������:<textarea id="reason"></textarea>' : '') +
				'<tr><td colspan="2">' +
					'<div class="i">' +
						'<h1>��������!</h1>' +
						'����������� ��������� ������������ ���� �������� ������. ' +
						'����� ������� ������ "��������� �������" �������� �������� ����� ����������.<br />' +
						'<b>����� �� ��������</b> �������� ������������� ������ � ��� ���������� �������� �� ��� ����� ����� ������ ������ ������� � �����.<br />' +
						'<b>��������� �����</b> ��������� �� �����������. ��� �������� ���������� ������ ������������� ����� ����� ����� �� ������ ������.' +
					'</div>' +
				'<tr><td colspan="2">' +
					'<a id="preview">��������������� ��������</a>' +
					'<form action="' + AJAX_MAIN + '" method="post" id="preview-form" target="_blank"></form>' +
				'</table>',
			dialog = _dialog({
				width:426,
				top:10,
				head:(DOG.id ? '�����' : '�') + '��������� ��������',
				content:html,
				butSubmit:(DOG.id ? '�����' : '�') + '�������� �������',
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
			if(!send.fio) err('�� ������� ��� �������', 'fio', type);
			else if(!send.adres) err('�� ������ �����', 'adres', type);
			else if(!REGEXP_NUMERIC.test(send.nomer) || send.nomer == 0) err('����������� ������ ����� ��������', 'nomer', type);
			else if(!REGEXP_NUMERIC.test(send.sum) || send.sum == 0) err('����������� ������� ����� �� ��������', 'sum', type);
			else if(send.avans && !REGEXP_NUMERIC.test(send.avans)) err('����������� ������ ��������� �����', 'avans', type);
			else if(DOG.id && !send.reason) err('�� ������� ������� �������������� ��������', 'reason', type);
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

	expenseSpisok = function() {
		var send = {
			op:'expense_spisok',
			category:$('#category').val(),
			worker:$('#worker').val(),
			year:$('#year').val(),
			month:$('#monthSum').val()
		};
		$('#mainLinks').addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			$('#mainLinks').removeClass('busy');
			if(res.success) {
				$('#spisok').html(res.html);
				$('#monthList').html(res.mon);
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
		title0:'������� ������� ������ �������...',
		spisok:[],
		ro:0,
		nofind:'�������� �� �������',
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
				if(ZAYAVRASHOD_TXT_ASS[cat_id])
					dop = u.find('.zrtxt').val();
				else if(ZAYAVRASHOD_WORKER_ASS[cat_id])
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
							(v[0] && ZAYAVRASHOD_TXT_ASS[v[0]] ? '<input type="text" class="zrtxt" placeholder="�������� �� �������" tabindex="' + (num * 10 - 1) + '" value="' + v[1] + '" />' : '') +
							(v[0] && ZAYAVRASHOD_WORKER_ASS[v[0]] ? '<input type="hidden" id="' + attr_worker + '" value="' + v[1] + '" />' : '') +
						'<td class="tdsum' + (v[0] ? '' : ' dn') + '"><input type="text" class="zrsum" maxlength="6" tabindex="' + (num * 10) + '" value="' + (v[2] || '') + '" />���.' +
					'</table>';
		zr.append(html);
		var ptab = $('#ptab' + num),
			tddop = ptab.find('.tddop'),
			zrsum = ptab.find('.zrsum');
		if(v[0] && ZAYAVRASHOD_WORKER_ASS[v[0]])
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
			spisok:ZAYAVRASHOD_SPISOK,
			func:function(id) {
				ptab.find('.tdsum')[(id > 0 ? 'remove' : 'add') + 'Class']('dn');
				if(ZAYAVRASHOD_TXT_ASS[id]) {
					tddop.html('<input type="text" class="zrtxt" placeholder="�������� �� �������" tabindex="' + (num * 10 - 11) + '" />');
					tddop.find('.zrtxt').focus();
				} else if(ZAYAVRASHOD_WORKER_ASS[id]) {
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
		send.op = 'zayav_next';
		send.page = next.attr('val');
		next.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#zayav .filter_clear', function() {
		$('.find-hide').removeClass('dn');
		window.zFind.clear();
		$('#product_id')._select(0);
		zayavSpisok();
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
		if(typeof CLIENT == 'undefined')
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
		if(typeof CLIENT == 'undefined')
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

	.on('click', '.invoice_set', function() {
		var t = $(this),
			html =
				'<table style="border-spacing:10px">' +
					'<tr><td class="label">�����:<td><INPUT type="text" class="money" id="sum" maxlength="7"> ���.' +
				'</table>',
			dialog = _dialog({
				width:300,
				top:60,
				head:'��������� ���������� ������� �����',
				content:html,
				butSubmit:'����������',
				submit:submit
			});
		$('#sum').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'invoice_set',
				invoice_id:t.attr('val'),
				sum:$('#sum').val()
			};
			if(!REGEXP_NUMERIC.test(send.sum)) {
				err('����������� ������� �����');
				$('#sum').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						t.parent().html('<b>' + send.sum + '</b> ���.');
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
				left:62
			});
		}
	})

	.on('click', '.income-add', function() {
		var html =
			'<table class="income-add-tab">' +
				'<tr><td class="label">������:<td>' + OPL.client_fio +
				'<tr><td class="label">������:' +
					'<td><input type="hidden" id="zayav_id" value="' + (OPL.zayav_id ? OPL.zayav_id : 0) + '">' +
						(OPL.zayav_id ? '<b>�' + OPL.zayav_id + '</b>' : '') +
				'<tr><td class="label">��� �������:<td><input type="hidden" id="income_id" value="0">' +
					'<a href="' + URL + '&p=setup&d=income" class="img_edit" title="������� � ��������� ����� ��������"></a>' +
				'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="7"> ���.' +
				'<tr><td class="label">����������:<em>(�� �����������)</em><td><input type="text" id="prim">' +
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
		$('#income_id')._select({
			width:180,
			title0:'�� ������',
			spisok:INCOME_SPISOK,
			func:function(uid) {
				$('#sum').focus();
			}
		});
		function submit() {
			var send = {
				op:'income_add',
				from:OPL.from,
				type:$('#income_id').val(),
				sum:$('#sum').val(),
				zayav_id:$('#zayav_id').val(),
				client_id:OPL.client_id,
				prim:$.trim($('#prim').val())
			};
			if(send.type == 0) err('�� ������ ��� �������');
			else if(!REGEXP_NUMERIC.test(send.sum)) {
				err('����������� ������� �����.');
				$('#sum').focus();
			} else if(send.zayav_id == 0 && !send.prim)
				err('���� �� ������� ������, ���������� ������� ����������');
			else {
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

	.on('click', '.expense ._next', function() {
		var next = $(this),
			send = {
				op:'expense_spisok',
				page:$(this).attr('val'),
				category:$('#category').val(),
				worker:$('#worker').val(),
				year:$('#year').val(),
				month:$('#monthSum').val()
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
				'<tr class="tr-work ' + (EXPENSE_WORKER_ASS[res.category] ? '' : 'dn') + '">' +
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
					$('.tr-work')[(EXPENSE_WORKER_ASS[id] ? 'remove' : 'add') + 'Class']('dn');
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

	.on('click', '#setup_my .pinset', function() {
		var t = $(this),
			html = '<table class="setup-tab">' +
				'<tr><td class="label">����� ���-���:<td><input id="pin" type="password" maxlength="10" />' +
				'</table>',
			dialog = _dialog({
				width:300,
				head:'��������� ������ ���-����',
				content:html,
				butSubmit:'����������',
				submit:submit
			});
		$('#pin').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'setup_my_pinset',
				pin: $.trim($('#pin').val())
			};
			if(!send.pin) {
				err('������� ���-���');
				$('#pin').focus();
			} else if(send.pin.length < 3) {
				err('����� ���-���� �� 3 �� 10 ��������');
				$('#pin').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('���-��� ����������.');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<span class="red">' + msg + '</span>',
				top:-47,
				left:52,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '#setup_my .pinchange', function() {
		var t = $(this),
			html = '<table class="setup-tab">' +
				'<tr><td class="label">������� ���-���:<td><input id="oldpin" type="password" maxlength="10" />' +
				'<tr><td class="label">����� ���-���:<td><input id="pin" type="password" maxlength="10" />' +
				'</table>',
			dialog = _dialog({
				width:300,
				head:'��������� ���-����',
				content:html,
				butSubmit:'��������',
				submit:submit
			});
		$('#oldpin').focus().keyEnter(submit);
		$('#pin').keyEnter(submit);
		function submit() {
			var send = {
				op:'setup_my_pinchange',
				oldpin: $.trim($('#oldpin').val()),
				pin: $.trim($('#pin').val())
			};
			if(!send.oldpin || !send.pin)
				err('��������� ��� ����');
			else if(send.oldpin.length < 3 || send.pin.length < 3)
				err('����� ���-���� �� 3 �� 10 ��������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('���-��� ������.');
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
				msg:'<span class="red">' + msg + '</span>',
				top:-47,
				left:52,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '#setup_my .pindel', function() {
		var t = $(this),
			html = '<table class="setup-tab">' +
				'<tr><td class="label">������� ���-���:<td><input id="oldpin" type="password" maxlength="10" />' +
				'</table>',
			dialog = _dialog({
				width:300,
				head:'�������� ���-����',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#oldpin').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'setup_my_pindel',
				oldpin:$.trim($('#oldpin').val())
			};
			if(!send.oldpin) {
				err('��������� ��� ����');
				$('#oldpin').focus();
			} else if(send.oldpin.length < 3) {
				err('����� ���-���� �� 3 �� 10 ��������');
				$('#oldpin').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('���-��� �����.');
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
				msg:'<span class="red">' + msg + '</span>',
				top:-47,
				left:52,
				indent:50,
				show:1,
				remove:1
			});
		}
	})

	.on('click', '#setup_worker .add', function() {
		var html = '<div id="setup_worker_add">' +
				'<h1>������� ����� �������� ������������ ��� ���<br />ID ���������:</h1>' +
				'<h2>������ ������ ����� ���� ��������� �����:<br />' +
					'<u>http://vk.com/id12345</u>, <u>http://vk.com/durov</u>.<br />' +
					'���� ����������� ID ������������: <u>id12345</u>, <u>durov</u>, <u>12345</u>.' +
				'</h2>' +
				'<input type="text" id="viewer_id" />' +
				'<div class="vkButton"><button>�����</button></div>' +
				'<a class="manual">��� ��������� ������ �������..</a>' +
				'<table class="manual_tab">' +
					'<tr><td class="label">���:<td><input type="text" id="first_name" />' +
					'<tr><td class="label">�������:<td><input type="text" id="last_name" />' +
					'<tr><td class="label">���:<td><input type="hidden" id="sex" value="0" />' +
					'<tr><td class="label">���������:<td><input type="text" id="post" />' +
				'</table>' +
			'</div>',
			dialog = _dialog({
				top:50,
				width:350,
				head:'���������� ������ ����������',
				content:html,
				butSubmit:'��������',
				submit:submit
			}),
			viewer_id = 0,
			but = $('#viewer_id').focus().keyEnter(user_find).next();
		but.click(user_find);
		$('.manual').click(function() {
			$(this)
				.hide()
				.next().show();
			$('.res').remove();
			viewer_id = 0;
			$('#viewer_id').val('');
			$('#first_name').focus();
		});
		$('#sex')._radio({
			spisok:[
				{uid:2, title:'�'},
				{uid:1, title:'�'}
			]
		});
		function user_find() {
			if(but.hasClass('busy'))
				return;
			viewer_id = 0;
			$('.res').remove();
			var send = {
				user_ids:$.trim($('#viewer_id').val()),
				fields:'photo_50',
				v:5.2
			};
			if(!send.user_ids)
				return;
			if(/vk.com/.test(send.user_ids))
				send.user_ids = send.user_ids.split('vk.com/')[1];
			if(/\?/.test(send.user_ids))
				send.user_ids = send.user_ids.split('?')[0];
			if(/#/.test(send.user_ids))
				send.user_ids = send.user_ids.split('#')[0];
			but.addClass('busy');
			VK.api('users.get', send, function(data) {
				but.removeClass('busy');
				if(data.response) {
					var u = data.response[0],
						html =
						'<table class="res">' +
							'<tr><td class="photo"><img src=' + u.photo_50 + '>' +
								'<td class="name">' + u.first_name + ' ' + u.last_name +
						'</table>';
					but.after(html);
					viewer_id = u.id;
				}
			});
		}
		function submit() {
			var send = {
				op:'setup_worker_add',
				viewer_id:viewer_id,
				first_name:$('#first_name').val(),
				last_name:$('#last_name').val(),
				sex:$('#sex').val(),
				post:$('#post').val()
			};
			if(!send.viewer_id && !send.first_name && !send.last_name) err('����������� ����� ������������<br>��� ������� ������� ��� � �������', -60);
			else if(send.first_name && send.last_name && send.sex == 0) err('�� ������ ���', -47);
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����� ��������� ������� ��������.');
						$('#spisok').html(res.html);
					} else {
						dialog.abort();
						err(res.text, -60);
					}
				}, 'json');
			}
		}
		function err(msg, top) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:top,
				left:90
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
			head:'�������� ����������',
			content:'<center>����������� �������� ����������.</center>',
			butSubmit:'�������',
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
					_msg('��������� ������.');
					$('#spisok').html(res.html);
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_product .add', function() {
		var t = $(this),
			html = '<table style="border-spacing:10px">' +
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" style="width:250px" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'���������� ������ ������������ �������',
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
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('�������!');
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
				'<tr><td class="label">������������:<td><input id="name" type="text" maxlength="100" style="width:250px" value="' + name.html() + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'�������������� ������������ �������',
				content:html,
				butSubmit:'���������',
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
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('���������!');
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
				head:'�������� �������',
				content:'<center><b>����������� �������� �������.</b></center>',
				butSubmit:'�������',
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
					_msg('�������!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_product_sub .add', function() {
		var t = $(this),
			html = '<table style="border-spacing:10px">' +
				'<tr><td class="label">������������:<td><input id="name" type="text" maxlength="100" style="width:250px" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'���������� ������ ������� �������',
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
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('�������!');
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
				'<tr><td class="label">������������:<td><input id="name" type="text" maxlength="100" style="width:250px" value="' + name.html() + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'�������������� ������� �������',
				content:html,
				butSubmit:'���������',
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
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('���������!');
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
				head:'�������� �������',
				content:'<center><b>����������� �������� ������� �������.</b></center>',
				butSubmit:'�������',
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
					_msg('�������!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_invoice .add', function() {
		var t = $(this),
			html = '<table class="setup-tab">' +
				'<tr><td class="label">������������:<td><input id="name" type="text" maxlength="50" />' +
				'<tr><td class="label topi">��������:<td><textarea id="about"></textarea>' +
				'<tr><td class="label topi">���� ��������:<td><input type="hidden" id="types" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:400,
				head:'���������� ������ �����',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#types')._select({
			width:218,
			multiselect:1,
			spisok:INCOME_SPISOK
		});
		function submit() {
			var send = {
				op:'setup_invoice_add',
				name:$('#name').val(),
				about:$('#about').val(),
				types:$('#types').val()
			};
			if(!send.name) {
				err('�� ������� ������������');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('�������!');
					} else {
						dialog.abort();
						err(res.text);
					}
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:100,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '#setup_invoice .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name div').html(),
			about = t.find('.name pre').html(),
			types = t.find('.type_id').val(),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'<tr><td class="label r top">��������:<td><textarea id="about">' + about + '</textarea>' +
				'<tr><td class="label topi">���� ��������:<td><input type="hidden" id="types" value="' + types + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:400,
				head:'�������������� ������ �����',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#types')._select({
			width:218,
			multiselect:1,
			spisok:INCOME_SPISOK
		});
		function submit() {
			var send = {
				op:'setup_invoice_edit',
				id:id,
				name:$('#name').val(),
				about:$('#about').val(),
				types:$('#types').val()
			};
			if(!send.name) {
				err('�� ������� ������������');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('���������!');
					} else {
						dialog.abort();
						err(res.text);
					}
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:100,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '#setup_invoice .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'�������� �����',
				content:'<center><b>����������� �������� �����.</b></center>',
				butSubmit:'�������',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'TR')
				t = t.parent();
			var send = {
				op:'setup_invoice_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('�������!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_income .add', function() {
		var t = $(this),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:440,
				head:'���������� ������ ���� �������',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'setup_income_add',
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('�������!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_income .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'DD')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name').html(),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:440,
				head:'�������������� ���� �������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'setup_income_edit',
				id:id,
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('���������!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_income .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'�������� ���� �������',
				content:'<center><b>����������� �������� ���� �������.</b></center>',
				butSubmit:'�������',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'setup_income_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('�������!');
					sortable();
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_expense .add', function() {
		var t = $(this),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="50" />' +
				'<tr><td class="label r">������ �����������:<td><input id="show_worker" type="hidden" />' +
				'</table>',
			dialog = _dialog({
				width:400,
				head:'���������� ����� ��������� ������� �����������',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#show_worker')._check();
		function submit() {
			var send = {
				op:'setup_expense_add',
				name:$('#name').val(),
				show_worker:$('#show_worker').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('�������!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_expense .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'DD')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name').html(),
			worker = t.find('.worker').html() ? 1 : 0,
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="50" value="' + name + '" />' +
				'<tr><td class="label r">������ �����������:<td><input id="show_worker" type="hidden" value="' + worker + '" />' +
				'</table>',
			dialog = _dialog({
				width:400,
				head:'�������������� ��������� ������� �����������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#show_worker')._check();
		function submit() {
			var send = {
				op:'setup_expense_edit',
				id:id,
				name:$('#name').val(),
				show_worker:$('#show_worker').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<span class="red">�� ������� ������������</span>',
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
						_msg('���������!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_expense .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				head:'�������� ��������� ������� �����������',
				content:'<center><b>����������� ��������.</b></center>',
				butSubmit:'�������',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'setup_expense_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('�������!');
					sortable();
				} else
					dialog.abort();
			}, 'json');
		}
	})


	.on('click', '#setup_zayavrashod .add', function() {
		var t = $(this),
			html = '<table style="border-spacing:10px">' +
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="50" style="width:210px" />' +
				'<tr><td class="label r">��������� ����:<td><input id="show_txt" type="hidden" />' +
				'<tr><td class="label r">������ �����������:<td><input id="show_worker" type="hidden" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:440,
				head:'���������� ����� ��������� ������� ������',
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
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('�������!');
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
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="50" style="width:210px" value="' + name + '" />' +
				'<tr><td class="label r">��������� ����:<td><input id="show_txt" type="hidden" value="' + txt + '" />' +
				'<tr><td class="label r">������ �����������:<td><input id="show_worker" type="hidden" value="' + worker + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:440,
				head:'�������������� ��������� ������� ������',
				content:html,
				butSubmit:'���������',
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
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('���������!');
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
				head:'�������� ��������� ������� ������',
				content:'<center><b>����������� ��������<br />��������� ������� ������.</b></center>',
				butSubmit:'�������',
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
					_msg('�������!');
					sortable();
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
				func:clientSpisokLoad
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
			$('#dolg')._check(clientSpisokLoad);
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
				$('#remind_spisok').css('display', val == 'remind' ? 'block' : 'none');
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
			$('#status').rightLink(clientZayavSpisokLoad);
		}

		if($('#zayav').length) {
			window.zFind = $('#find')._search({
				width:153,
				focus:1,
				txt:'������� �����...',
				enter:1,
				func:zayavFindFast
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
			$('#dogovor_action').linkMenu({
				head:'�� ��������',
				spisok:[
					{uid:1, title:'��������� �������'},
					{uid:2, title:'��������� ������ � ��������� "��������� �������"'}
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
						head:'����� �' + ZAYAV.id + ' - ��������������',
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
						head:'����� �' + ZAYAV.id + ' - ��������������',
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
						head:'��������� �' + ZAYAV.id + ' - ��������������',
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
					if(!REGEXP_NUMERIC.test(send.sum)) {
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
						op:'zayav_rashod_edit',
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
			window._calendarFilter = function(day) {
				var y = $('#remind').hasClass('y'),
					cal = $('#remind .right ._calendarFilter'),
					send = {
						op:'remind_day',
						day:day
					};
				if(y)
					$('#remind').removeClass('y');
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('#remind .left').html(res.html);
						if(y)
							cal.html(res.cal);
					}
				}, 'json');
			};
		}

		if($('#income').length) {
			window._calendarFilter = function(day) {
				send = {
					op:'income_get',
					day:day
				};
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						$('#income .inc-path').html(res.path);
						$('#income #spisok').html(res.html);
					}
				}, 'json');
			};
		}
		if($('#report.expense').length) {
			$('.add').click(function() {
				var html =
						'<table id="expense-add-tab">' +
							'<tr><td class="label">���������:<TD><INPUT type="hidden" id="cat">' +
							'<tr class="tr-work dn"><td class="label">���������:<TD><INPUT type="hidden" id="work">' +
							'<tr><td class="label">��������:<TD><INPUT type="text" id="about" maxlength="100">' +
							'<tr><td class="label">�� �����:<TD><INPUT type="hidden" id="invoice">' +
							'<tr><td class="label">�����:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> ���.' +
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
						$('.tr-work')[(EXPENSE_WORKER_ASS[id] ? 'remove' : 'add') + 'Class']('dn');
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

				function submit() {
					var send = {
						op:'expense_add',
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
			$('#year').years({func:expenseSpisok});
			$('#monthSum')._radio(expenseSpisok)
		}

		if($('#setup_rules').length) {
			$('.gtab-save').click(function() {
				var send = {
					op:'setup_worker_save',
					viewer_id:RULES_VIEWER_ID,
					first_name:$('#first_name').val(),
					last_name:$('#last_name').val(),
					post:$('#post').val()
				},
					but = $(this);
				if(!send.first_name) { err('�� ������� ���'); $('#first_name').focus(); }
				else if(!send.last_name) { err('�� ������� �������'); $('#last_name').focus(); }
				else {
					but.addClass('busy');
					$.post(AJAX_MAIN, send, function(res) {
						but.removeClass('busy');
						if(res.success)
							_msg('���������.');
					}, 'json');
				}
				function err(msg) {
					but.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						top:-57,
						left:-6,
						indent:40,
						show:1,
						remove:1
					});
				}
			});
			$('.pin-clear').click(function() {
				var send = {
						op:'setup_worker_pinclear',
						viewer_id:RULES_VIEWER_ID
					},
					but = $(this);
				but.addClass('busy');
				$.post(AJAX_MAIN, send, function(res) {
					but.removeClass('busy');
					if(res.success)
						_msg('���-��� �������.');
				}, 'json');
			});
			$('#rules_appenter')._check(function(v, id) {
				$('.app-div')[(v == 0 ? 'add' : 'remove') + 'Class']('dn');
				setupRulesSet(v, id);
				$('#rules_worker')._check(0);
				$('#rules_product')._check(0);
				$('#rules_income')._check(0);
				$('#rules_zayavrashod')._check(0);
				$('#rules_historyshow')._check(0);
			});
			$('#rules_worker')._check(setupRulesSet);
			$('#rules_rekvisit')._check(setupRulesSet);
			$('#rules_product')._check(setupRulesSet);
			$('#rules_income')._check(setupRulesSet);
			$('#rules_zayavrashod')._check(setupRulesSet);
			$('#rules_historyshow')._check(setupRulesSet);
		}
		if($('#setup_rekvisit').length) {
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
						_msg('���������� ���������.');
				}, 'json');
			});
		}
	});