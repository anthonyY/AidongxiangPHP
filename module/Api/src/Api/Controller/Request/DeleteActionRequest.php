<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\GoodsItem;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class DeleteActionRequest extends Request
{

    /**
     * @var 产品数组ids
     */
    public $ids;


}