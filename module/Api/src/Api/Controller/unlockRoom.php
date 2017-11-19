<?php
namespace Api\Controller;

/**
 * 解锁房号接口（商城下单时，锁定房号。 酒店端在一定时间内（默认10分钟）保留锁定状态）
 */
class unlockRoom extends roomBase
{
    public $tradeId = 'unlockRoom';

    /**
     * 房型类型(根据每个数据格式定义不同（如ktxhotel_s1, motel_s1, heyuan_s3）)
     * @var
     */
    public $roomId;

    /**
     * 订房日期
     * @var
     */
    public $date;

    /**
     * 锁定房号返回的序列号
     * @var
     */
    public $bookSerialNum;


    public $bodyRequestArray = ['roomId','date','bookSerialNum'];


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