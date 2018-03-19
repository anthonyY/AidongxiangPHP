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
        if(!trim($user->nickName))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $user_table= $this->getUserTable();
        $user_table->id = $this->getUserId();
        $user_details = $user_table->getDetails();
        if(!$user_details)
        {
            return STATUS_USER_NOT_EXIST;
        }
        $user_table->nickName = $user->nickName;
        $user_table->sex = $user->sex;
        $user_table->regionId = $user->regionId;
        $user_table->headImageId = $user->headImageId;
        $user_table->description = $user->description;

        if($user->regionId)
        {
            $region = $this->getAdminCommonController()->getRegionInfoArray($user->regionId);
            if($region['region_info'])
            {
                $user_table->regionInfo = $region['region_info'];
            }
        }
        $user_table->updateData();
        return STATUS_SUCCESS;
    }
}
