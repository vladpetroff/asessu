<?php

/**
* Главная страница
*/
class catalogController extends controller
{
	public $table;
	public function init(){
		$this->table = 'catalog';
	}

	public function index(){
 		wr('CATALOG_CONTENT','<div id="content" class="grid_15"><div class="h-inner catalog-main grid_15"><h3>Каталог товаров:</h3>');
 		$cats = $this->db->select($this->table,'*','parent_id=0','order');
 		foreach($cats as $el) {	
 			//[k] загрузим картинку категории каталога
			$imgsrc = $this->getImg($el->id);
 			//[k] описание каталога	
 			$desc = ($el->text == '')? '':$el->text;
 			//[k] выберем подкатегории, если есть
 			$subcats= $this->db->select($this->table,'name,link','parent_id='.$el->id.' and info_id = 0','order');
 			$subcatss = '';
 			if (!empty($subcats)) {
 				foreach($subcats as $subcat) {
 					$subcatss .= '<a href="/catalog/category/'.$subcat->link.'">'.$subcat->name.'</a>';
 				}
 			}
 			else {

 			}
			wr('CATALOG_CONTENT','<div class="cat-pic grid_4 alpha"><img src="'.$imgsrc.'" width="218" height="161" alt="'.$imgsrc.'"/></div><div class="cat-text b-menu grid_11 omega"><h4><a href="/catalog/category/'.$el->link.'">'.$el->name.'</a></h4>'.$desc.$subcatss.'</div><div class="clear"></div>');
 		}

 		wr($this->table,'</div></div> ');
		$this->simpleRender();
	}
	
	public function category(){
		$submenu = '<ul>';
		$catcontent = '';
		//[k] загрузим информацию о всех категориях первого уровня
		$cats = $this->db->select($this->table,'link,name,id','parent_id = 0 and info_id = 0','order');
		foreach ($cats as $el) {
			//[k] если категория не является текущей запрошенной	
			if ($el->link != $this->easy->params[2]) {
				$submenu .= '<li><a href="/catalog/category/'.$el->link.'">'.$el->name.'</a>';
				//[k] проверим наличие подкатегорий
				$subcats = $this->db->select($this->table,'link,name,id','info_id=0 and parent_id='.$el->id,'order');
				if ($subcats != 0) {
					$submenu .= '<ul>';
					foreach ($subcats as $subcat){
						//[k]  если подкатегория не является текущей запрошенной	
						if ($subcat->link != $this->easy->params[2]){
							$submenu .= '<li><a href="/catalog/category/'.$subcat->link.'">'.$subcat->name.'</a></li>';						
						}
						else {
							$submenu .= '<li><a href="#" class="subsel">'.$subcat->name.'</a></li>';
							//[k] вывести товары 
							$catcontent .= $this->getHeaders($el,$subcat);
							$catcontent .= $this->getSubcat($subcat);
						}

					}
					$submenu .= '</ul>';
				}			
				$submenu .= '</li>';
			}
			else {
				$submenu .= '<li><a href="#" class="sel">'.$el->name.'</a>';
				//[k] проверим наличие подкатегорий
				$subcats = $this->db->select($this->table,'link,name,id','parent_id='.$el->id,'order');
				if ($subcats != 0) {
					$submenu .= '<ul>';
					foreach ($subcats as $subcat){
						$submenu .= '<li><a href="/catalog/category/'.$subcat->link.'">'.$subcat->name.'</a></li>';						
					}
					$submenu .= '</ul>';
				}			
				$submenu .= '</li>';				
				$catcontent .= $this->getHeaders($el);
				$catcontent .= $this->getCat($el);
			}
		}
		$submenu .= '</ul>';

		wr('CATALOG_CONTENT','<div id="content" class="grid_15"><div class="h-inner catalog grid_15"><div class="b-menu b-sidemenu grid_3 alpha">'.$submenu.'</div><div class="h-catalog grid_12 omega">'.$catcontent.'</div></div>');
		
		$this->simpleRender();
	}

	//[k]  возвращает html заголовочной части категории или подкатегории	
	private function getHeaders($cat,$subcat = null) {
		return ($subcat) ? '<h3>'.$subcat->name.'</h3><div class="menu-cat"><a href="/catalog.html">Каталог товаров</a><a href="/catalog/category/'.$cat->link.'">'.$cat->name.'</a></div>':'<h3>'.$cat->name.'</h3><div class="menu-cat"><a href="/catalog.html">Каталог товаров</a></div>';
	}
	
	//[k]  возвращает html категории с товарами и подкатегориями	
	private function getCat ($cat) {
		$cont = '';

		//[k] определим, есть ли в запрошенной категории товары
		$goods = $this->db->select($this->table,'*','info_id = 1 and parent_id ='.$cat->id,'name',true); 
		if ($goods != 0) {
			//[k] товары есть, показываем 
			$cont .= $this->getGoods($goods,$cat);
		}

		//[k] проверим наличие подкаталогов	
		$subcats = $this->db->select($this->table,'id,link,name','info_id = 0 and parent_id ='.$cat->id,'order');
		if ($subcats != 0) {
			//[k] есть подкаталоги, для каждого выводим товары
			foreach ($subcats as $subcat) {
				$goods = $this->db->select($this->table,'*','info_id = 1 and parent_id ='.$subcat->id,'name',true); 
				if ($goods != 0) {
					//[k] товары есть, показываем 
					$cont .= $this->getGoods($goods,$subcat);
				}
			}
		}

		//[k]  пусто	
		if ($subcats == 0 && $goods == 0) {
			$cont = '<div class="grid_3 prefix_1 alpha"><h5>Приносим свои извинения.</h5><p>В этой категории пока что не размещены товары</p></div>';
		}

		return $cont;
	}	

	//[k] возвращает html подкатегории c товарами
	private function getSubcat ($cat) {
		//[k] определим, есть ли в запрошенной категории товары
		$goods = $this->db->select($this->table,'*','info_id = 1 and parent_id ='.$cat->id,'name',true); 
		if ($goods != 0) {
			//[k] товары есть, показываем 
			return $this->getGoods($goods,$cat);
		}
		else {
			return '<div class="grid_3 prefix_1 alpha"><h5>Приносим свои извинения.</h5><p>В этой категории пока что не размещены товары</p></div>';
		}
	}	

	//[k] возвращает html товаров запрошенной категории	
	private function getGoods($goods,$cat) {
		$g = '';
		foreach ($goods as $good) {
			$goodid = $cat->link.'_'.$good->link;
			$imgsrc = $this->getImg($good->id);
			$g .= '<div class="prod grid_3 prefix_1 alpha"><h5><a href="#'.$goodid.'" rel="facebox">'.$good->name.'</a></h5><a href="/catalog/category/'.$cat->link.'">'.$cat->name.'</a><a href="#'.$goodid.'" rel="facebox"><img src="'.$imgsrc.'" width="160" height="160" alt="'.$cat->name.' '.$good->name.'"/></a></div><div class="fb" id="'.$goodid.'" style="display:none;"><img src="'.$imgsrc.'" width="276" height="276" alt="'.$cat->name.' '.$good->name.'"/><h5>'.$good->name.'</h5><p>'.$cat->name.'</p>'.$good->text.'</div>';			
		}
		return $g;		
	}

	//[k]  возвращает путь к картинке товара с запрошенным id	
	private function getImg ($id) {
		$img = $this->db->select('links','cid','parent="catalog" and pid='.$id);
		if ($img != 0) {
			$imgsrc = $this->db->select('images','filename','id='.$img[0]->cid);
			//[k] сформируем имя файла
			if ( $imgsrc != 0 ) $imgsrc = '/media/uploads/orig/'.$imgsrc[0]->filename;
			else $imgsrc = '/templates/site/i/transp.png';
		}			
		else {
			$imgsrc = '/templates/site/i/transp.png';
		}		
		return $imgsrc;
	} 
}


?>