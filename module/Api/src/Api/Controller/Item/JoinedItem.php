<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 *
 * @author liujun
 *
 */
class JoinedItem extends Item
{

    /**
     * 负责人
     * @var String
     */
    public $name;

    /**
     * 店铺名称
     * @var String
     */
    public $merchantName;

    /**
     * 手机号码
     * @var String
     */
    public $mobile;

    /**
     * 店铺招牌IDs
     * @var array
     */
    public $imageIds;

    /**
     * 商家分类ID
     * @var String
     */
    public $categoryId;

    /**
     * 营业执照IDs
     * @var array
     */
    public $businessLicenseIds;

    /**
     * 店铺介绍
     */
    public $description;

    /**
     * 经营社区ID
     */
    public $communityRegionId;

    /**
     * 商家联系地址对像
     * @var unknown
     */
    public $address;


    function __construct()
    {
        $this->address = new AddressItem();
        //$this->contact_address = new AddressItem();

    }

}