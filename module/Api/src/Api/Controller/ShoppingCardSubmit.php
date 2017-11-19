<?php
namespace Api\Controller;

use Api\Controller\Request\ShoppingCardSubmitRequest;

/**
 * 添加/修改地址
 */
class ShoppingCardSubmit extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new ShoppingCardSubmitRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        if(!$request->cardNumber || !$request->password || !$request->verificationCode)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        if(isset($_SESSION['captcha']))
        {
            if($_SESSION['captcha'] != strtolower($request->verificationCode))
            {
                $response->status = STATUS_PARAMETERS_INCOMPLETE;
                $response->description = '验证码错误';
                return $response;
            }

        }
        else
        {
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            $res = $redis->get($this->getSessionId());//将图形验证码写入redis
            if(!$res)
            {
                $response->status = STATUS_PARAMETERS_INCOMPLETE;
                $response->description = '验证码错误';
                return $response;
            }
            else
            {
                if($res != strtolower($request->verificationCode))
                {
                    $response->status = STATUS_PARAMETERS_INCOMPLETE;
                    $response->description = '验证码错误';
                    return $response;
                }
            }
        }


        unset($_SESSION['captcha']);
        $this->tableObj = $this->getShoppingCardTable();
        $this->tableObj->userId = $this->getUserId();
        $this->tableObj->cardNumber = $request->cardNumber;
        $this->tableObj->password = $request->password;
        $res = $this->tableObj->ShoppingCardSubmit();
        $response->status = $res['s'];
        if($res['s'] == STATUS_SUCCESS)
        {
            $response->id = $res['id'];
        }
        if(isset($res['d']))
        {
            $response->description = $res['d'];
        }
        return $response;
    }
}
