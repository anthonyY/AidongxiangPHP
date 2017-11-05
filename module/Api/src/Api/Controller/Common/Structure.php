<?php
namespace Api\Controller\Common;

/**
 * 接收协议的结构
 * @author WZ
 *
 */
class Structure extends Item
{
    /**
     * 命名空间
     * @var String
     */
    public $namespace;

    /**
     * session_id
     * @var String
     */
    public $session_id;

    /**
     * 移动端缓存时间
     * @var date
     */
    public $timestampLeast;
    
    /**
     * query
     */
    public $query;
    
    function __construct()
    {
        parent::__construct();
        $key = array(
            'namespace' => 'n',
            'session_id' => 's',
            'timestampLeast' => 't',
            'query' => 'q'
        );
        $this->setOptions('key', $key);
    }
}