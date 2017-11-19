<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\MessageItem;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class WithdrawSubmitRequest extends Request
{
    /**
     * 银行账户ID
     */
    public $branchId;

    /**
     * 支付密码
     */
    public $payPassword;

    /**
     * 提现金额
     */
    public $cash;

    /**
     * 短信验证表id
     */
    public $smscodeId;

}