<?php
namespace Api\Controller;

/**
 * 查询余额、积分
 *
 */
class mallAccountBalanceRecharge extends mallBase
{
    public $method = 'mallAccountBalanceRecharge';
    /**
     * 充值金额
     * @var
     */
    public $rechargeAmount;

    /**
     * 支付方式。其中有两种方式。10000001(支付宝)，10000002（微信）
     * @var
     */
    public $payChannel;

    /**
     * 第三方支付流水号
     * @var
     */
    public $seqNo;

    /**
     * 充值后余额(已分为单位， 10.0元返回1000)
     * @var
     */
    public $balance;

    public $request = ['rechargeAmount','payChannel','seqNo'];

    public $return = ['balance'];

    public function index()
    {
        $this->respCode = 0;
        return $this->mallReturn();
    }

    public function submit()
    {
        foreach($this->request as $v)
        {
            if(!$this->$v)
            {
                return false;
            }
        }

        $this->mallRequest();

        return $this;
    }

}