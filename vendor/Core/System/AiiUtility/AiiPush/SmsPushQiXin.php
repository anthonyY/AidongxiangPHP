<?php
namespace Core\System\AiiUtility\AiiPush;

/**
 * 企信通企业版
 *
 * @author WZ
 *        
 */
class SmsPushQiXin extends AiiPushBase
{
    private $_url = '';

    private $_userId = '';

    private $_password = '';
    
    private $_number = '';

    function __destruct()
    {
    }

    /**
     * 初始化
     */
    public function init()
    {
        $this->_url = SMS_URL;
        $this->_userId = SMS_USERID;
        $this->_password = SMS_PASSWOED;
        $this->_number = SMS_NUMBER;
    }
    
    private function makeSmsids($mobile) {
        $list = array();
        if (is_array($mobile)) {
            foreach ($mobile as $value) {
                $time = round(microtime(true) * 1000);
                $list [] = $value . ',' . $value . $time;
            }
        }
        else {
            $time = round(microtime(true) * 1000);
            $list [] = $mobile . ',' . $mobile . $time;
        }
        return implode(';', $list) . ';';
    }
    
    private function makeMsg($mobile, $content) {
        $smsids = $this->makeSmsids($mobile);
        $data = array(
            'corpid' => $this->_userId,
            'pswd' => $this->_password,
            'smsids' => $smsids,
            'msg' => $content
        );
        
        return $data;
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
    public function pushSingleDevice($mobile, $content)
    {
        $params = $this->makeMsg($mobile, $content);
        $url = $this->_url . 'mts.php';
        $result = $this->http_get($url, $params);
        
        $return = true;
        if ($result) {
            $this->myfile->putAtEnd($this->getStatusCode($result));
            $return = false;
        }
        return $return;
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
            $result = $this->pushSingleDevice($value['device_token'], $content);
            if ($result) {
                $res_arr['success'][] = $value['id'];
            }
            else {
                $res_arr['fail'][] = $value['id'];
            }
        }
        return $res_arr;
    }
    
    /**
     * 获取余额
     * 
     * @return string
     * @version 2015-12-1 WZ
     */
    public function getBalance() {
        $params = array(
            'corpid' => $this->_userId,
            'pswd' => $this->_password
        );
        $url = $this->_url . 'mts.php';
        $result = $this->http_get($url, $params);
        return $result;
    }
    
    private function getStatusCode($code)
    {
        $statuscode = array(
            '0' => '成功',
            '-1' => '缺少用户名',
            '-2' => '缺少密码',
            '-3' => '缺少smsids',
            '-4' => '缺少短信内容',
            '-5' => '缺少目标号码',
            '-6' => '用户名或密码错误',
            '-7' => '短信余额不足',
            '-8' => '短信内容长度超出限制',
            '-9' => '目标号码位数不正确',
            '-10' => '目标号码不是数字',
            '-11' => '目标号码是黑名单号码',
            '-12' => '关键字',
            '-13' => '账号状态不正常',
        );
        return (isset($statuscode[$code]) ? $statuscode[$code] : '未知错误') . '(' . $code . ')';
    }
}
?>