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
class GetVouchersRequest extends Request
{

    /**
     * 手机
     */
    public $mobile;

    /**
     * 优惠加密串
     *
     * @var float
     */
    public $coupon_code = 'couponCode';

}