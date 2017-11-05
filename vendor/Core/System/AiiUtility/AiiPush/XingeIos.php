<?php
namespace Core\System\AiiUtility\AiiPush;

/**
 * 基于信鸽开发的iOS类
 *
 * @author WZ
 *        
 */
class XingeIos extends AiiPushBase
{
    
    private $_pem = array(1 => '/pem/push_prod.pem',2 => '/pem/push_dev.pem');

    /**
     * 设置参数，子类注意改写此方法
     */
    public function init()
    {
        $this->_access_id = XINGE_IOS_ACCESS_ID;
        $this->_secret_key = XINGE_IOS_SECRET_KEY;
        $this->_iosenv = XINGE_IOSENV;
    }

    /**
     * 根据设备号发送给单个设备
     *
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function pushSingleDevice($deviceToken, $content, $title, $args = array(), $environment = 0)
    {
        if (! in_array($environment,array(1,2))) {
			$environment = $this->_iosenv;
            // return array('ret_code' => '1,没有设置environment');
        }
        if (! $this->_access_id || ! $this->_secret_key)
        {
            $this->myfile->putAtEnd('ios没有设置id和key');
            return;
        }
        $push = new XingeApp($this->_access_id, $this->_secret_key);
        $mess = new MessageIOS();
        $mess->setAlert($content);
        $mess->setBadge(1);
        $mess->setCustom((array)$args);
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime);
//         $ret = $push->PushSingleDevice($deviceToken, $mess, $this->_iosenv);
        $ret = $push->PushSingleDevice($deviceToken, $mess, $environment);
        if (! isset($ret['ret_code']) || 0 != $ret['ret_code']) {
            if (is_readable(__DIR__ . $this->_pem[$environment])) {
                $apns = new Apns($environment == 1 ? 0 : 1, __DIR__ . $this->_pem[$environment]);
                try
                {
                    // $apns->setRCA($rootpath); //设置ROOT证书
                    $apns->connect(); // 连接
                    $apns->addDT($deviceToken); // 加入deviceToken
                    $apns->setText($content); // 发送内容
                    $apns->setBadge(1); //设置图标数
                                              // $apns->setSound(); //设置声音
                                              // $apns->setExpiry(3600); //过期时间
                    $apns->setCP('', $args); // 自定义操作
                    $apns->send(); // 发送
                    $result = $apns->readErrorMessage();
                    if ($result)
                    {
                        $this->myfile->putAtEnd($result['statusMessage']);
                    }
                    $apns->disconnect();
                }
                catch (\Exception $e)
                {
                    echo $e;
                }
            }
        }
        return $ret;
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
        
        foreach ($deviceTokens as $value)
        {
            $result = $this->pushSingleDevice($value['device_token'], $content, $title, $args, $environment);
            if (isset($result['ret_code']) && 0 == $result['ret_code'])
            {
                $res_arr['success'][] = $value['id'];
            }
            else
            {
                $res_arr['fail'][] = $value['id'];
                $content = $result['ret_code'];
                $this->myfile->putAtEnd($content);
            }
        }
        return $res_arr;
    }
}
?>