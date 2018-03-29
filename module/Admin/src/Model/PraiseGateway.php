<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* 点赞
*
* @author 系统生成
*
*/
class PraiseGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *外键id，根据type
    */
    public $fromId;

    /**
    *1音频，2评论，3微博,
    */
    public $type;

    /**
    *用户的id
    */
    public $userId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","fromId","type","userId","delete","timestamp"];

    public $table = DB_PREFIX . 'praise';

    /**
     * @param $user_id
     * @param $type //a:1音频点赞，2评论点赞，3微博点赞
     * @param $id
     * @param $open //1点赞；2取消点赞；
     * @return array
     * 协议：点赞/取消点赞
     */
    public function praiseSwitch($user_id,$type,$id,$open)
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
        switch ($type)
        {
            case 1:
                $model = new AudioGateway($this->adapter);
                break;
            case 2:
                $model = new CommentGateway($this->adapter);
                break;
            case 3:
                $model = new MicroblogGateway($this->adapter);
                break;
        }
        $details = $model->getOne(['id'=>$id,'delete'=>DELETE_FALSE],['id','praise_num']);
        if(!$details)
        {
            return ['s'=>STATUS_NODATA];
        }

        $where = array('type'=>$type,'user_id'=>$user_id,'from_id'=>$id);
        $res = $this->getOne($where,array('id'));
        if($open== 1)//点赞
        {
            if($res)
            {
                $this->update(array('delete'=>0),array('id'=>$res->id));
            }
            else
            {
                $data = [
                    'type' => $type,
                    'from_id' => $id,
                    'user_id' => $user_id,
                ];
                $this->insert($data);
            }
            $model->update(['praise_num'=>$details->praise_num+1],['id'=>$id]);
        }
        else//取消点赞
        {
            if($res)
            {
                $this->update(array('delete'=>1),array('id'=>$res->id));
                $model->update(['praise_num'=>($details->praise_num-1)>0?($details->praise_num-1):0],['id'=>$id]);
            }
        }
        $this->adapter->getDriver()->getConnection()->commit();
        return ['s'=>STATUS_SUCCESS];
    }

    /**
     * @return bool
     * 查询用户是否对音频或评论或微博点赞
     */
    public function checkUserPraise()
    {
        $praise = false;
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('type',$this->type)->equalTo('user_id',$this->userId)->equalTo('from_id',$this->fromId);
        $res = $this->getOne($where,['id']);
        if($res)$praise=true;
        return $praise;
    }

}