<?php
namespace AiiLibrary\AiiUtility\AiiPush;

/**
 * 新短信发送平台
 *
 * @author WZ
 *        
 */
class SmsPushMw extends AiiPushBase
{

    private $url = '';

    private $userid = '';

    private $password = '';

    function __destruct()
    {
    }

    /**
     * 初始化
     */
    public function init()
    {
        $this->url = MW_SMS_URL;
        $this->userid = MW_SMS_USERID;
        $this->password = MW_SMS_PASSWOED;
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
        $result = $this->sendMessage(array(
            $mobile
        ), $content);
        if ('' !== $result)
        {
            $this->myfile->putAtEnd($result);
        }
        return $result;
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
        if ($result === '')
        {
            $res_arr['success'] = $id_arr;
        }
        else
        {
            $res_arr['fail'] = $id_arr;
            $this->myfile->putAtEnd($result);
        }
        return $res_arr;
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
        set_time_limit(0);
        
        $action = $this->url;
        $action .= '/MongateSendSubmit';
        // 梦网短信平台
        require_once (__DIR__."/DemoSrc/function.php");
        require_once (__DIR__."/DemoSrc/Client.php");
        $sms = new \Client($action,2);
        
        $smsInfo = array();
        $smsInfo['userId'] = $this->userid;
        $smsInfo['password'] = $this->password;
        $smsInfo['pszSubPort'] = '*';
        $smsInfo['flownum'] = 0;
        $smsInfo['pszMsg'] = $content;
        $smsInfo['pszMsg'] = str_replace("\\\\","\\",$smsInfo['pszMsg']);
//         var_dump($action ,$smsInfo, $mobile_array);exit;
        $result = $sms->sendSMS($smsInfo, $mobile_array);
//         var_dump($result);
        $statuscode = $this->getStatusCode();
        $strRet = GetCodeMsg($result, $statuscode);
//         var_dump($strRet);
        return $strRet;
    }
    
    private function getStatusCode()
    {
        $statuscode = array(
            '-1' => '参数为空。信息、电话号码等有空指针，登陆失败',
            '-2' => '电话号码个数超过100',
            '-10' => '申请缓存空间失败',
            '-11' => '电话号码中有非数字字符',
            '-12' => '有异常电话号码',
            '-13' => '电话号码个数与实际个数不相等',
            '-14' => '实际号码个数超过100',
            '-101' => '发送消息等待超时',
            '-102' => '发送或接收消息失败',
            '-103' => '接收消息超时',
            '-200' => '其他错误',
            '-999' => 'web服务器内部错误',
            '-10001' => '用户登陆不成功',
            '-10002' => '提交格式不正确',
            '-10003' => '用户余额不足',
            '-10004' => '手机号码不正确',
            '-10005' => '计费用户帐号错误',
            '-10006' => '计费用户密码错',
            '-10007' => '账号已经被停用',
            '-10008' => '账号类型不支持该功能',
            '-10009' => '其它错误',
            '-10010' => '企业代码不正确',
            '-10011' => '信息内容超长',
            '-10012' => '不能发送联通号码',
            '-10013' => '操作员权限不够',
            '-10014' => '费率代码不正确',
            '-10015' => '服务器繁忙',
            '-10016' => '企业权限不够',
            '-10017' => '此时间段不允许发送',
            '-10018' => '经销商用户名或密码错',
            '-10019' => '手机列表或规则错误',
            '-10021' => '没有开停户权限',
            '-10022' => '没有转换用户类型的权限',
            '-10023' => '没有修改用户所属经销商的权限',
            '-10024' => '经销商用户名或密码错',
            '-10025' => '操作员登陆名或密码错误',
            '-10026' => '操作员所充值的用户不存在',
            '-10027' => '操作员没有充值商务版的权限',
            '-10028' => '该用户没有转正不能充值',
            '-10029' => '此用户没有权限从此通道发送信息',
            '-10030' => '不能发送移动号码',
            '-10031' => '手机号码(段)非法',
            '-10032' => '用户使用的费率代码错误',
            '-10033' => '非法关键词'
        );
        return $statuscode;
    }
    
    function getBalance() {
        $action = $this->url;
        $action .= '/MongateQueryBalance';
        // 梦网短信平台
        require_once (__DIR__."/DemoSrc/function.php");
        require_once (__DIR__."/DemoSrc/Client.php");
        $sms = new \Client($action,2);
        
        $smsInfo = array();
        $smsInfo['userId'] = $this->userid;
        $smsInfo['password'] = $this->password;
        $result = $sms->GetMoney($smsInfo);
        return $result;
    }
}
?>