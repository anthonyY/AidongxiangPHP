<?php
namespace Admin\Model;
/**
* 管理员表
*
* @author 系统生成
*
*/
class AdminGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *用户名，>= 6个字符
    */
    public $name;

    /**
    *移动电话（登录账号）
    */
    public $mobile;

    /**
    *密码，md5加密
    */
    public $password;

    /**
    *真实名称
    */
    public $realName;

    /**
    *1 是否是超级管理员，1否，2是
    */
    public $super;

    /**
    *平台类型1 平台管理员 
    */
    public $type;

    /**
    *状态：1正常；2注销／停用
    */
    public $status;

    /**
    *管理员类型id
    */
    public $adminCategoryId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","name","mobile","password","realName","super","type","status","adminCategoryId","delete","timestamp"];

    public $table = DB_PREFIX . 'admin';

    public function getList()
    {
        return $this->getAll(['delete'=>DELETE_FALSE]);
    }

}