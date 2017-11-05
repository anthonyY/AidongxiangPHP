<?php
namespace Admin\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Api\Controller\CommonController;
use Api\Controller\Item\PushTemplateItem;
/**
 * Model公共方法类
 *
 */
class PublicTable extends AbstractTableGateway
{

    /**
     * 是否打开缓存
     *
     * @var unknown
     */
    public $open_cache = TRUE;

    /**
     * 获取所有数据
     * 
     * @param $where array
     *            or obj 条件
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    public function fetchAll($where = null, $order = array('id desc'),$limit = null,$columns=null)
    {
        $select = new Select($this->table);
        if($columns)
        {
            $select->columns($columns);
        }
        $select->where($where);
        if($order)
        {
         $select->order($order);
        }
        if($limit)
        {
         $select->limit($limit);
        }
//         echo $select->getSqlString();
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
     * @return obj
     */
    public function getData($where = null, $columns = null, $order_by = array('id'=>'DESC'), $need_page = false, $search_key = '',$join = '')
    {
        $select = new Select();
        $select->from($this->table);
        
        if ($where instanceof Where)
        {
            $select->where->addPredicate($where);
        }
        else
        {
            $select->where($where);
        }
        
        if ($search_key)
        {
            $sub_where = new Where();
            foreach ($search_key as $key => $value)
            {
                //$pattern = "/[`!~!@#$%^&*(),.\/<>?;\':\"{}|\[\]_+=\\\-]*/";
                //$value = preg_replace($pattern, '', $value); 
                $sub_where_1 = new Where();
                $sub_where_1->like($key, '%' . $value . '%');
                $sub_where->orPredicate($sub_where_1);
            }
            $select->where->andPredicate($sub_where);
        }
        
        if ($columns)
        {
            $select->columns($columns);
        }
        
        if ($order_by)
        {
            $select->order($order_by);
        }

        if($join){
            $select->join($join['table'],$join['on'],$join['columns']);
        }

//        echo $select->getSqlString();
        if ($need_page)
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
     * @param array $join 联表查询
     * @return array
     */
    public function getAll($where = null, $columns = null, $order_by = null, $need_page = null, $page = 1, $limit = 0, $search_key = '',$join = '')
    {
        $result = $this->getData($where, $columns, $order_by, $need_page, $search_key,$join);
        if (! $need_page)
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
    public function getDataByIn($where = null, $columns = null, $order_by = null, $need_page = null, $search_key = null)
    {
        $resultSet = $this->getData($where, $columns, $order_by, $need_page, $search_key);
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
     * @param number $id            
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
    public function updateKey($id, $income, $key, $value)
    {
        if (1 == $income && 0 < $value)
        {
            $this->update(array(
                $key => new Expression("$key + $value")
            ), array(
                'id' => $id
            ));
        }
        elseif (2 == $income && 0 < $value)
        {
            $this->update(array(
                $key => new Expression("$key - $value")
            ), array(
                'id' => $id
            ));
        }
    }

    /**
     * 获取一条记录的部分数据
     */
    public function getOne($where, $part = array('*'),$order=array('id desc'))
    {
        $select = new Select();
        $select->from($this->table);
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
        return $row;
    }

    /**
     * 插入
     */
    public function insertData($data)
    {
        $this->insert($data);
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
    public function updateData($set, $where)
    {
        if(!$where)
        {
            return false;
        }
        $result = $this->update($set, $where);
        $this->clearCache();
        return $result;
    }

    /**
     * 假删除
     */
    public function deleteData($id)
    {
        $this->clearCache(); // 清除缓存
        return $this->update(array(
            'delete' => 1
        ), array(
            'id' => $id
        ));
    }

    /**
     * 返回最后一条数据
     *
     * @return boolean Ambigous ArrayObject, NULL, \ArrayObject, unknown>
     */
    public function getLastOne()
    {
        $select = new Select();
        $select->from($this->table);
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
     *
     * @param string $sql            
     * @param number $page            
     * @param number $limit            
     * @return array total,list
     */
    public function executeSql($sql, $page, $limit)
    {
        // echo $sql.$order;exit;
        $stmt = $this->adapter->createStatement($sql);
        $stmt->prepare();
        $result = $stmt->execute();
        
        $resultset = new ResultSet();
        $resultset->initialize($result);
        
        $total = $resultset->count(); // 获得总数
        
        $list = array();
        $key = 0;
        
        while ($item = $resultset->current())
        {
            if ($key < ($page - 1) * $limit)
            {
                $key ++;
                continue;
            }
            if ($key >= $page * $limit)
            {
                break;
            }
            $list[] = $item;
            $key ++;
        }
        
        $result = array();
        $result['total'] = $total;
        $result['list'] = $list;
        return $result;
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
            $param = json_decode($data);
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
    public function getDataByCache($time = '0000-00-00 00:00:00', $filename = null, $where = null, $order = array('id desc'))
    {
        if (false == $this->open_cache)
        {
          
            // 没有打开缓存就返回查询结果
            return $data = $this->fetchAll($where, $order);
        }
        
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
            $data = $this->fetchAll($where, $order);
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
        if (true == $this->open_cache)
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
    }
    
    /**
     * 插入订单跟踪记录
     * 
     * @param array $set   
     *          
     * @param int $status
     *            
     * @param float $order_sn
     *            
     * @return void
     * @version 1.0.141020 WZ
     */
    public function indateKey(array $set, $status,$order_info)
    {
        $content = '';
        $template = new PushTemplateItem();
        $template = array();
        switch ($status)
        {
            
            case '2':
                $customer_title = '车队已接单';
                $customer_content = '您购买的'.$order_info['info'].'水泥车队已接单，请保持电话畅通';//客户端推送信息
                $motorcade_title = '抢单成功';
                $motorcade_content = '您已接到'.$order_info['info'].'的订单，请联系客户确认订单详情';//业务端端推送信息
                break;
            case '3':
                $customer_title = '订单已确认';
                $customer_content = '您购买的'.$order_info['info'].'水泥车队已确认订单，车队将尽快发货';
                $motorcade_title = '货物已确认';
                $motorcade_content = '订单'.$order_info['info'].'已确认，请尽快到工厂装车';
                break;
            case '4':
                $customer_title = '订单已发货';
                $customer_content = '您购买的'.$order_info['info'].'水泥已发货，请保持电话畅通';
                $motorcade_title = '货物已发货';
                $motorcade_content = '订单'.$order_info['info'].'已发货，请按约定时间送达货物';
                break;
            case '5':
                $customer_title = '订单已收货';
                $customer_content = '您购买的'.$order_info['info'].'水泥已确认收货。请对水泥及车队进行评价';
                $motorcade_title = '货物已送达';
                $motorcade_content = '订单'.$order_info['info'].'已送达，请对客户进行评价';
                break;
        }
        if ($order_info['user_id'])
        {//客户端
            $set['client_description'] = $customer_content;
            $set['user_id'] = $order_info['user_id'];
            $this->insert($set);
            $template['content'] = $customer_content;
            $template['title'] = $customer_title;
            $template['type'] = 3;
            $template['link_id'] = $set['order_id'];
            $common = new CommonController();
            $common->pushForController($order_info['user_id'], 1 ,null, array(), $template);
        }
        if (isset($order_info['motorcade_id']))
        {//业务端
            $set['business_description'] = $motorcade_content;
            $set['user_id'] = $order_info['motorcade_id'];
            $this->insert($set);
            $template['content'] = $motorcade_content;
            $template['title'] = $motorcade_title;
            $template['type'] = 3;
            $template['link_id'] = $set['order_id'];
            $common->pushForController($order_info['motorcade_id'], 2 ,null, array(), $template);
        }    
        return $content;
    }
    
    /**
     * @param $user_id 用户id
     * @param $type 消息类型 类型：1系统通知 2好友路过 3打招呼 4网红路过 5 心动 6点赞 7参加活动 8好友注册
     * @param $title 
     * @param $content
     * @param $link_id 网红id
     * @abstract 推送
     * @author linzhiwen
     * @version 2016-09-06
     */
    public function sendNotice($user_id,$type,$link_id=0,$image_id=0,$title,$content,$online_star=array())
    {
        //$template = new PushTemplateItem();
        $template = array();
        $template['content'] = $content;
        $template['title'] = $title;
        $template['type'] = $type;
        $template['link_id'] = $link_id;
        $template['image_id'] = $image_id;
        $template['push_args'] = $online_star;
        $common = new CommonController();
        $common->pushForController($user_id, 2, array(), $template);
    }
}
