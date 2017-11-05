<?php
namespace Core\System\AiiUtility;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class Log {

    protected $dbAdapter;

    public function __construct($adapter = 'a') {
        $this->dbAdapter = $adapter;
    }

    public function emerg($str) {
        $this->getLogger('emerg')->emerg($str);
    }

    public function alert($str) {
        $this->getLogger('alert')->alert($str);
    }

    public function crit($str) {
        $this->getLogger('crit')->crit($str);
    }

    public function err($str) {
        $this->getLogger('err')->err($str);
    }

    public function warn($str) {
        $this->getLogger('warn')->warn($str);
    }

    public function notice($str) {
        $this->getLogger('notice')->notice($str);
    }

    public function info($str) {
        $this->getLogger('info')->info($str);
    }

    public function debug($str) {
        $this->getLogger('debug')->debug($str);
    }

    protected function getLogger($type) {
        $logName = APP_PATH . '/public/logs/' . date('Ymd') . '/'.$this->dbAdapter.'/'.$type.'.txt';
        if (! is_file($logName))
        {
            $dir = substr($logName, 0, strrpos($logName, '/'));
            if (! is_dir($dir))
            {
                @mkdir($dir, 0777, true);
            }
            @touch($logName);
            @chmod($logName, 0777); // 生成不了文件也不报错了
        }
        $writer = new Stream($logName);
        $logger = new Logger();
        $logger->addWriter($writer);
        return $logger;
    }
}