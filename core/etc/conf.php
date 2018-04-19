<?php

$site_debug = false;

define('__USER_LOGIN__','u-login.html');

define('WEB_ROOT',$_SERVER['DOCUMENT_ROOT']);
define('MEDIA',WEB_ROOT.'/media/');
define('TEMPLATES_PATH',WEB_ROOT.'/templates/');
define('CORE',WEB_ROOT.'/core');
define('MODULES_PATH',CORE.'/modules/');
define('CONTROLLERS_PATH',CORE.'/classes/');

function __autoload($class_name) {
    require_once 'classes/' . $class_name . '.class.php';
}

$MySQLServer = "localhost";
$MySQLUser = "asessu_user";
$MySQLPas = "!2vladik";
$MySQLbd_name = "asessu_db";
$table_prefix = "t_";
ini_set('display_errors', 'On');
error_reporting(E_ALL^E_NOTICE);
ini_set('date.timezone','Europe/Moscow');

?>
