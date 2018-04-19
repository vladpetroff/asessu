<?php

##[k] счетчик производительности
function getmicrotime()
	{
    	list($usec, $sec) = explode(" ",microtime());
    	return ((float)$usec + (float)$sec);
    }

##[k] функция очистки от мусора
function clear($value)
	{
        return htmlspecialchars($value);;
	}

##[k] stripslashes delete
function sl($value)
	{
		return stripslashes($value);;
	}

##[k] stripslashes add
function asl($value)
	{
		return addslashes($value);;
	}

##[k] для сокращения конструкции .= для массива $replace
function wr($in,$what)
	{
	global $replace;
	$replace[$in] .= $what;
	}

##[k] для сокращения конструкции = для массива $replace
function owr($in,$what)
  {
  global $replace;
  $replace[$in] = $what;
  }

##[k] запись в $replace['CONTENT']
function wc($what)
	{
		wr('CONTENT',$what);
	}

##[k] запись в $replace['debug']
function wd($what)
	{
		wr('debug',$what);
	}

##[k] замена
function rep($that,$than,$where)
	{
		return str_replace($that,$than,$where);
	}

##[k] функция для удаления из массива POST  x  и y значений при клике на графическую кнопку.
function delxy ($arr)
    {
    foreach ($arr as $key=>$value) if ($key != 'x' && $key != 'y') $ret[$key]=$value;
    return $ret;
    }
##[k] функция для удаления переносов строк
function rembr ($value)
    {
    return str_replace(array("\r","\n"),'',$value);
    }
##[k] функция транслитерации имен
function translit($str)
{
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '',  'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

        'А' => 'a',   'Б' => 'b',   'В' => 'v',
        'Г' => 'g',   'Д' => 'd',   'Е' => 'e',
        'Ё' => 'e',   'Ж' => 'zh',  'З' => 'z',
        'И' => 'i',   'Й' => 'y',   'К' => 'k',
        'Л' => 'l',   'М' => 'm',   'Н' => 'n',
        'О' => 'o',   'П' => 'p',   'Р' => 'r',
        'С' => 's',   'Т' => 't',   'У' => 'u',
        'Ф' => 'f',   'Х' => 'h',   'Ц' => 'c',
        'Ч' => 'ch',  'Ш' => 'sh',  'Щ' => 'sch',
        'Ь' => '',  'Ы' => 'y',   'Ъ' => '',
        'Э' => 'e',   'Ю' => 'yu',  'Я' => 'ya',
        ' ' => '_', '-' => '_'
    );
    $str =  strtr($str, $converter);
    $str = preg_replace('~[^a-z0-9_.]+~', '', $str);
    $str = trim ($str,'_');
    return $str;
}
## [tm] Функция перекодировки из UTF-8 в Win-CP1251
function utf2win($str)
{
/*$str=strtr($str,array("Р°"=>"а","Р±"=>"б","РІ"=>"в","Рі"=>"г","Рґ"=>"д","Рµ"=>"е","С‘"=>"ё",
"Р¶"=>"ж","Р·"=>"з",
"Рё"=>"и","Р№"=>"й","Рє"=>"к","Р»"=>"л","Рј"=>"м","РЅ"=>"н","Рѕ"=>"о","Рї"=>"п",
"СЂ"=>"р","СЃ"=>"с","С‚"=>"т","Сѓ"=>"у","С„"=>"ф","С…"=>"х","С†"=>"ц",
"С‡"=>"ч","С€"=>"ш","С‰"=>"щ","СЉ"=>"ъ","С‹"=>"ы","СЊ"=>"ь",
"СЌ"=>"э","СЋ"=>"ю","СЏ"=>"я",
"Рђ"=>"А","Р‘"=>"Б","Р’"=>"В","Р“"=>"Г","Р”"=>"Д",
"Р•"=>"Е","РЃ"=>"Ё","Р–"=>"Ж","Р—"=>"З","Р?"=>"И","Р™"=>"Й","Рљ"=>"К","Р›"=>"Л",
"Рњ"=>"М","Рќ"=>"Н","Рћ"=>"О","Рџ"=>"П","Р "=>"Р",
"РЎ"=>"С","Рў"=>"Т","РЈ"=>"У","Р¤"=>"Ф","РҐ"=>"Х",
"Р¦"=>"Ц","Р§"=>"Ч","РЁ"=>"Ш","Р©"=>"Щ","РЄ"=>"Ъ","Р«"=>"Ы",
"Р¬"=>"Ь","Р­"=>"Э","Р®"=>"Ю","РЇ"=>"Я"));*/
return iconv('utf-8', 'windows-1251', $str);
}

## [tm] Функция перекодировки из Win-CP1251 в UTF-8
function win2utf($s)    {
   /*for($i=0, $m=strlen($s); $i<$m; $i++)    {
       $c=ord($s[$i]);
       if ($c<=127) {$t.=chr($c); continue; }
       if ($c>=192 && $c<=207)    {$t.=chr(208).chr($c-48); continue; }
       if ($c>=208 && $c<=239) {$t.=chr(208).chr($c-48); continue; }
       if ($c>=240 && $c<=255) {$t.=chr(209).chr($c-112); continue; }
       if ($c==184) { $t.=chr(209).chr(209); continue; };
            if ($c==168) { $t.=chr(208).chr(129);  continue; };
            if ($c==184) { $t.=chr(209).chr(145); continue; }; #ё
            if ($c==168) { $t.=chr(208).chr(129); continue; }; #Ё
            if ($c==179) { $t.=chr(209).chr(150); continue; }; #і
            if ($c==178) { $t.=chr(208).chr(134); continue; }; #І
            if ($c==191) { $t.=chr(209).chr(151); continue; }; #ї
            if ($c==175) { $t.=chr(208).chr(135); continue; }; #ї
            if ($c==186) { $t.=chr(209).chr(148); continue; }; #є
            if ($c==170) { $t.=chr(208).chr(132); continue; }; #Є
            if ($c==180) { $t.=chr(210).chr(145); continue; }; #ґ
            if ($c==165) { $t.=chr(210).chr(144); continue; }; #Ґ
            if ($c==184) { $t.=chr(209).chr(145); continue; }; #Ґ           
   }*/
   return iconv('utf-8', 'windows-1251', $s);
}
## [tm] Функция проверки правильности строки UTF-8
function utfIsCorrect($str){
	if(strlen($str)==0)return true;
	return (preg_match('/^.{1}/us',$str)==1);
}

function php_multisort($data,$keys){
  // List As Columns
  foreach ($data as $key => $row) {
    foreach ($keys as $k){
      $cols[$k['key']][$key] = $row[$k['key']];
    }
  }
  // List original keys
  $idkeys=array_keys($data);
  // Sort Expression
  $i=0;
  foreach ($keys as $k){
    if($i>0){$sort.=',';}
    $sort.='$cols['.$k['key'].']';
    if($k['sort']){$sort.=',SORT_'.strtoupper($k['sort']);}
    if($k['type']){$sort.=',SORT_'.strtoupper($k['type']);}
    $i++;
  }
  $sort.=',$idkeys';
  // Sort Funct
  $sort='array_multisort('.$sort.');';
  eval($sort);
  // Rebuild Full Array
  foreach($idkeys as $idkey){
    $result[$idkey]=$data[$idkey];
  }
  return $results;
}

function GetFilesize($filename){
    $fsize = filesize($filename);
    if (floor($fsize/1048576)!=0){
        $fsize = round($fsize/1048576).' MB';
    }
    else if(floor($fsize/1024)!=0){
        $fsize = round($fsize/1024).' KB';
    }
    else{
        $fsize = $fsize.' B';
    }
    return $fsize;
}

function GetSettings($name){
    global $db;
    $settings = $db->select('config','value','name="'.$name.'"');
    if(!empty($settings)){
            $settings=$settings[0];
    }
    $settings = unserialize($settings->value);

    return $settings;
}
function dump($arr){
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

function date2db ($date){
	return substr($date,6,4).'-'.substr($date,3,2).'-'.substr($date,0,2);
}

function db2date ($date){
	return substr($date,8,2).'.'.substr($date,5,2).'.'.substr($date,0,4);
}

function db2rudate ($date){
	$arr['01'] = 'января';
	$arr['02'] = 'февраля';
	$arr['03'] = 'марта';
	$arr['04'] = 'апреля';
	$arr['05'] = 'мая';
	$arr['06'] = 'июня';
	$arr['07'] = 'июля';
	$arr['08'] = 'августа';
	$arr['09'] = 'сентября';
	$arr['10'] = 'октября';
	$arr['11'] = 'ноября';
	$arr['12'] = 'декабря';
	return substr($date,8,2).' '.$arr[substr($date,5,2)].' '.substr($date,0,4);
}

function redirect ($loc){
	header('HTTP/1.1 301 Moved Permanently');
	header("Location: ".$loc);
}

function merge($arr,$arr2){
	foreach ($arr2 as $key => $value) {
		if(is_array($arr))
			$arr[$key] = $value;
		else if(is_object($arr))
			$arr->$key = $value;
		else
			return 0;
	}
	return $arr;
}

//[k] функция проверяет старые версии браузеров ие 
function is_old_ie($user_agent) {
 
  /*$count = 0;
  str_ireplace('msie', '', $user_agent, $count);
  if ($count > 1) {
    return False;
  }*/

  if (stripos($user_agent, 'msie 6') === False and stripos($user_agent, 'msie 7') === False) {
    return False; 
  }
  return True;                                                       
}

// Применять так
//if (is_old_ie($_SERVER['HTTP_USER_AGENT'])) {
//  die('die old browsers, die!');
//}

//[k]  функция для проверки сессии
function cgr($id) {
  return ($_SESSION['group_id'] <= $id) ? true:false;
}

//[k]  пагинатор все страницы
//[k] количество страниц  
function gpages($elcount = 0,$pagesize = 10) {
  return ceil($elcount / $pagesize);
}

//[k] выбор текущей страницы 
function gpage($arr,$page = 1,$pagesize = 10) {
  $pages = sizeof($arr);
  $pgs = gpages($pages,$pagesize);
  if (($page < $pgs) || ($page >$pgs)) {
    return false;
  }
  else {
    $start = $page * $pagesize;
    $end = ( $page + 1 ) * $pagesize;    
    for ( $i = 1; $i < $pages; $i++ ) {
      if ( $i >= $start && $i <= $end ) {
        $ret[] = $arr[$i];        
      }
    }
    return $ret;
  }
}


//[k]  минишаблонизатор
function tpl($tpl, $arr){
  if (is_array($arr))
    foreach ($arr as $key=>$value) $tpl = rep('{'.$key.'}', $value, $tpl);
  return  $tpl;
} 

//[k] быдлоопределялка окончаний
function getEnd($count) {
  switch ($count) {
    case 1 : ;
    case 21 :;
    case 31 :;
    case 41 :;
    case 51 :;
    case 61 : return '';
    case 2 :;
    case 3 :;
    case 4 :;
    case 22 :;
    case 23 :;
    case 24 :;
    case 32 :;
    case 33 :;
    case 34 :;
    case 42 :;
    case 43 :;
    case 44 :;
    case 52 :;
    case 53 :;
    case 54 : return 'а';
    default : return 'ов';
  }
} 

?>