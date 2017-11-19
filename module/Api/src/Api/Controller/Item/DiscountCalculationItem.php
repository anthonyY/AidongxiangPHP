<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 * 地址基础类
 *
 * @author WZ
 *
 */
class DiscountCalculationItem extends Item
{

    /**
     * 商家UUID
     * @var
     */
    public $id;

    /**
     * 优惠券ID（单个）
     * @var
     */
    public $couponId;

    /**
     * 购物卡IDs格式如：[1，2，3]
     * @var array
     */
    public $shoppingCardIds;

    /**
     * 运费
     * @var
     */
    public $expensesCash;

    /**
     * 产品数组；
     * @var
     */
    public $goodses;

}