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
class DataAnalysisDetailsRequest extends Request
{
    /**
     *
     * @var $start_time 开始时间
     */
    public $start_time = "startTime";

    /**
     *
     * @var $end_time 结束时间
     */
    public $end_time = "endTime";

}