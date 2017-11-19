<?php
namespace Api\Controller;

/**
 * 商户登录接口
 */
class merchantLogin extends roomBase
{
    /**
     *
     * @var string
     */
    public $tradeId = 'merchantLogin';
    /**
     * 商户Key
     * @var
     */
    public $merchantKey;

    public $tokenId;

    public $bodyRequestArray = ['merchantKey'];

    public $bodyReturnArray = ['tokenId'];

    public function index()
    {
        $this->respCode = 0;
        $this->roomReturn();
    }

    public function submit()
    {
        return $this->roomRequest();
    }
}