<?php
namespace Api\Controller;

/**
 * 14、	商城返利
 */
class mallRebate extends mallBase
{
    public $method = 'mallRebate';

    /**
     *提现记录号
     */
    public $orderId;

    /**
     * 商城返利金额。以分为单位的整型。
     */
    public $rebateAmount;

    /**
     *标识位。1标识分销返利；
     *2标识订单返利；
     */
    public $type;

    public $request = ['orderId','rebateAmount','type'];

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