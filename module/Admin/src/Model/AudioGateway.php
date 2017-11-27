<?php
namespace Admin\Model;
/**
* 音频/视频
*
* @author 系统生成
*
*/
class AudioGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *类型1 视频 2 音频
    */
    public $type;

    /**
    *音频/视频名称
    */
    public $name;

    /**
    *1免费，2收费
    */
    public $payType;

    /**
    *普通成员价格
    */
    public $price;

    /**
    *会员专享价格
    */
    public $memberPrice;

    /**
    *音频课程介绍
    */
    public $description;

    /**
    *音频封面
    */
    public $imageId;

    /**
    *试听音频目录+文件名
    */
    public $auditionsPath;

    /**
    *完整音频目录+文件名
    */
    public $fullPath;

    /**
    *音频课程长度
    */
    public $audioLength;

    /**
    *音频试听长度
    */
    public $auditionsLength;

    /**
    *1 正常 2 已下架
    */
    public $status;

    /**
    *评论数
    */
    public $commentNum;

    /**
    *播放量
    */
    public $playNum;

    /**
    *点赞数
    */
    public $praiseNum;

    /**
    *分类ID
    */
    public $categoryId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","type","name","payType","price","memberPrice","description","imageId","auditionsPath","fullPath","audioLength","auditionsLength","status","commentNum","playNum","praiseNum","delete","categoryId","timestamp"];

    public $table = DB_PREFIX . 'audio';
}