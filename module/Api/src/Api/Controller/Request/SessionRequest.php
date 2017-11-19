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

    /**
     * 系统语言，一次插入不会更新
     *
     * @var string
     */
    public $lang=0;

    /**
     * 机型型号，一次插入不会更新
     *
     * @var string
     */
    public $model;

    /**
     * 协议版本号，每次请求，每次更新，可能会使用版本号做判断，影响协议返回的内容
     *
     * @var string
     */
    public $version;

    /**
     * 屏幕分辨率，一次插入不会更新
     *
     * @var string
     */
    public $resolution;

    /**
     * 屏幕大小，一次插入不会更新
     *
     * @var string
     */
    public $screenSize;

    /**
     * 设备号，重要。
     *
     * @var string
     */
    public $deviceToken;

    /**
     * 设备类型，重要。
     *
     * @var number
     */
    public $deviceType;

    /**
     * 备注，设备信息字符串等
     *
     * @var string
     */
    public $info='';
}