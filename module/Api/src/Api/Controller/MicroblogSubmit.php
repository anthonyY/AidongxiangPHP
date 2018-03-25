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
        $content = addslashes(strip_tags($request->message->content));
        if(!$content)
        {
            return false;
        }
        $leave_message_table = $this->getLeaveMessageTable();
        if($this->getUserType() == 1)
        {
            $leave_message_table->userType = 1;
        }

        $leave_message_table->content = $content;
        $leave_message_table->userId = $this->getUserId();

        $id =$leave_message_table->addData();
        $response->status = ($id ? STATUS_SUCCESS : STATUS_UNKNOWN); // 成功或未知错误
        $response->id = $id;
        return $response;
    }
}
