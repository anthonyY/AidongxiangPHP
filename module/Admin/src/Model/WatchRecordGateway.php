<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* 观看记录
*
* @author 系统生成
*
*/
class WatchRecordGateway extends BaseGateway {
    /**
    *主键、自动增长。
    */
    public $id;

    /**
    *用户id
    */
    public $userId;

    /**
    *音频id
    */
    public $audioId;

    /**
    *上次观看时间
    */
    public $time;

    /**
    *字段数组
    */
    protected $columns_array = ["id","userId","audioId","time","delete","timestamp"];

    public $table = DB_PREFIX . 'watch_record';

    public function deleteByIds($ids)
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('user_id',$this->userId)->in('id',$ids);
        $res = $this->update(array('delete' => 1), $where);
        return $res?['s'=>0,'d'=>'删除成功']:['s'=>10000,'d'=>'删除失败'];
    }

}