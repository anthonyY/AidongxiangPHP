<?php
namespace Admin\Model;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

/**
* 关注关系
*
* @author 系统生成
*
*/
class FocusRelationGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *用户id
    */
    public $userId;

    /**
    *被关注的用户id
    */
    public $targetUserId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","userId","targetUserId","delete","timestamp"];

    public $table = DB_PREFIX . 'focus_relation';

    public function getFansNum()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('target_user_id',$this->targetUserId);
        $res = $this->getOne($where,[new Expression('SUM(1) as total')]);
        return $res['total']?$res['total']:0;
    }

    public function getFocusNum()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('user_id',$this->userId);
        $res = $this->getOne($where,[new Expression('SUM(1) as total')]);
        return $res['total']?$res['total']:0;
    }

    /**
     * 关注关系，1未关注 2已关注，3被关注，4互粉
     */
    public function userFocusRelation()
    {
        $relation = 1;
        $res1 = $this->getOne(['delete'=>DELETE_FALSE,'user_id'=>$this->userId,'target_user_id'=>$this->targetUserId],['id']);
        $res2 = $this->getOne(['delete'=>DELETE_FALSE,'user_id'=>$this->targetUserId,'target_user_id'=>$this->userId],['id']);
        if($res1 && !$res2)
        {
            $relation = 2;
        }
        elseif($res2 && !$res1)
        {
            $relation = 3;
        }
        elseif($res2 && $res1)
        {
            $relation = 4;
        }
        return $relation;
    }

}