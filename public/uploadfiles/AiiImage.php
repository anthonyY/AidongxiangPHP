<?php
include_once 'Image.php';
defined('ROOT_PATH') or define('ROOT_PATH', __DIR__ . '/');

/**
 * 图片类，业务处理类
 *
 * @author WZ
 *        
 */
class AiiImage
{

    /**
     * 是否进行检查域名，如果打开，注意移动端能不能访问。
     */
    private $check_domain = false;
    
    /**
     * 域名验证的域名
     */
    private $domin_permission = array();

    /**
     * 是否进行检查超时
     */
    private $check_time = true;

    /**
     * 水印设置对象
     */
    private $water;

    /**
     * 超时时间（单位：秒）
     */
    private $expiry_time = 600;
    
    private $key = 'image';
    
    /**
     * 文件名
     */
    public $filename = '';
    
    /**
     * 文件路径
     */
    public $path = '';
    
    public $width = 0;
    
    public $height = 0;
    
    public $type = 1;
    
    /**
     * 加密/验证的时间（格式：年-月-日 是:分:秒）
     */
    private $timestamp;
    
    /**
     * 加密/验证的md5字符串
     */
    private $md5;

    private $project;

    private $no_permission_path = 'NoPermission.jpg';

    private $upload_path = '';

    /**
     * 
     * @param string $project 项目名称，需要跟项目文件夹一致
     * @version 2014-11-24 WZ
     */
    public function __construct($project = '')
    {
        $this->project = $project;
        // $this->upload_path = SERVER_ROOT_PATH . '/project/' . $this->project . '/uploadfiles/';
    }
    
    /**
     * 设置，是否进行域名验证
     * @param boolean $check_domain
     * @param array $permission_array
     * @return \AiiImage\AiiImage
     * @version 2014-12-1 WZ
     */
    public function setCheckDomain($check_domain, array $permission_array)
    {
        $this->check_domain = (bool)$check_domain;
        $this->domin_permission = $permission_array;
        return $this;
    }
    
    /**
     * 设置，是否进行时间验证
     *
     * @param boolean $check_time
     * @return \AiiImage\AiiImage
     * @version 2014-11-24 WZ
     */
    public function setCheckTime($check_time)
    {
        $this->check_time = (bool)$check_time;
        return $this;
    }
    
    /**
     * 设置超时时间，默认600
     * 
     * @param number $expiry_time 超时时间，需要check_time为true时才有效，超出检测时间时，不返回正确的图片
     * @return \AiiImage\AiiImage
     * @version 2014-11-24 WZ
     */
    public function setExpiryTime($expiry_time)
    {
        $this->expiry_time = intval($expiry_time);
        return $this;
    }
    
    /**
     * 设置水印
     * 
     * @param Water $water 设置水印参数，具体参考 Water 类
     * @return \AiiImage\AiiImage
     * @version 2014-11-24 WZ
     */
    public function setWaterConfig(Water $water)
    {
        $this->water = $water;
        return $this;
    }

    /**
     * 图片解密并返回图片
     *
     * @version 2014-11-20 WZ
     */
    public function imageDecode()
    {
        $key = $this->getRequest('key');
        
        if (! $this->checkPermission($key))
        {
            $this->returnImage($this->no_permission_path);
        }
        $this->makeThumb($this->width, $this->height, $this->type);
        
        exit();
    }
    
    /**
     * 图片解密并返回图片
     *
     * @version 2014-11-20 WZ
     */
    public function getThumb()
    {
        $this->filename = $this->getRequest('filename');
        $width = $this->getRequest('width');
        $height = $this->getRequest('height');
        $type = $this->getRequest('type');
        $this->path = $this->getRequest('path');
        $this->makeThumb($width, $height, $type);
        
        exit();
    }
    
    /**
     * 获取缩略图
     * 
     * @param unknown $width
     * @param unknown $height
     * @param unknown $type
     * @version 2015-1-29 WZ
     */
    public function makeThumb($width, $height, $type)
    {
        $type = $type ? $type : 1; // 默认值，等比例缩放
        $this->path = trim($this->path,'/') . '/';
        $thumb_path = $this->upload_path . 'thumb/' . $width . 'X' . $height . 'X' . $type . '/' . $this->path;
        $thumb_filename = $thumb_path . $this->filename;
        $orgin_path = $this->upload_path . $this->path;
        $orgin = $orgin_path . $this->filename;
        
        if (! $width && ! $height)
        {
            $filename = $orgin;
        }
        else
        {
            $filename = $thumb_filename;
        }
        
        if (is_file($filename))
        {
            $this->returnImage($filename);
        }
        elseif (is_file($orgin))
        {
            $image = new Image();
            $image->nameRule = substr($this->filename, 0, strpos($this->filename, '.'));
            $image->makeThumb($orgin, $width, $height, $thumb_path, '#FFFFFF', 85, $type);
            if ($this->water && 1 == $this->water->type && $this->water->image_path)
            {
                $image->watermark($filename, $filename, 'img', $this->water->water_img, '', $this->water->color, $this->water->font, NULL, $this->water->pos, $this->water->alpha, $this->water->quality);
            }
            elseif ($this->water && 2 == $this->water->type && $this->water->water_txt)
            {
                $image->watermark($filename, $filename, 'txt', '', $this->water->water_txt, $color = '#FF0000', $this->water->font, $font_type = 'msyh.ttf', $this->water->pos, $this->water->alpha, $this->water->quality);
            }
            $this->returnImage($filename);
        }
        else
        {
            $thumb_path = $this->upload_path . 'thumb/' . $width . 'X' . $height . 'X' . $type . '/';
            if (! is_file($filename))
            {
                $image = new Image();
                $image->nameRule = substr($this->no_permission_path, 0, strpos($this->no_permission_path, '.'));
                $orgin = __DIR__ . '/' . $this->no_permission_path;
                $image->makeThumb($orgin, $width, $height, $thumb_path, '#FFFFFF', 85, $type);
            }
            $this->returnImage($thumb_path . $this->no_permission_path);
        }
    }

    /**
     * 检查访问权限
     *
     * @return boolean
     * @version 2014-11-13 WZ
     */
    private function checkPermission($key)
    {
        if (! $this->checkPermissionByDomain())
        {
            return false;
        }
        
        $this->decodeKey($key);
        if (! $this->checkPermissionByMd5())
        {
            return false;
        }
        if (! $this->checkPermissionByTime())
        {
            return false;
        }
        return true;
    }
    
    /**
     * 检查md5是否正确
     * 
     * @return boolean
     * @version 2015-1-29 WZ
     */
    private function checkPermissionByMd5()
    {
        $item = array(
            'filename' => $this->filename,
            'path' => $this->path,
            'width' => $this->width,
            'height' => $this->height,
            'type' => $this->type,
            'timestamp' => $this->timestamp
        );
        $md5 = $this->makeMd5($item);
        if ($md5 != $this->md5)
        {
            return false;
        }
        return true;
    }

    /**
     * 域名白名单
     *
     * @return boolean
     * @version 2014-11-13 WZ
     */
    private function checkPermissionByDomain()
    {
        if (! $this->check_domain)
        {
            return true;
        }
        $domain = $this->getDomain();
        $domain_permission = array();
        return in_array($domain, $domain_permission);
    }

    /**
     * 时间限制
     *
     * @return boolean
     * @version 2014-11-13 WZ
     */
    private function checkPermissionByTime()
    {
        if (! $this->timestamp || ($this->timestamp < date('Y-m-d H:i:s',time() - $this->expiry_time)) && $this->check_time)
        {
            return false;
        }
        return true;
    }

    /**
     * IP白名单
     *
     * @return boolean
     * @version 2014-11-13 WZ
     */
    private function checkPermissionByIp()
    {
        $permission_array = array();
        
        return in_array($_SERVER['REMOTE_ADDR'], $permission_array);
    }

    /**
     * 获取来访域名
     *
     * @return string
     * @version 2014-11-13 WZ
     */
    private function getDomain()
    {
        if (isset($_SERVER['HTTP_REFERER']))
        {
            preg_match('@^(?:http://)?([^/]+)@i', $_SERVER['HTTP_REFERER'], $matches);
            $domain = $matches[1];
            return $domain;
        }
        return '';
    }

    public function setValue($item)
    {
        if (is_object($item))
        {
            $this->filename = isset($item->filename) ? $item->filename : '';
            $this->path = isset($item->path) ? $item->path : '';
        }
        elseif (is_array($item))
        {
            $this->filename = isset($item['filename']) ? $item['filename'] : '';
            $this->path = isset($item['path']) ? $item['path'] : '';
        }
    }

    /**
     * 本对象转json字符串
     *
     * @return string
     * @version 2014-11-13 WZ
     */
    public function itemToStr()
    {
        $item = array(
            'filename' => $this->filename,
            'path' => $this->path,
            'width' => $this->width,
            'height' => $this->height,
            'type' => $this->type,
            'timestamp' => date('Y-m-d H:i:s')
        );
        $item['md5'] = $this->makeMd5($item);
        $json = json_encode($item);
        return $json;
    }
    
    /**
     * 根据参数生成md5
     * 
     * @param unknown $item
     * @return string
     * @version 2015-1-29 WZ
     */
    private function makeMd5($item)
    {
        $key = implode(',', $item) . ',' . $this->key;
        $md5 = md5($key);
        return $md5;
    }

    /**
     * json字符串转本对象
     *
     * @param string $string
     *            json字符串
     * @return \Core\System\AiiImage\AiiImageItem
     * @version 2014-11-13 WZ
     */
    public function strToItem($string)
    {
        $key = base64_decode($string);
        $json = json_decode($key);
        if ($json)
        {
            foreach ($this as $key => $value)
            {
                if (isset($json->$key))
                {
                    $this->$key = $json->$key;
                }
            }
        }
        return $this;
    }

    /**
     * 从item对象生成key
     *
     * @param AiiImageItem $item            
     * @return string
     * @version 2014-11-13 WZ
     */
    public function encodeKey()
    {
        $string = $this->itemToStr();
        $key = base64_encode($string);
        return $key;
    }

    /**
     * 解密并返回item对象
     *
     * @param string $key
     *            加密后的key
     * @return \Core\System\AiiImage\AiiImageItem
     * @version 2014-11-13 WZ
     */
    public function decodeKey($key)
    {
        $this->strToItem($key);
        if ($this->path)
        {
            $this->path = $this->changePath($this->path); // 迎合数据库与文件目录的差异
        }
        return $this;
    }

    /**
     * 获取参数
     *
     * @param string $key            
     * @return Ambigous <string, unknown>
     * @version 2014-11-20 WZ
     */
    private function getRequest($key)
    {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
    }

    /**
     * 返回图像
     *
     * @param string $filename            
     * @version 2014-11-20 WZ
     */
    private function returnImage($filename)
    {
        $this->setHeader($filename);
        if (is_file($filename))
        {
            echo file_get_contents($filename);
        }
        elseif (is_file(ROOT_PATH . $filename))
        {
            echo file_get_contents(ROOT_PATH . $filename);
        }
        elseif (is_file(ROOT_PATH . '/' . $filename))
        {
            echo file_get_contents(ROOT_PATH . '/' . $filename);
        }
        exit();
    }
    
    /**
     * 设置header使得图片正常显示
     * 
     * @param string $filename
     * @version 2014-11-24 WZ
     */
    private function setHeader($filename)
    {
        $extension = $this->getExtension($filename);
        $array = array(
            'jpg' => 'jpeg',
            'jpeg' => 'jpeg',
            'png' => 'png',
            'gif' => 'gif',
            'bmp' => 'bmp'
        );
        if (key_exists($extension,$array))
        {
            header("Content-type: image/".$array[$extension]);
        }
    }
    
    /**
     * 获取文件扩展名
     * 
     * @param string $file
     * @return mixed
     * @version 2014-11-24 WZ
     */
    private function getExtension($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * 迎合数据库与文件目录的差异
     *
     * @version 2014-11-21 WZ
     */
    private function changePath($path)
    {
        return preg_replace('/(\d{6})(\d{2})\//i','$1/$2/',$path);substr($path, 0, 6) . '/' . substr($path, 6);
    }

    /**
     * 保存远方的图片
     *
     * @version 2014-11-21 WZ
     */
    public function saveImage()
    {
        $path = $this->changePath($this->getRequest('path'));
        $filename = $this->getRequest('filename');
        $content = $this->getRequest('content');
        if ($path && $filename && $content)
        {
            $file = new File();
            $upload_path = trim($this->upload_path . $path, '/') . '/';
            $file->mkFile($upload_path . $filename, $content);
            return 'Good';
        }
        else
        {
            return 'Fails';
        }
    }
}