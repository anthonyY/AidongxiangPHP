<?php
namespace Api\Model;

class SessionModel extends CommonModel{
    protected $table = 'login';
    
    public function saveDeviceUser($data) {
        $table = $this->table;
        $this->table = 'device_user';
        $result = $this->insertData($data);
        $this->table = $table;
        return $result;
    }
}
