<?php

/**
* Site router
*/
class router
{
	public $easy;
	
	function __construct($easy)
	{
		$this->easy = $easy;
		$classname = (file_exists(CONTROLLERS_PATH.$easy->page->link.'Controller.class.php'))? $easy->page->link.'Controller' :'defaultInnerController';
		$module = new $classname();
		$module->setEasy($easy);
		$module->setDb($easy->db);
		$module->init();
		if (isset($this->easy->params[1])) {
			$method = trim($this->easy->params[1]);
			if (method_exists($module,$method)) {
				$module->$method();
			}
			else {
				echo(function_exists($classname.'::'.$method));
				//$this->easy->err404();
			}
		} else {
			$module->index();
		}
	}
	
}

?>