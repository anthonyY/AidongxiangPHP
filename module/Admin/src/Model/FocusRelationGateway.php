<?php
namespace Admin\Model;
/**
* 关注关系
*
* @author 系统生成
*
*/
class FocusRelationGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *用户id
    */
    public $userId;

    /**
    *被关注的用户id
    */
    public $targetUserId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","userId","targetUserId","delete","timestamp"];

    public $table = DB_PREFIX . 'focus_relation';

}