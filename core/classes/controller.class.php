<?php
/**
* 
*/
include($_SERVER['DOCUMENT_ROOT'].'/core/classes/PHPMailer/class.phpmailer.php');
include($_SERVER['DOCUMENT_ROOT'].'/core/inc/image.class.php');

class controller 
{
	public $easy;
	public $db;

	public function init(){

	}
	
	public function setEasy($easy){
		$this->easy = $easy;
	}
	
	public function setDb($db){
		$this->db = $db;
	}
	
	public function easy(){
		return $this->easy;
	}
	
	//[k] стандартный рендер страницы 
	public function render($tpl = ''){
		$this->simpleRender($tpl);
		$this->contentRender($tpl);
	}

	## [k] отправка почты без проблем с русскими буквами в отправителе, теме и теле сообщения в любых почтовых клиентах
	function truemail($email, $subject, $body) {

		$headers  = "Content-type: text/html; charset=windows-1251". "\r\n";
		$headers.= "From: =?windows-1251?B?".base64_encode(iconv("UTF-8", "windows-1251", trim(preg_replace('#[\n\r]+#s', '', 'EASY ROBOT '))))."?= <no-reply@".$_SERVER['HTTP_HOST'].">\r\n";
	
		$subject = '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "windows-1251", trim(preg_replace('#[\n\r]+#s', '', $subject))))."?=";
		$body = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/><title>'.$subject.'</title></head><body>'.$body.'</body></html>';
		$body = iconv("UTF-8", "windows-1251", $body); 
		
		// disabled [k]2018-01-07
		//mail($email, $subject, $body, $headers);                              
	}

	//[k] рендер контента и подменю
	public function contentRender($tpl = ''){
		//[k] ====================
		//[k] подменю и контент
		//[k] ==================== 

		/* обработка формы обратной связи поправлено [k] 2015-05-07*/	
		if (
			!empty($_POST) && 
			$_POST['suid'] && 
			$_POST['feedback-company'] && 
			($_POST['feedback-company'] != 'google') // bot with 'google' in the company name field decline because it is spam
		) {
			$email = $this->db->select('config','value','name="email"');
			
			$body = "<p>Имя Фамилия: ".$_POST['feedback_name']."</p>";
			if (!empty($_POST['feedback-company'])) {
				$body .= "<p>Компания: ".$_POST['feedback-company']."</p>";
			} 
			$body .= "<p>Телефон: ".$_POST['feedback_new']."</p>";
			$body .= "<p>Почта: ".$_POST['feedback_email']."</p>";
			$body .= "<p>Сообщение: ".$_POST['feedback_message']."</p>";   
			
			$this->truemail($email[0]->value,"Письмо из формы обратной связи ASES",$body);
			wr('SUCCESS','<span>Ваше сообщение успешно отправлено. <a href="/">Закрыть</a></span>');
		}
		$_SESSION['suid'] = microtime();
		wr('SUID',$_SESSION['suid']);

		//[k] выберем соседей текущей страницы
		$smenu = $this->db->select('pages','name,link,parent_id','link != "news" and parent_id != 1 and parent_id = '.$this->easy->page->parent_id,'pos');
		$sc = 0;
		$smenuc = '';
		if (!empty($smenu) && sizeof($smenu) > 1) {
			$smenuc = '<div id="b-sidemenu"><ul>';
			foreach ($smenu as $el) {
				//[k]  если текущая страница - отобразим её c активным классом
				if ($el->link == $this->easy->page->link) {
					$smenuc .= '<li><a href="/'.$el->link.'.html" class="sel">'.$el->name.'</a></li>';
				}
				//[k]  если сосед 
				else {
					$sc++;
					$smenuc .= '<li><a href="/'.$el->link.'.html">'.$el->name.'</a></li>';
				}
			}
			$smenuc .= '</ul></div><div class="clear"></div>';
		}

		wr('PAGE_CONTENT',$this->easy->page->html);
		wr('SUBMENU',$smenuc);
		wr('FEEDBACK',$this->feedback());

		// [kal1sha] каталог товаров
 		if ($this->easy->page->link == 'katalog_tovarov') {
			wr('LIST',$this->list_products($_POST['product_id']));
			wr('OPTION_PRODUCTS',$this->options_products($_POST['product_id']));
		}
		//[k] смена шаблона если надо 
		if ($tpl != '')	$this->easy->page->path = $tpl;
	}

	public function options_products($product_id) {
		$html = '';
		$result = $this->db->select('catalog','*', 'parent_id=0', 'order', false);	
		foreach($result as $value) {
			if ($product_id == $value->id)
				$html .= '<option value="'.$value->id.'" selected>'.$value->name.'</option>';
			else 
				$html .= '<option value="'.$value->id.'">'.$value->name.'</option>';	
		}
		return $html;		
	}

	// список продуктов
	public function list_products($product_id=0) {
		$html = '';
		if ($product_id)
			$result = $this->db->select('catalog','*', 'id='.$product_id, 'order', false);	
		else
			$result = $this->db->select('catalog','*', 'parent_id=0', 'order', false);				
		foreach($result as $value) {
			$html .= '<h1>'.$value->name.'</h1>';
			$lists = $this->db->select('catalog','*', 'parent_id='.$value->id, 'order', false);	
			foreach($lists as $list) {
				$image_id = $this->db->select('links','cid','parent="catalog" AND child="images" AND pid='.$list->id.'');
				$image = $this->db->select('images', 'filename', 'id='.$image_id[0]->cid.'');
				$html .= '<article>';
                $html .= '<a class="various" href="#inline'.$list->id.'">';
                $html .= '<h2>'.$list->name.'</h2>';
                $html .= '<img src="/media/uploads/cache/900x480xResizeAutoxFFFFFF/'.$image[0]->filename.'" alt="" />';
                $html .= '</a>';
                $html .= '<div style="display: none;">';
				$html .= '<div class="easy_fancybox" id="inline'.$list->id.'" style="width:700px;height:500px;overflow:auto;">';
				$html .= $list->text;
				$html .= '</div>';
				$html .= '</div>';
                $html .= '</article>';
			}
			$html .= '<div class="clear"></div>';
		}
		return $html;
	}

	// [kal1sha] вывод обратной связи
	public function feedback() {
		$result = $this->db->select('forms','content','id=1');		
		$result[0]->content = unserialize($result[0]->content);
		$data =explode("&", $result[0]->content);
		for ($i = 0; $i < count($data); $i += 4) {
			$key = explode("=", $data[$i]);	
			$list_fields['key'][] = urldecode($key[1]);
			$value = explode("=", $data[$i+1]);	
			$list_fields['value'][] = urldecode($value[1]);		
			$type = explode("=", $data[$i+2]);	
			$list_fields['type'][] = $type[1];
			$class = explode("=", $data[$i+3]);	
			$list_fields['class'][] = $class[1];
		}
		$html = '';
		for ($i =0 ; $i < count($list_fields['class']); $i++) {
			$required = '';
			if ($list_fields['value'][$i]!='')
				$required = '<b style="color:#66148c">*</b>';
			else
				$required = '';

			if ($list_fields['type'][$i] == 'text')
				$html .= '<label class="'.$list_fields['class'][$i].'"><span>'.$list_fields['key'][$i].$required.':</span><input type="text" name="'.$list_fields['class'][$i].'" id="'.$list_fields['class'][$i].'" class="'.$list_fields['value'][$i].'"></label>';
			else
				$html .= '<label class="'.$list_fields['class'][$i].'"><span>'.$list_fields['key'][$i].$required.':</span><textarea name="'.$list_fields['class'][$i].'" id="'.$list_fields['class'][$i].'" class="'.$list_fields['value'][$i].'"></textarea></label>';
		}
		return $html;
	}

	//[k] простой рендер страницы (удобно использовать для кастомных страниц)
	public function simpleRender($tpl = ''){
		//[k] ====================
		//[k] стандартные переменные
		//[k] ==================== 

		//[k]  заполнение стандартных переменных
		wr('PAGE_TITLE', $this->easy->page->title);
		wr('PAGE_KEYWORDS', $this->easy->page->keywords);
		wr('PAGE_DESCRIPTION', $this->easy->page->description);
		wr('PAGE_H1', $this->easy->page->h1);
		if ($this->easy->page->header != '') {
			wr('PAGE_HEADER', '<h1>'.$this->easy->page->header.'</h1>');
		}
		else {
			wr('PAGE_HEADER', '');					
		}
		
		wr('PAGE_NAME', $this->easy->page->name);
		wr('PAGE_LINK', $this->easy->page->link);

		
		//[k] ====================
		//[k] главное меню
		//[k] ==================== 
		$mmenu = $this->db->select('pages','id,name,link','parent_id = 1','pos');
		foreach ($mmenu as $el) {
			$active = ($el->link == $this->easy->page->link || $el->id == $this->easy->page->parent_id)? ' class="sel" ':'';
			wr('MMENU','<li><a href="/'.$el->link.'.html" '.$active.'>'.$el->name.'</a></li>');					
		}

		//[k] смена шаблона если надо 
		if ($tpl != '')	$this->easy->page->path = $tpl;
	}
	
	public function fillOut($array){
		foreach ($array as $key => $value) {
			wr($key,$value);
		}
	}

	public function newsblock () {
		//[k] выбор и заполнение важных
		$nc = ($this->easy->page->link == 'index') ? 4:2;
		$news = $this->db->select('news','id,date,title,link,header','type = 6','date',true,$nc,0);
		$newsc = '';
		if ($news != 0) {
			$i=0;
			foreach ($news as $new) {
				$arr['image'] = '';
				$img = $this->getImg($new->id);
				if ($img) {
					$arr['image'] = ($this->easy->page->link == 'index') ? 
					'<a href="/news/article/'.$new->link.'.html"><img src="'.Image::get($img,190,190,'ResizeAuto').'" width="190" alt="alt"/></a>':
					'<a href="/news/article/'.$new->link.'.html"><img src="'.Image::get($img,170,170,'ResizeAuto').'" width="170" alt="alt"/></a>';
				}
				$arr['date'] = db2rudate($new->date);
				$arr['link'] = $new->link;
				$arr['header'] = $new->header;
				$arr['short'] = $new->title;
				$arr['addclass'] = ($i++ == 3)? 'news-p':'';
				$tpl = ($this->easy->page->link == 'index') ?
				'<div class="news {addclass}">
	              <h5><a href="/news/article/{link}.html">{header}</a></h5>
	              <p>{short}</p>
	              <p class="info">{date}</p>
	              </div>' : 
				'<div class="news">
				  <h5><a href="/news/article/{link}.html">{header}</a></h5>
				  <p>{short}</p>
				  <p class="info">{date}</p>
				</div>';                          
	                         
				$newsc .= tpl($tpl,$arr);
			}
		}
		wr('NEWS_BLOCK',$newsc);		
	}

	public function products_block() {
		$newsc = '';
		$products = $this->db->select('catalog','*','hot=1');	
		if ($products != 0) {
			$i=0;
			foreach (array_slice($products,0,2) as $product) {
				$arr['image'] = '';
				$img = $this->getImg($product->id, 'catalog');
				if ($img) {
					$arr['image'] = ($this->easy->page->link == 'index') ? 
					'<a href="/katalog_tovarov.html#inline'.$product->id.'"><img src="'.Image::get($img,190,190,'ResizeAuto').'" width="190" alt="alt"/></a>':
					'<a href="/katalog_tovarov.html#inline'.$product->id.'"><img src="'.Image::get($img,170,170,'ResizeAuto').'" width="170" alt="alt"/></a>';
				}
				$arr['link'] = "/katalog_tovarov.html#inline".$product->id."";
				$arr['header'] = $product->name;
				$arr['short'] = $product->text;
				$arr['addclass'] = ($i++ == 3)? 'news-p':'';
				$tpl = ($this->easy->page->link == 'index') ?
				'<div class="news prod {addclass}">
	              <h5><a href="{link}">{header}</a></h5>
	              {image}
	              </div>' : 
				'<div class="news">
				  <h5><a href="/{link}.html">{header}</a></h5>
				  {image}
				</div>';                        
	                         
				$newsc .= tpl($tpl,$arr);
			}
		}
		wr('PRODUCTS_BLOCK',$newsc);
	}

	public function oldbrowser(){
		if (is_old_ie($_SERVER['HTTP_USER_AGENT'])) {
			$browser = '<style>#bad_browser a {color:red;} #bad_browser a:hover { text-decoration: underline; }</style><div id="bad_browser" style="text-align: center; padding: 8px; border-bottom: 1px solid #B8C7D3; line-height: 150%; background-color: #fff; color:#f00;">Вы используете устаревший браузер.<br/>Чтобы использовать все возможности сайта, загрузите и установите обновленную версию <a href="http://www.microsoft.com/rus/windows/internet-explorer/">Internet Explorer</a> или один из этих браузеров:<br><div style="width: 400px; height: 100px; margin: 10px auto 0px;"><a href="http://www.google.com/chrome/" target="_blank" style="float: left; background: url(/templates/easy/i/browsers/chrome.gif) no-repeat 50% 6px; width: 100px; height: 20px; padding-top: 80px; text-align:center;">Google Chrome</a><a href="http://www.apple.com/safari/" target="_blank" style="float: left; background: url(/templates/easy/i/browsers/safari.gif) no-repeat 50% 0px; width: 100px; height: 20px; padding-top: 80px;text-align:center;">Safari</a><a href="http://www.opera.com/" target="_blank" style="float: left; background: url(/templates/easy/i/browsers/opera.gif) no-repeat 50% 7px; width: 100px; height: 20px; padding-top: 80px;text-align:center;">Opera</a><a href="http://www.mozilla-europe.org/" target="_blank" style="float: left; background: url(/templates/easy/i/browsers/firefox.gif) no-repeat 50% 7px; width: 100px; height: 20px; padding-top: 80px;text-align:center;">Mozilla Firefox</a></div></div>';
		}
		else {
			$browser = '';
		}
		wr('BROWSER',$browser);		
	}	

	//[k]  возвращает путь к картинке с запрошенным id и типом связи
	private function getImg ($id,$type='news') {
		$img = $this->db->select('links','cid','parent="'.$type.'" and pid='.$id);
		if ($img != 0) {
			$imgsrc = $this->db->select('images','filename','id='.$img[0]->cid);
			//[k] сформируем имя файла
			if ( $imgsrc != 0 ) $imgsrc = $imgsrc[0]->filename;
			else $imgsrc = false;
		}			
		else {
			$imgsrc = false;
		}		
		return $imgsrc;
	} 	
}

?>