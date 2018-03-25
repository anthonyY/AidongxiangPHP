<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 *
 * @author WZ
 *
 */
class MicroblogItem extends Item
{

    /**
     * 正文
     *
     * @var string
     */
    public $content;

    /**
     * 图片数组，例：[21,56,35]，图片视频二选一
     *
     * @var array
     */
    public $imageIds;

    /**
     * 小视频ID，图片视频二选一
     *
     * @var number
     */
    public $videoId;

    /**
     * 地址，选择地址时
     * @var
     */
    public $address;

    /**
     * 区ID，选择地址时
     * @var
     */
    public $regionId;

    /**
     * 经度，选择地址时
     * @var
     */
    public $longitude;

    /**
     * 纬度，选择地址时
     * @var
     */
    public $latitude;

    /**
     * 转发的父微博ID
     * @var
     */
    public $parentId;
}