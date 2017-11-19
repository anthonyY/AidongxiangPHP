<?php
namespace Api\Controller;

use Api\Controller\Request\MerchantJoinedSubmitRequest;

/**
 * 商家入驻
 */
class MerchantJoinedSubmit extends CommonController
{
    public $required_array = ['merchantName','mobile','name','categoryId','description'];

    public function __construct()
    {
        $this->myRequest = new MerchantJoinedSubmitRequest();
        parent::__construct();
    }

    /**
     *
     * @return \Api\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $join_object = $request->joined;
        foreach ($this->required_array as $value) {
            if(!$join_object->$value)
            {
                $response->status = STATUS_PARAMETERS_INCOMPLETE;
                $response->description = '请填写完整信息！';
                return $response;
            }
        }
        $imageIds = $join_object->imageIds;
        $businessLicenseIds = $join_object->businessLicenseIds;
        $address = $join_object->address;
        if(!is_array($imageIds) || !is_array($businessLicenseIds) || !$imageIds || !$businessLicenseIds)
        {
            $response->status = STATUS_PARAMETERS_INCOMPLETE;
            $response->description = '请填写完整信息！';
            return $response;
        }
        if(!$address || !$address->regionId || !$address->street)
        {
            $response->status = STATUS_PARAMETERS_INCOMPLETE;
            $response->description = '请填写完整信息！';
            return $response;
        }
        $this->tableObj = $this->getMerchantApplyTable();
        $this->tableObj->userId = $this->getUserId();
        $exist = $this->tableObj->getOneByUserId();
        if($exist && $exist->status == 1)
        {
            $response->status = STATUS_PARAMETERS_INCOMPLETE;
            $response->description = '审核中...，请不要重复申请！';
            return $response;
        }
        if($exist && $exist->status == 2)
        {
            $response->status = STATUS_PARAMETERS_INCOMPLETE;
            $response->description = '已审核通过，请耐心等候！';
            return $response;
        }
        $platformController = $this->getPlatformCommonController();

        $this->tableObj->name = $join_object->merchantName;
        $this->tableObj->mobile = $join_object->mobile;
        $this->tableObj->contactName = $join_object->name;
        $this->tableObj->categoryId = $join_object->categoryId;
        $this->tableObj->description = $join_object->description;
        $this->tableObj->communityId = $join_object->communityRegionId ? $join_object->communityRegionId : 0;
        $this->tableObj->regionId = $address->regionId;
        $this->tableObj->street = $address->street;
        $regionInfo = $platformController->getRegionInfoArray($address->regionId);
        $this->tableObj->regionInfo = $regionInfo['region_info'];
        $this->tableObj->address = $platformController->getProvinceCityCountryName($regionInfo['region_info']).$address->street;
        $this->tableObj->status = 1;

        $res = $this->tableObj->merchantJoinedSubmit($imageIds,$businessLicenseIds);
        $response->status = $res['s'];
        if($res['s'])
        {
            $response->description = $res['d'];
        }
        return $response;
    }
}
