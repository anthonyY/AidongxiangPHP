<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

;
use Api\Controller\Item\BankCardItem;

/**
 * 提交地址接收类
 *
 * @author WZ
 *
 */
class BankCardSubmitRequest extends Request
{

    public $bankCard;

    function __construct()
    {
        $this->bankCard = new BankCardItem();
    }
}