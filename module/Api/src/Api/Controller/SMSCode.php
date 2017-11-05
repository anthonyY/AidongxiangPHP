<?php

namespace Api\Controller;

use Api\Controller\Request\SMSCodeRequest;
use Core\System\AiiUtility\AiiPush\AiiPush;
use Core\System\AiiUtility\AiiPush\AiiMyFile;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\PredicateSet;
//error_reporting(E_ALL & ~E_NOTICE);
/**
 * 短信验证
 * 1.获取验证码，2.进行验证
 *
 * @author WZ
 *        
 */
class SMScode extends CommonController
{
    /**
     * type:2时排除这个用户
     */
    public $user_id;

    /**
     * 获取验证码
     *
     * @var 1
     */
    const MOBILE_VALIDATE_ACTION_GET = 1;

    /**
     * 验证验证码
     *
     * @var 2
     */
    const MOBILE_VALIDATE_ACTION_CHECK = 2;
    
    /**
     * 临时/未验证
     *
     * @var 0
     */
    const MOBILE_VALIDATE_STATUS_TEMP = 0;
    
    
    /**
     * 已验证
     *
     * @var unknown
     */
    const MOBILE_VALIDATE_STATUS_USED = 1;
    

    public function __construct()
    {
        $this->myRequest = new SMSCodeRequest();
        parent::__construct();
    }

    /**
     * 返回一个数组或者Result类
     *
     * @return \Api21\Controller\BaseResult
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        
        $request->action = $request->action ? $request->action : 1;
        $user_id = $request->user_id;
        if(!$user_id)
        {
            return STATUS_USER_NOT_LOGIN;
        }
        
        if (self::MOBILE_VALIDATE_ACTION_GET == $request->action)
        {
            if (in_array($request->type,array(1,2,3,4))){
                // 获取验证码
                $result = $this->getModel('SMSCode')->sendCode($request->mobile, $request->type, $user_id);
            }else{
                return STATUS_PARAMETERS_INCOMPLETE;
            }

            if ($result['status']) {
                $response->status = $result['status'];
                if (isset($result['description']) && $result['description']) {
                    $response->description = $result['description'];
                }
                return $response;
            }
        
            $response->status = STATUS_SUCCESS; // 成功或未知错误
            $response->id = $result['id'];
            if (! SMSCODE_SWITCH)
            {
                $response->code = $result['code'];
            }
        }
        elseif (self::MOBILE_VALIDATE_ACTION_CHECK == $request->action)
        {
            // action 2 验证验证码
            if (CHECK_SMSCODE)
            {
                $mobile = $request->mobile;
                if($request->type == 2){
                    $where=new Where();
                    $where->equalTo('id', $user_id);
                    $mobile_info=$this->getModel('user')->getOne($where,array('mobile'),'user');
                    $mobile = $mobile_info['mobile'];
                }
                $check = $this->getModel('SMSCode')->checkCode($mobile,$request->where->code,$request->type);
            }
            else
            {
                // 短信接口还没开通，所有验证码都可以通过
                $check = true;
            }
            if ($check && $request->type == 3) {
                $this->getModel('User')->updateData(array('mobile' => $request->mobile), array('id' => $user_id), 'user');
            }
            $response->status = ($check ? STATUS_SUCCESS : STATUS_CAPTCHA_ERROR);
        }
        else
        {
            $response->status = STATUS_PARAMETERS_INCOMPLETE;
        }
        return $response;
    }

    /**
     * 根据类型和手机号码，验证用户信息
     *
     * @param Number $type            
     * @param String $mobile            
     * @return Ambigous <\Api21\Controller\Ambigous, multitype:, boolean, ArrayObject, NULL, \ArrayObject, unknown>
     */
    private function checkUserInfo($type, $mobile)
    {
        $where = new Where();
        $where->equalTo('mobile', $mobile);
        
        $user_info = $this->getModel('user')->getOne($where, array('*'), 'user');
        //var_dump($user_info);exit;
        $status = STATUS_SUCCESS;
        switch ($type) {
            case 1:
            case 3:
            
                if ($user_info) {
                    $status = STATUS_USER_EXIST;
                }
                break;
            case 2:
            case 4:
          
                if (! $user_info) {
                    $status = STATUS_USER_NOT_EXIST;
                }
                break;
        }
        return $status;
    }

    /**
     * 更新验证状态
     *
     * @author WZ
     * @param number $id
     *            短信id
     */
    public function complete($id)
    {
        // 已验证
        $set = array(
            'status' => self::MOBILE_VALIDATE_STATUS_USED
        );
        
        $where = array(
            'id' => $id
        );
        return $this->getModel('SMSCode')->updatedata($set, $where,'sms_code');
    }

    /**
     * 2014.3.24 hexin
     * 生成手机验证码
     * <br />2014/3/25 WZ 改
     *
     * @param number $type            
     * @return string number
     */
    public function makeSmsCode($type)
    {
        $code = $this->makeCode(4, self::CODE_TYPE_NUMBER);
        return $code;
    }
    
    /**
     * 新的一条记录
     * @return unknown
     * @version 2015-8-6 WZ
     */
    public function newSmsRecord($mobile, $type, $code, $result) {
        $data = array(
            'ip'=>$this->getIP(),
            'mobile' => $mobile,
            'code' => $code,
            'status' => $result ? self::MOBILE_VALIDATE_STATUS_TEMP : self::MOBILE_VALIDATE_STATUS_FAIL,
            'count' => 1,
            'user_id' => $this->getUserId(),
            'session' => session_id(),
            'expire' => date('Y-m-d H:i:s', time() + SMSCODE_EXPIRE),
            'type' => $type,
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime()
        );
        $id = $this->getModel('SMSCode')->insertdata($data,'sms_code');
        return $id;
    }

    /**
     * 2014/3/25
     * 短信模版
     *
     * @author WZ
     * @param number $type
     * @param string $code            
     * @param
     *            array 其它参数
     * @return string
     */
    public function smsTemplate($type, $code, array $args)
    {
        switch ($type)
        {
            case '1':
            case '2':
            case '3':
            case '4':
                //1注册；2重新绑定手机；3重置密码；4完善个人资料；
                $template = TEMPLATE_SMS_CAPTCHA_1;
                $content = sprintf($template, $code);
                return $content;
                break;
            case '5':
                //社团F码审核发放成功
                if(!(isset($args['fcode']) && isset($args['expire']))){
                    return false;
                    die;
                }
                $fcode = $args['fcode'];
                $expire = $args['expire'];
                $template = TEMPLATE_SMS_CAPTCHA_2;
                $content = sprintf($template, $fcode, $expire);
                return $content;
                break;
            case '6':
                //关注日中上了全国排行/省份排行
                if(!isset($args['area'])){
                    return false;
                    die;
                }
                $area = $args['area'];
                $template = TEMPLATE_SMS_CAPTCHA_3;
                $content = sprintf($template, $area);
                return $content;
                break;
            case '7':
                //纪念日前一天的早上10:00
                if(!(isset($args['date']) && isset($args['anniversary']))){
                    return false;
                    die;
                }
                $date = $args['date'];
                $anniversary = $args['anniversary'];
                $template = TEMPLATE_SMS_CAPTCHA_4;
                $content = sprintf($template, $date, $anniversary);
                return $content;
                break;
            case '8':
                //纪念日当天的早上10:00
                if(!(isset($args['date']) && isset($args['anniversary']))){
                    return false;
                    die;
                }
                $date = $args['date'];
                $anniversary = $args['anniversary'];
                $template = TEMPLATE_SMS_CAPTCHA_5;
                $content = sprintf($template, $date, $anniversary);
                return $content;
                break;
            case '9':
                //申请让爱成书
                $template = TEMPLATE_SMS_CAPTCHA_6;
                $content = $template;
                return $content;
                break;
            case '10':
                //在关注圈推荐中被设置为推荐
                $template = TEMPLATE_SMS_CAPTCHA_7;
                $content = $template;
                return $content;
                break;
            case '11':
                //被邀请密聊。且被邀请的手机号未注册时发送
                $template = TEMPLATE_SMS_CAPTCHA_8;
                $content = $template;
                return $content;
                break;
            default:
                
                break;
        }
        
    }

    /**
     * 发送多条短信的
     *
     * @author WZ
     * @param unknown $content            
     * @param array $mobile            
     * @return multitype:boolean
     */
    public function smsPush($content, $mobile)
    {
        $push = new AiiPush();
        $return = false;
        if (! is_array($mobile)) {
            $mobile = array($mobile);
        }
        
        foreach ($mobile as $m)
        {
            if (SMSCODE_SWITCH)
            {
                if ($m)
                {
                    $result = $push->pushSingleDevice($m, 16, $content);
                    $return = (isset($result['success']) && $result['success']) ? true : false;
                }
            }
            else
            {
                $return = true;
            }
            
            if (PUSH_LOG_SWITCH)
            {
                // 开启了推送与短信的日志记录
                if (isset($result))
                {
                    if ($result)
                    {
                        $temp = '短信，短信发送成功， mobile：' . $m . '，content：' . $content;
                    }
                    else
                    {
                        $temp = '短信，短信发送失败不能进行验证， mobile：' . $m . '，content：' . $content;
                    }
                }
                else
                {
                    $temp = '短信，没有开启短信发送，mobile：' . $m . '，content：' . $content;
                }
                $myfile = new AiiMyFile();
                $myfile->setFileToPublicLog()->putAtStart($temp);
            }
        }
        return $return;
    }
    
    /**
     * 检查验证码是否合法
     * 
     * @return boolean
     * @version 2015-8-6 WZ
     */
    public function checkCode() {
        $request = $this->getAiiRequest();
        $where = array(
            'mobile' => $request->mobile,
            'type' => $request->type
        );
        $mobile_validate = $this->getModel('SMSCode')->getOne($where, $part = array('*'),'sms_code' );
        if ($mobile_validate && $mobile_validate->expire > $this->getTime()) {
            /*
             * $used用这个判断，每个验证码只能用一次
             */
            if (self::MOBILE_VALIDATE_STATUS_USED == $mobile_validate->status) {
                $used = true;
            }
            else {
                $used = false;
            }
            
            if (QUICK_SMSCODE_SWITCH && substr($request->mobile, - 4) == $request->where->code) {
                // 手机号码后四位
                $check = true;
            }
            elseif ($mobile_validate->code == $request->where->code) {
                // 匹配正确的短信验证码
                $check = true;
            }
            else {
                $check = false;
            }
        
            if ($check) {
                if (! $used) {
                    $this->complete($mobile_validate->id);
                }
                else {
                    $check = false; // 屏蔽这条可使得验证码在一定时间内可重复使用，不然每条验证码只能使用一次。
                }
            }
        }
        else
        {
            $check = false;
        }
        return $check;
    }
    
//     /**
//      * 发送验证码
//      *
//      * @param unknown $mobile 手机号
//      * @param number $type 类型，根据项目业务不同， 具体看checkUserInfo方法
//      * @return array status,description,id,code
//      * @version 2016-5-24 WZ
//      */
//     function sendCode($mobile, $type = 0) {
        
//         //echo "2222";exit;
//         // var_dump($mobile);exit;
//         $mobile = trim($mobile);
//         $type = (int) $type;
//         if (empty($mobile) || empty($type))
//         {
//             if (! $mobile) {
//                 return array('status' => STATUS_PARAMETERS_INCOMPLETE, 'description' => '手机不能为空');
//             }
//             return array('status' => STATUS_PARAMETERS_INCOMPLETE);
//         }
    
//         if (SMS_LIMIT_IP || SMS_LIMIT_MOBILE || SMS_LIMIT_SESSION_ID || SMS_LIMIT_DAY) {
//             $ip = $this->getIP();
//             //var_dump($ip);exit;
//             $session_id = $this->getSessionId();
//             //var_dump($session_id);exit;
//             $where = new Where();
//             $where->between('timestamp', date('Y-m-d H:i:s', strtotime('-1 day')), $this->getTime());
//             $data = $this->getModel('SMSCode')->getDataByCache(null, 'codeOneDay', $where,'sms_code');
            
//            // var_dump($data);exit;
//             $ip_count = 0;
//             $mobile_count = 0;
//             $session_count = 0;
//             foreach ($data as $value) {
//                 if ($ip == $value['ip']) {
//                     $ip_count += $value['count'];
//                 }
//                 if ($mobile == $value['mobile']) {
//                     $mobile_count += $value['count'];
//                 }
//                 if ($session_id && $session_id == $value['session']) {
//                     $session_count += $value['count'];
//                 }
//             }
//             if (SMS_LIMIT_IP && $ip_count >= SMS_LIMIT_IP) {
//                 $this->saveLog($ip, $mobile, $session_id, 1);
//                 return array('status' => STATUS_MD5); // 安全验证不通过
//             }
//             if (SMS_LIMIT_MOBILE && $mobile_count >= SMS_LIMIT_MOBILE) {
//                 $this->saveLog($ip, $mobile, $session_id, 2);
//                 return array('status' => STATUS_MD5); // 安全验证不通过
//             }
//             if (SMS_LIMIT_SESSION_ID && $session_count >= SMS_LIMIT_SESSION_ID) {
//                 $this->saveLog($ip, $mobile, $session_id, 3);
//                 return array('status' => STATUS_MD5); // 安全验证不通过
//             }
//             if (SMS_LIMIT_DAY && count($data) >= SMS_LIMIT_DAY) {
//                 $this->saveLog($ip, $mobile, $session_id, 4);
//                 return array('status' => STATUS_MD5); // 安全验证不通过
//             }
//         }
    
//         /*
//          * 根据type 检查相关内容
//          * 用户相关进行验证
//          */
//         $user_status = $this->checkUserInfo($type, $mobile);
//         if ($user_status != STATUS_SUCCESS) {
//             return array('status' => $user_status);
//         }
    
//         $new = true;
//         $code = $this->makeSmsCode($type) . '';
//         // 检查10分钟内有没有发送过验证码，有则找回这条验证码
//         $where = array(
//             'mobile' => $mobile,
//             'status' => self::MOBILE_VALIDATE_STATUS_TEMP,
//             'type' => $type
//         );
//         $mobile_validate = $this->getModel('SMSCode')->getOne($where, array('*'), 'sms_code');
//         $sent = false;
//         if ($mobile_validate && $mobile_validate->expire > $this->getTime())
//         {
//             $id = $mobile_validate->id;
//             $code = $mobile_validate->code;
//             $new = false;
//             $result = true;
//             if (time() - strtotime($mobile_validate->timestamp_update) < 60) {
//                 // 60秒内不重复发
//                 $sent = true;
//                 //                     return STATUS_TOO_FAST;
//             }
//         }
//         if (! $sent) {
//             // 短信内容
//             $content = $this->smsTemplate($type, $code, array());
//             if(!$content){
//                 return array('status' => STATUS_UNKNOWN, 'id' => 0, 'code' => '');
//             }
//             $result = $this->smsPush($content, $mobile); // 发送
    
//             if ($new)
//             {
//                 // 第一次发送
//                 $id = $this->newSmsRecord($mobile, $type, $code, $result);
//             }
//             else
//             {
//                 // 重复发送
//                 $this->getModel('SMSCode')->updateKey($id, 1, 'count', 1,'sms_code');
//                 $this->getModel('SMSCode')->updateData(array(
//                     'expire' => date('Y-m-d H:i:s', time() + SMSCODE_EXPIRE)
//                 ), array(
//                     'id' => $id
//                 ),'sms_code');
//             }
//         }
    
//         return array('status' => STATUS_SUCCESS, 'id' => $id, 'code' => $code);
//     }
    
    /**
     * 获取用户IP
     * @return Ambigous <unknown, string>
     * @version 2015年11月17日
     * @author liujun
     */
    public function getIP()
    {
        $ip = '';
        if (getenv('HTTP_CLIENT_IP'))
        {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        elseif (getenv('HTTP_X_FORWARDED_FOR'))
        {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_X_FORWARDED'))
        {
            $ip = getenv('HTTP_X_FORWARDED');
        }
        elseif (getenv('HTTP_FORWARDED_FOR'))
        {
            $ip = getenv('HTTP_FORWARDED_FOR');
    
        }
        elseif (getenv('HTTP_FORWARDED'))
        {
            $ip = getenv('HTTP_FORWARDED');
        }
        else
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    /**
     * 验证码用日志记录
     *
     * @param unknown $ip IP地址
     * @param unknown $mobile 手机号码
     * @param unknown $session_id 移动端Session
     * @param unknown $type 1IP,2手机,3session,4类型
     * @version 2016-5-24 WZ
     */
    private function saveLog($ip, $mobile, $session_id, $type) {
        $content = '';
        switch ($type) {
            case 1:
                $content = '相同IP重复请求超过' . SMS_LIMIT_IP;
                break;
            case 2:
                $content = '相同手机重复请求超过' . SMS_LIMIT_MOBILE;
                break;
            case 3:
                $content = '相同Session重复请求超过' . SMS_LIMIT_SESSION_ID;
                break;
            case 4:
                $content = '每日发出次数超过' . SMS_LIMIT_DAY;
                break;
        }
        $myfile = new AiiMyFile();
        $myfile->setFileToPublicLog()->putAtStart('短信拦截：' . $content . ',ip:' . $ip  . ',mobile:' . $mobile . ',session:' . $session_id);
    }
}
