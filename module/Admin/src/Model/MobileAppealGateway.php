<?php
namespace Admin\Model;
/**
* 手机申诉
*
* @author 系统生成
*
*/
class MobileAppealGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *新手机号码
    */
    public $newMobile;

    /**
    *注册日期
    */
    public $registerTime;

    /**
    *发送状态 1 待审核2 审核通过 3审核失败
    */
    public $status;

    /**
    *用户id
    */
    public $userId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","newMobile","registerTime","status","userId","delete","timestamp"];

    public $table = DB_PREFIX . 'mobile_appeal';

}