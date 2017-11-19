<?php
namespace Admin\Model;
/**
* 财务明细
*
* @author 系统生成
*
*/
class FinancialGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *1 购买课程 2充值
    */
    public $type;

    /**
    *金额
    */
    public $cash;

    /**
    *类型：1收入，2支出（相对于用户）
    */
    public $income;

    /**
    *交易流水号 ,如（140601） +（235001）+（10000）年月日+时分秒+五位随机数
    */
    public $transferNo;

    /**
    *交易方式：0无，1微信；2支付宝
    */
    public $paymentType;

    /**
    *备注
    */
    public $remark;

    /**
    *用户
    */
    public $userId;

    /**
    *购买记录ID
    */
    public $buyLogId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","type","cash","income","transferNo","paymentType","remark","userId","buyLogId","delete","timestamp"];

    public $table = DB_PREFIX . 'financial';
}