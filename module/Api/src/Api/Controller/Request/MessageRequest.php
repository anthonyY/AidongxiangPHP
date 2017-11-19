<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\MessageItem;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class MessageRequest extends Request
{
    /**
     * 留言对象
     */
    public $message;

    function __construct()
    {
        parent::__construct();
        $this->message = new MessageItem();
    }
}