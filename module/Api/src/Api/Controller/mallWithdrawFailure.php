<?php
namespace Api\Controller;

/**
 * 14、	提现失败
 */
class mallWithdrawFailure extends mallBase
{
    public $method = 'mallWithdrawFailure';

    /**
     *提现记录号
     */
    public $orderId;

    public $request = ['orderId'];

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