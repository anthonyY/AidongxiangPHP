<?php
namespace Admin\Model;
/**
* 举报
*
* @author 系统生成
*
*/
class ReportGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *举报补充内容
    */
    public $content;

    /**
    *用户ID
    */
    public $userId;

    /**
    *评论/微博ID
    */
    public $fromId;

    /**
    *1 微博  2 评论
    */
    public $type;

    /**
    *举报分类ID
    */
    public $categoryId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","content","userId","fromId","type","categoryId","delete","timestamp"];

    public $table = DB_PREFIX . 'report';

}