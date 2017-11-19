<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 * 继承基础Request
 * @author WZ
 */
class PayRequest extends Request
{
    /**
     * 现金
     * @var float
     */
    public $cash;

    /**
     * @var 支付密码pay=3时传(支付密码pay=3时传（MD5加密）)
     */
    public $password;

    /**
     * 支付方式
     * @var number
     */
    public $payType;

    /**
     * 微信open_id
     * @var unknown
     */
    public $openId;

}