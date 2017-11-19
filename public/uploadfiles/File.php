<?php
/**
 * 文件和目录的操作类
 *
 * @author liujun (文豆版)
 * @version 5.0 最后修改时间 2012年10月01日
 * @link http://www.lamsonphp.com (http://www.wengdo.com)
 * @example
 * 使用说明：
	不管是创建目录还是创建文件，为了安全（特别是在Windows环境下），最好是设置一下PHP.INI的open_basedir项，或者在apache的配置文件里的VirtualHost中配置php_admin_value open_basedir 安全路径。以防止PHP读取或覆盖敏感目录或敏感文件
 * 一个实例化的使用例子：

 * 需要依赖的资源：
	类：

 	函数：

 	常量：

 	变量：
 *
 */

/**
 * 系统分隔符, 在windows下路径分隔符是\， 在linux 上路径的分隔符是/
 */
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
class File
{
	const VERSION = '5.0';

	/**
	 * 浏览文件夹，返回其目录下的目录组和文件组
	 *
	 * @param string $dir 目标目录
	 * @param bool $dot 是否包括.和../
	 * @param int $order 默认的排序顺序是按字母升序排列。如果使用了可选参数 order（设为 1），则排序顺序是按字母降序排列
	 * @return array array('dirs' => (array)$darray, 'files' => (array)$farray)
	 */
	static function viewDir($dir = './', $dot = false, $order = 0)
	{
		if(! is_dir($dir))
		{
			return array('dirs' => array(), 'files' => array());
		}
		
		$files = ( array ) scandir($dir, $order);
		
		if(! $dot)
		{
			if(! $order)
			{
				unset($files[0], $files[1]);
			}
			else
			{
				$files = array_slice($files, 0, - 2);
			}
		}
		
		$dir = self::addDs($dir);
		$farray = $darray = array();
		foreach($files as $v)
		{
			$fullpath = $dir . $v;
			if(is_dir($fullpath))
			{
				$darray[] = $v;
			}
			else if(is_file($fullpath))
			{
				$farray[] = $v;
			}
		}
		return array('dirs' => ( array ) $darray, 'files' => ( array ) $farray);
	}

	/**
	 * 建立目录,如果目录已存，页面会得到一个Warning级别的错误报告
	 *
	 * @param string $pathname        	
	 * @param int $mode        	
	 * @param bool $recursive PHP 5.0.0 添加的。
	 * @return bool
	 */
	// @ mkdir($pathname, $mode = 0777, $recursive = false);
	
	/**
	 * 建立文件
	 *
	 * @param string $filename        	
	 * @param string $data 要写入的内容
	 * @param boolean $overwrite 是否覆盖原文件
	 * @param int $mode 权限模式
	 * @example File::mkFile('./lamson.txt', 'it is a test');	//在当前目录下创建一个文件叫lamson.txt
	 * @return boolean
	 */
	static function mkFile($filename, $data = '', $overwrite = false, $mode = 0777)
	{
		if(file_exists($filename))
		{
			if(! $overwrite)
			{
				return false;
			}
			self::delFile($filename);
		}
		$dir = dirname($filename);
		// is_dir()对于实际存在但没权读取的目录也会返回false
		is_dir($dir) or @ mkdir($dir, $mode, true);
		file_put_contents($filename, $data);
		@ chmod($filename, $mode);
		return true;
	}

	/**
	 * 移动目录
	 *
	 * @param string $filename        	
	 * @param string $destion        	
	 * @param boolean $overwrite 是否覆盖原文件
	 * @example File::moveDir('./lamson', '../lamsonphp');	//将当前目录下的lamson文件夹移到上一级目录下，并改名为lamsonphp
	 * @return boolean
	 */
	static function moveDir($olddir, $destion, $overwrite = false)
	{
		if(! is_dir($olddir))
		{
			return false;
		}
		is_dir($destion) or @ mkdir($destion, true, 0777);
		$olddir = self::addDs($olddir);
		$destion = self::addDs($destion);
		$arr = self::viewDir($olddir);
		foreach($arr['dirs'] as $d)
		{
			self::moveDir($olddir . $d, $destion . $d, $overwrite);
		}
		foreach($arr['files'] as $f)
		{
			self::moveFile($olddir . $f, $destion . $f, $overwrite);
		}
		return @ rmdir($olddir);
	}

	/**
	 * 移动文件
	 *
	 * @param string $filename        	
	 * @param string $destion        	
	 * @param boolean $overwrite 是否覆盖原文件
	 * @example File::moveFile('./lamson.txt', '../lamsonphp.txt');	//将当前目录下的lamson.txt移动到上一级目录下，并改名为lamsonphp.txt
	 * @return boolean
	 */
	function moveFile($oldname, $newname, $overwrite = false)
	{
		if(! file_exists($oldname) or (file_exists($newname) && ! $overwrite))
		{
			return false;
		}
		else
		{
			@ self::delFile($newname);
		}
		$dir = dirname($newname);
		is_dir($dir) or @ mkdir($dir, 0777, true);
		rename($oldname, $newname);
		return true;
	}

	/**
	 * 删除目录
	 *
	 * @param string $dir        	
	 * @param boolean $empty 是否仅仅清空目录
	 * @param bool $recursive 是否递归传递
	 * @example File::delDir('./del');	//将当前目录下的del文件夹删除
	 *          File::delDir('./del', true, true);	//将当前目录下的del文件夹和del下的文件夹都递归清空
	 *          File::delDir('./del', true, false);	//将当前目录下的del文件夹清空
	 * @return void
	 */
	static function delDir($dir, $empty = false, $recursive = true)
	{
		if(! is_dir($dir))
		{
			return false;
		}
		$dir = self::addDs($dir);
		$files = self::viewDir($dir);
		foreach($files['files'] as $v)
		{
			self::delFile($dir . $v);
		}
		foreach($files['dirs'] as $v)
		{
			self::delDir($dir . $v, $recursive && $empty);
		}
		$empty || rmdir($dir);
	}

	/**
	 * 复制目录
	 *
	 * @param string $olddir        	
	 * @param string $destion        	
	 * @param boolean $overwrite 是否覆盖原文件
	 * @param int $mode 权限模式
	 * @example File::copyDir('./demo', '../lamson');	//将当前目录下的demo文件夹复制到上一级，并改名为lamson
	 * @return boolean
	 */
	function copyDir($olddir, $destion, $overwrite = false, $mode = 0777)
	{
		if(! is_dir($olddir))
		{
			return false;
		}
		is_dir($destion) or @ mkdir($destion, true, 0777);
		$dir = self::addDs($dir);
		$destion = self::addDs($destion);
		$arr = self::viewDir($olddir);
		foreach($arr['dirs'] as $d)
		{
			self::copyDir($olddir . $d, $destion . $d, $overwrite);
		}
		foreach($arr['files'] as $f)
		{
			self::copyFile($olddir . $f, $destion . $f, $overwrite);
		}
		return true;
	}

	/**
	 * 复制文件
	 *
	 * @param string $filename        	
	 * @param string $filename        	
	 * @param boolean $overwrite 是否覆盖原文件
	 * @param int $mode 权限模式
	 * @example File::copyFile('./test.txt', '../lamson/demo.txt');	//将当前目录下的text.txt复制到上一级的lamson目录下，并改名为demo.txt
	 * @return boolean
	 */
	function copyFile($oldname, $newname, $overwrite = false, $mode = 0777)
	{
		if(! is_file($oldname) || (is_file($newname) && ! $overwrite))
		{
			return false;
		}
		self::delFile($newname);
		$dir = dirname($newname);
		is_dir($dir) or @ mkdir($dir, $mode, true);
		return copy($oldname, $newname);
	}

	/**
	 * 获取文件的扩展名
	 *
	 * @param string $filename        	
	 * @param boolean $dian        	
	 * @return string 返回已转换成小写字母的扩展名
	 */
	static function getExten($filename, $dot = false)
	{
		return (! $dot ? '' : '.') . strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}

	/**
	 * 删除文件
	 *
	 * @param mixed $filename        	
	 * @example File::delFile('./demo.txt');	//删除当前目录下的demo.txt文件
	 *          File::delFile( array('./demo.txt', '../text.txt') );	//删除当前目录下的demo.txt和上一级的text.txt这两个文件
	 * @return boolean
	 */
	static function delFile($filename)
	{
		if(is_array($filename))
		{
			array_map(__METHOD__, $filename);
		}
		else
		{
			return @ unlink($filename);
		}
	}

	/**
	 * 格式化文件大小
	 *
	 * @param string $bytes        	
	 * @return string 带单位的文件大小
	 */
	static function formatFileSize($bytes)
	{
		$size = sprintf("%u", $bytes);
		if(empty($size))
		{
			return '0 Bytes';
		}
		
		$sizename = array(' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
		$i = floor(log($size, 1024));
		return round($size / pow(1024, $i), 2) . $sizename[$i];
	}

	/**
	 * 将文件大小（带单位的)转换成字节
	 *
	 * @param string $bytes        	
	 * @return string 带单位的文件大小
	 */
	static function sizeToBytes($fileSize)
	{
		$arr = preg_match('/(\d+.?\d*)([a-z]+)/i', $fileSize, $match);
		$s = trim(round($match[1], 2));
		$d = strtoupper(substr(trim($match[2]), 0, 1));
		$sizename = array('B' => 0, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5, 'E' => 6, 'Z' => 7, 'Y' => 8);
		return in_array($d, $sizename) ? ($s * pow(1024, $sizename[$d])) : - 1;
	}

	/**
	 * 生成配置文件
	 *
	 * @param string $file 配置文件
	 * @param array $arr 存储配置内容的关联数组
	 * @param string $filetype 配置文件的格式类型 取值范围为 array, const
	 * @param bool $stripline 是否删除所有的空行
	 * @return void
	 */
	static function setCfgFile($file, $arr, $filetype = 'array', $stripline = false)
	{
		$partern = $replacement = array();
		foreach($arr as $k => $v)
		{
			if($stripline)
			{
				$v = str_replace(array("\r\n", "\n", "\r"), '', $v);
			}
			if($filetype == 'array')
			{
				$partern[$k] = "/'$k'=>'.*',\/\//isU";
				$replacement[$k] = "'$k'=>'$v',//";
			}
			elseif($filetype == 'const')
			{
				$partern[$k] = "/define\('$k', '.*'\);/isU";
				$replacement[$k] = "define('$k', '$v');";
			}
		}
		$str = file_get_contents($file);
		$str = preg_replace($partern, $replacement, $str);
		file_put_contents($file, $str);
	}
	
	// 在目录后面添加一个系统分隔符， 在windows下路径分隔符是\， 在linux 上路径的分隔符是/
	static function addDs($dir)
	{
		return rtrim($dir, DS) . DS;
	}
}