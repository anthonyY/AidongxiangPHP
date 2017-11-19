<?php
namespace Api\Controller;

/**
 * 地址详情
 */
class AddressDetails extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();

        $details = '';
        $this->tableObj = $this->getContactsTable();
        $this->tableObj->userId = $this->getUserId();
        $this->tableObj->id = $request->id;
        $data = $this->tableObj->getDetails();
        if($data)
        {
            $region = $this->getPlatformCommonController()->getRegionInfoArray($data->region_id);
            $details = array(
                'id' => $data->id,
                'name' => $data->name,
                'mobile' => $data->mobile,
                'default' => $data->default,
                'regionId' => $data->region_id,
                'regionInfo' =>$data->region_info,
                'address' =>$data->address,
                'street' => $data->street,
                'timestamp' => $data->timestamp,
            );
        }

        $response->status = STATUS_SUCCESS;
        $response->address = $details;
        return $response;
    }
}
