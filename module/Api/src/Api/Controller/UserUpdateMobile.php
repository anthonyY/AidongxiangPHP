<?php
namespace Api\Controller;

use Api\Controller\Request\UserRequest;

/**
 * 用户绑定手机
 * @author WZ
 */
class UserUpdateMobile extends User
{
    public function __construct()
    {
        $this->myRequest = new UserRequest ();
        parent::__construct();
    }

    /**
     * @return Common\Response|string
     * @throws \Exception
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        if(!preg_match("/^1[345789]{1}\d{9}$/", $request->mobile))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $type = 2;
        $this->checkSmsComplete($type, $request->smscodeId, $request->mobile); // 注册，检查是否有效，无效返回1010，请求超时

        $user_id = $this->getUserId();
        $user_model = $this->getUserTable();
        $user_model->mobile = $request->mobile;
        $user_model->id = $user_id;
        $user_info = $user_model->getDetails();
        if(!$user_info)
        {
            return STATUS_USER_NOT_EXIST;
        }
        if($user_info->mobile == $request->mobile)
        {
            $response->status = 10000;
            $response->description = '新手机号码不能与旧手机一致';
            return $response;
        }
        $res = $user_model->checkOtherMobile($request->mobile,$user_id);
        if($res)
        {
            return STATUS_MOBILE_EXIST;
        }
        $result = $user_model->updateData();
        return $result?STATUS_SUCCESS:STATUS_UNKNOWN;
    }
}
