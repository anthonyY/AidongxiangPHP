<?php
namespace Api\Controller\Request;

use Api\Controller\Common\WhereRequest;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *
 */
class FinancialWhereRequest extends WhereRequest
{
    /**
     * @var 开始时间
     */
    public $start_time = 'startTime';

    /**
     * @var 结束时间
     */
    public $end_time = 'endTime';

    /**
     * @var 款项
     */
    public $payment = "0";

    /**
     * @var 业务
     */
    public $business = "0";
}