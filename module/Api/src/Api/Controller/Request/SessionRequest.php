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
class SessionRequest extends Request
{
    public $model;
    
    public $version;
    
    public $resolution;
    
    public $device_token;
    
    public $device_type;
    
    public $info;
    
    public $environment;

    function __construct()
    {
        parent::__construct();
        $key = array(
            'device_token' => 'deviceToken',
            'device_type' => 'deviceType'
        );
        $this->setOptions('key', $key);
    }
}