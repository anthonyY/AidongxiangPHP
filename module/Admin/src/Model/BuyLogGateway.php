<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* 购买记录
*
* @author 系统生成
*
*/
class BuyLogGateway extends BaseGateway {
    /**
    *主键、自动增长。
    */
    public $id;

    /**
    *金额
    */
    public $cash;

    /**
    *状态：1待支付 2支付成功；3支付失败；
    */
    public $status;

    /**
    *音频id
    */
    public $audioId;

    /**
    *用户id
    */
    public $userId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","cash","status","audioId","userId","delete","timestamp"];

    public $table = DB_PREFIX . 'buy_log';

    /**
     * @return array|\ArrayObject|bool|null
     * 查询用户是否已购买某音频
     */
    public function checkUserBuy()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('status',2)->equalTo('user_id',$this->userId)->equalTo('audio_id',$this->audioId);
        return $this->getOne($where,['id']);
    }

}