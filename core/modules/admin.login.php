<?php
##[k] запрет запуска модуля вне контекста системы
if (!isset($db))
    {
        header("HTTP/1.0 404 Not Found");
        header("Location: /404.html");
	}

function omsg($msg = '') {
    wr('ERROR_MSG',(strlen($msg) > 0) ? '<p style="color:red; padding:10px 0;">'.$msg.'</p>': ''); 
}

##[k] если получена команда разлогинивания
if (isset($params[1]) && $params[1]=='logout')
{
    session_destroy();
    session_start();
    $_SESSION['user_id'] = 2;
    $_SESSION['group_id'] = 5;
	$params[1] = 'init';
	header ("Location: /");
}
if(!isset($params[1])) $params[1] = 'init';
//[k] первый вход 
if ($params[1] == 'init') {
	$user_agent = $_SERVER['HTTP_USER_AGENT']; 
    //[k] проверка браузера. Если ие6 - отключаемся и показываем страницу обновления.
	if (stripos($user_agent, 'MSIE 6.0') !== false && stripos($user_agent, 'MSIE 8.0') === false && stripos($user_agent, 'MSIE 7.0') === false) {
		header ("Location: /ie6.html");
	}
    omsg();
}
if ($params[1] == 'attempt') {
##[k] проверка полученных значений если есть
    if (strlen(trim($_POST['login'])) > 0) {
        $result = $db->select('users','login,pass,id,group_id','login = "'.clear($_POST['login']).'"');
        if ($result[0]->pass === crypt(clear($_POST['pass']),$result[0]->pass)) {
            $_SESSION['user_id'] = $result[0]->id;
            $_SESSION['group_id']= $result[0]->group_id;
            header('Location: /dashboard.html#tree');
        }
        else {
            omsg('Вы ошиблись при вводе логина или пароля.');
        }
    }  
    else{
        omsg('Необходимо заполнить поле логина.');
    }
}
?>