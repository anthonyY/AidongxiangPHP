<?php
namespace Api\Controller;

/**
 * 用户提现
 *
 *
 */
class mallWithdraw extends mallBase
{
    public $method = 'mallWithdraw';

    /**
     *提现记录号
     */
    public $orderId;

    /**
     * 商城提现金额。以分为单位的整型。
     */
    public $withdrawAmount;

    public $request = ['orderId','withdrawAmount'];

    public $return = ['balance'];

    public function index()
    {
        $this->respCode = 0;
        return $this->mallReturn();
    }

    /**
     * php->java
     */
    public function submit()
    {
        return $this->mallRequest();
    }

}