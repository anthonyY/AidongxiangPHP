<?php
namespace Api\Controller;

use Api\Controller\Request\AddressSubmitRequest;

/**
 * 添加/修改地址
 */
class AddressSubmit extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new AddressSubmitRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $address = $request->address;
        $this->checkLogin();
        $action = $request->action ? $request->action : 1;//1修改/添加地址，2修改默认地址
        if($action == 1)
        {
            if(!$address->name || !$address->mobile || !$address->default || !$address->regionId || !$address->street)
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }

            $region = $this->getPlatformCommonController()->getRegionInfoArray($address->regionId);
            $contacts_model = $this->getContactsTable();
            $columns = $contacts_model->getTableColumns();
            foreach($columns as $v){
                if(isset($address->$v) && $address->$v)
                {
                    $contacts_model->$v = $address->$v;
                }
            }
            // 弄出数据库的address
            $set_address = $this->getPlatformCommonController()->getProvinceCityCountryName($region['region_info']) . $address->street;
            $contacts_model->userId = $this->getUserId();
            $contacts_model->default = $address->default == 2 ? 2 : 1;
            $contacts_model->regionInfo = $region['region_info'];
            $contacts_model->address = $set_address;

            if($address->id)
            {
                // 有id就是修改
                $contacts_model->updateData();
                $id = $address->id;
            }
            else
            {
                // 没id就是插入新的
                $id = $contacts_model->addData();
            }

            if($address->default == 2)//修改其他默认为非默认
            {
                $contacts_model = $this->getContactsTable();
                $contacts_model->default = 1;
                $contacts_model->id = $id;
                $contacts_model->userId = $this->getUserId();
                $contacts_model->changeAllNotDefault();
            }
        }
        else //2仅修改默认地址
        {
            $id = $address->id;
            if(!$address->id)
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $contacts_model = $this->getContactsTable();
            $contacts_model->default = 2;
            $contacts_model->id = $id;
            $contacts_model->userId = $this->getUserId();
            $update_address = $contacts_model->updateData();
            if($update_address)
            {
                //修改其他默认为非默认
                $contacts_model->default = 1;
                $contacts_model->changeAllNotDefault();
            }
        }


        $response->status = STATUS_SUCCESS;
        $response->id = $id;
        return $response;
    }
}
