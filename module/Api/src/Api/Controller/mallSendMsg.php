<?php
namespace Api\Controller;

/**
 * 发送短信接口
 *
 *
 */
class mallSendMsg extends mallBase
{
    public $method = 'mallSendMsg';

    /**
     *  1.用户注册短信验证码
     *  2.用户重置登陆密码短信验证码
     *  3.用户设置支付密码短信验证码
     *  4.订单已经开始配送
     *  5.用户修改手机号时的验证码
     *  6.商城门店商家提现验证码
     *  7.平台后台登陆验证码
     *  8.用户绑定银行卡
     *  9.用户提现
     *  10.后台审核验证码
     *  11.商城第三方登录绑定手机号
     */
    public $msgType;

    /**
     * 验证码/订单号(如若是类型4，此字段订单号，其余传缓存的验证码)
     */
    public $msgContent;

    public $request = ['msgType','msgContent'];

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

        if(!in_array($this->msgType,array(1,2,3,4,5,6,7,8,9,10,11)) || !$this->msgContent)
        {
            return false;
        }
        if(in_array($this->msgType,array(1,2,6,7,10,11)))
        {
            $this->userId = '';
        }

        return $this->mallRequest();
    }

}