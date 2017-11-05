<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 *
 * @author WZ
 *        
 */
class PushFromItem extends Item
{
    /**
     * 外键id，如任务/活动id
     * @var Number
     */
    public $id;
    
    /**
     * 触发这次推送的用户id
     * @var Number
     */    
    public $user_id;
    
    /**
     * 这个推送的类型：0公告/其它：1职位，2论坛，3订单；
     * @var Number
     */
    public $type;
}