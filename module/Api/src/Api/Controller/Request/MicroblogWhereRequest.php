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
class MicroblogWhereRequest extends WhereRequest
{
    /**
     * @var 用户id
     */
    public $userId;

}