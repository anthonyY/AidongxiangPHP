<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\MerchantItem;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class BusinessesSubmitRequest extends Request
{
    /**
     * 商家对象
     */
    public $merchant;

    function __construct()
    {
        parent::__construct();
        $this->merchant = new MerchantItem();
    }
}