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
class MerchantRequest extends Request
{
    /**
     * 当前用户经度
     */
    public $latitude;

    /**
     * 当前用户纬度
     */
    public $longitude;


}