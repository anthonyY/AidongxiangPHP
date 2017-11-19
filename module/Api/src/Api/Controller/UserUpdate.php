<?php
namespace Api\Controller;

use Api\Controller\Request\UserUpdateRequest;
use Platform\Model\RegionGateway;
use Platform\Model\UserGateway;
use Platform\Model\ViewAlbumGateway;


/**
 * 用户，更新个人信息
 * @author WZ
 */
class UserUpdate extends User
{

    public function __construct()
    {
        $this->myRequest = new UserUpdateRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();

        $user = $request->user;
        if(!trim($user->name))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $user_model = new UserGateway($this->adapter);
        $user_model->id = $this->getUserId();
        $user_details = $user_model->getDetails();
        $region_details = '';
        if($user->communityId)
        {
            $region_model = new RegionGateway($this->adapter);
            $region_model->id = $user->communityId;
            $region_details = $region_model->getDetails();
        }

        $mallUpdateAccountInfo = new mallUpdateAccountInfo();
        $mallUpdateAccountInfo->mobileNo = $user_details->mobile;
        $mallUpdateAccountInfo->userId = $user_details->user_id;
        $mallUpdateAccountInfo->nickName = $user->name;
        $mallUpdateAccountInfo->sex = $user->sex == 1 ? 1 : 0;
        $mallUpdateAccountInfo->address = $user->address;
        if(isset($region_details->uuid) && $region_details->uuid)
        {
            $mallUpdateAccountInfo->defaultCommunityNo = $region_details->uuid;
        }
        if($user->description)
        {
            $mallUpdateAccountInfo->remark = $user->description;
        }
        $mallUpdateAccountInfo->submit();
        $respond = $mallUpdateAccountInfo->getRespCode();
        if($respond && $respond['respCode'] == 0)
        {
            //更新头像
            if($user->image)
            {
                $view_album = new ViewAlbumGateway($this->adapter);
                $view_album->id = $user->image;
                $album = $view_album->getDetails();
                if($album)
                {
                    //调用php->Java更新用户头像接口start
                    $mallUpdateAccountLogo = new mallUpdateAccountLogo();
                    $mallUpdateAccountLogo->userId = $user_details->user_id;
                    $mallUpdateAccountLogo->mobileNo = $user_details->mobile;
                    $mallUpdateAccountLogo->logoUrl = IMAGE_PATH . $album->path . $album->filename;
                    $mallUpdateAccountLogo->logoLength = $album->width;
                    $mallUpdateAccountLogo->logoHeigth = $album->height;
                    $mallUpdateAccountLogo->logoPicType = substr(strrchr($album->filename,'.'),1);
                    $logo_respond = $mallUpdateAccountLogo->submit();
                    //调用php->Java更新用户头像接口end
                }
            }

            $user_model = $this->getUserTable();
            $user_model->name = $user->name;
            $user_model->realName = $user->realName;
            $user_model->sex = $user->sex;
            $user_model->regionId = $user->communityId ? $user->communityId : 0;
            $user_model->street = $user->address;
            $user_model->image = $user->image;
            $user_model->description = $user->description;
            if($user->communityId)
            {
                $region = $this->getPlatformCommonController()->getRegionInfoArray($user->communityId);
                if($region['region_info'])
                {
                    $user_model->regionInfo = $region['region_info'];
                    $user_model->address = $this->getPlatformCommonController()->getProvinceCityCountryName($region['region_info']).$user->address;
                }
            }
            $user_model->id = $this->getUserId();
            $user_model->updateData();
            return STATUS_SUCCESS;
        }
        else
        {
            $response->status = STATUS_UNKNOWN;
            $response->description = $respond['respMsg'];
            return $response;
        }
    }
}
