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
class OrderListWhereRequest extends WhereRequest
{
    /**
     * @var 订单状态
     */
    public $status;

    /**
     * 评价类型：1待评价和已评价的订单PC
     * @var
     */
    public $evaluateType;
}