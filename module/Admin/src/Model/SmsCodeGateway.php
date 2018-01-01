<?php
namespace Admin\Model;
/**
* 短信验证码
*
* @author 系统生成
*
*/
class SmsCodeGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *移动电话
    */
    public $mobile;

    /**
    *短信验证码（随机生成6位数字）
    */
    public $code;

    /**
    *ip地址
    */
    public $ip;

    /**
    *网页session或者移动端session
    */
    public $sessionId;

    /**
    *验证状态：0未验证；1已验证 2发送失败
    */
    public $status;

    /**
    *类型：1注册；2重新绑定手机；
    */
    public $type;

    /**
    *失效时间。默认+10分钟
    */
    public $expire;

    /**
    *短信下发次数统计
    */
    public $count;

    /**
    *会员id
    */
    public $userId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","mobile","code","ip","session","status","type","expire","count","userId","delete","timestamp"];

    public $table = DB_PREFIX . 'sms_code';
}