<?php
namespace Api\Controller\Common;

/**
 * 基础查询条件
 *
 * @author WZ
 *        
 */
class WhereRequest extends Item
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
    public $search_key;
    
    public $region_id;
    
    public $category_id;

    function __construct()
    {
        parent::__construct();
        $key = array(
            'search_key' => 'sk',
            'region_id' => 'regionId',
            'category_id' => 'categoryId',
        );
        $this->setOptions('key', $key);
        $functions = array(
            'search_key' => array(
                'key' => 'findSensitiveWord',
                'true' => STATUS_SENSITIVE_WORD
            )
        );
        $this->setOptions('functions', $functions);
    }
}