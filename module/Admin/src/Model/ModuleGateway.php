<?php
namespace Admin\Model;
/**
* 模块表
*
* @author 系统生成
*
*/
class ModuleGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *类型：0默认；1平台后台；2自营后台 3加盟商家
    */
    public $type;

    /**
    *权限中文名
    */
    public $name;

    /**
    *0，表示控制器模块名
    */
    public $parentId;

    /**
    *模块里的功能英文名
    */
    public $actionCode;

    /**
    *字段数组
    */
    protected $columns_array = ["id","type","name","parentId","actionCode","delete","timestamp"];

    public $table = DB_PREFIX . 'module';

    public function getListTree(){
        $where['parent_id'] = 0;
        $where['delete'] = 0;
        $list = $this->fetchAll($where);
        foreach ($list['list'] as $k => $v){
            $where2['delete'] = 0;
            $where2['parent_id'] = $v->id;
            $childList = $this->fetchAll($where2);
            $v->children = $childList['list'];
        }
        return $list;
    }

}