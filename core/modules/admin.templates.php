<?php
include($_SERVER['DOCUMENT_ROOT'].'/core/inc/image.class.php');
$id = $_POST['id'];
unset($_POST['id']);

function genMenu($object){
	global $db;
	$menu[] = array("name"=>"Модули","action"=>"modules","inactive"=>false);
	$menu[] = array("name"=>"Блоки","action"=>"blocks","inactive"=>false);
	$menu[] = "separator";
	$menu[] = array("name"=>"Путь к файлу и переменные","action"=>"edit","inactive"=>false);
	
	$groups = $db->select('groups','');
	foreach ($groups as $row) {
		$sub[] = array("name"=>$row->name,"action"=>$row->id,"selected"=> ($row->id == $object->shown)?true:false);
	}
	$menu[] = array("name"=>"Виден","action"=>"set_group","inactive"=>false, "sub"=>$sub);
	$menu[] = "separator";
	$menu[] = array("name"=>"Переименовать","action"=>"rename","inactive"=>false);	
	$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>false);					
	return $menu;
}

function genHTML($object){
	$html = '<div class="page"></div>';
	$html.= '<h1>'.$object->name.'</h1>';
	$html.= '<p>'.$object->path.'</p>';
	$html.='<input type="hidden" name="id" value="'.$object->id.'" />';
	return $html;
}

function out($obj){
	die(json_encode($obj));
}

switch ($params[1]) {
	case 'getall':
		unset($menu,$html,$objects);
		$result = $db->select('templates','*',null,'name');
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
	case "get":
		$result = $db->select('templates','path, templates_set','id='.$id);
		out($result[0]);
	break;
	case "update":
		$db->update('templates',$_POST,'id='.$id);
		unset($menu,$html,$objects);
		$result = $db->select('templates','*','id='.$id);
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
	case "modules":
		$db->cer_delete('links','parent="templates" and child="modules" and pid='.$id);
		foreach($_POST['ids'] as $cid){
			$db->insert('links',array('id'=>'','parent'=>'templates','child'=>'modules','pid'=>$id,'cid'=>$cid));
		}
		out('ok');
	break;
	case 'blocks':
		$res = $db->selectRow('templates','*','id='.$id);
		$content = ($res->path == 'base') ? $res->text : file_get_contents($_SERVER['DOCUMENT_ROOT'].'/templates/'.$res->path);
		preg_match_all('/{\*([A-z0-9_]+)\*}/',$content, $vars);
		$html='';
		if (sizeof($vars[1]) !=0) {
			foreach($vars[1] as $var){
				$res = $db->selectRow('pages_block','*','template_id='.$id.' and block_name="'.$var.'"');
				$html.='<label for="'.$var.'">'.$var.'</label>';
				$blocks1 = $db->select('blocks','*','type="block" and parent_id=0');
				$blocks2 = $db->select('blocks','*','type="group"');
				$blocks3 = $db->select('blocks','*','type="rotate"');
				$blocks = array_merge((array)$blocks1,(array)$blocks2,(array)$blocks3);
				$folder = $db->select('blocks','*','type="folder"');
				if ($folder)
					foreach($folder as $row){
						$blocks = array_merge($blocks,(array)$db->select('blocks','*','type="block" and parent_id='.$row->id));
					}
				$html.='<select name="'.$var.'" id="'.$var.'">';
				$html.='<option value="0" selected>Не показывать</option>';	
				foreach($blocks as $block){
					if($block)
						if($res && $res->block == $block->id)
							$html.= '<option value="'.$block->id.'" selected>'.$block->name.'</option>';
						else
							$html.= '<option value="'.$block->id.'">'.$block->name.'</option>';
					
				}
				$html.='</select>';
			}
			
		} else {
			$html.='<p>В этом шаблоне блоков нет.</p>';
		}	
		out($html);	
	break;
	case "blocks-save":
	
		$db->cer_delete('pages_block','template_id='.$id);
		foreach($_POST['blocs'] as $name=>$bid){
			if ($bid != 0) {
				$db->insert('pages_block',array('template_id'=>$id,'block_name'=>$name,'block'=>$bid));
			}
		}
		die('"OK"');
	break;
	case "create":
	$name = 'Новый шаблон';
	$c = $db->select('templates','COUNT(*) c', 'name LIKE "Новый шаблон%"');
	if ($c[0]->c > 0) {
		$name = $name.' '.($c[0]->c+1);
	}
	$insertArray['id'] = '';
	$insertArray['name'] = $name;
	$insertArray['path'] = 'easy/module.html';
	$insertArray['text'] = '';
	$insertArray['templates_set'] = '';
	$insertArray['shown'] = 5;
	$insertArray['group_id'] = '';
	$insertArray['created_by'] = '';
	$insertArray['create_date'] = '';
	$insertArray['modified_by'] = '';
	$insertArray['modify_date'] = '';
	$insertArray['del'] = 'n';
	$db->insert('templates',$insertArray);	
	unset($menu,$html,$objects);
	$result = $db->select('templates','*','name="'.$name.'"','name');
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
	case 'rename':
		$name = trim($_POST['name']);
		$db->update('blocks',array('name'=>$name),'id='.$id);
		die('"OK"');
	break;
	case "remove":
		$db->cer_delete('templates','id='.$id);
		$db->cer_delete('links','parent="templates" and pid='.$id);
		die('"ok"');
	break;
}
?>