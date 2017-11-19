<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * AdList定义接收类的属性
 *
 * @author WZ
 *
 */
class ExchangeScoreRequest extends Request
{

    /**
     * 用户积分
     *
     * @var String
     */
    public $points;
}