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
class GoodsSubmitRequest extends Request
{

    /**
     * 商品对象
     *
     * @var GoodsItem
     */
    public $goods;

    function __construct()
    {
        $this->goods = new GoodsItem();
    }
}