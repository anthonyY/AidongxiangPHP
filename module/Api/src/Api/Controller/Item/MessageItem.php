<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 *
 * @author WZ
 *
 */
class MessageItem extends Item
{

    /**
     * 正文
     *
     * @var string
     */
    public $content;

    /**
     * 用户id
     *
     * @var number
     */
    public $userId;

    /**
     * 说话人 id（0 表示管理员）
     *
     * @var number
     */
    public $adminId;
}