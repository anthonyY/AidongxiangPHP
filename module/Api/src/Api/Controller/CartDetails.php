<?php
namespace Api\Controller;

use Api\Controller\Request\CartSubmitRequest;

/**
 * 产品添加购物车
 *
 * @author liujun
 */
class CartDetails extends CommonController
{
    /**
     * 普通用户
     *
     * @var 1
     */
    const USER_TYPE_NORMAL = 1;

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();

        $this->checkLogin();
        $action = $request->action;
        if(!in_array($action,array(1,2)))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $user_id = $this->getUserId();

        $this->tableObj = $this->getViewCartTable();
        $this->initModel();
        $this->tableObj->userId = $user_id;
        $this->tableObj->goodsType = $action;
        $list = $this->tableObj->getList();
        $carts = array();
        $merchant = array();
        foreach($list['list'] as $value)
        {
            if(!isset($carts[$value->merchant_id]))
            {
//                $merchant['id'] = $value->merchant_id;
                $merchant['id'] = $value->m_uuid;
                $merchant['type'] = $value->m_type;
                $merchant['name'] = $value->merchant_name;
                $cart_goods = array();
                $merchant['cartGoodses'] = array();
                foreach($list['list'] as $v)
                {
                    if($value->merchant_id == $v->merchant_id)
                    {
                        $cart_goods['id'] = $v->id;
                        $cart_goods['goodsType'] = $v->goods_type;
                        $cart_goods['goodsId'] = $v->uuid;
                        $cart_goods['name'] = $v->goods_name;
                        $cart_goods['serviceTypeId'] = $v->service_type_id;

                        $cart_goods['cash'] = $v->type == 1 ? $v->price : $v->active_price;
                        $cart_goods['number'] = $v->number;
                        $cart_goods['stock'] = $v->stock;
                        $cart_goods['status'] = $v->status;
                        $cart_goods['type'] = $v->type;
                        if($v->type == 2)//判断限时抢购是否过期
                        {
//                            $cart_goods['status'] = 1;
                            if(time() > strtotime($v->end_time) || $v->f_delete == 1)
                            {
                                $cart_goods['status'] = 2;
                            }
                        }
                        $album_table = $this->getViewAlbumTable();
                        $album_table->fromId = $v->goods_id;
                        $album_table->type = 1;
                        $image = $album_table->getDetails();
                        $cart_goods['imagePath'] = '';
                        if($image){
                            $cart_goods['imagePath'] = $image->path . $image->filename;
                        }
                        $attrDesc = '';
                        $cart_goods['attrIds'] = $v->attr_ids;
                        if($v->attr_ids){
                            $attr_ids = explode("|", $v->attr_ids);
                            $goods_attr_table = $this->getGoodsAttrTable();
                            $goods_attr_info = $goods_attr_table->getAttrName($attr_ids);
                            foreach($goods_attr_info as $v){
                                $attrDesc .= $v['name'];
                            }
                        }

                        $cart_goods['attrDesc'] = $attrDesc;
                        $merchant['cartGoodses'][] = $cart_goods;
                    }
                }

                $carts[$value->merchant_id]['merchant'] = $merchant;
            }
        }

        $response->status = STATUS_SUCCESS;
        $response->carts = array_values($carts);
        return $response;
    }
}
