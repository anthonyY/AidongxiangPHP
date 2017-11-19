<?php
namespace Api\Controller;

use Core\System\UploadfileApi;
use Core\System\Image;

/**
 * 新增账户
 *
 */
class mallRegisterAccount extends mallBase
{
    public $method = 'mallRegisterAccount';

    /**
     * 用户昵称
     */
    public $nickName;

    /**
     * 性别 0 - 女； 1 – 男
     */
    public $sex;

    /**
     * Md5加密(对密码两次MD5加密)
     */
    public $password;

    /**
     * 完整的logo图片url
     */
    public $logoUrl;

    /**
     * Logo图片格式
     */
    public $picType;

    /**
     * 默认小区编号
     */
    public $defaultCommunityNo = '000001';

    /**
     * 用户生日。格式如2017-01-01
     */
    public $birthday;

    /**
     * 用户地址
     */
    public $address;

    /**
     * 用户签名
     */
    public $remark;

    public $request = ['nickName','sex','password','defaultCommunityNo','birthday','address','remark',"picType","logoUrl"];

    public $return = ['userId'];

    /**
     * java->PHP
     */
    public function index()
    {
        $image_id = 0;
        $image_full_path = $this->logoUrl;
        $image_exname = strrchr($image_full_path,'.');
        if($this->logoUrl && (strpos($image_full_path,"http://") === 0 || strpos($image_full_path,"https://") === 0) && in_array($image_exname,['.jpg','.png','.jpeg','.gif']))
        {
            $resource = file_get_contents($image_full_path);
            $path = date('Ymd')."/";
            $filename = $this->userId."_".date("YmdHis").$image_exname;
            $full_path = APP_PATH ."/". UPLOAD_PATH . $path . $filename;
            if(!is_dir(APP_PATH ."/". UPLOAD_PATH . $path))
            {
                mkdir(APP_PATH ."/". UPLOAD_PATH . $path , 0775,true);
            }
            $res = file_put_contents($full_path,$resource);
            if($res)
            {
                $image_model = $this->getImageTable();
                $image_model->filename = $filename;
                $image_model->path = $path;
                $image_id = $image_model->addData();
            }
        }
        $user_model = $this->getUserTable();
        $user_model->name = $this->nickName;
        $user_model->sex = $this->sex == 1 ? 1 : 2;
        $user_model->password = $this->password;
        $user_model->iniRegionId = $this->defaultCommunityNo;
        $user_model->street = $this->address;
        $user_model->mobile = $this->mobileNo;
        $user_model->userId = $this->userId;
        $user_model->image = $image_id;
        $res = $user_model->javaRegister();
        if($res['code'] == 0)
        {
            $this->respCode = 0;
        }
        else
        {
            $this->respCode = 99;
        }
        return $this->mallReturn();
    }

    /**
     * PHP->JAVA
     */
    public function submit()
    {
        if(!$this->nickName || !in_array($this->sex,array('0','1')) || !$this->password)
        {
            return false;
        }
        return $this->mallRequest();
    }

}