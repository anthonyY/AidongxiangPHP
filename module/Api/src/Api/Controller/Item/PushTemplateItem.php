<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 *
 * @author WZ
 *        
 */
class PushTemplateItem extends Item
{
    /**
     * 推送标题，仅安卓有用
     * @var String
     */
    public $title;
    
    /**
     * 推送正文
     * @var String
     */    
    public $content;
    
    /**
     * 推送其它参数
     * @var Array
     */
    public $push_args = array();
}