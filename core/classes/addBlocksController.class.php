<?php

/**
* Главная страница - Вход в админку
*/
class addBlocksController extends controller
{

	public function init(){
		$this->subTitle('Текстовые блоки');
	}

	public function index(){
		$blocks  = $this->db->select('add_block','*');
		wr('CONTENT',$this->easylist($blocks,array('description'=>'text'),false));
		$this->render();
	}
		
	public function edit(){
		if (isset($_POST['block'])) {
			$block = $this->db->selectById('add_block',$this->easy->params[2]);
			$this->db->update('add_block',merge($block,$_POST['block']));
			redirect('/content/addBlocks.html');
		}
		$block = $this->db->selectById('add_block',$this->easy->params[2]);
		$this->fillOut($block);
		$this->render('site/add_blocks_edit.html');		
	}
}


?>