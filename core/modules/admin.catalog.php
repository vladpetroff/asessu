<?php
include($_SERVER['DOCUMENT_ROOT'].'/core/inc/image.class.php');
$id = $_POST['id'];
unset($_POST['id']);

function genMenu($object){
	if ($object->type == 'folder') {
		$menu[] = array("name"=>"Открыть","action"=>"open","inactive"=>false);
		$menu[] = "separator";
		$menu[] = array("name"=>"Редактировать описание","action"=>"edit_text","inactive"=>false);
		$menu[] = array("name"=>"Изображение каталога","action"=>"images","inactive"=>false);
	} else {	
		$menu[] = array("name"=>"Редактировать описание","action"=>"edit_text","inactive"=>false);
		//$menu[] = array("name"=>"Редактировать тех. характеристики","action"=>"edit_tx","inactive"=>false);
		$menu[] = "separator";
		$menu[] = array("name"=>"Изображение товара","action"=>"images","inactive"=>false);
		$hot = ($object->hot == 1)?"/0":"/1";
		$menu[] = array("name"=>"Горячее предложение","action"=>"mark_hot".$hot,"inactive"=>false,"set"=>($object->hot == 1)?true:false);	
		$menu[] = "separator";			
	}
	$menu[] = array("name"=>"Переименовать","action"=>"rename","inactive"=>false);	
	$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>($object->count == 0)?false:true);					
	return $menu;
}

function genHTML($object){
	if ($object->info_id == 0) {
		$html = '<div class="folder"></div>';
	} else {
		if (isset($object->thumb)) {
			$html = '<div class="page thumb" style="background:transparent url('.Image::get($object->thumb,49,57,'ResizeCut').') 4px 4px no-repeat;"></div>';
		} else {
			$html = '<div class="page"></div>';
		}
		
	}
	$html.= '<h1>'.$object->name.'</h1>';
	if ($object->info_id == 0){
		$html.='<p>Элементов:'.$object->count.'</p>';
		$html.='<input type="hidden" name="id" value="'.$object->id.'" />';
	} else {
		//$html.='<p>'.$object->link.'.html</p>';
		//$html.='<p><span>Цена: </span><a class="price" href="#price">'.$object->price.' руб.</a><span>Количество на складе: </span><a class="quantity" href="#quantity">'.$object->quantity.' шт.</a></p>';
		$html.='<input type="hidden" name="id" value="'.$object->id.'" />';
	}
	if ($object->hot == 1) {
		$html.='<div class="hot"></div>';
	}
	return $html;
}

function out($obj){
	die(json_encode($obj));
}

switch ($params[1]) {
	case 'getall':
		unset($menu,$html,$objects);
		$result = $db->select('catalog','*','parent_id='.$id,'order');

		if ($result != 0)
		foreach ($result as $row) {
			if ($row->info_id == 0) {
				$count = $db->select('catalog','COUNT(*) c','parent_id='.$row->id);				
				$row->count = $count[0]->c;
				$row->type = 'folder';
			} else {
				$i = $db->select('links','cid','parent="catalog" and child="images" and pid = '.$row->id,'id');
				if ($i != 0) {
					$ii = $db->select('images','filename','id='.$i[0]->cid);
					if ($ii !=0){
						$row->thumb = $ii[0]->filename;
					}
				}				$row->type = 'page';
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
			$result = $db->select('catalog','name','id = '.$id[$i]);
			$out[] = $result[0];
		}
		out($out);		
	break;
	case "get_text":
		$result = $db->select('catalog','text html','id='.$id);
		out($result[0]);
	break;
	case "get_tx":
		$result = $db->select('catalog','tx html','id='.$id);
		out($result[0]);
	break;
	case "update":
		$db->update('catalog',$_POST,'id='.$id);
		$res = $db->select('catalog','*','id='.$id);
		$res = $res[0];
		if ($res->info_id == 0) {
			$count = $db->select('catalog','COUNT(*) c','parent_id='.$res->id);				
			$res->count = $count[0]->c;
			$res->type = 'folder';
		} else {
				$i = $db->select('links','cid','parent="catalog" and child="images" and pid = '.$row->id,'id');
				if ($i != 0) {
					$ii = $db->select('images','filename','id='.$i[0]->cid);
					if ($ii !=0){
						$row->thumb = $ii[0]->filename;
					}
				}
			$res->type = 'page';
		}
		$html = genHTML($res);
		$menu = genMenu($res);
		$object = $res;
		out(array('html'=>$html,'menu'=>$menu,'object'=>$object));
	break;
	case "setimgs":
		$db->cer_delete('links','parent="catalog" and child="images" and pid = '.$id);
		foreach($_POST['imgs'] as $img){
			$insertArray['id'] = '';
			$insertArray['parent'] = 'catalog';
			$insertArray['child'] = 'images';
			$insertArray['pid'] = $id;
			$insertArray['cid'] = $img;
			$db->insert('links',$insertArray);
		}
		die("OK");
	break;
	case "getimgs":
		unset($out);
		$links = $db->select('links','cid','parent="catalog" and child="images" and pid='.$id,'id');
		if ($links !=0)
		foreach($links as $link){
			$image = $db->select('images','filename','id='.$link->cid);
			$out[] = array('filename'=>Image::get($image[0]->filename,50,50,'ResizeCut'),'id'=>$link->cid);
		}
		else
			$out = 0;
		out($out);
	break;
	case "create_dir":
	$name = 'Новый каталог';
	$c = $db->select('catalog','COUNT(*) c', 'name LIKE "Новый каталог%"');
	if ($c[0]->c > 0) {
		$name = $name.' '.($c[0]->c+1);
	}
	$link = 'untitled_folder';
	$c = $db->select('catalog','COUNT(*) c', 'link LIKE "untitled_folder%"');
	if ($c[0]->c > 0) {
		$link = $link.'_'.($c[0]->c+1);
	}
	$insertArray['id'] = '';
	$insertArray['parent_id'] = $id;
	$insertArray['name'] = $name;
	$insertArray['text'] = '';
	$insertArray['link'] = $link;
	$insertArray['tx'] = '';
	$insertArray['title'] = '';
	$insertArray['description'] = '';
	$insertArray['keywords'] = '';
	$insertArray['h1'] = '';
	$insertArray['info_id'] = 0;
	$insertArray['mainpage'] = 0;
	$insertArray['order'] = 999;
	$insertArray['quantity'] = 0;
	$insertArray['hot'] = 0;
	$insertArray['price'] = 0;
	$db->insert('catalog',$insertArray);	
	unset($menu,$html,$objects);
	$result = $db->select('catalog','*','link="'.$link.'"','order');
	foreach ($result as $row) {
		$count = $db->select('catalog','COUNT(*) c','parent_id='.$row->id);				
		$row->count = $count[0]->c;
		$row->type = 'folder';
		$html[] = genHTML($row);
		$menu[] = genMenu($row);
		$objects[] = $row;
	}
	out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));	
	break;
	case "create_item":
		$name = 'Новый товар';
		$c = $db->select('catalog','COUNT(*) c', 'name LIKE "Новый товар%"');
		if ($c[0]->c > 0) {
			$name = $name.' '.($c[0]->c+1);
		}
		$link = 'untitled_item';
		$c = $db->select('catalog','COUNT(*) c', 'link LIKE "untitled_item%"');
		if ($c[0]->c > 0) {
			$link = $link.'_'.($c[0]->c+1);
		}
		$insertArray['id'] = '';
		$insertArray['parent_id'] = $id;
		$insertArray['name'] = $name;
		$insertArray['text'] = '';
		$insertArray['link'] = $link;
		$insertArray['tx'] = '';
		$insertArray['title'] = '';
		$insertArray['description'] = '';
		$insertArray['keywords'] = '';
		$insertArray['h1'] = '';
		$insertArray['info_id'] = 1;
		$insertArray['mainpage'] = 0;
		$insertArray['order'] = 999;
		$insertArray['quantity'] = 0;
		$insertArray['hot'] = 0;
		$insertArray['price'] = 0;
		$db->insert('catalog',$insertArray);	
		unset($menu,$html,$objects);
		$result = $db->select('catalog','*','link="'.$link.'"','order');
		foreach ($result as $row) {
			// $i = $db->select('links','image_id','cat_id='.$row->id,'id');
			// if ($i != 0) {
			// 	$img = $db->select('images','filename','id='.$i[0]->image_id);
			// 	$row->thumb = $img[0]->filename;
			// }
			$row->type = 'page';
			$html[] = genHTML($row);
			$menu[] = genMenu($row);
			$objects[] = $row;
		}
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));	
	break;
	case 'rename':
		$name = trim($_POST['name']);
		$db->update('catalog',array('name'=>$name),'id='.$id);
		$link = translit(strtolower($_POST['link']));
		$c = $db->select('catalog','COUNT(id) c', 'id <> '.$id.' and link LIKE "'.$link.'%"');
		if ($c[0]->c > 0) {
			$link = $link.'_'.($c[0]->c+1);
		}
		die($link);
	break;
	case "remove":
		$db->cer_delete('catalog','id='.$id);
		$db->cer_delete('links','parent="catalog" AND child="images" AND pid='.$id);
		die('"ok"');
	break;
	case "save_pos":
		for ($i=0; $i < sizeof($_POST['pos']); $i++) { 
			$db->update('catalog',array('order'=>$i),'id='.$_POST['pos'][$i]);
		}
		out('OK');
	break;
}
?>