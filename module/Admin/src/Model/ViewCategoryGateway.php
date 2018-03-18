<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewCategoryGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *分类类型：1视频分类，2音频分类，3资讯分类 4举报分类
    */
    public $type;

    /**
    *分类名
    */
    public $name;

    /**
    *分类对应的icon_id(图片表ID)
    */
    public $icon;

    /**
    *排序（倒序，1排在2后面）
    */
    public $sort;

    /**
    *审核状态：1正常，2禁用
    */
    public $status;

    /**
    *父id
    */
    public $parentId;

    /**
    *文件名，时分秒毫秒+用户id（如：13145200010000.png）
    */
    public $filename;

    /**
    *目录，由日期组成（如：20130520/）
    */
    public $path;

    /**
    *字段数组
    */
    protected $columns_array = ["id","type","name","icon","sort","status","parentId","delete","timestamp","filename","path"];

    public $table = 'view_category';

    public function getList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE);
        if($this->type)
        {
            $where->equalTo('type',$this->type);
        }
        if($this->status)
        {
            $where->equalTo('status',$this->status);
        }
        return $this->fetchAll($where,'',['name']);
    }

    public function getApiList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('type',$this->type)->equalTo('status',$this->status);
        return $this->fetchAll($where,'',['id','name','path','filename']);
    }

}