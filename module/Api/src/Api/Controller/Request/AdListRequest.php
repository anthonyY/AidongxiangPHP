<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * AdList定义接收类的属性
 *
 * @author WZ
 *
 */
class AdListRequest extends Request
{

    /**
     * 广告位id
     *
     * @var String
     */
    public $positionId;
}