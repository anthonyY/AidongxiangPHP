<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

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

    /**
     * 管理员角色列表
     */
    public function getList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE);
        return $this->getAll($where);
    }

    /**
     * 添加管理员角色
     * @return int
     */
    public function addData()
    {
        $id = parent::addData();
        if(!$id){
            return false;
        }
        return $id;
    }

    /**
     * 更新管理员角色，通过ID更新
     * @return bool|int
     * @throws \Exception
     */
    public function updateData()
    {
        if(!parent::updateData()){
            return false;
        }
        return true;
    }

    /**
     * 删除角色，删除前会检查是否还关联管理员，是则抛出不能删除异常
     * @return int
     * @throws \Exception
     */
    public function deleteData()
    {
        if (!$this->id) {
            throw new \Exception('id不能为空');
        }
        $admin = new AdminGateway($this->adapter);
        $res = $admin->getOne(array('admin_category_id'=>$this->id,'delete'=>0),array('id'));
        if($res)
        {
            throw new \Exception('不能删除，该角色下还存在管理员');
        }
        return parent::deleteData();
    }

}