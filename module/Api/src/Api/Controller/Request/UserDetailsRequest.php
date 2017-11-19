<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class UserDetailsRequest extends Request
{
    /**
     * 检验的时间
     */
    public $t;

    /**
     * 检验的时间
     */
    public $timestamp;

    /**
     * 加密串
     */
    public $md5;

    /**
     * 手机号
     *
     */
    public $mobile;
}