<?php
namespace Api\Controller\Request;

use Api\Controller\Common\WhereRequest;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 * @author WZ
 */
class CouponListWhereRequest extends WhereRequest
{
    /**
     * @var 门店id
     */
    public $merchantId;

    /**
     * @var 1未使用,2已使用,3已过期
     */
    public $status;

    /**
     * 订单提交的商品uuid数组
     */
    public $goodsIds;
}