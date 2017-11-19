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
class ShippingAddressUpdateRequest extends Request
{
    /**
     *
     * @var $order_id 订单ID
     */
    public $order_id = "orderId";

    /**
     *
     * @var $order_id 订单ID
     */
    public $type = "type";

}