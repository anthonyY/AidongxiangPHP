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
     * @return Common\Response|string
     * @throws \Exception
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $user_id = $request->id?$request->id:$this->getUserId();
        $view_user_model = $this->getViewUserTable();
        $view_user_model->id = $user_id;
        $details = $view_user_model->getDetails();
        if(!$details)
        {
            return STATUS_NODATA;
        }
        $microblogTable = $this->getMicroblogTable();
        $microblogTable->userId = $user_id;
        $microblogTable->display = 1;
        $microblog_sum = $microblogTable->getSumByUser();
        $focusRelationTable = $this->getFocusRelationTable();
        $focusRelationTable->userId = $user_id;
        $focusRelationTable->targetUserId = $user_id;
        $fansNum = $focusRelationTable->getFansNum();
        $focusNum = $focusRelationTable->getFocusNum();

        $user = array(
            'id' => $details->id,
            'nickName' => $details->nick_name,
            'sex' => $details->sex,
            'imagePath' => $details->head_path . $details->head_filename,
            'description' => $details->description,
            'backImagePath' => $details->back_path . $details->back_filename,
            'regionId' => $details->region_id,
            'regionInfo' => $details->region_info,
            'mobile' => $details->mobile,
            'microblogNum' => $microblog_sum,
            'fansNum' => $fansNum,
            'focusNum' => $focusNum,
        );

        if(!$details->head_filename)
        {
            $userPartner = $this->getUserPartnerTable();
            $user_partner_details = $userPartner->getDetailsByUserId($user_id);
            if($user_partner_details)
            {
                $user['imagePath'] = $user_partner_details->image_url?$user_partner_details->image_url:'';
            }
        }

        $response->status = STATUS_SUCCESS;
        $response->user = $user;
        return $response;
    }
}