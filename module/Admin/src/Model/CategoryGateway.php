<?php
namespace Admin\Model;
/**
* 分类表
*
* @author 系统生成
*
*/
class CategoryGateway extends BaseGateway {
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
    *排序（升序，1排在2前面）
    */
    public $sort;

    /**
    *审核状态：1正常，2禁用
    */
    public $status;

    /**
    *父ID
    */
    public $parentId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","type","name","icon","sort","status","parentId","delete","timestamp"];

    public $table = DB_PREFIX . 'category';

}