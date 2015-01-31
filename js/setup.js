$(document)
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
				'<tr><td class="label topi">���������<br />��� �����������:<td><input type="hidden" id="visible" />' +
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
		$('#visible')._select({
			width:218,
			multiselect:1,
			spisok:WORKER_SPISOK
		});
		function submit() {
			var send = {
				op:'setup_invoice_add',
				name:$('#name').val(),
				about:$('#about').val(),
				types:$('#types').val(),
				visible:$('#visible').val()
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
			visible = t.find('.visible_id').val(),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'<tr><td class="label r top">��������:<td><textarea id="about">' + about + '</textarea>' +
				'<tr><td class="label topi">���� ��������:<td><input type="hidden" id="types" value="' + types + '" />' +
				'<tr><td class="label topi">���������<br />��� �����������:<td><input type="hidden" id="visible" value="' + visible + '" />' +
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
		$('#visible')._select({
			width:218,
			multiselect:1,
			spisok:WORKER_SPISOK
		});

		function submit() {
			var send = {
				op:'setup_invoice_edit',
				id:id,
				name:$('#name').val(),
				about:$('#about').val(),
				types:$('#types').val(),
				visible:$('#visible').val()
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
				'<tr><td class="label r">�������������<br />�����������<br />�� ����:<td><input id="confirm" type="hidden" />' +
				'</table>',
			dialog = _dialog({
				head:'���������� ������ ���� �������',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#confirm')._check();
		function submit() {
			var send = {
				op:'setup_income_add',
				name:$('#name').val(),
				confirm:$('#confirm').val()
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
			confirm = t.find('.confirm').html() ? 1 : 0,
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'<tr><td class="label r">�������������<br />�����������<br />�� ����:<td><input id="confirm" type="hidden" value="' + confirm + '" />' +
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
		$('#confirm')._check();
		function submit() {
			var send = {
				op:'setup_income_edit',
				id:id,
				name:$('#name').val(),
				confirm:$('#confirm').val()
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

	.on('click', '#setup_zayavexpense .add', function() {
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
				op:'setup_zayav_expense_add',
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
	.on('click', '#setup_zayavexpense .img_edit', function() {
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
				op:'setup_zayav_expense_edit',
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
	.on('click', '#setup_zayavexpense .img_del', function() {
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
				op:'setup_zayav_expense_del',
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
		if($('#setup_rules').length) {
			$('.g-save').click(function() {
				var send = {
					op:'setup_worker_save',
					viewer_id:RULES_VIEWER_ID,
					first_name:$('#first_name').val(),
					last_name:$('#last_name').val(),
					middle_name:$('#middle_name').val(),
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
				if(but.hasClass('busy'))
					return;
				but.addClass('busy');
				$.post(AJAX_MAIN, send, function(res) {
					but.removeClass('busy');
					if(res.success)
						_msg('���-��� �������.');
				}, 'json');
			});
			$('#rules_cash')._check(function(v) {
				$('.tr_selmoney')[(v ? 'remove' : 'add') + 'Class']('dn');
				$('#rules_selmoney')._check(0);
			});
			$('#rules_appenter')._check(function(v) {
				$('.app-div')[(v == 0 ? 'add' : 'remove') + 'Class']('dn');
				$('#rules_worker')._check(0);
				$('#rules_rules')._check(0);
				$('#rules_rekvisit')._check(0);
				$('#rules_product')._check(0);
				$('#rules_income')._check(0);
				$('#rules_zayavrashod')._check(0);
				$('#rules_historyshow')._check(0);
				$('#rules_money')._dropdown(0);
			});
			$('#rules_money')._dropdown({
				spisok:[
					{uid:0,title:'������ ����'},
					{uid:1,title:'��� �������'}
				]
			});
			$('.rules-save').click(function() {
					var send = {
						op:'setup_worker_rules_save',
						viewer_id:RULES_VIEWER_ID,
						rules_appenter:$('#rules_appenter').val(),
						rules_worker:$('#rules_worker').val(),
						rules_rules:$('#rules_rules').val(),
						rules_rekvisit:$('#rules_rekvisit').val(),
						rules_product:$('#rules_product').val(),
						rules_income:$('#rules_income').val(),
						rules_zayavrashod:$('#rules_zayavrashod').val(),
						rules_historyshow:$('#rules_historyshow').val(),
						rules_money:$('#rules_money').val()
					},
					but = $(this);
				if(but.hasClass('busy'))
					return;
				but.addClass('busy');
				$.post(AJAX_MAIN, send, function(res) {
					but.removeClass('busy');
					if(res.success)
						_msg('����� ���������.');
				}, 'json');
			});
			$('.dop-save').click(function() {
					var send = {
							op:'setup_worker_dop_save',
							viewer_id:RULES_VIEWER_ID,
							rules_cash:$('#rules_cash').val(),
							rules_selmoney:$('#rules_selmoney').val(),
							rules_getmoney:$('#rules_getmoney').val(),
							rules_nosalary:$('#rules_nosalary').val(),
							rules_zpzayavauto:$('#rules_zpzayavauto').val()
						},
						but = $(this);
				if(but.hasClass('busy'))
					return;
				but.addClass('busy');
				$.post(AJAX_MAIN, send, function(res) {
					but.removeClass('busy');
					if(res.success)
						_msg('�������������� ��������� ���������.');
				}, 'json');
			});
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
