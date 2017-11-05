<?php
namespace Core\System\AiiUtility\AiiPush;
require_once __DIR__ . '/config/config.php';

/**
 * 推送要用到记录推送信息的文件操作类
 *
 * @author WZ
 * @version 1.0.141031 重写
 */
class AiiMyFile
{

    private $file = '';

    private $filename = '';

    function __construct($filename = LOG_FILENAME)
    {
        if(! defined('APP_PATH'))
        {
            require_once __DIR__ . '/../../../../../config.php';
        }
        $this->setFile($filename);
    }

    function __destruct()
    {
        if ($this->file)
        {
            $this->closeFile();
            unset($this->file);
        }
    }

    private function openFile()
    {
        $this->file = fopen($this->filename, "ab");
    }

    private function closeFile()
    {
        @fclose($this->file);
    }

    /**
     * 检查文件是否存在，如果不存在就创建
     *
     * @param string $filename            
     */
    public function checkFile($filename)
    {
        if (! is_file($filename))
        {
            $dir = substr($filename, 0, strrpos($filename, '/'));
            if (! is_dir($dir))
            {
                @mkdir($dir, 0777, true);
            }
            @touch($filename);
            @chmod($filename, 0777); // 生成不了文件也不报错了
        }
    }

    /**
     * 不影响原来的内容，把内容写入到文件的尾部
     *
     * @param unknown $content
     *            日志内容
     */
    public function putAtEnd($content)
    {
        $this->checkFile($this->filename);
        if (! $this->file)
        {
            $this->openFile();
        }
        $temp = date('Y-m-d H:i:s') . " " . $content . "\r\n";
        @fwrite($this->file, $temp);
        $this->closeFile();
    }

    /**
     * 设置文件名
     *
     * @param string $filename
     *            带路径的文件名
     */
    public function setFile($filename)
    {
        $this->filename = $filename;
    }

    /**
     * 不影响原来的内容，把内容写入到文件的头部
     *
     * @param string $content
     *            正文
     * @return void
     * @author WZ
     * @version 1.0.20140429 WZ
     *         
     */
    public function putAtStart($content)
    {
        $this->checkFile($this->filename);
        $temp = @file_get_contents($this->filename);
        $temp = date('Y-m-d H:i:s') . " $content" . "\r\n" . $temp;
        @file_put_contents($this->filename, $temp);
    }

    /**
     * 覆盖原文件，把内容写入到文件
     *
     * @param unknown $content
     *            日志内容
     */
    public function putIn($content)
    {
        $this->checkFile($this->filename);
        @file_put_contents($this->filename, $content);
    }

    /**
     * 把目标文件修改成可以访问的日志文件
     *
     * @return \Core\System\AiiPush\AiiMyFile
     */
    public function setFileToPublicLog()
    {
        $filename = APP_PATH . '/public/push_log/log_' . date('Ymd') . '.txt';
        $this->setFile($filename);
        return $this;
    }
    
    /**
     * 缓存写入文件
     *
     * @param unknown $filename
     *            文件名格式 region 或 Admin/category
     * @param unknown $param
     *            数组
     * @return boolean
     */
    public function setCache($filename, $param)
    {
        $filename = $this->getCacheFilename($filename);
    
        if ($param)
        {
            if (! is_file($filename))
            {
                $dir = substr($filename, 0, strrpos($filename, '/'));
                if (! is_dir($dir))
                {
                    @mkdir($dir, 0777);
                }
                @touch($filename);
                @chmod($filename, 0777);
            }
    
            if (! is_array($param))
            {
                $param = array(
                    $param
                );
            }
            $data = json_encode($param);
            @file_put_contents($filename, $data);
        }
        else
        {
            @unlink($filename);
        }
        return true;
    }
    
    /**
     * 获得缓存内容
     *
     * @param string $filename
     *            文件名格式 region 或 Admin/category
     * @return boolean mixed
     * @version 1.0.141020 WZ
     */
    public function getCache($filename)
    {
        $filename = $this->getCacheFilename($filename);
        if (! is_file($filename))
        {
            return false;
        }
        $data = file_get_contents($filename);
        if ($data)
        {
            $param = json_decode($data);
            return $param;
        }
    
        return false;
    }
    
    /**
     * 获取缓存更新时间
     *
     * @param string $filename
     * @return boolean|number
     * @version 1.0.141020 WZ
     */
    public function getCacheTime($filename)
    {
        $filename = $this->getCacheFilename($filename);
        if (! is_file($filename))
        {
            return false;
        }
        return filemtime($filename);
    }
    
    /**
     * 简单文件名转化缓存文件路径
     *
     * @param string $filename
     * @return string
     * @version 1.0.141020 WZ
     */
    public function getCacheFilename($filename)
    {
        return APP_PATH . '/Cache/' . $filename . '.php';
    }
}
?>