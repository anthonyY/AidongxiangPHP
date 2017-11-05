<?php
namespace Api\Controller\Common;

/**
 * 基础分页对象属性，getRequest方法配置默认值
 *
 * @author WZ
 *        
 */
class TableRequest extends Item
{

    /**
     * page 当前第几页，默认是1
     *
     * @var Number
     */
    public $page = 'pa';

    /**
     * limit 每页多少条 默认是10
     *
     * @var Number
     */
    public $limit = 'li';

    /**
     * 根据不同协议有不同排序类型，默认是1
     *
     * @var Number
     */
    public $order_by = 'ob';

    /**
     * 1是倒序Desc，2是正序Asc
     *
     * @var Number
     */
    public $order_type = 'ot';

    /**
     * 列表查询的时候需要用到的查询条件，要看QueryWhere类
     */
    public $where;

    function __construct()
    {
        parent::__construct();
        $key = array(
            'page' => 'pa',
            'limit' => 'li',
            'order_by' => 'ob',
            'order_type' => 'ot',
            'where' => 'w'
        );
        $this->setOptions('key', $key);
        $default = array(
            'page' => '1',
            'limit' => '10',
            'order_by' => '1',
            'order_type' => '1'
        );
        $this->setOptions('default', $default);
        $this->where = new WhereRequest();
    }
}