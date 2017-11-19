<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\PushItem;

/**
 *
 * @author WZ
 *
 */
class PushSubmitRequest extends Request
{

    /**
     * @var 推送对像
     */
    public $push;

    public function __construct()
    {
        $this->push = new PushItem();
    }


}