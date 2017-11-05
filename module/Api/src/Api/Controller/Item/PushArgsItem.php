<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 *
 * @author WZ
 *        
 */
class PushArgsItem extends Item
{
    /**
     * 外键id，如任务id
     * @var Number
     */
    public $id;
    
    /**
     * 参数1：数量（人数/星星/...）
     * @var Number
     */
    public $number;
    
    /**
     * 参数2：名称（昵称/活动名称/...）
     * @var String
     */
    public $name;
    
    
    /**
     * 参数3
     * @var String
     */
    public $param1;
    
    /**
     * 参数4
     * @var String
     */
    public $param2;
    
    /**
     * 安卓用推送通知id
     * @var number
     */
    public $nid = 0;
    
    /**
     * 安卓用推送通知类型
     * @var number
     */
    public $action;
}