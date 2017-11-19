<?php
namespace Api\Controller;

use Api\Controller\Request\CustomerServiceSubmitRequest;
use Zend\Db\Sql\Where;

/**
 * 申请售后
 * @author WZ
 */
class CustomerServiceSubmit extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new CustomerServiceSubmitRequest();
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
        $this->checkLogin();
        $service = $request->service;
        $type = $service->type;
        $receivingStatus = $service->receivingStatus;
        $orderGoodsId = $service->orderGoodsId;
        $reason = $service->reason;
        $image_ids = $service->imageIds;
        $reasonType = $service->reasonType;
        if(!in_array($type,array(1,2)) || !in_array($receivingStatus,array(1,2)) || !in_array($reasonType,array(1,2,3,4,5)))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $customer_service_model = $this->getCustomerServiceApplyTable();
        $customer_service_model->type = $type;
        $customer_service_model->reason = $reason;
        $customer_service_model->receivingStatus = $receivingStatus;
        $customer_service_model->reasonType = $reasonType;
        $customer_service_model->userId = $this->getUserId();
        $result = $customer_service_model->customerServiceSubmit($orderGoodsId,$image_ids);

        $response->status = $result['code'];
        $response->description = $result['d'];
        if($result['code'] == STATUS_SUCCESS)
        {
            $response->id = $result['id'];
        }
        return $response;
    }
}
