<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\AddressItem;

/**
 * 提交地址接收类
 *
 * @author WZ
 *
 */
class AddressSubmitRequest extends Request
{

    public $address;

    function __construct()
    {
        $this->address = new AddressItem();
    }
}