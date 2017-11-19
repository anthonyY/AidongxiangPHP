<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\UserItem;
use Api\Controller\Item\MerchantItem;

/**
 * 定义接收类的属性
 *
 * @author WZ
 *
 */
class UserUpdateRequest extends Request
{
    public $smscodeId;

    /**
     * 用户对象
     * @var UserItem
     */
    public $user;


    function __construct()
    {
        parent::__construct();
        $this->user = new UserItem();
    }
}