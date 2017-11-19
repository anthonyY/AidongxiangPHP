<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\CustomerServiceItem;
use Api\Controller\Item\MessageItem;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class CustomerServiceSubmitRequest extends Request
{
    public $service;

    function __construct()
    {
        parent::__construct();
        $this->service = new CustomerServiceItem();
    }
}