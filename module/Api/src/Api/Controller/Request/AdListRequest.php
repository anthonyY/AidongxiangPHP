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

    /**
     * 广告位id
     *
     * @var String
     */
    public $categoryId;

    /**
     * 终端类型，1手机网站，2 PC网站, 默认1
     * @var
     */
    public $terminalType;
}