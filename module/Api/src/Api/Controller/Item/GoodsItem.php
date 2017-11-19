<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 *
 * @author WZ
 *
 */
class GoodsItem extends Item
{

    /**
     *
     * @var number
     */
    public $id;

    /**
     * 商品名称
     *
     * @var string
     */
    public $name;

    /**
     * 通用券
     *
     * @var float
     */
    public $golden_cat = 'goldenCat';

    /**
     * 银猫券
     *
     * @var float
     */
    public $silver_cat = 'silverCat';


    /**
     * 现金
     *
     * @var float
     */
    public $cash;

    /**
     * 原价
     * @var Float
     */
    public $original_price = 'originalPrice';

    /**
     * 数量
     *
     * @var number
     */
    public $number;

    /**
     * 已出售数量
     *
     * @var number
     */
    public $sale_number = 'saleNumber';

    /**
     * 商品图像数组
     *
     * @var array
     */
    public $images;

    /**
     * 1上架；2下架；
     *
     * @var number
     */
    public $status;

    /**
     * 分类id
     *
     * @var number
     */
    public $category_id = 'categoryId';

    /**
     * 商品描述
     *
     * @var String
     */
    public $description;

    /**
     * 开始时间
     *
     * @var string
     */
    public $start_time = 'startTime';

    /**
     * 截止时间
     *
     * @var string
     */
    public $deadline = 'endTime';

    /**
     * 赠送通用券
     *
     * @var number
     */
    public $give_golden_cat = 'giveGoldenCat';

    /**
     * 赠送银猫
     *
     * @var unknown
     */
    public $give_silver_cat = 'giveSilverCat';
}