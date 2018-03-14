<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewFinancialGateway extends BaseGateway {
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
    *支付方式：0无，1微信；2支付宝
    */
    public $paymentType;

    /**
    *备注
    */
    public $remark;

    /**
    *用户id
    */
    public $userId;

    /**
    *购买记录id
    */
    public $buyLogId;

    /**
    *昵称
    */
    public $nickName;

    /**
    *真实姓名
    */
    public $realName;

    /**
    *移动电话（登录账号）
    */
    public $mobile;

    /**
    *上一次登录时间
    */
    public $lastLoginTime;

    /**
    *用户头像ID
    */
    public $headImageId;

    /**
    *音频id
    */
    public $audioId;

    /**
    *金额
    */
    public $buyLogCash;

    /**
    *状态：1待支付 2支付成功；3支付失败；
    */
    public $buyLogStatus;

    public $startTime;

    public $endTime;

    /**
    *字段数组
    */
    protected $columns_array = ["id","type","cash","income","transferNo","paymentType","remark","userId","buyLogId","delete","timestamp","nickName","realName","mobile","lastLoginTime","headImageId","audioId","buyLogCash","buyLogStatus"];

    public $table = 'view_financial';

    public function getList($search_key=[])
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE);
        if($this->type)
        {
            $where->equalTo('type',$this->type);
        }
        if($this->startTime)
        {
            $where->greaterThan('timestamp',$this->startTime." 00:00:01");
        }
        if($this->endTime)
        {
            $where->lessThan('timestamp',$this->startTime." 23:59:59");
        }
        return $this->getAll($where,$search_key);
    }

}