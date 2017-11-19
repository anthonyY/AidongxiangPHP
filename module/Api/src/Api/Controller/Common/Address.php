<?php
namespace Api\Controller\Common;

/**
 * 地址类
 * @author WZ
 *
 */
class Address
{
    /**
     * @var 街道
     */
    public $street;

    /**
     * @var 地区编号
     */
    public $region_id = 'regionId';

    /**
     * @var 地区列表json
     */
    public $region_info = 'regionInfo';
}