<?php
namespace AiiLibray\AiiUtility\AiiPush;
require_once __DIR__ . '/configs/configs.php';

/**
 * 推送要用到记录推送信息的文件操作类
 *
 * @author WZ
 * @version 1.0.141031 重写
 * @method setFile 设置要写入的文件
 * @method putAtEnd 不影响原来的内容，把内容写入到文件的尾部
 * @method putAtStart 不影响原来的内容，把内容写入到文件的头部
 */
class AiiMyFile
{

    private $file = '';

    private $filename = '';

    function __construct($filename = LOG_FILENAME)
    {
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
                @mkdir($dir, 0777);
            }
            @touch($filename);
            @chmod($filename, 0777); // 生成不了文件也不报错了
        }
    }

    /**
     * 设置文件名
     * 
     * @param string $filename 带路径的文件名
     */
    public function setFile($filename)
    {
        $this->filename = $filename;
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
        $temp = file_get_contents($this->filename);
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
}
?>