<?php

/**
* Главная страница
*/
class indexController extends controller
{

	public function init(){}

	public function index(){
		$this->oldbrowser();		
		$this->newsblock();		
		$this->products_block();	
		$this->render();
	}	
}


?>