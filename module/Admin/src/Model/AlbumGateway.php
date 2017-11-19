<?php
namespace Admin\Model;
/**
* 相册
*
* @author 系统生成
*
*/
class AlbumGateway extends BaseGateway {
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
    *字段数组
    */
    protected $columns_array = ["id","type","imageId","fromId","delete","timestamp"];

    public $table = DB_PREFIX . 'album';
}