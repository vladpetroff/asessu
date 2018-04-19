<?php

/**
* Core Easy Class
*/
class easy 
{
	public $uid;
	public $gid;
	public $db = false;
	public $page;
	public $params;
	
	function __construct($arg)
	{
		foreach ($arg as $key => $value) {
			$this->$key = $value;
		}
	}
	
	
	public static function isDeveloper(){
		return ($_SESSION['group_id'] === 1)?false:true;
	}
	
	public static function isAdmin(){
		return ($_SESSION['group_id'] === 2)?false:true;
	}
	
	public static function isModerator(){
		return ($_SESSION['group_id'] === 3)?false:true;
	}

	public static function isUser(){
		return ($_SESSION['group_id'] === 4)?false:true;
	}

	public static function isGuest(){
		return ($_SESSION['group_id'] === 5)?false:true;
	}
	
	public static function developer(){
		return 1;
	}

	public static function admin(){
		return 2;
	}

	public static function moderator(){
		return 3;
	}

	public static function user(){
		return 4;
	}
	
	public static function guest(){
		return 5;
	}	
	
	public function gid($gid = false){
		if($gid){
			$_SESSION['group_id'] = $gid;
			$this->gid = $gid;
		}
		return $this->gid;
	}
	
	public function uid($uid = false){
		if($uid){
			$_SESSION['user_id'] = $uid;
			$this->uid = $uid;
		}
		return $this->uid;
	}
	
	public function db(){
		return $this->db;
	}
	
	public function allow($gid){

		if ($this->gid() != $gid) {
			redirect(__USER_LOGIN__);
		}
	}
	
	public function inContext(){
		if ($this->db() === false) {
			header("HTTP/1.0 404 Not Found");
			header("Location: /content/404.html");
		}
	}

	public function err404(){
		header("HTTP/1.0 404 Not Found");
		header("Location: /content/404.html");
	}
}


?>