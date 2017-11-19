<?php
namespace Api\Controller;

use Api\Controller\Request\BankBranchSubmitRequest;

/**
 * 添加银行卡
 */
class BankBranchSubmit extends User
{
    public function __construct()
    {
        $this->myRequest = new BankBranchSubmitRequest();
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
        if(!$request->bankName || !$request->account || !$request->name || !$request->mobile)
        {
            $response->status = 10000;
            $response->description = '请填写完整信息！';
            return $response;
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/", $request->mobile))
        {
            $response->status = 10000;
            $response->description = '请输入正确的手机号码！';
            return $response;
        }
        if(!$request->smscodeId)
        {
            $response->status = 10000;
            $response->description = '请输入验证码！';
            return $response;
        }
        $this->checkSmsComplete(8, $request->smscodeId, $request->mobile);
        $BankBranchTable = $this->getBankBranchTable();
        $BankBranchTable->bank = $request->bankName;
        $BankBranchTable->account = $request->account;
        $BankBranchTable->name = $request->name;
        $BankBranchTable->mobile = $request->mobile;
        $BankBranchTable->userId = $this->getUserId();
        $res = $BankBranchTable->addData();
        if($res)
        {
            return STATUS_SUCCESS;
        }
        else
        {
            $response->status = 10000;
            $response->description = '网络错误！';
            return $response;
        }
    }
}
