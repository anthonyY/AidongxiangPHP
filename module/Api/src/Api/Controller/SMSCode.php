<?php
namespace Api\Controller;

use AiiLibrary\AiiUtility\AiiPush\AiiMyFile;
use Api\Controller\Request\SMSCodeRequest;
use AiiLibrary\AiiUtility\AiiPush\AiiPush;
use Zend\Db\Sql\Where;

/**
 * 短信验证
 * 1.获取验证码，2.进行验证
 *
 * @author WZ
 *
 */
class SMSCode extends User
{

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
     * 普通用户
     *
     * @var 1
     */
    const USER_TYPE_NORMAL = 1;

    /**
     * 商家
     *
     * @var 2
     */
    const USER_TYPE_MERCHANT = 2;

    /**
     * 用户ID
     * @var
     */
    private $userId;

    public function __construct()
    {
        $this->myRequest = new SMSCodeRequest();
        parent::__construct();
    }

    /**
     * @return Common\Response|string
     * @throws \Exception
     * 返回一个数组或者Result类
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action;
        $verification_code = $request->verificationCode;
        $ip = $this->getIP();//获取IP
        if(!in_array($action, array(1, 2)) || !in_array($request->type, array(1, 2))){
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        if(!$this->getSessionId())
        {
            return STATUS_SEND_SMSCODE_FAIL;
        }
        else
        {
            /*if($request->action == 1 && isset($_SESSION['captcha']) && $_SESSION['captcha'])
             {//如果是PC端需验证图形验证码
                 if(!$verification_code)
                 {
                     $response->description = '图形验证码不能为空!';
                     $response->status = '10000';
                     return $response;
                 }
                 $captcha = $_SESSION['captcha'];
                 if($captcha != $verification_code){
                     return STATUS_FAILED_TO_SEND;
                 }
                 unset($_SESSION['captcha']);//验证成功删除验证码

            if($request->action == 1)
            {//如果是PC端需验证图形验证码
                 $redis = new \Redis();
                 $redis->connect('127.0.0.1', 6379);
                 $res = $redis->get($this->getSessionId());//将图形验证码写入redis
                 if(!$res)
                 {
//                     $response->status = STATUS_PARAMETERS_INCOMPLETE;
//                     $response->description = '验证码错误';
//                     return $response;
                 }
                 else
                 {
                     if($res != strtolower($request->verificationCode))
                     {
                         $response->status = STATUS_PARAMETERS_INCOMPLETE;
                         $response->description = '验证码错误';
                         return $response;
                     }
                 }
             }*/

            $login_table = $this->getLoginTable();
            $login_table->sessionId = $this->getSessionId();
            $device = $login_table->getDetails();
            if($device){
                if(!in_array($device->device_type, array(1, 2, 16)))
                {

                    return STATUS_SEND_SMSCODE_FAIL;
                }
                else
                {
                    $start_time = date("Y-m-d H:i:s", time() - 3600 * 24);
                    $end_time = $this->getTime();
                    $sms_code_table = $this->getSmsCodeTable();
                    $sms_code_table->ip = $ip;
                    $count = $sms_code_table->getSendCount($start_time,$end_time);
                    if($count > 100){//24小时超同一IP10条就结束程序
                        return STATUS_SEND_SMSCODE_FAIL;
                    }else{

                        $sms_code_table->ip = 0;
                        $sms_code_table->sessionId = $this->getSessionId();
                        $count = $sms_code_table->getSendCount($start_time,$end_time);
                        if($count > 100){//24小时一个session_id超过10条就结束

                            return STATUS_SEND_SMSCODE_FAIL;
                        }
                    }
                }
            }else{
                return STATUS_SEND_SMSCODE_FAIL;
            }
        }

        if(self::MOBILE_VALIDATE_ACTION_GET == $action){
            /*
             * 根据type 检查相关内容
             */
            if(empty($request->mobile) || empty($request->type) || $request->type > self::THIRD_MOBILE_VALIDATE_TYPE_WITHDRAW){

                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $user_info = $this->getUserInfo($request->type, $request->mobile);
            if($user_info){
                $this->userId = $user_info->user_id;
            }
            $code = $this->makeSmsCode() . '';
            $sms_code_table = $this->getSmsCodeTable();
            $sms_code_table->mobile = $request->mobile;
            $sms_code_table->status = self::MOBILE_VALIDATE_STATUS_TEMP;
            $sms_code_table->type = $request->type;
            $mobile_validate = $sms_code_table->getSendCode();
            // 检查10分钟内有没有发送过验证码，有则找回这条验证码
            if($mobile_validate){
                if(time() < strtotime($mobile_validate['expire'])){
                    return STATUS_FALSE_REPETITION;
                }
            }
            // 短信内容
            $res = $this->smsPush("{$code}为您的登录验证码，请于10分钟内填写。如非本人操作，请忽略本短信。", $request->mobile); // 发送
            if(!$res){
                return STATUS_UNKNOWN;
            }
            $session_id = $this->getSessionId();
            $sms_code_table->sessionId = $session_id;
            $sms_code_table->code = $code;
            $sms_code_table->count = 1;
            $sms_code_table->expire = date('Y-m-d H:i:s', time() + SMSCODE_EXPIRE);
            $sms_code_table->ip = $ip;

            if($user_info){
                $sms_code_table->userId = $user_info['id'];
            }
            $id = $sms_code_table->addData();
            $response->status = ($id ? STATUS_SUCCESS : STATUS_UNKNOWN); // 成功或未知错误
            $response->id = $id;

            if(IS_DEBUG == 1){
                // 测试环境看短信内容
                $response->code = $code;
            }
        }elseif(self::MOBILE_VALIDATE_ACTION_CHECK == $request->action){
            // action 2 验证验证码
            if(CHECK_SMSCODE){
                $sms_code_table = $this->getSmsCodeTable();
                $sms_code_table->mobile = $request->mobile;
                $sms_code_table->type = $request->type;
                $mobile_validate = $sms_code_table->getSendCode();
                if($mobile_validate){
                    if(self::MOBILE_VALIDATE_STATUS_USED == $mobile_validate->status){
                        $used = true;
                    }else{
                        $used = false;
                    }
                    if(self::MOBILE_VALIDATE_TYPE_REGISTER == $request->type && QUICK_SMSCODE_SWITCH == true){
                        // 手机号码后四位，用于用户收不到验证码的时候填写
                        $check = true;
                    }elseif($mobile_validate->code == $request->where->code && $mobile_validate->expire > $this->getTime()){
                        $check = true;
                        if(!$used){
                            $this->complete($mobile_validate->id);
                        }else{
                            $check = false; // 屏蔽这条可使得验证码在一定时间内可重复使用，不然每条验证码只能使用一次。
                        }
                    }else{
                        $check = false;
                    }
                }else{
                    $check = false;
                }
            }else{
                // 短信接口还没开通，所有验证码都可以通过
                $check = true;
            }

            $response->status = ($check ? STATUS_SUCCESS : STATUS_CAPTCHA_ERROR);
        }else{
            $response->status = STATUS_PARAMETERS_INCOMPLETE;
        }
        return $response;
    }


    public function getIP($type = 0)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if($ip !== NULL){
            return $ip[$type];
        }
        if(isset($_SERVER['HTTP_X_REAL_IP']) && $_SERVER['HTTP_X_REAL_IP']){//nginx 代理模式下，获取客户端真实IP
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){//客户端的ip
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){//浏览当前页面的用户计算机的网关
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if(false !== $pos){
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        }elseif(isset($_SERVER['REMOTE_ADDR'])){
            $ip = $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的ip地址
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    /**
     * @param $type
     * @param $mobile
     * @return array|\ArrayObject|bool|int|null
     * @throws \Exception
     * 根据类型和手机号码，验证用户信息
     */
    private function getUserInfo($type, $mobile)
    {
        $user_table = $this->getUserTable();
        switch($type){
            case self::MOBILE_VALIDATE_TYPE_REGISTER:
                // 注册
                $user_table->mobile = $mobile;
                $user_table->delete = null;
                $user_info = $user_table->checkMobile();
                if($user_info){
                    // 用户已存在，手机号码不能重复，不能注册，退出
                    $this->response(STATUS_MOBILE_EXIST);
                }
                break;
            case self::MOBILE_VALIDATE_TYPE_BIND:
                $this->checkLogin();
                $user_table->id = $this->getUserId();
                $user_info = $user_table->getDetails();
                if(!$user_info->mobile){
                    return STATUS_UNKNOWN;
                }
                break;
            default:
                $this->response(STATUS_PARAMETERS_INCOMPLETE);
                break;
        }
        return $user_info;
    }

    /**
     * 2014.3.24 hexin
     * 生成手机验证码
     * <br />2014/3/25 WZ 改
     *
     * @param number $type
     * @return string number
     */
    public function makeSmsCode()
    {
        return  $this->makeCode(6, self::CODE_TYPE_NUMBER);
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
        $template = '';
        switch($type){
            case self::MOBILE_VALIDATE_TYPE_REGISTER: // 注册
            case self::MOBILE_VALIDATE_TYPE_BIND: // 绑定
            case self::MOBILE_VALIDATE_TYPE_RESET: // 重置
            case self::MOBILE_VALIDATE_TYPE_MERCHANT_RESET: // 商家重置
            case self::MOBILE_VALIDATE_TYPE_BIND_CHECK: // 绑定验证
            case self::MOBILE_VALIDATE_TYPE_PARTNER: // 第三方登录信息完善
            case self::MOBILE_VALIDATE_TYPE_WITHDRAW: // 提现
            case self::MOBILE_VALIDATE_TYPE_BINDING://员工绑定
            case self::MOBILE_VALIDATE_TYPE_MERCHANT_REGISTER: //商家注册
            case self::USER_BINDING_MOBILE: //用户绑定手机
                $need = 0;
                $template = TEMPLATE_SMS_CAPTCHA;
                $content = sprintf($template, $code);
                break;
            default:
                $content = '';
                break;
        }
        return $content;
    }

    /**
     * 发送多条短信的
     *
     * @author WZ
     * @param int $type
     * @param array $mobile
     * @param integer $code
     * @return multitype:boolean
     */
    /*public function smsPush($type, $mobile, $code)
    {
        $sms_push = new mallSendMsg();
        if($type != 1){
            $sms_push->userId = $this->userId;
        }
        else
        {
            $sms_push->userId = 0;
        }
        $sms_push->mobileNo = $mobile;
        $sms_push->msgType = $type;
        $sms_push->msgContent = $code;
        $sms_push->submit();
        $status = $sms_push->getRespCode();
        if($status['respCode']){
            $response = array('s' => '10000', 'd' => $status['respMsg']);
            $this->response($response);
        }
        return $code;
    }*/

    /**
     * @param $content
     * @param $mobile
     * @return bool
     */
    public function smsPush($content, $mobile)
    {
        $push = new AiiPush();
        $return = false;
        if (SMSCODE_SWITCH)
        {
            if ($mobile)
            {
                $result = $push->pushSingleDevice($mobile, 16, $content);
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
                    $temp = '短信，短信发送成功， mobile：' . $mobile . '，content：' . $content;
                }
                else
                {
                    $temp = '短信，短信发送失败不能进行验证， mobile：' . $mobile . '，content：' . $content;
                }
            }
            else
            {
                $temp = '短信，没有开启短信发送，mobile：' . $mobile . '，content：' . $content;
            }
            $myfile = new AiiMyFile();
            $myfile->setFileToPublicLog()->putAtStart($temp);
        }
        return $return;
    }

    /**
     * 重复发送,记录+1
     *
     * @author WZ
     * @param int $id
     * @param int $count
     */
    public function addCount($id, $count)
    {
        $set = array('count' => $count + 1);
        $where = array('id' => $id);
        $this->getSmsCodeTable()->update($set, $where);
    }

    /**
     * 获取用户IP
     * @return Ambigous <unknown, string>
     * @version 2015年11月17日
     * @author liujun
     */
    /*   public function getIP()
      {
          $ip = '';
         /*  if (getenv('HTTP_CLIENT_IP'))
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
          } */

    /*
    if (isset($_SERVER)){

        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){

            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];

        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {

            $ip = $_SERVER["HTTP_CLIENT_IP"];

        } else {

            $ip = $_SERVER["REMOTE_ADDR"];
        }

    } else {

        if (getenv("HTTP_X_FORWARDED_FOR")){

            $ip = getenv("HTTP_X_FORWARDED_FOR");

        } else if (getenv("HTTP_CLIENT_IP")) {

            $ip = getenv("HTTP_CLIENT_IP");

        } else {

            $ip = getenv("REMOTE_ADDR");

        }

    }


    foreach (array(
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_FROM',
        'REMOTE_ADDR'
    ) as $v) {
        if (isset($_SERVER[$v])) {
            if (! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $_SERVER[$v])) {
                continue;
            }
            $ip = $_SERVER[$v];
        }
    }
    return $ip;
}  */

    /**
     * @param $id 短信id
     * @return mixed
     * 更新验证状态
     */
    private function complete($id)
    {
        $sms_table = $this->getSmsCodeTable();
        $sms_table->status = self::MOBILE_VALIDATE_STATUS_USED; // 已验证
        $sms_table->id = $id;
        return $sms_table->updateData();
    }
}
