<?php
namespace Api\Controller;
use Api\Controller\Request\DiscountCalculationRequest;


/**
 * 计算购物卡和优惠券的优惠金额
 */
class DiscountCalculation extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new DiscountCalculationRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();

        $merchant = $request->merchant;
        if(!$merchant->id || !is_array($merchant->goodses) || !$merchant->goodses)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if(!$merchant->couponId && (!is_array($merchant->shoppingCardIds) || !$merchant->shoppingCardIds))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $user_id = $this->getUserId();
        $view_goods_table = $this->getViewGoodsTable();
        $result = $view_goods_table->discountCalculation($merchant,$user_id);
        if($result['s'])
        {
            $response->status = $result['s'];
            $response->description = $result['d'];
            return $response;
        }

        $response->status = STATUS_SUCCESS;
        $response->couponDiscount = $result['couponDiscount'];
        $response->shoppingCardDiscount = $result['shoppingCardDiscount'];
        $response->memberDiscount = $result['memberDiscount'];
    }
}