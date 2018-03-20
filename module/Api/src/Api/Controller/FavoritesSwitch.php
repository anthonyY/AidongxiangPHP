<?php
namespace Api\Controller;

use Api\Controller\Request\FavoritesSwitchRequest;

/**
 * 收藏或取消收藏
 * @author WZ
 * @version 1.0.140722
 */
class FavoritesSwitch extends CommonController
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

        if(!$request->id || !in_array($request->action,array(1,2)) || !in_array($request->open,[1,2])){
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $favorites_model = $this->getFavoriteTable();
        $favorites_model->userId = $this->getUserId();
        $favorites_model->type = $request->action;
        $favorites_model->audioId = $request->id;
        $status = $favorites_model->favoritesSwitch($request->open);
        return $status;
    }
}

