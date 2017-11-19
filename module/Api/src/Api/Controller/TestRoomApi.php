<?php
namespace Api\Controller;

class TestRoomApi extends CommonController
{
    public function index()
    {
        $data = [
            'head' => [
                'tradeId' => 'lockRoom',
                'timestamp' => date('YmdHis'),
                'respCode' => '0',
                'respMsg' => '成功',
            ],
            'body' => [
                'roomName' => '503',
                'bookSerialNum' => '131444313134'
            ],
        ];
        die(json_encode($data));
    }
}
