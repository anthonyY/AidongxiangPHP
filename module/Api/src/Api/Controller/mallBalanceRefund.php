<?php
namespace Api\Controller;

/**
 * 	商城退款
 *
 */
class mallBalanceRefund extends mallBase
{

    public $method = 'mallBalanceRefund';
    /**
     * 商城订单号
     * @var
     */
    public $mallOrderNo;

    /**
     * 商城退款金额。以分为单位的整型
     * @var
     */
    public $refundAmount;

    /**
     * 退单的商品ID
     * @var
     */
    public $commodityId;

    /**
     * @var 该笔订单的总金额。以分为单位的整型
     */
    public $orderAmount;

    /**
     * 充值后余额(已分为单位， 10.0元返回1000)
     * @var
     */
    public $balance;

    public $request = ['mallOrderNo','refundAmount','commodityId','orderAmount'];

    public $return = ['balance'];

    /**
     * java->php
     */
    public function index()
    {
        $this->respCode = 0;
        return $this->mallReturn();
    }

    /**
     * php->java
     * @param $id 售后表ID 默认是0，有值表示后台传过来要进行售后退款的
     */
    public function submit($id = 0)
    {
        if($id)
        {
            $view_customer_service_model = $this->getViewCustomerServiceApplyTable();
            $view_customer_service_model->id = $id;
            $service_info = $view_customer_service_model->getDetails();
            if($service_info)
            {
                $this->userId = $service_info->u_user_id;
                $this->mobileNo = $service_info->mobile;
                $this->mallOrderNo = $service_info->uuid;
                $this->refundAmount = $service_info->cash;
                $this->commodityId = null;
                $this->orderAmount = $service_info->total_cash;
            }
            else
            {
                $this->respCode = 100;
            }
        }
        foreach($this->request as $v)
        {
            if(!$this->$v && $v != 'commodityId')
            {
                $this->respCode = 100;
            }
        }

        $this->mallRequest();
        return $this;
    }

}