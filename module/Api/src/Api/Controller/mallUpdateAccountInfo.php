<?php
namespace Api\Controller;

/**
 * 更新账户信息
 *
 *
 */
class mallUpdateAccountInfo extends mallBase
{
    public $method = 'mallUpdateAccountInfo';

    /**
     * @var用户昵称
     */
    public $nickName;

    /**
     * @var性别 0 - 女； 1 – 男
     */
    public $sex;

    /**
     * @var默认小区编号
     */
    public $defaultCommunityNo;

    /*
     * 是否认证业主 0否 1是(php端注册时默认非认证)
     */
    public $isAuth;

    /**
     * @var用户生日。格式如2017-01-01
     */
    public $birthday;

    /**
     * @var用户地址
     */
    public $address;

    /**
     * @var用户签名
     */
    public $remark;

    public $request = ['nickName','sex','defaultCommunityNo','isAuth','birthday','address','remark'];

    /**
     * java->php
     */
    public function index()
    {
        $user_model = $this->getUserTable();
        $user_model->userId = $this->userId;
        $user_details = $user_model->getUserDetailsByUserId();
        if(!$user_details)
        {
            $this->respCode = 300;
            return $this->mallReturn();
        }
        $user_model->sex = $this->sex == 1 ? 1 : 2;
        if($this->nickName)
        {
            $user_model->name = $this->nickName;
        }
        if($this->defaultCommunityNo)
        {
            $user_model->regionId = $this->defaultCommunityNo;
        }
        if($this->address)
        {
            $user_model->street = $this->address;
        }
        if($this->remark)
        {
            $user_model->description = $this->remark;
        }
        $user_model->id = $user_details->id;
        $user_model->userId = null;
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
     * PHP->JAVA
     */
    public function submit()
    {
        if(!in_array($this->sex,array(0,1)))
        {
            return false;
        }
        return $this->mallRequest();
    }

}