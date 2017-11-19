<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 *
 * @author liujun
 *
 */
class PushItem extends Item
{

    /**
     * 推送标题
     * @var String
     */
    public $title;

    /**
     * 推送内容
     * @var String
     */
    public $content = "content";

    /**
     * 推送用户ID 数组
     * @var Array
     */
    public $user_ids = "userIds";
}