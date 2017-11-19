<?php
namespace Api\Controller;

use Api\Controller\Request\UserDetailsRequest;

/**
 * 用户详情协议
 * @author
 * LZW
 */
class UserDetails extends User
{

    public function __construct()
    {
        $this->myRequest = new UserDetailsRequest();
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
        $user_id = $this->getUserId();
        $view_user_model = $this->getViewUserTable();
        $view_user_model->id = $user_id;
        $details = $view_user_model->getDetails();
        if(!$details)
        {
            return STATUS_NODATA;
        }
        $user_level = array(
            'id' => $details->user_level_id,
            'name' => $details->user_level_name,
            'discount' => $details->discount,
        );
        $View_notification_records = $this->getViewNotificationRecordsTable();
        $cart_model = $this->getCartTable();
        $View_notification_records->userId = $user_id;
        $un_read_num = $View_notification_records->getUnReadNum();
        $cart_model->userId = $user_id;
        $cart_num = $cart_model->getCartNum();
        $user = array(
            'id' => $details->uuid,
            'name' => $details->name,
            'mobile' => $details->mobile,
            'realName' => $details->real_name,
            'sex' => $details->sex,
            'image' => $details->image,
            'imagePath' => $details->path && $details->filename ? $details->path . $details->filename : '',
            'community' => $details->region_name,
            'regionId' => $details->region_id,
            'regionInfo' => '',
            'address' => $details->street,
            'userLevel' => $user_level,
            'cash' => $details->cash,
            'points' => $details->points,
            'description' => $details->description,
            'unReadNum' => $un_read_num,
            'cartNum' => $cart_num,
            'issetPayPwd' => $details->pay_password ? 1 : 2,
            'totalConsumption' => $details->total_consumption,
        );
        if($details->region_id)
        {
            $region = $this->getPlatformCommonController()->getRegionInfoArray($details->region_id);
            if($region['region_info'])
            {
                $user['regionInfo'] = $this->getPlatformCommonController()->getProvinceCityCountryName($region['region_info']);
            }
        }

        $response->status = STATUS_SUCCESS;
        $response->user = $user;
        return $response;
    }
}