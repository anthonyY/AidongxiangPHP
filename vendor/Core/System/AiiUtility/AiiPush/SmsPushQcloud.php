<?php
namespace Core\System\AiiUtility\AiiPush;

/**
 * 新短信发送平台
 *
 * @author WZ
 *        
 */
class SmsPushQcloud extends AiiPushBase
{
    private $_url = '';

    private $_userId = '';

    private $_password = '';
    
    private $_tpl_id = 0;
    
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
        $this->_tpl_id = SMS_NUMBER;
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
    
    function pushSingleDevice($phoneNumber, $msg) {
        
        $random = rand(100000, 999999);
        $curTime = time();
        $wholeUrl = $this->_url . "?sdkappid=" . $this->_userId . "&random=" . $random;
        $data = new \stdClass();
        $tel = new \stdClass();
        $tel->nationcode = "".'86';
        $tel->mobile = "".$phoneNumber;
    
        $data->tel = $tel;
        $data->type = (int)0;
        $data->msg = '【一起聚餐】'.$msg;
        $data->sig = hash("sha256","appkey=".$this->_password."&random=".$random."&time=".$curTime."&mobile=".$phoneNumber, FALSE);
        $data->time = $curTime;
        $data->extend = "";
        $data->ext = "";
        $result = $this->sendCurlPost($wholeUrl, $data);
        $result_json = json_decode($result, true);
        $return = true;
        if (! $result_json || $result_json['result'])
        {
            $this->myfile->putAtEnd($result_json['errmsg'] . ' - ' . $result);
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
}
?>