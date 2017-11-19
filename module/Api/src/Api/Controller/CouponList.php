<?php
namespace Api\Controller;
use Api\Controller\Request\CouponListWhereRequest;

/**
 * 优惠券列表
 * @author Administrator
 */
class CouponList extends CommonController
{

    public function __construct()
    {
        $this->myWhereRequest = new CouponListWhereRequest();
        parent::__construct();
    }
    /**
     * 返回一个数组或者Result类
     * @return \Api\Controller\BaseResult
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $table_where = $this->getTableWhere();
        $this->checkLogin();
        $action = $request->action ? $request->action : 1;
        if(!in_array($action,[1,2]))//a：1个人中心 2订单提交可使用的优惠券列表V2.0
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $merchant_id = $table_where->merchantId;//提交订单时的商家uuid
        $status = $table_where->status;//1未使用,2已使用,3已过期
        $goodsIds = $table_where->goodsIds;//提交订单时的产品uuid数组
        if(($action == 1 && !in_array($status,array(1,2,3))) || ($action == 2 && (!is_array($goodsIds) || !$goodsIds)))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $this->tableObj = $this->getViewCouponUserTable();
        $this->initModel();
        $user_id = $this->getUserId();
        $this->tableObj->userId = $user_id;
        if($merchant_id)
        {
            $this->tableObj->couponMUuid = $merchant_id;
        }
        if($goodsIds)
        {
            $this->tableObj->goodsUuidIds = $goodsIds;
        }
        $data = $this->tableObj->getApiList($action,$status);
        $list = array();
        $total = 0;
        if($data['list'])
        {
            foreach ($data['list'] as $val) {
                $item = array(
                    'id' => $val->id,
                    'name' => $val->coupon_name,
                    'code' => $val->code,
                    'type' => $val->type,
                    'full' => $val->type == 1 ? $val->full_money : $val->full_number,//优惠券类型1满X元减Y元，2满X件减Y折
                    'discount' => $val->type == 1 ? $val->discount_money : $val->discount_percent,
                    'couponId' => $val->coupon_id,
                    'startTime' => $val->start_time,
                    'endTime' => $val->end_time,
//                    'goodsRange' => $val->goods_ids ? 2 : 1,
//                    'serviceRange' => $val->service_ids ? 2 : 1,
                );
                /*$goods_ids = array();
                if($val->goods_ids)
                {
                    $goods_ids = array_merge($goods_ids,explode('|',$val->goods_ids));
                }
                if($val->service_ids)
                {
                    $goods_ids = array_merge($goods_ids,explode('|',$val->service_ids));
                }
                $item['goodsIds'] = $goods_ids;*/
                if($action == 2)
                {
                    $item['availableType'] =  $val->available_type;
                    $item['availableJson'] = $val->available_json;
                }
                if($status == 1)
                {
                    if(strtotime($val->start_time) > time())
                    {
                        $timestamp = strtotime($val->end_time) - strtotime($val->start_time);
                    }
                    else
                    {
                        $timestamp = strtotime($val->end_time) - time();
                    }
                    $valid_time = '';
                    if($timestamp > 3600*24)
                    {
                        $valid_time = round($timestamp/(3600*24))."天";
                    }
                    elseif($timestamp > 60)
                    {
                        $valid_time = round($timestamp/60)."分钟";
                    }
                    else
                    {
                        $valid_time = $timestamp."秒";
                    }
                    $item['validTime'] = $valid_time;
                }
                $list[] = $item;
            }

            $total = $data['total'];
        }
        $response->coupons = $list;
        $response->total = $total;
        return $response;
    }
}