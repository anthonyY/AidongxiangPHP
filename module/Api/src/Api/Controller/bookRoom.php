<?php
namespace Api\Controller;

/**
 * 选定房号接口
 */
class bookRoom extends roomBase
{
    public $tradeId = 'bookRoom';

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

    /**
     * 人员姓名
     * @var
     */
    public $personName;

    /**
     * 手机号码
     * @var
     */
    public $personMobileNo;

    /**
     * 房号
     * @var
     */
    public $roomName;

    public $bodyRequestArray = ['roomId','date','bookSerialNum','personName','personMobileNo'];

    public $bodyReturnArray = ['roomName'];


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