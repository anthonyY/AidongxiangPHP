<?php
namespace Api\Controller;

use Api\Controller\Request\CartSubmitRequest;
use Platform\Model\GoodsAttrRelationGateway;

/**
 * 产品添加购物车
 *
 * @author liujun
 */
class CartSubmit extends CommonController
{


    function __construct()
    {
        $this->myRequest = new CartSubmitRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();

        $this->checkLogin();

        $action = $request->action ? (int)$request->action : 0;//提交类型：1加入购物车 2编辑购物车
        $goods_id = $request->goodsId ? $request->goodsId : 0;//产品id a=1 时要传
        $attr_ids = $request->attrIds ? $request->attrIds : '';//属性IDS用|线隔开
        $number = $request->number ? (int)$request->number : 1;//数量
        $cart_id = $request->id ? (int)$request->id : 0;//购物车产品id a=2 要传
        $type = $request->type ? $request->type : 0;//1普通商品 2限时抢购

        if(!in_array($type,array(1,2)) || !in_array($action,array(1,2)) || ($action == 1 && !is_array($goods_id) && !$attr_ids))
        {
           return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }

        $goods_table = $this->getGoodsTable();

        $view_cart_table = $this->getViewCartTable();
        if($action == 1)
        {//加入购物车
            //$goods_table->checkProductInventory($number,$attr_ids);//先判断库存是否足
            //判断购物车里是否存在此商品并且属性相同，存在则更新商品数量不插入购物车
            $goods_ids = is_array($goods_id) ? $goods_id : [$goods_id];
            foreach ($goods_ids as $goods_id) {
                $goods_table->uuid = $goods_id;
                $goods_info = $goods_table->getDetails();
                if(!$goods_info)
                {
                    return STATUS_NODATA;
                }

                //如果是批量加入购物车是没有传attr_ids,需要查询商品规格型号价格最低的attr_ids
                if(!$attr_ids)
                {
                    $GoodsAttrRelationTable = $this->getGoodsAttrRelationTable();
                    $GoodsAttrRelationTable->goodsId = $goods_info->id;
                    $GoodsAttrRelationTable->orderBy = 'price ASC';
                    $attr_ids_list = $GoodsAttrRelationTable->getAttrIdsList();
                    if(isset($attr_ids_list['list']) && $attr_ids_list['list'])
                    {
                        $attr_ids = $attr_ids_list['list'][0]['attr_ids'];
                    }
                    else
                    {
                        continue;
                    }
                }

                $view_cart_table->uuid = $goods_id;
                $view_cart_table->attrIds = $attr_ids;
                $view_cart_table->userId = $this->getUserId();
                $view_cart_table->type = $type;
                $info = $view_cart_table->getDetails();
                $cart_table = $this->getCartTable();
                //入库
                if(!$info)
                {
                    $cart_table->goodsId = $goods_info->id;
                    $cart_table->attrIds = $attr_ids;
                    $cart_table->type = $type;
                    $cart_table->number = $number;
                    $cart_table->userId = $this->getUserId();
                    if($type ==2)
                    {
                        $goods_flash = $this->getViewGoodsFlashSaleTable();
                        $goods_flash->uuid = $goods_id;
                        $goods_flash->attrIds = $attr_ids;
                        $info = $goods_flash->getDetails();
                        if(!$info)
                        {
                            return STATUS_UNKNOWN;
                        }
                        $cart_table->goodsFlashSaleId = $info->id;
                    }

                    $cart_id =  $cart_table->addData();//产品加入购物车
                    if(!$cart_id)
                    {
                        return STATUS_UNKNOWN;
                    }
                }
                else
                {//已存在则更新购物车数量
                    $cart_table->id = $info->id;
                    $cart_table->number = $info->number + $number;
                    $cart_table->updateData();//更新购物车
                    $cart_id = $info->id;
                }
            }
        }
        else
        {//编辑购物车
            $view_cart_table->id = $cart_id;
            $view_cart_table->userId = $this->getUserId();
            $info = $view_cart_table->getDetails();
            if(!$info)
            {
                return STATUS_NODATA;
            }

            $cart_table = $this->getCartTable();
            $cart_table->number = $number;
//            $cart_table->attrIds = $attr_ids;
            $cart_table->id = $cart_id;
            $cart_table->updateData();//更新购物车
        }

        $response->status = STATUS_SUCCESS;
        $response->id = $cart_id.'';
        return $response;
    }
}
