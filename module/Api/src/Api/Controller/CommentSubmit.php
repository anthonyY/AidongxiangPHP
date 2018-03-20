<?php
namespace Api\Controller;

use Api\Controller\Request\CommentRequest;

/**
 * 评论
 *
 * @author WZ
 * @version 1.0.140515 WZ
 *
 */
class CommentSubmit extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new CommentRequest();
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
        $content = addslashes(strip_tags($request->content));
        $action = $request->action;//1音频评论，2视频评论，3微博评论
        $id = $request->id;//音频ID|微博ID
        if(!$content || !$id || !in_array($action,[1,2,3]))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $commentTable = $this->getCommentTable();
        $commentTable->userId = $this->getUserId();
        $commentTable->content = $content;
        $commentTable->fromId = $id;
        $commentTable->type = $action;
        $res = $commentTable->CommentSubmit();
        $response->status = $res['s'];
        if(isset($res['d']))
        {
            $response->description = $res['d'];
        }
        return $response;
    }
}
