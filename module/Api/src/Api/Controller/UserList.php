<?php
namespace Api\Controller;

use Api\Controller\Request\AdListRequest;
use Zend\Db\Sql\Where;

/**
 * 用户列表
 *
 * @author WZ
 */
class UserList extends CommonController
{
    /**
     * @return Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action;
        if(!in_array($action,[1,2,3,4]))//a：1我的关注 2我的粉丝 3微博点赞用户列表, 4屏蔽的用户列表v2
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        if($action != 3)
        {
            $this->checkLogin();
        }

        $user_id = $this->getUserId();
        $list = array();
        $total = 0;

        if($action == 1 || $action == 2)////a：1我的关注 2我的粉丝
        {
            $this->tableObj = $this->getViewFocusRelationTable();
            $this->initModel();
            $data = $this->tableObj->getApiList($action,$user_id);

            $focusRelationTable = $this->getFocusRelationTable();
            if($action == 1)
            {
                foreach($data['list'] as $v)
                {
                    $item = array(
                        'id' => $v->target_user_id,
                        'name' => $v->be_user_nick_name,
                        'imagePath' => $v->be_user_image_path.$v->be_user_image_filename,
                        'description' => $v->be_user_description,
                        'isFocus' => 1,
                    );
                    $focusRelationTable->userId = $v->target_user_id;
                    $focusRelationTable->targetUserId = $user_id;
                    $res = $focusRelationTable->userFocusRelation();
                    if($res==4)$item['isFocus']=2;
                    $list[] = $item;
                }
            }
            elseif($action == 2)
            {
                foreach($data['list'] as $v)
                {
                    $item = array(
                        'id' => $v->user_id,
                        'name' => $v->nick_name,
                        'imagePath' => $v->image_path.$v->image_filename,
                        'description' => $v->user_description,
                        'isFocus' => 1,
                    );
                    $focusRelationTable->userId = $user_id;
                    $focusRelationTable->targetUserId = $v->user_id;
                    $res = $focusRelationTable->userFocusRelation();
                    if($res==4)$item['isFocus']=2;
                    $list[] = $item;
                }
            }
        }
        elseif($action == 3)//3微博点赞用户列表
        {
            $id = $request->id;
            if(!$id)return STATUS_PARAMETERS_CONDITIONAL_ERROR;
            $this->tableObj = $this->getPraiseTable();
            $this->initModel();
            $this->tableObj->fromId = $id;
            $this->tableObj->type = 3;
            $data = $this->tableObj->getPraiseUserList();
            if($data['list'])
            {
                $user_table = $this->getViewUserTable();
                foreach ($data['list'] as $v) {
                    $user_table->id = $v->user_id;
                    $user_info = $user_table->getDetails();
                    $item = array(
                        'id' => $v->user_id,
                        'name' => $user_info->nick_name,
                        'imagePath' => $user_info->head_path.$user_info->head_filename,
                    );
                    $list[] = $item;
                }
            }
        }
        elseif($action == 4)//4屏蔽的用户列表v2
        {
            $this->tableObj = $this->getScreenTable();
            $this->initModel();
            $this->tableObj->userId = $user_id;
            $data = $this->tableObj->getScreenUserList();
            if($data['list'])
            {
                $user_table = $this->getViewUserTable();
                foreach ($data['list'] as $v) {
                    $user_table->id = $v->from_id;
                    $user_info = $user_table->getDetails();
                    $item = array(
                        'id' => $v->id,
                        'fromId' => $user_info->id,
                        'name' => $user_info->nick_name,
                        'imagePath' => $user_info->head_path.$user_info->head_filename,
                    );
                    $list[] = $item;
                }
            }
        }

        $total = $data['total'] . '';
        $response->total = $total;
        $response->users = $list;
        return $response;
    }
}