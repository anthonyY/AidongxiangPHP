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
     * 返回一个数组或者Result类
     * @return \Api\Controller\BaseResult
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action ? $request->action : 1;//1普通注册 2第三方注册
        $type = $action;
        if(!$request->mobile || !preg_match("/^1[345789]{1}\d{9}$/", $request->mobile) || !$request->name || !$request->smscodeId || !in_array($action,array(1,2))){
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $this->tableObj = $this->getUserTable();
        if($action == 2)
        {//第三方注册绑定参数判断
            if((!$request->unionId && !$request->openId) || !$request->partner || !in_array($request->partner,array(1,2)))
            {
                return STATUS_PARAMETERS_CONDITIONAL_ERROR;
            }
            $type = 11;//转为第三方登录验证判断
        }
        else
        {
            if(!$request->password)
            {
                return STATUS_PARAMETERS_CONDITIONAL_ERROR;
            }
            $this->tableObj->password = strtoupper(md5(strtoupper($request->password)));
        }

        $this->checkSmsComplete($type, $request->smscodeId, $request->mobile); // 注册，检查是否有效，无效返回1010，请求超时
        if($request->referrerId)
        {
            $user_table = $this->getUserTable();
            $user_table->id =$request->referrerId;
            $user = $user_table->getDetails();
            if($user)
            {//用户存在，才把推荐人赋值
                $this->tableObj->referrerId = $user->id;
            }
        }
        $this->tableObj->mobile = $request->mobile;
        $this->tableObj->name = $request->name;
        if($request->image)
        {
            $this->tableObj->image = $request->image;
        }

        if($action ==2)
        {
            $user = $this->tableObj->checkMobile();
            if(!$user)
            {
                $this->tableObj->password = strtoupper(md5(strtoupper(123456)));
                $res = $this->tableObj->register();
            }
            else
            {
                $res['info'] = $user;
                $res['code'] = STATUS_SUCCESS;
            }
        }
        else
        {
            $res = $this->tableObj->register();
        }

        $response->status = $res['code'];
        if($res['code'] == STATUS_SUCCESS)
        {
            if($action ==2)
            {//第三方登录
                $user_partner_table = $this->getUserPartnerTable();
                $user_partner_table->unionId = $request->unionId;
                $user_partner_table->openId = $request->openId;
                $user_partner_table->partner = $request->partner;
                $user_partner = $user_partner_table->getDetails();
                if($user_partner)
                {
                    if($res['info']['id'])
                    {
                        $user_partner_table = $this->getUserPartnerTable();
                        $user_partner_table->userId = $res['info']['id'];
                        $user_partner_table->id = $user_partner->id;
                        $user_partner_table->updateData();//把生成的用户ID更新到第三方登录表
                    }
                }
            }
            // 更新各个表
            $this->loginUpdate($res['info'], 1);
            $_SESSION['user_id'] = $res['info']['id'];
            $_SESSION['user_name'] = $res['info']['name'];
            $response->id = $res['info']['uuid'];
        }
        else
        {
            if(isset($res['d']))
            {
                $response->description = $res['d'];
            }
        }
        return $response;
    }
}
