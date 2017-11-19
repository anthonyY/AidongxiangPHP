<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 *
 * @author WZ
 *
 */
class CustomerServiceItem extends Item
{

    /**
     * 售后类型:1退款；2退货
     */
    public $type;

    /**
     * 收货状态：1已收到货 2未收到货
     */
    public $receivingStatus;

    /**
     * 订单产品ID
     */
    public $orderGoodsId;

    /**
     * 商品数量（不填为全部）
     */
    public $number;

    /**
     * 退款说明
     */
    public $reason;

    /**
     * 图片ID数组例[“1”,”2”]
     */
    public $imageIds;

    /**
     * 退款原因：1商品与描述不符 2 少件漏发 3 卖家发错货 4 未按约定时间发货 5 其它原因
     */
    public $reasonType;

}