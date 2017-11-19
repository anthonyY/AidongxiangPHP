<?php
namespace Admin\Model;
/**
* 广告
*
* @author 系统生成
*
*/
class AdsGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *生效时间
    */
    public $startTime;

    /**
    *下架时间
    */
    public $endTime;

    /**
    *是否下架 1 正常 2 已下架
    */
    public $status;

    /**
    *封面
    */
    public $imageId;

    /**
    *1视频2 音频 3  图文4外部链接
    */
    public $type;

    /**
    *type= 1|2,需要存储
    */
    public $audioId;

    /**
    *type= 3|4 需存储
    */
    public $content;

    /**
    *广告0位，1首页 2视频首页 3音频首页
    */
    public $position;

    /**
    *字段数组
    */
    protected $columns_array = ["id","startTime","endTime","status","imageId","type","audioId","content","position","delete","timestamp"];

    public $table = DB_PREFIX . 'ads';

}