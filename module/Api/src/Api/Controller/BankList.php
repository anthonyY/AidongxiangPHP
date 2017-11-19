<?php
namespace Api\Controller;

/**
 * 用户银行列表
 * @author WZ
 */
class BankList extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $total = 0;
        $list = array();
        $this->tableObj = $this->getBankBranchTable();
        $this->tableObj->userId = $this->getUserId();
        $this->initModel();
        $data = $this->tableObj->getUserApiList();
        if(isset($data['list']) && $data['list'])
        {
            foreach ($data['list'] as $val) {
                $account = '';
                $j = 1;
                for($i=0;$i<(strlen($val->account)-4);$i++)
                {
                    $j++;
                    $account .= '*';
                    if($j > 4)
                    {
                        $account .= " ";
                        $j = 1;
                    }
                }
                $account = $account." ".substr($val->account,-4);

                $item = [
                    'id' => $val->id,
                    'bankName' => $val->bank,
                    'account' => $account
                ];
                $list[] = $item;
            }
            $total = $data['total'];
        }
        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->banks = $list;
        return $response;
    }
}
