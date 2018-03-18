<?php
namespace Api\Controller;

use Api\Controller\Request\DeleteActionRequest;

/**
 * 删除对象<br />
 * action类型，1.删除车辆
 *
 * @author WZ
 *
 */
class DeleteAction extends CommonController
{

    function __construct()
    {
        $this->myRequest = new DeleteActionRequest();
        parent::__construct();
    }

    /**
     *
     * @return string|\Api\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $action = $request->action ? $request->action : 0;//类型：1删除评论，2删除微博，3收藏 4观看记录
        $ids = (array)$request->ids;
        if(!is_array($ids) || !$ids || !in_array($action,[1,2,3,4])){
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        switch($request->action){
            case 1:
                // 1删除评论
                $comment_table = $this->getCommentTable();
                $comment_table->userId = $this->getUserId();
                $res = $comment_table->deleteByIds($ids);
                break;
            case 2:
                // 2删除微博
                $microblog_table = $this->getMicroblogTable();
                $microblog_table->userId = $this->getUserId();
                $res = $microblog_table->deleteByIds($ids);
                break;
            case 3:
                // 3收藏
                $favorite_table = $this->getFavoriteTable();
                $favorite_table->userId = $this->getUserId();
                $res = $favorite_table->deleteByIds($ids);
                break;
            case 4:
                //4观看记录
                $WatchRecordTable = $this->getWatchRecordTable();
                $WatchRecordTable->userId = $this->getUserId();
                $res = $WatchRecordTable->deleteByIds($ids);
                break;
            default:
                // 请求参数不完整
                return STATUS_PARAMETERS_INCOMPLETE;
                break;
        }
        $response->status = $res['s'];
        $response->description = $res['d'];
        return $response;
    }
}
