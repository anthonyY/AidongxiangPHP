<?php
namespace Core\System\AiiUtility\AiiPush;

/**
 * 短信宝短信发送平台
 *
 * @author WZ
 *        
 */
class SmsPushBao extends AiiPushBase
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
    
    private function makeMsg($mobile, $content) {
    
        $data = $this->_url."sms?u=".$this->_userId."&p=".$this->_password."&m=".$mobile."&c=".urlencode($content);
        
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
        $data = $this->makeMsg($mobile, $content);
        $result = file_get_contents($data);
        
        $statuscode = $this->getStatusCode();

        $return = true;
        if ($result != 0)
        {
            $this->myfile->putAtEnd($statuscode[$result]);
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
    
    private function getStatusCode()
    {
        $statuscode = array(
    		"0" => "短信发送成功",
    		"-1" => "参数不全",
    		"-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
    		"30" => "密码错误",
    		"40" => "账号不存在",
    		"41" => "余额不足",
    		"42" => "帐户已过期",
    		"43" => "IP地址限制",
    		"50" => "内容含有敏感词"
        );
        return $statuscode;
    }
}
?>