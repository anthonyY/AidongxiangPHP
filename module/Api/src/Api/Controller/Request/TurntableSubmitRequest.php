<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class TurntableSubmitRequest extends Request
{

    /**
     * 转盘
     *
     * @var turntable
     */
    public $turntable;


}