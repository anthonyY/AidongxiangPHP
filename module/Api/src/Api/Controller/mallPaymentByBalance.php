<?php
namespace Api\Controller;

/**
 * 余额支付
 *
 *
 */
class mallPaymentByBalance extends mallBase
{
    public $method = 'mallPaymentByBalance';
    /**
     * 商城支付单号
     * @var
     */
    public $mallOrderNo;

    /**
     * md5(用户密码的两次MD5)
     * @var
     */
    public $password;

    /**
     * 支付金额(以分为单位)
     * @var
     */
    public $payAmount;

    /**
     * 余额(已分为单位， 10.0元返回1000)
     * @var
     */
    public $balance;

    /**
     * Java支付订单号
     * @var
     */
    public $seqNo;


    public $request = ['mallOrderNo','password','payAmount'];

    public $return = ['balance','seqNo'];

    /**
     * 接收
     */
    public function index()
    {
        $this->respCode = 0;
        return $this->mallReturn();
    }

    /**
     * 提交
     * @return $this|bool
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

       return $this->mallRequest();
    }


}