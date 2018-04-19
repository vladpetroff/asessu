<?php
$id = $_POST['id'];
unset($_POST['id']);

function genMenu($object){
	global $db;
	$menu[] = array("name"=>"Путь к файлу","action"=>"edit","inactive"=>false);
	$menu[] = "separator";
	$menu[] = array("name"=>"Переименовать","action"=>"rename","inactive"=>false);	
	$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>false);					
	return $menu;
}

function genMenum($object){
	global $db;
	$menu[] = array("name"=>"Путь к файлу","action"=>"edit","inactive"=>false);
	$menu[] = "separator";
	if(!$object->link)
		$menu[] = array("name"=>"Привязать","action"=>"link","inactive"=>false);
	else
		$menu[] = array("name"=>"Отвязать","action"=>"unlink","inactive"=>false);	
	$menu[] = "separator";
	$menu[] = array("name"=>"Переименовать","action"=>"rename","inactive"=>false);	
	$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>false);					
	return $menu;
}

function genHTML($object){
	$html = '<div class="page"></div>';
	$html.= '<h1>'.$object->description.'</h1>';
	$html.= '<p>'.$object->name.'</p>';
	if ($object->link)
		$html.='<div class="fav"></div>';
	$html.='<input type="hidden" name="id" value="'.$object->id.'" />';
	return $html;
}

function out($obj){
	die(json_encode($obj));
}

switch ($params[1]) {
	case 'getall':
		unset($menu,$html,$objects);
		$result = $db->select('modules','*',null,'description');
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
	case 'getallm':
		unset($menu,$html,$objects);
		$linked = $db->select('links','cid','parent="templates" and child="modules" AND pid='.$id,'id');
		if ($linked != 0)
		foreach($linked as $row){
			$result = $db->select('modules','*','id='.$row->cid);
			$result[0]->link = true;
			$array[] = $result[0];
		}
		$result = $db->select('modules','*');
		if ($result != 0)
		foreach ($result as $row) {
			$dupl = true;
			if ($linked != 0)
			foreach($linked as $row2){
				if ($row->id == $row2->cid) {
					$dupl = false;
				}
			}
			if($dupl)
				$array[] = $row;
		}		
		if ($result != 0)
		foreach ($array as $row) {
			$html[] = genHTML($row);
			$menu[] = genMenum($row);
			$objects[] = $row;
		}
		else
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
	break;
	case "get":
		$result = $db->select('modules','name','id='.$id);
		out($result[0]);
	break;
	case "update":
		$db->update('modules',$_POST,'id='.$id);
		unset($menu,$html,$objects);
		$result = $db->select('modules','*','id='.$id,'description');
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
	break;
	case "create":
	$name = 'Новый модуль';
	$c = $db->select('modules','COUNT(*) c', 'description LIKE "Новый модуль%"');
	if ($c[0]->c > 0) {
		$name = $name.' '.($c[0]->c+1);
	}
	$insertArray['id'] = '';
	$insertArray['name'] = '';
	$insertArray['description'] = $name;
	$insertArray['text'] = '';
	$insertArray['user_id'] = '';
	$insertArray['group_id'] = '';
	$insertArray['create_date'] = '';
	$insertArray['modified_by'] = '';
	$insertArray['modify_date'] = '';
	$insertArray['del'] = 'n';
	$db->insert('modules',$insertArray);	
	unset($menu,$html,$objects);
	$result = $db->select('modules','*','description="'.$name.'"','description');
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
	case "remove":
		$db->cer_delete('modules','id='.$id);
		die('"ok"');
	break;
}
?>