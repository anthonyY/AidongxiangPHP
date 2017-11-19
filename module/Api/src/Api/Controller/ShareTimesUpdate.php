<?php
namespace Api\Controller;

use Api\Controller\Request\ShareTimesUpdateRequest;
use Platform\Model\ShareRecordGateway;

/**
 * 其它，更新分享次数
 */
class ShareTimesUpdate extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new ShareTimesUpdateRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $url = $request->url;
        if(!$url)
        {
            return STATUS_SUCCESS;
        }

        $share_record_table = new ShareRecordGateway($this->adapter);
        $share_record_table->url = $url;
        if($this->getUserId())
        {
            $share_record_table->userId = $this->getUserId();
        }
        $share_record_table->addData();
        return STATUS_SUCCESS;

    }
}
