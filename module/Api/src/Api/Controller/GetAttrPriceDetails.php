<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;
use Api\Controller\Request\GetAttrPriceDetailsRequest;

/**
 * 获取属性价格
 */
class GetAttrPriceDetails extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new GetAttrPriceDetailsRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action ? $request->action : 1;//1普通商品，2限时抢购，3拼团
        $id = $request->id;//限时抢购id或拼团id，a=2|3
        $attrIds = $request->attrIds;
        $presaleTime = $request->presaleTime;
        if(!$attrIds || ($action != 1 && !$id))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if($action == 1)
        {
            $this->tableObj = $this->getGoodsAttrRelationTable();
            $this->tableObj->attrIds = $attrIds;
            $details = $this->tableObj->getAttrDetailsByAttrIds();
        }
        else
        {
            if($action == 2)
            {
                $this->tableObj = $this->getViewGoodsFlashSaleTable();
            }
            else if($action == 3)
            {
                $this->tableObj = $this->getViewGroupBuyingGoodsTable();
            }
            $this->tableObj->id = $id;
            $details = $this->tableObj->getDetails();
        }

        //如果是预购商品，查询pre_order_product_inventory每日更新库存表的库存
        if($presaleTime)
        {
            $details->stock = 0;
            $GoodsAttrRelationTable = $this->getGoodsAttrRelationTable();
            $GoodsAttrRelationTable->attrIds = $attrIds;
            $AttrRelationDetails = $GoodsAttrRelationTable->getAttrDetailsByAttrIds();//查询商品属性详情
            if($AttrRelationDetails)
            {
                $ViewPreOrderProductInventoryTable = $this->getViewPreOrderProductInventoryTable();
                $ViewPreOrderProductInventoryTable->goodsAttrRelationId = $AttrRelationDetails->id;
                $ViewPreOrderProductInventoryTable->date = $presaleTime;
                $PreOrderProductInventoryDetails = $ViewPreOrderProductInventoryTable->getOneByDate();//查询预购产品的库存
                if($PreOrderProductInventoryDetails && $PreOrderProductInventoryDetails->stock > 0)
                {
                    $details->stock = $PreOrderProductInventoryDetails->stock;
                }
            }
        }

        $attr = '';
        if($details)
        {
            $attr = array(
                'originalPrice' => $action != 3 ? $details->original_price : $details->attr_original_price,
                'price' => $action == 1 ? $details->price : ($action == 2 ? $details->active_price : $details->group_price),
                'number' => $details->stock,
            );
        }
        $response->attr = $attr;
        return $response;

    }
}
