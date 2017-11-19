<?php
/**
 * ExpressGoods.php
 * ketx
 * 
 * Created by danny on 2017年8月11日.
 * Copyright © 2017年 Aiitec. All rights reserved.
 */

namespace Platform\Object;
 
class ExpressGoods extends Obj
{
    /**
     * 物流配送运费
     * @var number
     */
    public $expressFee; 
    
    /**
     * 物流配送运费模版
     * @var number
     */
    public $expressTemplateId; 
    public $expressTemplateWay; 
    public $expressTemplateDefaultRuleId; // 默认运费规则
    
    /**
     * 社区配送运费
     * @var number
     */
    public $communityFee;
    
    /**
     * 社区配送运费模版
     * @var number
     */
    public $communityTemplateId;
    public $communityTemplateWay;
    public $communityTemplateDefaultRuleId; // 默认运费规则
    
    /**
     * 总重量(单位：kg)
     * @var number
     */
    public $totalWeight;
    
    /**
     * 总数量／件数
     * @var number
     */
    public $totalNumber;
    
    /**
     * 总价格
     * @var number
     */
    public $totalPrice;
    
    /**
     * 同一商品id的多个规格集合.
     * @var NULL|Array(GoodsGateway)
     */
    public $goodsCollection;
    
    /**
     * 统计物流运费、社区运费.
     */
    
    /**
     * 统计物流运费、社区运费.
     * @param array $expressGoodsCollection
     * @return number[]|NULL[] array("statExpressFee"=>0, "statCommunityFee"=>0);
     */
    public function statFee($expressGoodsCollection = array())
    {
        $stat = array("statExpressFee"=>0, "statCommunityFee"=>0);
        
        for($i = 0; $i < count($expressGoodsCollection); $i ++) {
            $stat["statExpressFee"] += $expressGoodsCollection[$i]->expressFee;
            $stat["statCommunityFee"] += $expressGoodsCollection[$i]->communityFee;
        }
        
       return $stat;
    }
    
}







