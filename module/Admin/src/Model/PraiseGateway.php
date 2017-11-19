<?php
namespace Admin\Model;
/**
* 点赞
*
* @author 系统生成
*
*/
class PraiseGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *外键id，根据type
    */
    public $fromId;

    /**
    *1音频，2评论，3微博,
    */
    public $type;

    /**
    *用户的id
    */
    public $userId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","fromId","type","userId","delete","timestamp"];

    public $table = DB_PREFIX . 'praise';

}