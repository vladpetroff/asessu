<?php

##[k] подключение классов и необходимых файлов
include 'inc/utils.php';
include 'inc/easy.mysql.class.php';


##[k] запуск счетчика производительности
$time_start = getmicrotime();
$_SESSION['current_module'] = 'Диспетчер модулей';



##[k] формирование экземпляра класса работы с базой
$db = new EasyMysql;

session_start();

##[k] установка в сессии анонимного пользователя
$_SESSION['user_id'] = (isset($_SESSION['user_id'])) ?  $_SESSION['user_id'] : 2;
##[k] установка в сессии группы для анонимного пользователя
if (!isset($_SESSION['group_id'])) $_SESSION['group_id'] = 5;

##[k] создание массива параметров конфигурации, переданных методом GET, если есть в запросе
if (isset($_GET['params'])){
	$params=explode('/',clear($_GET['params']));
}
##[k] установка главной страницы при отсутствии запросного параметра
if (!isset($params[0])){
	$where = " t_pages.def = 'y'";
}
##[k] или взять из запроса
else if(($params[0] == 'pages') && (isset($params[1]))){
	$where = " t_pages.link = '$params[1]'";
} else{
	$where = " t_pages.link = '$params[0]'";
}

$pages = $db->set_prefix('pages');
$templates = $db->set_prefix('templates');
$config = $db->set_prefix('config');
//$where.="AND $pages.type = 'page' AND $pages.group_id >= ".$_SESSION['group_id'];
$where.="AND ($pages.type = 'page' OR $pages.type = 'folder') AND $pages.group_id >= ".$_SESSION['group_id'];
//$where.=" AND $pages.group_id >= ".$_SESSION['group_id'];
$query = "
	SELECT 
	$pages.id, $pages.parent_id, $pages.type, $pages.header, $pages.link, $pages.template,
	IF($pages.keywords='',(SELECT `value` FROM `$config` WHERE name = 'default_keywords'),$pages.keywords) as keywords, 
	IF($pages.description='',(SELECT `value` FROM `$config` WHERE name = 'default_description'),$pages.description) as description, 
	IF($pages.title='',(SELECT `value` FROM `$config` WHERE name = 'default_title'),$pages.title) as title, 
	IF($pages.h1='',(SELECT `value` FROM `$config` WHERE name = 'default_h1'),$pages.h1) as h1,
	$pages.html, $pages.name,
	$templates.text, $templates.templates_set, $templates.path
	FROM `$pages`
	JOIN $templates ON $templates.id = $pages.template
	WHERE $where;
";

$page = $db->query($query);

$page = $page[0];

if ($page->link == '') {
    header("HTTP/1.0 404 Not Found");
    die(file_get_contents('error/404.htm'));
}

if ($page->type != 'page') {
	//[k] запрошена папка или несуществующая страница, поищем детей папки доступных для просмотра
	$firstchild = $db->select('pages','*','parent_id = '.$page->id,'pos');
	if ($firstchild != 0){
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /".$firstchild[0]->link.'.html');
		die();		
	}
	else {
	    header("HTTP/1.0 404 Not Found");
	    die(file_get_contents('error/404.htm'));		
	}
}


if (empty($page->template))	die ('не найдена запись шаблона в базе');
if ($page->path != 'base' && !file_exists($_SERVER['DOCUMENT_ROOT'].'/templates/'.$page->path)) die ('не найден файл шаблона с именем «'.$page->path.'»');

##[k] промежуточный массив переменных подстановки получаемый на основе информации из базы данных
$rep=explode(',',$page->templates_set);
foreach ($rep as $element){
##[k]  в массиве replace будет храниться информация о подставляемых данных. изначально массив заполняется пустыми значениями чтобы корректно проводить замену.
	if ($element != '') $replace[$element]='';
}
$modules = $db->set_prefix('modules');
$links = $db->set_prefix('links');
$query = "
	SELECT $modules.name, $modules.description
	FROM $links
	LEFT JOIN $modules
	ON $modules.id = $links.cid 
	WHERE $links.pid = $page->template
	AND parent = 'templates' AND child = 'modules'
";
$modules = $db->query($query);


$easy = new easy(array(
	'db'=>$db,
	'uid'=>$_SESSION['user_id'],
	'gid'=>$_SESSION['group_id'],
	'page'=>$page,
	'params'=>$params
	));
	

if ($modules != 0)
foreach($modules as $module){
	if (file_exists(MODULES_PATH.$module->name)){
		$_SESSION['current_module'] = $module->description;
        require_once(MODULES_PATH.$module->name);
	} else {
		$db->errormsg('Ошибка сборки модулей. <b>Не найден модуль с именем '.'modules/'.$name['name']);
	}
}
##[k] выбор и наполнение шаблона
if ($easy->page->path){
	$template = ($easy->page->path == 'base') ? $easy->page->text : file_get_contents(TEMPLATES_PATH.$easy->page->path);
	//blia - блоки
	unset($vars);
	preg_match_all('/{\*([A-z0-9_]+)\*}/',$template, $vars);
	$blocks_replace = $vars[0];
	$blocks = $db->set_prefix('blocks');
	$pages_block = $db->set_prefix('pages_block');
	$template_id = $easy->page->template;
	$query = "
		SELECT 
		$blocks.content, $pages_block.block_name FROM $pages_block 
		JOIN $blocks ON $blocks.id = $pages_block.block 
		WHERE $pages_block.template_id = $template_id
	";
	$blocks = $db->query($query);
	if ($blocks != 0)
		foreach($blocks as $block) $b_replace[$block->block_name] = $block->content;

	if (is_array($b_replace))
		foreach ($b_replace as $key=>$value) $template = rep('{*'.$key.'*}', $value, $template);
	if (is_array($replace))
		foreach ($replace as $key=>$value) $template = rep('{'.$key.'}', $value, $template);
	foreach($blocks_replace as $rep)
		$template = str_replace($rep,'',$template);
	##[k] формирование общего вывода и вывода счетчика производительности
	$time_end = getmicrotime();
	$time = $time_end - $time_start;
	##[k] выводим время формирования страницы перед последним закрывающим тегом body (без учета последней подстановки конечно)
	//$template = substr_replace($template, '<script type="text\javascript" src="/core/inc/js/utils.js"></script><!-- generation of page: '.$time.' seconds --></body>', strripos($template,'</body>'), 7);
	echo $template;
}
?>