<?php

namespace Api\Model;

use Zend\Db\Sql\Where;
use Core\System\AiiUtility\AiiPush\AiiMyFile;
use Core\System\AiiUtility\AiiPush\AiiPush;
use Core\System\AiiUtility\Log;
class SMSCodeModel extends CommonModel{

    /**
     * 临时/未验证
     *
     * @var 0
     */
    const MOBILE_VALIDATE_STATUS_TEMP = 0;
    /**
     * 发送失败
     *
     * @var unknown
     */
    const MOBILE_VALIDATE_STATUS_FAIL = 2;
    /**
     * 已验证
     *
     * @var unknown
     */
    const MOBILE_VALIDATE_STATUS_USED = 1;
    
    
    protected $table = 'sms_code';
    /**
     * 发送验证码
     *
     * @param unknown $mobile 手机号
     * @param number $type 类型，根据项目业务不同， 具体看checkUserInfo方法
     * @return array status,description,id,code
     * @version 2016-5-24 WZ
     */
    function sendCode($mobile, $type = 0, $user_id) {
        $user_id = (int) $user_id;
        $mobile = trim($mobile);
        $type = (int) $type;
        if (empty($mobile) || empty($type))
        {
            if (! $mobile) {
                return array('status' => STATUS_PARAMETERS_INCOMPLETE, 'description' => '手机不能为空');
            }
            return array('status' => STATUS_PARAMETERS_INCOMPLETE);
        }

        if (!$this->is_mobile_phone($mobile)) {
            return array(
                "status" => "400",
                "description" => "手机号码格式错误！",
            );
        }

        if (SMS_LIMIT_IP || SMS_LIMIT_MOBILE || SMS_LIMIT_SESSION_ID || SMS_LIMIT_DAY) {
            $ip = $this->getIP();
            $where = new Where();
            $where->between('timestamp', date('Y-m-d H:i:s', strtotime('-1 day')), $this->getTime());
            $data = $this->fetchAll($where,array('*'),'sms_code');
           
            $ip_count = 0;
            $mobile_count = 0;
            foreach ($data as $value) {
                if ($ip == $value['ip']) {
                    $ip_count += $value['count'];
                }
                if ($mobile == $value['mobile']) {
                    $mobile_count += $value['count'];
                }
            }
            if (SMS_LIMIT_IP && $ip_count >= SMS_LIMIT_IP) {
                $this->saveLog($ip, $mobile, $user_id, 1);
                return array('status' => STATUS_MD5); // 安全验证不通过
            }
            if (SMS_LIMIT_MOBILE && $mobile_count >= SMS_LIMIT_MOBILE) {
                $this->saveLog($ip, $mobile, $user_id, 2);
                return array('status' => STATUS_MD5); // 安全验证不通过
            }
            if (SMS_LIMIT_DAY && count($data) >= SMS_LIMIT_DAY) {
                $this->saveLog($ip, $mobile, $user_id, 4);
                return array('status' => STATUS_MD5); // 安全验证不通过
            }
        }

        /*
         * 根据type 检查相关内容
         * 用户相关进行验证
         */
        $user_status = $this->checkUserInfo($type, $mobile);
        if ($user_status != STATUS_SUCCESS) {
            return array('status' => $user_status);
        }

        $new = true;
        $code = $this->makeSmsCode($type) . '';
        // 检查10分钟内有没有发送过验证码，有则找回这条验证码
        $where = array(
            'mobile' => $mobile,
            'status' => self::MOBILE_VALIDATE_STATUS_TEMP,
            'type' => $type
        );
        $mobile_validate = $this->getOne($where, array('*'), 'sms_code');
        $sent = false;
        if ($mobile_validate && $mobile_validate->expire > $this->getTime())
        {
            $id = $mobile_validate->id;
            $code = $mobile_validate->code;
            $new = false;
            $result = true;
            if (time() - strtotime($mobile_validate->timestamp_update) < 60) {
                // 60秒内不重复发
                $sent = true;
                //                     return STATUS_TOO_FAST;
            }
        }

        if (! $sent) {
            // 短信内容
            $content = $this->smsTemplate($type, $code, array());
            if(!$content){
                return array('status' => STATUS_UNKNOWN, 'id' => 0, 'code' => '');
            }
            $result = $this->smsPush($content, $mobile); // 发送

            if ($new)
            {
                // 第一次发送
                $id = $this->newSmsRecord($mobile, $type, $code, $result, $user_id);
            }
            else
            {
                // 重复发送
                $this->updateKey($id, 1, 'count', 1,'sms_code');
                $this->updateData(array(
                    'expire' => date('Y-m-d H:i:s', time() + SMSCODE_EXPIRE)
                ), array(
                    'id' => $id
                ),'sms_code');
            }
        }
    
        return array('status' => STATUS_SUCCESS, 'id' => $id, 'code' => $code);
    }
    
    
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
        $log = new Log('sendsms');
        $log->info('短信拦截：' . $content . ',ip:' . $ip  . ',mobile:' . $mobile . ',session:' . $session_id);

//        $myfile = new AiiMyFile();
//        $myfile->setFileToPublicLog()->putAtStart('短信拦截：' . $content . ',ip:' . $ip  . ',mobile:' . $mobile . ',session:' . $session_id);
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
        $where->equalTo('delete', 0);
        $user_info = $this->getOne($where, array('*'), "user");
        $status = STATUS_SUCCESS;
        switch ($type) {
            case 1:// 注册
                if ($user_info) {
                    $status = STATUS_USER_EXIST;
                }
                break;
            case 2: // 更换手机第一步
                if (! $user_info) {
                    $status = STATUS_USER_NOT_EXIST;
                }
                break;
            case 3: // 更换手机第二步
                if ($user_info) {
                    $status = STATUS_USER_EXIST;
                }
                break;
            case 4: // 忘记密码
                $session_user_id = $_SESSION['user_id'];
                $session_user_info = $this->getOne(array('id' => $session_user_id) , array('*'), "user");
                if($session_user_info['mobile']){
                    if($session_user_info['mobile'] != $mobile){
                        return STATUS_USER_MOBILE_USED_ERROR;
                    }
                }else{
                    if ($user_info) {
                        $status = STATUS_USER_EXIST;
                    }
                }
                break;
        }
        return $status;
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
            case '4':
            case '3':
                //1注册；2重新绑定手机；3重置密码；4完善个人资料；
                $template = TEMPLATE_SMS_CAPTCHA;
                $content = sprintf($template, $code);
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
                $log = new Log('sendsms');
                $log->info($temp);
//                $myfile = new AiiMyFile();
//                $myfile->setFileToPublicLog()->putAtStart($temp);
            }
        }
        return $return;
    }
    
    /**
     * 新的一条记录
     * @return unknown
     * @version 2015-8-6 WZ
     */
    public function newSmsRecord($mobile, $type, $code, $result,$user_id) {
        $data = array(
            'mobile' => $mobile,
            'code' => $code,
            'status' => $result ? self::MOBILE_VALIDATE_STATUS_TEMP : self::MOBILE_VALIDATE_STATUS_FAIL,
            'count' => 1,
            'user_id' => $user_id,
            'expire' => date('Y-m-d H:i:s', time() + SMSCODE_EXPIRE),
            'type' => $type,
            'ip' => $this->getIP(),
            'session' => session_id(),
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime()
        );
        $id = $this->insertdata($data, "sms_code");
        return $id;
    }
    
    
    /**
     * 验证码表->验证
     *
     * @param unknown $smscode_id
     * @param unknown $mobile
     * @author YSQ
     */
    public function authCode($smscode_id, $mobile)
    {
        $code_info = $this->getOne(array(
            'id' => $smscode_id,
            'mobile' => $mobile
        ), array('*'), "sms_code");
        if (! $code_info) {
            return $this->back('电话号码不正确');
        }
        else {
            if ($code_info['status'] != 1) {
                return $this->back('验证不成功');
            }
        }
    }
    
//     /**
//      * 检查验证码是否合法
//      *
//      * @return boolean
//      * @version 2015-8-6 WZ
//      */
//     public function checkCode($mobile, $code, $type) {
//         if (!$this->is_mobile_phone($mobile) || !in_array($type, array(1,2,3,4))) {
//             return array(
//                 "code" => "400",
//                 "message" => "手机格式错误！"
//             );
//         }
    
//         $where = array(
//             'mobile' => $mobile,
//             'type' => $type
//         );
    
//         $mobile_validate = $this->getOne($where, $part = array('*'), "sms_code", array('id' => 'desc'));
    
//         if ($mobile_validate && $mobile_validate->expire > $this->getTime()) {
//             /*
//              * $used用这个判断，每个验证码只能用一次
//              */
//             if (self::MOBILE_VALIDATE_STATUS_USED == $mobile_validate->status) {
//                 $used = true;
//             }
//             else {
//                 $used = false;
//             }
//             $check = false;
//             if ($mobile_validate->code == $code) {
//                 // 匹配正确的短信验证码
    
//                 $check = array(
//                     "code" => "200",
//                     "message" => "验证成功！",
//                     "data" => array(
//                         'mobile' => $mobile,
//                         "password" => $password
//                     )
//                 );
//             }
//             if ($check) {
//                 if (! $used) {
//                     $this->complete($mobile_validate->id);
//                 } else {
//                     $check = array(
//                         "code" => "401",
//                         "message" => "验证码错误！"
//                     ); // 屏蔽这条可使得验证码在一定时间内可重复使用，不然每条验证码只能使用一次。
//                 }
//             } else {
//                 $check = array(
//                     "code" => "402",
//                     "message" => "验证码错误！"
//                 );
//             }
//         } else {
//             $check = array(
//                 "code" => "403",
//                 "message" => "验证码错误！"
//             );
//         }
//         return $check;
//     }
    
    
    /**
     * 检查验证码是否合法
     *
     * @return boolean
     * @version 2015-8-6 WZ
     */
    public function checkCode($mobile, $code, $type) {

        if (!$this->is_mobile_phone($mobile)) {
            return false;
        }
        $where = array(
            'mobile' => $mobile,
            'type' => $type
        );
        $mobile_validate = $this->getOne($where, $part = array('*'),'sms_code' );
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
    
            if (QUICK_SMSCODE_SWITCH && substr($mobile, - 4) == $code) {
                // 手机号码后四位
                $check = true;
            }
            elseif ($mobile_validate->code == $code) {
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
        return $this->updateData($set, $where, "sms_code");
    }
    
    /**
     * 检查验证码是否合法
     * 修改手机（新旧手机验证）
     * @return boolean
     * @version 2015-8-6 WZ
     */
    public function checkMobileCode($type, $mobile, $code) {
        if (!$this->is_mobile_phone($mobile) || !in_array($type, array(1,2,3,4))) {
            return array(
                "code" => "400",
                "message" => "手机格式错误！"
            );
        }
    
        $where = array(
            'mobile' => $mobile,
            'type' => $type
        );
    
        $mobile_validate = $this->getOne($where, $part = array('*'), "sms_code", array('id' => 'desc'));
    
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
            $check = false;
            if (CHECK_SMSCODE || $mobile_validate->code == $code) {
                // 匹配正确的短信验证码
                $check = array(
                    "code" => "200",
                    "message" => "验证成功！",
                    "data" => array(
                        'mobile' => $mobile,
                    )
                );
            }
    
            if ($check) {
                if (! $used) {
                    $this->complete($mobile_validate->id);
                } else {
                    $check = array(
                        "code" => "400",
                        "message" => "验证码错误！"
                    ); // 屏蔽这条可使得验证码在一定时间内可重复使用，不然每条验证码只能使用一次。
                }
            } else {
                $check = array(
                    "code" => "400",
                    "message" => "验证码错误！"
                );
            }
        } else {
            $check = array(
                "code" => "400",
                "message" => "验证码错误！"
            );
        }
        return $check;
    }
    
    /**
     * 检查验证码是否已验证
     *
     * @param unknown $id
     * @param unknown $mobile
     * @version 2016-9-5 WZ
     */
    function checkSmscode($id, $mobile, $type) {
        $info = $this->getOne(array('id' => $id, 'mobile' => $mobile, 'status' => 1, 'type' => $type));
        if ($info) {
            if ($info['expire'] > $this->getTime()) {
                return array('code' => 200, 'message' => '验证码有效');
            }
            else {
                return array('code' => 400, 'message' => '验证码过期');
            }
        }
        return array('code' => 400, 'message' => '验证码未验证');
    }

    
    
//     /**
//      * 已验证
//      *
//      * @var unknown
//      */
//     const MOBILE_VALIDATE_STATUS_USED = 1;
    
    /**
     * 查看验证码 是否已经验证
     *
     * @author WZ
     * @param int $type
     *            1.注册,2.绑定手机,3.重置密码
     * @param int $id
     *            短信验证码id
     * @param string $mobile
     *            手机号码
     * @return Ambigous false|object 短信表记录信息
     * @version 1.0.140325
     */
    public function checkSmsComplete($type, $id, $mobile = '')
    {
        $status = STATUS_SUCCESS;
        if (! $id || ! $type)
        {
            $status = STATUS_PARAMETERS_INCOMPLETE;
        }
        else
        {
            $where = array(
                'id' => $id
            );
    
            $data = $this->getOne($where,array('*'),'sms_code');

            if (! $data || self::MOBILE_VALIDATE_STATUS_USED != $data['status'] || ($mobile && $mobile != $data['mobile']) || $type != $data['type'])
            {
                // 数据不存在 或 未验证 或 手机不匹配 或 请求类型不匹配 返回错误
                $status = STATUS_TIMEOUT;
            }
        }
        return $status;
    }
    
   
}

