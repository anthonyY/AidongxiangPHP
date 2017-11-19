<?php
namespace Api\Controller\Common;

/**
 * 地址类
 * @author WZ
 *
 */
class Structure
{
    /**
     * 命名空间
     * @var String
     */
    public $namespace = 'n';

    /**
     * session_id
     * @var String
     */
    public $session_id = 's';

    /**
     * 移动端缓存时间
     * @var date
     */
    public $timestampLeast = 't';

    /**
     * query
     * @var BaseQuery
     */
    public $query = 'q';
}