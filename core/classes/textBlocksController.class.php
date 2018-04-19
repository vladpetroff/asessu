<?php

/**
* Главная страница - Вход в админку
*/
class textBlocksController extends controller
{

	public function init(){
		$this->subTitle('Текстовые блоки');
	}

	public function index(){
		$blocks  = $this->db->select('text_block','*');
		wr('CONTENT',$this->easylist($blocks,array('description'=>'text')));
		$this->render();
	}
	
	public function upload(){
		require_once 'inc/image.class.php';
		$img = Image::upload($_FILES['file']['tmp_name'],$_FILES['file']['name']);
		echo Image::get($img,250,400,'ResizeAuto');
		die();
	}
	
	public function create(){
		if (isset($_POST['block'])) {
			// $_POST['banner']['is_show'] = ($_POST['banner']['is_show'] == 'on')?1:0;
			$_POST['block']['text'] = $text = preg_replace('/<!--.*-->/Uis', '', $_POST['block']['text']);
			$this->db->insert('text_block',$_POST['block']);
			$this->generateHTML();
			redirect('/content/textBlocks.html');
		}
		$this->render('site/text_blocks_create.html');
	}
	
	public function edit(){
		if (isset($_POST['block'])) {
			$_POST['block']['text'] = $text = preg_replace('/<!--.*-->/Uis', '', $_POST['block']['text']);
			$block = $this->db->selectById('text_block',$this->easy->params[2]);
			$this->db->update('text_block',merge($block,$_POST['block']));
			$this->generateHTML();
			redirect('/content/textBlocks.html');
		}
		$block = $this->db->selectById('text_block',$this->easy->params[2]);
		$this->fillOut($block);
		$this->render('site/text_blocks_edit.html');		
	}
	
	public function delete(){
		$this->db->cer_delete('text_block','id='.$this->easy->params[2]);
		$this->generateHTML();
		redirect('/content/textBlocks.html');
	}
	
	public function generateHTML(){
		$html = array(
			///'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
			//'<html>',
			//  '<head>',
			//    '<title></title>',
			//    '<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>',
			 //   '<meta http-equiv="X-UA-Compatible" content="IE=8" />',
			 //   '<meta name="keywords" content="ключевые слова"/>',
			 //   '<meta name="description" content="описание деятельности"/>',
			    '<script type="text/javascript" src="/content/media/banner/js/jquery-1.4.1.min.js"></script>',
			 //   '<script type="text/javascript" src="js/banner.js"></script>',
			//  '</head>',
			 //   '<body>',
			);

			$banners = $this->db->select('banner','*','is_show = 1');
			if ($banners) {
			//	array_push($html,'<div style="margin:50px auto; width:800px;">');
				array_push($html,'<script type="text/javascript" src="/content/media/banner/js/banner.js"></script>');
				array_push($html,'<script type="text/javascript" src="/content/media/banner/js/banner-data.js"></script>');
				array_push($html,'<div id="accordion"></div>');
				$limit = (sizeof($banners) < 5)?sizeof($banners):5;
				unset($b);
				for ($i=0; $i < $limit; $i++) { 
					require_once 'inc/image.class.php';
					if ((int)$banners[$i]->image_id > 0) {
						$banners[$i]->image = Image::get($this->db->selectById('images',$banners[$i]->image_id)->filename,0,0,'Original');
					} else {
						$banners[$i]->image = 'none';
					}
					$b[$i] = $banners[$i];					
				}
				$fp = fopen($_SERVER['DOCUMENT_ROOT'].'content/media/banner/js/banner-data.js', 'w');
				fwrite($fp, iconv('UTF-8','WINDOWS-1251','var banner = '.json_encode($b).';'));
				fclose($fp);
			//	array_push($html,'var banner = '.json_encode($b).';');
				array_push($html,'<script type="text/javascript">');
				array_push($html,'make_banner(banner); auto();');
				array_push($html,'</script>');
			//	array_push($html,'<style>');
			///	array_push($html,'</style>');
			}
			$blocks = $this->db->select('text_block','*');
			if (sizeof($blocks) < 4) {
				$i=0;
				foreach ($blocks as $block) {
					if ($i == 0) {
						array_push($html,'<div style="width:250px; float:left; overflow:hidden;">');
					} else {
						array_push($html,'<div style="width:250px; float:left; margin-left:25px; overflow:hidden;">');
					}
					array_push($html,'<h3 style="margin:20px 0px;">'.$block->title.'</h3>');
					array_push($html,$block->text);
					array_push($html,'</div>');
					$i++;
				}
			} else {
				shuffle($blocks);
				$blocks = array_slice($blocks,0,3);
				$i=0;
				foreach ($blocks as $block) {
					if ($i == 0) {
						array_push($html,'<div style="width:250px; float:left; overflow:hidden;">');
					} else {
						array_push($html,'<div style="width:250px; float:left; margin-left:25px; overflow:hidden;">');
					}
					array_push($html,'<h3 style="margin:20px 0px;">'.$block->title.'</h3>');
					array_push($html,$block->text);
					array_push($html,'</div>');
					$i++;
				}
				
			}
			array_push($html,'</body>');
			array_push($html,'</html>');
			
			$fp = fopen($_SERVER['DOCUMENT_ROOT'].'/templates/banner-1251.tpl', 'w');
			foreach ($html as $row) {
				fwrite($fp, iconv('UTF-8','WINDOWS-1251',$row));
			}
					$path = $_SERVER['DOCUMENT_ROOT'].'/tmp/templates_c';
		if ($handle = opendir($path)) {
		    while (false !== ($file = readdir($handle))) {
		        if ($file != "." && $file != "..") {
					unlink($_SERVER['DOCUMENT_ROOT'].'/tmp/templates_c/'.$file);
		        }
		    }
		    closedir($handle);
		}
			fclose($fp);


	}
	
}


?>