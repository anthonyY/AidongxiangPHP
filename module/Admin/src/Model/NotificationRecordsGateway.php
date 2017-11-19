<?php
namespace Admin\Model;
/**
* 通知推送记录（用户）表
*
* @author 系统生成
*
*/
class NotificationRecordsGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *推送标题
    */
    public $title;

    /**
    *内容
    */
    public $content;

    /**
    *消息类型 1系统消息
    */
    public $type;

    /**
    *用户类型 1用户，2 商家
    */
    public $userType;

    /**
    *1未读，2已读，用户已读状态
    */
    public $status;

    /**
    *用户ID
    */
    public $userId;

    /**
    *外键表ID
    */
    public $fromId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","title","content","type","userType","status","userId","fromId","delete","timestamp"];

    public $table = DB_PREFIX . 'notification_records';
}