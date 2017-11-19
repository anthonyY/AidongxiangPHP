<?php
namespace Api\Controller;
use Api\Controller\Request\ShoppingCardListWhereRequest;

/**
 * 分类
 *
 * @author Administrator
 *
 */
class ShoppingCardList extends CommonController
{
    public function __construct()
    {
        $this->myWhereRequest = new ShoppingCardListWhereRequest();
        parent::__construct();
    }

    /**
     * 返回一个数组或者Result类
     * @return \Api\Controller\BaseResult
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $action = (int)$request->action;//1可用(有效期未过，并且有钱的)  2不可用(没有钱或已过有效期或未到使用时间) 3订单提交可使用的优惠券列表
        if(!$action || !in_array($action, array(1, 2 ,3))){
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $table_where = $this->getTableWhere();
        $goods_ids = $table_where->goodsIds;//订单提交的产品UUID数组
        if($action == 3 && (!is_array($goods_ids) || !$goods_ids))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }

        $this->tableObj = $this->getViewShoppingCardTable();
        $this->initModel();
        $this->tableObj->userId = $this->getUserId();
        $data = $this->tableObj->getApiList($action,$goods_ids);
        $list = [];
        $total = 0;
        if($data['list'])
        {
            foreach ($data['list'] as $val) {
                $item = array(
                    'id' => $val->id,
                    'name' => $val->name,
                    'cardNumber' => $val->card_number,
                    'parValue' => $val->par_value,
                    'balance' => ($val->par_value - $val->used) > 0 ? ($val->par_value - $val->used) : 0,
                    'startTime' => $val->start_time,
                    'endTime' => $val->end_time,
                    'isAllPlatform' => $val->available_goods_type == 1 && $val->available_service_type == 1 ? 1 : 2,
                );
                if($action == 3)
                {
                    $item['availableType'] =  $val->available_type;
                    $item['availableJson'] = $val->available_json;
                }
                $list[] = $item;
            }
            $total = $data['total'];
        }

        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->shoppingCards = $list;
        return $response;
    }
}