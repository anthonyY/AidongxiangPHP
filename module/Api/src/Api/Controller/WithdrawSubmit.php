<?php
namespace Api\Controller;

use Api\Controller\Request\WithdrawSubmitRequest;

/**
 * 提现申请
 * @author WZ
 * @version 1.0.140515 WZ
 */
class WithdrawSubmit extends User
{
    public function __construct()
    {
        $this->myRequest = new WithdrawSubmitRequest();
        parent::__construct();
    }

    /**
     * @return \Api\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $user_model = $this->getUserTable();
        $user_model->id = $this->getUserId();
        $user_details = $user_model->getDetails();
        $this->checkSmsComplete(9, $request->smscodeId, $user_details->mobile);
        $res = $user_model->userWithdrawSubmit($this->myRequest);
        $response->status = $res['s'];
        if($res['s'] != STATUS_SUCCESS)
        {
            $response->description = $res['d'];
        }
        return $response;
    }
}
