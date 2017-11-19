<?php
namespace Api\Controller;

use Api\Controller\Request\MerchantRequest;

/**
 * 业务，商家入驻详情
 */
class MerchantJoinedDetails extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new MerchantRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();

        $MerchantApplyTable = $this->getMerchantApplyTable();
        $ViewAlbumTable = $this->getViewAlbumTable();
        $MerchantApplyTable->userId = $this->getUserId();
        $details = $MerchantApplyTable->getOneByUserId();
        if(!$details)
        {
            return STATUS_NODATA;
        }
        $merchant = [
            'merchantName' => $details->name,
            'mobile' => $details->mobile,
            'name' => $details->contact_name,
            'categoryId' => $details->category_id,
            'description' => $details->description,
            'communityRegionId' => $details->community_id,
            'address' => ['regionId'=>$details->region_id,'street'=>$details->street,'regionInfo'=>$details->region_info],
            'timestamp' => $details->timestamp,
            'status' => $details->status,
            'reason' => $details->reason
        ];

        $images = [];
        $businessLicenses = [];
        $ViewAlbumTable->fromId = $details->id;
        $ViewAlbumTable->type = 5;
        $list = $ViewAlbumTable->getList();
        if($list)
        {
            foreach ($list as $val) {
                $item = [
                    'id' => $val['id'],
                    'path' => $val['path'] . $val['filename'],
                ];
                $images[] = $item;
            }
        }

        $ViewAlbumTable->type = 6;
        $list = $ViewAlbumTable->getList();
        if($list)
        {
            foreach ($list as $val) {
                $item = [
                    'id' => $val['id'],
                    'path' => $val['path'] . $val['filename'],
                ];
                $businessLicenses[] = $item;
            }
        }

        $community = '';
        if($details->community_id)
        {
            $PlatformCommonController = $this->getPlatformCommonController();
            $region_info = $PlatformCommonController->getRegionInfoArray($details->community_id);
            $community = ['regionId'=>$details->community_id,'regionInfo'=>$region_info['region_info']];
        }
        $merchant['community'] = $community;
        $merchant['images'] = $images;
        $merchant['businessLicenses'] = $businessLicenses;

        $response->status = STATUS_SUCCESS;
        $response->joined = $merchant;
        return $response;
    }
}
