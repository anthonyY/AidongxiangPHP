<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *
 */
class SMSCodeRequest extends Request
{

    /**
     * 类型，1用户注册短信验证码 2用户重置登陆密码短信验证码
     * 3用户设置支付密码短信验证码 4订单已经开始配送 5用户修改手机号时的验证码
     *
     * @var Number
     */
    public $type;

    /**
     * 手机号码
     *
     * @var String
     */
    public $mobile;

    /**
     * 短信验证码
     * PC专用
     */
    public $verificationCode;

    /**
     * where
     */
    public $where;

    function __construct()
    {
        parent::__construct();
        $this->where = new SMSCodeWhereRequest();
    }
}