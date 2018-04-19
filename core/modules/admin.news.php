<?php
include($_SERVER['DOCUMENT_ROOT'].'/core/inc/image.class.php');
$id = $_POST['id'];
unset($_POST['id']);

function genMenu($object){
	$menu[] = array("name"=>"Изменить текст","action"=>"open","inactive"=>false);
	$menu[] = array("name"=>"Изменить анонс","action"=>"edit_short","inactive"=>false);
	$menu[] = array("name"=>"Изменить дату","action"=>"date/".$object->date,"inactive"=>false);
	$menu[] = array("name"=>"Изображение","action"=>"images","inactive"=>false);
	$menu[] = "separator";
	$hot = ($object->type == 6)?"/1":"/6";
	$menu[] = array("name"=>"Важная новость","action"=>"mark_hot".$hot,"inactive"=>false,"set"=>($object->type == 6)?true:false);
	$menu[] = "separator";
	$menu[] = array("name"=>"Переименовать","action"=>"rename","inactive"=>false);
	$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>false);					
	return $menu;
}

function genHTML($object){
	$html = '<div class="folder"></div>';
	$html.= '<h1>'.$object->header.'</h1>';
	$html.='<p>'.htmlspecialchars_decode($object->title).'</p>';
	$html.='<input type="hidden" name="id" value="'.$object->id.'" />';
	if($object->type == 6)
	$html.='<div class="fav"></div>';
	return $html;
}

function out($obj){
	die(json_encode($obj));
}

switch ($params[1]) {
	case 'getpages':
		unset($out);
		$c = $db->select('news','count(*) as c');
		out(array('count' => gpages($c[0]->c)));
		break;

	case 'getpage':
		unset($menu,$html,$objects);
		$page = (isset($params[2]))? (int)$params[2]:1;		
		$result = $db->select('news','*',null,'date',true,10,($page-1)*10);
		if ($result != 0){
			//$result = gpage($result,$page);
			foreach ($result as $row) {
				$html[] = genHTML($row);
				$menu[] = genMenu($row);
				$objects[] = $row;
			}			
		}
		else {
			out(array('html'=>0,'menu'=>0,'object'=>0));			
		}
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
		break;

	case 'getall':
		unset($menu,$html,$objects);
		$result = $db->select('news','*','','date',true);
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
	$result = $db->select('news','text html, header, date','id='.$id);
	out($result[0]);
	break;
	case "get_short":
	$result = $db->select('news','title html, header, date','id='.$id);
	out($result[0]);
	break;
	case "setimgs":
		$db->cer_delete('links','parent="news" and child="images" and pid = '.$id);
		foreach($_POST['imgs'] as $img){
			$insertArray['id'] = '';
			$insertArray['parent'] = 'news';
			$insertArray['child'] = 'images';
			$insertArray['pid'] = $id;
			$insertArray['cid'] = $img;
			$db->insert('links',$insertArray);
		}
		die("OK");
	break;
	case "getimgs":
		unset($out);
		$links = $db->select('links','cid','parent="news" and child="images" and pid='.$id,'id');
		if ($links !=0)
		foreach($links as $link){
			$image = $db->select('images','filename','id='.$link->cid);
			$out[] = array('filename'=>Image::get($image[0]->filename,50,50,'ResizeCut'),'id'=>$link->cid);
		}
		else
			$out = 0;
		out($out);
	break;	
	case "remove":
		$db->cer_delete('news','id='.$id);
		die('"ok"');
	break;
	case "rename":
		$name = trim($_POST['name']);
		$db->update('news',array('header'=>$name),'id='.$id);
		$link = translit(strtolower($_POST['link']));
		$c = $db->select('pages','COUNT(id) c', 'id <> '.$id.' and link LIKE "'.$link.'%"');
		if ($c[0]->c > 0) {
			$link = $link.'_'.($c[0]->c+1);
		}
		out($link);
	break;
	case "update":
		$db->update('news',$_POST,'id='.$id);
		$result = $db->select('news','*','id='.$id,'date',true);
		if ($result != 0){
			$html = genHTML($result[0]);
			$menu = genMenu($result[0]);
			$objects = $result[0];			
		}
		else
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
	break;
	case "update-text":
		$db->update('news',$_POST,'id='.$id);
		out($_POST['text']);
	break;
	case "create_act":
	$name = 'Акция';
	$c = $db->select('news','COUNT(*) c', 'header LIKE "Акция%"');
	if ($c[0]->c > 0) {
		$name = $name.' '.($c[0]->c+1);
	}
	$link = 'article';
	$c = $db->select('news','COUNT(*) c', 'link LIKE "article%"');
	if ($c[0]->c > 0) {
		$link = $link.'_'.($c[0]->c+1);
	}
	$insertArray['id'] = '';
	$insertArray['header'] = $name;
	$insertArray['link'] = $link;
	$insertArray['title'] = '';
	$insertArray['image'] = 0;
	$insertArray['text'] = '';
	$insertArray['date'] = date('Y-m-d');
	$insertArray['type'] = 6;
	$db->insert('news',$insertArray);	
	unset($menu,$html,$objects);
	$result = $db->select('news','*','link="'.$link.'"');
	if ($result != 0){
		$html = genHTML($result[0]);
		$menu = genMenu($result[0]);
		$objects = $result[0];			
	}
	else
		out(array('html'=>0,'menu'=>0,'object'=>0));
	out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
	break;
	case "create_new":
	$name = 'Новость';
	$c = $db->select('news','COUNT(*) c', 'header LIKE "Новость%"');
	if ($c[0]->c > 0) {
		$name = $name.' '.($c[0]->c+1);
	}
	$link = 'article';
	$c = $db->select('news','COUNT(*) c', 'link LIKE "article%"');
	if ($c[0]->c > 0) {
		$link = $link.'_'.($c[0]->c+1);
	}
	$insertArray['id'] = '';
	$insertArray['header'] = $name;
	$insertArray['link'] = $link;
	$insertArray['title'] = '';
	$insertArray['image'] = 0;
	$insertArray['text'] = '';
	$insertArray['date'] = date('Y-m-d');
	$insertArray['type'] = 1;
	$db->insert('news',$insertArray);	
	unset($menu,$html,$objects);
	$result = $db->select('news','*','link="'.$link.'"');
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