<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

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

    /**
     * 管理员登录
     */
    public function adminLogin()
    {
        $where['name'] = $this->name;
        $where['password'] = $this->password;
        $where['delete'] = 0;
        if($admin_info = $this->getOne($where)){
            if($admin_info['status'] == 2){    //帐号被禁用
                return ['s'=>10000,'d'=>'账号被禁用'];
            }else{
                $_SESSION['admin_id'] = $admin_info['id'];
                $_SESSION['admin_name'] = $admin_info['name'];
                $adminCategory = new AdminCategoryGateway($this->adapter);
                $adminCategory->id = $admin_info['admin_category_id'];
                $adminCategoryInfo = $adminCategory->getDetails();
                if($adminCategoryInfo->action_list){
                    $_SESSION['action_list'] = $adminCategoryInfo->action_list;
                }else{
                    $_SESSION['action_list'] = 'all';
                }
                return ['s'=>0,'d'=>'登录成功'];
            }
        }else{
            return ['s'=>10000,'d'=>'账号和密码错误'];
        }
    }

    /**
     * 更新管理员，通过ID更新
     * @param array $set
     * @param array $where
     * @return bool|int
     */
    public function updateData()
    {
        if($this->password)
        {
            $this->password = md5($this->password);
        }
        return parent::updateData();
    }

    /**
     * 删除管理员
     */
    public function deleteData()
    {
        return parent::deleteData();
    }

    //删除职务时判断职务下是否有管理员
    public function getByCategoryId(){
        $where['admin_category_id'] = $this->adminCategoryId;
        $where['delete'] = 0;
        return $this->getOne($where,array('id'));
    }

    /**
     * 查询管理名字是否存在数据
     */
    public function queryName()
    {
        $where = new Where();
        $where->equalTo('name',$this->name);
        if($this->type)
        {
            $where->equalTo('type',$this->type);
        }
        if($this->id)
        {
            $where->notEqualTo('id',$this->id);
        }
        return $this->getOne($where);
    }

    /**
     * 查询管理号码是否存在数据
     */
    public function queryMobile()
    {
        $where = new Where();
        $where->equalTo('mobile',$this->mobile);
        if($this->type){
            $where->equalTo('type',$this->type);
        }
        if($this->id)
        {
            $where->notEqualTo('id',$this->id);
        }
        return $this->getOne($where);
    }

    /**
     * @return int
     * 新增管理员
     */
    public function addData()
    {
        if($this->password)
        {
            $this->password = md5($this->password);
        }
        $data = $this->queryName();//查询登录是否存在
        if($data)
        {
            return false;
        }
        return parent::addData();
    }

}