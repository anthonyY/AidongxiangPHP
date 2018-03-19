<?php
namespace Api\Controller;

use Api\Controller\Request\UserUpdateRequest;
use Platform\Model\RegionGateway;
use Platform\Model\UserGateway;
use Platform\Model\ViewAlbumGateway;


/**
 * 用户，更新个人头像
 * @author WZ
 */
class UserUpdateImage extends User
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

        $action = $request->action;//a:1更新头像，2更新背景
        $id = $request->id;
        if(!in_array($action,[1,2]) || !$id)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $user_table = $this->getUserTable();
        $user_table->id = $this->getUserId();
        if($action == 1)
        {
            $user_table->headImageId = $id;
        }
        elseif($action == 2)
        {
            $user_table->backImageId = $id;
        }
        $res = $user_table->updateData();
        return $res?STATUS_SUCCESS:STATUS_UNKNOWN;
    }
}
