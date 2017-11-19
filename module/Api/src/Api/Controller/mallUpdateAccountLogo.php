<?php
namespace Api\Controller;

/**
 * 更新用户头头像
 *
 *
 */
class mallUpdateAccountLogo extends mallBase
{
    public $method = 'mallUpdateAccountLogo';

    /**
     * @var 头像的Url。这里只返回名称命名。看下面命名规范。
     */
    public $logoUrl;

    /**
     * @var 头像图片的格式，jpg、png等
     */
    public $logoPicType;

    /**
     * @var图片的宽度
     */
    public $logoLength;

    /**
     * @var图片的高度
     */
    public $logoHeigth;

    public $request = ['logoUrl','logoPicType','logoLength','logoHeigth'];

    /**
     * java->php
     */
    public function index()
    {
        $image_full_path = $this->logoUrl;
        $image_exname = strrchr($image_full_path,'.');
        if(!$this->logoUrl || (strpos($image_full_path,"http://") !== 0 && strpos($image_full_path,"https://") !== 0) || !in_array($image_exname,['.jpg','.png','.jpeg','.gif']))
        {
            $this->respCode = 100;
            return $this->mallReturn();
        }
        $resource = file_get_contents($image_full_path);
        $path = date('Ymd')."/";
        $filename = $this->userId."_".date("YmdHis").$image_exname;
        $full_path = APP_PATH ."/".UPLOAD_PATH . $path . $filename;
        if(!is_dir(APP_PATH ."/".UPLOAD_PATH . $path))
        {
            mkdir(APP_PATH ."/".UPLOAD_PATH . $path , 0775,true);
        }
        $res = file_put_contents($full_path,$resource);
        if($res)
        {
            $image_model = $this->getImageTable();
            $image_model->filename = $filename;
            $image_model->path = $path;
            $image_model->width = $this->logoLength;
            $image_model->height = $this->logoHeigth;
            $id = $image_model->addData();
            if($id)
            {
                $user_model = $this->getUserTable();
                $user_model->image = $id;
                $user_model->userId = $this->userId;
                $user_details = $user_model->getUserDetailsByUserId();
                if(!$user_details)
                {
                    $this->respCode =300;
                }
                $user_model->id = $user_details->id;
                $user_model->userId = null;
                $result = $user_model->updateData();
                if($result)
                {
                    $this->respCode =0;
                }
                else
                {
                    $this->respCode =99;
                }
            }
            else
            {
                $this->respCode = 99;
            }
        }
        else
        {
            $this->respCode = 99;
        }
        return $this->mallReturn();
    }

    /**
     * php->java
     */
    public function submit()
    {
        if(!$this->logoUrl || !$this->logoPicType || !$this->logoLength || !$this->logoHeigth)
        {
            return false;
        }
        return $this->mallRequest();
    }

}