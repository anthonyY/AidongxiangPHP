<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\CommentItem;

/**
 * 定义接收类的属性
 * 继承基础Request
 * @author WZ
 */
class CommentSubmitRequest extends Request
{

    /**
     * 评论对象
     */
    public $comment;

    /**
     * 订单产品ID
     */
    public $orderGoodsId;

    public function __construct()
    {
        parent::__construct();
        $this->comment = new CommentItem();
    }
}