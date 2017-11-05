<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class SMSCodeRequest extends Request
{

    /**
     * 类型，1.注册，2.绑定手机，3.重置密码
     *
     * @var Number
     */
    public $type;

    /**
     * 手机号码
     *
     * @var String
     */
    public $mobile;

    /**
     * where
     */
    public $where;
    
    public $user_id;
    
    function __construct()
    {
        parent::__construct();
        $key = array(
            'where' => 'w',
            'user_id' => 'userId'
        );
        $this->setOptions('key', $key);
        $this->where = new SMSCodeWhereRequest();
    }
}