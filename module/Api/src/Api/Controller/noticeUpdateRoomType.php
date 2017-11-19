<?php
namespace Api\Controller;

/**
 * 更新房型信息
 *
 *
 */
class noticeUpdateRoomType extends roomBase
{
    public $tokenId = 'noticeUpdateRoomType';

    /**
     * 房型ID
     */
    public $id;

    /**
     * 更新类型
     * 1 更新
     * 2 新增
     * 3 删除
     */
    public $action;

    /**
     * 房型名称
     * Action=1 或 2时传
     */
    public $name;

    /**
     * 房型描述
     * Action=1 或 2时传，可为空字符串
     */
    public $desc;


    public $bodyRequestArray = ['id','action','name','desc'];

    /**
     * java->php
     */
    public function index()
    {
        if(!in_array($this->action,[1,2,3]) || !$this->id)
        {
            $this->respCode = 300;
            return $this->roomReturn();
        }
        $RoomTypeTable = $this->getRoomTypeTable();
        $RoomTypeTable->uuid = $this->id;
        $RoomTypeTable->name = $this->name;
        $RoomTypeTable->description = $this->desc;
        $RoomTypeTable->action = $this->action;
        $RoomTypeTable->merchantId = $this->merchantId;
        $res = $RoomTypeTable->updateRoomType();
        $this->respCode = $res['s'];
        $this->respMsg = $res['d'];
        return $this->roomReturn();
    }

}