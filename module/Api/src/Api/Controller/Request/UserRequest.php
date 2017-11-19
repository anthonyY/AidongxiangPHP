<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\UserItem;

/**
 * 定义接收类的属性
 *
 * @author WZ
 *
 */
class UserRequest extends Request
{

    /**
     * 用户名
     *
     * @var String
     */
    public $name;

    /**
     * 支付密码
     *
     * @var String
     */
    public $payPassword;

    /**
     * 密码
     *
     * @var String
     */
    public $password;

    /**
     * 新密码
     *
     * @var String
     */
    public $passwordNew;

    /**
     * @var
     * 重复新密码
     */
    public $repeatPasswordNew;

    /**
     * 短信验证码编号
     *
     * @var number
     */
    public $smscodeId;

    /**
     * 手机号码
     *
     * @var string
     */
    public $mobile;

    /**
     * 第三方登录唯一ID
     * @var unknown
     */
    public $openId;

    /**
     * 1QQ，2微信V2.0
     */
    public $partner;

    /**
     * 用户对象
     */
    public $user;

    /**
     * 用户头像id
     */
    public $image;

    /**
     * 推荐人ID V2.0
     */
    public $referrerId;

    /**
     * 微信unionId V2.0
     */
    public $unionId;

    /**
     * 绑定类型
     * @var unknown
     */
    public $type;

    /**
     *
     * 图片路径：
     * 1腾讯figureurl
     * 2 微信figureurl
     */
    public $imagePath;

    /**
     * 如果回调有返回：1男；2女；没有返回0；
     */
    public $sex;

    function __construct()
    {
        parent::__construct();
        $this->user = new UserItem();
    }
}