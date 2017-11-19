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
class BranchWhereRequest extends WhereRequest
{

    /**
     * @var 地区id
     */
    public $region_id = 'regionIdStr';

    /**
     * @var 银行ID
     */
    public $bank_id = 'bankId';

}