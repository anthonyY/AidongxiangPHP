<?php
namespace Api\Controller\Request;

use Api\Controller\Common\WhereRequest;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 * @author WZ
 */
class MerchantWhereRequest extends WhereRequest
{
    /**
     * @var 分类id
     */
    public $categoryId;

    /**
     * @var 经度
     */
    public $longitude;

    /**
     * @var 纬度
     */
    public $latitude;

    /**
     * @var 社区id
     */
    public $communityId;

    /**
     * @var 城市id
     */
    public $cityId;

    /**
     * @var 板块类型 1首页；2门店首页，3门店列表
     */
    public $moduleType;

}