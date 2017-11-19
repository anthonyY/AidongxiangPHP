<?php
namespace Api\Controller;

/**
 * 更新账户密码
 *
 */
class mallUpdateAccountPassword extends mallBase
{
    public $method = 'mallUpdateAccountPassword';

    /**
     * @var Md5 加密(对旧密码二次MD5)
     */
    public $oldPassword;

    /**
     * @var Md5 加密(对用户新密码二次MD5)
     */
    public $newPassword;

    public $request = ['oldPassword','newPassword'];

    public function index()
    {
        $user_model = $this->getUserTable();
        $user_model->password = $this->newPassword;
        $user_model->userId = $this->userId;
        $user_details = $user_model->getUserDetailsByUserId();
        if(!$user_details)
        {
            $this->respCode = 300;
        }
        elseif($user_details->password != $this->oldPassword)
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
     * php->java
     */
    public function submit()
    {
        if(!$this->newPassword || !$this->oldPassword)
        {
            return false;
        }
        return $this->mallRequest();
    }

}