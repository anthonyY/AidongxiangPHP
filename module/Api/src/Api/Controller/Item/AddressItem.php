<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 * 地址基础类
 *
 * @author WZ
 *
 */
class AddressItem extends Item
{

    public $id;

    /**
     * 联系人
     * @var 联系人
     */
    public $name;

    /**
     * 联系电话
     * @var 联系电话
     */
    public $mobile;

    /**
     * 1否（默认）；2是；
     * @var 是否默认
     */
    public $default;

    /**
     * 地区
     * @var 地区
     */
    public $regionId;

    /**
     * 街道
     * @var 街道
     */
    public $street;

    /**
     * 经度
     * @var 经度
     */
    public $longitude;

    /**
     * 纬度
     * @var 纬度
     */
    public $latitude;
}