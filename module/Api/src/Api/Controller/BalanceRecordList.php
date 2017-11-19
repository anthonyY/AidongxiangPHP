<?php
namespace Api\Controller;

/**
 * 用户余额明细列表
 */
class BalanceRecordList extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();

        $list = array();
        $total = 0;
        $user_id = $this->getUserId();
        $this->tableObj = $this->getFinancialTable();
        $this->initModel();
        $this->tableObj->userId = $user_id;
        $this->tableObj->userType = 1;
        $this->tableObj->status = 1;
        $data = $this->tableObj->getApiList();

        if($data['list'])
        {
            foreach ($data['list'] as $val) {
                $item = array(
                    'id' => $val->uuid,
                    'money' => $val->cash,
                    'income' => $val->income,
                    'transferWay' => $val->transfer_way,
                    'timestamp' => $val->timestamp,
                );
                $list[] = $item;
            }
            $total = $data['total'];
        }
        $response->total = $total;
        $response->records = $list;
        return $response;
    }
}
