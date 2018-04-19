<?php

/**
* Главная страница
*/
class newsController extends controller
{
	//[k] количество новостей на страницу	
	public $pageCount = 5;
	//[k] имя таблицы с которой работаем	
	public $table = 'news';
	public $pageStart = 1;	

	public function init(){
	}

	public function index(){
		$this->page();
	}

	public function page () {	

		//[k]  стартовая страница	
		$this->pageStart = ($this->easy->params[2]) ? (int)$this->easy->params[2]:$this->pageStart;
		//[k] номер с какой новости по счету стартовать
		$startWith = $this->pageCount * ($this->pageStart - 1);	
		//[k] посчитаем сколько всего элементов
		$counter = $this->db->select($this->table,'count(*) as c');
		$counter = $counter[0]->c;

		//[k] посчитаем сколько всего страниц 
		$pages = ceil($counter / $this->pageCount);

		$paging = '';
		if ($pages > 1) {
			$flag = 0;
			//[k] создадим наполнение для пагинации
			for ($i = 1; $i <= $pages;$i++) {
				if ($flag++ != 0) $paging .= ' / ';
				$paging .= ($i == $this->pageStart)? '<span>'.$i.'</span>':'<a href="/news/page/'.$i.'.html">'.$i.'</a>';
			}
		}
		$paging .= '';

		//[k] выбор и заполнение запрошенной страницы
		$news = $this->db->select('news','date,header,title,link',null,'date',true,$this->pageCount,$startWith);
		if ($news != 0) {
			$newsc = '';
			$c = 0;
			foreach ($news as $new) {
				$newsc .= '<p class="news">'.db2date($new->date).' &mdash; <a href="/news/article/'.$new->link.'.html">'.$new->header.'</a></p><p>'.$new->title.'</p>';
			}
		}

       	wr('PAGE_CONTENT',$newsc);
		wr('PAGE_CONTENT','<div class="clear"></div><div class="pages">'.$paging.'</div>');
		
		$this->oldbrowser();
		//[k] смена шаблона на список новостей	
		$this->simpleRender('ases/newslist.html');		
	} 
	
	public function article() {
		$newsc = '';
		//[k] выбор запрошенной новости
		$news = $this->db->select('news','date,header,text','link = "'.$this->easy->params[2].'"');
		if ($news != 0){
           $newsc .= '<h3>'.$news[0]->header.'</h3>'.'<p class="date">'.db2rudate($news[0]->date).'</p>'.$news[0]->text;
       	}
       	else {
       		$newsc .= '<p>Извините, запрошенная вами новость не найдена. Возможно ссылка изменилась, или новость была удалена.</p>';
       	}

		$newsc .= '<p>← <a href="/news">к списку новостей</a></p>';
       	
       	wr('PAGE_CONTENT',$newsc);

		$this->oldbrowser();       	
       	$this->newsblock();
		$this->simpleRender();
	}	
}


?>