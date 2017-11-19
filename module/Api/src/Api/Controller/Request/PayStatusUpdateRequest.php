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
class PayStatusUpdateRequest extends Request
{
    /**
     * 用户id
     */
    public $user_id = 'userId';

    /**
     * 类型：2秒杀；3抽奖；
     */
    public $type;

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
}