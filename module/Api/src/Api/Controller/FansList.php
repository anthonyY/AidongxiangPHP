<?php
namespace Api\Controller;

use Api\Controller\Request\AdListRequest;
use Zend\Db\Sql\Where;

/**
 * 粉丝列表
 *
 * @author WZ
 */
class FansList extends CommonController
{
    /**
     * @return Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action;
        if(!in_array($action,[1,2]))//a：1我的关注 2我的粉丝
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $this->checkLogin();
        $user_id = $this->getUserId();
        $this->tableObj = $this->getViewFocusRelationTable();
        $this->initModel();
        $data = $this->tableObj->getApiList($action,$user_id);
        $list = array();
        $total = 0;

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
                $focusRelationTable->userId = $v->target_user_id;
                $focusRelationTable->targetUserId = $user_id;
                $res = $focusRelationTable->userFocusRelation();
                if($res==4)$item['isFocus']=2;
                $list[] = $item;
            }
        }
        $total = $data['total'] . '';
        $response->total = $total;
        $response->fanses = $list;
        return $response;
    }
}