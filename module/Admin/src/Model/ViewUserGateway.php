<?php
namespace Admin\Model;
/**
* VIEW
*
* @author 系统生成
*
*/
class ViewUserGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

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
    *余额
    */
    public $cash;

    /**
    *积分
    */
    public $points;

    /**
    *1男，2女 3保密
    */
    public $sex;

    /**
    *密码，md5加密
    */
    public $password;

    /**
    *用户状态：1正常；2停用
    */
    public $status;

    /**
    *上一次登录时间
    */
    public $lastLoginTime;

    /**
    *个性签名
    */
    public $description;

    /**
    *地区id
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
    *用户头像ID
    */
    public $headImageId;

    /**
    *用户中心背景图片ID
    */
    public $backImageId;

    /**
    *文件名，时分秒毫秒+用户id（如：13145200010000.png）
    */
    public $headFilename;

    /**
    *目录，由日期组成（如：20130520/）
    */
    public $headPath;

    /**
    *文件名，时分秒毫秒+用户id（如：13145200010000.png）
    */
    public $backFilename;

    /**
    *目录，由日期组成（如：20130520/）
    */
    public $backPath;

    /**
    *字段数组
    */
    protected $columns_array = ["id","nickName","realName","mobile","cash","points","sex","password","status","lastLoginTime","description","regionId","regionInfo","street","address","longitude","latitude","headImageId","backImageId","delete","timestamp","headFilename","headPath","backFilename","backPath"];

    public $table = 'view_user';

    /**
     * 后台 ：总管理后台用户列表
     * @param integer $search_key 0不搜索 1手机号 2昵称 要搜索的字段
     * @return array
     */
    public function getList($search_key = 0)
    {
        $where = array();
        if($this->status)
        {
            $where['status'] = $this->status;
        }
        $search_columns = array(0 => '', '1' => 'mobile', 2 => 'nick_name');
        $search_key = $search_key ? array($search_columns[$search_key]) : array('mobile', 'nick_name');
        $list = $this->getAll($where, $search_key);
        return $list;
    }
}