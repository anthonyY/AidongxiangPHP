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
class ReportRequest extends Request
{
    /**
     * 举报分类
     */
    public $categoryId;

    /**
     * 举报内容
     */
    public $content;

}