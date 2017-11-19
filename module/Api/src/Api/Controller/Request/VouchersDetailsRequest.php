<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 *
 * @author lj
 *
 */
class VouchersDetailsRequest extends Request
{

    /**
     * @var 分享次数
     */
    public $coupon_code = "couponCode";
}