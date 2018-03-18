<?php
namespace Api\Controller;

use Api\Controller\Request\MobileAppealRequest;


/**
 * 手机申诉
 *
 * @author WZ
 * @version 1.0.140515 WZ
 *
 */
class MobileAppealSubmit extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new MobileAppealRequest();
        parent::__construct();
    }

    /**
     *
     * @return \Api\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $mobile = $request->mobile;
        $newMobile = $request->newMobile;
        $password = $request->password;
        $registerTime = $request->registerTime;

        if(!preg_match("/^1[345789]{1}\d{9}$/", $mobile))
        {
            return STATUS_MOBILE_ERROR;
        }

        if(!preg_match("/^1[345789]{1}\d{9}$/", $newMobile))
        {
            return STATUS_MOBILE_ERROR;
        }

        if($mobile === $newMobile)
        {
            $response->status = 10000;
            $response->description = '不能更旧手机一致';
            return $response;
        }
        if(!$password || !$registerTime)
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }

        $user = $this->getUserTable();
        $user->mobile = $mobile;
        $user->password = md5($password);
        $user_info = $user->userLogin();
        if(!$user_info)
        {
            return STATUS_PASSWORD_ERROR;
        }
        $mobileAppeal = $this->getMobileAppealTable();
        $mobileAppeal->newMobile = $newMobile;
        $mobileAppeal->registerTime = $registerTime;
        $mobileAppeal->userId = $user_info->id;
        $res = $mobileAppeal->mobileAppealSubmit();
        $response->status = $res['s']; // 成功或未知错误
        $response->description = $res['d'];
        return $response;
    }
}
