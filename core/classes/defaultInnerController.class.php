<?php

/**
* Главная страница
*/
class defaultInnerController extends controller
{

	public function init(){}

	public function index(){
		$this->newsblock();
		$this->oldbrowser();
		$this->render();
	}	
}


?>