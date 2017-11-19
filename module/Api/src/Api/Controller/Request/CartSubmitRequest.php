<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class CartSubmitRequest extends Request
{

    /**
     * @var 商品ID
     */
    public $goodsId;

    /**
     * @array 商品属性id数组
     */
    public $attrIds;

    /**
     * @var 商品数量
     */
    public $number;

    /**
     * @var 购物车ID
     */
    public $type;


}