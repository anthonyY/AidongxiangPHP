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
class AudioWhereRequest extends WhereRequest
{
    /**
     * @var 分类id
     */
    public $categoryId;

    /**
     * @var 类型 1视频 2音频
     */
    public $audioType;

}