<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\UserItem;
use Api\Controller\Item\MerchantItem;

/**
 * 定义接收类的属性
 *
 * @author WZ
 *
 */
class GoodsPriceStorageRequest extends Request
{
    /**
     * 用户对象
     *
     * @var UserItem
     */
    public $attribute_ids = 'attributeIds';

}