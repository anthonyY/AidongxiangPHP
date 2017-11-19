<?php
namespace Admin\Model;
/**
* 微博
*
* @author 系统生成
*
*/
class MicroblogGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *微博内容
    */
    public $content;

    /**
    *点赞总数
    */
    public $praiseNum;

    /**
    *回复总数
    */
    public $commentNum;

    /**
    *转发总数
    */
    public $repeatNum;

    /**
    *是否显示，1显示，2隐藏
    */
    public $display;

    /**
    *地区ID
    */
    public $regionId;

    /**
    *地区数组
    */
    public $regionInfo;

    /**
    *街道
    */
    public $street;

    /**
    *详细地址
    */
    public $address;

    /**
    *经度
    */
    public $longitude;

    /**
    *纬度
    */
    public $latitude;

    /**
    *父ID，转发用
    */
    public $parentId;

    /**
    *用户的id
    */
    public $userId;

    /**
    *小视频的id
    */
    public $videoId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","content","praiseNum","commentNum","repeatNum","display","regionId","regionInfo","street","address","longitude","latitude","parentId","userId","videoId","delete","timestamp"];

    public $table = DB_PREFIX . 'microblog';
}