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
class BankBranchSubmitRequest extends Request
{
    /**
     * 银行名称
     */
    public $bankName;

    /**
     * 银行账号
     */
    public $account;

    /**
     * 持卡人姓名
     */
    public $name;

    /**
     * 银行预留的手机号
     */
    public $mobile;

    /**
     * 短信验证表id
     */
    public $smscodeId;

}