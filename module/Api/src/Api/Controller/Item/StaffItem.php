<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 *
 * @author WZ
 *
 */
class StaffItem extends Item
{

    /**
     * 用户昵称
     * @var String
     */
    public $name;

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
     * 手机号码
     * @var String
     */
    public $type;

    /**
     * 姓名
     * @var unknown
     */
    public $id_name = 'idName';
}