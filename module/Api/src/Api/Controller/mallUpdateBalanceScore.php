<?php
namespace Api\Controller;

/**
 * 	商城退款
 *
 */
class mallUpdateBalanceScore extends mallBase
{
    public $method = 'mallUpdateBalanceScore';

    /**
     * 钱包余额
     * @var
     */
    public $score;

    /**
     * 积分
     * @var
     */
    public $balance;

    public $request = ['score','balance'];

    /**
     * java->php
     */
    public function index()
    {
        $user_model = $this->getUserTable();
        $user_model->cash = $this->balance/100;
        $user_model->points = $this->score;
        $user_model->userId = $this->userId;
        $user_details = $user_model->getUserDetailsByUserId();
        $user_model->id = $user_details->id;
        $res = $user_model->updateData();
        if($res)
        {
            $this->respCode = 0;
        }
        else
        {
            $this->respCode = 99;
        }
        return $this->mallReturn();
    }

    /**
     * php->java
     */
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