<?php
include($_SERVER['DOCUMENT_ROOT'].'/core/inc/image.class.php');
$id = $_POST['id'];
unset($_POST['id']);

function genMenu($object){
	$menu[] = array("name"=>"Открыть","action"=>"open","inactive"=>false);
	$menu[] = "separator";
	$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>false);					
	return $menu;
}

function genHTML($object){
	$html = '<div class="folder"></div>';
	$html.= '<h1>'.$object->name.'</h1>';
	$phone = ($object->phone != '')?'<span class="phone">Телефон: '.$object->phone.'</span>':'';
	$mail = ($object->mail != '')?'<span class="mail">Телефон: '.$object->mail.'</span>':'';
	$html.='<p>'.$phone.$mail.'</span></p>';
	$html.='<input type="hidden" name="id" value="'.$object->id.'" />';
	return $html;
}

function out($obj){
	die(json_encode($obj));
}

switch ($params[1]) {
	case 'getall':
		unset($menu,$html,$objects);
		$result = $db->select('ecg','*','','id',true);
		if ($result != 0)
		foreach ($result as $row) {
			$row->type = 'folder';
			$html[] = genHTML($row);
			$menu[] = genMenu($row);
			$objects[] = $row;
		}
		else
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
		break;
	case "get_text":
		$result = $db->select('ecg','*','id='.$id);
		$html = '';
		$html.= '<h1>Кардиограмма #'.$result[0]->id.'</h1>';
		$html.= '<dl>';
		$html.= '<a style="clear: both; display: block; margin: 10px; float: left;" href="/media/uploads/ecg/'.$result[0]->filename.'">Скачать кардиограмму</a>';
		$html.='<dt>Имя:</dt><dd>'.$result[0]->name.'</dd>';
		$sex = ($result[0]->sex == 'f')?'Женский':'Мужской';
		$html.='<dt>Пол:</dt><dd>'.$sex.'</dd>';
		$html.='<dt>Возраст:</dt><dd>'.$result[0]->age.'</dd>';
		$html.='<dt>Телефон:</dt><dd>'.$result[0]->phone.'</dd>';
		$html.='<dt>Электронная почта:</dt><dd>'.$result[0]->mail.'</dd>';
		$html.='<dt>Дополнительная информация:</dt><dd><pre>'.$result[0]->additional.'</pre></dd>';
		$html.='</dl>';
		
		out($html);
	break;
	case "remove":
		$res=$db->selectRow('ecg','filename','id='.$id);
		$db->cer_delete('ecg','id='.$id);
		@unlink($_SERVER['DOCUMENT_ROOT'].'/media/uploads/ecg/'.$res->filename);
		die('"ok"');
	break;
}
?>