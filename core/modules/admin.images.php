<?php
include($_SERVER['DOCUMENT_ROOT'].'/core/inc/image.class.php');
$id = $_POST['id'];
unset($_POST['id']);

function genMenu($object){
	if ($object->type == 'folder') {
		$menu[] = array("name"=>"Открыть","action"=>"open","inactive"=>false);
		$menu[] = "separator";
		$menu[] = array("name"=>"Переименовать","action"=>"rename","inactive"=>false);	
		$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>($object->count == 0)?false:true);
	} else {	
		$menu[] = array("name"=>"Показать оригинал","action"=>"openimg","inactive"=>false);
		$menu[] = "separator";	
		$menu[] = array("name"=>"Переименовать","action"=>"rename","inactive"=>false);	
		$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>($object->count == 0)?false:true);		
	}
					
	return $menu;
}

function genHTML($object){
	if ($object->type == 'folder') {
		$html = '<div class="folder"><img alt="'.$object->name.'" src="/templates/easy/i/images-folder.png"/></div>';
	} else {
		$html = '<div class="page"><img alt="'.$object->name.'" src="'.Image::get($object->filename,127,127,'ResizeCut').'"/></div>';
		$html.= '<a style="display:none" class="lightbox id-'.$object->id.'"  title="'.$object->name.'" href="'.Image::get($object->filename,900,480,'ResizeAuto').'">Увеличить</a>';
		$html.= '<input type="hidden" name="id" value="'.$object->id.'">';
	}
	$html.= '<h1>'.$object->name.'</h1>';
	if ($object->type == 'folder'){
		$html.='<p>Элементов:'.$object->count.'</p>';
	}
	return $html;
}

function out($obj){
	die(json_encode($obj));
}

switch ($params[1]) {
	case 'getall':
		$result = $db->select('images','*','parent_id='.$id,'name');
		if ($result != 0){
			foreach ($result as $row) {
				if ($row->type == 'folder') {
					$count = $db->select('images','COUNT(*) c','parent_id='.$row->id);				
					$row->count = $count[0]->c;
				} else {
					$count = $db->select('links','COUNT(*) c','parent="catalog" AND child="images" AND cid='.$row->id);	
					$row->count = $count[0]->c;
				}
				$html[] = genHTML($row);
				$menu[] = genMenu($row);
				$objects[] = $row;
			}
			out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));	
		} else {
			out(array('html'=>0,'menu'=>0,'object'=>0));
		}
		
		break;
	case "getnames":
		unset($out);
		for ($i=0; $i < sizeof($id); $i++) { 
			$result = $db->select('images','name','id = '.$id[$i]);
			$out[] = $result[0];
		}
		out($out);		
	break;
	case "create_dir":
	$name = 'Новый каталог';
	$c = $db->select('images','COUNT(*) c', 'name LIKE "Новый каталог%"');
	if ($c[0]->c > 0) {
		$name = $name.' '.($c[0]->c+1);
	}
	$insert['id'] = '';
	$insert['parent_id'] = $id;
	$insert['name'] = $name;
	$insert['type'] = 'folder';
	$insert['filename'] = '';
	$insert['order'] = 999;
	$insert['user_id'] = $_SESSION['user_id'];
	$insert['group_id'] = $_SESSION['group_id'];
	$insert['create_date'] = '';
	$insert['modified_by'] = 0;
	$insert['modify_date'] = '';

	$db->insert('images',$insert);
	$res = $db->select('images','name, id, type','name="'.$name.'"');
	$count = $db->select('images','COUNT(*) c','parent_id='.$res[0]->id);
	$res[0]->count = $count->c;
	$html = genHTML($res[0]);
	$menu = genMenu($res[0]);
	$object = $res[0];
	out(array('html'=>$html,'menu'=>$menu,'object'=>$object));	
	break;
	case "upload":
		$fz = filesize($_FILES['file']['tmp_name']);
		$name = $_FILES['file']['name'];
		$filename = Image::upload($_FILES['file']['tmp_name'],$_FILES['file']['name']);
		$tr = translit($filename);
		rename( $_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$filename , $_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$tr);
		$filename = $tr;
		if ($filename) {
			// $name = 'Новое изображение';
			$c = $db->select('images','COUNT(*) c', 'name LIKE "'.$name.'%"');
			if ($c[0]->c > 0) {
				$name = $name.' '.($c[0]->c+1);
			}
			$insert['id'] = '';
			$insert['parent_id'] = $id;
			$insert['name'] = $name;
			$insert['type'] = 'file';
			$insert['filename'] = $filename;
			$insert['order'] = 999;
			$insert['user_id'] = $_SESSION['user_id'];
			$insert['group_id'] = $_SESSION['group_id'];
			$insert['create_date'] = '';
			$insert['modified_by'] = 0;
			$insert['modify_date'] = '';

			$db->insert('images',$insert);
			$res = $db->select('images','name, id, type, filename','name="'.$name.'"');
			$menu = genMenu($res[0]);
			$object = $res[0];
			$object->prev = Image::get($res[0]->filename,127,127,'ResizeCut');
			$object->big = Image::get($res[0]->filename,900,480,'ResizeAuto');
			out(array('menu'=>$menu,'object'=>$object));
		} else {
			out(array('error'=>true));
		}
	break;	
	case "upload-bin":
	$filename = Image::uploadBin();
	foreach($_SERVER as $h=>$v)
		if(preg_match('/HTTP_(.+)/',$h,$hp))
			$headers[$hp[1]]=$v;
	$name = $headers['X_FILE_NAME'];
	$tr = translit($filename);
	rename( $_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$filename , $_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$tr);
	$filename = $tr;
	if ($filename) {
		// $name = 'Новое изображение';
		$c = $db->select('images','COUNT(*) c', 'name LIKE "'.$name.'%"');
		if ($c[0]->c > 0) {
			$name = $name.' '.($c[0]->c+1);
		}
		$insert['id'] = '';
		$insert['parent_id'] = $params[2];
		$insert['name'] = $name;
		$insert['type'] = 'file';
		$insert['filename'] = $filename;
		$insert['order'] = 999;
		$insert['user_id'] = $_SESSION['user_id'];
		$insert['group_id'] = $_SESSION['group_id'];
		$insert['create_date'] = '';
		$insert['modified_by'] = 0;
		$insert['modify_date'] = '';

		$db->insert('images',$insert);
		$res = $db->select('images','name, id, type, filename','name="'.$name.'"');
		$menu = genMenu($res[0]);
		$object = $res[0];
		$object->prev = Image::get($res[0]->filename,127,127,'ResizeCut');
		$object->big = Image::get($res[0]->filename,900,480,'ResizeAuto');
		out(array('menu'=>$menu,'object'=>$object));
	} else {
		out(array('error'=>true));
	}
	break;
	case "update":
		$db->update('images',$_POST,'id='.$id);
		$res = $db->select('images','name, id, type, filename','id='.$id);
		$res = $res[0];
		if ($res->type == 'folder') {
			$count = $db->select('images','COUNT(*) c','parent_id='.$res->id);				
			$res->count = $count[0]->c;
		}
		$html = genHTML($res);
		$menu = genMenu($res);
		$object = $res;
		out(array('html'=>$html,'menu'=>$menu,'object'=>$object));
	break;
	case "remove":
		$res = $db->select('images','*','id='.$id);
		if ($res[0]->type == 'file') {
			Image::delete($res[0]->filename);
		}
		$db->cer_delete('images','id='.$id);
	break;
}
?>