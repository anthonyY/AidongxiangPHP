<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class GetPayParameterRequest extends Request
{
    /**
     * 支付方式
     *
     * @var number
     */
    public $pay_type = "payType";

    /**
     * 微信open_id
     * @var unknown
     */
    public $open_id = 'openId';

    /**
     * 微信open_id
     * @var unknown
     */
    public $golden_cat_deduction = 'goldenCatDeduction';

    /**
     * 微信open_id
     * @var unknown
     */
    public $silver_cat_deduction = 'silverCatDeduction';
}