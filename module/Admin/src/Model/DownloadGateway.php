<?php
namespace Admin\Model;
/**
* 订阅的老师
*
* @author 系统生成
*
*/
class DownloadGateway extends BaseGateway {
    /**
    *主键、自动增长。
    */
    public $id;

    /**
    *用户id
    */
    public $userId;

    /**
    *音频id
    */
    public $audioId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","userId","audioId","delete","timestamp"];

    public $table = DB_PREFIX . 'download';

}