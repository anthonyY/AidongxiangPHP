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
class CategoryRequest extends Request
{
    /**
     * 门店类型：1自营 2加盟 a!=4时
     *
     * @var number
     */
    public $type;

    /**
     * 商家ID a!=4时
     * @var unknown
     */
    public $merchantId;

}