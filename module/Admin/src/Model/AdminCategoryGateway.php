<?php
namespace Admin\Model;
/**
* 管理员角色权限表
*
* @author 系统生成
*
*/
class AdminCategoryGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *名称
    */
    public $name;

    /**
    *类型对应的模块id列表多个以|隔开
    */
    public $actionList;

    /**
    *平台类型1 平台管理员， 2商家管理员，3自营商家管理员
    */
    public $type;

    /**
    *字段数组
    */
    protected $columns_array = ["id","name","actionList","type","delete","timestamp"];

    public $table = DB_PREFIX . 'admin_category';

}