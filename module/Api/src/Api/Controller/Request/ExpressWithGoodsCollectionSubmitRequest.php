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
class ExpressWithGoodsCollectionSubmitRequest extends Request
{
    /**
     *  省市区id
     * @var integer
     */
    public $regionId;
    
    /**
     * 商品数组
     * @var $goodses
     */
    public $goodses;
    
}