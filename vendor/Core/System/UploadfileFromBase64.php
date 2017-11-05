<?php
namespace Core\System;

use Core\System\File;

/**
 * 
 * @author WZ
 *
 */
class UploadfileFromBase64
{
    
	// 要存的路径
	public $path;
	//文件路径
	public $imgPath;
		
	/**
	 * 
	 * @param string $path
	 * @version 2016-9-7 WZ
	 */
	function __construct($path = NULL)
	{
	    $this->path = $path ? $path : __DIR__ . '/';
	    $this->imgPath = date('Ym/d') . '/';
	}
	
	/**
	 * 保存图片
	 * 
	 * @param unknown $data
	 * @return multitype:string unknown Ambigous <> 
	 * @version 2016-9-7 WZ
	 */
	function save($data) {
	    $strInfo = unpack("C2chars", $data);
	    $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
	    $fileType = $this->check_type($typeCode);
	    
	    $new_name = date('His') . '_' . mt_rand(1000, 9999) . '.' . $fileType;
	    $filename = $this->path . $this->imgPath . $new_name;
	    File::mkFile($filename, $data);
	    chmod($filename, 0775);
	    
	    $imagesize = getimagesize($filename);
	    
        return array(
            'path' => $this->imgPath,
            'filename' => $new_name,
//             'filepath' => $this->imgPath . $new_name,
            'width' => $imagesize[0],
            'height' => $imagesize['1'],
            'md5' => md5($data)
        );
	}
	
	/**
	 * 检查上传的文件类型
	 * @param unknown $typeCode
	 * @return string
	 */
	function check_type($typeCode) {
		switch ($typeCode)
		{
			case 255216:
				$fileType = 'jpg';
				break;
			case 7173:
				$fileType = 'gif';
				break;
			case 6677:
				$fileType = 'bmp';
				break;
			case 13780:
				$fileType = 'png';
				break;
			case 8273:
			    $fileType = 'avi';
			    break;
			case 5050:
			case 122100:
			    $fileType = 'txt';
			    break;
			default:
				$fileType = 'unknown';
		}
		return $fileType;
	}
}