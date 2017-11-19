<?php
namespace Admin\Model;
/**
* 评论
*
* @author 系统生成
*
*/
class CommentGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *音频/微博id
    */
    public $fromId;

    /**
    *用户的id
    */
    public $userId;

    /**
    *评论内容
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
    *显示状态：1现实，2隐藏
    */
    public $display;

    /**
    *上级
    */
    public $parentId;

    /**
    *评论所属1 音频 2 视频 3微博
    */
    public $type;

    /**
    *字段数组
    */
    protected $columns_array = ["id","fromId","userId","content","praiseNum","commentNum","display","parentId","type","delete","timestamp"];

    public $table = DB_PREFIX . 'comment';
}