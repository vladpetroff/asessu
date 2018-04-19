<?php
include($_SERVER['DOCUMENT_ROOT'].'/core/inc/image.class.php');
$id = $_POST['id'];
unset($_POST['id']);

function genMenu($object){
	$menu[] = array("name"=>"Редактировать форму","action"=>"edit_text","inactive"=>false);
	$menu[] = array("name"=>"Переименовать","action"=>"rename","inactive"=>false);	
	$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>($object->count == 0)?false:true);					
	return $menu;
}

function genHTML($object){
	$html = '<div class="page"></div>';
	$html.= '<h1>'.$object->name.'</h1>';
	$html.='<input type="hidden" name="id" value="'.$object->id.'" />';
	return $html;
}

function out($obj){
	die(json_encode($obj));
}

switch ($params[1]) {
	case 'getall':
		unset($menu,$html,$objects);
		$result = $db->select('forms','*','parent_id='.$id);
		if ($result != 0)
		foreach ($result as $row) {
			if ($row->type != 'block') {
				$count = $db->select('forms','COUNT(*) c','parent_id='.$row->id);				
				$row->count = $count[0]->c;
			} else {
				if ($row->parent_id == 0) {
					$row->parent_type = 'folder';
				} else {
					$pres = $db->select('forms','type','id='.$row->parent_id);
					$row->parent_type = $pres[0]->type;
				}
				
			}
			$html[] = genHTML($row);
			$menu[] = genMenu($row);
			$objects[] = $row;
		}
		else
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
		break;
	case "getnames":
		unset($out);
		for ($i=0; $i < sizeof($id); $i++) { 
			$result = $db->select('forms','name','id = '.$id[$i]);
			$out[] = $result[0];
		}
		out($out);		
	break;
	case "get_text":
		$list_fields = "";
		$result = $db->select('forms','content','id='.$id);
		$result[0]->content = unserialize($result[0]->content);
		$data =explode("&", $result[0]->content);
		for ($i = 0; $i < count($data); $i += 4) {
			$key = explode("=", $data[$i]);	
			$list_fields['key'][] = urldecode($key[1]);
			$value = explode("=", $data[$i+1]);	
			$list_fields['value'][] = urldecode($value[1]);
			$type = explode("=", $data[$i+2]);	
			$list_fields['type'][] = $type[1];
			$class = explode("=", $data[$i+3]);	
			$list_fields['class'][] = $class[1];
		}
		out($list_fields);
	break;
	case "update":
		$_POST['content'] = serialize($_POST['content']);
		$db->update('forms',$_POST,'id='.$id);
		unset($menu,$html,$objects);
		$result = $db->select('forms','*','id='.$id);
		foreach ($result as $row) {
			foreach ($result as $row) {
				if ($row->type != 'block') {
					$count = $db->select('forms','COUNT(*) c','parent_id='.$row->id);				
					$row->count = $count[0]->c;
				} else {
					if ($row->parent_id == 0) {
						$row->parent_type = 'folder';
					} else {
						$pres = $db->select('forms','type','id='.$row->parent_id);
						$row->parent_type = $pres[0]->type;
					}

				}
				$html[] = genHTML($row);
				$menu[] = genMenu($row);
				$objects[] = $row;
			}
		}
		out(array('html'=>$html,'menu'=>$menu,'object'=>$object));
	break;
	case "create":
	switch($_POST['type']){
		case "folder":
			$name = 'Новый каталог';
			$c = $db->select('forms','COUNT(*) c', 'name LIKE "Новый каталог%"');
			if ($c[0]->c > 0) {
				$name = $name.' '.($c[0]->c+1);
			}
		break;
		case "block":
			$name = 'Новый блок';
			$c = $db->select('forms','COUNT(*) c', 'name LIKE "Новый блок%"');
			if ($c[0]->c > 0) {
				$name = $name.' '.($c[0]->c+1);
			}
		break;
		case "group":
			$name = 'Новая группа';
			$c = $db->select('forms','COUNT(*) c', 'name LIKE "Новая группа%"');
			if ($c[0]->c > 0) {
				$name = $name.' '.($c[0]->c+1);
			}
		break;
		case "rotate":
			$name = 'Группа ротации';
			$c = $db->select('forms','COUNT(*) c', 'name LIKE "Новый блок ротации%"');
			if ($c[0]->c > 0) {
				$name = $name.' '.($c[0]->c+1);
			}
		break;
	}
	$insertArray['id'] = '';
	$insertArray['parent_id'] = $id;
	$insertArray['type'] = $_POST['type'];
	$insertArray['name'] = $name;
	$insertArray['content'] = '';
	$db->insert('forms',$insertArray);	
	unset($menu,$html,$objects);
	$result = $db->select('forms','*','name="'.$name.'"');
	foreach ($result as $row) {
		foreach ($result as $row) {
			if ($row->type != 'block') {
				$count = $db->select('forms','COUNT(*) c','parent_id='.$row->id);				
				$row->count = $count[0]->c;
			} else {
				if ($row->parent_id == 0) {
					$row->parent_type = 'folder';
				} else {
					$pres = $db->select('forms','type','id='.$row->parent_id);
					$row->parent_type = $pres[0]->type;
				}
				
			}
			$html[] = genHTML($row);
			$menu[] = genMenu($row);
			$objects[] = $row;
		}
	}
	out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));	
	break;
	case 'rename':
		$name = trim($_POST['name']);
		$db->update('forms',array('name'=>$name),'id='.$id);
		die('"OK"');
	break;
	case "remove":
		$db->cer_delete('forms','id='.$id);
		die('"ok"');
	break;
}
?>