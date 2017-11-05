<?php
namespace Core\System;

use Core\System\AiiUtility\Log;

class Compound
{
	/**
	 * @todo : 本函数用于 将方形的图片压缩后
	 *         再裁减成圆形 做成logo 与背景图合并
	 * @return 返回url
	 * headimgurl 头像
	 * bgurl 背景图
	 *
	 *
	 * 合成图片
	 * imgs  array();
	 * dst 背景图片路径
	 * src 水印图片路径
	 * saveimg 保存图片路劲 如果为空则覆盖原图片
	 * pos 图片水印添加的位置，取值范围：0~9
	 * 0：随机位置，在1~9之间随机选取一个位置
	 * 1：顶部居左 2：顶部居中 3：顶部居右 4：左边居中
	 * 5：图片中心 6：右边居中 7：底部居左 8：底部居中 9：底部居右(默认)
	 *
	 *
	 */
	public function index($imgs = array()){
		$log = new Log('web');
		if(!$imgs['dst']){
			$log->err(__CLASS__.'<-->'.__FUNCTION__.'缺少背景图片,行号为：'.__LINE__.'行');
			return false;
		}
		if(!$imgs['src']){
			$log->err(__CLASS__.'<-->'.__FUNCTION__.'缺少水印图片,行号为：'.__LINE__.'行');
			return false;
		}
		//生成文件夹
		$path_parts = pathinfo($imgs['saveimg']);
		@ mkdir($path_parts['dirname'], 0777, true);
		//第一步 压缩图片
		$imggzip = $this->resize_img($imgs['src'],$path_parts['dirname'].'/');
		//第二步 裁减成圆角图片
		$imgs['src'] = $this->test($imggzip,$path_parts['dirname'].'/');
		//第三步 合并图片
		return $this->mergerImg($imgs);
	}

	public function resize_img($url,$path='./'){
		$log = new Log('web');
		$imgname = $path.uniqid().'.jpg';
		$file = $url;
		if(!is_file($file)){
			$log->err(__CLASS__.'<-->'.__FUNCTION__.'文件打开失败,行号为：'.__LINE__.'行');
			return false;
		}
		list($width, $height) = getimagesize($file); //获取原图尺寸
		$percent = (100/$width);
		//缩放尺寸
		$newwidth = $width * $percent;
		$newheight = $height * $percent;
		$src_im = imagecreatefromjpeg($file);
		$dst_im = imagecreatetruecolor($newwidth, $newheight);
		imagecopyresized($dst_im, $src_im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		imagejpeg($dst_im, $imgname); //输出压缩后的图片
		imagedestroy($dst_im);
		imagedestroy($src_im);
		return $imgname;
	}

	//第一步生成圆角图片
	public function test($url,$path='./'){
		$log = new Log('web');
		$w = 100;  $h = 100; // original size
		$original_path= $url;
		if(!$original_path){
			$log->err(__CLASS__.'<-->'.__FUNCTION__.'图片路劲不存在,行号为：'.__LINE__.'行');
			return false;
		}
		$dest_path = $path.uniqid().'.png';
		$src = imagecreatefromstring(file_get_contents($original_path));
		$newpic = imagecreatetruecolor($w,$h);
		imagealphablending($newpic,false);
		$transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);
		$r=$w/2;
		for($x=0;$x<$w;$x++)
			for($y=0;$y<$h;$y++){
				$c = imagecolorat($src,$x,$y);
				$_x = $x - $w/2;
				$_y = $y - $h/2;
				if((($_x*$_x) + ($_y*$_y)) < ($r*$r)){
					imagesetpixel($newpic,$x,$y,$c);
				}else{
					imagesetpixel($newpic,$x,$y,$transparent);
				}
			}
		imagesavealpha($newpic, true);
		// header('Content-Type: image/png');
		imagepng($newpic, $dest_path);
		imagedestroy($newpic);
		imagedestroy($src);
		unlink($url);
		return $dest_path;
	}

	/* 合成图片
	* imgs  array();
	* dst 背景图片路径
	* src 水印图片路径
	* saveimg 保存图片路劲 如果为空则覆盖原图片
	* pos 图片水印添加的位置，取值范围：0~9
	* 0：随机位置，在1~9之间随机选取一个位置
	* 1：顶部居左 2：顶部居中 3：顶部居右 4：左边居中
	* 5：图片中心 6：右边居中 7：底部居左 8：底部居中 9：底部居右(默认)
	* 10 离顶部居中  11 离底部居中
	* */
	public function mergerImg($imgs) {
		$log = new Log('web');
		if(!$imgs['dst']){
			$log->err(__CLASS__.'<-->'.__FUNCTION__.'缺少背景图片');
			return false;
		}
		if(!$imgs['src']){
			$log->err(__CLASS__.'<-->'.__FUNCTION__.'缺少水印图片');
			return false;
		}
		$ext = pathinfo($imgs['dst']);
		list($max_width, $max_height) = getimagesize($imgs['dst']);
		$dests = imagecreatetruecolor($max_width, $max_height);
		switch ($ext['extension']) {
			case 'jpg':
				$dst_im = imagecreatefromjpeg($imgs['dst']);
				break;
			case 'png':
				$dst_im = imagecreatefrompng($imgs['dst']);
				break;
		}
		imagecopy($dests,$dst_im,0,0,0,0,$max_width,$max_height);
		imagedestroy($dst_im);
		$src_im = imagecreatefrompng($imgs['src']);
		$src_info = getimagesize($imgs['src']);

		// 根据系统设置获得水印的位置
		$pos = isset($imgs['pos']) && $imgs['pos'] ? $imgs['pos'] : 0;
		if($pos == 0)
		{
			$pos = rand(1, 9); // 随机
		}
		// $max_width 原图的宽$max_height 原图的高$src_info[0] 水印图的宽 $src_info[1] 水印图的高
		switch($pos)
		{
			case 1:
				$x = + 5;
				$y = + 5;
				break;
			case 2:
				$x = ($max_width - $src_info[0]) / 2;
				$y = + 5;
				break;
			case 3:
				$x = $max_width - $src_info[0] - 5;
				$y = + 15;
				break;
			case 4:
				$x = + 5;
				$y = ($max_height - $src_info[1]) / 2;
				break;
			case 5:
				$x = ($max_width - $src_info[0]) / 2;
				$y = ($max_height - $src_info[1]) / 2;
				break;
			case 6:
				$x = $max_width - $src_info[0] - 5;
				$y = ($max_height - $src_info[1]) / 2;
				break;
			case 7:
				$x = + 5;
				$y = $max_height - $src_info[1] - 5;
				break;
			case 8:
				$x = ($max_width - $src_info[0]) / 2;
				$y = $max_height - $src_info[1] - 5;
				break;
			case 9:
				$x = $max_width - $src_info[0] - 60;
				$y = $max_height - $src_info[1] - 40;
				break;
			case 10:
				$x = ($max_width - $src_info[0]) / 2;
				$y = (($max_height)/9) - ($src_info[1]/2) + 20;
				break;
			case 11:
				$x = ($max_width - $src_info[0]) / 2 + 8;
				$y = ($max_height - $src_info[1]*2) + 40;
				break;
			default:
				$x = $max_width - $src_info[0] - 5;
				$y = $max_height - $src_info[1] - 5;
		}
		$path_parts = pathinfo($imgs['saveimg']);
		@ mkdir($path_parts['dirname'], 0777, true);

		imagecopy($dests,$src_im,$x,$y,0,0,$src_info[0],$src_info[1]);
		imagedestroy($src_im);
		imagejpeg($dests,$imgs['saveimg']);
		unlink($imgs['src']);
		return $imgs['saveimg'];
	}
}