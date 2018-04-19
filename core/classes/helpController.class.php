<?php

/**
* Контроллур системы помощи для СУ
*/
class helpController extends controller
{

	public function init(){}

	public function index(){
		wr('CURYEAR',date('Y'));
		wr('HELP_HEADER',$this->easy->page->header);
		wr('HELP_CONTENT',$this->easy->page->html);
		$this->render();
	}	

	public function show(){
		wr('CURYEAR',date('Y'));

		$cur = $this->db->select('pages','*','link = "'.$this->easy->params[2].'"');
		if ($cur[0]) {
			$cur = $cur[0];
		}
		else {
			$cur->html = $cur->header = 'нет содержимого';
		}

		wr('HELP_HEADER',$cur->header);
		wr('HELP_CONTENT',$cur->html);
		$this->render();
	}
		
	public function render (){
		$menu = '';
		$ha = $this->db->select('pages','*','link != "help" and type != "folder" and parent_id=3 and group_id >='.$_SESSION['group_id']);
		if ($ha[0]) {
			foreach ($ha as $el) {
				if ($el->link == $this->easy->params[2]) {
					$menu .= '<li>'.$el->name.'</li>';
				}
				else {
					$menu .= '<li><a href="/help/show/'.$el->link.'.html">'.$el->name.'</a></li>';					
				}
			}
		}
		wr('HELP_MENU',$menu);		
		wr('HELP_TITLE',$this->easy->page->title);
	}


}


?>