<?php
namespace Api\Controller;

use Api\Controller\Request\UserRequest;
use Zend\Db\Sql\Where;

/**
 * 用户注册协议
 * @author WZ
 */
class UserRegister extends User
{

    public function __construct()
    {
        $this->myRequest = new UserRequest();
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
        $action = $request->action ? $request->action : 1;//1普通注册 2第三方注册
        if(!$request->mobile || !preg_match("/^1[345789]{1}\d{9}$/", $request->mobile) || !$request->nickName || !$request->smscodeId || !in_array($action,array(1,2))){
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $this->tableObj = $this->getUserTable();
        if($action == 2)
        {
            //第三方注册绑定参数判断
            if((!$request->unionId && !$request->openId) || !$request->partner || !in_array($request->partner,array(1,2,3)))
            {
                return STATUS_PARAMETERS_CONDITIONAL_ERROR;
            }
        }
        else
        {
            if(!$request->password)
            {
                return STATUS_PARAMETERS_CONDITIONAL_ERROR;
            }
            $this->tableObj->password = md5($request->password);
        }


        $this->checkSmsComplete(1, $request->smscodeId, $request->mobile); // 注册，检查是否有效，无效返回1010，请求超时
        $this->tableObj->mobile = $request->mobile;
        $this->tableObj->nickName = $request->nickName;
        if($request->headImageId)
        {
            $this->tableObj->headImageId = $request->headImageId;
        }
        $user_info = $this->tableObj->checkMobile();
        if($user_info)
        {
            return STATUS_USER_EXIST;
        }

        if($action ==2)
        {
            $userPartnerTable = $this->getUserPartnerTable();
            $userPartnerTable->openId = $request->openId;
            $userPartnerTable->unionId = $request->unionId;
            $userPartnerTable->partner = $request->partner;
            $userPartner = $userPartnerTable->getDetails();
            if(!$userPartner)
            {
                $response->status = STATUS_UNKNOWN;
                $response->description = '第三方登录信息不存在';
                return $response;
            }
            $this->tableObj->password = md5(md5(123456));
            $user_id = $this->tableObj->addData();

            $userPartnerTable->userId = $user_id;
            $userPartnerTable->id = $userPartner->id;
            $userPartnerTable->updateData();//把生成的用户ID更新到第三方登录表

        }
        else
        {
            $user_id = $this->tableObj->addData();
        }
        $this->tableObj->id = $user_id;
        $user_info = $this->tableObj->getDetails();

        // 更新各个表
        $this->loginUpdate($user_info, 1);
        $response->id = $user_id;
        return $response;
    }
}
