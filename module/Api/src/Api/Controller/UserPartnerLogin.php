<?php
namespace Api\Controller;

use Api\Controller\Request\UserPartnerLoginRequest;
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
     *
     * @return string|\Api\Controller\Common\Response
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

        if((!$user_partner_table->unionId && !$user_partner_table->openId) || !$user_partner_table->partner  || !in_array($request->partner,array(1,2)))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $user_partner = $user_partner_table->getDetails();
        if (! $user_partner)
        {
            if($user_partner_table->imageUrl)
            {
                $user_partner_table->imageId = $this->getWximage($user_partner_table->imageUrl);
            }
            $res = $user_partner_table->addData();
            if($res)
            {
                $_SESSION['headImage']= $user_partner_table->imageId;//将图片写入session
                $response->description = '操作成功';
                $response->status = 0;
                $response->imgId = $user_partner_table->imageId;
                return $response;
            }
            else
            {
                return STATUS_UNKNOWN;
            }

        }
        elseif ($user_partner)
        {
            if($user_partner->user_id)
            {
                // 登录
                $user_table = $this->getUserTable();
                $user_table->id = $user_partner->user_id;
                $user_info = $user_table->getDetails();
                if(!$user_info)
                {
                    return STATUS_USERNAME_EXIST;
                }
                $this->loginUpdate($user_info);
                $response->id = $user_partner->user_id;
                $_SESSION['user_id'] = $user_info['id'];
                //记录用户登录日志
                $logModel = new LogGateway($this->adapter);
                $logModel->setUserLog($user_info->id, '手机wap网站');
                return $response;

            }
            else
            {
                return STATUS_SUCCESS;
            }
        }
        return STATUS_SUCCESS;
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