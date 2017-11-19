<?php
namespace Core\System\AiiPush;

/**
 * IOS原生推送
 *
 * @author WZ
 *        
 */
class AiiIosPush
{

    private $config_file = 'config/ios_config.php';

    private $_pem = '';

    private $_url = '';

    private $myfile;

    /**
     * 构造函数
     */
    function __construct()
    {
        $this->myfile = new AiiMyFile();
        $this->init();
    }

    /**
     * 初始化
     */
    private function init()
    {
        if (is_file(PUSH_ROOT . '/' . $this->config_file))
        {
            require_once PUSH_ROOT . '/' . $this->config_file;
            $this->_pem = IOS_PEM;
            $this->_url = IOS_URL;
        }
        else
        {
            $this->myfile->put(sprintf(STATUS_12001, $this->config_file), 1);
            die();
        }
    }

    /**
     * 发送
     * 
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function sendbyone($deviceToken, $content)
    {
        $res_arr = array(
            'errcode' => 0,
            'errmsg' => ''
        );
        $ctx = stream_context_create();
        $msg_content = $this->makemsg($deviceToken, $content);
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->_pem);
        
        $fp = stream_socket_client($this->_url, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        if (! $fp)
        {
            $res_arr['errmsg'] = "Failed to connect: $err $errstr" . PHP_EOL;
            $res_arr['errcode'] = 2001;
            return $res_arr;
        }
        
        $result = fwrite($fp, $msg_content, strlen($msg_content));
        if (! $result)
        {
            $res_arr['errcode'] = 3001;
            $res_arr['errmsg'] = 'Message not delivered' . PHP_EOL . ' <br> ';
        }
        fclose($fp);
        return $res_arr;
    }

    /**
     * 批量发送
     * 
     * @param array $deviceTokens
     *            id,device_token
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function send($deviceTokens, $content)
    {
        $res_arr = array(
            'success' => array(),
            'fail' => array(),
            'errcode' => 0,
            'errmsg' => ''
        );
        if (mb_strlen($content, 'utf-8') > 30)
        {
            $content = mb_substr($content, 0, 22, 'utf-8') . '...';
        }
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->_pem);
        
        foreach ($deviceTokens as $value)
        {
            $fp = stream_socket_client($this->_url, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
            if (! $fp)
            {
                $res_arr['errmsg'] = "Failed to connect: $err $errstr" . PHP_EOL;
                $res_arr['errcode'] = 2001;
                return $res_arr;
            }
            $msg_content = $this->makemsg($value['device_token'], $content);
            $result = fwrite($fp, $msg_content, strlen($msg_content));
            if (! $result)
            {
                $res_arr['errcode'] = 3001;
                $res_arr['errmsg'] = 'Some message not delivered' . PHP_EOL . ' <br /> ';
                $res_arr['fail'][] = $value['id'];
            }
            else
            {
                $res_arr['success'][] = $value['id'];
            }
            fclose($fp);
        }
        return $res_arr;
    }

    /**
     *
     * @param string $deciceToken
     *            设备码
     * @param string $content
     *            推送内容
     * @return $msg ios用的msg
     */
    private function makemsg($deviceToken, $content)
    {
        $body['aps'] = array(
            'alert' => $content,
            'sound' => 'default'
        );
        $payload = json_encode($body);
        // $timestamp = time()+1440;
        // 版本2接口
        $msg = chr(1) . pack('L', 32) . pack('L', 0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        return $msg;
    }
}
?>