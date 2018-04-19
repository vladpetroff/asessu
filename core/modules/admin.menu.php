<?php


$result = $db->select('pages','name, link, classname className','id <> 202 and parent_id = 10 and group_id >='.$_SESSION['group_id'],'pos');
$out[0]['name'] = 'Управление контентом';
$out[0]['items'] = $result;			

$result = $db->select('pages','name, link, classname className','parent_id = 287 and group_id >='.$_SESSION['group_id'],'pos');
$out[1]['name'] = 'Менеджмент';
$out[1]['items'] = $result;			

$result = $db->select('pages','name, link, classname className','parent_id = 286 and group_id >='.$_SESSION['group_id'],'pos');
$out[2]['name'] = 'Управление сайтом';
$out[2]['items'] = $result;			



for ($i=0; $i < sizeof($out); $i++) { 
	$menu[] = $out[$i];
}
wr('ADMIN_MENU',json_encode($menu));

wr('USER_GROUP',$_SESSION['group_id']);
?>