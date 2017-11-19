<?php
namespace Api\Controller;

use Api\Controller\Request\UserRequest;

/**
 * 修改密码
 * @author WZ
 */
class UserUpdatePayPassword extends User
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

        $pay_password = trim($request->payPassword);
        $smscode_id = trim($request->smscodeId);
        if(!$pay_password || !$smscode_id)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $user_model = $this->getUserTable();
        $user_model->id = $this->getUserId();
        $user_details = $user_model->getDetails();
        $this->checkSmsComplete(3,$smscode_id,$user_details->mobile);
        $mallUpdatePayPassword = new mallUpdatePayPassword();
        $mallUpdatePayPassword->mobileNo = $user_details->mobile;
        $mallUpdatePayPassword->userId = $user_details->user_id;
        $mallUpdatePayPassword->oldPassword = $user_details->pay_password;
        $mallUpdatePayPassword->newPassword = strtoupper(md5(strtoupper($pay_password)));
        $mallUpdatePayPassword->submit();
        $respond = $mallUpdatePayPassword->getRespCode();
        if($respond && $respond['respCode'] == 0)
        {
            $user_model->payPassword = strtoupper(md5(strtoupper($pay_password)));
            $update_user = $user_model->updateData();
            return STATUS_SUCCESS;
        }
        else
        {
            $response->status = STATUS_PARAMETERS_INCOMPLETE;
            $response->description = $respond['respMsg'];
        }
    }
}
