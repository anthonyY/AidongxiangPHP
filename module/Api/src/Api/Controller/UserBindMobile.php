<?php
namespace Api\Controller;

use Api\Controller\Request\UserRequest;

/**
 * 用户绑定手机
 * @author WZ
 */
class UserBindMobile extends User
{
    public function __construct()
    {
        $this->myRequest = new UserRequest ();
        parent::__construct();
    }

    /**
     * @return string
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        if(!$request->mobile)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $type = 1;//注册的类型用作更换手机第二部
        $this->checkSmsComplete($type, $request->smscodeId, $request->mobile); // 注册，检查是否有效，无效返回1010，请求超时

        $user_id = $this->getUserId();
        $user_model = $this->getUserTable();
        $user_model->mobile = $request->mobile;
        $user_model->id = $user_id;
        $result = $user_model->userBindMobile();
        $response->status = $result['code'];
        if(isset($result['d']))
        {
            $response->description = $result['d'];
        }
        return $response;
    }
}
