<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 *
 * @author WZ
 *
 */
class OrderSubmitRequest extends Request
{

    /**
     * @var 商品数量
     */
    public $number;

    /**
     * @var 收货地址
     */
    public $contactsId;

    /**
     * 支付方式
     * @var
     */

    public $payType;

    /**
     * 微信open_id
     * @var unknown
     */
    public $openId;

    /**
     * 购物车数组对像
     * @var unknown
     */
    public $carts;
    /**
     * 提交类型：1购物车提交 2立即购买提交
     * @var unknown
     */
    public $type;

    /**
     * 支付密码
     * @var
     */
    public $password;

    /**
     * 拼团id V2.0 a=3
     * @var
     */
    public $userGroupBuyingId;

    /**
     * 预购日期V2.1 预购产品
     * @var
     */
    public $presaleTime;

    /**
     * 订房对像
     * @var
     */
    public $roomBook;
}