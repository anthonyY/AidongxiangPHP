<?php
namespace Api\Controller;

/**
 * 查询订单支付结果
 */
class OrderPayQuery extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        if(!$request->id)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $this->checkLogin();

        $this->tableObj = $this->getPayLogTable();
        $this->tableObj->id = $request->id;
        $data = $this->tableObj->getDetails();
        if(!$data)
        {
            return STATUS_NODATA;
        }
        //状态： 1成功；2失败; 3 待支付
        if($data->status == 1)
        {
            return STATUS_SUCCESS;
        }
        elseif($data->status == 2)
        {
            $response->status = STATUS_SUCCESS;
            $response->description = '支付失败';
            return $response;
        }
        elseif($data->status == 3)
        {
            $response->status = STATUS_SUCCESS;
            $response->description = '待支付';
            return $response;
        }
        else
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
    }
}
