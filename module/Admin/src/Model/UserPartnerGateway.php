<?php
namespace Admin\Model;
/**
* 第三方登录／授权表
*
* @author 系统生成
*
*/
class UserPartnerGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;
    
    /**
    *第三方登录唯一标识
    */
    public $openId;
    
    /**
    *微信第三方等录唯一标识
    */
    public $unionId;
    
    /**
    *昵称
    */
    public $nickname;
    
    /**
    *用户头像URL
    */
    public $imageUrl;
    
    /**
    *授权类型： 1 QQ；2微信
    */
    public $partner;
    
    /**
    *性别：0保密 1男 2女
    */
    public $sex;
    
    /**
    *
    */
    public $userId;
    
    /**
    *头像id
    */
    public $imageId;
    
    /**
    *字段数组
    */
    protected $columns_array = ["id","openId","unionId","nickname","imageUrl","partner","sex","userId","imageId","delete","timestamp"];

    public $table = DB_PREFIX . 'UserPartner';
}