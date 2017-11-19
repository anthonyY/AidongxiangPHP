<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * EventJoin接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class EventJoinRequest extends Request
{

    /**
     * 银猫券
     *
     * @var float
     */
    public $silver_cat = 'silverCat';

    /**
     * 通用券
     *
     * @var float
     */
    public $golden_cat = 'goldenCat';
}