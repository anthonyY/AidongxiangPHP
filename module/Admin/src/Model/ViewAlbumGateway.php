<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewAlbumGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *类型：1微博
    */
    public $type;

    /**
    *图片ID
    */
    public $imageId;

    /**
    *外健ID 根据type类型写入不同表的外键id
    */
    public $fromId;

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
    protected $columns_array = ["id","type","imageId","fromId","delete","timestamp","filename","path"];

    public $table = 'view_album';

    public function getList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('type',$this->type)->equalTo('from_id',$this->fromId);
        return $this->fetchAll($where);
    }

}