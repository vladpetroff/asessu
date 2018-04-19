<?php
// 
//  image.class.php
//  Управление Изображениями
//  
//  Created by Kirill Yakovenko on 2010-03-02.
//  Copyright 2010 EasyLabs. All rights reserved.
// 

/**
 * Image class
 *
 * 
 * @author Kirill Yakovenko
 **/
class Image{

	/**
	 * Upload Image on server
	 *
	 * @return String filename
	 * @author Kirill Yakovenko
	 **/
	public static function upload($uploadname, $filename){
		$guid = time();
		$filename = strtolower($filename);
		$filename = $guid."-".$filename;
		if(move_uploaded_file($uploadname, $_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$filename)){
			chmod($_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$filename, 0777);
			return $filename;
		} else{
			return false;
		}
	}
	
	/**
	 * AJAX Upload Image on server
	 *
	 * @return String filename
	 * @author Kirill Yakovenko
	 **/
	public static function uploadBin(){
		//$headers = getallheaders();
		function emu_getallheaders() {
		foreach($_SERVER as $h=>$v)
		if(preg_match('/HTTP_(.+)/',$h,$hp))
		$headers[$hp[1]]=$v;
		return $headers;
		}
		$headers = emu_getallheaders();
	    if(
	        // Проверки
	        isset(
	            //$headers['Content-Type'],
	            //$headers['Content-Length'],
	            $headers['X_FILE_NAME'],
	            $headers['X_FILE_SIZE']
	        )// &&
	        //$headers['Content-Type'] === 'multipart/form-data' &&
	        //$headers['Content-Length'] === $headers['X-File-Size']
	    ){
			$guid = time();
			$filename = strtolower(basename($headers['X_FILE_NAME']));
			$filename = $guid."-".$filename;
	        $content = file_get_contents("php://input");
	        if(file_put_contents($_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$filename, $content)){
				chmod($_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$filename, 0777);
				return $filename;		
			} else {
				return false;
			}
	    }
	}

	/**
	 * Get a image by filename
	 *
	 * @return String filename
	 **/
	public static function get($filename,$width,$height,$method="ResizeAuto",$bgcolor="FFFFFF") {
		if ($method == 'Original') {
			if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$filename)) {
				return 'Not found';
			} else {
				return '/media/uploads/orig/'.$filename;
			}
		}
		if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$filename)) {
			return 'Not found';
		}
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/media/uploads/cache/'.$width.'x'.$height.'x'.$method.'x'.$bgcolor.'/'.$filename)) {
			return '/media/uploads/cache/'.$width.'x'.$height.'x'.$method.'x'.$bgcolor.'/'.$filename;
		} else {
			if (!is_dir($_SERVER['DOCUMENT_ROOT'].'/media/uploads/cache/'.$width.'x'.$height.'x'.$method.'x'.$bgcolor.'/')) {
				mkdir($_SERVER['DOCUMENT_ROOT'].'/media/uploads/cache/'.$width.'x'.$height.'x'.$method.'x'.$bgcolor.'/',0777);
			}
			$file = $_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$filename;
			$toFile = $_SERVER['DOCUMENT_ROOT'].'/media/uploads/cache/'.$width.'x'.$height.'x'.$method.'x'.$bgcolor.'/'.$filename;
			$webName = '/media/uploads/cache/'.$width.'x'.$height.'x'.$method.'x'.$bgcolor.'/'.$filename;
			$imageSize = getimagesize($_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$filename);
			$imageWidth = $imageSize[0];
			$imageHeight = $imageSize[1];
			switch ($method) {
				case 'ResizeAuto':
				if ($imageWidth >= $imageHeight) {
					if ($imageWidth > $width){
						$rect = Image::ResizeBySize($imageWidth,$imageHeight,$width,$height,'toWidth');
						if ($rect['height'] > $height) {
							$rect = Image::ResizeBySize($imageWidth,$imageHeight,$width,$height,'toHeight');
						}
						Image::ProcessImage($file,$toFile,$rect,$imageWidth,$imageHeight,$rect['width'],$rect['height'],$bgcolor);
					} else {
						copy($file,$toFile);
						chmod($toFile, 0777);
					}		
				} else {
					if ($imageHeight > $height){
						$rect = Image::ResizeBySize($imageWidth,$imageHeight,$width,$height,'toHeight');
						if ($rect['width'] > $width) {
							$rect = Image::ResizeBySize($imageWidth,$imageHeight,$width,$height,'toWidth');
						}
						Image::ProcessImage($file,$toFile,$rect,$imageWidth,$imageHeight,$rect['width'],$rect['height'],$bgcolor);			
					} else {
						copy($file,$toFile);
						chmod($toFile, 0777);				
					}	
				}					
				break;
				case 'Resize':
					$rect = Image::Resize($imageWidth,$imageHeight,$width,$height,'Resize');
					Image::ProcessImage($file,$toFile,$rect,$imageWidth,$imageHeight,$width,$height,$bgcolor);					
				break;
				case 'ResizeCut':
					$rect = Image::Resize($imageWidth,$imageHeight,$width,$height);
					Image::ProcessImage($file,$toFile,$rect,$imageWidth,$imageHeight,$width,$height,$bgcolor);					
				break;
				case 'toWidth':
					$rect = Image::ResizeBySize($imageWidth,$imageHeight,$width,$height,'toWidth');
					Image::ProcessImage($file,$toFile,$rect,$imageWidth,$imageHeight,$width,$height,$bgcolor);					
				break;
				case 'toHeight':
					$rect = Image::ResizeBySize($imageWidth,$imageHeight,$width,$height,'toHeight');
					Image::ProcessImage($file,$toFile,$rect,$imageWidth,$imageHeight,$width,$height,$bgcolor);					
				break;
			}
			return $webName;
		}
	}	

	/**
	 * Remove image and cache by filename
	 *
	 * @return void
	 * 
	 **/
	public static function delete($filename){
		unlink($_SERVER['DOCUMENT_ROOT'].'/media/uploads/orig/'.$filename);
		if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].'/media/uploads/cache')) {
		    while (false !== ($file = readdir($handle))) {
		        if ($file != "." && $file != "..") {
					if (file_exists($_SERVER['DOCUMENT_ROOT'].'/media/uploads/cache/'.$file.'/'.$filename)) {
						unlink($_SERVER['DOCUMENT_ROOT'].'/media/uploads/cache/'.$file.'/'.$filename);
					}
		        }
		    }
		    closedir($handle);
		}
	}

	public static function ResizeBySize ($width, $height, $toWidth, $toHeight, $sizeName) {
		if($sizeName=="toHeight"){
			$k = $height/$toHeight;
			$dstWidth = ceil($width/$k);
			return(array('x'=>0,'y'=>0,'width'=>$dstWidth,'height'=>$toHeight));
		}
		else if($sizeName=="toWidth"){
			$k = $width/$toWidth;
			$dstHeight = ceil($height/$k);
			return(array('x'=>0,'y'=>0,'width'=>$toWidth,'height'=>$dstHeight));
		}
	}
	public static function ProcessImage($fromname, $toname, $rect, $imageWidth, $imageHeight, $toWidth, $toHeight, $bgcolor = 'FFFFFF'){
		if ( preg_match("/jp[e]?g$/i", $fromname) ){
			return Image::jpegImage($fromname, $toname, $rect, $imageWidth, $imageHeight, $toWidth, $toHeight, $bgcolor);
		}
		elseif ( preg_match("/gif$/i", $fromname) ){
			return Image::gifImage($fromname, $toname, $rect, $imageWidth, $imageHeight, $toWidth, $toHeight);
		}
		elseif ( preg_match("/png$/i", $fromname) ){
			return Image::pngImage($fromname, $toname, $rect, $imageWidth, $imageHeight, $toWidth, $toHeight);
		}
	}
	public static function pngImage($fromname, $toname, $rect, $imageWidth, $imageHeight, $toWidth, $toHeight){
		$imagePreview = imagecreatetruecolor($toWidth, $toHeight);
		##сохраняем фон с альфа прозрачностью (пока не прозрачен)
		imagesavealpha($imagePreview, true);
		##[skid] Цифра 127 говорит о полной прозрачности бэкграунда
		$trans_colour = imagecolorallocatealpha($imagePreview, 0, 0, 0, 127);
		##[skid] делаем фон полностью прозрачным
		imagefill($imagePreview, 0, 0, $trans_colour);
		$imageCreate = imagecreatefrompng($fromname);
		imagealphablending($imageCreate, true); //[skid] включаем alpha blending
		imagesavealpha($imageCreate, true);
		imagecopyresampled($imagePreview, $imageCreate, $rect['x'], $rect['y'], 0, 0, $rect['width'], $rect['height'], $imageWidth, $imageHeight);
		imagepng($imagePreview, $toname);
		chmod($toname, 0777);
	}
	##[skid] Метод преобразования цвета из шестнадцатеричной системы в десятичную
	public static function ColorConv($hexcolor){
		$r = hexdec(substr($hexcolor, 0, 2));
    	$g = hexdec(substr($hexcolor, 2, 2));
    	$b = hexdec(substr($hexcolor, 4, 2));
    	return array("r"=>$r,"g"=>$g,"b"=>$b);
	}

	##[skid] Метод для сохранения картинки в формате jpeg на сервер
	public static function jpegImage($fromname, $toname, $rect, $imageWidth, $imageHeight, $toWidth, $toHeight, $bgcolor){
		$rgb = Image::ColorConv($bgcolor);
		$imagePreview = imagecreatetruecolor($toWidth, $toHeight);
		$back = imagecolorallocate($imagePreview, $rgb['r'], $rgb['g'], $rgb['b']);//[skid] Устанавливаем бэкграунд
		imagefill($imagePreview, 0, 0, $back);
		$imageCreate = imagecreatefromjpeg($fromname);
		imagecopyresampled($imagePreview, $imageCreate, $rect['x'], $rect['y'], 0, 0, $rect['width'], $rect['height'], $imageWidth, $imageHeight);
		imagejpeg($imagePreview, $toname, 100);
		chmod($toname, 0777);
	}

	##[skid] Метод для сохранения картинки в формате gif на сервер
	public static function gifImage($fromname, $toname, $rect, $imageWidth, $imageHeight, $toWidth, $toHeight){
		$imagePreview = imagecreatetruecolor($toWidth, $toHeight);
		$back = imagecolorallocate($imagePreview, 0, 0, 0);//[skid] Устанавливаем бэкграунд
		imagecolortransparent($imagePreview, $back);//[skid] Устанавливаем прозрачный бэкграунд
		$imageCreate = imagecreatefromgif($fromname);
		imagecopyresampled($imagePreview, $imageCreate, $rect['x'], $rect['y'], 0, 0, $rect['width'], $rect['height'], $imageWidth, $imageHeight);
		imagegif($imagePreview, $toname, 100);
		chmod($toname, 0777);
	}
	## [skid] Функция расчета размеров картинки для заливки
	public static function getSizeValues($imageSize1, $dstSize1, $imageSize2, $dstSize2){
		$k=$imageSize1/$dstSize1;
		$output[1] = ceil($imageSize2/$k);
		$output[0] = $dstSize1;
		if($output[1]<$dstSize2){
			$k=$imageSize2/$dstSize2;
			$output[0]=ceil($imageSize1/$k);
			$output[1] = $dstSize2;
		}
		else{
			$k=$output[1]/$dstSize2;
			$output[1] = $dstSize2;
			$output[0] = ceil($output[0]/$k);
		}
		return $output;
	}

	## [skid] Метод изменения размера картинки исходя их 2-х размеров.
	public static function Resize($width, $height, $toWidth, $toHeight, $option='ResizeCut'){
		## [skid] если в настройках метода указано обрезать, то уменьшаем меньшую сторону
		if ($width == $height) {
			if ($toWidth > $toHeight) {
				$dstWidth = $toWidth;
				$k = $width/$toWidth;
				$dstHeight = $height/$k;
			} else if ($toWidth == $toHeight){
				$dstWidth = $toWidth;
				$dstHeight = $toHeight;
			} else {
				$dstHeight = $toHeight;
				$k = $height/$toHeight;
				$dstWidth = $width/$k;				
			}
		} else if($width<$height){
			if($option == 'Resize'){
				$sizes = Image::getSizeValues($width, $toWidth, $height, $toHeight);
				$dstWidth = $sizes[0];
				$dstHeight = $sizes[1];
			}
			else if($option == 'ResizeCut'){
				$sizes = Image::getSizeValues($height, $toHeight, $width, $toWidth);
				$dstHeight = $sizes[0];
				$dstWidth = $sizes[1];
			}
		}
		else {
			if($option == 'Resize'){
				$sizes = Image::getSizeValues($height, $toHeight, $width, $toWidth);
				$dstHeight = $sizes[0];
				$dstWidth = $sizes[1];
			}
			else if($option == 'ResizeCut'){
				$sizes = Image::getSizeValues($width, $toWidth, $height, $toHeight);
				$dstWidth = $sizes[0];
				$dstHeight = $sizes[1];
			}
		}
		## [skid] если картинка меньше чем заданные размеры то размещаем ее по центру
		if($width<$toWidth && $height<$toHeight){
			$dstWidth = $width;
			$dstHeight = $height;
		}
		if($width>$toWidth && $height<$toHeight){
			$k = $width/$toWidth;
			$dstHeight = ceil($height/$k);
			$dstWidth=$toWidth;
		}
		if($width<$toWidth && $height>$toHeight){
			$k = $height/$toHeight;
			$dstWidth = ceil($width/$k);
			$dstHeight=$toHeight;
		}
		if($toHeight%2==0){
			$dst_y=$dstHeight%2==0?($toHeight-$dstHeight)/2:($toHeight-$dstHeight)/2+1;
		}
		else{
			$dst_y=$dstHeight%2==1?($toHeight-$dstHeight)/2:($toHeight-$dstHeight)/2+1;
		}
		if($toWidth%2==0){
			$dst_x=$dstWidth%2==0?($toWidth-$dstWidth)/2:($toWidth-$dstWidth)/2+1;
		}
		else{
			$dst_x=$dstWidth%2==1?($toWidth-$dstWidth)/2:($toWidth-$dstWidth)/2+1;
		}
		return(array('x'=>$dst_x,'y'=>$dst_y,'width'=>$dstWidth,'height'=>$dstHeight));
	}
} // END class 

?>