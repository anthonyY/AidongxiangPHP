<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;

/**
 * 用户消息列表列表
 * @author WZ
 */
class NewsList extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $total = 0;
        $list = array();
        $action = $request->action ? $request->action : 1;
        if(!in_array($action,array(1,2)))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $user_id = $this->getUserId();
        $this->tableObj = $this->getViewNotificationRecordsTable();
        $this->initModel();
        $this->tableObj->orderBy = array('status' => 'ASC','id' => 'DESC');
        $this->tableObj->userId = $user_id;
        if(1 == $action)//系统消息
        {
            $this->tableObj->type = 1;
        }
        elseif(2 == $action)//订单消息
        {
            $this->tableObj->type = 2;
        }
        $data = $this->tableObj->getApiList($action);
        if($data['total'] > 0)
        {
            $type_array_map = [0=>0,1=>1,7=>2,11=>2];
            foreach ($data['list'] as $val) {
                $item = array(
                    'id' => $action == 1 && !in_array($val->type,[7,11]) ? $val->notification_id : $val->id,
                    'title' => $action == 1 && !in_array($val->type,[7,11]) ? $val->n_title : $val->title,
                    'content' => $action == 1 && !in_array($val->type,[7,11]) ? $val->n_content : $val->content,
                    'link' => $val->link,
                    'imagePath' => $val->path && $val->filename ? $val->path.$val->filename : '',
                    'orderId' => $val->o_uuid,
                    'timestamp' => $val->timestamp,
                    'type' => $val->type ? (isset($type_array_map[$val->type]) ? $type_array_map[$val->type] : 0) : 0
                );
                if($action == 2)
                {
                    //查询订单的一个商品
                    $order_goods_model = $this->getViewOrderGoodsTable();
                    $order_goods_model->orderId = $val->order_id;
                    $goods_info = $order_goods_model->getOneOrderGoods();
                    if($goods_info)
                    {
                        $item['content'] = $goods_info->goods_name;
                        $item['orderType'] = $goods_info->order_type;
                        $view_album_model = $this->getViewAlbumTable();
                        $view_album_model->type = 1;
                        $view_album_model->fromId = $goods_info->goods_id;
                        $album = $view_album_model->getDetails();
                        $item['imagePath'] = $album && $album->path && $album->filename ? $album->path . $album->filename : '';
                    }
                }
                $list[] = $item;
            }
            $total = $data['total'];
        }

        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->newses = $list;
        return $response;
    }
}
