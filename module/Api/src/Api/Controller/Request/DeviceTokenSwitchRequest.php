<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * DeviceTokenSwitch定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class DeviceTokenSwitchRequest extends Request
{

    /**
     * 设备号，根据设备号查找表，执行开关操作
     *
     * @var String
     */
    public $device_token = 'deviceToken';

    /**
     * 1开启，2关闭；
     *
     * @var Number
     */
    public $open;

    /**
     * 响铃,振动,免打扰
     *
     * @var object
     */
    public $style;

    /**
     * 类型
     *
     * @var object
     */
    public $type;
}