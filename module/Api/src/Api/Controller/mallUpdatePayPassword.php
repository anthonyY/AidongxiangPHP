<?php
namespace Api\Controller;

/**
 * 更新账户支付密码
 *
 */
class mallUpdatePayPassword extends mallBase
{
    /**
     * @var Md5(对旧密码二次MD5，若是第一次设置密码，此字段传空) 。若首次设置该密码，此字段可以为空。
     */
    public $oldPassword;

    /**
     * @var Md5(对用户新密码二次MD5)
     */
    public $newPassword;

    public $method = 'mallUpdatePayPassword';

    public $request = ['oldPassword','newPassword'];

    /**
     * java->php
     */
    public function index()
    {
        $user_model = $this->getUserTable();
        $user_model->payPassword = $this->newPassword;
        $user_model->userId = $this->userId;
        $user_details = $user_model->getUserDetailsByUserId();
        if(!$user_details)
        {
            $this->respCode = 300;
        }
        elseif($user_details->pay_password != $this->oldPassword)
        {
            $this->respCode = 100;
        }
        $user_model->id = $user_details->id;
        $user_model->userId = null;
        $res = $user_model->updateData();
        if(!$res)
        {
            $this->respCode = 99;
        }
        else
        {
            $this->respCode = 0;
        }
        return $this->mallReturn();
    }

    /**
     * PHP->JAVA
     */
    public function submit()
    {
        if(!$this->newPassword)
        {
            return false;
        }
        return $this->mallRequest();
    }

}