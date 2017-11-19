<?php
namespace Api\Controller;

/**
 * 锁定房号接口（商城下单时，锁定房号。 酒店端在一定时间内（默认10分钟）保留锁定状态）
 */
class lockRoom extends roomBase
{
    public $tradeId = 'lockRoom';

    /**
     * 房间id
     * @var
     */
    public $roomId;

    /**
     * 订房日期
     * @var
     */
    public $date;

    /**
     * 房号
     * @var
     */
    public $roomName;

    /**
     * 预订锁定序列号(在解锁房号和选定房号时使用（最大12位）)
     * @var
     */
    public $bookSerialNum;

    public $bodyRequestArray = ['roomId','date'];

    public $bodyReturnArray = ['roomName','bookSerialNum'];

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