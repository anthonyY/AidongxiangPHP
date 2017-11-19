<?php
namespace Api\Controller\Request;

use Api\Controller\Common\WhereRequest;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 * @author WZ
 */
class GoodsWhereRequest extends WhereRequest
{
    /**
     * @var 分类id
     */
    public $categoryId;

    /**
     * @var 1.商品（默认）；2服务，action=2|6
     */
    public $type;

    /**
     * @var 社区id
     */
    public $communityId;

    /**
     * @var 城市id
     */
    public $cityId;

    /**
     * @var商家ID
     */
    public $merchantId;

    /**
     * @var分类类型，1平台分类，2门店分类
     */
    public $categoryType;

    /**
     * @var 板块类型 1商城首页，2商城列表，3服务首页，4服务列表
     */
    public $moduleType;

    /**
     * @var购物卡ID（V1.1新增）
     */
    public $shoppingCardId;

    /**
     *优惠券ID（V2.0新增）a=10
     */
    public $couponId;

    /**
     * 模块推荐标签ID（V2.0新增）a=11
     */
    public $partRecommendLabelId;

    /**
     * 商品标签ID数组PC
     */
    public $labelIds;

}