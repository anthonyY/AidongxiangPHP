<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 *
 * @author WZ
 *
 */
class ReferenceItemListRequest extends Request
{

    /**
     * 是否推荐
     *
     * @var Number
     */
    public $recommend;
}