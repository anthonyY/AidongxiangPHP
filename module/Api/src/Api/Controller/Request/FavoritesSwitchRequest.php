<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *
 */
class FavoritesSwitchRequest extends Request
{
    /**
     * 1收藏，2取消收藏
     * @var 1|2
     */
    public $open;

    public $userId;
}
