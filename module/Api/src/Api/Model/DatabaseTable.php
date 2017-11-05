<?php
namespace Api\Model;


use Zend\Db\Adapter\Adapter;
class DatabaseTable {
    
    private static $adapter = null;
    
    private function __construct()
    {
        
    }
    
    private static function conn()
    {
        $driver = array(
            "driver" => "Pdo",
            "dsn" => "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST,
            "username" => DB_USER,
            "password" => DB_PASSWORD,
            "charset" => DB_CHARSET,
            "driver_options" => array(
                "1002" => "SET NAMES '" . DB_SET_NAME . "'"
            )
        );
     return new Adapter($driver);
    }
    
    public static function getConn()
    {
        if(self::$adapter == null){
             self::$adapter =  self::conn();
        }
        return self::$adapter;
    }

    private function __clone()
    {
        return false;
    }
}
