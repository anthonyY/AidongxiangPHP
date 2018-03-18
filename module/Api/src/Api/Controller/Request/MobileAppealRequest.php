<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\MessageItem;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class MobileAppealRequest extends Request
{
    /**
     * 原手机号码
     */
    public $mobile;

    /**
     * 新手机号码
     */
    public $newMobile;

    /**
     * 登录密码（MD5）
     */
    public $password;

    /**
     * 注册时间，例如2017-10-10
     */
    public $registerTime;
}