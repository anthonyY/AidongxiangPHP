<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\StaffItem;

/**
 * 定义接收类的属性
 *
 * @author WZ
 *
 */
class SecretCollectionSubmitRequest extends Request
{

    /**
     * 密保问题集合
     */
    public $answers;
}