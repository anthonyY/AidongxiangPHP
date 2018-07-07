<?php
namespace Api\Controller;

use Api\Controller\Request\UserPartnerLoginRequest;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Platform\Model\LogGateway;

/**
 * 用户第三方登录
 *
 * @author WZ
 *
 */
class UserPartnerLogin extends User
{

    public function __construct()
    {
        $this->myRequest = new UserPartnerLoginRequest();
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
        $user_partner_table = $this->getUserPartnerTable();
        $table_columns = $user_partner_table->getTableColumns();
        foreach($table_columns as $v)
        {
            if(isset($request->$v) && $request->$v) $user_partner_table->$v = $request->$v;
        }

        if((!$user_partner_table->unionId && !$user_partner_table->openId) || !$user_partner_table->partner  || !in_array($request->partner,array(1,2,3)))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $user_partner = $user_partner_table->getDetails();
        if (! $user_partner)
        {
            $res = $user_partner_table->addData();
            if($res)
            {
                return STATUS_SUCCESS;
            }
            else
            {
                return STATUS_UNKNOWN;
            }

        }
        else
        {
            if($user_partner->user_id)
            {
                // 登录
                $user_table = $this->getUserTable();
                $user_table->id = $user_partner->user_id;
                $user_info = $user_table->getDetails();
                if(!$user_info)
                {
                    return STATUS_USER_NOT_EXIST;
                }
                $this->loginUpdate($user_info,1);
                $response->status = STATUS_SUCCESS;
                $response->id = $user_partner->user_id;
                return $response;

            }
            else
            {
                return STATUS_SUCCESS;
            }
        }
    }

    /**
     * 获取微信用户头像并插入用户表
     * @return number
     * @version 2017年9月22日
     * @author liujun
     */
    public function getWximage($img_url)
    {
        $image_id = 0;
        if($img_url)
        {
            $app_path = LOCAL_SAVEPATH;
            $file_path = date("Ymd").'/';
            $file_name = date("His").rand(1000, 9999).'.jpg';
            if(!is_dir($app_path.$file_path))
            {
                mkdir($app_path.$file_path,0775);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch , CURLOPT_URL , $img_url);
            $res = curl_exec($ch);
            $res = file_put_contents($app_path.$file_path.$file_name,$res);
            if($res)
            {
                $image_table = $this->getImageTable();
                $image_table->filename = $file_name;
                $image_table->path = $file_path;
                $image_id =$image_table->addData();
            }
        }
        else
        {
            $image_id = 0;
        }
        return $image_id;
    }
}