<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\MicroblogItem;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class MicroblogRequest extends Request
{
    /**
     * 微博对象
     */
    public $microblog;

    function __construct()
    {
        parent::__construct();
        $this->message = new MicroblogItem();
    }
}