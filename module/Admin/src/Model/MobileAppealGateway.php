<?php
namespace Admin\Model;
/**
* 手机申诉
*
* @author 系统生成
*
*/
class MobileAppealGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *新手机号码
    */
    public $newMobile;

    /**
    *注册日期
    */
    public $registerTime;

    /**
    *发送状态 1 待审核2 审核通过 3审核失败
    */
    public $status;

    /**
    *用户id
    */
    public $userId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","newMobile","registerTime","status","userId","delete","timestamp"];

    public $table = DB_PREFIX . 'mobile_appeal';

    //手机申诉处理
    public function mobileAppeal($action)
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
        $info = $this->getDetails();
        if(!$info)
        {
            return ['s'=>10000,'d'=>'数据不存在'];
        }
        if($info->status != 1)
        {
            return ['s'=>10000,'d'=>'请求已处理'];
        }
        if($action == 'SUCCESS')
        {
            $this->update(['status'=>2],['id'=>$this->id]);//更改状态
            $user = new UserGateway($this->adapter);
            $user->update(['mobile'=>$info->new_mobile],['id'=>$info->user_id]);//更改手机
            //用户退出登录
        }
        elseif($action == 'FAIL')
        {
            $this->update(['status'=>3],['id'=>$this->id]);
        }

        $this->adapter->getDriver()->getConnection()->commit();
        return ['s'=>0,'d'=>'操作成功'];
    }

}