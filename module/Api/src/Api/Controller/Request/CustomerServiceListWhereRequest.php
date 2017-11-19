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
class CustomerServiceListWhereRequest extends WhereRequest
{
    /**
     * @var 售后状态：
     * 1.待处理 2已处理 3已驳回
     */
    public $status;

}