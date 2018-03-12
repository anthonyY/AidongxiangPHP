<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewMicroblogGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *微博内容
    */
    public $content;

    /**
    *点赞数
    */
    public $praiseNum;

    /**
    *评论数
    */
    public $commentNum;

    /**
    *转发数
    */
    public $repeatNum;

    /**
    *是否显示，1显示，2隐藏
    */
    public $display;

    /**
    *区域id
    */
    public $regionId;

    /**
    *
    */
    public $regionInfo;

    /**
    *
    */
    public $street;

    /**
    *
    */
    public $address;

    /**
    *经度
    */
    public $longitude;

    /**
    *维度
    */
    public $latitude;

    /**
    *父ID，转发用
    */
    public $parentId;

    /**
    *用户id
    */
    public $userId;

    /**
    *小视频id
    */
    public $videoId;

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
    *上一次登录时间
    */
    public $lastLoginTime;

    /**
    *用户头像ID
    */
    public $headImageId;

    /**
    *用户中心背景图片ID
    */
    public $backImageId;

    /**
    *1男，2女 3保密
    */
    public $sex;

    /**
    *余额
    */
    public $cash;

    /**
    *用户状态：1正常；2停用
    */
    public $status;

    /**
    *字段数组
    */
    protected $columns_array = ["id","content","praiseNum","commentNum","repeatNum","display","regionId","regionInfo","street","address","longitude","latitude","parentId","userId","videoId","delete","timestamp","nickName","realName","mobile","lastLoginTime","headImageId","backImageId","sex","cash","status"];

    public $table = 'view_microblog';

    public function getList($search_key=[])
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE);
        if($this->display)
        {
            $where->equalTo('display',$this->display);
        }
        return $this->getAll($where,$search_key);
    }

}