<?php
namespace Api\Controller;

use Api\Controller\Request\FavoritesSwitchRequest;

/**
 * 点赞或取消点赞
 * @author WZ
 * @version 1.0.140722
 */
class PraiseSwitch extends CommonController
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
        $action = $request->action;//a:1音频点赞，2评论点赞，3微博点赞
        $open = $request->open;//1点赞；2取消点赞；
        $id = $request->id;

        if(!$id || !in_array($action,array(1,2,3)) || !in_array($open,[1,2])){
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $user_id = $this->getUserId();
        $praiseTable = $this->getPraiseTable();
        $res = $praiseTable->praiseSwitch($user_id,$action,$id,$open);
        $response->status = $res['s'];
        if(isset($res['d']))
        {
            $response->description = $res['d'];
        }
        return $response;
    }
}

