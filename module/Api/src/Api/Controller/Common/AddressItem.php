<?php
namespace Api\Controller\Common;

/**
 * 地址类
 * @author WZ
 *
 */
class AddressItem extends Item
{
    /**
     * 街道
     * @var String
     */
    public $street;
    
    /**
     * 联系人
     * @var unknown
     */
    public $name;

    /**
     * 地区编号
     * @var Number
     */
    public $region_id;

    /**
     * 地区列表json
     * @var String
     */
    public $region_info;
    
    public $longitude;
    
    public $latitude;
    
    public $telephone;
    
    public function __construct()
    {
        parent::__construct();
        $key = array(
            'region_id' => 'regionId',
            'region_info' => 'regionInfo'
        );
        $this->setOptions('key', $key);
        
        $functions = array(
            'street' => array(
                'key' => 'findSensitiveWord',
                'true' => STATUS_SENSITIVE_WORD
            ),
            'name' => array(
                'key' => 'findSensitiveWord',
                'true' => STATUS_SENSITIVE_WORD
            )
        );
        $this->setOptions('functions', $functions);
    }
}