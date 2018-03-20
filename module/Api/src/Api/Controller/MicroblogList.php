<?php
namespace Api\Controller;

use Api\Controller\Request\AdListRequest;
use Api\Controller\Request\MicroblogWhereRequest;
use Zend\Db\Sql\Where;

/**
 * 微博协议
 *
 * @author WZ
 * @version 1.0.140515
 */
class MicroblogList extends CommonController
{

    public function __construct()
    {
        $this->myWhereRequest = new MicroblogWhereRequest();
        parent::__construct();
    }

    /**
     * @return Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action;//1关注微博，2热门微博，3个人微博
        if(!in_array($action,[1,2,3]))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $user_id = 0;
        if($action == 1)
        {
            $this->checkLogin();
            $user_id = $this->getUserId();
        }
        if($action == 3)
        {
            $where = $this->getTableWhere();
            $user_id = $where->userId;
            if(!$user_id)
            {
                $this->checkLogin();
                $user_id = $this->getUserId();
            }
        }
        $this->tableObj = $this->getViewMicroblogTable();
        $this->initModel();
        $this->tableObj->userId = $user_id;
        $data = $this->tableObj->getApiList($action);
        if(!$data)
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $list = array();
        foreach($data['list'] as $v)
        {
            $item = array(
                'id' => $v->id,
                'content' => $v->content,
                'timestamp' => $v->timestamp,
                'address' => $v->address,
                'praiseNum' => $v->praise_num,
                'commentNum' => $v->comment_num,
                'repeatNum'=> $v->repeat_num,
                'user' => [
                    'id' => $v->user_id,
                    'nickName' => $v->nick_name,
                    'imagePath' => ''
                ],
            );
            //用户头像
            if($v->head_image_id)
            {
                $image = $this->getImageTable();
                $image->id = $v->head_image_id;
                $head = $image->getDetails();
                if($head)
                {
                    $item['user']['imagePath'] = $head->path.$head->filename;
                }
                else
                {
                    $user_partner = $this->getUserPartnerTable();
                    $user_partner_info = $user_partner->getDetailsByUserId($v->user_id);
                    if($user_partner_info)$item['user']['imagePath'] = $user_partner_info->image_url;
                }
            }
            $list[] = $item;
        }
        $response->total =  $data['total'] . '';
        $response->ads = $list;
        return $response;
    }

    /**
     * 排序字段
     * @param int $order_by
     * @return string
     */
    public function OrderBy($order_by = 1)
    {
        $result = "sort";
        return $result;
    }
}