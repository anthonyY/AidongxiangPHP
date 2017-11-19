<?php
namespace Api\Controller;

use Api\Controller\Request\UserRequest;

class UserResetPassword extends User
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

        $password = trim($request->password);
        $mobile = trim($request->mobile);
        if (!preg_match("/^1[34578]{1}\d{9}$/", $mobile) || !$password) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $type = 2;
        $this->checkSmsComplete($type, $request->smscodeId, $mobile); // 注册，检查是否有效，无效返回1010，请求超时

        $this->tableObj = $this->getUserTable();
        $this->tableObj->mobile = $mobile;
        $this->tableObj->password = strtoupper(md5(strtoupper($password)));
        $res = $this->tableObj->passwordReset();
        return $res['code'];
    }

}

