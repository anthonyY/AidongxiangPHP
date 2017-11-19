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
class StaffSubmitRequest extends Request
{

    /**
     * 用户对象
     */
    public $staff;

    /**
     * 验证码ID
     * @var unknown
     */
    public $smscode_id = "smscodeId";

    function __construct()
    {
        parent::__construct();
        $this->staff = new StaffItem();
    }
}