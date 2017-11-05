<?php
namespace Api\Model;

use Zend\Mvc\Controller\AbstractActionController;
use Api\Model\PublicTable;
use Api\Model\DatabaseTable;
class Table extends AbstractActionController
{

    public $adapter;
    
    protected $Table = array();
    
    
    public function __construct()
    {
          $this->adapter = DatabaseTable::getConn();
    }
    
    /**
     * @param string $db 表名
     * @return PublicTable $db
     */
    public function getTable($db)
    {
        if (empty($this->Table[$db]))
        {
            $this->Table[$db] = new PublicTable($this->adapter, DB_PREFIX . $db);
        }
        return $this->Table[$db];
    }
    
    
}