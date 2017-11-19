<?php
namespace Api\Controller\Request;

use Api\Controller\Common\WhereRequest;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 * @author WZ
 */
class EvaluateListWhereRequest extends WhereRequest
{

    /**
     * @var 1全部评论，2好评，3中评，4差评，5有图片的评论
     */
    public $type;

    /**
     * @var 1已评价的，2未评价的 a=1
     */
    public $status;

}