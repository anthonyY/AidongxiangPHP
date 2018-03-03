<?php
namespace Admin\Model;
/**
* VIEW
*
* @author 系统生成
*
*/
class ViewMobileAppealGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *新手机号码
    */
    public $newMobile;

    /**
    *注册时间
    */
    public $registerTime;

    /**
    *状态：1 待审核2 审核通过 3审核失败
    */
    public $status;

    /**
    *用户id
    */
    public $userId;

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
    *字段数组
    */
    protected $columns_array = ["id","newMobile","registerTime","status","userId","delete","timestamp","nickName","realName","mobile"];

    public $table = 'view_mobile_appeal';

    public function getList()
    {
        $this->orderBy = ['status' => 'ASC','id'=>'DESC'];
        $where['delete'] = 0;
        if($this->status){
            $where['status'] = $this->status;
        }

        return $this->getAll($where);
    }
}