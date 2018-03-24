<?php
namespace Api\Controller;

use Api\Controller\Request\FavoritesSwitchRequest;

/**
 * 屏蔽或取消屏蔽
 * @author WZ
 * @version 1.0.140722
 */
class ScreenSwitch extends CommonController
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
        $action = $request->action;//1用户屏蔽(用户所有微博)2屏蔽微博，
        $open = $request->open;//1屏蔽；2取消屏蔽；
        $id = $request->id;

        if(!$id || !in_array($action,array(1,2)) || !in_array($open,[1,2])){
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $user_id = $this->getUserId();
        $screenTable = $this->getScreenTable();
        $res = $screenTable->screenSwitch($user_id,$action,$id,$open);
        $response->status = $res['s'];
        if(isset($res['d']))
        {
            $response->description = $res['d'];
        }
        return $response;
    }
}

