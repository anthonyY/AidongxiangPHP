<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* 中国省市区地名表
*
* @author 系统生成
*
*/
class RegionGateway extends BaseGateway {
    /**
    *主键，区域编码
    */
    public $id;

    /**
    *父id
    */
    public $parentId;

    /**
    *名称
    */
    public $name;

    /**
    *区域名称拼音
    */
    public $pinyin;

    /**
    *层：1省；2市；4区；8街道；
    */
    public $deep;

    /**
    *字段数组
    */
    protected $columns_array = ["id","parentId","name","pinyin","deep"];

    public $table = DB_PREFIX . 'region';

    /**
     * api 获取省市区列表
     */
    public function getApiList()
    {
        $where = new Where();
        if($this->parentId)
        {
            $where->equalTo('parent_id',$this->parentId);
        }
        return $this->fetchAll($where);
    }

}