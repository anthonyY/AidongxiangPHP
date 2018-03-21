<?php
namespace Admin\Model;
/**
* VIEW
*
* @author 系统生成
*
*/
class ViewNavigationGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *导航名称
    */
    public $name;

    /**
    *导航链接
    */
    public $link;

    /**
    *导航图标
    */
    public $icon;

    /**
    *模块分类：1首页
    */
    public $type;

    /**
    *排序（倒序，1排在2后面）
    */
    public $sort;

    /**
    *目录，由日期组成（如：20130520/）
    */
    public $path;

    /**
    *文件名，时分秒毫秒+用户id（如：13145200010000.png）
    */
    public $filename;

    /**
    *字段数组
    */
    protected $columns_array = ["id","name","link","icon","type","sort","delete","timestamp","path","filename"];

    public $table = 'view_navigation';

    /**
     * @return array
     * 导航列表
     */
    public function getList()
    {
        $this->orderBy = 'sort DESC';
        $where['delete'] = 0;
        if($this->type){
            $where['type'] = $this->type;
        }

        return $this->getAll($where);
    }

    public function getApiList()
    {
        $this->orderBy = 'sort DESC';
        $where['delete'] = 0;
        if($this->type){
            $where['type'] = $this->type;
        }

        return $this->fetchAll($where);
    }

}