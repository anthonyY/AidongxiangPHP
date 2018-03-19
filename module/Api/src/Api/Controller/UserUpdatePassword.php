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
        $password = $request->password;
        $passwordNew = $request->passwordNew;
        if(!$password || !$passwordNew)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $this->tableObj = $this->getUserTable();
        $this->tableObj->id = $user_id;
        $details = $this->tableObj->getDetails();
        if(!$details)
        {
            return STATUS_NODATA;
        }
        if($details->password != md5($password))
        {
            return STATUS_PASSWORD_ERROR_FOR_UPDATE;
        }
        if(md5($passwordNew) == md5($password))
        {
            $response->status = 10000;
            $response->description = '新密码不能跟原密码一致';
            return $response;
        }

        if(LOGIN_STATUS_LOGIN == $this->getUserStatus()){
            $this->clearDeviceUser($this->getUserId(), $this->getUserType());
        }
        // 再把登录表的状态改变成登出状态。
        $login_table =  $this->getLoginTable();
        $login_table->status = LOGIN_STATUS_LOGOUT;
        $login_table->userId= 0;
        $login_table->sessionId= $this->getSessionId();
        $login_table->updateLogout();

        $this->tableObj->password = md5($passwordNew);
        $res = $this->tableObj->updateData();
        return $res?STATUS_SUCCESS:STATUS_UNKNOWN;
    }
}
