<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\JoinedItem;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class MerchantJoinedSubmitRequest extends Request
{
    /**
     * 加盟对像
     */
    public $joined;

    function __construct()
    {
        $this->joined = new JoinedItem();
    }
}