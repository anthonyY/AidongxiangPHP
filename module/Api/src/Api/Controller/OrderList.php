<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;
use Api\Controller\Request\OrderListWhereRequest;
use Zend\Db\Sql\Select;

/**
 * 业务，订单列表
 */
class OrderList extends CommonController
{
    public function __construct()
    {
        $this->myWhereRequest = new OrderListWhereRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $table_where = $this->getTableWhere();
        //订单状态：商品的状态：0 全部 1 待支付 2 待发货 2 已发货；4 待评价(已收货/待评价) ,7待拼团V2.0
        //服务的状态：0 全部1 待支付 2 待消费(已支付) 3 待评价（已消费）,6待拼团V2.0
        $status = $table_where->status;
        $evaluateType = $table_where->evaluateType;//1待评价和已评价的订单PC
        if(!in_array($request->action,array(1,2,3)))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $total = 0;
        $list = array();
        $ViewUserGroupBuyingTable = $this->getViewUserGroupBuyingTable();
        $groupBuying = '';
        if(in_array($status,[6,7]))//待拼单V2.0
        {
            $ViewUserGroupBuyingTable = $this->getViewUserGroupBuyingTable();
            $ViewUserGroupBuyingTable->userId = $this->getUserId();
            $ViewUserGroupBuyingTable->oStatus = $status;
            $ViewUserGroupBuyingTable->oType = $request->action;
            $data = $ViewUserGroupBuyingTable->getOrderList();
            if(isset($data['list']) && $data['list'])
            {
                $view_order_goods_model = $this->getViewOrderGoodsTable();
                foreach ($data['list'] as $val) {
                    $view_order_goods_model->orderId = $val->order_id;
                    $order_goods = $view_order_goods_model->getList();
                    $order_goodses = array();
                    if($order_goods['list'])
                    {
                        $view_album_model = $this->getViewAlbumTable();
                        $view_album_model->type = 1;
                        foreach ($order_goods['list'] as $m) {
                            $view_album_model->fromId = $m->goods_id;
                            $album = $view_album_model->getDetails();
                            $item = array(
                                'id' => $m->id,
//                                'goodsId' => $m->goods_id,
                                'goodsId' => $m->g_uuid,
                                'name' => $m->goods_name,
                                'cash' => $m->price,
                                'number' => $m->number,
                                'imagePath' => $album && $album->path && $album->filename ? $album->path . $album->filename : '',
                                'attrDesc' => $m->attr_desc,
                            );
                            $order_goodses[] = $item;
                        }
                    }
                    $merchant = array(
                        'id' => $val->m_uuid,
                        'type' => $val->m_type,
                        'name' => $val->m_name,
                        'telephone' => $val->m_telephone ? $val->m_telephone : $val->m_mobile,
                        'orderGoodses' => $order_goodses,
                    );
                    $item = array(
                        'id' => $val->o_uuid,
                        'totalCash' => round($val->total_cash - $val->preferential_amount,2),
                        'orderSn' => $val->o_uuid,
                        'number' => count($order_goodses),
                        'status' => $val->o_status,
                        'serviceStatus' => $val->service_status,
                        'surplusPerson' => 0,
                        'merchant' => $merchant,
                        'timestamp' => $val->o_timestamp,
                        'consignee' => $val->o_type == 1 ? $val->o_name : $val->u_name
                    );

                    if($val->parent_id == 0)//自己就是团主
                    {
//                        $item['surplusPerson'] = $val->group_number - $val->member_number;
                        $groupBuying = [
                            'surplusPerson' => $val->group_number - $val->member_number,
                            'attrIds' => $val->attr_ids,
                            'groupBuyingId' => $val->id,
                        ];
                    }
                    else
                    {
                        $ViewUserGroupBuyingTable->id = $val->parent_id;
                        $ViewUserGroupBuyingTable->groupBuyingGoodsId = $val->group_buying_goods_id;
                        $parent_details = $ViewUserGroupBuyingTable->getParentDetails();
//                        $item['surplusPerson'] = $parent_details ? ($val->group_number - $parent_details->member_number) : $val->group_number;
                        $groupBuying = [
                            'surplusPerson' => $parent_details ? ($val->group_number - $parent_details->member_number) : $val->group_number,
                            'attrIds' => $parent_details->attr_ids,
                            'groupBuyingId' => $parent_details->id,
                        ];
                    }
                    $item['groupBuying'] = $groupBuying;
                    $list[] = $item;
                }
                $total = $data['total'];
            }
        }
        else
        {
            $this->tableObj = $this->getViewOrderTable();
            $this->initModel();
            $this->tableObj->type = $request->action;
            $this->tableObj->status = $status;
            $this->tableObj->userId = $this->getUserId();
            if($evaluateType)
            {
                $this->tableObj->evaluateType = $evaluateType;
            }

            $data = $this->tableObj->getApiList();
            if($data['list'])
            {
                $view_order_goods_model = $this->getViewOrderGoodsTable();
                foreach ($data['list'] as $val) {
                    $order_goods_number = 0;
                    $view_order_goods_model->orderId = $val->id;
                    $order_goods = $view_order_goods_model->getList();
                    $order_goodses = array();
                    if($order_goods['list'])
                    {
                        $view_album_model = $this->getViewAlbumTable();
                        $view_album_model->type = 1;
                        foreach ($order_goods['list'] as $m) {
                            $view_album_model->fromId = $m->goods_id;
                            $album = $view_album_model->getDetails();
                            $item = array(
                                'id' => $m->id,
//                                'goodsId' => $m->goods_id,
                                'goodsId' => $m->g_uuid,
                                'name' => $m->goods_name,
                                'cash' => $m->price,
                                'number' => $m->number,
                                'imagePath' => $album && $album->path && $album->filename ? $album->path . $album->filename : '',
                                'attrDesc' => $m->attr_desc,
                            );
                            $order_goods_number += $m->number;
                            $order_goodses[] = $item;
                        }
                    }
                    $merchant = array(
                        'id' => $val->m_uuid,
                        'type' => $val->merchant_type,
                        'name' => $val->merchant_name,
                        'orderGoodses' => $order_goodses,
                        'telephone' => $val->m_telephone ? $val->m_telephone : $val->m_mobile,
                    );
                    $item = array(
                        'id' => $val->uuid,
                        'type' => $val->type,
                        'totalCash' => round($val->total_cash - $val->preferential_amount,2),
                        'orderSn' => $val->uuid,
                        'number' => $order_goods_number,
                        'expressName' => $val->express_name,
                        'expressCode' => $val->express_code,
                        'shippingSn' => $val->shipping_sn,
                        'status' => $val->status,
                        'serviceStatus' => $val->service_status,
                        'merchant' => $merchant,
                        'timestamp' => $val->timestamp,
                        'consignee' => $val->type == 1 ? $val->name : $val->user_name
                    );

                    //查看是否是拼团订单
                    $ViewUserGroupBuyingTable->userId = $this->getUserId();
                    $ViewUserGroupBuyingTable->orderId = $val->id;
                    $UserGroupBuyingDetails = $ViewUserGroupBuyingTable->getOneByOrderId();
                    if($UserGroupBuyingDetails)
                    {
                        if($UserGroupBuyingDetails->parent_id == 0)//自己就是团主
                        {
                            $groupBuying = [
                                'surplusPerson' => $UserGroupBuyingDetails->group_number - $UserGroupBuyingDetails->member_number,
                                'attrIds' => $UserGroupBuyingDetails->attr_ids,
                                'groupBuyingId' => $UserGroupBuyingDetails->id,
                            ];
                        }
                        else
                        {
                            $ViewUserGroupBuyingTable->id = $UserGroupBuyingDetails->parent_id;
                            $ViewUserGroupBuyingTable->groupBuyingGoodsId = $UserGroupBuyingDetails->group_buying_goods_id;
                            $parent_details = $ViewUserGroupBuyingTable->getParentDetails();
                            $groupBuying = [
                                'surplusPerson' => $parent_details ? ($UserGroupBuyingDetails->group_number - $parent_details->member_number) : $UserGroupBuyingDetails->group_number,
                                'attrIds' => $parent_details->attr_ids,
                                'groupBuyingId' => $parent_details->id,
                            ];
                        }
                    }
                    $item['groupBuying'] = $groupBuying;

                    $list[] = $item;
                }
                $total = $data['total'];
            }
        }

        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->orders = $list;
        return $response;
    }
}