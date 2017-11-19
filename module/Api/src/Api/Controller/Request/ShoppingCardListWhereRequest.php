<?php
namespace Api\Controller\Request;

use Api\Controller\Common\WhereRequest;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 * @author WZ
 */
class ShoppingCardListWhereRequest extends WhereRequest
{
    /**
     * @var 订单提交的产品UUID数组
     */
    public $goodsIds;

}