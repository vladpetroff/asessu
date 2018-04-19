<?php
include($_SERVER['DOCUMENT_ROOT'].'/core/inc/image.class.php');
$id = $_POST['id'];
unset($_POST['id']);

function genMenu($object){
	if ($object->type == 'folder') {
		$menu[] = array("name"=>"Открыть","action"=>"open","inactive"=>false);
		$menu[] = "separator";
		foreach($object->users as $user){
			$submenu[] = array("name"=>$user->nickname,"action"=>$user->id, "selected"=>($user->id == $object->user_id)?true:false);
		}				
		$menu[] = array("name"=>"Ответственный","action"=>"set_user","inactive"=>false, "sub"=>$submenu);
		$menu[] = array("name"=>"Переименовать","action"=>"rename","inactive"=>false);
		$menu[] = array("name"=>"Удалить","action"=>"remove_cat","inactive"=>($object->count == 0)?false:true);
	} else {
		if ($object->type == 'new') {
			$menu[] = array("name"=>"Ответить","action"=>"reply","inactive"=>false);
		} else {
			$menu[] = array("name"=>"Редактировать ответ","action"=>"edit_reply","inactive"=>false);
		}
		$menu[] = "separator";			
		$menu[] = array("name"=>"Редактировать вопрос","action"=>"edit_question","inactive"=>false);
		$menu[] = array("name"=>"Удалить","action"=>"remove","inactive"=>($object->count == 0)?false:true);
	}
	
						
	return $menu;
}

function genHTML($object){
	if ($object->type == 'folder') {
		$html = '<div class="folder"></div>';
		$html.= '<h1>'.$object->name.'</h1>';
		$html.='<p>Вопросов: '.$object->count.'</p>';
	} else {
		$html = '<div class="page"></div>';
		$html.= '<h1>Вопрос задал - '.$object->author.'</h1>';
		$html.= '<p>Почта: '.$object->a_email.'</p>';
		$html.= '<p>Дата вопроса: '.$object->create_date.'</p>';
		if ($object->type == 'new') {
			$html.= '<div class="new"></div>';
		}
	}
	$html.='<input type="hidden" name="id" value="'.$object->id.'" />';
	return $html;
}

function out($obj){
	die(json_encode($obj));
}

switch ($params[1]) {
	case 'getall':
		unset($menu,$html,$objects);
		if ($id == 0) {
			$result = $db->select('q_cat');
			if ($result)
			foreach($result as $row){
				$row->type = 'folder';
				$row->count = $db->count('questions','cat='.$row->id);
				$row->users = $db->select('users','*'/*, 'group_id > 1'*/);
				$html[] = genHTML($row);
				$menu[] = genMenu($row);
				$objects[] = $row;	
			}
			else 
				out(array('html'=>0,'menu'=>0,'object'=>0));
			out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
		} else {
			$result = $db->select('questions','*','cat='.$id);
			$i = 0;
			if ($result){
				foreach($result as $row){
					$answer = $db->selectRow('answers','*','question='.$row->id);
					if (!$answer) {
						$row->type = 'new';
						unset($result[$i]);
						$html[] = genHTML($row);
						$menu[] = genMenu($row);
						$objects[] = $row;
					}
					$i++;
				}
				if(sizeof($result)>0)
				foreach($result as $row){
					$row->type = 'old';
					$html[] = genHTML($row);
					$menu[] = genMenu($row);
					$objects[] = $row;
					$i++;
				}							
			}
			else 
				out(array('html'=>0,'menu'=>0,'object'=>0));
			out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
		}
		break;
	case "getnames":
		unset($out);
		for ($i=0; $i < sizeof($id); $i++) { 
			$result = $db->select('q_cat','name','id = '.$id[$i]);
			$out[] = $result[0];
		}
		out($out);		
	break;
	case "get_question":
		$result = $db->selectRow('questions','*','id='.$id);
		out($result);
	break;
	case "get_reply":
		$result = $db->selectRow('answers','*','question='.$id);
		out($result);
	break;
	case "update_question":
		$db->update('questions',$_POST,'id='.$id);
		unset($menu,$html,$objects);
		$result = $db->select('questions','*','id='.$id);
		$i = 0;
		if ($result){
			foreach($result as $row){
				$answer = $db->selectRow('answers','*','question='.$row->id);
				if (!$answer) {
					$row->type = 'new';
					unset($result[$i]);
					$html[] = genHTML($row);
					$menu[] = genMenu($row);
					$objects[] = $row;
				}
				$i++;
			}
			if(sizeof($result)>0)
			foreach($result as $row){
				$row->type = 'old';
				$html[] = genHTML($row);
				$menu[] = genMenu($row);
				$objects[] = $row;
				$i++;
			}							
		}
		else 
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
	break;
	case "update_cat":
		$db->update('q_cat',$_POST,'id='.$id);
		unset($menu,$html,$objects);
		$result = $db->select('q_cat','*','id='.$id);
		if ($result)
		foreach($result as $row){
			$row->type = 'folder';
			$row->count = $db->count('questions','cat='.$row->id);
			$row->users = $db->select('users','*'/*, 'group_id > 1'*/);
			$html[] = genHTML($row);
			$menu[] = genMenu($row);
			$objects[] = $row;	
		}
		else 
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
	break;
	case "update_answer":
		$db->update('answers',$_POST,'question='.$id);
		unset($menu,$html,$objects);
		$result = $db->select('questions','*','id='.$id);
		$i = 0;
		if ($result){
			foreach($result as $row){
				$answer = $db->selectRow('answers','*','question='.$row->id);
				if (!$answer) {
					$row->type = 'new';
					unset($result[$i]);
					$html[] = genHTML($row);
					$menu[] = genMenu($row);
					$objects[] = $row;
				}
				$i++;
			}
			if(sizeof($result)>0)
			foreach($result as $row){
				$row->type = 'old';
				$html[] = genHTML($row);
				$menu[] = genMenu($row);
				$objects[] = $row;
				$i++;
			}							
		}
		else 
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
	break;

	case "create":
		$insertArray['id'] = '';
		$insertArray['name'] = 'Новая категория';
		$insertArray['desc'] = '';
		$insertArray['user_id'] = 1;
		$insertArray['group_id'] = 15;
		$id = $db->insert('q_cat',$insertArray);	
		unset($menu,$html,$objects);
		$result = $db->select('q_cat','*','id='.$id);
		if ($result)
		foreach($result as $row){
			$row->type = 'folder';
			$row->count = $db->count('questions','cat='.$row->id);
			$html[] = genHTML($row);
			$menu[] = genMenu($row);
			$objects[] = $row;	
		}
		else 
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
	break;
	case "reply":
		$insertArray['id'] = '';
		$insertArray['question'] = $id;
		$insertArray['text'] = $_POST['text'];
		$insertArray['user_id'] = $_SESSION['user_id'];
		$id = $db->insert('answers',$insertArray);	
		unset($menu,$html,$objects);
		$result = $db->select('questions','*','id='.$insertArray['question']);
		$i = 0;
		if ($result){
			foreach($result as $row){
				$answer = $db->selectRow('answers','*','question='.$row->id);
				if (!$answer) {
					$row->type = 'new';
					unset($result[$i]);
					$html[] = genHTML($row);
					$menu[] = genMenu($row);
					$objects[] = $row;
				}
				$i++;
			}
			if(sizeof($result)>0)
			foreach($result as $row){
				$row->type = 'old';
				$html[] = genHTML($row);
				$menu[] = genMenu($row);
				$objects[] = $row;
				$i++;
			}							
		}
		else 
			out(array('html'=>0,'menu'=>0,'object'=>0));
		out(array('html'=>$html,'menu'=>$menu,'object'=>$objects));
	break;
	case 'rename':
		$name = trim($_POST['name']);
		$db->update('q_cat',array('name'=>$name),'id='.$id);
		die('"OK"');
	break;
	case "remove":
		$db->cer_delete('questions','id='.$id);
		$db->cer_delete('answers','question='.$id);
		die('"ok"');
	break;
	case "remove_cat":
		$db->cer_delete('q_cat','id='.$id);
		die('"ok"');
	break;
}
?>