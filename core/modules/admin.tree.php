<?php
$id = $_POST['id'];
unset($_POST['id']);

if ($params[1] == 'getnames') {
	unset($out);
	for ($i=2; $i < sizeof($params); $i++) { 
		$pages = $db->select('pages','name','id = '.$params[$i]);
		$out[] = $pages[0];
	}
	die(json_encode($out));
}

if ($params[1] == 'get') {
	$pages = $db->select('pages','name, id, type, title, def, link, template, group_id','id <> 202 and id <> 2 and group_id >= '.$_SESSION['group_id'].' and parent_id = '.$params[2],'pos');
	if ($pages != 0) {
		foreach($pages as $row){
			$c = $db->select('pages','COUNT(id) c', 'parent_id ='.$row->id);
			$row->c = $c[0]->c;
			$out[] = $row;
		}
	}
	$groups = $db->select('groups','id,name','id >='.$_SESSION['group_id']);
	$templates = $db->select('templates','id,name','shown >='.$_SESSION['group_id'],'name');
	die(json_encode(array('pages'=>$out,'groups'=>$groups,'templates'=>$templates)));
}
if ($params[1] == 'get_templates') {
	$templates = $db->select('templates','id,name','shown >='.$_SESSION['group_id'],'name');
	die(json_encode(array('templates'=>$templates)));
}
if ($params[1] == 'get_text') {
	$result = $db->select('pages','html','id='.$id);
	die(json_encode($result[0]));
}
if ($params[1] == 'set_text') {
	$result = $db->update('pages',$_POST,'id='.$id);
	die('ok');
}
if ($params[1] == 'get_meta') {
	$result = $db->select('pages','title, description, keywords, h1, header, link, classname','id='.$id);
	die(json_encode($result[0]));
}
if ($params[1] == 'save_pos') {
	for ($i=0; $i < sizeof($_POST['pos']); $i++) { 
		$db->update('pages',array('pos'=>$i),'id='.$_POST['pos'][$i]);
	}
	die('"ok"');
}
if ($params[1] == 'set_meta') {
	
	$db->update('pages',$_POST,'id='.$id);
	die("ok");
}
if ($params[1] == 'remove') {
	$db->cer_delete('pages','id='.$id);
	die('ok');
}
if ($params[1] == 'create_tree') {
	$name = 'Новый каталог';
	$c = $db->select('pages','COUNT(*) c', 'name LIKE "Новый каталог%"');
	if ($c[0]->c > 0) {
		$name = $name.' '.($c[0]->c+1);
	}
	$link = 'untitled_folder';
	$c = $db->select('pages','COUNT(*) c', 'link LIKE "untitled_folder%"');
	if ($c[0]->c > 0) {
		$link = $link.'_'.($c[0]->c+1);
	}
	$insert['id'] = '';
	$insert['parent_id'] = $_POST['parent'];
	$insert['type'] = 'folder';
	$insert['pos'] = 999;
	$insert['link'] = $link;
	$insert['name'] = $name;
	$insert['className'] = '';
	$insert['template'] = 0;
	$insert['infoblock_doc'] = 0;
	$insert['keywords'] = '';
	$insert['description'] = '';
	$insert['title'] = '';
	$insert['h1'] = '';
	$insert['html'] = '';
	$insert['def'] = 'n';
	$insert['user_id'] = $_SESSION['user_id'];
	$insert['group_id'] = $_SESSION['group_id'];
	$insert['create_date'] = '';
	$insert['modified_by'] = 0;
	$insert['modify_date'] = '';
	$insert['del'] = 'n';
	$id = $db->insert('pages',$insert);
	$pages = $db->select('pages','name, id, type, title, def, link, template, group_id','id='.$id);
	if ($pages != 0) {
		foreach($pages as $row){
			$c = $db->select('pages','COUNT(id) c', 'parent_id ='.$row->id);
			$row->c = $c[0]->c;
			$out[] = $row;
		}
	}
	$groups = $db->select('groups','id,name','id >='.$_SESSION['group_id']);
	$templates = $db->select('templates','id,name','shown >='.$_SESSION['group_id'],'name');
	die(json_encode(array('pages'=>$out,'groups'=>$groups,'templates'=>$templates)));
}
if ($params[1] == 'create_page') {
	$name = 'Новая страница';
	$c = $db->select('pages','COUNT(*) c', 'name LIKE "Новая страница%"');
	if ($c[0]->c > 0) {
		$name = $name.' '.($c[0]->c+1);
	}
	$link = 'untitled_page';
	$c = $db->select('pages','COUNT(*) c', 'link LIKE "untitled_page%"');
	if ($c[0]->c > 0) {
		$link = $link.'_'.($c[0]->c+1);
	}
	$insert['id'] = '';
	$insert['parent_id'] = $_POST['parent'];
	$insert['type'] = 'page';
	$insert['pos'] = 999;
	$insert['link'] = $link;
	$insert['name'] = $name;
	$insert['className'] = '';
	$insert['template'] = $_POST['template'];
	$insert['infoblock_doc'] = 0;
	$insert['keywords'] = '';
	$insert['description'] = '';
	$insert['title'] = '';
	$insert['h1'] = '';
	$insert['html'] = '';
	$insert['def'] = 'n';
	$insert['user_id'] = $_SESSION['user_id'];
	$insert['group_id'] = $_SESSION['group_id'];
	$insert['create_date'] = '';
	$insert['modified_by'] = 0;
	$insert['modify_date'] = '';
	$insert['del'] = 'n';
	$db->insert('pages',$insert);
	$pages = $db->select('pages','name, id, type, title, def, link, template, group_id','link="'.$link.'"');
	if ($pages != 0) {
		foreach($pages as $row){
			$c = $db->select('pages','COUNT(id) c', 'parent_id ='.$row->id);
			$row->c = $c[0]->c;
			$out[] = $row;
		}
	}
	$groups = $db->select('groups','id,name','id >='.$_SESSION['group_id']);
	$templates = $db->select('templates','id,name','shown >='.$_SESSION['group_id'],'name');
	die(json_encode(array('pages'=>$out,'groups'=>$groups,'templates'=>$templates)));
}
if ($params[1] == 'rename') {
	$name = trim($_POST['name']);
	$db->update('pages',array('name'=>$name),'id='.$id);
	$link = translit(strtolower($_POST['link']));
	$c = $db->select('pages','COUNT(id) c', 'id <> '.$id.' and link LIKE "'.$link.'%"');
	if ($c[0]->c > 0) {
		$link = $link.'_'.($c[0]->c+1);
	}
	die($link);
}

if ($params[1] == 'mark_def') {
	$db->update('pages',array('def'=>'n'),'1=1');
	$db->update('pages',array('def'=>'y'),'id='.$id);
	$pages = $db->select('pages','name,id, title, def, link, template, group_id','id = '.$id);
	if ($pages != 0) {
		foreach($pages as $row){
			$c = $db->select('pages','COUNT(id) c', 'parent_id ='.$row->id);
			$row->c = $c[0]->c;
			$out[] = $row;
		}
	}
	$groups = $db->select('groups','id,name','id >='.$_SESSION['group_id']);
	$templates = $db->select('templates','id,name','shown >='.$_SESSION['group_id'],'name');
	die(json_encode(array('page'=>$out[0],'groups'=>$groups,'templates'=>$templates)));
}
if ($params[1] == 'set_group') {
	$result = $db->update('pages',$_POST,'id='.$id);
	$pages = $db->select('pages','name, id, type, title, def, link, template, group_id','id = '.$id);
	if ($pages != 0) {
		foreach($pages as $row){
			$c = $db->select('pages','COUNT(id) c', 'parent_id ='.$row->id);
			$row->c = $c[0]->c;
			$out[] = $row;
		}
	}
	$groups = $db->select('groups','id,name','id >='.$_SESSION['group_id']);
	$templates = $db->select('templates','id,name','shown >='.$_SESSION['group_id'],'name');
	die(json_encode(array('page'=>$out[0],'groups'=>$groups,'templates'=>$templates)));
}
if ($params[1] == 'move') {
	$arr['parent_id'] = $_POST['pid'];
	$arr['pos'] = 999;
	$db->update('pages',$arr,'id='.$id);
	die($arr['parent_id']);
}
if ($params[1] == 'set_template') {
	$result = $db->update('pages',$_POST,'id='.$id);
	$pages = $db->select('pages','name, id, type, title, def, link, template, group_id','id = '.$id);
	if ($pages != 0) {
		foreach($pages as $row){
			$c = $db->select('pages','COUNT(id) c', 'parent_id ='.$row->id);
			$row->c = $c[0]->c;
			$out[] = $row;
		}
	}
	$groups = $db->select('groups','id,name','id >='.$_SESSION['group_id']);
	$templates = $db->select('templates','id,name','shown >='.$_SESSION['group_id'],'name');
	die(json_encode(array('page'=>$out[0],'groups'=>$groups,'templates'=>$templates)));
}

?>