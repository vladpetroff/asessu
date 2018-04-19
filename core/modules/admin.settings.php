<?php

$id = $_POST['id'];
unset($_POST['id']);

function genMenu($object){
	$menu[] = array("name"=>"Изменить","action"=>"open","inactive"=>false);
if ($_SESSION['group_id']  == 1) {
	$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>false);					
}

	return $menu;
}

function genHTML($object){
	$html = '<div class="page"></div>';
	$html.= '<h1>'.$object->description.'</h1>';
	$html.='<p><span>'.$object->name.':</span> '.$object->value.'</p>';
	$html.='<input type="hidden" name="id" value="'.$object->id.'" />';
	return $html;
}

function out($obj){
	die(json_encode($obj));
}

switch ($params[1]) {
	case 'getall':
		unset($menu,$html,$objects);
		$result = $db->select('config','*');
		if ($result != 0)
		foreach ($result as $row) {
			$html[] = genHTML($row);
			$menu[] = genMenu($row);
			$objects[] = $row;
		}
		else
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
		break;
	case "get_text":
	$result = $db->select('config','description, name, value html','id='.$id);
	out($result[0]);
	break;
	case "remove":
		if (cgr(1))	$db->cer_delete('config','id='.$id);
		die('"ok"');
	break;
	case "update":
		$db->update('config',$_POST,'id='.$id);
		$result = $db->select('config','*','id='.$id);
		if ($result != 0){
			$html = genHTML($result[0]);
			$menu = genMenu($result[0]);
			$objects = $result[0];			
		}
		else
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
	break;
	case "add-option":
	if (cgr(1)) {
		$name = 'Опция';
		$c = $db->select('config','COUNT(*) c', 'description LIKE "Опция%"');
		if ($c[0]->c > 0) {
			$name = $name.' '.($c[0]->c+1);
		}
		$link = 'option';
		$c = $db->select('config','COUNT(*) c', 'name LIKE "option%"');
		if ($c[0]->c > 0) {
			$link = $link.'_'.($c[0]->c+1);
		}
		$insertArray['id'] = '';
		$insertArray['name'] = $link;
		$insertArray['description'] = $name;
		$insertArray['value'] = '';
		$insertArray['user_id'] = '';
		$insertArray['group_id'] = '';
		$insertArray['create_date'] = '';
		$insertArray['modified_by'] = '';
		$insertArray['modify_date'] = '';
		$insertArray['del'] = 'n';
		$db->insert('config',$insertArray);	
	}		

	unset($menu,$html,$objects);
	$result = $db->select('config','*','name="'.$link.'"');
	if ($result != 0){
		$html = genHTML($result[0]);
		$menu = genMenu($result[0]);
		$objects = $result[0];			
	}
	else
		out(array('html'=>0,'menu'=>0,'object'=>0));
	out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
	break;
}

?>