<?php
namespace Api\Controller\Common;

/**
 * 返回结果类
 * @author WZ
 *
 */
class Response
{
    /**
     * 返回状态码
     * @var Number
     */
    public $status = STATUS_SUCCESS;

    /**
     * 返回状态描述
     * @var string
     */
    public $description = null;

    /**
     * 返回时间
     * @var ????-??-?? ??:??:??
     */
    public $timestamp = null;

    /**
     * 返回列表数量
     * @var Number
     */
    public $total = null;

    /**
     * 返回对象id
     * @var Number
     */
    public $id = null;
}