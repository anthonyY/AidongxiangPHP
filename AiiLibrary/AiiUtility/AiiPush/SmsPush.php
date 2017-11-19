<?php
namespace Core\System\AiiPush;

require_once __DIR__ . '/sms/Client.php';

/**
 * 基于亿美短信开发的短信类
 *
 * @author WZ
 *        
 */
class SmsPush
{

    private $url = '';

    private $serialNumber = '';

    private $password = '';

    private $sessionKey = '';

    private $myfile;

    /**
     * 构造函数
     */
    function __construct()
    {
        $this->myfile = new AiiMyFile();
        $this->init();
    }

    function __destruct()
    {
    }

    /**
     * 初始化
     */
    private function init()
    {
        $this->url = SMS_URL;
        $this->serialNumber = SMS_SERIALNUMBER;
        $this->password = SMS_PASSWOED;
        $this->sessionKey = SMS_SESSIONKEY;
    }

    /**
     * 批量发送
     * 
     * @param array $deviceTokens
     *            id,device_token
     * @param string $content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function pushCollectionDevice($deviceTokens, $content)
    {
        $res_arr = array(
            'success' => array(),
            'fail' => array(),
            'errcode' => 0
        );
        
        $mobile_arr = array();
        $id_arr = array();
        foreach ($deviceTokens as $value)
        {
            if ($value['device_token'] and strlen($value['device_token']) == 11)
            {
                $mobile_arr[] = $value['device_token'];
            }
            $id_arr[] = $value['id'];
        }
        $result = $this->sendMessage($mobile_arr, $content);
        if ($result == 1)
        {
            $res_arr['success'] = $id_arr;
        }
        else
        {
            $res_arr['fail'] = $id_arr;
            $res_arr['errcode'] = 13001;
            $this->myfile->putAtEnd(sprintf(STATUS_13001, count($res_arr)));
        }
        return $res_arr;
    }

    /**
     * 发送一个
     * 
     * @param array $mobile
     *            电话号码
     * @param string $content
     *            发送的内容
     * @return bool $result
     */
    public function sendbyone($mobile, $content)
    {
        $result = $this->sendMessage(array(
            $mobile
        ), $content);
        return $result;
    }

    /**
     *
     * @param array $mobile_array
     *            设备码
     * @param string $content
     *            推送内容
     * @return $msg ios用的msg
     */
    private function sendMessage($mobile_array, $content)
    {
        $client = new \Client($this->url, $this->serialNumber, $this->password, $this->sessionKey);
        $client->setOutgoingEncoding('UTF-8');
        $statusCode = $client->sendSMS($mobile_array, $content);
        if ($statusCode != 0)
        {
            $this->myfile->putAtEnd(sprintf("statusCode:" . $statusCode . " " . $client->getError()));
            return false;
        }
        else
        {
            return true;
        }
        return $statusCode;
    }

    public function login()
    {
        /**
         * 下面的操作是产生随机6位数 session key
         * 注意: 如果要更换新的session key，则必须要求先成功执行 logout(注销操作)后才能更换
         * 我们建议 sesson key不用常变
         */
        // $sessionKey = $client->generateKey();
        // $statusCode = $client->login($sessionKey);
        $client = new \Client($this->url, $this->serialNumber, $this->password);
        $client->setOutgoingEncoding('UTF-8');
        $statusCode = $client->login();
        echo $client->getError();
        echo "处理状态码:" . $statusCode . "<br/>";
        if ($statusCode != null && $statusCode == "0")
        {
            // 登录成功，并且做保存 $sessionKey 的操作，用于以后相关操作的使用
            echo "登录成功, session key:" . $client->getSessionKey() . "<br/>";
        }
        else
        {
            // 登录失败处理
            echo "登录失败";
        }
    }
}
?>