<?php
namespace Core\System\AiiUtility\AiiPush;

/**
 * 新短信发送平台
 *
 * @author WZ
 *        
 */
class SmsPushYunCe extends AiiPushBase
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
        $data['apiKey'] = $this->_userId;
        $data['content'] = $content;
        $data['op'] = "Sms.send";
        $data['phone'] = $mobile;
        $data['templateId'] = $this->_number;
        $data['ts'] = floor((microtime(true)*1000));
        
        //array_multisort($data,SORT_ASC);
        $str = '';
        foreach ($data as $k => $v)
        {
            $str .= $k.'='.$v;
        }$str .= $this->_password;
//         var_dump($str);
        $data['sig'] = md5($str);
        
        return json_encode($data);
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
        $result = $this->http_post($this->_url, $params);
        
        $result_json = json_decode($result, true);
        $return = true;
        if (! $result_json || 1000 != $result_json['code'])
        {
            $this->myfile->putAtEnd($result);
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
            '999' => '服务启动中',
            '1000' => '成功',
            '3002' => '无效的参数（apiKey）',
            '3003' => '模版id不存在',
            '3004' => '变量数据与模版不匹配',
            '3005' => '超过50个变量标签',
            '3008' => '手机号不合法',
            '3009' => '数字签名错误',
            '3010' => '数字签名为空',
            '9999' => '其他错误',
            '10000' => '服务异常',
            '10010' => '请求的报文超过限制',
            '10011' => '无效参数ts',
            '10012' => '无效接入方',
            '10014' => '账户余额不足',
            '10016' => '短信内容不合法',
        );
        return $statuscode;
    }
}
?>