<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewFocusRelationGateway extends BaseGateway {
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
    *昵称
    */
    public $nickName;

    /**
    *真实姓名
    */
    public $realName;

    /**
    *移动电话（登录账号）
    */
    public $mobile;

    /**
    *用户头像ID
    */
    public $headImageId;

    /**
    *昵称
    */
    public $beUserNickName;

    /**
    *真实姓名
    */
    public $beUserRealName;

    /**
    *移动电话（登录账号）
    */
    public $beUserMobile;

    /**
    *用户头像ID
    */
    public $beHeadImageId;

    /**
    *文件名，时分秒毫秒+用户id（如：13145200010000.png）
    */
    public $imageFilename;

    /**
    *目录，由日期组成（如：20130520/）
    */
    public $imagePath;

    /**
    *文件名，时分秒毫秒+用户id（如：13145200010000.png）
    */
    public $beUserImageFilename;

    /**
    *目录，由日期组成（如：20130520/）
    */
    public $beUserImagePath;

    public $userDescription;

    public $beUserDescription;

    /**
    *字段数组
    */
    protected $columns_array = ["id","userId","targetUserId","delete","timestamp","nickName","realName","mobile","headImageId","beUserNickName","beUserRealName","beUserMobile","beHeadImageId","imageFilename","imagePath","beUserImageFilename","beUserImagePath","userDescription","beUserDescription"];

    public $table = 'view_focus_relation';

    /**
     * @param $action //a：1我的关注 2我的粉丝
     * @param $user_id
     * @return array
     */
    public function getApiList($action,$user_id)
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE);
        if($action == 1)
        {
            $where->equalTo('user_id',$user_id);
        }
        elseif($action == 2)
        {
            $where->equalTo('target_user_id',$user_id);
        }
        return $this->getAll($where);
    }

}