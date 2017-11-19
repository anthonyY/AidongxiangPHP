<?php
namespace Admin\Model;
/**
* 屏蔽
*
* @author 系统生成
*
*/
class ScreenGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *用户ID
    */
    public $userId;

    /**
    *评论/微博/用户ID
    */
    public $fromId;

    /**
    *1 微博  2 评论 3用户
    */
    public $type;

    /**
    *字段数组
    */
    protected $columns_array = ["id","userId","fromId","type","delete","timestamp"];

    public $table = 'screen';

}