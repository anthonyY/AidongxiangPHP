<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewAdsGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
     *广告名稱
     */
    public $name;

    /**
    *广告位，1首页 2视频首页 3音频首页
    */
    public $position;

    /**
    *1视频2 音频 3 图文4外部链接
    */
    public $type;

    /**
    *广告图文内容
    */
    public $content;

    /**
    *广告开始时间
    */
    public $startTime;

    /**
    *广告结束时间
    */
    public $endTime;

    /**
    *排序：越大越前
    */
    public $sort;

    /**
    *广告图片id
    */
    public $imageId;

    /**
    *type= 1|2,需要存储
    */
    public $audioId;

    /**
    *文件名，时分秒毫秒+用户id（如：13145200010000.png）
    */
    public $filename;

    /**
    *目录，由日期组成（如：20130520/）
    */
    public $path;

    /**
    *字段数组
    */
    protected $columns_array = ["id","name","position","type","content","startTime","endTime","sort","imageId","audioId","delete","timestamp","filename","path"];

    public $table = 'view_ads';

    /**
     * 后台 ：总管理后台廣告列表
     * @return array
     */
    public function getList()
    {
        $where = array('delete'=>DELETE_FALSE);
        if($this->position)
        {
            $where['position'] = $this->position;
        }
        $list = $this->getAll($where);
        return $list;
    }

    public function getApiList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('position',$this->position)->lessThan('start_time',date('Y-m-d H:i:s'))->greaterThan('end_time',date('Y-m-d H:i:s'));
        $this->orderBy = 'sort DESC';
        return $this->fetchAll($where);
    }
}