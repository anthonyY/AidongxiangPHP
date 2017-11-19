<?php
namespace Api\Controller;

/**
 * 积分登记
 *
 *
 */
class mallAddScore extends mallBase
{
    /**
     *
     * @var string
     */
    public $method = 'mallAddScore';
    /**
     * 积分数（一般为订单实际支付金额，不包含余额支付，一般为订单完成后提交积分登记）
     * @var
     */
    public $scoreNumber;

    /**
     * 订单编号
     * @var
     */
    public $remark;

    /**
     * 积分总数
     * @var
     */
    public $score;

    public $request = ['scoreNumber','remark'];

    public $return= ['score'];

    public function index()
    {
        $this->respCode = 0;
        $this->mallReturn();
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
        return $this->mallRequest();
    }
}