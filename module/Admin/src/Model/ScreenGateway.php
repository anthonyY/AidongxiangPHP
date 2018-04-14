<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* 屏蔽
*
* @author 系统生成
*
*/
class ScreenGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *用户ID
    */
    public $userId;

    /**
    *评论/微博/用户ID
    */
    public $fromId;

    /**
    *1 微博  2 评论 3用户
    */
    public $type;

    /**
    *字段数组
    */
    protected $columns_array = ["id","userId","fromId","type","delete","timestamp"];

    public $table = DB_PREFIX . 'screen';

    /**
     * @param $user_id
     * @param $type //1用户屏蔽(用户所有微博) 2屏蔽微博
     * @param $id
     * @param $open //1屏蔽；2取消屏蔽；
     * @return array
     * 协议：点赞/取消点赞
     */
    public function screenSwitch($user_id,$type,$id,$open)
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
        switch ($type)
        {
            case 1:
                $model = new UserGateway($this->adapter);
                break;
            case 2:
                $model = new MicroblogGateway($this->adapter);
                break;
        }
        $details = $model->getOne(['id'=>$id,'delete'=>DELETE_FALSE],['id']);
        if(!$details)
        {
            return ['s'=>STATUS_NODATA];
        }

        $where = array('type'=>$type,'user_id'=>$user_id,'from_id'=>$id);
        $res = $this->getOne($where,array('id'));
        if($open== 1)//屏蔽
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
                    'timestamp' => $this->getTime()
                ];
                $this->insertData($data);
            }
        }
        else//取消屏蔽
        {
            if($res)
            {
                $this->update(array('delete'=>1),array('id'=>$res->id));
            }
        }
        $this->adapter->getDriver()->getConnection()->commit();
        return ['s'=>STATUS_SUCCESS];
    }

    public function getMicroblogList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('type',2)->equalTo('user_id',$this->userId);
        return $this->getAll($where);
    }

    public function getScreenUserList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('type',3)->equalTo('user_id',$this->userId);
        return $this->getAll($where);
    }
}