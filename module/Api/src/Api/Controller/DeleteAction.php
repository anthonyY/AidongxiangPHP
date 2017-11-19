<?php
namespace Api\Controller;

use Api\Controller\Request\DeleteActionRequest;

/**
 * 删除对象<br />
 * action类型，1.删除车辆
 *
 * @author WZ
 *
 */
class DeleteAction extends CommonController
{

    function __construct()
    {
        $this->myRequest = new DeleteActionRequest();
        parent::__construct();
    }

    /**
     *
     * @return string|\Api\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $request->action = $request->action ? $request->action : 1;//类型：1收藏商品 2 购物车产品 3收货地址 4 订单,5银行卡
        $ids = (array)$request->ids;
        if(!$request->ids || !$request->action){
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $result = false;
        switch($request->action){
            case 1:
                // 删除收藏商品
                $favorites_table = $this->getFavoritesTable();
                $favorites_table->userId = $this->getUserId();
                foreach($ids as $v)
                {
                    $favorites_table->id = $v;
                    $favorites_table->deleteUserDate();//删除收藏产品
                }

                break;
            case 2:
                // 删除购物车产品
                $cart_table = $this->getCartTable();
                $cart_table->userId = $this->getUserId();
                foreach($ids as $v)
                {
                    $cart_table->id = $v;
                    $cart_table->deleteData();
                }
                break;
            case 3:
                //删除收货地址
                $contacts_table = $this->getContactsTable();
                $contacts_table->userId = $this->getUserId();
                foreach($ids as $v)
                {
                    $contacts_table->id = $v;
                    $contacts_table->deleteData();
                }
                break;
            case 4:
                //删除订单
                $order_table = $this->getOrderTable();
                $order_table->userId = $this->getUserId();
                foreach($ids as $v)
                {
                    $order_table->uuid = $v;
                    $info = $order_table->getDetails();
                    if($info)
                    {
                        if($info->type == 1 && in_array($info->status,array(5,6)))
                        {//状态为已取消获已完成才可取消
                            $order_table->id = $info->id;
                            $order_table->deleteData();
                        }
                        elseif($info->type ==2 && in_array($info->status,array(4,5)))
                        {
                            $order_table->id = $info->id;
                            $order_table->deleteData();
                        }
                        else
                        {
                            $response->description = '当前订单状态不允许删除！';
                        }
                    }
                }
                break;
            case 5:
                //删除银行卡
                $BankBranchTable = $this->getBankBranchTable();
                foreach($ids as $v)
                {
                    $BankBranchTable->id = $v;
                    $info = $BankBranchTable->getDetails();
                    if($info && $info->user_id == $this->getUserId())
                    {
                        $BankBranchTable->id = $info->id;
                        $BankBranchTable->deleteData();
                    }
                }
                break;
            default:
                // 请求参数不完整
                return STATUS_PARAMETERS_INCOMPLETE;
                break;
        }
        return  STATUS_SUCCESS;
    }
}
