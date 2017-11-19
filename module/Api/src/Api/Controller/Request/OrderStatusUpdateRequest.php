<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 *
 * @author WZ
 *
 */
class OrderStatusUpdateRequest extends Request
{

    /**
     * @var 类型
     */
    public $type;

    /**
     * @var 用户取消订单原因：1我不想买了2 信息填写错误，重新拍 3其它原因
     */
    public $cancelReasonType;

}