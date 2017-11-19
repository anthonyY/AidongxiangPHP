<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 * 商家对象
 *
 * @author WZ
 *
 */
class MerchantItem extends Item
{
    /**
     * 联系人
     */
    public $name;

    /**
     * 商家名称
     *
     * @var string
     */
    public $company_name = 'companyName';

    /**
     * 手机号码
     *
     * @var string
     */
    public $mobile;

    /**
     * 座机
     *
     * @var string
     */
    public $phone;

    /**
     * 商家描述
     *
     * @var string
     */
    public $description;

    /**
     * 通用券赠送比例
     *
     * @var number
     */
    public $golden_cat_proportion = 'goldenCatProportion';

    /**
     * 银猫券赠送比例
     *
     * @var number
     */
    public $silver_cat_proportion = 'silverCatProportion';

    /**
     * 收到通用券的比例
     *
     * @var number
     */
    public $golden_cat_get_proportion = 'goldenCatGetProportion';

    /**
     * 折扣比例
     */
    public $discount;

    /**
     * 地址对象
     *
     * @var AddressItem
     */
    public $address;

    /**
     * 邮箱地址
     */
    public $email;

    /**
     * 营业额
     */
    public $turnover;

    /**
     * 行业
     */
    public $industry;

    /**
     * 图片
     */
    public $images;

    /**
     * 头像
     */
    public $head_image = 'headImage';

    /**
     * 宣传语
     * @var unknown
     */
    public $marketing_lingo = 'marketingLingo';

    /**
     * 营业开始时间
     * @var string
     */
    public $time_of_opening = 'timeOfOpening';

    /**
     * 营业结束时间
     * @var string
     */
    public $end_time_opening = 'endTimeOpening';

    /**
     * 是否显示营业时间
     * @var string
     */
    public $show_opening = 'showOpening';

    /**
     * 服务设施数组ID
     * @var unknown
     */
    public $service_facility_id = 'facilityIds';

    /**
     * 人均价格
     * @var folat
     */
    public $average_price = 'averagePrice';

    function __construct()
    {
        $this->address = new AddressItem();
    }
}