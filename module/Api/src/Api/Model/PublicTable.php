<?php
namespace Api\Model;

use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Expression;

/**
 * Model公共方法类
 *
 */
class PublicTable extends AbstractTableGateway
{
    const SELECT = 'select';
    const QUANTIFIER = 'quantifier';
    const COLUMNS = 'columns';
    const TABLE = 'table';
    const JOINS = 'joins';
    const WHERE = 'where';
    const GROUP = 'group';
    const HAVING = 'having';
    const ORDER = 'order';
    const LIMIT = 'limit';
    const OFFSET = 'offset';
    const QUANTIFIER_DISTINCT = 'DISTINCT';
    const QUANTIFIER_ALL = 'ALL';
    const JOIN_INNER = 'inner';
    const JOIN_OUTER = 'outer';
    const JOIN_LEFT = 'left';
    const JOIN_RIGHT = 'right';
    const SQL_STAR = '*';
    const ORDER_ASCENDING = 'ASC';
    const ORDER_DESCENDING = 'DESC';

    /**
     * 获取所有数据
     * 
     * @param $where array
     *            or obj 条件
     *
     * @param $data array("columns" => array(), "order" => array(), "limit" => array());
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    public function fetchAll( $where = null, $data = array(), $table = null)
    {
        if ($table) {
            $this->table = DB_PREFIX . $table;
        }
        if (strpos($this->table, DB_PREFIX) === false) {
            $this->table = DB_PREFIX . $this->table;
        }
        $select = new Select($this->table);
        if(!empty($data["columns"]))
        {
            $select->columns($data["columns"]);
        }
        $select->where($where);
        
        if(!empty($data["join"]))
        {
            foreach($data["join"] as $vo) {
                if (!empty($vo["name"]) && !empty($vo["on"])) {
                    $columns = empty($vo["columns"]) ? self::SQL_STAR :$vo["columns"];
                    $type = empty($vo["type"]) ? self::JOIN_INNER :$vo["type"];
                    $select->join($vo["name"], $vo["on"], $columns, $type);
                }
            }
        }
        
        $data["order"] = empty($data["order"]) ? array('id desc') : $data["order"];
        $select->order($data["order"]);
        
        if(!empty($data["group"]))
        {
            $select->group($data["group"]);
        }
        
        if(!empty($data["limit"]))
        {
            $select->limit($data["limit"]);
        }
//         echo $select->getSqlString();die;
        $resultSet = $this->executeSelect($select);
        $result = array();
        while ($r = $resultSet->current())
        {
            $result[] = $r;
        }
        
        return $result;
    }

    /**
     * 查询多条数据
     * 
     * @param array $where
     *            查询条件
     * @param array $columns
     *            要查询的字段
     * @param array $order_by
     *            排序字段
     * @param bool $need_page
     *            是否分页 1 or ture 是 0 or false 否，默认不分页
     * @param array $search_key
     *            关键字查询
     *
     * @param $data array("columns" => array(), "order" => array(), "need_page" => 1, "search_key" => array(), "join" => array(array("name", "on", "columns", type)), "group", "limit");
     * @return obj
     */
    public function getData($where = null, $data = array(), $table = null)
    {
        if ($table) {
            $this->table = DB_PREFIX . $table;
        }
        if (strpos($this->table, DB_PREFIX) === false) {
            $this->table = DB_PREFIX . $this->table;
        }
        $select = new Select($this->table);
        
        if ($where instanceof Where)
        {
            $select->where->addPredicate($where);
        }
        else
        {
            $select->where($where);
        }

        
        if (!empty($data['search_key']))
        {
            $sub_where = new Where();
            foreach ($data['search_key'] as $key => $value)
            {
                //$pattern = "/[`!~!@#$%^&*(),.\/<>?;\':\"{}|\[\]_+=\\\-]*/";
                //$value = preg_replace($pattern, '', $value); 
                $sub_where_1 = new Where();
                $sub_where_1->like($key, '%' . $value . '%');
                $sub_where->orPredicate($sub_where_1);
            }
            $select->where->andPredicate($sub_where);
        }

        if(!empty($data["join"]))
        {
            foreach($data["join"] as $vo) {
                if (!empty($vo["name"]) && !empty($vo["on"])) {
                    $columns = empty($vo["columns"]) ? self::SQL_STAR :$vo["columns"];
                    $type = empty($vo["type"]) ? self::JOIN_INNER :$vo["type"];
                    $select->join($vo["name"], $vo["on"], $columns, $type);
                }
            }
        }

        if(!empty($data["limit"]))
        {
            $select->limit($data["limit"]);
        }
        if(!empty($data["group"]))
        {
            $select->group($data["group"]);
        }
        if(!empty($data["columns"]))
        {
            $select->columns($data["columns"]);
        }
// var_dump($data["order"]);exit;//
        $data["order"] = empty($data["order"]) ? array($this->table.'.id desc') : $data["order"];
        $select->order($data["order"]);
//         echo $select->getSqlString();die;///
        if (!empty($data["need_page"]))
        {
            $adapterOrSqlObject = $this->getSql()->getAdapter();
            $adapter = new DbSelect($select, $adapterOrSqlObject);
            return $adapter;
        }
        else
        {
            $resultSet = $this->executeSelect($select);
            return $resultSet;
        }
    }

    /**
     * 获取列表数据(包括分页和不分页数据)
     * 
     * @param array $where   查询条件
     * @param array $columns  要查询的字段
     * @param array $order_by  排序字段
     * @param bool $need_page  是否分页 1 or ture 是 0 or false 否，默认分页
     * @param number $page 页码
     * @param number $limit  每页条数
     * @param array $search_key 关键字查询
     * @return array
     */

    
    public function getAll($where = null, $data = array(), $page = 1, $limit = 0, $table = null)
    {
        $result = $this->getData($where, $data, $table);
        if (empty($data['need_page']))
        {
            $total = $result->count();
            $list = array();
            if ($total > 0)
            {
                while ($row = $result->current())
                {
                    $list[] = $row;
                }
            }
            return array(
                'total' => $total,
                'list' => $list
            );
        }
        else
        {
            // 分页数据
            $limit = $limit == 0 ? PAGE_NUMBER : $limit;
            $list = $this->adapterToPager($result, $page, $limit);
            return array(
                'total' => $list['total'],
                'list' => $list['list'],
                'paginator' => $list['paginator']
            ); // PC专用，用于传入模板
        }
    }

    /**
     * 获取以列ID做下标的多条记录
     * 
     * @param array $where
     *            查询条件
     * @param array $columns
     *            要查询的字段
     * @param array $order_by
     *            排序字段
     * @param bool $need_page
     *            是否分页 1 or ture 是 0 or false 否，默认不分页
     * @param array $in_array
     *            in条件查询
     * @param array $like_array
     *            关键字查询
     * @return array $list
     */
    public function getDataByIn($where = null, $data = array(), $table = null)
    {
        $resultSet = $this->getData($where, $data, $table);
        $list = array();
        if ($resultSet->count() > 0)
        {
            foreach ($resultSet as $key => $value)
            {
                $list[$value['id']] = (array) $value;
            }
        }
        
        return $list;
    }

    /**
     * 查询对象转化成列表返回
     *
     * @param Adapter $adapter            
     * @param Number $page
     *            当前分页
     * @param Number $limit
     *            每页条数
     * @return array(total , list => object) 分页结果
     */
    public function adapterToPager($adapter, $page, $limit)
    {
        $paginator = new Paginator($adapter); // 实列化分页类
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($limit);
        $paginator->setPageRange(8);
        
        $total = $paginator->getTotalItemCount() . "";
        $list = array();
        if ($total > ($page - 1) * $limit)
        {
            foreach ($paginator as $key => $v)
            {
                $list[] = $v;
            }
        }
        
        return array(
            'total' => $total,
            'list' => $list,
            'paginator' => $paginator
        ); // PC专用，用于传入模板
    }

    /**
     * 更新字段数据
     *
     * @param number $id | $where
     * @param number $income
     *            1增加|2减少
     * @param string $key
     *            字段
     * @param float $value
     *            改变量
     * @return void
     * @version 1.0.141017 WZ
     * @author WZ
     */
    public function updateKey($id, $income, $key, $value, $table = null)
    {
        if (is_numeric($id)) {
            $where = array('id' => $id);
        }
        else {
            $where = $id;
        }
        $row = 0;
        if (1 == $income && 0 < $value)
        {
            $row = $this->updateData(array(
                $key => new Expression("$key + $value")
            ), $where, $table);
        }
        elseif (2 == $income && 0 < $value)
        {
            $row = $this->updateData(array(
                $key => new Expression("$key - $value")
            ), $where, $table);
        }
        return $row;
    }

    /**
     * 获取一条记录的部分数据
     */
    public function getOne($where, $part = array('*'), $table = null,  $order=array('id desc'))
    {

        if ($table) {
            $this->table = DB_PREFIX . $table;
        }
        if (strpos($this->table, DB_PREFIX) === false) {
            $this->table = DB_PREFIX . $this->table;
        }
        $select = new Select($this->table);
        
        if (! $part) {
            $part = array('*');
        }
        $select->columns($part);

        if ($where)
        {
            $select->where($where);
        }

        $select->order($order);

        $rowset = $this->executeSelect($select);
        
        $row = $rowset->current();
        if (! $row)
        {
            return false;
        }
        //echo $select->getSqlString();
        return $row;
    }

    /**
     * 插入
     */
    public function insertData($data, $table = null, $hasTimestamp = true)
    {
        if ($table) {
            $this->table = DB_PREFIX . $table;
        }
        if (strpos($this->table, DB_PREFIX) === false) {
            $this->table = DB_PREFIX . $this->table;
        }
        if ($hasTimestamp) {
            $data['timestamp'] = date('Y-m-d H:i:s');
        }
        $insert = new Insert($this->table);
        $insert->values($data);
        $this->executeInsert($insert);
//        $this->insert($data);
        $this->clearCache(); // 清除缓存
        return $this->getLastInsertValue();
    }

    /**
     * 更新
     * 
     * @param array $set            
     * @param array $where            
     * @return Ambigous <number, \Zend\Db\TableGateway\mixed>
     */
    public function updateData($set, $where, $table = null)
    {
        if(!$where)
        {
            return false;
        }
        if ($table) {
            $this->table = DB_PREFIX . $table;
        }
        if (strpos($this->table, DB_PREFIX) === false) {
            $this->table = DB_PREFIX . $this->table;
        }
        $update =  new Update($this->table);
        $update->set($set);
        if ($where !== null) {
            $update->where($where);
        }

        $result = $this->executeUpdate($update);
//         echo $update->getSqlString();//die;//
        $this->clearCache();
        return $result;
    }

    /**
     * 假删除
     */
//     public function deleteData($id, $table = null)
//     {
//         $this->clearCache(); // 清除缓存
//         return $this->updateData(array(
//             'delete' => 1
//         ), array(
//             'id' => $id
//         ), $table);
//     }
    
    public function deleteData($id, $table = null, $true = false)
    {
        $this->clearCache(); // 清除缓存
    
        if (is_int($id) || is_string($id)) {
            $where = array(
                'id' => $id
            );
        } else {
            $where = $id;
        }
        if ($table) {
            $this->table = DB_PREFIX . $table;
        }
        if (strpos($this->table, DB_PREFIX) === false) {
            $this->table = DB_PREFIX . $this->table;
        }
 
        if (! $true) {
            if ($id)
                return $this->updateData(array(
                    'delete' => 1
                ), $where, $this->table);
        }else {
            $delete = new Delete($this->table);
            $delete->where($where);
            return $this->executeDelete($delete);
        }
    }

    /**
     * 返回最后一条数据
     *
     * @return boolean Ambigous ArrayObject, NULL, \ArrayObject, unknown>
     */
    public function getLastOne($table = null)
    {
        if ($table) {
            $this->table = DB_PREFIX . $table;
        }
        if (strpos($this->table, DB_PREFIX) === false) {
            $this->table = DB_PREFIX . $this->table;
        }
        $select = new Select($this->table);
        $select->order(array(
            'id' => 'desc'
        ));
        $select->limit(1);
        $rowset = $this->executeSelect($select);
        $row = $rowset->current();
        if (! $row)
        {
            return false;
        }
        return $row;
    }


    /**
     * 获取统计条目
     * @param unknown $where
     * @return Ambigous <number, NULL>
     */
    public function countData($where=null,$table=null,$join=array(),$group=null)
    {
        if($table)
        {
            $this->table = DB_PREFIX . $table;
        }
        if (strpos($this->table, DB_PREFIX) === false) {
            $this->table = DB_PREFIX . $this->table;
        }
        $select = new Select();
        $select->from($this->table);
        if($join && is_array($join))
        {
            foreach($join as $vo) {
                if (!empty($vo["name"])) {
                    $columns = empty($vo["columns"]) ? self::SQL_STAR :$vo["columns"];
                    $type = empty($vo["type"]) ? self::JOIN_INNER :$vo["type"];
                    $select->join($vo["name"], $columns, $type);
                }
            }
        }
        if($group)
        {
            $select->group($group);
        }
        if($where) {
            $select->where($where);
        }
//         echo $select->getSqlString();
        $rowset = $this->executeSelect($select);
        return $rowset->count();
    }

    /**This use for execute one sql
     * @param $sql
     * @return array
     */
    public function executeSql($sql,$update="")
    {
        $stmt = $this->adapter->createStatement($sql);
        $stmt->prepare();
        $result = $stmt->execute();
        
        $resultset = new ResultSet();
        $resultset->initialize($result);
        if(!$update){
            $total = $resultset->count();
            $list = array();
            
            while ($item = $resultset->current())
            {
                $list[] = $item;
            }
            
            $result = array();
            $result['total'] = $total;
            $result['list'] = $list;
            return $result;
        }else{
            return $resultset;
        }
        
    }

    public function getImage($image)
    {
        $img = json_decode($image, true);
        return array('id' =>isset($img['id']) ? $img['id'] : '','path' => isset($img['path']) ? $img['path'] : '');
    }

    /**
     * 缓存写入文件
     *
     * @param unknown $filename
     *            文件名格式 region 或 Admin/category
     * @param unknown $param
     *            数组
     * @return boolean
     */
    public function setCache($filename, $param)
    {
        $filename = $this->getCacheFilename($filename);
        
        if ($param)
        {
            if (! is_file($filename))
            {
                $dir = substr($filename, 0, strrpos($filename, '/'));
                if (! is_dir($dir))
                {
                    @mkdir($dir, 0777);
                }
                @touch($filename);
                @chmod($filename, 0777);
            }
            
            if (! is_array($param))
            {
                $param = array(
                    $param
                );
            }
            $data = json_encode($param);
            @file_put_contents($filename, $data);
        }
        else
        {
            @unlink($filename);
        }
        return true;
    }

    /**
     * 获得缓存内容
     *
     * @param string $filename
     *            文件名格式 region 或 Admin/category
     * @return boolean mixed
     * @version 1.0.141020 WZ
     */
    public function getCache($filename)
    {
        $filename = $this->getCacheFilename($filename);
        if (! is_file($filename))
        {
            return false;
        }
        $data = file_get_contents($filename);
        if ($data)
        {
            $param = json_decode($data, true);
            return $param;
        }
        
        return false;
    }

    /**
     * 获取缓存更新时间
     *
     * @param string $filename            
     * @return boolean|number
     * @version 1.0.141020 WZ
     */
    public function getCacheTime($filename)
    {
        $filename = $this->getCacheFilename($filename);
        if (! is_file($filename))
        {
            return false;
        }
        return filemtime($filename);
    }

    /**
     * 简单文件名转化缓存文件路径
     *
     * @param string $filename            
     * @return string
     * @version 1.0.141020 WZ
     */
    public function getCacheFilename($filename)
    {
        return APP_PATH . '/Cache/' . $filename . '.php';
    }

    /**
     * 检查缓存是否可用，可用立即退出，不可用返回新缓存
     *
     * @param string $time
     *            移动端缓存时间
     * @param string $filename            
     * @param string $where            
     * @return Ambigous <boolean, \Api\Controller\mixed, mixed>
     */
    public function getDataByCache($time = '0000-00-00 00:00:00', $filename = null, $where = null,$table = null, $order = array('id desc'))
    {
        if (! $filename)
        {
            $filename = $this->table . '/' . 'all';
        }
        else
        {
            $filename = $this->table . '/' . $filename;
        }
        $ctime = $this->getCacheTime($filename); // 缓存更新时间
        if (! $ctime)
        {
            $data = $this->fetchAll($where, $order,'sms_code');
            $this->setCache($filename, $data);
            return $data;
        }
        if (strtotime($time) >= $ctime)
        {
            // 缓存时间大于文件生成时间就不用返回整个列表啦
            return STATUS_CACHE_AVAILABLE;
        }
        $data = $this->getCache($filename);
        return $data;
    }

    /**
     * 更新、插入的时候清除这个表的缓存
     *
     * @version 1.0.141020 WZ
     */
    public function clearCache()
    {
        // 打开了缓存才处理这些缓存文件
        $dir = APP_PATH . '/Cache/' . $this->table . '/';
        if (is_dir($dir))
        {
            if ($dh = opendir($dir))
            {
                while (($file = readdir($dh)) !== false)
                {
                    @unlink($dir . $file);
                }
                closedir($dh);
            }
        }
    }

    /*后台首页查询会员数*/
    public function dbSelectSql($sql,$update="")
    {
        $stmt = $this->adapter->createStatement($sql);
        $stmt->prepare();
        $result = $stmt->execute();
        $resultset = new ResultSet();
        $resultset->initialize($result);
        $item = $resultset->current();
        return $item->sum;
    }
}
