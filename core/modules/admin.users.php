<?php

if (!isset($_SESSION['group_id'])) die('запуск модуля вне контекста системы запрещен');

$id = $_POST['id'];
unset($_POST['id']);

$groups = $db->select('groups','id,name','id >='.$_SESSION['group_id']);
$grarr[0] = 'Не установлена';
foreach ($groups as $el) {
	$grarr[$el->id] = $el->name;
}

function genMenu($object){
	if ($object->id == 2) {
		$menu[] = array("name"=>"Изменить имя","action"=>"editname","inactive"=>false);
	}
	else {
		$menu[] = array("name"=>"Изменить имя","action"=>"editname","inactive"=>false);
		$menu[] = array("name"=>"Изменить пароль","action"=>"editpass","inactive"=>false);
		$menu[] = "separator";
		$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>($object->id == 2 || $object->id == $_SESSION['user_id'])?true:false);		
	}
	return $menu;
}

function genHTML($object){
	global $grarr;
	if ($object->id == 2) {
		$html = '<div class="user"></div>';
		$html.= '<h1>'.$object->nickname.'</h1>';
		$html.= '<p><span>Анонимный пользователь, только просмотр страниц сайта</span></p>';
		$html.= '<input type="hidden" name="id" value="'.$object->id.'" />';		
	}
	else {
		$html = '<div class="user"></div>';
		$html.= '<h1>'.$object->nickname.'</h1>';
		$html.='<p><span>Логин: </span><a class="ulogin" href="#ulogin">'.$object->login.'</a><span> Группа: </span><a class="ugroup" href="#ugroup">'.$grarr[$object->group_id].'</a></p>';
		$html.='<input type="hidden" name="id" value="'.$object->id.'" />';				
	}

	return $html;
}

function out($obj){
	die(json_encode($obj));
}

switch ($params[1]) {
	case 'getall':
		unset($menu,$html,$objects);
		$result = $db->select('users','id,group_id,login,nickname','group_id >='.$_SESSION['group_id'],'nickname');		
		if ($result != 0)
		foreach ($result as $row) {
			$row->type = 'page';			
			if (trim($row->login) == '') $row->login = 'не введен';
			$html[] = genHTML($row);
			$menu[] = genMenu($row);
			$objects[] = $row;
		}
		else
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
		break;


	case "update":
		$db->update('users',$_POST,'id='.$id);
		$res = $db->select('users','id,group_id,login,nickname','id='.$id);
		$res = $res[0];
		$res->type = 'page';
		if (trim($row->login) == '') $row->login = 'не введен';		
		$html = genHTML($res);
		$menu = genMenu($res);
		$object = $res;
		out(array('html'=>$html,'menu'=>$menu,'object'=>$object));
	break;

	case "updatepass":
		$seluser = $db->select('users','group_id','id ='.clear($id));
		if ($seluser[0]->group_id < $_SESSION['group_id']) die ('запрещено');

		$db->update('users',array('pass'=>crypt(clear($_POST['pass']))),'id='.$id);
		$res = $db->select('users','id,group_id,login,nickname','id='.$id);
		$res = $res[0];
		$res->type = 'page';
		if (trim($row->login) == '') $row->login = 'не введен';		
		$html = genHTML($res);
		$menu = genMenu($res);
		$object = $res;
		out(array('html'=>$html,'menu'=>$menu,'object'=>$object));
	break;


	case "create_item":
		$nickname = 'Новый пользователь';
		$c = $db->select('users','COUNT(*) c', 'nickname LIKE "Новый пользователь%"');
		if ($c[0]->c > 0) {
			$nickname = $nickname.($c[0]->c+1);
		}
		$login = 'user';
		$c = $db->select('users','COUNT(*) c', 'login LIKE "user%"');
		if ($c[0]->c > 0) {
			$login = $login.($c[0]->c+1);
		}
		$insertArray['id'] = '';
		$insertArray['login'] = $login;
		$insertArray['nickname'] = $nickname;
		$insertArray['group_id'] = 5;
		$insertArray['user_id'] = $_SESSION['user_id'];
		$db->insert('users',$insertArray);	
		unset($menu,$html,$objects);
		$result = $db->select('users','id,group_id,login,nickname','login = "'.$login.'"');
		foreach ($result as $row) {
			$row->type = 'page';
			$html[] = genHTML($row);
			$menu[] = genMenu($row);
			$objects[] = $row;
		}
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));	
	break;

	case "get_groups":
		out($grarr);
	break;

	case "remove":
		if ($id == 2 || $id == 1) die('error');
		$db->cer_delete('users','id='.$id);
		die('"ok"');
	break;
}
?>