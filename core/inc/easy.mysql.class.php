<?php
/**
 * @class EasyMysql
 * @version 3
 * @author kodji
 * @copyright EasyLabs
 */

class EasyMysql {

//[k] resource identifier	
var $cdb;

//[k] table prefix
var $table_prefix;

//[k] database name
var $databasename;

//[k] error message container
var $error;


function __construct(){
    require $_SERVER['DOCUMENT_ROOT'].'/core/etc/conf.php';
    $this->connect($MySQLServer,$MySQLUser,$MySQLPas,$MySQLbd_name);
    mysql_query('SET NAMES utf8;', $this->cdb);
    $this->databasename = $MySQLbd_name;
    $this->table_prefix = $table_prefix;
}

//[k] setter for error message
function errormsg ($msg, $mysqlerror = '', $querystr = '',$die = false)
	{
		//[k] current module
		$_SESSION['current_module'] = (!isset($_SESSION['current_module']))? '<b>unknown</b>' : $_SESSION['current_module'];
		
		//[k] error array
		$this->error['iserror'] = true;
		$this->error['modulename'] = $_SESSION['current_module'];
		$this->error['filename'] = __FILE__;
		$this->error['line'] = __LINE__;
		$this->error['message'] = $msg;
		$this->error['mysqlerror'] = $mysqlerror;
		$this->error['query'] = $querystr;
		
		
		if ($die) {
			$err = '<h1 style="color:red;">'.$this->error['message'].'</h1>';
			unset($this->error['message']);
			foreach ($this->error as $key => $value) {
				$err .= '<p><b>'.$key.':</b> '.$value.'</p>';
			}
			die('<html><head><title>Error.</title></head><body>'.$err.'</body></html>');	
		} 
	}

//[k] error cleaner
function cleanerr () {
	$this->error['iserror'] = false;
}
//[k] logger method
//[k] $table - table name
//[k] $rowid - record id
//[k] operation - name of operation
//[k] $query - additional info about operation
//[k] other parts of log record will be filled automatically
function log($table,$rowid = 0, $operation = '', $query = '') {
	$ins['uid'] = $_SESSION['user_id'];
	$ins['gid'] = $_SESSION['group_id'];
	$ins['table'] = $table;
	$ins['rid'] = $rowid;
	$ins['operation'] = $operation;
	$ins['ip'] = $_SERVER['REMOTE_ADDR'];
	$ins['agent'] = $_SERVER['HTTP_USER_AGENT'];
	$ins['query'] = $query;
	
	$cols = '';
	$values = '';
	foreach($ins as $key=>$value){
		$cols.='`'.$this->prepare($key).'`,';
		$values.='\''.$this->prepare($value).'\',';
	}
	$query='INSERT INTO '.$this->table_prefix.'log ('.$cols.') VALUES ('.$values.')';
	$query = str_replace(',)', ')', $query);
    if(!$result = mysql_query($query, $this->cdb))
    $this->errormsg ("Ошибка запроса",mysql_error(),$query);	
}
	
##[k] Функция установки соединения с бд и выбора таблицы
function connect($server, $user, $pas, $db)
	{
		
		@$this->cdb = mysql_connect($server, $user, $pas);
		
		if (!$this->cdb) $this->errormsg ('Не удалось соединиться с сервером MySQL',mysql_error(),null,true);
		
        if (!mysql_select_db($db, $this->cdb)) $this->errormsg('Ошибка выбора базы! Проверьте настройки и наличие базы '.$db, mysql_error(),null,true);
	}

##[k] произвольный запрос к базе.
/*
ВНИМАНИЕ!
Не защищен от инъекции и не устанавливает права доступа.
Ответственность за использование этого метода напрямую
полностью лежит на разработчике. 

Метод по умолчанию работает в режиме возврата объекта - object, если нужен возврат массива - дать вторым параметром "array"
*/

function query ($query, $mode='object')
	{
        $this->cleanerr();
        if(!$result = mysql_query($query, $this->cdb))
        $this->errormsg ("Ошибка запроса",mysql_error(),$query);
        
        //[k] отлавливает события кроме вставки, апдейта и удаления и возвращает для них результат работы метода 0
        /*
        	@todo с какого хуя оно возвращает 0? Переделать на вменяемый ответ, но сначала проверить, нет ли где построенных запросов с учетом 0 ответа 
        	а в принципе все равно, конечно. ничего страшного не произойдет и так
		*/      
		if (
			//(strpos($query,'INSERT') !== false) or
			(strpos($query,'UPDATE') !== false) or
			(strpos($query,'DELETE') !== false)
		)
        {
            return true;
        }
        else if (strpos($query,'INSERT') !== false) {
        	//[k] инсерт возвращает айдишник вставленной записи, но только в случае если айдишник сгенерирован автоматически. Если сгенерировано вручную или вставка в таблицу без автоинкремента, то будет возвращен 0
        	return mysql_insert_id($this->cdb); 
        }
        //[k] парсит результат в запрошенном виде для выборки
        else
        {
			if($mode == 'object'){
				while (@$row = mysql_fetch_object($result))
				{
					$arr[]=$row;
				}
				if (!isset($arr))
				{
					$arr = 0;
				}
				return $arr;
			}
			if($mode == 'array'){
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
				{
					$arr[]=$row;
				}
				if (!isset($arr))
				{
					$arr = 0;
				}
				return $arr;
			}
    	}
    }

##[k] Функция выбора записей по условию наличия переменной в запросе с поддержкой группы пользователя и с защитой от инъекции
// $table - таблица БД
// $nfields - имена полей БД
// $fields - фильтр записей БД
// $order - имя поля упорядочивания
// $desc - упорядочить, true - по убыванию, false (null) - по возрастанию
// $limit - количество записей удовлетворяющих фильтру
// $nlim - номер начальной записи (с 0)
function select_all ($table, $nfields='', $fields='', $order='', $desc=false, $limit=0, $nlim=0, $mode='object')
	{
        $table = $this->set_prefix($table);
		$query= 'SELECT ';
		if (!strlen($nfields)) $nfields = '*'; $query.=$nfields." FROM ".$table;
		if (strlen($fields))
        $query.= " WHERE ".$this->prepare($fields);
        $q = $this->query('SELECT mode FROM '.$this->table_prefix.'sec_conf WHERE group_id='.$_SESSION['group_id'].' AND tablename="'.$this->del_prefix($table).'"');
        if ($q!=0) $q = $q[0]->mode;
		if ($q!=0 && $q!=9) $query .= (strlen($fields)) ? " AND " : " WHERE ";
		switch ($q)
		{
		case 5:
		case 6:
		case 8: $query .="(group_id >= ".$_SESSION['group_id']." OR user_id=".$_SESSION['user_id'].")"; break;
        case 2:
		case 4: $query .="user_id=".$_SESSION['user_id']; break;
		}
		if (strlen($order)) { $query.= " ORDER BY ".$order;	if ($desc) $query .= ' DESC';}
		if ($limit) {
					$query.= ' LIMIT ';
					if ($nlim) $query .= "$nlim, ";
      					$query.= $limit;
   			      }
		
		$result = $this->query($query, $mode);
		if ($site_debug == true) $this->log($table,null,'SELECT_ALL',$query);
		return ($this->error['iserror']) ? false : $result;
	}

##[k] выборка с поддержкой удаления в корзину и группы пользователя
function select($table, $nfields='', $fields='', $order='', $desc=false, $limit=0, $nlim=0, $mode='object')
	{
        $table = $this->set_prefix($table);
        $query= 'SELECT ';
		if (!strlen($nfields)) $nfields = '*'; $query.=$nfields." FROM ".$table;
		if (strlen($fields)) $query.= " WHERE ".$fields;
        $q = $this->query('SELECT mode FROM '.$this->table_prefix.'sec_conf WHERE group_id='.$_SESSION['group_id'].' AND tablename="'.$this->del_prefix($table).'"');
        if ($q!=0) $q = $q[0]->mode;
		if ($q!=0 && $q!=9) $query .= (strlen($fields)) ? " AND " : " WHERE ";
        switch ($q)
		{
		case 5:
		case 6:
		case 8: $query .="(group_id >= ".$_SESSION['group_id']." OR user_id=".$_SESSION['user_id'].")"; break;
        case 2:
		case 4: $query .="user_id=".$_SESSION['user_id']; break;
		}
        if ($this->isset_deleted($table))
		{
        $query .= (strpos($query,' AND ')) ? ' AND' : (strpos($query,' WHERE')) ? ' AND' : ' WHERE';
		$query .= ' (deleted IS NULL or deleted = "" or deleted = " ")' ;
		}
		if (strlen($order)) { $query.= " ORDER BY "."`".$order."`";	if ($desc) $query .= ' DESC';}
		if ($limit) {
					$query.= ' LIMIT ';
					if ($nlim) $query .= "$nlim, ";
      					$query.= $limit;
   			      }
		$result = $this->query($query, $mode);
		
		//[k] селекты будут логироваться только если включен дебаг
		if ($site_debug == true)	$this->log($table,null,'SELECT',$query);
		return ($this->error['iserror']) ? false : $result;
	}

//[k] 
function selectRow($table, $nfields='', $fields='', $order='', $desc=false, $limit=0, $nlim=0, $mode='object') {
	$result = $this->select($table, $nfields, $fields, $order, $desc, $limit, $nlim, $mode);
	return ($this->error['iserror']) ? false : $result[0]; 
}
//blia - выбираем по id 
function selectById($table, $id) {
	$result = $this->selectRow($table,'*','id='.$id);
	return $result; 
}
##[k] определение количества записей в таблице
function count ($table, $fields='')
	{
        $table = $this->set_prefix($table);
        $query="select * from ".$table;
		if (strlen($fields)) $query.=" where ".$fields;
		$result = $this->query($query);
		if ($this->error['iserror']) return false;
        $result = ($result != 0) ? $result=count($result) : 0;
		return $result;
	}

##[k] 
function insert ($table,$set_array)
	{
        $table = $this->set_prefix($table);
        $i=0;
		$cols = '';
		$values = '';
		foreach($set_array as $key=>$value){
			$cols.='`'.$this->prepare($key).'`,';
			$values.='\''.$this->prepare($value).'\',';
		}
		$query='INSERT INTO '.$table.'('.$cols.') VALUES ('.$values.')';
		$query = str_replace(',)', ')', $query);
        $result=$this->query($query);
       //[k]@todo выводить в лог номер обрабатываемой записи
		$this->log($table,$result,'INSERT_AUTO',$query);        
		return ($this->error['iserror']) ? false : $result;
		
        /* //[k] устаревший код оставил на всякий случай. вдруг кому пригодится. если через полгода (30 сентября 2010) он никому не пригодится, можно удалить
        $table = $this->set_prefix($table);
        $valueset = '';
        $i=0;
		##[k] прогоним входной массив, установив системные поля, если вдруг есть
        while ($element = each($set_array))
        {
            if ($i++ != 0) $valueset.=', ';
            if ($element[0]=='create_date' || $element[0]=='modify_date') $valueset .= "'".date("Y-m-d H:i:s")."'";
            elseif ($element[0] == 'modified_by' || $element[0] == 'created_by' || ($element[0] == 'user_id' && $table != 't_users')) $valueset .= $_SESSION['user_id'];
            //elseif (trim($element[1])=='') $valueset .= "null";
            else $valueset .= "'".$this->prepare($element[1])."'";
        }
        $result=$this->query('INSERT INTO '.$table.' VALUES ('.$valueset.')');
		$this->log($table,null,'INSERT',$valueset);        
		return $result;
		*/
	}
##[k] синоним, оставлен в целях совместимости
function insert_auto ($table,$set_array)
	{
		$this->insert($table,$set_array);
	}

##[k] обновление базы
function update ($table,$set_array,$fields='')
	{
		if (is_object($set_array)) {
			foreach ($set_array as $key => $value) {
				$tmp[$key] = $value;
			}
			$set_array = $tmp;
		}
		if ($fields === '') {
			$fields = 'id ='.$set_array['id'];
			unset($set_array['id']);
		}
        $table=$this->set_prefix($table);
        $set = '';
        $i=0;
##[k] прогоним входной массив, исключив системные поля
        while ($element = each($set_array))
        {
            if ($i++ != 0 && $element[0]!='modify_date' && $element[0] != 'modified_by' && (trim($element[1])!='' || $element[1] === ''))
            $valueset .= ', ';
            if ($element[0]!='modify_date' && $element[0] != 'modified_by' && (trim($element[1])!='' || $element[1] === ''))
            $valueset .= "`".$element[0]."`='".$this->prepare($element[1])."'";
        }
##[k] проверим поддержку полей и если есть вставим значения modify_date и modified_by
        if ($this->isset_fields($table))
        {
           $valueset .= ", modify_date='".date("Y-m-d H:i:s")."', modified_by = '".$_SESSION['user_id']."'";
        }

		$result=$this->query('UPDATE '.$table.' SET '.$valueset.' WHERE '.$fields);
		$this->log($table,$fields,'UPDATE',$valueset);        
		return ($this->error['iserror']) ? false : $result;
	}

##[k] удаление записи из базы с поддержкой безопасного удаления
function delete ($table, $fields)
	{
##[k] если есть поддержка безопасного удаления
        $table=$this->set_prefix($table);
		if ($this->isset_deleted($table) == 1)
		{
            $set_array['deleted'] = 'y';
			$result = $this->update($table,$set_array,$fields);
			$this->log($table,$fields,'UPDATE_BUSKET',$query);
			return ($this->error['iserror']) ? false : $result;
        }
		else {
			$this->errormsg('Метод удаления в корзину для таблицы '.$table.' не поддерживается');			
			return false;
		}
	}

##[k] функция безусловного удаления
function cer_delete ($table, $fields)
	{
        $table=$this->set_prefix($table);
        $result=$this->query("DELETE FROM ".$table. " WHERE ".$fields);
		$this->log($table,$fields,'DELETE',$query);        
		return ($this->error['iserror']) ? false : $result;
	}

##[k] функция проверки наличия поддержки безопасного удаления для таблицы
function isset_deleted($table)
	{
##[k] проверим наличие поля deleted в таблице
	$q = $this->query("desc ".$this->set_prefix($table));
	foreach ($q as $element) if ($element->Field == 'deleted') return 1;
	}

##[k] функция проверки наличия полей created_by create_date modified_by & modify_date
function isset_fields($table)
	{
##[k] проверим наличие поля create_date в таблице
	$q = $this->query("desc ".$this->set_prefix($table));
	foreach ($q as $element) if ($element->Field == 'create_date') return 1;
	}


##[k] функция проверки имени таблицы и наличие у нее префикса и добавление префикса при необходимости
function set_prefix($table)
	{
		if (strpos($table,$this->table_prefix) !== 0) $table = $this->table_prefix.$table;
		return $table;
	}
##[k] функция удаления префикса из имени таблицы
function del_prefix($table)
    {
		if (strpos($table,$this->table_prefix) === 0) $table = substr($table,strlen($this->table_prefix));
		return $table;
    }

##[k] функция формирования списка тегов option для тега select
function gen_options($table,$value,$description,$selected=0,$additional='')
{
    $table = $this->set_prefix($table);
    $result = ($selected==0) ? '<option selected="selected">выбрать</option>': '';
    $res = (strlen($additional)==0) ? $this->select($table, $value.', '.$description) : $this->select($table, $value.', '.$description, $additional);
    if ($res != 0) foreach ($res as $element)
    {
    $result .= '<option value="'.$element->$value.'"';
    if ($element->$value == $selected) $result .= ' selected="selected"';
    $result .= '>'.$element->$description.'</option>';
    }
    return $result;
}
##[k] функция для защиты от sql инъекций
function prepare($value)
{
    // если magic_quotes_gpc включена - используем stripslashes
    if (get_magic_quotes_gpc()) {
    $value = stripslashes($value);
    }
    // Если переменная - число, то экранировать её не нужно
    // если нет - то окружим её кавычками, и экранируем
    if (!is_numeric($value)) {
        $value = mysql_real_escape_string($value, $this->cdb);
    }
    return $value;
}

function __destruct()
	{
		if ($this->cdb) mysql_close($this->cdb);
	}
}
?>
