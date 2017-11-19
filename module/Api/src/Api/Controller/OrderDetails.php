<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;

/**
 * 业务，订单详情
 */
class OrderDetails extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();

        $action = $request->action;//a:1商品；2服务；
        $id = $request->id;//订单编号UUID
        if(!in_array($action,array(1,2)) || !$id)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $this->tableObj = $this->getViewOrderTable();
        $this->tableObj->uuid = $id;
        $details = $this->tableObj->getDetails();
        if(!$details || $details->delete == '1')
        {
            return STATUS_NODATA;
        }
        //查询订单是否支付过
        /*$financial_model = $this->getFinancialTable();
        $financial_model->userId = $details->user_id;
        $financial_model->userType = 1;
        $financial_model->transferWay = $details->type + 1;
        $is_pay = $financial_model->getDetailsByOrderIdAndUserId();*/

        $order = array(
            'id' => $details->uuid,
            'orderSn' => $details->uuid,
            'totalCash' => round($details->total_cash - $details->preferential_amount,2),
            'couponCode' => $details->coupon_code,
            'status' => $details->status,
            'serviceStatus' => $details->service_status,
            'memberDiscountCash' => $details->member_discount_cash,
            'couponCash' => $details->coupon_cash,
            'shoppingCardCash' => $details->shopping_card_cash,
            'expressCash' => $details->distribution_expenses_cash,
            'preferentialAmount' => $details->preferential_amount,
            'paymentTime' => $details->payment_time,
            'deliveryTime' => $details->delivery_time,
            'receiveTime' => $details->receive_time,
            'evaluateTime' => $details->evaluate_time,
            'dissipateTime' => $details->dissipate_time,
            'expressName' => $details->express_name,
            'expressCode' => $details->express_code,
            'shippingSn' => $details->shipping_sn,
            'timestamp' => $details->timestamp,
            'deliveryMode' => $details->delivery_mode,
            'expectedDeliveryTime' => $details->expected_delivery_time,
            'expectDeliveryTime' => $details->expect_delivery_time,
            'userMessage' => $details->user_message,
            'presaleTime' => $details->presale_time,//预购
//            'isPay' => $is_pay ? 1 : 2,
        );

        if($details->room_id)
        {
            $order['roomBook'] = [
                'presaleTime' => $details->presale_time,//订房订票
                'roomNumber' => $details->room_number,
                'bookName' => $details->book_name,
                'bookMobile' => $details->book_mobile,
            ];
        }

        //退款流程
        $refund = '';
        if($details->type == 1 && $details->status == 6)
        {
            $refund = ['timestamp'=>$details->cancel_time,'confirmationTime'=>$details->refund_time,'refundTime'=>$details->refund_time];
        }
        if($details->type == 2 && in_array($details->service_status,[1,2]))
        {
            $refund = ['timestamp'=>$details->cancel_time,'confirmationTime'=>$details->refund_time,'refundTime'=>$details->refund_time];
        }
        $order['refund'] = $refund;

//        if(in_array($details->status,[6,7]))//如果是待拼团订单，查询剩余人数surplusPerson
//        {
            $groupBuying = '';
            $ViewUserGroupBuyingTable = $this->getViewUserGroupBuyingTable();
            $ViewUserGroupBuyingTable->userId = $details->user_id;
            $ViewUserGroupBuyingTable->orderId = $details->id;
            $GroupBuyingDetails = $ViewUserGroupBuyingTable->getOneByOrderId();
            if($GroupBuyingDetails)
            {
                if($GroupBuyingDetails->parent_id == 0)//自己就是团主
                {
                    $groupBuying = [
                        'surplusPerson' => $GroupBuyingDetails->group_number - $GroupBuyingDetails->member_number,
                        'attrIds' => $GroupBuyingDetails->attr_ids,
                        'groupBuyingId' => $GroupBuyingDetails->id,
                        'successTime' => $GroupBuyingDetails->success_time,
                    ];
                }
                else
                {
                    $ViewUserGroupBuyingTable->id = $GroupBuyingDetails->parent_id;
                    $ViewUserGroupBuyingTable->groupBuyingGoodsId = $GroupBuyingDetails->group_buying_goods_id;
                    $parent_details = $ViewUserGroupBuyingTable->getParentDetails();
//                    $order['surplusPerson'] = $parent_details ? ($GroupBuyingDetails->group_number - $parent_details->member_number) : $GroupBuyingDetails->group_number;
                    $groupBuying = [
                        'surplusPerson' => $parent_details ? ($GroupBuyingDetails->group_number - $parent_details->member_number) : $GroupBuyingDetails->group_number,
                        'attrIds' => $parent_details->attr_ids,
                        'groupBuyingId' => $parent_details->id,
                        'successTime' => $parent_details->success_time,
                    ];
                }
            }
            else
            {

//                $order['surplusPerson'] = 0;
            }
            $order['groupBuying'] = $groupBuying;
//        }
        if($action == 2 && $details->service_type_attribute_json)
        {
            $service_type_attribute = json_decode($details->service_type_attribute_json,true);
            $order['informations'] = $service_type_attribute ? $service_type_attribute : array();
        }
        if($action == 1)
        {
            $address = array(
                'name' => $details->name,
                'mobile' => $details->mobile,
                'street' => $details->address,
            );
            $order['address'] = $address;
        }
        $view_order_goods_model = $this->getViewOrderGoodsTable();
        $view_order_goods_model->orderId = $details->id;
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
                    'goodsId' => $m->g_uuid,
                    'name' => $m->goods_name,
                    'cash' => $m->price,
                    'number' => $m->number,
                    'imagePath' => $album && $album->path && $album->filename ? $album->path . $album->filename : '',
                    'commentStatus' => $m->comment_status,
                    'commentId' => $m->comment_id,
                    'customerServiceStatus' => $m->customer_service_id ? 2 : 1,
                    'customerServiceId' => $m->customer_service_id,
                    'attrDesc' => $m->attr_desc,
                );
                $order_goodses[] = $item;
            }
        }
        $merchant = array(
            'id' => $details->m_uuid,
            'type' => $details->merchant_type,
            'name' => $details->merchant_name,
            'telephone' => $details->m_telephone ? $details->m_telephone : $details->m_mobile,
            'orderGoodses' => $order_goodses
        );
        $order['merchant'] = $merchant;
        $response->status = STATUS_SUCCESS;
        $response->order = $order;
        return $response; // 返回结果
    }
}
