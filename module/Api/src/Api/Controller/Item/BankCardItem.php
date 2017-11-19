<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 * 地址基础类
 *
 * @author liujun
 *
 */
class BankCardItem extends Item
{

    /**
     * 户名
     * @var int
     */

    public $bank_acct_name = "bankAcctName";

    /**
     * 帐号
     *
     * @var  帐号
     */
    public $bank_acct_num = "bankAcctNum";

    /**
     * 支行ID
     *
     * @var 支行ID
     */
    public $branch_id = "branchId";
}