<?php
namespace Api\Controller;

use Api\Controller\Request\OrderStatusUpdateRequest;
use Zend\Db\Sql\Expression;

/**
 * 任务，任务状态更新
 * @author LJW
 * @version 1.0.141013 WZ 添加订单跟踪记录
 */
class OrderStatusUpdate extends CommonController
{
    /**
     * 3 确认收货
     * @var number
     */
    const ORDER_STATUS_RECEIPT = 4;

    /**
     * 取消商品订单
     * @var number
     */
    const ORDER_STATUS_GOODS_CANCEL = 6;

    /**
     * 取消服务订单
     * @var number
     */
    const ORDER_STATUS_SERVICE_CANCEL = 5;

    public function __construct()
    {
        $this->myRequest = new OrderStatusUpdateRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        // 1确认收货（默认）；
        $action = $request->action ? $request->action : '1';
        $id = $request->id; // 订单id；

        if(!in_array($action, array('1', '2')) && !$id){
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $this->checkLogin();
        $this->tableObj = $this->getOrderTable();
        $this->tableObj->uuid = $id;
        $details = $this->tableObj->getDetails();
        if(!$details)
        {
            return STATUS_NODATA;
        }
        if($details->user_id != $this->getUserId())
        {
            return STATUS_ILLEGAL_OPERATION;
        }
        $this->tableObj->uuid = null;
        $this->tableObj->id = $details->id;
        if($action == 1){//确认收货
            $this->tableObj->status = self::ORDER_STATUS_RECEIPT;
            $this->tableObj->receiveTime = date('Y-m-d H:i:s');
            $confirm_result = $this->tableObj->confirmReceive();
            $response->status = $confirm_result['s'];
            $response->description = $confirm_result['d'];
            return $response;
        }else{//取消订单，
            if(!in_array($request->cancelReasonType,[1,2,3]))
            {
                $response->status = STATUS_PARAMETERS_INCOMPLETE;
                $response->description = '请选择取消原因';
                return $response;
            }
            if(($details->type == 1 && !in_array($details->status,[1,2,7])) || ($details->type == 2 && !in_array($details->status,[1,2,6])))//如果不是待支付和待发货状态和待消费状态和待成团，不能取消
            {
                $response->status = STATUS_ILLEGAL_OPERATION;
                $response->description = '该状态下不能取消订单';
                return $response;
            }
            if($details->type == 1)//商品订单
            {
                $this->tableObj->status = self::ORDER_STATUS_GOODS_CANCEL;
            }
            else if($details->type == 2)//服务订单
            {
                if(in_array($details->status,[2,6]))
                {
                    $this->tableObj->serviceStatus = 1;
                    $this->tableObj->status = $details->status;//支付了就要提交后台申请，通过后才能跟新取消状态,所以还是待消费
                }
                else
                {
                    $this->tableObj->status = self::ORDER_STATUS_SERVICE_CANCEL;//服务订单取消，还没有支付，直接取消，支付了就要提交后台申请，通过后才能跟新取消状态
                }
            }
            else
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $this->tableObj->cancelReasonType = $request->cancelReasonType;
            $this->tableObj->cancelTime = $this->getTime();
            $res = $this->tableObj->cancelOrderRefund();
            if($res['s'])
            {
                $response->status = $res['s'];
                $response->description = $res['d'];
                return $response;
            }

            //发送已取消订单消息给用户start
            $NotificationRecordsTable = $this->getNotificationRecordsTable();
            $NotificationRecordsTable->title = '订单已取消';
            $NotificationRecordsTable->content = '订单已取消';
            $NotificationRecordsTable->type = 2;
            $NotificationRecordsTable->userType = 1;
            $NotificationRecordsTable->userId = $this->getUserId();
            $NotificationRecordsTable->merchantId = $details->merchant_id;
            $NotificationRecordsTable->orderId = $details->id;
            $NotificationRecordsTable->addData();
            //发送已取消订单消息给用户end
        }

        return STATUS_SUCCESS;
    }
}
