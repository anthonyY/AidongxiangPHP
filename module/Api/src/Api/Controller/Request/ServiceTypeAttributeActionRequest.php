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
class ServiceTypeAttributeActionRequest extends Request
{

    /**
     * @var 服务类型数组ids
     */
    public $ids;


}