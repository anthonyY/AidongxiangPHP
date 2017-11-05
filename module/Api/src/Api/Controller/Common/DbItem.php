<?php
namespace Api\Controller\Common;

/**
 *
 * @author WZ
 *        
 */
class DbItem extends Item
{

    /**
     * 删除状态
     * 
     * @var 0|1
     */
    public $delete;

    /**
     *
     * @var date
     */
    public $timestamp_update;

    /**
     *
     * @var date
     */
    public $timestamp;

    function __construct()
    {
        parent::__construct();
        $key = array(
            'timestamp_update' => 'timestampUpdate'
        );
        $this->setOptions('key', $key);
    }
}