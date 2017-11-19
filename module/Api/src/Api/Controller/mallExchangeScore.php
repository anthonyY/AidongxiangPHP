<?php
namespace Api\Controller;

/**
 * 积分兑换余额接口
 *
 *
 */
class mallExchangeScore extends mallBase
{
    public $method = 'mallExchangeScore';

    /**
     * 兑换的积分数。目前规则100积分兑换1元。该值应为100整数倍
     * @var
     */
    public $scoreNumber;

    /**
     * 兑换后的余额(已分为单位， 10.0元返回1000)
     * @var
     */
    public $balance;

    /**
     * 兑换后的积分
     * @var
     */
    public $score;

    public $request = ['scoreNumber'];

    public $return = ['balance','score'];

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
        return $this->mallRequest();
    }
}