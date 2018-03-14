<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewCommentGateway extends BaseGateway {
    /**
    *
    */
    public $id;

    /**
    *音频/微博id
    */
    public $fromId;

    /**
    *用户id
    */
    public $userId;

    /**
    *评论内容
    */
    public $content;

    /**
    *点赞总数
    */
    public $praiseNum;

    /**
    *回复总数
    */
    public $commentNum;

    /**
    *显示状态：1显示 2隐藏
    */
    public $display;

    /**
    *上级
    */
    public $parentId;

    /**
    *评论所属：1 音频 2 视频 3微博
    */
    public $type;

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
    *1男，2女 3保密
    */
    public $sex;

    /**
    *上一次登录时间
    */
    public $lastLoginTime;

    /**
    *用户状态：1正常；2停用
    */
    public $userStatus;

    /**
    *用户头像ID
    */
    public $headImageId;

    /**
    *用户中心背景图片ID
    */
    public $backImageId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","fromId","userId","content","praiseNum","commentNum","display","parentId","type","delete","timestamp","nickName","realName","mobile","sex","lastLoginTime","userStatus","headImageId","backImageId"];

    public $table = 'view_comment';

    public function getList($search_key=[])
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE);
        if($this->display)
        {
            $where->equalTo('display',$this->display);
        }
        if($this->type)
        {
            $where->equalTo('type',$this->type);
        }
        return $this->getAll($where,$search_key);
    }

}