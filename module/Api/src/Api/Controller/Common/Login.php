<?php
namespace Api\Controller\Common;

/**
 * 基础用户类
 * 
 * @author WZ
 *        
 */
class Login
{

    /**
     * 用户Id
     * 
     * @var string
     */
    public $user_id = '0';

    /**
     * 用户名
     * 
     * @var number
     */
    public $user_name = '';

    /**
     * 状态
     * 
     * @var string
     */
    public $status = '0';

    /**
     * 设备类型
     *
     * @var number
     */
    public $device_type = '0';
}