<?php
namespace Admin\Model;
/**
* 系统设置表
*
* @author 系统生成
*
*/
class SetupGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *设置值
    */
    public $value;

    /**
    *设置项名称。如：提现下限
    */
    public $text;

    /**
    *设置分类:1.SEO 2.搜索关键字设置3..分享领取优惠券
    */
    public $type;

    /**
    *数据类型 1 数字型 2字符串类型 3 浮点型
    */
    public $dataType;

    /**
    *字段数组
    */
    protected $columns_array = ["id","value","text","type","dataType","delete","timestamp"];

    public $table = DB_PREFIX . 'setup';

    /**
     * 根据ID更新系统设置
     * @return bool|int
     * @throws \Exception
     */
    public function updateData()
    {
        return parent::updateData();
    }

    public function getDataByInId()
    {
        return $this->getDataByIn(['id'=>[3]],['id','value','text']);
    }

}