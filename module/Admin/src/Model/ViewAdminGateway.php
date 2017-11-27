<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewAdminGateway extends BaseGateway {
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
    *类型对应的模块id列表多个以|隔开
    */
    public $actionList;

    /**
    *名称
    */
    public $adminCategoryName;

    /**
    *字段数组
    */
    protected $columns_array = ["id","name","mobile","password","realName","super","type","status","adminCategoryId","delete","timestamp","actionList","adminCategoryName"];

    public $table = 'view_admin';

    /**
     * 管理员列表
     */
    public function getList()
    {
        $where = new Where();
        $where->equalTo('delete', 0);
        if ($this->status) {
            $where->equalTo('status', $this->status);
        }
        if ($this->adminCategoryId) {
            $where->equalTo('admin_category_id', $this->adminCategoryId);
        }
        $search_key = array('name', 'mobile');
        return $this->getAll($where);
    }
}