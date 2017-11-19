<?php
namespace Api\Controller;

/**
 * 更新手机号码
 *
 */
class mallUpdateMobileNo extends mallBase
{
    public $method = 'mallUpdateMobileNo';

    /**
     * @var 新的手机号码
     */
    public $newMobileNo;

    /**
     * @var 用户登陆密码(两次MD5加密)
     */
    public $password;

    public $request = ['newMobileNo','password'];

    /**
     * java->php
     */
    public function index()
    {
        $user_model = $this->getUserTable();
        $user_model->userId = $this->userId;
        $user_model->mobile = $this->newMobileNo;
        $user_details = $user_model->getUserDetailsByUserId();
        if(!$user_details)
        {
            $this->respCode = 300;
        }
        elseif($user_details->password != $this->password)
        {
            $this->respCode = 100;
        }
        else
        {
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
        }
        return $this->mallReturn();
    }

    /**
     * PHP->JAVA
     */
    public function submit()
    {
        if(!$this->mobileNo || !$this->password)
        {
            return false;
        }
        return $this->mallRequest();
    }

}