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
class ShareTimesUpdateRequest extends Request
{
    /**
     * 分享链接地址
     */
    public $url;

}