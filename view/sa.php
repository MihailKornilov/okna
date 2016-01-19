<?php
function sa_index() {
	return '<div class="path">'.sa_cookie_back().'Администрирование</div>'.
	'<div class="sa-index">'.
		'<div class="headName">Счётчики</div>'.
		'<div class="vkButton client_balans"><button>Обновить балансы клиентов</button></div>'.
		'<br />'.
		'<br />'.
		'<div class="vkButton zayav_balans"><button>Обновить суммы начислений и платежей заявок</button></div>'.
		'<br />'.
		'<br />'.
	'</div>';
}//sa_index()
