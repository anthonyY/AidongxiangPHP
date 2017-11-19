<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 *定义接收类的属性
 * @author WZ
 */
class GetAttrPriceDetailsRequest extends Request
{

    /**
     * 属性IDS 多个用|线隔开
     * @var String
     */
    public $attrIds;

    /**
     * 预购时间(预购产品用)V2.1格式2017-10-16
     * @var
     */
    public $presaleTime;
}