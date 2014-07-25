<?php
function sa_cookie_back() {
	if(!empty($_GET['pre_p'])) {
		$_COOKIE['pre_p'] = $_GET['pre_p'];
		$_COOKIE['pre_d'] = empty($_GET['pre_d']) ? '' : $_GET['pre_d'];
		$_COOKIE['pre_d1'] = empty($_GET['pre_d1']) ? '' : $_GET['pre_d1'];
		$_COOKIE['pre_id'] = empty($_GET['pre_id']) ? '' : $_GET['pre_id'];
		setcookie('pre_p', $_COOKIE['pre_p'], time() + 2592000, '/');
		setcookie('pre_d', $_COOKIE['pre_d'], time() + 2592000, '/');
		setcookie('pre_d1', $_COOKIE['pre_d1'], time() + 2592000, '/');
		setcookie('pre_id', $_COOKIE['pre_id'], time() + 2592000, '/');
	}
	$d = empty($_COOKIE['pre_d']) ? '' :'&d='.$_COOKIE['pre_d'];
	$d1 = empty($_COOKIE['pre_d1']) ? '' :'&d1='.$_COOKIE['pre_d1'];
	$id = empty($_COOKIE['pre_id']) ? '' :'&id='.$_COOKIE['pre_id'];
	return '<a href="'.URL.'&p='.$_COOKIE['pre_p'].$d.$d1.$id.'">Назад</a> » ';
}//sa_cookie_back()

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
