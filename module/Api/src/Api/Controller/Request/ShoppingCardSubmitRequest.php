<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 *
 * @author WZ
 *
 */
class ShoppingCardSubmitRequest extends Request
{

    /**
     * @var 购物卡号
     */
    public $cardNumber;

    /**
     * @var 购物卡密码
     */
    public $password;

    /**
     * @var 图形验证码
     */
    public $verificationCode;

}