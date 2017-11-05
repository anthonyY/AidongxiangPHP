<?php
namespace Core\System\AiiUtility\AiiPush;

/**
 * IOS原生推送
 *
 * @author WZ
 *        
 */
class AiiIosPush extends AiiPushBase
{

    private $config_file = 'config/ios_config.php';

    private $_pem = array(1 => '/pem/push_prod.pem',2 => '/pem/push_dev.pem');

    private $_url = array(1 => 'ssl://gateway.push.apple.com:2195',2 => 'ssl://gateway.sandbox.push.apple.com:2195');

    /**
     * 根据设备号发送给单个设备
     *
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function pushSingleDevice($deviceToken, $content, $title = "", $args = array(), $environment = 0)
    {
        $res_arr = array(
            'ret_code' => 0
        );
        if (! in_array($environment,array(1,2))) {
            return array('ret_code' => '1,没有设置environment');
        }
        
        $apns = new Apns($environment == 1 ? 0 : 1, __DIR__ . $this->_pem[$environment]);
        try
        {
            //$apns->setRCA($rootpath); //设置ROOT证书
            $apns->connect(); //连接
            $apns->addDT($deviceToken); //加入deviceToken
            $apns->setText($content); //发送内容
//             $apns->setBadge(1); //设置图标数
//             $apns->setSound(); //设置声音
//             $apns->setExpiry(3600); //过期时间
            $apns->setCP('',$args); //自定义操作
            $apns->send(); //发送
            $result = $apns->readErrorMessage();
            if ($result) {
                $res_arr['ret_code'] = $result['statusMessage'];
            }
            $apns->disconnect();
        }
        catch(\Exception $e)
        {
            echo $e;
        }
        return $res_arr;
    }
    
    /**
     * 推送给所有iOS设备
     *
     * @param string $content 正文
     * @param string $title 标题，其实是没用，只是跟安卓推送保持一致所以才有的参数
     * @param array $args 其它参数
     * @version 2014-11-5 WZ
     */
    public function pushAllDevices($content, $title = '', $args = array())
    {
        if (! $this->_access_id || ! $this->_secret_key)
        {
            $this->myfile->putAtEnd('ios没有设置id和key');
            return;
        }
        $push = new XingeApp($this->_access_id, $this->_secret_key);
        $mess = new MessageIOS();
        $mess->setAlert($content);
        $mess->setBadge(1);
        if ($args) {
            $mess->setCustom((array)$args);
        }
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime);
        $ret = $push->PushAllDevices(XingeApp::DEVICE_IOS, $mess, $this->_iosenv);
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
    public function pushCollectionDevice($deviceTokens, $content, $title = '', $args = array(), $environment = 0)
    {
        $res_arr = array(
            'success' => array(),
            'fail' => array()
        );
        
        $apns = new Apns($environment == 1 ? 0 : 1, __DIR__ . $this->_pem[$environment]);
        //$apns->setRCA($rootpath); //设置ROOT证书
        $apns->connect(); //连接
        $apns->setText($content); //发送内容
        $apns->setCP('',$args); //自定义操作
        $ids = array();
        foreach ($deviceTokens as $value)
        {
            $ids = $value['id'];
            try
            {
                $apns->addDT($value['device_token']); //加入deviceToken
            }
            catch(\Exception $e)
            {
                echo $e;
            }
            
//             $result = $this->pushSingleDevice($value['device_token'], $content, $title, $args, $environment);
//             if (isset($result['ret_code']) && 0 === $result['ret_code'])
//             {
//                 $res_arr['success'][] = $value['id'];
//             }
//             else
//             {
//                 $res_arr['fail'][] = $value['id'];
//                 $content = $result['ret_code'];
//                 $this->myfile->putAtEnd($content);
//             }
        }
        $apns->send(); //发送
        $result = $apns->readErrorMessage();
        if ($result) {
            $res_arr['fail'] = $ids;
            $this->myfile->putAtEnd($result['statusMessage']);
        }
        else {
            $res_arr['success'] = $ids;
        }
        $apns->disconnect();
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
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', __DIR__ . $this->_pem[$environment]);
        
        $fp = stream_socket_client($this->_url[$environment], $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        stream_set_blocking($fp, 0);
        stream_set_write_buffer($fp, 0);
        if (! $fp)
        {
            $res_arr['ret_code'] = 2001 . ",Failed to connect: $err $errstr" . PHP_EOL;
            return $res_arr;
        }
        $msg_content = $this->makemsg($deviceToken, $content, $args);
        $result = fwrite($fp, $msg_content, strlen($msg_content));
        //         var_dump($result);
        //         var_dump(unpack("H*",stream_get_contents($fp)));
        if (! $result)
        {
            $res_arr['ret_code'] = 3001 . ',Message not delivered' . PHP_EOL . ' <br> ';
        }
        fclose($fp);
        
        
        $res_arr = array(
            'success' => array(),
            'fail' => array(),
            'errcode' => 0,
            'errmsg' => ''
        );
//         if (mb_strlen($content, 'utf-8') > 30)
//         {
//             $content = mb_substr($content, 0, 22, 'utf-8') . '...';
//         }
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
            $msg_content = $this->makemsg($value['device_token'], $content, $value['args']);
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
    private function makemsg($deviceToken, $content, $args = array())
    {
        $body = (array) $args;
        $body['aps'] = array(
            'alert' => $content,
            'sound' => 'default'
        );
        $payload = json_encode($body);
        $payloadLength = strlen($payload);
        // $timestamp = time()+1440;
        // 版本2接口
//         $msg = chr(1) . pack('L', 32) . pack('L', 0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        
        $msg  = pack('CNNnH*', 1, 32, 0, 32, $deviceToken);
        $msg .= pack('n', $payloadLength);
        $msg .= $payload;
        return $msg;
    }
}
?>