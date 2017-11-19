<?php
/**
 * 实现图片缩略图， 增加水印(支持PHP 5+)
 *
 * @author liujun (文豆版)
 * @version 5.2.6 最后修改时间 2012年10月03日
 * @link http://www.lamsonphp.com	(http://www.wengdo.com)
 * @example 
 * 		//生成缩略图
 *      $image = new Image();
 *      echo $ok = $image -> makeThumb('../../images/thumbview.jpg', 80, 60);
 *      echo $image->getError();
 *     
 *     //添加水印
 *     $image = new Image();
 *     echo $ok = $image -> watermark('../../images/thumbview.jpg', './test.jpg', 'img', '../../images/no.gif');
 *     echo $image->getError();
 *         
 *     $image = new Image();
 *
 *     echo $ok = $image -> watermark(
 *     array('orgin'=>'../../images/320.gif', 'target'=>'./test.gif', 'mark_type'=>'img', 'water_img'=>'../../images/icon_zoom.jpg', 'alpha'=>'15')
 *     );
 *     echo $image->getError();
 *         
 *     $image = new Image();
 *     echo $ok = $image -> watermark('../../images/320.gif', './test.gif', 'txt', '', 'lamson', $color = '#FF0000', $font = 12, $font_type = '../vendors/fonts/msyh.ttf', 9);
 *     echo $image->getError();
 *     
 * 需要依赖的资源：
 *          类： 
 *          函数：
 *          变量：
 *         
 */
class Image
{
	public $nameRule = ''; // 新图的命名规则。XXX_表示加前缀，_XXX表示加后缀，其他字符表示指定名称，空则表示自动生成
	public $tpBg = false; // 对于GIF，是否需要保持原背景透明（开启的话，由于缩略图的宽度width必须为原图宽度的约数！所以生成的图都会偏小）
	protected $_error = '';
	protected $_snum = 0;
	public static $lang = array('invalid_size' => '缩略图的宽高不能同时为0', 'missing_gd' => '没有安装GD库', 'missing_orgin_image' => '找不到原始图片 %s ', 'nonsupport_type' => '不支持该图像格式 %s ', 'creating_failure' => '创建图片失败', 'writting_failure' => '图片写入失败', 'empty_watermark' => '水印文件参数不能为空', 'missing_water_image' => '找不到水印文件 %s', 'invalid_image_type' => '无法识别水印图片 %s ', 'file_unavailable' => '文件 %s 不存在或不可读', 'missing_warter_txt' => '没有设置水印文本', 'missing_font_type' => '找不到字体库');

	function __construct()
	{
		
	}

	/**
	 * 创建图片的缩略图
	 *
	 * $orgin			原始图片的路径
	 * $thumb_width	缩略图宽度
	 * $thumb_height	缩略图高度
	 * $path			指定生成图片的目录名
	 * $bgcolor		背景填充色
	 * $type	缩略类型	1填充	2截取	3拉伸	@by waydy
	 * 返回		如果成功返回缩略图的路径，失败则返回false
	 */
	function makeThumb($orgin, $thumb_width = 0, $thumb_height = 0, $path = NULL, $bgcolor = '#FFFFFF', $quality = 85, $type = 1)
	{
		if(! ($orgarr = $this->handle($orgin)))
		{
			return false;
		}
		
		
		/* 检查缩略图宽度和高度是否合法 */
		if($thumb_width == 0 && $thumb_height == 0)
		{
			$this->_error = self::$lang['invalid_size'];
			return false;
		}
		
		$org_info = $orgarr['info'];
		
		/* 原始图片以及缩略图的尺寸比例 （浮点数） */
		$src_scale = $org_info[0] / $org_info[1]; // 原图宽高比
		
		$canvas_width = $thumb_width;
		$canvas_height = $thumb_height;
		
		/* 处理只有缩略图宽和高有一个为0（即自适应）的情况 */
		if($thumb_width == 0)
		{
		    $thumb_width = $canvas_width = min($org_info[0], round($thumb_height * $src_scale)); // 画布宽
		}
		elseif($thumb_height == 0)
		{
		    $thumb_height = $canvas_height = min($org_info[1], round($thumb_width / $src_scale)); // 画布高
		}
		switch($type){
		    case 1://等比例缩小
		        @$dst_scale = $thumb_width / $thumb_height; // 缩略图宽高比
		        $pos_x = 0;
		        $pos_y = 0;
		        if($src_scale >= $dst_scale)//原图宽高比 缩略图宽高比
		        {
		            $thumb_height = $canvas_height = round($thumb_width / $src_scale); // 画布高
		        }
		        else
		        {
		            $thumb_width = $canvas_width = round($thumb_height * $src_scale); // 画布宽
		        }
		        break;
			case 2://截取
				@$dst_scale = $thumb_width / $thumb_height; // 缩略图宽高比
				if($src_scale >= $dst_scale)//原图宽高比 缩略图宽高比
				{
					$thumb_width=$org_info[0]*($canvas_height/$org_info[1]);
					$thumb_height=$canvas_height;
					
					$pos_x = ($canvas_width - $thumb_width) / 2;
					$pos_y = 0;
				}
				else
				{
					$thumb_width=$canvas_width;
					$thumb_height=$org_info[1]*($canvas_width/$org_info[0]);
						
					$pos_x = 0;
					$pos_y = ($canvas_height - $thumb_height) / 2;
				}
			break;
			case 3://拉伸
				$thumb_width=$canvas_width;
				$thumb_height=$canvas_height;
					
				$pos_x = 0;
				$pos_y = 0;
			break;
			default://填充
				if(stripos($org_info['mime'], 'gif') !== false && $this->tpBg) // 如果图片是GIF格式
				{
					$this->_snum = $canvas_width;
					// 为了支持透明，必须将缩略图的宽度设置为原图的宽度的约数
					$arr = $this->_min_multiple($org_info[0]);
					$thumb_width = $arr['min_val']; // 找出离画布宽度最近的最小约数
					$thumb_height = round($thumb_width / $src_scale);
					if($thumb_height > $canvas_height) // 如果高度大于画布高度
					{
						for($i = 0; $i < $arr['min_key']; ++ $i)
						{
							if(($h = round($arr['mul'][$i] / $src_scale)) <= $canvas_height)
							{
								$thumb_height = $h;
								break;
							}
						}
						if($i == $arr['min_key'])
						{
							$thumb_height = $canvas_height;
						}
					}
				}
				else
				{
					@$dst_scale = $thumb_width / $thumb_height; // 缩略图宽高比
					if($src_scale >= $dst_scale)
					{
						$thumb_height = floor($thumb_width / $src_scale);
					}
					else
					{
						$thumb_width = floor($thumb_height * $src_scale);
					}
				}
				
				$pos_x = ($canvas_width - $thumb_width) / 2;
				$pos_y = ($canvas_height - $thumb_height) / 2;
		}
		
		/* 创建缩略图的标志符 */
		if($orgarr['gd'] == 2)
		{
		    $thumb = imagecreatetruecolor($canvas_width, $canvas_height);
		}
		else
		{
		    $thumb = imagecreate($canvas_width, $canvas_height);
		}
		
		// PNG和GIF的背景处理
		if((stripos($org_info['mime'], 'gif') !== false && $this->tpBg) || stripos($org_info['mime'], 'png') !== false)
		{
			$tmpcolor = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
			imagefill($thumb, 0, 0, $tmpcolor);
			imagecolortransparent($thumb, $tmpcolor); // 将背景颜色换成透明
			$quality *= 0.1;
		}
		else
		{
			imagefill($thumb, 0, 0, $this->coloral($thumb, $bgcolor));
		}
		
		/* 将原始图片进行缩放处理 */
		if($orgarr['gd'] == 2)
		{
			imagecopyresampled($thumb, $orgarr['res'], $pos_x, $pos_y, 0, 0, $thumb_width, $thumb_height, $org_info[0], $org_info[1]);
		}
		else
		{
			imagecopyresized($thumb, $orgarr['res'], $pos_x, $pos_y, 0, 0, $thumb_width, $thumb_height, $org_info[0], $org_info[1]);
		}
		
		is_null($path) and $path = pathinfo($orgin, PATHINFO_DIRNAME);
		$path = rtrim($path, '/') . '/';
		is_dir($path) or @ mkdir($path, 0777, true); // 如果路径不存在，自动创建
		$ext = strtolower(pathinfo($orgin, PATHINFO_EXTENSION));
		
		//新命名规则
		if($this->nameRule=='')
		{
			$newname = $_SERVER['REQUEST_TIME'] . mt_rand(1000, 9999);
		}
		else
		{
			if(strpos($this->nameRule, '_')===0)
			{
				$newname = pathinfo($orgin, PATHINFO_FILENAME) . $this->nameRule;
			}
			elseif(preg_match('/_$/', $this->nameRule))
			{
				$newname = $this->nameRule . pathinfo($orgin, PATHINFO_FILENAME);
			}
			else
			{
				$newname = $this->nameRule;
			}
		}
		
		$f = $path . $newname . ".$ext";
		/* 生成文件 */
		$ext == 'jpg' and $ext = 'jpeg';
		if(function_exists($fun = "image$ext"))
		{
			$fun($thumb, $f, $quality);
		}
		else
		{
			$this->_error = self::$lang['creating_failure'];
			return false;
		}
		imagedestroy($thumb);
		imagedestroy($orgarr['res']);
		
		// 确认文件是否生成
		if(is_file($f))
		{
			return str_replace(array('../', './'), '', $f);
		}
		else
		{
			$this->_error = self::$lang['writting_failure'];
			return false;
		}
	}

	/**
	 * 为图片增加水印
	 *
	 * $orgin		原始图片文件名，包含完整路径
	 * target		需要加水印的图片文件名，包含完整路径。如果为空则覆盖源文件
	 * $mark_type	水印类型	txt 文字(默认) img 图片
	 * $water_img	水印图片完整路径
	 * $water_txt	水印文字
	 * $color		文字颜色
	 * $font		文字大小
	 * $font_type	文字字体库
	 * $pos		图片水印添加的位置，取值范围：0~9
	 * 0：随机位置，在1~9之间随机选取一个位置
	 * 1：顶部居左 2：顶部居中 3：顶部居右 4：左边居中
	 * 5：图片中心 6：右边居中 7：底部居左 8：底部居中 9：底部居右(默认)
	 * $alpha		alpha 透明度，0为全透明，100为不透明，只对水印图有效
	 * $quality	范围从 0（最差质量，文件更小）到 100（最佳质量，文件最大）。原系统默认为 IJG 默认的质量值（大约 75），本系统默认85。
	 * 返回：如果成功则返回文件路径，否则返回false
	 */
	function watermark($orgin, $target = '', $mark_type = 'txt', $water_img = '', $water_txt = '', $color = '#FF0000', $font = 14, $font_type = NULL, $pos = 9, $alpha = 30, $quality = 85)
	{
		if(is_array($orgin))
		{
			extract($orgin);
		}
		
		if(! ($orgarr = $this->handle($orgin)))
		{
			return false;
		}
		// 根据文件类型获得原始图片的操作句柄
		$org_info = $orgarr['info'];
		$org_handle = $orgarr['res'];
		
		if(! strcmp($mark_type, 'img')) // 如果是图片水印
		{
			if(! ($wtearr = $this->handle($water_img, 'water')))
			{
				return false;
			}
			// 获得水印文件以及源文件的信息
			$mark_info = $wtearr['info'];
			$mark_handle = $wtearr['res'];
			if(stripos($org_info['mime'], 'png') !== false) // 如果原图是png格式
			{
				imagesavealpha($org_handle, true); // 这里很重要,意思是不要丢了$org_handle图像的透明色;
			}
		}
		else
		{
			if($water_txt == '')
			{
				$this->_error = self::$lang['missing_warter_txt'];
				return false;
			}
			if(! is_file($font_type))
			{
			    $font_type = __DIR__ . '\\' . $font_type;
			}
			if(! is_file($font_type))
			{
				$this->_error = self::$lang['missing_font_type'];
				return false;
			}
			// f(!is_utf8($water_txt)){$water_txt = iconv('GBK', 'UTF-8//IGNORE', $water_txt);}
			$box = @imagettfbbox($font, 0, $font_type, $water_txt);
			$mark_info[0] = max($box[2], $box[4]) - min($box[0], $box[6]);
			$mark_info[1] = max($box[1], $box[3]) - min($box[5], $box[7]);
			
// 			imagettftext($org_handle, $font, 0, $x, $y, $this->coloral($org_handle, $color), $font_type, $water_txt);
		}
		
		// 根据系统设置获得水印的位置
		if($pos == 0)
		{
			$pos = rand(1, 9); // 随机
		}
		switch($pos)
		{
			case 1:
				$x = + 5;
				$y = + 5;
			break;
			case 2:
				$x = ($org_info[0] - $mark_info[0]) / 2;
				$y = + 5;
			break;
			case 3:
				$x = $org_info[0] - $mark_info[0] - 5;
				$y = + 15;
			break;
			case 4:
				$x = + 5;
				$y = ($org_info[1] - $mark_info[1]) / 2;
			break;
			case 5:
				$x = ($org_info[0] - $mark_info[0]) / 2;
				$y = ($org_info[1] - $mark_info[1]) / 2;
			break;
			case 6:
				$x = $org_info[0] - $mark_info[0] - 5;
				$y = ($org_info[1] - $mark_info[1]) / 2;
			break;
			case 7:
				$x = + 5;
				$y = $org_info[1] - $mark_info[1] - 5;
			break;
			case 8:
				$x = ($org_info[0] - $mark_info[0]) / 2;
				$y = $org_info[1] - $mark_info[1] - 5;
			break;
			case 9:
			default:
				$x = $org_info[0] - $mark_info[0] - 5;
				$y = $org_info[1] - $mark_info[1] - 5;
		}
		
		if(! strcmp($mark_type, 'img')) // 如果是图片水印
		{
			imagecopymerge($org_handle, $mark_handle, $x, $y, 0, 0, $mark_info[0], $mark_info[1], $alpha);
		/**
		 * //下面这个也能处理PNG的背景透明
		 * if(stripos($org_info['mime'], 'png') !== false)	//如果原图是png格式
		 * {
		 * $tmpcolor = imagecolorallocatealpha($org_handle, 255, 255, 255, 127);
		 * imagefill($org_handle, 0, 0, $tmpcolor);
		 * imagecolortransparent($org_handle, $tmpcolor);//将背景颜色换成透明
		 * }
		 */
		}
		else
		{
			imagettftext($org_handle, $font, 0, $x, $y, $this->coloral($org_handle, $color), $font_type, $water_txt);
		}
		
		$target = empty($target) ? $orgin : $target;
		$path_parts = pathinfo($target);
		@ mkdir($path_parts['dirname'], 0777, true);
		
		// 根据目标路径生成图片
		$ext = strtolower($path_parts['extension']);
		// $ext == 'jpg' && $ext = 'jpeg';
		if(function_exists($fun = "image$ext"))
		{
		    if ('png' == $ext && $quality > 10)
		    {
		        $quality = round($quality/10);
		    }
			$fun($org_handle, $target, $quality);
		}
		else
		{
			$this->_error = self::$lang['creating_failure'];
			return false;
		}
		
		@imagedestroy($mark_handle);
		imagedestroy($org_handle);
		
		if(is_file($target))
		{
			return str_replace(array('../', './'), '', $target);
		}
		else
		{
			$this->_error = self::$lang['writting_failure'];
			return false;
		}
	}

	/**
	 * 返回错误信息
	 *
	 * @return string 错误信息
	 */
	function getError()
	{
		return $this->_error;
	}

	/**
	 * 获得服务器上的 GD 版本
	 *
	 * @access public
	 * @return int 可能的值为0，1，2
	 */
	function gdVersion()
	{
		static $version = - 1;
		
		if($version >= 0)
		{
			return $version;
		}
		
		if(! extension_loaded('gd'))
		{
			$version = 0;
		}
		else
		{
			// 尝试使用gd_info函数
			if(PHP_VERSION >= '4.3')
			{
				if(function_exists('gd_info'))
				{
					$ver_info = gd_info();
					preg_match('/\d/', $ver_info['GD Version'], $match);
					$version = $match[0];
				}
				else
				{
					if(function_exists('imagecreatetruecolor'))
					{
						$version = 2;
					}
					elseif(function_exists('imagecreate'))
					{
						$version = 1;
					}
				}
			}
			else
			{
				if(preg_match('/phpinfo/', ini_get('disable_functions')))
				{
					/* 如果phpinfo被禁用，无法确定gd版本 */
					$version = 1;
				}
				else
				{
					// 使用phpinfo函数
					ob_start();
					phpinfo(8);
					$info = ob_get_contents();
					ob_end_clean();
					$info = stristr($info, 'gd version');
					preg_match('/\d/', $info, $match);
					$version = $match[0];
				}
			}
		}
		
		return $version;
	}
	
	/*
	 * 检测图片是否存在，并判断是否支持此图片类型的处理 $imgtype: orgin 原始图片 water 水印图片 返回 array('gd'=>gd库版本, 'info'=>图片详情, 'res'=>图片资源句柄)
	 */
	function handle($img, $imgtype = 'orgin')
	{
		$gd = $this->gdVersion(); // 获取 GD 版本。0 表示没有 GD 库，1 表示 GD 1.x，2 表示 GD 2.x
		if($gd == 0)
		{
			$this->_error = self::$lang['missing_gd'];
			return false;
		}
		
		/* 检查原始文件是否存在及获得原始文件的信息 */
		$info = @getimagesize($img);
		if(! $info)
		{
			$this->_error = sprintf(self::$lang["missing_{$imgtype}_image"], $img);
			return false;
		}
		
		if(! function_exists($fun = ('imagecreatefrom' . str_replace('image/', '', $info['mime']))))
		{
			$this->_error = sprintf(self::$lang['nonsupport_type'], $info['mime']);
			return false;
		}
		else
		{
			// 根据来源文件的文件类型创建一个图像操作的标识符
			$res = $fun($img);
		}
		return array('gd' => $gd, 'info' => $info, 'res' => $res);
	}
	
	/*
	 * 分配颜色
	 */
	function coloral($thumb, $color = '')
	{
		$color = trim($color, '#');
		sscanf($color, "%2x%2x%2x", $red, $green, $blue);
		$clr = imagecolorallocate($thumb, $red, $green, $blue);
		return $clr;
	}
	
	// 找出所有$m的约数当中，离$n最近的约数
	protected function _min_multiple($m)
	{
		// 找出所有$m的约数
		for($i = 1; $i <= min($m, $this->_snum); ++ $i)
		{
			if($m % $i == 0)
			{
				$rs[] = $i;
			}
		}

		// 计算出$m中每个成员与$n的差值
		$res = array_map(array(__CLASS__, '_min_multiple_subtract'), $rs);
		// 取出最小值
		$min = min($res);
		// 反转键值
		$filp = array_flip($res);
		// 所有的倍数 最小倍数 最小倍数的下标
		return array('mul' => $rs, 'min_val' => $rs[$filp[$min]], 'min_key' => $filp[$min]);
	}

	protected function _min_multiple_subtract($num)
	{
		return abs($num - $this->_snum);
	}
}


/*
//	相关知识
//	imagecreatetruecolor()或imagecopyresampled()来处理透明背景的gif或png时，透明背景会变为黑色，解决方案是
	
//	对于PNG，最好用	
	$img = imagecreatefrompng('../../images/logo.png');
	imagesavealpha($img,true);//这里很重要;
	$thumb = imagecreatetruecolor(300,60);
	imagealphablending($thumb,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
	imagesavealpha($thumb,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
	if(imagecopyresampled($thumb,$img,0,0,0,0,300,60,300,300)){
		imagepng($thumb,"temp.png");
	}

//	对于PNG或GIF，可以用
	$img = imagecreatefromgif('../../images/logo.gif');
	$thumb = imagecreatetruecolor(100, 100);
	$black = imagecolorallocatealpha($thumb, 0,0,0,127);
	imagefill($thumb, 0, 0, $black);
	imagecolortransparent($thumb, $black);
	if(imagecopyresampled($thumb, $img,0,0,0,0, $thumb_width, 100, 100, 100)){	//对于gif，这里的$thumb_width必须为原图宽度的约数！！
		imagegif($thumb,"temp.gif");
	}
*/