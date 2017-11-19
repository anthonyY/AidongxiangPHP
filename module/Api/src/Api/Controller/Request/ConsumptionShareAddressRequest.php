<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *
 */
class ConsumptionShareAddressRequest extends Request
{
    /**
     * @var 产品分类 1兑换商城分类 2其它
     */
    public $type = 'type';

}