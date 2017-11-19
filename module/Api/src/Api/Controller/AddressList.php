<?php
namespace Api\Controller;

/**
 * 地址列表
 */
class AddressList extends CommonController
{

    public function index()
    {
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $list = array();
        $total = 0;
        $this->tableObj = $this->getContactsTable();
        $this->initModel();
        $this->tableObj->userId = $this->getUserId();
        $data = $this->tableObj->getApiList();
        if($data['list'])
        {
            foreach ($data['list'] as $val) {
                $item = array(
                    'id' => $val->id,
                    'name' => $val->name,
                    'mobile' => $val->mobile,
                    'default' => $val->default,
                    'regionId' => $val->region_id,
                    'regionInfo' => $val->region_info,
                    'street' => $val->street,
                    'address' =>$val->address,
                    'timestamp' => $val->timestamp,
                );
                $list[] = $item;
            }
            $total = $data['total'];
        }

        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->address = $list;
        return $response;
    }
}
