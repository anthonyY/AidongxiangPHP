<?php
namespace Api\Controller;

use Api\Controller\Request\FavoritesSwitchRequest;

/**
 * 关注或取消关注
 * @author WZ
 * @version 1.0.140722
 */
class FocusSwitch extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new FavoritesSwitchRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $open = $request->open;//1关注；2取消关注；
        $be_user_id = $request->userId;
        $user_id = $this->getUserId();

        if(!$be_user_id || $user_id == $be_user_id || !in_array($open,[1,2])){
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $FocusRelationTable = $this->getFocusRelationTable();
        $res = $FocusRelationTable->focusSwitch($user_id,$be_user_id,$open);
        $response->status = $res['s'];
        if(isset($res['d']))
        {
            $response->description = $res['d'];
        }
        return $response;
    }
}

