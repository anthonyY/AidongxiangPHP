<?php
    
    namespace Admin\Model;

    use Zend\Db\Adapter\Adapter;
    use Zend\Db\ResultSet\ResultSet;
    use Zend\Db\Sql\Expression;
    use Zend\Db\Sql\Select;
    use Zend\Db\Sql\Where;
    use Zend\Db\TableGateway\AbstractTableGateway;
    use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
    use Zend\Db\TableGateway\Feature;
    use Zend\Paginator\Adapter\DbSelect;
    use Zend\Paginator\Paginator;
                
    class BaseGateway extends AbstractTableGateway
    {
        public $uuid;

        /**
         * 排序字段
         * 示例 : 1排序一个字段: id desc
         *        2排序N个字段: id desc,timestamp asc
         * @var string
         */
        public $orderBy = 'id desc';

        /**
         * 每页显示多少条
         * @var int
         */
        public $limit = PAGE_NUMBER;

        /**
         * 搜索关键词
         * @var string
         */
        public $searchKeyWord;

        /**
         * 页码（列表默认第一页）
         * @var int
         */
        public $page = 1;

        /**
         * 数据表对像公共属性
         * 是否删除（此属性可用于查询更新数据） 0 未删除 1删除
         * @var int
         */
        public $delete = 0;

        /**
         * 数据表对像公共属性
         * 新增记录的插入时间 可不传，不传为当前时间
         * @var string
         */
        public $timestamp;

        /**
         * 要查询表的字段
         * @var
         */
        public $tableColumns = array();

        public $mapping = array();
        
        /**
         * @var ResultSetInterface
         */
//         protected $set = null;
        
        /**
         * 组包时,获取对象的默认属性.默认不包含properties自己.
         *
         * @var array
         */
        public $properties;
        
        /**
         * 组包时,设置对象的必须属性.
         *
         * @var array
         */
        public $jsonEncodeProperties;

        //private $parentProperty;

        public function __construct(adapter $adapter)
        {
            GlobalAdapterFeature::setStaticAdapter($adapter);
            $this->featureSet = new Feature\FeatureSet();
            $this->featureSet->addFeature(new Feature\GlobalAdapterFeature());
            $this->initialize();
            $this->resultSetPrototype = new ResultSet();
            //$this->parentProperty = array("adapter", "featureSet", "resultSetPrototype", "sql", "isInitialized", "columns", "lastInsertValue");
        }

        /**
         * 查询记录详细(所有表通用)
         * @return array|\ArrayObject|bool|null
         * @throws \Exception
         */
        public function getDetails()
        {
            if (!$this->id)
            {
                return false;
            }
            $where = array("id"=>$this->id);
            return $this->getOne($where);
        }

        /**
         * 查询一条记录
         * @param array $where
         * @param array $columns
         * @return array|\ArrayObject|bool|null
         */
        protected function getOne($where, $columns = array('*'))
        {
            $select = new Select();
            $select->from($this->table);
            $columns = $columns ? $columns : $this->tableColumns;
            $select->columns($columns);
            if ($where) {
                $select->where($where);
            }
            if ($this->orderBy) {
                $select->order($this->orderBy);
            }
            //echo $select->getSqlString();
            $rowset = $this->executeSelect($select);
            $row = $rowset->current();
            if (!$row) {
                return false;
            }
            return $row;
        }

        /**
         * 或取当前表对像所有字段值，可用于控制器批量给表对像属性赋值
         * @return array 表对像属性
         */
        public function getTableColumns()
        {
            return $this->columns_array;
        }

        /**
         * 设置要查询的字段名
         */
        public function setTableColumns(array $columns)
        {
            if(!$columns){
                throw new \Exception("字段数组不能为空");
            }
            $columns_array = array();
            foreach($columns as $v){
                if(!in_array($v, $this->getTableColumns())){
                    throw new \Exception('数据表不存在' . $v . "字段");
                }
                $columns_array[] = $this->convert($v);
            }
            $this->tableColumns = $columns_array;
            return $this->tableColumns;

        }

        /**
         * 大小字母转换小写字母加——方法
         * @param $var
         * @return string
         */
        protected function convert($var)
        {
            $formatStr = strtolower(preg_replace("/([A-Z])/", "_\\1", $var));
            return $formatStr;
        }

        /**
         * 更新字段数据
         * @param number $id
         * @param number $income 1增加|2减少
         * @param string $key 字段
         * @param float $value 改变量
         * @return void
         * @version 1.0.141017 WZ
         * @author WZ
         */
        protected function updateKey($id, $income, $key, $value)
        {
            $res = false;
            if(1 == $income && 0 < $value){
                $res = $this->update(array($key => new Expression("$key + $value")), array('id' => $id));
            }elseif(2 == $income && 0 < $value){
                $res = $this->update(array($key => new Expression("$key - $value")), array('id' => $id));
            }
            return $res;
        }

        /**
         * 获取多条数据(不分页)
         *
         * @param $where array or obj 条件
         * @param $columns array 要查询的字段
         * @param $search_key string 要搜索的关键字字段数组
         * @return array
         */
        protected function fetchAll($where = null, $columns = null,  $search_key = '')
        {
            $search = array();
    
            if($search_key && $this->searchKeyWord){
                foreach($search_key as $v){
                    $search[$v] = $this->searchKeyWord;
                }
            }
            $select = new Select($this->table);
            if($columns || $this->tableColumns){
                $columns = $columns ? $columns : $this->tableColumns;
                $select->columns($columns);
            }
            if($where){
                if($where instanceof Where){
                    $where->equalTo("delete", $this->delete);
                    $select->where->addPredicate($where);
                }else{
                    $where['delete'] = $this->delete;
                    $select->where($where);
                }
            }else{
                $select->where(array("delete" => $this->delete));
            }

            if($this->orderBy){
                $select->order($this->orderBy);
            }
         /*   if($this->limit){
                $select->limit($this->limit);
            }*/
//             if($search_key){
//                 $sub_where = new Where();
//                 foreach($search_key as $key => $value){
//                     //$pattern = "/[`!~!@#$%^&*(),.\/<>?;\':\"{}|\[\]_+=\\\-]*/";
//                     //$value = preg_replace($pattern, '', $value);
//                     $sub_where_1 = new Where();
//                     $sub_where_1->like($key, '%' . $value . '%');
//                     $sub_where->orPredicate($sub_where_1);
//                 }
//                 $select->where->andPredicate($sub_where);
//             }
            if($search){
                $sub_where = new Where();
                foreach($search as $key => $value){
                    //$pattern = "/[`!~!@#$%^&*(),.\/<>?;\':\"{}|\[\]_+=\\\-]*/";
                    //$value = preg_replace($pattern, '', $value);
                    $sub_where_1 = new Where();
                    $sub_where_1->like($key, '%' . $value . '%');
                    $sub_where->orPredicate($sub_where_1);
                }
                $select->where->andPredicate($sub_where);
            }
//            echo $select->getSqlString();
            $resultSet = $this->executeSelect($select);

            $result = array();
            $result['list']= array();
            foreach ($resultSet as $item) {
                $result['list'][] = $item;
            }

//            while($r = $resultSet->current()){
//                $result['list'][] = $r;
//            }
            $result['total'] = $resultSet->count();
            return $result;
        }

        /**
         * 获取列表数据(包括分页和不分页数据)
         *
         * @param array $where 查询条件
         * @param array $columns 要查询的字段
         * @param array $search_key 要搜索的关键字字段数组
         * @return array
         */
        protected function getAll($where = null, array $search_key = array(), array $columns = array())
        {

            $columns = $columns ? $columns : $this->tableColumns;
            $result = $this->getData($where, $columns, true, $search_key);
            // 分页数据
            $list = $this->adapterToPager($result, $this->page, $this->limit);

            return array('total' => $list['total'], 'list' => $list['list'], 'paginator' => $list['paginator']); // PC专用，用于传入模板

        }

        /**
         * 查询多条记录
         * @param array $where 查询条件
         * @param array $columns 查询字段
         * @param bool $need_page 是否分页 true 是 false 否
         * @param string $search_key 要搜索的关键字字段数组
         * @return ResultSet|DbSelect
         */
        protected function getData($where = null, array $columns = array(), $need_page = false, $search_key = '')
        {
            $search = array();

            if($search_key && $this->searchKeyWord){
                foreach($search_key as $v){
                    $search[$v] = $this->searchKeyWord;
                }
            }
            $select = new Select();
            $select->from($this->table);;
            if($where){
                if($where instanceof Where){
                    $where->equalTo("delete",$this->delete);
                    $select->where->addPredicate($where);
                }else{
                    $where['delete'] = $this->delete;
                    $select->where($where);
                }
            }else{
                $select->where(array("delete"=>$this->delete));
            }


            if($search){
                $sub_where = new Where();
                foreach($search as $key => $value){
                    //$pattern = "/[`!~!@#$%^&*(),.\/<>?;\':\"{}|\[\]_+=\\\-]*/";
                    //$value = preg_replace($pattern, '', $value);
                    $sub_where_1 = new Where();
                    $sub_where_1->like($key, '%' . $value . '%');
                    $sub_where->orPredicate($sub_where_1);
                }
                $select->where->andPredicate($sub_where);
            }

            if($columns){
                $select->columns($columns);
            }

            if($this->orderBy){
                $select->order($this->orderBy);
            }
            /*   $start_time = $this->microtime_float();
              echo $start_time;
            */
          //  echo $select->getSqlString();

            if($need_page){
                $adapterOrSqlObject = $this->getAdapter();
                $adapter = new DbSelect($select, $adapterOrSqlObject);
                // $end_time = $this->microtime_float();
                /*  echo '结束运行时间：';
                 echo $end_time-$start_time;
                 echo '-------}'; */
                return $adapter;
            }else{
                $resultSet = $this->executeSelect($select);
                //$end_time = $this->microtime_float();
                /*  echo '结束运行时间：';
                 echo $end_time-$start_time;
                 echo '-------}'; */
                return $resultSet;
            }
        }

        /**
         * 查询对象转化成列表返回
         *
         * @param Adapter $adapter
         * @param Number $page 当前分页
         * @param Number $limit 每页条数
         * @return array(total , list => object) 分页结果
         */
        protected function adapterToPager($adapter, $page, $limit)
        {
            $paginator = new Paginator($adapter); // 实列化分页类
            $paginator->setCurrentPageNumber($page);
            $paginator->setItemCountPerPage($limit);
            $paginator->setPageRange(8);

            $total = $paginator->getTotalItemCount() . "";
            $list = array();
            if($total > ($page - 1) * $limit){
                foreach($paginator as $key => $v){
                    $list[] = $v;
                }
            }

            return array('total' => $total, 'list' => $list, 'paginator' => $paginator); // PC专用，用于传入模板
        }

        /**
         * 获取以列ID做下标的多条记录
         *
         * @param array $where 查询条件
         * @param array $columns 要查询的字段
         * @param array $search_key 关键字查询
         * @return array $list
         */
        protected function getDataByIn($where = null, $columns = null, $search_key = array())
        {
            $columns = $columns ? $columns : $this->tableColumns;
            $resultSet = $this->getData($where, $columns, false, $search_key);
            $list = array();
            if($resultSet->count() > 0){
                foreach($resultSet as $key => $value){
                    $list[$value['id']] = (array)$value;
                }
            }

            return $list;
        }

        /**
         * 更新数据
         * @param $set array 更新字段
         * @param $where  array 更新条件
         * @return bool|int 更新条目数
         */
        protected function updateData()
        {
            if(!$this->id){
                throw new \Exception("ID不能为空");
            }
            $where = array("id" => $this->id);
            $set = $this->getVal();
            $result = $this->update($set, $where);
            if($result === false)
            {
                $res = $result;
            }
            else
            {
                $res = $result == 0 ? true : $result;
            }
            return $res;
        }

        /**
         * 循环设置当前表对像值
         * @return mixed
         */
        protected function getVal()
        {
            foreach($this->columns_array as $v){
                if($this->$v !== null){
                    $set[$this->convert($v)] = $this->$v;
                }

            }
            return $set;
        }

        /**
         * 假删除数据
         * @return int
         */
        protected function deleteData()
        {
            if(!$this->id){
                throw new \Exception("ID不能为空");
            }
            return $this->update(array('delete' => 1), array('id' => $this->id));
        }

        /**
         * 新增一条记录
         * @return int
         */
        protected function addData()
        {
            $data = $this->getVal();
            $data['timestamp'] = date("Y-m-d H:i:s");//初始为当前时间
            unset($data['id']);
            return $this->insertData($data);
        }

        /**
         * 插入数据
         * @param $data 插入数据数组
         * @return int 插入后的id
         */
        protected function insertData($data)
        {
            $this->insert($data);
            return $this->getLastInsertValue();
        }

        /**
         * 数据字段转小驼峰式(API 接口专用)
         * @param $data array|object 数据库查询出来的数据，可以是对像，也可以是数据
         * @param $columns_array 需要返回的字段名称
         * @return array|object 返回传入的数据格式
         *
         */
        public function dataConvert($data,$columns_array = array())
        {

            if(is_array($data))
            {
                $result = array();
                foreach($data as $v)
                {
                    $result[] = $this->formatConversion($v,$columns_array);
                }
                return $result;
            }
            elseif(is_object($data))
            {
               return $this->formatConversion($data,$columns_array);
            }
            return $data;
        }

        /**
         * 除理对像KEY值
         * @param $data
         * @param $columns_array 需要返回的字段名称
         * @return array|string
         */
        protected function formatConversion($data,$columns_array)
        {
            $result = array();
            foreach($data as $k=>$v)
            {
                $k = $this->convertUnderline($k);
                if(in_array($k,$columns_array))
                {
                    $result[$k] = $v ? $v : '';
                }

            }
            return (object)$result;
        }

        /**
         * 下划线转驼峰
         * @param $str
         * @return mixed
         */
        protected function convertUnderline($str)
        {
            $str = preg_replace_callback('/([-_]+([a-z]{1}))/i',function($matches){
                return strtoupper($matches[2]);
            },$str);
            return $str;
        }


        /**
         * 获取当前时间(格式：2017-08-05 11：10：12)
         */
        public function getTime()
        {
            return date("Y-m-d H:i:s");
        }
        
        
        /** === Danny, 2017-08-03 === */
        
        /**
         *  对 JSON 格式的字符串解码为当前对象
         * @param $json_string
         * @return $this
         */
        public function jsonDecode($json_string)
        {
            $json_obj = json_decode($json_string, true);
            
            $keys = get_object_vars($this);
            // 用于移除基类不必要的属性.
            //             foreach($this->parentProperty as $v){
            //                 unset($keys[$v]);
            //             }
            //             var_dump($keys);
            
            foreach($json_obj as $k => $v){
                if(in_array($k, $keys)){
                    $this->$k = $v;
                }
            }
            return $this;
        }
        
        /**
         *  对当前对象进行 JSON 编码
         * @param $json_string
         * @return $this
         */
        public function jsonEncode()
        {
            $json_string = json_encode($this);
            return json_encode($this);
            //             $keys = get_object_vars($this);
            //             foreach ($json_obj as $k => $v) {
            //                 if (in_array($k, $keys)) {
            //                     $this->$k = $v;
            //                 }
            //             }
            //             return $this;
        }
        
        /**
         * 循环设置当前表对像值
         * @return mixed
         */
        public function getSet()
        {
            $set = array();
            foreach($this->mapping as $k => $v) {
                if($this->$k !== null) {
                    $set[$v] = $this->$k;
                }
            }
            return $set;
        }
        
        /**
         * 根据对象id查询结果集，返回当前对象.
         *
         * @param int $id
         * @throws \Exception
         * @return array|ArrayObject|NULL
         */
        public function selectObjectById($id)
        {
            if (!$id) {
                throw new \Exception("id不能为空");
            }
            $where = array("id" => $id);
            return $this->executeSelectByWhere($where, false);
        }
        
        public function selectCollectionByIds($ids = array())
        {
            if (!count($ids)) {
                throw new \Exception("ids不能为空");
            }
            $where= new Where();
            $where->in("id", $ids);
            return $this->executeSelectByWhere($where);
        }
        
        public function selectCollectionByUUIDs($uuids = array())
        {
            if (!count($uuids)) {
                throw new \Exception("uuids不能为空");
            }
            $where= new Where();
            $where->in("uuid", $uuids);
            return $this->executeSelectByWhere($where);
        }
        
        public function selectCollectionByPaging($id = array())
        {
            
        }
        
        public function selectCollectionBySearch($id = array())
        {
            
        }
        
        public function executeSelectByWhere($where, $isArray = TRUE)
        {
            $select = new Select();
            $select->from($this->table);
            $columns = array('*');
            $select->columns($columns);
            if ($where) {
                $select->where($where);
            }
            if ($this->orderBy) {
                $select->order($this->orderBy);
            }
//             echo "SQL:" . $select->getSqlString() . "\r\n<br />\r\n";
            $resultSet = $this->executeSelect($select);
            $count = $resultSet->count();
            $data = array();
            if ($isArray) {
                foreach ($resultSet as $item) {
                    $data[] = $item;
                }
            }
            else {
                $data = $resultSet->current();
            }
            return $data;
        }
        
        
        public function insertObject()
        {
            $set = $this->getSet();
            unset($set['id']);
//             var_dump($set);
            $set['timestamp'] = date("Y-m-d H:i:s");//初始为当前时间
            $affectedRows = $this->insert($set);      
            return $this->getLastInsertValue();
        }
        
        public function updateObject()
        {
            if(!$this->id){
                throw new \Exception("id不能为空");
            }
            $set = $this->getSet();
            //             var_dump($set);
            $where = array("id" => $this->id);
            return $this->update($set, $where);
        }
        
        public function updateWithObjectId()
        {
            if(!$this->id){
                throw new \Exception("id不能为空");
            }
            $set = $this->getSet();
//             var_dump($set);
            $where = array("id" => $this->id);
            return $this->update($set, $where);
        }

        /***
         * 软删除，即标记为删除delete=1，实际数据库为更新操作.
         * @throws \Exception
         * @return number
         */
        public function deleteMarkObject()
        {
            if(!$this->id){
                throw new \Exception("ID不能为空");
            }
            $set = array('delete' => 1);
            $where = array("id" => $this->id);
            return $this->update($set, $where);
        }
        
        public function deleteObject()
        {
            if(!$this->id){
                throw new \Exception("ID不能为空");
            }
            $where = array("id" => $this->id);
            return $this->delete($where);
        }
        
        public function deleteWithIds($ids = array())
        {
            if(!count($ids)){
                throw new \Exception("ids不能为空");
            }
            $where = new Where();
            $where->in("id", $ids);
            return $this->delete($where);
        }

        /**
         * 结果集转当前对象
         * @param array $set
         */
        public function convertSetToObject($set)
        {
            foreach($this->mapping as $k => $v){
                if (isset($set[$v])) {
                    $this->$k = $set[$v];
                }
            }
        }
        
        public function convertObjectToSet()
        {
            
        }
        
    }