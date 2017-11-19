<?php
namespace Api\Controller;

use Api\Controller\Request\GroupBuyingListWhereRequest;

/**
 * 业务，拼团列表
 */
class GroupBuyingList extends CommonController
{
    public $valid_time = 24;//拼团有效期（24小时）

    public function __construct()
    {
        $this->myWhereRequest = new GroupBuyingListWhereRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $table_where = $this->getTableWhere();
        $groupBuyingGoodsId = $table_where->groupBuyingGoodsId;
        if(!$groupBuyingGoodsId)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $total = 0;
        $list = array();

        $this->tableObj = $this->getViewUserGroupBuyingTable();
        $this->initModel();
        $this->tableObj->groupBuyingGoodsId = $groupBuyingGoodsId;
        $this->tableObj->userId = $this->getUserId();
        $data = $this->tableObj->getApiList();
        if(isset($data['list']) && $data['list'])
        {
            $total = $data['total'];
            foreach ($data['list'] as $val) {
                if($val->group_number - $val->member_number > 0)
                {
                    $item = [
                        'id' => $val->id,
                        'surplusPerson' => $val->group_number - $val->member_number,
                        'surplusTime' => '',
                        'isGroup' => isset($val->isGroup) ? $val->isGroup : 1,
                        'user' => ['imagePath'=>$val->path . $val->filename,'mobile'=>$val->u_mobile,'name'=>$val->u_name]
                    ];
                    $end_timestamp = strtotime($val->timestamp) + $this->valid_time * 3600;
                    $time_diff_res = $this->getTimeDiff(time(),$end_timestamp);
                    $item['surplusTime'] = $time_diff_res['hour'].":".$time_diff_res['min'].":".$time_diff_res['sec'];
                    $list[] = $item;
                }
                else
                {
                    $total--;
                }
            }
        }

        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->groups = $list;
        return $response;
    }
}