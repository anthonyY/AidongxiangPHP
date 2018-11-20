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
     * @return Common\Response|string
     * @throws \Exception
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action;//1关注微博，2热门微博，3个人微博，4微博转发列表，5屏蔽的微博列表v2
        if(!in_array($action,[1,2,3,4,5]))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $user_id = 0;
        $parent_id = 0;
        if(in_array($action,[1,5]))
        {
            $this->checkLogin();
            $user_id = $this->getUserId();
        }
        if($action == 4)
        {
            if(!$request->id)return STATUS_PARAMETERS_CONDITIONAL_ERROR;
            $parent_id = $request->id;
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
        if($action == 2){
            $user_id = $this->getUserId();
            $user_id = $user_id ? $user_id : 0;
        }
        $list = array();
        $user_table = $this->getUserTable();
        $image = $this->getImageTable();
        $viewAlbum = $this->getViewAlbumTable();
        $viewAlbum->type = 1;
        $praiseTable = $this->getPraiseTable();
        $praiseTable->userId = $this->getUserId();
        $praiseTable->type = 3;
        if($action != 5)
        {
            $this->tableObj = $this->getViewMicroblogTable();
            $this->initModel();
            $this->tableObj->userId = $user_id;
            $this->tableObj->parentId = $parent_id;
            $data = $this->tableObj->getApiList($action,$user_id);
            if(!$data)
            {
                return STATUS_PARAMETERS_CONDITIONAL_ERROR;
            }
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
                    'isPraise' => 1,
                    'user' => [
                        'id' => $v->user_id,
                        'nickName' => $v->nick_name,
                        'imagePath' => ''
                    ],
                );
                //用户头像和小视频
                if($v->head_image_id || $v->video_id)
                {
                    if($v->head_image_id)//用户头像
                    {
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
                    if($v->video_id)//小视频
                    {
                        $image->id = $v->video_id;
                        $little_video = $image->getDetails();
                        if($little_video)$item['videoPath'] = $little_video->path.$little_video->filename;
                    }
                }
                //图片集
                $viewAlbum->fromId = $v->id;
                $album_list = $viewAlbum->getList();
                $images = [];
                if($album_list['total'] > 0)
                {
                    foreach ($album_list['list'] as $m) {
                        $images[] = $m->path.$m->filename;
                    }
                }
                $item['images'] = $images;

                //关注关系
                $relation = 1;
                if($this->getUserId())
                {
                    $FocusRelation = $this->getFocusRelationTable();
                    $FocusRelation->userId = $this->getUserId();
                    $FocusRelation->targetUserId = $v->user_id;
                    $relation = $FocusRelation->userFocusRelation();
                }
                $item['isFocus'] = $relation;

                $praiseTable->fromId = $v->id;
                $praise_res = $praiseTable->checkUserPraise();
                if($praise_res)$item['isPraise'] = 2;

                if($action < 4 && $v->parent_id)//父微博
                {
                    $this->tableObj->id = $v->parent_id;
                    $parent_info = $this->tableObj->getDetails();
                    if($parent_info)
                    {
                        $item['parent'] = [
                            'id' => $v->parent_id,
                            'content' => $parent_info->content,
                            'user' => ['id'=>$parent_info->user_id,'nickName'=>'']
                        ];
                        $user_table->id = $parent_info->user_id;
                        $parent_user_info = $user_table->getDetails();
                        if($parent_user_info)$item['parent']['user']['nickName'] = $parent_user_info->nick_name;
                        if($parent_info->video_id)//小视频
                        {
                            $image->id = $v->video_id;
                            $little_video = $image->getDetails();
                            if($little_video)$item['parent']['videoPath'] = $little_video->path.$little_video->filename;
                        }
                        //图片集
                        $viewAlbum->fromId = $v->parent_id;
                        $album_list = $viewAlbum->getList();
                        $images = [];
                        if($album_list['total'] > 0)
                        {
                            foreach ($album_list['list'] as $m) {
                                $images[] = $m->path.$m->filename;
                            }
                        }
                        $item['parent']['images'] = $images;
                    }
                }

                $list[] = $item;
            }
        }
        else //屏蔽的微博列表v2
        {
            $this->tableObj = $this->getScreenTable();
            $this->initModel();
            $this->tableObj->userId = $user_id;
            $data = $this->tableObj->getMicroblogList();
            if($data['list'])
            {
                $view_microblog_table = $this->getViewMicroblogTable();
                foreach ($data['list'] as $v) {
                    $view_microblog_table->id = $v->from_id;
                    $info = $view_microblog_table->getDetails();
                    $item = [
                        'id' => $v->id,
                        'fromId' => $v->from_id,
                        'content' => $info->content,
                        'isPraise' => 1,
                        'user' => [
                            'id' => $info->user_id,
                            'nickName' => $info->nick_name,
                            'imagePath' => ''
                        ],
                    ];
                    //用户头像
                    if($info->head_image_id)
                    {
                        $image->id = $info->head_image_id;
                        $head = $image->getDetails();
                        if($head)
                        {
                            $item['user']['imagePath'] = $head->path.$head->filename;
                        }
                        else
                        {
                            $user_partner = $this->getUserPartnerTable();
                            $user_partner_info = $user_partner->getDetailsByUserId($info->user_id);
                            if($user_partner_info)$item['user']['imagePath'] = $user_partner_info->image_url;
                        }
                    }

                    $praiseTable->fromId = $v->from_id;
                    $praise_res = $praiseTable->checkUserPraise();
                    if($praise_res)$item['isPraise'] = 2;
                    $list[] = $item;
                }
            }
        }

        $response->total =  $data['total'] . '';
        $response->microblogs = $list;
        return $response;
    }

    /**
     * 排序字段
     * @param int $order_by
     * @return string
     */
    public function OrderBy($order_by = 1)
    {
        $result = "id";
        return $result;
    }
}