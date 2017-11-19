<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 * @author WZ
 */
class UserItem extends Item
{

    /**
     * 用户昵称
     * @var String
     */
    public $name;

    /**
     * 用户姓名
     * @var String
     */
    public $realName;

    /**
     * 用户密码
     * @var String
     */
    public $password;

    /**
     * 手机号码
     * @var String
     */
    public $mobile;

    /**
     * 性别：1男；2女；0未知；
     * @var String
     */
    public $sex;

    /**
     * 头像id
     * @var String
     */
    public $image;

    /**
     * @var社区ID
     */
    public $communityId;

    /**
     * 详细地址
     */
    public $address;

    /**
     * @var个人签名
     */
    public $description;

}