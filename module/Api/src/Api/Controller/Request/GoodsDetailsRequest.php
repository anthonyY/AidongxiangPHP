<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * AdList定义接收类的属性
 *
 * @author WZ
 *
 */
class GoodsDetailsRequest extends Request
{

    /**
     * 属性IDS用|线隔开 a=2
     *
     * @var String
     */
    public $attrIds;

}