<?php
namespace Api\Controller\Common;

/**
 *
 * @author WZ
 *
 */
class Item
{
    /**
     * 返回对象id
     * @var Number
     */
    public $id;

    /**
     * 删除状态
     * @var 0|1
     */
    public $delete;

    /**
     *
     * @var date
     */
    public $timestamp_update = 'timestampUpdate';

    /**
     *
     * @var date
     */
    public $timestamp;
}