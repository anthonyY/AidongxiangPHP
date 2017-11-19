<?php
namespace Api\Controller;

use Api\Controller\Request\UserRequest;

/**
 * 修改密码
 * @author WZ
 */
class UserUpdatePassword extends User
{

    public function __construct()
    {
        $this->myRequest = new UserRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin(); // 检查登录状态

        $user_id = $this->getUserId();
        $password = trim($request->password);
        $passwordNew = trim($request->passwordNew);
        $repeatPasswordNew = trim($request->repeatPasswordNew);
        if(!$password || !$passwordNew || !$repeatPasswordNew)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if($passwordNew !== $repeatPasswordNew)
        {
            return STATUS_PASSWORD_DISAGREE;
        }
        $this->tableObj = $this->getUserTable();
        $this->tableObj->id = $user_id;
        $details = $this->tableObj->getDetails();
        if(!$details)
        {
            return STATUS_NODATA;
        }
        if($details->password != strtoupper(md5(strtoupper($password))))
        {
            return STATUS_PASSWORD_ERROR_FOR_UPDATE;
        }

        $mallUpdateAccountPassword = new mallUpdateAccountPassword();
        $mallUpdateAccountPassword->mobileNo = $details->mobile;
        $mallUpdateAccountPassword->userId = $details->user_id;
        $mallUpdateAccountPassword->oldPassword = $details->password;
        $mallUpdateAccountPassword->newPassword = strtoupper(md5(strtoupper($passwordNew)));
        $mallUpdateAccountPassword->submit();
        $respond = $mallUpdateAccountPassword->getRespCode();
        if($respond && $respond['respCode']  == 0)
        {
            $this->tableObj->password = strtoupper(md5(strtoupper($passwordNew)));
            $this->tableObj->updateData();
            return STATUS_SUCCESS;
        }
        else
        {
            $response->status = STATUS_UNKNOWN;
            $response->description = $respond['respMsg'];
            return $response;
        }
    }
}
