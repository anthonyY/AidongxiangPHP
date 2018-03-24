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

    /**
     * @param $open 1收藏；2取消收藏；
     * @return bool
     * 协议：收藏/取消收藏
     */
    public function favoritesSwitch($open)
    {
        $audio = new AudioGateway($this->adapter);
        $details = $audio->getOne(['id'=>$this->audioId,'type'=>$this->type,'delete'=>DELETE_FALSE],['id']);
        if(!$details)
        {
            return STATUS_NODATA;
        }

        $where = array('type'=>$this->type,'user_id'=>$this->userId,'audio_id'=>$this->audioId);
        $res = $this->getOne($where,array('id'));
        if($open== 1)//收藏
        {
            if($res)
            {
                $this->update(array('delete'=>0),array('id'=>$res->id));
            }
            else
            {
                $this->addData();
            }
        }
        else//取消收藏
        {
            if($res)
            {
                $this->update(array('delete'=>1),array('id'=>$res->id));
            }
        }
        return STATUS_SUCCESS;
    }

    /**
     * @return array|\ArrayObject|bool|null
     * 查询用户是否已收藏某音频
     */
    public function checkUserFavorite()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('user_id',$this->userId)->equalTo('audio_id',$this->audioId);
        return $this->getOne($where,['id']);
    }

}