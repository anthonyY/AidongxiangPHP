<?php
namespace Api\Controller;

use Api\Controller\Request\ShoppingCardSubmitRequest;

/**
 * 领取优惠券
 */
class CouponSubmit extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $user_id = $this->getUserId();
        $user_model = $this->getUserTable();
        $user_model->id = $user_id;
        $user_details = $user_model->getDetails();
        $id = $request->id;//优惠券ID
        if(!$id)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if(!$user_details || $user_details->status != 1)
        {
            $response->status = STATUS_USER_NOT_EXIST;
            $response->description = '用户不存在或已被停用!';
            return $response;
        }
        $view_coupon_model = $this->getViewCouponTable();
        $view_coupon_model->id = $id;
        $coupon_details = $view_coupon_model->getDetails();
        if(!$coupon_details || $coupon_details->delete == 1)
        {
            $response->status = STATUS_NODATA;
            $response->description = '优惠券不存在!';
            return $response;
        }
        if($coupon_details->end_time < date('Y-m-d H:i:s'))
        {
            $response->status = 10000;
            $response->description = '优惠券已过期!';
            return $response;
        }
        if($coupon_details->stock <= 0)
        {
            $response->status = STATUS_NODATA;
            $response->description = '优惠券已领光!';
            return $response;
        }
        $user_coupon_model = $this->getCouponUserTable();
        $user_coupon_model->userId = $user_id;
        $user_coupon_model->merchantId =$coupon_details->merchant_id;
        $user_coupon_model->couponId = $id;
        $result = $user_coupon_model->getCoupon($coupon_details->limit);
        $response->status = $result['s'];
        $response->description = $result['d'];
        return $response;
    }
}
