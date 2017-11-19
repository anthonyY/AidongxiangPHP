<?php
namespace Api\Controller\Common;

/**
 * 公共Request对象配置
 *
 * @author WZ
 *
 */
class Request
{

    /**
     * 操作，一个协议可能有多个操作
     *
     * @var Number
     */
    public $action = 'a';

    /**
     * 列表查询的时候需要用到的分页信息，要看QueryTable类
     */
    public $table;

    /**
     * 各个协议有可能返回的id
     *
     * @var Number
     */
    public $id;

    /**
     * 各个协议有可能输入的正文；
     *
     * @var unknown
     */
    public $content;

    function __construct()
    {
        $this->table = new TableRequest();
    }
}