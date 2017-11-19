<?php
namespace Api\Controller;

use Api\Controller\Request\OrderSubmitRequest;
use Platform\Model\NotificationRecordsGateway;

/**
 * 业务，订单提交
 */
class OrderSubmit extends CommonController
{

    protected $action;
    protected $type;
    protected $contacts_id;
    protected $payType;
    protected $openId;
    protected $carts;
    protected $userGroupBuyingId;//拼团id V2.0 a=3
    protected $roomBook;//订房对象


    public function __construct()
    {
        $this->myRequest = new OrderSubmitRequest();
        parent::__construct();
    }

    public function index()
    {

        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();

        $this->action = $request->action;//订单类型:1商品订单 2服务订单 3拼团商品订单 4拼团服务
        $this->type = $request->type;//提交类型：1购物车提交 2立即购买提交
        $this->contacts_id = $request->contactsId;//收货地址a=1
        //$this->payType = $request->payType;//支付方式: 1微信(H5专用） 2支付宝 3余额支付
       // $this->openId = $request->openId;//微信openId:payType = 1 时用
        $this->carts = $request->carts;//购物车数组 type=1 传
        $this->roomBook = $request->roomBook;//订房

        //必填参数验证
        if(!in_array($this->action,array(1,2,3,4)) || !in_array($this->type, array(1,2,3)))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }

        if(in_array($this->action,array(1,3))  && !$request->contactsId)
        {//商品订单，并填收货地址
            $response->description = '收货地址不能为空';
            $response->status = 10000;
            return $response;
        }


       /* if($this->payType == 1 && !$this->openId)
        {
            $response->description = 'openId不能为空';
            $response->status = 10000;
            return $response;
        }*/

        if(!is_array($this->carts) || !$this->carts)
        {
            $response->description = '未提交商品';
            $response->status = 10000;
            return $response;
        }

        /* $user_model = $this->getUserTable();
         $user_model->id = $this->getUserId();
         $user_details = $user_model->getDetails();
         if($this->payType == 3 && !$user_details->pay_password)
         {
             $response->description = '请先设置支付密码!';
             $response->status = 10000;
             return $response;
         }
         if($this->payType == 3 && $user_details->pay_password !== strtoupper(md5(strtoupper($request->password))))
         {
             $response->description = '支付密码不正确!';
             $response->status = 10000;
             return $response;
         }*/

        $order_table = $this->getOrderTable();
        $order_table->type = $this->action;
        $order_table->userId = $this->getUserId();
        //$order_table->paymentMethod = $this->payType;
        $info = $order_table->generateOrder($this->myRequest);
        if($info['s'])
        {
            $response->description = $info['d'];
            $response->status = 10000;
            return $response;
        }

       /* if($this->payType == 3)
        {
            return STATUS_SUCCESS;
        }
        if($this->payType ==2)
        {*/
        $response->cash = $info['m'];
        $response->transferNo = $info['i'];
        $response->groupId = $info['groupId'];//拼团订单的团主ID
        return $response;
        /*}*/
//       return $this->setPay($this->payType,$info['i'],$info['m']);
    }

    /**
     * 设置返回的支付参数
     * @param integer $payType 支付方式
     * @param integer $transfer_no 财务流水号
     * @param integer $cash 现金
     * @return \Api\Controller\Common\Response
     * @version 2015年10月13日
     * @author liujun
     */
    public function setPay($payType, $transfer_no, $cash)
    {
        $response = $this->getAiiResponse();
        if($payType == 1){//微信H5支付
            $_SESSION['open_id'] = $this->openId;
            $pay_table = $this->getPayLogTable();
            $pay_table->id = $transfer_no;
            $pay_info = $pay_table->getDetails();
            $wx_data = $this->getWxPayInfo($payType, $pay_info->out_trade_no, $cash, 1);
            $response->wxPay = $wx_data;
        }/*elseif($payType == 2){//支付宝支付
            $response->alipay = $this->getAlipayQueryData($action, $transfer_no, $cash);
            $response->transferNo = $type . $transfer_no;
            $response->cash = $cash;
        }*/
        //$response->id = $this->order_id;

        return $response;
    }
}