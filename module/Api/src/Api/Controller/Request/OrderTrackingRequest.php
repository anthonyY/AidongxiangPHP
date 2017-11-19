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
class OrderTrackingRequest extends Request
{

    /**
     * 类型 0 订单 1售后
     */
    public $type;

}