<?php
namespace Api\Controller\Request;

use Api\Controller\Common\WhereRequest;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *
 */
class GroupBuyingListWhereRequest extends WhereRequest
{
    /**
     * 拼团表产品ID
     */
    public $groupBuyingGoodsId;
}