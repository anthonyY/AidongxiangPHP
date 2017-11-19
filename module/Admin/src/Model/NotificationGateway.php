<?php
namespace Admin\Model;
/**
* 通知推送表
*
* @author 系统生成
*
*/
class NotificationGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *推送标题
    */
    public $title;

    /**
    *图文
    */
    public $content;

    /**
    *自定义连接
    */
    public $link;

    /**
    *图片ID
    */
    public $image;

    /**
    *发送对象 1所有用户；
    */
    public $sendTo;

    /**
    *1图文推送；2自定义连接；
    */
    public $type;

    /**
    *1待推送，2已推送
    */
    public $status;

    /**
    *发送人姓名
    */
    public $adminName;

    /**
    *推送时间
    */
    public $pushTime;

    /**
    *字段数组
    */
    protected $columns_array = ["id","title","content","link","image","sendTo","type","status","adminName","pushTime","delete","timestamp"];

    public $table = DB_PREFIX . 'notification';
}