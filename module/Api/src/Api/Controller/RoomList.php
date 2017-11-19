<?php
namespace Api\Controller;
use Api\Controller\Request\RoomListWhereRequest;

/**
 * 房间列表
 */
class RoomList extends CommonController
{
    public function __construct()
    {
        $this->myWhereRequest = new RoomListWhereRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $total = 0;
        $list = array();
        $id = $request->id ? $request->id : 0;//1服务产品ID(uuid)
        $table_where = $this->getTableWhere();
        $presaleTime = $table_where->presaleTime;//订购日期 格式年月日yyyymmdd
        if(!$id || !$presaleTime || strlen($presaleTime) != 10)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $ViewGoodsTable = $this->getViewGoodsTable();
        $ViewGoodsTable->uuid = $id;
        $goods_details = $ViewGoodsTable->getDetails();
        if(!$goods_details)
        {
            return STATUS_NODATA;
        }

        /*if(!$goods_details->room_type_id)
        {
            return STATUS_NODATA;
        }

        $RoomTypeTable = $this->getRoomTypeTable();
        $RoomTypeTable->id = $goods_details->room_type_id;
        $room_type_details = $RoomTypeTable->getDetails();
        if(!$room_type_details || !$room_type_details->uuid)
        {
            return STATUS_NODATA;
        }*/

        /**
         * 返回模拟数据↓↓↓
         */
        for ($i=1;$i<=10;$i++) {
            $item = [
                'id' => $i,
                'name' => '测试房间100'.$i,
                'imagePath' => '20170829/180923_9190.jpg',
                'description' => "测试房间100".$i."描述",
                'timestamp' => date('Y-m-d',strtotime($presaleTime)),
            ];
            $list[] = $item;
            $total++;
        }
        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->rooms = $list;
        return $response;
        /**
         * 返回模拟数据↑↑↑↑
         */


        $RoomList = new queryRoomList();
        $RoomList->roomType = $room_type_details->uuid;
        $RoomList->date = str_replace('-','',$presaleTime);
        $result = $RoomList->submit();
        $RespCode = $RoomList->getRespCode();
        if($RespCode['respCode'] == 0)
        {
            if($result->list && is_array($result->list))
            {
                foreach ($result->list as $val) {
                    $item = [
                        'id' => $val['id'],
                        'name' => $val['name'],
                        'imagePath' => $val['image'],
                        'description' => $val['desc'],
                        'timestamp' => date('Y-m-d',strtotime($presaleTime)),
                    ];
                    $list[] = $item;
                }
                $total = $result->totalNum;
            }
            $response->status = STATUS_SUCCESS;
            $response->total = $total;
            $response->rooms = $list;
            return $response;
        }
        else
        {
            $response->status = $RespCode['respCode'];
            $response->description = $RespCode['respMsg'];
            $response->total = $total;
            return $response;
        }
    }
}
