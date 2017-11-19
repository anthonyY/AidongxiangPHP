<?php
namespace Api\Controller;

/**
 * 余额充值
 *
 *
 */
class mallQueryAccountBalanceScore extends mallBase
{

    /**
     * 积分
     * @var
     */
    public $score;

    /**
     * 余额(以分为单位， 10.0元返回1000)
     * @var
     */
    public $balance;

    /**
     * 命命空间
     * @var string
     */
    public $method = 'mallQueryAccountBalanceScore';

    public $return = ['score','balance'];

    public function index()
    {
        $this->respCode = 0;
        return $this->mallReturn();
    }

    /**
     * 提交到java接口方法
     */
    public function submit()
    {
       return $this->mallRequest();
    }
}