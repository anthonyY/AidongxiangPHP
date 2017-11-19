<?php
namespace Api\Controller;
use Api\Controller\Request\ExchangeScoreRequest;

/**
 * 业务，积分兑换余额
 */
class ExchangeScore extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new ExchangeScoreRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $points = $request->points;
        $user_model = $this->getUserTable();
        $user_model->id = $this->getUserId();
        $user_details = $user_model->getDetails();
        if($user_details)
        {
            $mallExchangeScore = new mallExchangeScore();
            $mallExchangeScore->mobileNo = $user_details->mobile;
            $mallExchangeScore->userId = $user_details->user_id;
            $mallExchangeScore->scoreNumber = $points;
            $res = $mallExchangeScore->submit();
            $respond = $mallExchangeScore->getRespCode();
            if($respond && $respond['respCode'] == 0)
            {
                $user_model->cash = $res->balance;
                $user_model->points = $res->score;
                $user_model->updateData();

                //新增财务记录
                $financial_table = $this->getFinancialTable();
                $financial_table->income = 1;
                $financial_table->userType = 1;
                $financial_table->cashBefore = $user_details->cash;
                $financial_table->cashAfter = $res->balance;
                $financial_table->cash = $res->balance - $user_details->cash;
                $financial_table->transferWay = 8;
                $financial_table->status = 1;
                $financial_table->userId = $user_details->id;
                $financial_table->description = '积分兑换余额';
                $financial_table->addData();
                return STATUS_SUCCESS;
            }
            else
            {
                $response->status = STATUS_PARAMETERS_INCOMPLETE;
                $response->description = $respond['respMsg'];
                return $response;
            }

        }
        else
        {
            return STATUS_USER_NOT_EXIST;
        }

    }
}
