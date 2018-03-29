<?php
namespace Api\Controller;

use Api\Controller\Request\AudioWhereRequest;
use Zend\Db\Sql\Where;

/**
 * 评论列表列表
 * @author lzw
 */
class CommentList extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action;//a：评论所属：1 音频 2 视频 3微博
        $id = $request->id;//视频ID|微博ID
        if(!in_array($action,[1,2,3]) || !$id){
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $this->tableObj = $this->getViewCommentTable();
        $this->initModel();
        $this->tableObj->type = $action;
        $this->tableObj->fromId = $id;
        $data = $this->tableObj->getApiList();

        $total = 0;
        $list = [];

        $praiseTable = $this->getPraiseTable();
        $praiseTable->userId = $this->getUserId();
        $praiseTable->type = 2;
        if($data['list'])
        {
            foreach ($data['list'] as $val) {
                $item = array(
                    'id' => $val->id,
                    'content' => $val->content,
                    'timestamp' => $val->timestamp,
                    'praiseNum' => $val->praise_num,
                    'isPraise' => 1,
                    'user' => [
                        'id' => $val->user_id,
                        'name' => $val->nick_name,
                        'imagePath' => $val->image_path.$val->image_filename
                    ],
                );

                $praiseTable->fromId = $val->id;
                $praise_res = $praiseTable->checkUserPraise();
                if($praise_res)$item['isPraise'] = 2;
                $list[] = $item;
            }
            $total = $data['total'];
        }

        $response->status = STATUS_SUCCESS;
        $response->comments = $list;
        $response->total = $total;
        return $response;
    }
}
