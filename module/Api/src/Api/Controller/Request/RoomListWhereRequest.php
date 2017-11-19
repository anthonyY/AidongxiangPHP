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
class RoomListWhereRequest extends WhereRequest
{
    /**
     * @var 订购日期 格式年月日
     */
    public $presaleTime;
}