<?php
namespace Api\Controller;

/**
 * 取消选定房号接口
 */
class cancelBookRoom extends roomBase
{
    public $tradeId = 'cancelBookRoom';

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