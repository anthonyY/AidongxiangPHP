<?php
namespace Api\Controller;

/**
 * 积分记录列表
 */
class PointRecordList extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();

        $list = array();
        $total = 0;
        $user_id = $this->getUserId();
        $this->tableObj = $this->getPointRecordTable();
        $this->initModel();
        $this->tableObj->userId = $user_id;
        $data = $this->tableObj->getApiList();

        if($data['list'])
        {
            foreach ($data['list'] as $val) {
                $item = array(
                    'id' => $val->id,
                    'point' => $val->points,
                    'type' => $val->type,
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
