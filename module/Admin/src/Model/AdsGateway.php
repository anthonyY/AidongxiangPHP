<?php
namespace Admin\Model;
/**
* 广告
*
* @author 系统生成
*
*/
class AdsGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
     *广告名稱
     */
    public $name;

    /**
    *生效时间
    */
    public $startTime;

    /**
    *下架时间
    */
    public $endTime;

    /**
     *排序：越大越前
     */
    public $sort;

    /**
    *封面
    */
    public $imageId;

    /**
    *1视频2 音频 3  图文4外部链接
    */
    public $type;

    /**
    *type= 1|2,需要存储
    */
    public $audioId;

    /**
    *type= 3|4 需存储
    */
    public $content;

    /**
    *广告0位，1首页 2视频首页 3音频首页
    */
    public $position;

    /**
    *字段数组
    */
    protected $columns_array = ["id","name","startTime","endTime","sort","imageId","type","audioId","content","position","delete","timestamp"];

    public $table = DB_PREFIX . 'ads';

    public function deleteData()
    {
        return parent::deleteData(); // TODO: Change the autogenerated stub
    }

    /**
     * @param $sort_array
     * @return array
     * @throws \Exception
     * 保存廣告排序
     */
    public function saveSort($sort_array)
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
        if(!is_array($sort_array) || !$sort_array)
        {
            return ['s'=>10000,'d'=>'操作失敗'];
        }
        foreach ($sort_array as $id=>$sort) {
            $this->id = $id;
            $this->sort = $sort;
            $res = $this->updateData();
            if($res === false)
            {
                $this->adapter->getDriver()->getConnection()->rollback();
                return ['s'=>10000,'d'=>'操作失敗'];
            }
        }
        $this->adapter->getDriver()->getConnection()->commit();
        return ['s'=>0,'d'=>'操作成功'];
    }

    public function addData()
    {
        return parent::addData(); // TODO: Change the autogenerated stub
    }

    public function updateData()
    {
        return parent::updateData(); // TODO: Change the autogenerated stub
    }
}