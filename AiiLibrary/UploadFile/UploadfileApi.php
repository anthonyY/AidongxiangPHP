<?php
namespace AiiLibrary\UploadFile;

if(! defined('IMAGE_SERVER'))
{
    include_once '/../../config.php';
}

use AiiLibrary\UploadFile\File;
/**
 * 上传文件类(支持PHP 5+)
 *
 * @author liujun (文豆版)
 * @version 5.0 最后修改时间 2012年09月30日
 * @link http://www.lamsonphp.com (http://www.wengdo.com)
 * @example
 * 		//把图片上传到上一级目录下的20121001文件夹下（假设当天所在年月为2012年10月01日）,大小不要超过1024Kb
 * 		$f = new Uploadfile('../', 1024, 1, 'Ymd');
 * 		//执行成功后$ok的值为最后一个上传文件的新名称（包含路径）
 * 		$newname = $f->uploadfile($oldimg);	//开始上传并删除$oldimg文件
 * 		echo $newname;	//新文件名称 (多文件上传时会以|隔开返回)
 *
 * 		//如果是多文件上传，上传成功后的所有新文件名称会存在按表单的file域结构的new_name里,可以借助以下方法来查看上传结果
 * 		print_r( $f->getUploadFileInfo() )
 *
 * 		//遇到应用错误时：该类在上传文件的过程中，如果遇到某个文件类型或大小不符合要求的，会把文件名称以及错误信息记录到$_error数组里，然后跳过该文件继续上传其他文件。等全部文件都处理完了，会把错误信息输出并终止程序运行，可以调用 $this->exit = false;来关闭此提示功能
 * 		$f->exitInfo();	//如果有错误
 * 需要依赖的资源：
类：File
 */
class UploadfileApi
{
    const VERSION = '5.0'; // 本类版本
    const CONVER = 1024; // 容量的折换率

    // 要存的路径
    public $path;
    //文件路径
    public $imgPath;

    // 允许上传的文件大小,单位 Kb ,默认为1M
    public $fileSize = 1024;
    // 允许上传的文件类型
    public $fileType = array(1 => array('gif', 'png', 'jpg', 'jpeg', 'bmp'), 2 => array('swf', 'flv'), 3 => array('rm', 'rmvb', 'avi', 'wmv', 'mpg', 'asf', 'mp3','mp4', 'wma', 'wmv', 'mid'), 4 => array('txt', 'doc', 'xls', 'ppt', 'docx', 'xlsx', 'pptx', 'pdf', 'xml', 'rar', 'zip', 'gzip', 'cab', 'iso', 'sql', 'csv', 'ini', 'conf', 'bin'), 6 => array('exe', 'com', 'scr', 'bat'));
    // 是否输出错误信息并终止程序
    public $exit = true;
    // 是否原名保存
    public $original = false;
    //新文件名
    public $newfilename;
    // 默认的上传文件类型
    protected $_type = 1;
    // 要上传的文件
    protected $_files = array();
    /**
     * 错误信息
     * (array) $_error['t'] 存储的是类型有误的文件名称
     * (array) $_error['s'] 存储的是大小超标的文件名称
     */
    public $_error = array();
    // 自动给每个要上传的文件分配序号
    private static $_num = 0;

    // 上传成功的文件的新名称(字符串类型，多文件时是'|'隔开)
    protected  $_lastName;
    public $lang = array('set_max_size' => '参数：实例化时设置的文件大小为 %u KB', 'up_max' => 'php.ini中的upload_max_filesize的值', 'err_bigmax' => '错误信息：$max_size不能设置为大于upload_max_filesize的值', 'err_page' => '发生错误的页面为', 'err_type' => '以下文件因类型不符合要求而没能上传成功', 'err_size' => '以下文件因大小超出 %s 而没能上传成功', 'return' => '返回');

    /**
     * 构造函数
     * @author liujun
     * @param string $path 文件上传后要存放的路径
     * @param int $max_size 设置最大文件大小，单位是Kb
     * @param int $type 文件类型 	1代表图片
     * @param string $sub_path 自动生成以日期格式的子目录
     * @param array $file_key 只上传指定的文件域
     */
    function __construct($path = NULL, $max_size = 1024, $type = 1, $sub_path = 'Ym', $file_key = array())
    {
        $this->fileType[5] = array_merge($this->fileType[2], $this->fileType[3]);
        $this->setFilePara($path, $max_size, $type, $sub_path, $file_key);
    }

    // 设置要存放的路径, 文件的大小，类型, 路径的形式以及要上传的文件的key
    function setFilePara($path = NULL, $max_size = 1024, $type = 1, $sub_path = 'Ym', $file_key = array())
    {
        if(isset($path))
        {
            $this->setPath($path, $sub_path);
        }
        if($max_size)
        {
            $this->setFileSize($max_size);
        }
        if($type)
        {
            $this->setType($type);
        }
        $this->setFiles($file_key);
    }

    /**
     * 构造函数
     * @author liujun
     * @param string $path 文件上传后要存放的路径,不存在时会自动创建
     * @param string $sub_path 自动生成以日期格式的子目录
     */
    function setPath($path = NULL, $sub_path = 'Ym')
    {
        if(! empty($path))
        {
            $path = rtrim($path, '/') . '/';
        }
        if(! empty($sub_path))
        {
            $path .= date($sub_path) . '/';
            $this->imgPath=date($sub_path) . '/';
        }

        if (1 & IMAGE_SAVE_MODE)
        {
            if (! is_dir($path))
            {
                mkdir($path, 0777, true); // 如果路径不存在，自动创建
            }
        }

        $this->path = $path;
    }

    // 设置文件大小
    function setFileSize($max_size = 1024)
    {
        $m = ini_get('upload_max_filesize');
        $max_byte = $max_size * self::CONVER;
        /**
         * ************** 注意：调用于类：File ************
         */
        if($max_byte > File::sizeToBytes($m))
        {
            /**
             * ************** 注意：调用于类：File ************
             */
            $info = '<p>' . sprintf($this->lang['set_max_size'], $max_size) . '(' . File::formatFileSize($max_byte) . ')，' . "{$this->lang[up_max]} $m</p>" . "<p>{$this->lang[err_bigmax]}</p>" . "<p>{$this->lang[err_page]}：$_SERVER[PHP_SELF]</p>";
            die($info);
        }
        $this->max_size = $max_size;
    }

    // 设置文件类型
    function setType($type = 1)
    {
        if(! array_key_exists($type, $this->fileType))
        {
            $type = 7;
        }
        $this->_type = $type;
    }

    // 设置要上传的文件
    function setFiles($file_key = array())
    {
        $this->_files = array();
        if(empty($file_key))
        {
            $this->_files = $_FILES;
        }
        else
        {
            // array_intersect_key((array)$file_key, array_flip($_FILES));
// 			$this->_files = array_intersect_key($_FILES, array_flip(( array ) $file_key));
            $this->_files = $file_key;

            // xtract($_FILES);
            // this->_files = compact((array)$file_key);

            /*
             * foreach((array)$file_key as $k => $v) { $this->_files[$v] = $_FILES[$v]; }
             */
        }
    }

    /*
     * ################################################################################# 外部调用此函数实现文件上传 参数说明 $unlink：	要删除的文件 $original：	是否原名保存,true或false，如果是字符串，则为指定名字保存（适用于单文件上传） 上传成功后将新名字返回（ '|'隔开 ） #################################################################################
     */
    function uploadfile($unlink = NULL, $original = NULL)
    {
        $this->_lastName = '';
        isset($original) && $this->original = $original;

        foreach($this->_files as $key => $value)
        {
            if(! is_array($value['size'])) // 如果是单文件
            {
                $this->_files[$key]['new_name'] = $this->_doUpload($value);
                if(isset($value['data']))
                {
                    $content = $value['data'];
                }
                else
                {
                    $content = $this->getUrlImage($value['tmp_name']);
                }
                $this->_files[$key]['md5'] = md5($content);
				//echo IMAGE_SERVER.$this->_files[$key]['new_name'];
                $imageinfo = $this->getImageSize($this->_files[$key]['new_name']);
                $this->_files[$key]['width'] = $imageinfo[0];
                $this->_files[$key]['height'] = $imageinfo[1];
            }
            /*
             * 如果文件域的name是数组形式，例如 $_FILES["img"]["size"] 或 $_FILES["img"]["size"][0] 或 $_FILES["img"]["size"]["ab"][0]
             */
            else
            {
                foreach($value['size'] as $k => $v)
                {
                    $this->_arrFile($key, $k, $v);
                }
            }
        }

        $this->exitInfo();

        /**
         * ************** 注意：调用于类：File ************
         */
        $this->_lastName && $unlink && File::delFile($unlink);

        // 上传成功后将新名字返回（ '|'隔开 ）
        return $this->_lastName ? substr($this->_lastName, 1) : str_replace('../', '', $unlink);
    }

    /**
     * 获取文件名切除/之前的内容
     * @param unknown $filename
     * @return string
     * @version 2014-12-17 WZ
     */
    function getFilename($filename)
    {
        $name = substr($filename, strrpos($filename, '/') + 1);
        return $name;
    }

    /**
     * 获取图片信息
     * @param unknown $filename
     * @return multitype:
     * @version 2014-12-17 WZ
     */
    function getImageSize($filename)
    {
        if (1 & IMAGE_SAVE_MODE && is_file($filename))
        {
            $name = $filename;
        }
        elseif (2 & IMAGE_SAVE_MODE)
        {
            $name = $this->getFilename($filename);
            $name = IMAGE_SERVER. UPLOAD_PATH . date('Ym/d/') . $name;
        }
        $imagesize = getimagesize($name);
        return $imagesize;
    }

    /*
     * ################################################################################# 外部调用此函数获取已上传文件的信息 #################################################################################
     */
    function getUploadFileInfo()
    {
        return $this->_files;
    }

    /**
     * 数组文件上传前的预处理程序，调用上传文件并将新生成的文件名赋值到$this->_files，保持$this->_files的原结构。
     * @author liujun
     * @return void
     */
    function _arrFile($key, $k, $v, $keystr = '')
    {
        $keystr .= "['$k']";
        if(is_array($v))
        {
            foreach($v as $vk => $vv)
            {
                $this->_arrFile($key, $vk, $vv, $keystr);
            }
        }
        else
        {
            $keyarr = array('name', 'type', 'tmp_name', 'error', 'size', 'data');
            foreach($keyarr as $r)
            {
                eval("\$f['$r'] = \$this->_files['$key']['$r']$keystr;");
            }
            $newname = $this->_doUpload($f);
            eval("\$this->_files['$key']['new_name']$keystr = '$newname';");
            if(isset($f['data']))
            {
                $content = $f['data'];
            }
            else
            {
                $content = $this->getUrlImage($f['tmp_name']);
            }
            $md5 = md5($content);
            eval("\$this->_files['$key']['md5']$keystr = '$md5';");
            $imageinfo = $this->getImageSize($newname);
            eval("\$this->_files['$key']['width']$keystr = '$imageinfo[0]';");
            eval("\$this->_files['$key']['height']$keystr = '$imageinfo[1]';");
        }
    }

    /**
     * 检测每个文件的大小和类型是否符合要求, 如果不符合则将不符合的文件记录下来，否则上传到指定路径。
     * @author liujun
     * @param array $f 要上传的单文件数组
     * @return 上传成功后的文件名
     */
    function _doUpload($f)
    {
        if($f['size'] <= 0)
        {
            return;
        }
        /**
         * ************** 注意：调用于类：File ************
         */
// 		$exten = File::getExten($f['name']); // 获取扩展名

        if(isset($f['data']))
        {
            $binary_file = $f['data'];
        }
        else
        {
            $binary_file = $this->getUrlImage($f['tmp_name']);
        }
        $bin = substr($binary_file,0,2);
        $strInfo = unpack("C2chars", $bin);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);

        $fileType = $this->check_type($typeCode);

        if($this->_type != 7 && ! in_array($fileType, $this->fileType[$this->_type]))
        { // 类型不符合要求
            $this->_error['t'][] = $f['name'];
            return;
        }
        elseif($f['size'] > $this->max_size * self::CONVER)
        { // 大小不符合要求
            $this->_error['s'][] = $f['name'];
            return;
        }
        $this->newfilename=date('His') .'_'. mt_rand(100, 999) . (self::$_num ++). '.' . $fileType;
        $newfile = $this->path . ((is_string($this->original) && $this->original != '') ? $this->original . '.' . $fileType : ($this->original ? $f['name'] : $this->newfilename));

        if (1 & IMAGE_SAVE_MODE)
        {
            File::mkFile($newfile, $binary_file);
            // 		move_uploaded_file($f['tmp_name'], $newfile);
            chmod($newfile, 0777);
        }

        if (2 & IMAGE_SAVE_MODE)
        {
            $url = IMAGE_SERVER.'GetFile.php';
            $params = array(
                'path' => date('Ym/d/'),
                'filename' => $this->newfilename,
                'content' => $binary_file
            );
            $this->urlExec($url, $params, 'post');
        }

        $this->_lastName .= '|' . str_replace('../', '', $newfile);
        return $newfile;
    }

    // 获取错误信息
    function getError()
    {
        if(! empty($this->_error['t']))
        {
            $e = "<dl><dt>{$this->lang[err_type]}：</dt><dd>" . implode('&nbsp;&nbsp;', $this->_error['t']) . '</dd></dl>';
        }
        if(! empty($this->_error['s']))
        {
            /**
             * ************** 注意：调用于类：File ************
             */
            $e .= '<dl><dt>' . sprintf($this->lang['err_size'], File::formatFileSize($this->max_size * self::CONVER)) . '：</dt><dd>' . implode('&nbsp;&nbsp;', $this->_error['s']) . '</dd></dl>';
        }
        return @$e;
    }

    // 是否输出错误信息，并终止程序
    function exitInfo()
    {
        if($this->exit && ($e = $this->getError()))
        {
            die($e);
        }
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
            case 102116:
                $fileType = 'mp4';
                break;
            case 8075:
            case 208207:
                $fileType = 'xls';  
                break; 
            default:
                $fileType = 'unknown';
        }
        return $fileType;
    }


    /**
     * 发起一个get或post请求
     * @param $url 请求的url
     * @param int $method 请求方式
     * @param array $params 请求参数
     * @param array $extra_conf curl配置, 高级需求可以用, 如
     * $extra_conf = array(
     *    CURLOPT_HEADER => true,
     *    CURLOPT_RETURNTRANSFER = false
     * )
     * @return bool|mixed 成功返回数据，失败返回false
     * @throws Exception
     */
    public static function urlExec($url,  $params = array(), $method = 'get', $extra_conf = array())
    {
        $params = is_array($params)? http_build_query($params): $params;
        //如果是get请求，直接将参数附在url后面
        if($method == 'get')
        {
            $url .= (strpos($url, '?') === false ? '?':'&') . $params;
        }

        //默认配置
        $curl_conf = array(
            CURLOPT_URL => $url,  //请求url
            CURLOPT_HEADER => false,  //不输出头信息
            CURLOPT_RETURNTRANSFER => true, //不输出返回数据
            CURLOPT_CONNECTTIMEOUT => 3 // 连接超时时间
        );

        //配置post请求额外需要的配置项
        if($method == 'post')
        {
            //使用post方式
            $curl_conf[CURLOPT_POST] = true;
            //post参数
            $curl_conf[CURLOPT_POSTFIELDS] = $params;
        }

        //添加额外的配置
        foreach($extra_conf as $k => $v)
        {
            $curl_conf[$k] = $v;
        }

        $data = false;
        try
        {
            //初始化一个curl句柄
            $curl_handle = curl_init();
            //设置curl的配置项
            curl_setopt_array($curl_handle, $curl_conf);
            //发起请求
            $data = curl_exec($curl_handle);
            if($data === false)
            {
                throw new \Exception('CURL ERROR: ' . curl_error($curl_handle));
            }
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }

        return $data;
    }

    /**
     * 获取图片内容
     * @param unknown $path
     * @return mixed
     * @version 2014-12-16 WZ
     */
    public function getUrlImage($path)
    {
        if (preg_match('/http\:\/\//i', $path))
        {
            $cookie_file = tempnam('./temp','cookie');
            $url = $path;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
            $content = curl_exec($ch);
        }
        else {
            $content = file_get_contents($path);
        }

        return $content;
    }
}