<?php
namespace Admin\Model;
/**
* 收藏记录
*
* @author 系统生成
*
*/
class FavoriteGateway extends BaseGateway {
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
    *1 音频 2 视频
    */
    public $type;

    /**
    *字段数组
    */
    protected $columns_array = ["id","userId","audioId","type","delete","timestamp"];

    public $table = DB_PREFIX . 'favorite';

}