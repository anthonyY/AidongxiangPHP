<?php
namespace Api\Controller\Common;

/**
 * 基础查询条件
 *
 * @author WZ
 *
 */
class WhereRequest
{

    /**
     * 一个或多个id，用逗号分隔
     *
     * @var String
     */
    public $ids;

    /**
     * 查询的关键字
     *
     * @var String
     */
    public $search_key = 'sk';
}