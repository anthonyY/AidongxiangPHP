<?php
namespace Api\Controller;

use Api\Controller\Request\MicroblogRequest;

/**
 * 发布微博
 *
 * @author WZ
 * @version 1.0.140515 WZ
 *
 */
class MicroblogSubmit extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new MicroblogRequest();
        parent::__construct();
    }

    /**
     *
     * @return \Api\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $microblog = $request->microblog;
        if(!$microblog)
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $microblogTable = $this->getMicroblogTable();
        $user_id = $this->getUserId();
        $res = $microblogTable->MicroblogSubmit($microblog,$user_id);
        $response->status = $res['s'];
        if(isset($res['d']))
        {
            $response->description = $res['d'];
        }
        return $response;
    }
}
