<?php
namespace Api\Controller\Common;

/**
 * 基础用户类
 * @author WZ
 *
 */
class Login
{
    /**
     * 用户Id
     * @var String
     */
    public $user_id = 'userId';

    /**
     * 用户类型：1用户；2商家；
     * @var unknown
     */
    public $user_type = 'userType';

    /**
     * 用户名
     * @var Number
     */
    public $user_name = 'username';

    /**
     * 状态
     * @var String
     */
    public $status = 'status';

    /**
     * 版本号
     *
     * @var unknown
     */
    public $version = 'version';

    /**
     * 过期时间
     *
     * @var unknown
     */
    public $expiry = 'expiry';
}