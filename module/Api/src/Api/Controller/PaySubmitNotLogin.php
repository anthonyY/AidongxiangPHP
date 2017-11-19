<?php
namespace Api\Controller;

use Api\Controller\Request\PayRequest;
use Zend\Db\Sql\Where;
use Core\System\AiiPush\AiiMyFile;

/**
 * 充值/订单付款
 */
class PaySubmitNotLogin extends CommonController
{
    private $pay_log_array;

    private $user_id;

    public function __construct()
    {
        $this->myRequest = new PayRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
    
        //余额支付检查登录
        if((int)$request->payType == 3){
            $this->checkLogin();
            $password = $request->password;//余额支付需要支付密码（一次MD5加密）
        }

        // 1用户充值；2订单付款
        $action = (int)$request->action;
        $pay_type = (int)$request->payType; //支付方式: 1微信(H5) 2支付宝 3余额支付(a=2)
        if(!in_array($action, array('1', '2','3')) || !in_array($pay_type,array('1','2','3','4','5','6'))){
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        // 订单id
        $pay_log_model = $this->getPayLogTable();
        $view_financial_model = $this->getViewFinancialTable();
        $merchant_model = $this->getMerchantTable();
        $financial_model = $this->getFinancialTable();
        $order_model = $this->getOrderTable();
        
        $id = $request->id;
        
        //获得用户id
        switch($action){
            case 1: $user_id = $request->id;break;
            case 2: $user_id = $order_model->getUserIdByOrderId($id);
                if(!$user_id){
                    return STATUS_ILLEGAL_OPERATION;
                }
                break;
            case 3:
                $user_id = $view_financial_model->getUserIdPayId($id);
                if(!$user_id){
                    return STATUS_ILLEGAL_OPERATION;
                }
                break;
        }
        
        $user_model = $this->getUserTable();
        $user_model->id = $user_id;
        $user_details = $user_model->getDetails();
        if(!$user_details)
        {
            return STATUS_USER_NOT_EXIST;
        }
//        if($pay_type == 3) {
//            if (!$user_details->pay_password) {
//                return STATUS_NO_PAY_PASSWORD;
//            }
//            if ($user_details->pay_password != strtoupper(md5(strtoupper($password)))) {
//                $response->status = STATUS_ILLEGAL_OPERATION;
//                $response->description = '用户支付密码不正确！';
//                return $response;
//            }
//        }
        $cash = (float)$request->cash;
        $id = $request->id;//a=2订单uuid,a=3支付id
        $open_id = $request->openId;
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){//公众号支付
            if(($pay_type == 1 &&  !$open_id))
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            if($open_id){
                $_SESSION['open_id'] = $open_id;
            }
        }
        if(($action != 1 && !$id) || !$pay_type){
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $pay_para = [];
        $pay_url = '';
        $ali_pay = '';
        switch($action){
            case '1': // 用户充值
                if($cash <= 0)
                {
                    return STATUS_PARAMETERS_INCOMPLETE;
                }
                //新增支付记录
                $pay_log_model->paymentType = $pay_type;
                $pay_log_model->cash = $cash;
                $pay_log_model->status = 3;
                $pay_log_id = $pay_log_model->addData();
                if(!$pay_log_id)
                {
                    return STATUS_UNKNOWN;
                }
                //新增财务记录
                $financial_model->uuid = $financial_model->generateFinancialNumber();
                $financial_model->income = 1;
                $financial_model->userType = 1;
                $financial_model->cashBefore = $user_details->cash;
                $financial_model->cash = $cash;
                $financial_model->cashAfter = $user_details->cash + $cash;
                $financial_model->transferWay = 1;
                $financial_model->paymentType = $pay_type;
                $financial_model->status = 3;
                $financial_model->userId = $user_id;
                $financial_model->payId = $pay_log_id;
                $financial_model->description = "充值".$cash."元";
                $financial_id = $financial_model->addData();
                if(!$financial_id)
                {
                    return STATUS_UNKNOWN;
                }

                if($pay_log_id && $financial_id)
                {
                    if($pay_type == 1)//微信
                    {
                        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){//微信浏览器
                            $pay_para = $this->getWxPayInfo(1,$pay_log_model->outTradeNo,$cash,1,$financial_model->description);
                        }
                        else
                        {
                            $pay_H5 = $this->getWxPayInfo(1,$pay_log_model->outTradeNo,$cash,2,$financial_model->description);
                            if($pay_H5 && $pay_H5['return_code'] == 'SUCCESS')
                            {
//                                $url = $this->url()->fromRoute('wap-user', array(
//                                    'action' => 'wallet'
//                                ));
//                                $return_url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
                                $pay_url = $pay_H5['mweb_url'] . '&redirect_url=';
                            }
                            else
                            {
                                $response->status = STATUS_UNKNOWN;
                                $response->description = '支付失败';
                                return $response;
                            }
                        }
                    }
                    elseif($pay_type == 2) //支付宝
                    {

                    }
                    elseif($pay_type == 4)//4微信扫码支付（PC）V2.0
                    {
                        $pay_para = $this->getWxPayInfo(1,$pay_log_model->outTradeNo,$cash,3);
                    }
                    elseif($pay_type == 5)//5App微信支付V2.0
                    {
                        $pay_para = $this->getWxPayInfo(1,$pay_log_model->outTradeNo,$cash,4);
                    }
                    elseif($pay_type == 6)//6App支付宝支付
                    {
                        require_once 'AiiLibrary/AliPay/AliPayApi.php';
                        $alipay = new \AlipayApi();
                        $alipay->out_trade_no = $pay_log_model->outTradeNo;
                        $test_amount = '0.0' . $cash * 100;
                        $alipay->total_amount = IS_DEBUG == 1 ? $test_amount : $cash;
                        $alipay->body = '支付充值款：'.$cash;
                        $alipay->subject ='支付充值款：'.$cash;
                        $ali_pay =  $alipay->submitPay(2);
                    }
                }
                break;
            case '2': // 2用户中心订单付款
                if(!$id)//订单id
                {
                    return STATUS_PARAMETERS_INCOMPLETE;
                }
                $this->adapter->getDriver()->getConnection()->beginTransaction();
                $order_model->uuid = $id;
                $order_details = $order_model->getDetails();//订单详情
                if(!$order_details || $order_details->user_id != $user_id)
                {
                    return STATUS_NODATA;
                }
                if($order_details->status != 1)//如果不是待支付状态，直接返回
                {
                    return STATUS_ILLEGAL_OPERATION;
                }
                if($pay_type == 3 && $user_details->cash < $order_details->total_cash)
                {
                    $response->status = STATUS_UNKNOWN;
                    $response->description = "钱包余额不足，请充值";
                    return $response;
                }

                $view_financial_model->userId = $user_id;
                $view_financial_model->orderId = $order_details->id;
                $financial_details = $view_financial_model->getOneByOrderId();
                if(!$financial_details)
                {
                    return STATUS_NODATA;
                }

                $view_financial_model->id = $financial_details->id;
                $view_financial_model->payId = $financial_details->pay_id;
                $other_financial = $view_financial_model->getOtherFinancialInSamePayId();
                if($other_financial)
                {
                    //取消订单重新支付导致了拆单支付（如果是多订单）,所以要重新生成支付记录，更新对应的财务记录的支付记录表ID
                    //重新新增支付记录
                    $pay_log_model->paymentType = $pay_type;
                    $pay_log_model->cash = $financial_details->cash;
                    $pay_log_model->status = 3;
                    $pay_log_id = $pay_log_model->addData();
                    if($pay_log_id)
                    {
                        $financial_model->payId = $pay_log_id;//更新财务表的支付id
                        $financial_model->id = $financial_details->id;
                        $financial_model->updateData();
                    }
                    else
                    {
                        $response->status = STATUS_UNKNOWN;
                        $response->status = '支付失败';
                        return $response;
                    }
                }
                else
                {
                    $pay_log_id = $financial_details->pay_id;
                }


                $pay_log_model->id = $pay_log_id;
                $pay_details = $pay_log_model->getDetails();
                if(!$pay_details)
                {
                    return STATUS_NODATA;
                }

                //如果是钱包支付，修改用户钱包(这里应该是微信支付宝回调更新的，还要更新积分,升级等级,更新商家钱包)
                if($pay_type == 3)
                {
                    //如果该订单不用钱，直接成功，调用回调方法
                    if($pay_details->cash != 0)
                    {
                        if(!$user_details->pay_password)
                        {
                            $response->status = STATUS_ILLEGAL_OPERATION;
                            $response->description = '请先设置支付密码！';
                            return $response;
                        }
                        if($user_details->pay_password !== strtoupper(md5(strtoupper($password))))
                        {
                            $response->status = STATUS_ILLEGAL_OPERATION;
                            $response->description = '用户支付密码不正确！';
                            return $response;
                        }

//                    if($user_details->cash < $order_details->total_cash)
//                    {
//                        $response->status = STATUS_CASH_NOT_ENOUGH_TWO;
//                        $response->description = '用户钱包余额不足，请先充值！';
//                        return $response;
//                    }

                        $mallPaymentByBalance = new mallPaymentByBalance();
                        $mallPaymentByBalance->mobileNo = $user_details->mobile;
                        $mallPaymentByBalance->userId = $user_details->user_id;
                        $mallPaymentByBalance->mallOrderNo = $order_details->uuid;
                        $mallPaymentByBalance->password = $user_details->pay_password;
                        $mallPaymentByBalance->payAmount = $order_details->total_cash;
                        $java_pay_result = $mallPaymentByBalance->submit();
                        $respond = $mallPaymentByBalance->getRespCode();
                        if($respond && $respond['respCode'] != 0)
                        {
                            $response->status = STATUS_UNKNOWN;
                            $response->description = isset($respond['respMsg']) ? $respond['respMsg'] : '支付失败';
                            return $response;
                        }
                        $user_model->cash = $java_pay_result->balance;
                        $result_user_update = $user_model->updateData();//更新用户钱包
                        if(!$result_user_update)
                        {
                            $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                            $response->status = STATUS_UNKNOWN;
                            $response->description = '更新用户钱包失败';
                            return $response;
                        }

                        $pay_log_model->seqNo = $java_pay_result->seqNo;
                    }

                    //更新订单状态，财务状态，支付记录状态，商家余额
                    /*$order_model->status = 2;
                    $order_model->id = $order_details->id;
                    $order_model->paymentMethod = $pay_type;
                    $order_model->paymentTime = $this->getTime();//支付时间
                    $result_order_update = $order_model->updateData();//更新订单状态为待发货
                    if(!$result_order_update)
                    {
                        $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                        $response->status = STATUS_UNKNOWN;
                        $response->description = '更新订单状态失败';
                        return $response;
                    }*/
                    $financial_model->id = $financial_details->id;
                    $financial_model->status = 1;//成功
                    $financial_model->paymentType = $pay_type;//更新支付方式
                    $result_financial_update = $financial_model->updateData();//更新财务状态
                    if(!$result_financial_update)
                    {
                        $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                        $response->status = STATUS_UNKNOWN;
                        $response->description = '更新财务状态失败';
                        return $response;
                    }
                    $pay_log_model->status = 1;//成功
                    $pay_log_model->id = $pay_log_id;
                    $result_pay_log_update = $pay_log_model->updateData();
                    if(!$result_pay_log_update)
                    {
                        $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                        $response->status = STATUS_UNKNOWN;
                        $response->description = '更新支付记录失败';
                        return $response;
                    }
                    /*$merchant_model->id = $order_details->merchant_id;
                    $merchant_details = $merchant_model->getDetails();
                    $merchant_model->cash = $merchant_details->cash + $financial_details->cash - $financial_details->commission;
                    $result_merchant_update = $merchant_model->updateApiMerchant();
                    if(!$result_merchant_update)
                    {
                        $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                        $response->status = STATUS_UNKNOWN;
                        $response->description = '更新商家钱包失败';
                        return $response;
                    }*/
                    /**
                     * 处理下单后的关联业务，
                     * 1增加商家与用户的消费关系
                     * 2增加商品的销量
                     */
                    $consumptionRelationModel = $this->getConsumptionRelationTable();
                    $consumptionRelationModel->userId = $order_details->user_id;
                    $consumptionRelationModel->merchantId = $order_details->merchant_id;
                    $exist = $consumptionRelationModel->getOneByUserIdMerchantId();
                    if(!$exist)
                    {
                        $consumptionRelationModel->cash = $order_details->total_cash + $order_details->shopping_card_cash;
                        $consum_update = $consumptionRelationModel->addData();
                    }
                    else
                    {
                        $consumptionRelationModel->id = $exist->id;
                        $consumptionRelationModel->cash = $exist->cash + $order_details->total_cash + $order_details->shopping_card_cash;
                        $consum_update = $consumptionRelationModel->updateData();
                    }
                    if(!$consum_update)
                    {
                        $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                        $response->status = STATUS_UNKNOWN;
                        $response->description = '更新用户与商家消费关系失败';
                        return $response;
                    }
                    $view_order_goods_model = $this->getViewOrderGoodsTable();
                    $view_order_goods_model->orderId = $id;
                    $goods_list = $view_order_goods_model->getList();
                    if($goods_list['list'])
                    {
                        $goods_model = $this->getGoodsTable();
                        foreach ($goods_list['list'] as $val) {
                            $goods_update = $goods_model->updateKey($val->goods_id, 1, 'sales_volume', $val->number);
                            if(!$goods_update)
                            {
                                $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                                $response->status = STATUS_UNKNOWN;
                                $response->description = '新增产品销量失败';
                                return $response;
                            }
                        }
                    }

                    $ViewUserGroupBuyingTable = $this->getViewUserGroupBuyingTable();
                    $ViewUserGroupBuyingTable->orderId = $order_details->id;
                    $ViewUserGroupBuyingTable->userId = $order_details->user_id;
                    $UserGroupBuyingDetails = $ViewUserGroupBuyingTable->getOneByOrderId();//查询是否是拼团订单
                    if($UserGroupBuyingDetails)
                    {
                        /**
                         * 每支付一单请求一次，如果是拼团订单，查看是否成团，是，待发货，否，待拼团
                         * 检查拼团订单是否需要修改成待发货
                         * 不包括正在支付的订单
                         */
                        /*$ViewUserGroupBuyingTable = $this->getViewUserGroupBuyingTable();
                        $ViewUserGroupBuyingTable->userId = $order_details->user_id;
                        $ViewUserGroupBuyingTable->orderId = $order_details->id;*/
                        $checkUserGroupBuyingOrderDelivery = $ViewUserGroupBuyingTable->checkUserGroupBuyingOrderDelivery($pay_type);
                        if($checkUserGroupBuyingOrderDelivery['s'] == 20000)
                        {
                            $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                            $response->status = $checkUserGroupBuyingOrderDelivery['s'];
                            $response->description = $checkUserGroupBuyingOrderDelivery['d'];
                            return $response;
                            break;
                        }
                    }
                    else
                    {
                        //更新订单状态
                        $order_model->id = $order_details->id;
                        $order_model->paymentMethod = $pay_type;
                        $order_model->paymentTime = $this->getTime();//支付时间
                        $order_model->status = 2;
                        $result_order_update = $order_model->updateData();//更新订单状态为待发货
                        if(!$result_order_update)
                        {
                            $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                            $response->status = STATUS_UNKNOWN;
                            $response->description = '更新订单状态失败';
                            return $response;
                            break;
                        }
                    }
                }
                else
                {
                    if($pay_type == 1)//微信
                    {
                        $order_type = $order_details->type + 1;
                        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){//公众号支付
                            $pay_para = $this->getWxPayInfo($order_type,$pay_details->out_trade_no,$order_details->total_cash,1);
                        }
                        else
                        {
                            $pay_H5 = $this->getWxPayInfo($order_type,$pay_details->out_trade_no,$order_details->total_cash,2);
                            if($pay_H5 && $pay_H5['return_code'] == 'SUCCESS')
                            {
//                                $url = $this->url()->fromRoute('wap-order', array(
//                                    'action' => 'successfulPayment'
//                                ));
//                                $return_url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
                                $pay_url = $pay_H5['mweb_url'] . '&redirect_url=';
                            }
                            else
                            {
                                $response->status = STATUS_UNKNOWN;
                                $response->description = '支付失败';
                                return $response;
                            }
                        }

                    }
                    elseif($pay_type == 2) //支付宝
                    {

                    }
                    elseif($pay_type == 4)//4微信扫码支付（PC）V2.0
                    {
                        $order_type = $financial_details->transfer_way;
                        $pay_para = $this->getWxPayInfo($order_type,$pay_details->out_trade_no,$pay_details->cash,3);
                    }
                    elseif($pay_type == 5)//5App微信支付V2.0
                    {
                        $order_type = $financial_details->transfer_way;
                        $pay_para = $this->getWxPayInfo($order_type,$pay_details->out_trade_no,$pay_details->cash,4);
                    }
                    elseif($pay_type == 6)//6App支付宝支付
                    {
                        require_once 'AiiLibrary/AliPay/AliPayApi.php';
                        $alipay = new \AlipayApi();
                        $alipay->out_trade_no = $pay_details->out_trade_no;
                        $test_amount = '0.0' . $pay_details->cash * 100;
                        $alipay->total_amount = IS_DEBUG == 1 ? $test_amount : $pay_details->cash;
                        $alipay->body = '支付订单款：'.$pay_details->cash;
                        $alipay->subject ='支付订单款：'.$pay_details->cash;
                        $ali_pay = $alipay->submitPay(2);
                    }
                }

                $this->adapter->getDriver()->getConnection()->commit();
                break;
            case 3://3非个人中心订单支付
                $pay_log_id = $id;
                if(!$id)//支付ID
                {
                    return STATUS_PARAMETERS_INCOMPLETE;
                }
                $this->adapter->getDriver()->getConnection()->beginTransaction();
                $pay_log_model->id = $id;
                $pay_details = $pay_log_model->getDetails();//支付记录详情
                if(!$pay_details)
                {
                    return STATUS_NODATA;
                }
                if($pay_details->status != 3)//如果不是待支付状态，直接返回
                {
                    return STATUS_ILLEGAL_OPERATION;
                }
                if($pay_type == 3 && $user_details->cash < $pay_details->cash)
                {
                    $response->status = STATUS_UNKNOWN;
                    $response->description = "钱包余额不足，请充值";
                    return $response;
                }

                $view_financial_model->userId = $user_id;
                $view_financial_model->payId = $id;
                $financial_list = $view_financial_model->getFinancialListByPayId();
                if(!$financial_list['list'])
                {
                    return STATUS_NODATA;
                }
                $financial_details = $financial_list['list'][0];
                if($pay_type == 3)
                {
                    //如果该订单不用钱，直接成功，调用回调方法
                    if($pay_details->cash != 0)
                    {
                        if(!$user_details->pay_password)
                        {
                            $response->status = STATUS_ILLEGAL_OPERATION;
                            $response->description = '请先设置支付密码！';
                            return $response;
                        }
                        if($user_details->pay_password !== strtoupper(md5(strtoupper($password))))
                        {
                            $response->status = STATUS_ILLEGAL_OPERATION;
                            $response->description = '用户支付密码不正确！';
                            return $response;
                        }

                        $mallPaymentByBalance = new mallPaymentByBalance();
                        $mallPaymentByBalance->mobileNo = $user_details->mobile;
                        $mallPaymentByBalance->userId = $user_details->user_id;
                        $mallPaymentByBalance->mallOrderNo = $pay_details->out_trade_no;
                        $mallPaymentByBalance->password = $user_details->pay_password;
                        $mallPaymentByBalance->payAmount = $pay_details->cash;
                        $java_pay_result = $mallPaymentByBalance->submit();
                        $respond = $mallPaymentByBalance->getRespCode();
                        if($respond && $respond['respCode'] != 0)
                        {
                            $response->status = STATUS_UNKNOWN;
                            $response->description = isset($respond['respMsg']) ? $respond['respMsg'] : '支付失败';
                            return $response;
                        }

                        $user_model->cash = $java_pay_result->balance;
                        $result_user_update = $user_model->updateData();//更新用户钱包
                        if(!$result_user_update)
                        {
                            $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                            $response->status = STATUS_UNKNOWN;
                            $response->description = '更新用户钱包失败';
                            return $response;
                        }

                        $pay_log_model->seqNo = $java_pay_result->seqNo;
                    }

                    $pay_log_model->status = 1;//成功
                    $pay_log_model->id = $pay_log_id;
                    $result_pay_log_update = $pay_log_model->updateData();
                    if(!$result_pay_log_update)
                    {
                        $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                        $response->status = STATUS_UNKNOWN;
                        $response->description = '更新支付记录失败';
                        return $response;
                        break;
                    }

                    foreach ($financial_list['list'] as $item) {
                        $order_model->id = $item->order_id;
                        $order_details = $order_model->getDetails();

                        //更新订单状态，财务状态，支付记录状态，商家余额
                        $financial_model->id = $item->id;
                        $financial_model->status = 1;//成功
                        $financial_model->paymentType = $pay_type;//更新支付方式
                        $result_financial_update = $financial_model->updateData();//更新财务状态
                        if(!$result_financial_update)
                        {
                            $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                            $response->status = STATUS_UNKNOWN;
                            $response->description = '更新财务状态失败';
                            return $response;
                            break;
                        }
                        if(in_array($item->transfer_way,array(2,3)) && $item->user_type == 2) {//是订单,就要增加商家对应的金额
                            /*$merchant_model->id = $order_details->merchant_id;
                            $merchant_details = $merchant_model->getDetails();
                            $merchant_model->cash = $merchant_details->cash + $item->cash - $item->commission;
                            $result_merchant_update = $merchant_model->updateApiMerchant();
                            if (!$result_merchant_update) {
                                $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                                $response->status = STATUS_UNKNOWN;
                                $response->description = '更新商家钱包失败';
                                return $response;
                                break;
                            }*/
                            /**
                             * 处理下单后的关联业务，
                             * 1增加商家与用户的消费关系
                             * 2增加商品的销量
                             */
                            $consumptionRelationModel = $this->getConsumptionRelationTable();
                            $consumptionRelationModel->userId = $order_details->user_id;
                            $consumptionRelationModel->merchantId = $order_details->merchant_id;
                            $exist = $consumptionRelationModel->getOneByUserIdMerchantId();
                            if (!$exist) {
                                $consumptionRelationModel->cash = $order_details->total_cash + $order_details->shopping_card_cash;
                                $consum_update = $consumptionRelationModel->addData();
                            } else {
                                $consumptionRelationModel->id = $exist->id;
                                $consumptionRelationModel->cash = $exist->cash + $order_details->total_cash + $order_details->shopping_card_cash;
                                $consum_update = $consumptionRelationModel->updateData();
                            }
                            if (!$consum_update) {
                                $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                                $response->status = STATUS_UNKNOWN;
                                $response->description = '更新用户与商家消费关系失败';
                                return $response;
                                break;
                            }
                            $view_order_goods_model = $this->getViewOrderGoodsTable();
                            $view_order_goods_model->orderId = $item->order_id;
                            $goods_list = $view_order_goods_model->getList();
                            if ($goods_list['list']) {
                                $goods_model = $this->getGoodsTable();
                                foreach ($goods_list['list'] as $val) {
                                    $goods_update = $goods_model->updateKey($val->goods_id, 1, 'sales_volume', $val->number);
                                    if (!$goods_update) {
                                        $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                                        $response->status = STATUS_UNKNOWN;
                                        $response->description = '新增产品销量失败';
                                        return $response;
                                        break;
                                    }
                                }
                            }

                            $ViewUserGroupBuyingTable = $this->getViewUserGroupBuyingTable();
                            $ViewUserGroupBuyingTable->orderId = $order_details->id;
                            $ViewUserGroupBuyingTable->userId = $order_details->user_id;
                            $UserGroupBuyingDetails = $ViewUserGroupBuyingTable->getOneByOrderId();//查询是否是拼团订单
                            if($UserGroupBuyingDetails)
                            {
                                /**
                                 * 每支付一单请求一次，如果是拼团订单，查看是否成团，是，待发货，否，待拼团
                                 * 检查拼团订单是否需要修改成待发货
                                 * 不包括正在支付的订单
                                 */
                                /*$ViewUserGroupBuyingTable = $this->getViewUserGroupBuyingTable();
                                $ViewUserGroupBuyingTable->userId = $order_details->user_id;
                                $ViewUserGroupBuyingTable->orderId = $order_details->id;*/
                                $checkUserGroupBuyingOrderDelivery = $ViewUserGroupBuyingTable->checkUserGroupBuyingOrderDelivery($pay_type);
                                if($checkUserGroupBuyingOrderDelivery['s'] == 20000)
                                {
                                    $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                                    $response->status = $checkUserGroupBuyingOrderDelivery['s'];
                                    $response->description = $checkUserGroupBuyingOrderDelivery['d'];
                                    return $response;
                                    break;
                                }
                            }
                            else
                            {
                                //更新订单状态
                                $order_model->status = 2;
                                $order_model->id = $order_details->id;
                                $order_model->paymentMethod = $pay_type;
                                $order_model->paymentTime = $this->getTime();//支付时间
                                $result_order_update = $order_model->updateData();//更新订单状态为待发货
                                if(!$result_order_update)
                                {
                                    $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                                    $response->status = STATUS_UNKNOWN;
                                    $response->description = '更新订单状态失败';
                                    return $response;
                                    break;
                                }
                            }
                        }
                    }
                }
                else //不是余额支付
                {
                    if($pay_type == 1)//微信
                    {
                        $order_type = $financial_details->transfer_way;
                        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){//微信公众号支付
                            $pay_para = $this->getWxPayInfo($order_type,$pay_details->out_trade_no,$pay_details->cash,1);
                        }
                        else
                        {
                            $pay_H5 = $this->getWxPayInfo($order_type,$pay_details->out_trade_no,$pay_details->cash,2);
                            if($pay_H5 && $pay_H5['return_code'] == 'SUCCESS')
                            {
//                                $url = $this->url()->fromRoute('wap-order', array(
//                                    'action' => 'successfulPayment'
//                                ));
//                                $return_url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
                                $pay_url = $pay_H5['mweb_url'] . '&redirect_url=';
                            }
                            else
                            {
                                $response->status = STATUS_UNKNOWN;
                                $response->description = '支付失败';
                                return $response;
                            }
                        }

                    }
                    elseif($pay_type == 2) //支付宝
                    {

                    }
                    elseif($pay_type == 4)//4微信扫码支付（PC）V2.0
                    {
                        $order_type = $financial_details->transfer_way;
                        $pay_para = $this->getWxPayInfo($order_type,$pay_details->out_trade_no,$pay_details->cash,3);
                    }
                    elseif($pay_type == 5)//5App微信支付V2.0
                    {
                        $order_type = $financial_details->transfer_way;
                        $pay_para = $this->getWxPayInfo($order_type,$pay_details->out_trade_no,$pay_details->cash,4);
                    }
                    elseif($pay_type == 6)//6App支付宝支付
                    {
                        require_once 'AiiLibrary/AliPay/AliPayApi.php';
                        $alipay = new \AlipayApi();
                        $alipay->out_trade_no = $pay_details->out_trade_no;
                        $test_amount = '0.0' . $pay_details->cash * 100;
                        $alipay->total_amount = IS_DEBUG == 1 ? $test_amount : $pay_details->cash;
                        $alipay->body = '支付订单款：'.$pay_details->cash;
                        $alipay->subject ='支付订单款：'.$pay_details->cash;
                        $ali_pay = $alipay->submitPay(2);
                    }
                }

                $this->adapter->getDriver()->getConnection()->commit();
                break;
        }

        $response->status = STATUS_SUCCESS;
        $response->wxPay = $pay_para ? $pay_para : '';
        $response->aliPay = $ali_pay ? $ali_pay : '';
        $response->transferNo = $pay_log_id;
        $response->id = $id;
        $response->mwebUrl = $pay_url;
        if(isset($order_details) && isset($order_details->type))
        {
            $_SESSION['pay_para'] =  ['orderType'=>$order_details->type,'payType'=>$pay_type,'payId'=>$pay_log_id];
        }
        return $response;
    }
}
