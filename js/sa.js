var AJAX_SA = APP_HTML + '/ajax/sa.php?' + VALUES;

$(document)
	.on('click', '.client_balans', function() {
		var t = $(this),
			send = {
				op:'client_balans'
			};
		t.addClass('busy');
		$.post(AJAX_SA, send, function(res) {
			t.removeClass('busy');
			if(res.success) {
				t.next().remove('span');
				t.after('<span> Изменено: ' + res.count + '. Время: ' + res.time + '</span>');
			}
		}, 'json');
	})
	.on('click', '.zayav_balans', function() {
		var t = $(this),
			send = {
				op:'zayav_balans'
			};
		t.addClass('busy');
		$.post(AJAX_SA, send, function(res) {
			t.removeClass('busy');
			if(res.success) {
				t.next().remove('span');
				t.after('<span> Изменено: ' + res.count + '. Время: ' + res.time + '</span>');
			}
		}, 'json');
	})

	.ready(function() {
	});
