<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\DiscountCalculationItem;

/**
 *
 * @author WZ
 *
 */
class DiscountCalculationRequest extends Request
{
    /**
     * 商家对像
     * @var
     */
    public $merchant;

    public function __construct()
    {
        $this->merchant = new DiscountCalculationItem();
        parent::__construct();
    }
}