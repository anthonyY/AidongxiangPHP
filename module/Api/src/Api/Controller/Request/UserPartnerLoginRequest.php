<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 *
 * @author WZ
 *
 */
class UserPartnerLoginRequest extends Request
{

    /**
     *
     * @var 用户名
     */
    public $nickname = 'name';

    /**
     * 密码
     *
     * @var String
     */
    public $openId;

    /**
     * 新密码
     *
     * @var String
     */
    public $imageUrl = 'imagePath';

    /**
     * @var 1男；2女；没有返回0；
     */
    public $sex;

    /**
     * @var 1QQ；2微信；
     */
    public $partner;

    /**
     * @var 微信第三方登录唯一标识；
     */
    public $unionId = 'unionId';

}