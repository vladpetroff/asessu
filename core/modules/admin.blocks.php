<?php
include($_SERVER['DOCUMENT_ROOT'].'/core/inc/image.class.php');
$id = $_POST['id'];
unset($_POST['id']);

function genMenu($object){
	if ($object->type != 'block') {
		$menu[] = array("name"=>"Открыть","action"=>"open","inactive"=>false);
		$menu[] = "separator";
	} else {
		if ($object->parent_type == 'folder') {
			$menu[] = array("name"=>"Редактировать текст","action"=>"edit_text","inactive"=>false);
		} else if ($object->parent_type == 'rotate') {
			$menu[] = array("name"=>"Редактировать текст","action"=>"edit_text","inactive"=>false);
		} else if ($object->parent_type == 'group') {
			$menu[] = array("name"=>"Редактировать текст","action"=>"edit_text","inactive"=>false);
			$menu[] = array("name"=>"Изображение","action"=>"images","inactive"=>false);
		}
		$menu[] = "separator";			
	}
	$menu[] = array("name"=>"Переименовать","action"=>"rename","inactive"=>false);	
	$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>($object->count == 0)?false:true);					
	return $menu;
}

function genHTML($object){
	if ($object->type == 'block') {
		$html = '<div class="page"></div>';
	} else if($object->type == 'folder') {
		$html = '<div class="folder"></div>';
	} else if($object->type == 'rotate') {
		$html = '<div class="folder-rotate"></div>';
	} else if($object->type == 'group') {
		$html = '<div class="folder-group"></div>';
	}
	$html.= '<h1>'.$object->name.'</h1>';
	if ($object->type != 'block'){
		$html.='<p>Элементов:'.$object->count.'</p>';
		$html.='<input type="hidden" name="id" value="'.$object->id.'" />';
	} else {
		//$html.='<p>Элементов:'.$object->parent_type.'</p>';
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
		$result = $db->select('blocks','*','parent_id='.$id);
		if ($result != 0)
		foreach ($result as $row) {
			if ($row->type != 'block') {
				$count = $db->select('blocks','COUNT(*) c','parent_id='.$row->id);				
				$row->count = $count[0]->c;
			} else {
				if ($row->parent_id == 0) {
					$row->parent_type = 'folder';
				} else {
					$pres = $db->select('blocks','type','id='.$row->parent_id);
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
			$result = $db->select('blocks','name','id = '.$id[$i]);
			$out[] = $result[0];
		}
		out($out);		
	break;
	case "get_text":
		$result = $db->select('blocks','content html','id='.$id);
		out($result[0]);
	break;
	case "update":
		$db->update('blocks',$_POST,'id='.$id);
		unset($menu,$html,$objects);
		$result = $db->select('blocks','*','id='.$id);
		foreach ($result as $row) {
			foreach ($result as $row) {
				if ($row->type != 'block') {
					$count = $db->select('blocks','COUNT(*) c','parent_id='.$row->id);				
					$row->count = $count[0]->c;
				} else {
					if ($row->parent_id == 0) {
						$row->parent_type = 'folder';
					} else {
						$pres = $db->select('blocks','type','id='.$row->parent_id);
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
	case "setimgs":
		$db->cer_delete('links','parent="block" and child="images" and pid = '.$id);
		foreach($_POST['imgs'] as $img){
			$insertArray['id'] = '';
			$insertArray['parent'] = 'blocks';
			$insertArray['child'] = 'images';
			$insertArray['pid'] = $id;
			$insertArray['cid'] = $img;
			$db->insert('links',$insertArray);
		}
		die("OK");
	break;
	case "getimgs":
		unset($out);
		$links = $db->select('links','cid','parent="blocks" and child="images" and pid='.$id,'id');
		if ($links !=0)
		foreach($links as $link){
			$image = $db->select('images','filename','id='.$link->cid);
			$out[] = array('filename'=>Image::get($image[0]->filename,50,50,'ResizeCut'),'id'=>$link->cid);
		}
		else
			$out = 0;
		out($out);
	break;
	case "create":
	switch($_POST['type']){
		case "folder":
			$name = 'Новый каталог';
			$c = $db->select('blocks','COUNT(*) c', 'name LIKE "Новый каталог%"');
			if ($c[0]->c > 0) {
				$name = $name.' '.($c[0]->c+1);
			}
		break;
		case "block":
			$name = 'Новый блок';
			$c = $db->select('blocks','COUNT(*) c', 'name LIKE "Новый блок%"');
			if ($c[0]->c > 0) {
				$name = $name.' '.($c[0]->c+1);
			}
		break;
		case "group":
			$name = 'Новая группа';
			$c = $db->select('blocks','COUNT(*) c', 'name LIKE "Новая группа%"');
			if ($c[0]->c > 0) {
				$name = $name.' '.($c[0]->c+1);
			}
		break;
		case "rotate":
			$name = 'Группа ротации';
			$c = $db->select('blocks','COUNT(*) c', 'name LIKE "Новый блок ротации%"');
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
	$db->insert('blocks',$insertArray);	
	unset($menu,$html,$objects);
	$result = $db->select('blocks','*','name="'.$name.'"');
	foreach ($result as $row) {
		foreach ($result as $row) {
			if ($row->type != 'block') {
				$count = $db->select('blocks','COUNT(*) c','parent_id='.$row->id);				
				$row->count = $count[0]->c;
			} else {
				if ($row->parent_id == 0) {
					$row->parent_type = 'folder';
				} else {
					$pres = $db->select('blocks','type','id='.$row->parent_id);
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
		$db->update('blocks',array('name'=>$name),'id='.$id);
		die('"OK"');
	break;
	case "remove":
		$db->cer_delete('blocks','id='.$id);
		die('"ok"');
	break;
}
?>