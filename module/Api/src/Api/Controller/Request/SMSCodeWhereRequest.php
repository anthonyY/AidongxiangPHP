<?php
namespace Api\Controller\Request;

use Api\Controller\Common\WhereRequest;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *
 */
class SMSCodeWhereRequest extends WhereRequest
{

    /**
     * 验证码
     *
     * @var String
     */
    public $code;

}