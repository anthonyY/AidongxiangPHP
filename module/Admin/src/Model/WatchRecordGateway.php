<?php
namespace Admin\Model;
/**
* 观看记录
*
* @author 系统生成
*
*/
class WatchRecordGateway extends BaseGateway {
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
    *上次观看时间
    */
    public $time;

    /**
    *字段数组
    */
    protected $columns_array = ["id","userId","audioId","time","delete","timestamp"];

    public $table = DB_PREFIX . 'watch_record';

}