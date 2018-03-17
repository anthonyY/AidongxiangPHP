<?php
namespace AiiLibrary\AiiUtility\AiiPush;

/**
 * 推送接口
 *
 * @author WZ
 *        
 */
class AiiPushBase
{

    /**
     * 文件类
     */
    public $myfile;

    /**
     * 推送接口的id
     * @var unknown
     */
    public $_access_id;

    /**
     * 推送接口的key
     * @var unknown
     */
    public $_secret_key;

    /**
     * iOS的使用版本
     * 1 PROD ; 2 DEV
     * @var number
     */
    public $_iosenv;

    /**
     * 构造函数
     */
    function __construct()
    {
        $this->myfile = new AiiMyFile();
        $this->init();
    }
    
    /**
     * 设置参数，子类注意改写此方法
     */
    public function init()
    {
    }
}
?>