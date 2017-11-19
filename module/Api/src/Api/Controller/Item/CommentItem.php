<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 * @author WZ
 */
class CommentItem extends Item
{
    /**
     * 评价星级：1一星；2二星；3三星；4四星；5五星；
     *
     * @var 1|2|3|4|5
     */
    public $stars;

    /**
     * 正文
     *
     * @var string
     */
    public $content;

    /**
     * 图片数组ID
     * @var array
     */
    public $imagesId;
}