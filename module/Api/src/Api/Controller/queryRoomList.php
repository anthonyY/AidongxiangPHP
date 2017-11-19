<?php
namespace Api\Controller;

/**
 * 查询房间列表（通过房型）接口
 */
class queryRoomList extends roomBase
{
    public $tradeId = 'queryRoomList';

    /**
     * 房型类型(根据每个数据格式定义不同（如ktxhotel_s1, motel_s1, heyuan_s3）)
     * @var
     */
    public $roomType;

    /**
     * 订房日期
     * @var
     */
    public $date;

    /**
     * 总房间数
     * @var
     */
    public $totalNum;

    /**
     * 房型列表
     * @var
     */
    public $list;

    public $bodyRequestArray = ['roomType','date'];

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
        foreach ($this->bodyRequestArray as $v) {
            if(!$this->$v)
            {
                return false;
            }
        }
        $this->roomRequest();
        return $this;
    }

}