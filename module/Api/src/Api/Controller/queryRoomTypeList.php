<?php
namespace Api\Controller;

/**
 * 	查询房型列表接口
 */
class queryRoomTypeList extends roomBase
{
    public $tradeId = 'queryRoomTypeList';

    /**
     * 总房型数
     * @var
     */
    public $totalNum;

    /**
     * 房型列表
     * @var
     */
    public $list;

    public $bodyReturnArray = ['totalNum','list'];

    /**
     * java->php
     */
    public function index()
    {
        $this->respCode = 0;
        $this->roomReturn();
    }

    /**
     * php->java
     */
    public function submit()
    {
        $this->roomRequest();
        return $this;
    }

}