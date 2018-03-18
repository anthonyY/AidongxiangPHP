<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* 收藏记录
*
* @author 系统生成
*
*/
class FavoriteGateway extends BaseGateway {
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
    *1 音频 2 视频
    */
    public $type;

    /**
    *字段数组
    */
    protected $columns_array = ["id","userId","audioId","type","delete","timestamp"];

    public $table = DB_PREFIX . 'favorite';

    public function deleteByIds($ids)
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('user_id',$this->userId)->in('id',$ids);
        $res = $this->update(array('delete' => 1), $where);
        return $res?['s'=>0,'d'=>'删除成功']:['s'=>10000,'d'=>'删除失败'];
    }

}