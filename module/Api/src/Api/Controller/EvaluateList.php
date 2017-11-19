<?php
namespace Api\Controller;
use Api\Controller\Request\EvaluateListWhereRequest;

/**
 * 评价列表
 * @author liujun
 */
class EvaluateList extends CommonController
{
    public function __construct()
    {
        $this->myWhereRequest = new EvaluateListWhereRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $total = 0;
        $list = array();

        $table_where = $this->getTableWhere();
        $action = $request->action;//a：1我的评论（默认）2商品/服务的评论；3商家的评论；
        $type = $table_where->type ? $table_where->type : 1;//1全部评论，2好评，3中评，4差评，5有图片的评论 a!=1
        $id = $request->id;//a=2为商品/服务id; a=3为商家ID(uuid)
        if(!in_array($action,array(1,2,3,4)) || ($action != 1 && !$id))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $this->tableObj = $this->getViewEvaluateTable();
        $this->initModel();
        $this->tableObj->display = 1;
        $this->tableObj->action = $action;
        if($action == 1)//1我的评论（默认）
        {
            $this->checkLogin();
            $user_id = $this->getUserId();
            $this->tableObj->userId = $user_id;
        }
        elseif($action == 2) //2商品/服务的评论
        {
            $this->tableObj->gUuid = $id;
        }
        elseif($action == 3) //；3商家的评论
        {
            $this->tableObj->mUuid = $id;
        }
        elseif($action == 4)
        {
            $this->tableObj->orderUuid = $id;
        }
        $evaluates_num = array();
        if($action != 1 && $action != 4)
        {
            $evaluates_num = $this->tableObj->getAllNumberByMerchantOrGoods();
        }
        $data = $this->tableObj->getApiList($type);
        if($data['list'])
        {
            $view_album_model = $this->getViewAlbumTable();
            foreach ($data['list'] as $val) {
                $images = array();
                if($val->pic_show == 1)
                {
                    $view_album_model->type = 3;
                    $view_album_model->fromId = $val->id;
                    $albums = $view_album_model->getList();
                    if($albums)
                    {
                        foreach ($albums as $m) {
                            if($m['path'] && $m['filename'])
                            {
                                $images[] = $m['path'].$m['filename'];
                            }
                        }
                    }
                }
                $user = array(
                    'id' => $val->u_uuid,
                    'name' => $val->user_name,
                    'mobile' => $val->mobile,
                    'imagePath' => $val->u_path . $val->u_filename,
                );

                $view_album_model->type = 1;
                $view_album_model->fromId = $val->goods_id;
                $album = $view_album_model->getDetails();

                $goods = array(
                    'id' => $val->g_uuid,
                    'name' => $val->goods_name,
                    'attributes' => $val->attr_desc,
                    'price' => $val->price,
                    'imagePath' => $album && $album->path && $album->filename ? $album->path . $album->filename : '',
                );
                $item = array(
                    'id' => $val->id,
                    'stars' => $val->stars,
                    'content' => $val->content,
                    'reply' => $val->reply,
                    'images' => $images,
                    'timestamp' => $val->timestamp,
                    'user' => $user,
                    'goods' => $goods
                );
                $list[] = $item;
            }
            $total = $data['total'];
        }
        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->evaluates = $list;
        $response->evaluatesNum = $evaluates_num;
        return $response;
    }

}
