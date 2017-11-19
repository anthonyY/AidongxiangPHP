<?php
/**
 * ExpressWithGoodsCollectionSubmit.php
 * ketx
 *
 * Created by danny on 2017年8月11日.
 * Copyright © 2017年 Aiitec. All rights reserved.
 */

namespace Api\Controller;

use Api\Controller\Request\ExpressWithGoodsCollectionSubmitRequest;
use Platform\Model\ExpressTemplateGateway;

/**
 * 物流信息
 *
 * @author lizuolin
 */
class ExpressWithGoodsCollectionSubmit extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new ExpressWithGoodsCollectionSubmitRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();

        $regionId = $request->regionId;
        $goodses = $request->goodses;
        if(!is_array($goodses) || !$goodses)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        // Request:
        // {"n":"ExpressWithGoodsCollectionSubmit","s":"1m0gjk1py756j7w4qmqag2x54vpge7ut","o":"HTML","t":"2017-08-11 22:25:54","q":{"regionId":"440100","goodses":[{"id":"7","attrIds":"6|7","number":"1"},{"id":"7","attrIds":"8|9","number":"3"},{"id":"6","attrIds":"4|5","number":"2"},{"id":"1","attrIds":"","number":"1"}]}}
        $expressTemplate = new ExpressTemplateGateway($this->adapter);
        $expressGoodsCollection = $expressTemplate->claculateExpressFee($goodses, $regionId);

        foreach ($expressGoodsCollection as $k => $v) {
            // 总价格 四舍五入 保留两位小数
            $expressGoodsCollection[$k]->totalPrice = round($expressGoodsCollection[$k]->totalPrice, 2);
            
            unset($expressGoodsCollection[$k]->expressTemplateId);
            unset($expressGoodsCollection[$k]->expressTemplateWay);
            unset($expressGoodsCollection[$k]->expressTemplateDefaultRuleId);
            unset($expressGoodsCollection[$k]->communityTemplateId);
            unset($expressGoodsCollection[$k]->communityTemplateWay);
            unset($expressGoodsCollection[$k]->communityTemplateDefaultRuleId);
        }

        $response->expressGoodses = $expressGoodsCollection;
        $response->status = STATUS_SUCCESS;

        return $response;
    }
}
