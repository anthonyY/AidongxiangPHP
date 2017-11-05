<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;
use Api\Model\PublicTable;
use Api\Controller\Common\Login;
use Api\Controller\Common\Structure;
use Api\Controller\Common\Request;
use Api\Controller\Common\TableRequest;
use Api\Controller\Common\WhereRequest;
use Api\Controller\Common\Response;
use Admin\Controller\CommonController as AdminController;
use Core\System\AiiPush\AiiPush;
use Core\System\AiiPush\AiiMyFile;
use Core\System\File;
use Core\System\UploadfileApi;
use Core\System\Image;
use Api\Controller\Item\PushFromItem;
use Api\Controller\Item\PushArgsItem;
use Api\Controller\Item\PushTemplateItem;

class CommonController extends AdminController
{
    public $images = array();
    /**
     * 生成验证码时候的需要英文大写
     *
     * @var 1
     */
    const CODE_TYPE_UPPERCAS = 1;

    /**
     * 生成验证码时候的需要英文小写
     *
     * @var 2
     */
    const CODE_TYPE_LOWERCASE = 2;

    /**
     * 生成验证码时候的需要数字
     *
     * @var 4
     */
    const CODE_TYPE_NUMBER = 4;

    /**
     * 结构用来转化POST过来的数据，试行
     *
     * @var array
     */
    public $structure = array();

    /**
     * 命名空间，协议类型
     *
     * @var String
     */
    public $namespace = '';

    /**
     * 会话id，32随机字符串，在Session协议生成，一个设备绑定一个SessionId
     *
     * @var String
     */
    public $session_id = '';

    /**
     * 输出类型，json或套模版输出HTML
     *
     * @var string JSON 或 HTML
     */
    public $output = '';

    /**
     * 缓存时间，缓存协议用到
     *
     * @var date 格式：????-??-?? ??:??:??
     */
    public $timestampLeast;

    /**
     * 可选
     * 0不缓存；【默认】
     * 1启动缓存；【按需缓存】
     * 2获取ids数据明细；【按需缓存】
     * 3获取>timestampLastest新数据；【完全缓存】
     * 4获取>timestampLastest且delete数据。【完全缓存】
     *
     * @var unknown
     */
    public $cache = '';

    /**
     * 主要参数，查询或提交保存参数。
     */
    // public $c; //cache 不知道哪里用到
    // public $ta; //table 转移到query里面，保持跟协议结构一致
    // public $a; //action 转移到query里面，保持跟协议结构一致
    /**
     * 设置和调用POST过来的参数
     */
    public $myRequest;

    /**
     * 设置和调用POST过来的table参数
     */
    public $myTableRequest;

    /**
     * 设置和调用POST过来的Where参数
     */
    public $myWhereRequest;

    /**
     * 返回结果属性，实际上是返回的q
     */
    public $myResponse;

    public $query;
    
    public $memcache;
    /**
     * 用户对象
     */
    public $login;
    
    /**
     * 上传图片时的key
     */
    public $file_key;
    
    /**
     * 用户对象
     *
     * @var unknown
     */
    public $user_info;
    
    /**
     * 协议开始运行时间
     * @var microtime();
     */
    public $startTime;
    
    private $admin_controller;
    
    private $index_controller;

    public function __construct()
    {
        if(defined("IS_MEMCACHE") && IS_MEMCACHE){
            $this->memcache= new \Memcache();
            $this->memcache->addserver(MEMCACHE_HOST,MEMCACHE_PORT);//memcache服务联接
        }

        if (! $this->login)
        {
            $this->login = new Login();
        }
    }

    /**
     * 获取当前时间
     *
     * @return string
     */
    public function getTime()
    {
        return date("Y-m-d H:i:s");
    }

    /**
     *
     * @author hexin
     *         @date 2014.3.18
     * @abstract order_by 1、DESC 2、ASC
     * @param number $order_type            
     * @return string
     */
    public function OrderType($order_type = 1)
    {
        if ($order_type == 1)
        {
            $result = 'DESC';
        }
        elseif ($order_type == 2)
        {
            $result = 'ASC';
        }
        else
        {
            $result = 'DESC';
        }
        return $result;
    }

    /**
     *
     * @author hexin
     *         @date 2014.3.18
     * @abstract order_by 1、timestamp
     * @param number $order_by            
     * @return string
     */
    public function OrderBy($order_by = 1)
    {
        switch ($order_by)
        {
            case 1:
                $result = 'id';
                break;
            default:
                $result = 'id';
                break;
        }
        
        return $result;
    }
    
    /**
     * 获取协议用orderby
     * @return multitype:string
     */
    public function getOrder() {
        $table = $this->getTable();
        return array(
            'need_page' => true,
            'page' => $table->page,
            'limit' => $table->limit,
            'order' => array(
                $this->OrderBy($table->order_by) => $this->OrderType($table->order_type)
            )
        );
    }

    /**
     * 2014/3/21
     * 给列表增加key
     *
     * @author WZ
     * @param array $list            
     * @param string $key            
     * @return string
     */
    public function addKey($list, $key)
    {
        $formatList = array();
        if (is_array($list)) {
            foreach ($list as $item)
            {
                $formatList[][$key] = $item;
            }
        }
        return $formatList;
    }

    /**
     * 2014/3/21
     * 给列表去除key
     *
     * @author WZ
     * @param array $list            
     * @param string $key            
     * @return multitype:unknown
     */
    public function removeKey($list, $key)
    {
        $formatList = array();
        foreach ($list as $item)
        {
            $formatList[] = $item[$key];
        }
        return $formatList;
    }

    /**
     * 协议返回信息描述处理
     *
     * @param string $err_type
     *            返回的状态码
     * @param string $err_info
     *            自定义返回信息
     * @version 1.0.140513
     *          WZ 由数组定义返回结果描述，改变成用常量定义 详见根目录status_config.php
     */
    private function api_err($err_type, $err_info = '')
    {
        // 状态列表详见status_config.php
        if (2 == ENVIRONMENT_TYPE)
        {
            $prefix = 'REAL_DESCRIPTION_';
        }
        else
        {
            $prefix = 'DESCRIPTION_';
        }
        
        $description = (defined($prefix . $err_type) ? // 是否已经定义的描述
constant($prefix . $err_type) : constant($prefix . STATUS_NOSTATUS)); // 忘记定义状态
        
        $err_info = $err_info ? // 自定义返回信息
$err_info : $description;
        return $err_info;
    }

    /**
     * 根据session获取用户信息
     *
     * @param $check 是否必须登录            
     * @author WZ
     * @version 1.0.140513 WZ 简化判断流程
     */
    public function checkLogin($check = true, $return = false)
    {
        // 查看是否空
        if (LOGIN_STATUS_LOGIN == $this->login->status && $this->login->user_id && $this->login->user_id != 'userId') {
            $user_info = $this->user_info;
            
            if (! $user_info) {
                // 用户不存在
                return $this->response(STATUS_USER_NOT_EXIST);
            }
            if (STATUS_STOP == $user_info->status && $check) {
                // 禁用用户
                return $this->response(STATUS_USER_LOCKED);
            }
            return true;
        }

        $session_id = $this->getSessionId();
        if (empty($session_id)) {
            // session
            // 为空
            if ($check) {
                $this->response(STATUS_SESSION_EMPTY);
            } else {
                return false;
            }
        } else {
            // 非空就查
            $where = array(
                "session_id" => $session_id
            );
            $login = $this->getModel('User')->getOne($where,array('*'),'login');
        }
        
        if (isset($login) && $login) {
            if (LOGIN_STATUS_OTHER_LOGIN == $login->status && $check) {
                // 用户在别处登录 1107
                $this->response(STATUS_USER_OTHER_LOGIN);
            }
            if (LOGIN_STATUS_LOGIN != $login->status && $check) {
                // 非登录状态 1100
                $this->response(STATUS_USER_NOT_LOGIN);
            }
//             if ($login->expire < $this->getTime() && $check) {
//                 // session id 会话过期 1012
//                 $this->response(STATUS_SESSION_TIMEOUT);
//             }
            
            if ($login->user_id) {
                $user_info = null;
                $where = array(
                    'id' => $login->user_id,
                    'delete' => DELETE_FALSE
                );
                $user_info = $this->getModel('User')->getOne($where,array("*"),'user');
                
                $this->user_info = $user_info;

                
            }
//             
            $this->setLoginInfo($login); // 记录登录信息
        } elseif ($check) {
            // session_id 为空或不存在 1002
            $this->response(STATUS_SESSION_EMPTY);
        }
    }
    

    
    /**
     * 初始化协议请求
     *
     * @version
     *          2015-7-17
     *          WZ
     */
    public function initRequest()
    {
        $structure = $this->initializeStructure(); // 初始化读取结构
        $this->setStructure($structure);
        $this->setRequest(); // 获取POST的数据，初始化一些数据
    
        $this->checkLogin(false);
    }

    /**
     * 初始化读取移动端提交过来的结构，按照结构获取属性
     *
     * @return multitype:array
     */
    private function initializeStructure()
    {
        $structure = new Structure();
        if (! $this->myRequest)
        {
            $this->myRequest = new Request();
        }
        if ($this->myTableRequest)
        {
            $this->myRequest->table = $this->myTableRequest;
        }
        elseif (! isset($this->myRequest->table) || ! $this->myRequest->table)
        {
            $this->myRequest->table = new TableRequest();
        }
        $this->myTableRequest = $this->myRequest->table;
        if ($this->myWhereRequest)
        {
            $this->myRequest->table->where = $this->myWhereRequest;
        }
        elseif (! isset($this->myRequest->table->where) || ! $this->myRequest->table->where)
        {
            $this->myRequest->table->where = new WhereRequest();
        }
        $this->myWhereRequest = $this->myRequest->table->where;
        
        $structure->query = $this->myRequest;
        $this->query = $this->myRequest;
        return $structure;
    }

    /**
     * 初始化返回结果，对象化初始结果
     */
    private function initializeResponse()
    {
        if (! $this->myResponse)
        {
            $this->myResponse = new Response();
        }
    }

    /**
     * 设置返回结果
     *
     * @param array|object $response            
     */
    public function setResponse($response)
    {
        $this->initializeResponse();
        if ($response instanceof Response)
        {
            foreach ($response as $key => $item)
            {
                $this->myResponse->$key = $item;
            }
        }
        elseif (is_array($response))
        {
            foreach ($response as $key => $item)
            {
                $this->myResponse->$key = $item;
            }
        }
        elseif (is_string($response) || is_int($response))
        {
            $this->myResponse->status = $response;
        }
    }

    /**
     * 设置参数结构，转化POST的传参。
     *
     * @param array $structure
     *            结构数组
     */
    public function setStructure($structure)
    {
        $this->structure[] = $structure;
    }

    /**
     * 设置基本的用户属性
     *
     * @param unknown $user_info            
     */
    private function setLoginInfo($login)
    {
        if (! $this->login)
        {
            $this->login = new Login();
        }
        foreach ($this->login as $key => $item)
        {
            $this->login->$key = isset($login->$key) ? $login->$key : 0;
        }
    }

    /**
     * 协议结果json类型输出，区分公共属性和其它属性，其它属性都在default
     *
     * @version 1.0.140513 WZ 修改了返回结果，描述根据status返回。
     */
    public function response($param = null, $return = false)
    {
        if ($param)
        {
            $this->setResponse($param);
        }
        $response = array(
            'n' => $this->namespace,
            's' => $this->session_id
        );
        $response['q'] = array();
        if (! $this->myResponse)
        {
            $this->initializeResponse();
        }
        foreach ($this->myResponse as $key => $item)
        {
            switch ($key)
            {
                case 'status':
                    $response['q']['s'] = $item;
                    $response['q']['d'] = (! isset($response['q']['d']) || empty($response['q']['d'])) ? $this->api_err($response['q']['s']) : '';
                    break;
                case 'description':
				    if($item)
                    {
                        $response['q']['d'] = $item;
                    }
                    break;
                case 'timestamp':
                case 'total':
                case 'id':
                    if ($item !== null)
                    {
                        $response['q'][$key] = $item;
                    }
                    break;
                default:
                    $response['q'][$key] = $item;
                    break;
            }
        }
        if (! isset($response['q']['s']))
        {
            // 防止忘记返回状态码
            $response['q']['s'] = STATUS_NOSTATUS;
        }
        if (! isset($response['q']['d']))
        {
            // 防止忘记返回状态描述
            $response['q']['s'] = $this->api_err($response['q']['s']);
        }
        if (! isset($response['q']['t']))
        {
            $response['q']['t'] = $this->getTime();
        }
        if ($this->startTime) {
            list($usecStart, $secStart) = explode(' ', $this->startTime);
            list($usecEnd, $secEnd) = explode(' ', microtime());
            $response['tt'] = ($secEnd - $secStart) * 1000 + (int) (($usecEnd - $usecStart) * 1000);
            if ($response['tt'] > 1000) {
                $myfile = new AiiMyFile();
                $myfile->setFileToPublicLog();
                $myfile->putAtStart("请求处理慢："."时间（".$response['tt']."）毫秒"."请求，".$_REQUEST['json']);
            }
        }
        $json = json_encode($response);
//         $json = str_replace('null', '""', $json);
        $json = str_replace(array(':null,',':null}'), array(':"",',':""}'), $json);
        if ($return) {
            return $json;
        }
        die($json);
    }

    /**
     * 获得命名空间Namespace
     *
     * @return String
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * 获得会话Id，session_id
     *
     * @return String
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    public function getLogin()
    {
        return $this->login;
    }
    
    public function setUserId($user_id)
    {
        $user_id = (int)$user_id;
        
        $user = $this->getUserTable()->getOne(array('id' => $user_id));
        if ($user)
        {
            $this->login->user_id = $user_id;
            $this->login->status = LOGIN_STATUS_LOGIN;
            $this->login->device_type = 32;
            $this->login->user_name = $user->name;
        }
        else 
        {
            $this->response(STATUS_NODATA);
        }
    }

    public function getUserId()
    {
        $login = $this->login;
        return isset($login->user_id) ? $login->user_id : 0;
    }

    public function getUserName()
    {
        $login = $this->login;
        return isset($login->user_name) ? $login->user_name : '';
    }

    public function getUserStatus()
    {
        $login = $this->login;
        return isset($login->status) ? $login->status : '';
    }

    public function getTimestampLeast()
    {
        return $this->timestampLeast ? $this->timestampLeast : '0000-00-00 00:00:00';
    }

    /**
     * 获取Post过来的Query对象，防止
     */
    public function getAiiRequest()
    {
        foreach ($this->myRequest as $key => $value)
        {
            $this->myRequest->$key = $this->query->$key;
        }
        return $this->myRequest;
    }

    /**
     * 获取Post过来的Query对象里面的table
     *
     * @return \Api\Controller\Common\TableRequest
     */
    public function getTable()
    {
        foreach ($this->myTableRequest as $key => $value)
        {
            $this->myTableRequest->$key = $this->query->table->$key;
        }
        return $this->myTableRequest;
    }

    /**
     * 获取Post过来的Query对象里面的table的where对象
     *
     * @return \Api\Controller\Common\WhereRequest
     */
    public function getTableWhere()
    {
        foreach ($this->myWhereRequest as $key => $value)
        {
            $this->myWhereRequest->$key = $this->query->table->where->$key;
        }
        return $this->myWhereRequest;
    }

    /**
     * 初始化输出对象
     *
     * @return \Api\Controller\Common\Response
     */
    public function getAiiResponse()
    {
        $this->initializeResponse();
        return $this->myResponse;
    }

    /**
     * 获取JSON
     * 自动将参数写入属性
     */
    public function setRequest()
    {
        $json = isset($_REQUEST['json']) ? $_REQUEST['json'] : false;
        if (! $json) {
            return false;
        }
        $jsonArray = json_decode($json);
        foreach ($this->structure as $childStructure) {
            $this->getJson($this, $jsonArray, $childStructure);
        }
        unset($this->structure);
    }

    /**
     * 获取json数据，以数组的形式返回，尽量不要使用这个方法获取json数组。
     * 除非遇到传送的变量数量不确定的时候。
     *
     * @return boolean array
     */
    public function getJsonObject()
    {
        $json = isset($_REQUEST['json']) ? $_REQUEST['json'] : false;
        if (! $json)
        {
            return false;
        }
        $json = json_decode($json);
        return $json;
    }

    /**
     * Json对象的属性转化成本对象的属性
     *
     * @param Object $obj            
     * @param Object $query            
     * @param array $structure            
     * @author WZ
     * @version 1.0.140514 WZ
     */
    public function getJson($this_obj, $json_query, $structure)
    {
        foreach ($structure as $key => $item)
        {
            if ($key == 'options')
            {
                continue;
            }
            if (! $item)
            {
                $this_obj->$key = isset($json_query->$key) ? $json_query->$key : "";
            }
            elseif ('object' == gettype($item))
            {
                if (! isset($this_obj->$key) || ! is_object($this_obj->$key))
                {
                    $this_obj->$key = (object) array();
                }
                
                if(isset($json_query->$key))
                {
                    $this->getJson($this_obj->$key, isset($json_query->$key) ? $json_query->$key : "", $item);
                }
            }
        }
        
        if (isset($structure->options))
        {
            if (isset($structure->options['key']) && ! empty($structure->options['key']))
            {
                foreach ($structure->options['key'] as $key => $json_key)
                {
                    if(is_object($this_obj->$key))
                    {
                        $this->getJson($this_obj->$key, isset($json_query->$json_key) ? $json_query->$json_key : "", $structure->$key);
                    }
                    else
                    {
                        $this_obj->$key = isset($json_query->$json_key) ? $json_query->$json_key : '';
                    }
                }
                unset($this_obj->options['key']);
            }
            
            if (isset($structure->options['default']) && ! empty($structure->options['default']))
            {
                foreach ($structure->options['default'] as $key => $value)
                {
                    if("" === $this_obj->$key)
                    {
                        $this_obj->$key = $value;
                    }
                }
            }
            
            if (isset($structure->options['functions']) && ! empty($structure->options['functions']))
            {
                foreach ($structure->options['functions'] as $key => $value)
                {
                    if ($this_obj->$key)
                    {
                        $result = $this->$value['key']($this_obj->$key);
                        
                        switch ($value['key'])
                        {
                            case 'findSensitiveWord':
                                if(isset($value['true']) && $result)
                                {
                                    $param = array(
                                        'status' => $value['true'],
                                        'description' => $this->api_err($value['true']) . '：' . $result
                                    );
                                    $this->response($param);
                                }
                                break;
                            default:
                                if(isset($value['true']) && $result)
                                {
                                    $this->response($value['true']);
                                }
                                elseif(isset($value['false']) && ! $result)
                                {
                                    $this->response($value['false']);
                                }
                                break;
                        }
                    }
                }
            }
            unset($this_obj->options);
        }
    }

    /**
     * 缓存写入
     *
     * @param unknown $filename
     *            文件名格式 region 或 Admin/category
     * @param unknown $param
     *            数组
     * @param integer $type 1.文件 2.内存           
     * @return boolean
     */
    public function setCache($filename, $param, $type, $cache_time = 5)
    {
        if($type==1)
        {
            //文件缓存
            $filename = $this->getCacheFilename($filename);
           
            if ($param)
            {
                if (! is_file($filename))
                {
                    @touch($filename);
                    @chmod($filename, 0777);
                }
                
                if (is_string($param))
                {
                    $data = $param;
                }
                else {
                    $data = json_encode($param, JSON_UNESCAPED_UNICODE);
                }
//                 @file_put_contents($filename, $data);
                $file = new File();
                $file->mkFile($filename,$data,true);
            }
            elseif(is_file($filename))
            {
                @unlink($filename);
            }
            return true;
        }
        else
        {
            //内存缓存
            if(! IS_MEMCACHE)
            {
                return false;
            }
            
            $param['cache_timestamp'] = $this->getTime();
            if($this->memcache->set($filename, $param,false,$cache_time))
            {
                return 2;
            }
            
        }
        return false;
    }

    /**
     * 获得缓存
     * @param unknown $filename 文件名格式 region 或 Admin/category
     * @param integer $type 1 文件缓存  2 内存缓存
     * @return boolean mixed
     */
    public function getCache($filename,$type,$check = true)
    {   
        $timestampLeast = $this->getTimestampLeast();//APP提交的缓存时间
        
        if($type==1)
        {
            //文件缓存
            $filename = $this->getCacheFilename($filename);
            if (! is_file($filename))
            {
                return false;
            }
           
            $ctime = filemtime($filename); // 缓存更新时间
            if (! $ctime)
            {
                return false;
            }
            
            if (strtotime($timestampLeast) >= $ctime && $check)
            {
                // 缓存时间大于文件生成时间就不用返回整个列表啦
                $this->response(STATUS_CACHE_AVAILABLE); // 1020 缓存数据可用
            }else{
                
                $data = file_get_contents($filename);
                if ($data)
                {
                    $param = json_decode($data, true);
                    
                    return $param;
                }
            }
        }
        elseif($type==2)
        {
            //memcache 缓存
            if(! IS_MEMCACHE)
            {
                return false;
            }
            
            $data = @$this->memcache->get($filename);
            if(!$data)
            {
                //没找到缓存返回false
                return false;
            }
            else
            {   
                       
                if($timestampLeast>=$data['cache_timestamp'] && $check)
                {
                    // 缓存时间大于文件生成时间就不用返回整个列表啦
                    $this->response(STATUS_CACHE_AVAILABLE); // 1020 缓存数据可用
                }
                else
                {
                    unset($data['cache_timestamp']);
                    return $data;
                } 
            }
           
        }
        return false;
    }
    
    /**
     * 清除文件缓存
     * 
     * @param string $filename
     * @param number $type 1删除文件夹，2删除文件
     * @version 2014-12-5 WZ
     */
    public function clearCache($filename, $type = 1)
    {
        $file = new File();
        if(1 == $type)
        {
            $cache_path = $this->getCacheFilename($filename);
            if(is_dir($cache_path))
            {
                $file->delDir($cache_path, true);
            }
            else
            {
                $cache_path = substr($cache_path, 0, strrpos($cache_path, '/'));
                $file->delDir($cache_path);
            }
        }
        elseif(2 == $type)
        {
            $file->delFile($filename);
        }
    }
    
    /**
     * 生成缓存名
     * 
     * @param array|string $param
     * @return string
     * @version 2014-12-5 WZ
     */
    public function makeCacheFilename($param = array(), $type = 2, $namespace = '')
    {
        if(1 == $type)
        {
            $filename = ($namespace ? $namespace : $this->getNamespace()) . '/';
            if (is_array($param))
            {
                foreach ($param as $key => $value)
                {
                    $filename .= $key . $value . '/';
                }
            }
            elseif(is_string($param))
            {
                $filename .= $param . '/';
            }
            $request = $this->getAiiRequest();
            $filename .= 'a' . $request->action;
            $table = $request->table;
            $filename .= '_pa' . $table->page;
            $filename .= '_li' . $table->limit;
            $filename .= '_ob' . $table->order_by;
            $filename .= '_ot' . $table->order_type;
        }
        elseif(2 == $type)
        {
            $filename = $this->getNamespace();
            if (is_array($param))
            {
                foreach ($param as $key => $value)
                {
                    $filename .= '_' . $key . $value;
                }
            }
            elseif(is_string($param))
            {
                $filename .= '_' . $param;
            }
            $request = $this->getAiiRequest();
            $filename .= '_a' . $request->action;
            $table = $request->table;
            $filename .= '_pa' . $table->page;
            $filename .= '_li' . $table->limit;
            $filename .= '_ob' . $table->order_by;
            $filename .= '_ot' . $table->order_type;
        }
        
        return $filename;
    }

    public function getCacheFilename($filename)
    {
        return APP_PATH . '/Cache/' . $filename . '';
    }

    /**
     * 检查缓存是否可用，可用立即退出。
     *
     * @param unknown $filename
     * @return Ambigous <boolean, \Api\Controller\mixed, mixed>
     */
    public function checkCacheFile($filename)
    {
        $timestampLeast = $this->getTimestampLeast();
        $ctime = $this->getCacheTime($filename); // 缓存更新时间
        if (! $ctime)
        {
            return false;
        }
        if (strtotime($timestampLeast) >= $ctime)
        {
            // 缓存时间大于文件生成时间就不用返回整个列表啦
            $this->setResponse(STATUS_CACHE_AVAILABLE); // 1020 缓存数据可用
            $this->response();
        }
        $cache = $this->getCache($filename);
        return $cache;
    }

    /**
     * 2014/3/28
     * 根据坐标给出一个方形四角的经纬度
     *
     * @author WZ
     * @param float $centerX
     *            经度
     * @param float $centerY
     *            纬度
     * @param number $type
     *            周边距离_N
     * @return array 二维数组
     */
    public function getCornersCoordinate($centerX, $centerY, $length)
    {
       
        $diffCoordinateX = $this->getCoordinatesDifference($length, "x", $centerY); // 经度
        $diffCoordinateY = $this->getCoordinatesDifference($length, "y"); // 纬度
        
        $positionLeft = round($centerX - $diffCoordinateX, 6); // 方形左侧经度
        $positionRight = round($centerX + $diffCoordinateX, 6); // 方形右侧经度
        $positionDown = round($centerY - $diffCoordinateY, 6); // 方形下侧纬度
        $positionUp = round($centerY + $diffCoordinateY, 6); // 方形上侧纬度
        
        return array(
            array(
                $positionLeft,
                $positionRight
            ),
            array(
                $positionDown,
                $positionUp
            )
        );
    }

    /**
     * 2014/3/28
     * 根据长度获取度数差
     *
     * @author WZ
     * @param float $length
     *            长度
     * @param string $type
     *            x表示经度 y表示纬度
     * @param string $value
     *            计算经度的时候需要用到纬度
     * @return number 返回度数差
     */
    public function getCoordinatesDifference($length, $type, $value = "")
    {
        $diffCoordinate = 0.00;
        switch ($type)
        {
            case "x":
                $diffCoordinate = $length / (pi() * EARTH_RADIUS * cos(deg2rad($value))) * 180;
                break;
            case "y":
                $diffCoordinate = $length / (pi() * EARTH_RADIUS) * 180;
                break;
        }
        return $diffCoordinate;
    }

    /**
     * 2014/3/28
     * 排序 ，从大到小，冒泡
     *
     * @author WZ
     * @param array $list
     *            longitude 和 latitude 必须要有
     * @param float $centerX
     *            longitude 经度
     * @param float $centerY
     *            latitude 纬度
     * @return array
     */
    public function sortCoordinates($list, $centerX, $centerY)
    {
        $total = count($list);
        for ($i = 0; $i < $total - 1; $i ++)
        {
            for ($j = $i + 1; $j < $total; $j ++)
            {
                $length_i = sqrt(pow($list[$i]["longitude"] - $centerX, 2) + pow($list[$i]["latitude"] - $centerY, 2)); // i下标到中心的距离
                $length_j = sqrt(pow($list[$j]["longitude"] - $centerX, 2) + pow($list[$j]["latitude"] - $centerY, 2)); // j下标到中心的距离
                if ($length_i > $length_j)
                {
                    $temp = $list[$i];
                    $list[$i] = $list[$j];
                    $list[$j] = $temp;
                }
            }
        }
        return $list;
    }
    
    /**
     * 计算两坐标之间距离
     *
     * @param double $lat1
     *            起点纬度
     * @param double $lng1
     *            起点经度
     * @param double $lat2
     *            终点纬度
     * @param double $lng2
     *            终点经度
     * @return float 米
     */
    public function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $radLat1 = $this->rad($lat1);
        $radLat2 = $this->rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($lng1) - $this->rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * EARTH_RADIUS;
        $s = round($s * 10000 * 1000) / 10000;
        return $s; // 米
    }

    public function rad($d)
    {
        return $d * M_PI / 180.0;
    }

    /**
     * 控制器，统一访问模型getAll方法。
     *
     * @param PublicTable $table            
     * @param array|Where $where            
     * @return array (total=>int , list=>array)
     */
    public function getAll($table, $where = array(), $columns = null, $like = array())
    {
        $query_table = $this->getTable();
        $page = $query_table->page;
        $limit = $query_table->limit;
        $order_by = trim($this->OrderBy($query_table->order_by)) . ' ' . $this->OrderType($query_table->order_type);
        
        return $table->getAll($where, $columns, $order_by, true, $page, $limit, $like);
    }

    /**
     * 生成随机字符串
     *
     * @param number $length
     *            长度
     * @param number $type
     *            类型 1大写；2小写；3大小写混合；4数字；5大写+数字；6小写+数字；7大小写+数字；
     * @return string
     */
    public function makeCode($length, $type)
    {
        $uppercase_chars = 'ABCDEFGHIJKLMOPQRSTUVWXYZ'; // 1 去掉N
        $lowercase_chars = 'abcdefghijklmopqrstuvwxyz'; // 2 去掉n防止出现null的错误
        $number_chars = '0123456789'; // 4
        
        $chars = '';
        if ($type & self::CODE_TYPE_UPPERCAS)
        {
            $chars .= $uppercase_chars;
        }
        if ($type & self::CODE_TYPE_LOWERCASE)
        {
            $chars .= $lowercase_chars;
        }
        if ($type & self::CODE_TYPE_NUMBER)
        {
            $chars .= $number_chars;
        }
        
        $code = '';
        for ($i = 0; $i < $length; $i ++)
        {
            $code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $code;
    }

    /**
     * 初始化的值
     *
     * @param unknown $item            
     */
    public function initItem(&$item)
    {
        foreach ($item as $key => $value)
        {
            $item->$key = '';
        }
    }

    /**
     * 取得后台控制器的方法
     *
     * @return \Admin\Controller\CommonController
     */
    public function getAdminController()
    {
        if (! isset($this->admin_controller))
        {
//             $serviceLocator = $this->getServiceLocator();
            $this->admin_controller = new AdminController();
//             $this->admin_controller->setServiceLocator($serviceLocator);
        }
        return $this->admin_controller;
    }

    /**
     * 生成流水号
     *
     * @param number $type
     *            1财务交易流水号；2订单号
     * @param
     *            string|array 其它参数
     */
    public function createNumber($type, $param = '')
    {
        $number = '';
        switch ($type)
        {
            case 1:
                
                // 财务交易流水号 年月日时分秒+5位随机数 // 用户id
                $number = date('ymdHis') . mt_rand(10000, 99999);// . $param;
                break;
            case 2:
                
                // 年月日
                $type_array = array('user');
                if (! in_array($param,$type_array))
                {
                    return null;
                }
                $filename = 'system/' . $param;
                $cache = $this->getCache($filename, 1);
                
                $date = date('ymd');
                if($cache && $cache['time'] == $date)
                {
                    return null;
                }
                else 
                {
                    $cache = array();
                    $cache['time'] = $date;
                    $this->setCache($filename, $cache, 1);
                    
                    if ('user' == $param)
                    {
                        $data = $this->getUserTable()->getLastOne();
                    }
                    if ($data && ! (strpos($data['id'], $date) === false))
                    {
                        return false;
                    }
                    $number = $date . sprintf('%04d', 1);
                }
                break;
            default:
                break;
        }
        return $number;
    }

    /**
     * 结果保存到数据库
     * @param unknown $data
     * @return multitype:multitype:multitype:multitype:unknown    multitype:string unknown
     * @version 2014-12-6 WZ
     */
    public function saveFileInfo($data)
    {
        $ids = array();
        $files = array();
        foreach ($data as $key => $value)
        {
            if(! isset($value['id']) && isset($value['filename']) && isset($value['path']) && $value['filename'] && $value['path'])
            {
                $value['timestamp'] = $this->getTime();
                $id = $this->getImageTable()->insertData($value);
                $ids[] = $id;
                $files[] = array(
                    $this->file_key => array(
                        'id' => $id,
                        'path' => $value['path'],
                        'filename' => $value['filename']
                    )
                );
            }
            else
            {
                $this->getImageTable()->updateKey($value['id'], 1, 'count', 1);
                $ids[] = $value['id'];
                $files[] = array(
                    $this->file_key => array(
                        'id' => $value['id'],
                        'path' => $value['path'],
                        'filename' => $value['filename'],
                    )
                );
            }
        }
    
        return array(
            'ids' => $ids,
            'files' => $files
        );
    }
    
    /**
     * 格式化 region_info 字段（转为JSON，用于插入数据库）
     *
     * @author liujun
     * @param integer $county
     *            区域ID
     * @param integer $city
     *            城市ID
     * @param integer $province
     *            省份直辖市ID
     * @return string region_info JSON数据
     *        
     */
    protected function encode($county, $city, $province)
    {
        $region_info = array();
        if ($province > 1)
        {
            $res = $this->getRegionTable()->getOne(array(
                'id' => $province
            ));
            $province_info = array(
                "id" => $res->id,
                "name" => $res->name,
                "parent_id" => 1,
                "pinyin" => $res->pinyin
            );
            $region_info[] = array(
                "region" => $province_info
            );
        }
        if ($city > 1)
        {
            $res = $this->getRegionTable()->getOne(array(
                'id' => $city
            ));
            $city_info = array(
                "id" => $res->id,
                "name" => $res->name,
                "parent_id" => $res->parent_id,
                "pinyin" => $res->pinyin
            );
            $region_info[] = array(
                "region" => $city_info
            );
        }
        if ($county > 1)
        {
            $res = $this->getRegionTable()->getOne(array(
                'id' => $county
            ));
            $county_info = array(
                "id" => $res->id,
                "name" => $res->name,
                "parent_id" => $res->parent_id,
                "pinyin" => $res->pinyin
            );
            $region_info[] = array(
                "region" => $county_info
            );
        }
        return $this->JSON($region_info);
    }

    /**
     * 将数组转换为JSON字符串（兼容中文）
     *
     * @param array $array            
     * @return string
     * @access public
     */
    protected function JSON($array)
    {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }

    /**
     *
     *
     *
     * 使用特定function对数组中所有元素做处理
     *
     * @param
     *            string	&$array		要处理的字符串
     * @param string $function            
     * @return boolean
     * @access public
     *        
     *        
     */
    protected function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++ $recursive_counter > 1000)
        {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            }
            else
            {
                $array[$key] = $function($value);
            }
            
            if ($apply_to_keys_also && is_string($key))
            {
                $new_key = $function($key);
                if ($new_key != $key)
                {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter --;
    }

    /**
     * 解析 region_info 字段（转为数组，用于模板数据）
     *
     * @author liujun
     * @param string $result
     *            数据库region_info JSON数据
     * @return array array('province'=>省信息数组,'city'=>市信息数组，'county'=>区信息数组)
     * @version 2015/04/14 WZ 大改
     */
    protected function decode($result)
    {
        $result_info = array();
        $result = json_decode($result,true);
        if (is_array($result)) {
            foreach ($result as $key => $value)
            {
                $value = $value['region'];
                if (isset($value['parent_id'])) {
                    $value['parentId'] = $value['parent_id'];
                    unset($value['parent_id']);
                }
                $result_info[]['region'] = $value;
            }
        }
        return $result_info;
    }
    
    public function getRegionInfoArray($region_id)
    {
        $result = array(
            'region_info' => "[]",
            'province' => 0,
            'city' => 0,
            'county' => 0,
            'district' => 0
        );
        if (! $region_id)
        {
            return $result;
        }
        $count = 0;
        $region_array = array();
        $region_data = array();
        // 开始获取数据
        while($region_info = $this->getRegionTable()->getOne(array('id' => $region_id))) {
            $region_array[] = $region_id;
            $region_data[$region_id] = $region_info;
            $region_id = $region_info['parent_id'];
            if (1 == $region_info['parent_id']) { // 省级就退出
                break;
            }
            if (++ $count > 4) { // 防死循环
                break;
            }
        }
        if (! $region_array)
        {
            return $result;
        }
        $region_array = array_reverse($region_array);
        
        // 开始整理数据
        $item = array(
            0 => 'province',
            1 => 'city',
            2 => 'county',
            3 => 'district'
        );
        $region_list = array();
        foreach ($item as $k => $v) {
            if (isset($region_array[$k])) {
                $region_id = $region_array[$k];
                $region_item = $region_data[$region_id];
                $result[$v] = $region_id;
                $region_list[]['region'] = array(
                    'id' => $region_item->id,
                    'name' => $region_item->name,
                    'parent_id' => $region_item->parent_id,
                    'pinyin' => $region_item->pinyin
                );
            }
        }
        $result['region_info'] = $this->JSON($region_list);
        return $result;
    }
    
    /**
     * 获取这地区id的相关地区
     *
     * @param number $region_id
     * @param number $type 1不带0，2带0
     * @return string
     * @version 2015-4-7 WZ
     */
    public function getRegionList($region_id, $type = 1)
    {
        $count = 0;
        $region_array = array();
        if (! $region_id && $type == 1)
        {
            return '';
        }
        while($region_info = $this->getRegionTable()->getOne(array('id' => $region_id)))
        {
            $region_array[] = $region_id;
            $region_id = $region_info['parent_id'];
            if (1 == $region_info['parent_id']) { // 省级就退出
                break;
            }
            if (++ $count > 4) { // 防死循环
                break;
            }
        }
        if ($type == 2) {
            $region_array[] = 0;
        }
        if (! $region_array)
        {
            return '';
        }
        $result = array_reverse($region_array);
        return implode(',', $result);
    }
    
    /**
     * 根据region_info 提取省市区
     * @param unknown $regionInfo
     * @return string
     * @version 2015-8-18 WZ
     */
    public function regionInfoToString($regionInfo) {
        $string = "";
        if (is_string($regionInfo)) {
            $regionInfo = json_decode($regionInfo, true);
        }
        if ($regionInfo && is_array($regionInfo)) {
            $list = array();
            foreach ($regionInfo as $value) {
                $list [] = $value['region']['name'];
            }
            if ($list) {
                $string = implode("", $list);
            }
        }
        return $string;
    }

//     /**
//      * 2014/3/31
//      * 根据用户信息查找用户的设备号和设备类型
//      *
//      * @author WZ
//      * @param array $ids
//      *            用户id或者司机id，数组
//      * @param array $types
//      *            对应id的类型，数组
//      * @return multitype:
//      */
//     public function getDeviceForUser($user_id)
//     {
//         $where = array('delete' => DELETE_FALSE, 'user_id' => $user_id);
//         $device = $this->getDeviceUserTable()->getOne($where);
//         return $device;
//     }

//     /**
//      * 2014/3/31
//      * 推送内容模版设置
//      *
//      * @author WZ
//      * @param number $type            
//      * @param
//      *            array 其它参数
//      * @return array {string content ,string title} 内容和标题（标题是安卓推送需要的）
//      */
//     public function pushTemplate($type, PushArgsItem $args = null)
//     {
//         $template = new PushTemplateItem();
        
//         if(defined('TEMPLATE_PUSH_TITLE_' . $type) && defined('TEMPLATE_PUSH_CONTENT_' . $type))
//         {
//             $template->title = constant('TEMPLATE_PUSH_TITLE_' . $type);
//             $template->content = constant('TEMPLATE_PUSH_CONTENT_' . $type);
            
//             if($args)
//             {
//                 $template->content = sprintf($template->content, $args->money, $args->point, $args->number, $args->name, $args->name2);
//             }
//         }
        
//         $template->push_args['type'] = $type;
//         if($args && $args->id) {
//             $template->push_args['id'] = $args->id;
//         }
//         if ($args && $args->nid) {
//             $template->push_args['nid'] = $args->nid;
//         }
        
//         return $template;
//     }

//     /**
//      * 推送方法改为直接查数据库
//      * 
//      * @version 2015-1-5 WZ
//      */
//     public function pushForDeviceCollection()
//     {
//         $table = new Table();
//         $where = array('status' => 2);
        
//         $order_by = array('id' => 'desc');
//         $data = $table->getViewPushRecordTable()->getAll($where,null,$order_by,true,1,100); // 一次发送100条
//         if ($data['total'] > 0)
//         {
//             $time_now = date('H:i:s');
//             $push = new AiiPush();
//             $set_5 = array('status' => 5); // 免打扰
//             $set_1 = array('status' => 1); // 成功
//             $set_4 = array('status' => 4); // 失败
//             foreach ($data['list'] as $value)
//             {
//                 $where = array('id' => $value->id);
//                 if ('00:00:00' == $value->quiet_start_time)
//                 {
//                     $value->quiet_start_time = '23:59:59';
//                 }
                
//                 $args = array();
//                 if ($value->parameter)
//                 {
//                     $args = json_decode($value->parameter, true);
//                     if(! $args)
//                     {
//                         $args = array();
//                     }
//                 }
                
//                 if (OPEN_FALSE == $value->notification || $time_now > $value->quiet_start_time || $time_now < $value->quiet_end_time)
//                 {
//                     $table->getNotificationRecordsTable()->updateData($set_5, $where);
//                 }
//                 else
//                 {
//                     $value['ring'] = $value['sound'] == OPEN_FALSE ? 0 : 1;
//                     $value['vibrate'] = $value['vibrate'] == OPEN_FALSE ? 0 : 1;
// //                     $result = $push->pushCollectionDevice(array($value), $value->content, $value->title, $args);
//                     $nid = 0;
//                     if (isset($args['nid']) && $args['nid']) {
//                         $nid = $args['nid'];
//                         unset($args['nid']);
//                     }
//                     $result = $push->pushSingleDevice($value['device_token'], $value['device_type'], $value['content'], $value['title'], $args, $value['ring'], $value['vibrate'], $nid);
                    
//                     if ($result['success'])
//                     {
//                         $table->getNotificationRecordsTable()->updateData($set_1, $where);
//                     }
//                     elseif ($result['fail'])
//                     {
//                         $table->getNotificationRecordsTable()->updateData($set_4, $where);
//                     }
//                 }
//             }
//         }
//         return;
//     }

//     /**
//      * 2014/3/31
//      * 根据用户和用户类型发送推送
//      *
//      * @author WZ
//      * @param array $ids
//      *            用户id数组
//      * @param number $contentType
//      * @param PushArgsItem $args
//      *            模版中内容参数
//      * @param PushFromItem $from user_id,type,id
//      *            模版编号
//      * @version 1.0.14515 WZ 添加更多插入记录的信息
//      */
//     public function pushForController($user_id, $type, PushArgsItem $args = null, PushFromItem $from = null, PushTemplateItem $template = null)
//     {
//         $myfile = new AiiMyFile();
//         $myfile->setFileToPublicLog();
//         if (! $user_id || ! $type) {
//             return ;
//         }
//         if (! $template) {
//             $template = $this->pushTemplate($type, $args); // 根据模版编号获得推送的title和content
//         }
        
//         $device = $this->getDeviceForUser($user_id); // 根据id和用户类型查找设备号与设备类型
//         $status = 2; // 未发送
//         if ($device && $template->content) {
//             // 找到设备和模版信息没问题就开始推送
//             if (PUSH_SWITCH) {
//                 // 开启推送功能
//                 $push = new AiiPush();
// //                 $result = $this->pushForDeviceCollection();
//                 $push_args = $template->push_args;
//                 $nid = 0;
//                 if (isset($push_args['nid'])) {
//                     $nid = $push_args['nid'];
//                     unset($push_args['nid']);
//                 }
//                 $result = $push->pushSingleDevice($device['device_token'], $device['device_type'], $template->content, $template->title, $push_args, $nid);
//                 if ($result['success'])
//                 {
//                     $status = 1;
//                 }
//                 elseif ($result['fail'])
//                 {
//                     $status = 3;
//                 }
//             }
        
//             if (PUSH_LOG_SWITCH) {
//                 $content = "推送" . ($status == 1 ? "成功" : "失败") . ",".(2 == $device['delete'] ? '用户关闭推送,':'')." user_id :$device[user_id] , title : ".$template->title." , content : ".$template->content." , args：" . json_encode($template->push_args) . ", 设备号：$device[device_token]";
//                 $myfile->putAtStart($content);
//             }
//         }
//         else {
//             // 发送失败也记录
//             if (PUSH_LOG_SWITCH) {
//                 if (! $device) {
//                     $content = "推送, msg：找不到对应的设备号 , 或对应设备号关闭推送功能 , user_id：" . $user_id . " , args：" . json_encode($template->push_args) . ", 模版：" . $type;
//                 }
//                 elseif (! $template->content) {
//                     $content = "推送 , msg：不能生成content , 检查模版类型和参数是否相对应 ,  user_id：" . $user_id . " , args：" . json_encode($template->push_args) . ", 模版：" . $type;
//                 }
//                 else {
//                     $content = "推送, 未知错误";
//                 }
//                 $myfile->putAtStart($content);
//             }
//         }
        
//         if ($template->content) {
//             // 保存到数据库
//             $data = array(
//                 'title' => $template->title,
//                 'content' => $template->content,
//                 'type' => $type,
//                 'parameter' => json_encode($template->push_args),
//                 'status' => $status,
//                 'user_id' => $user_id,
//             );
//             $this->getNotificationRecordsTable()->insertData($data);
//         }
//     }

    /**
     * 推送给附近的人
     * 
     * @param unknown $task_id            
     * @param unknown $user_id            
     * @param unknown $longitude            
     * @param unknown $latitude            
     */
    public function pushForNear($task_id, $user_id, $longitude, $latitude, $args = array())
    {
        // 推送给周边的人
        // [[左,右],[上,下]]
        $setup = $this->getSettingTable()->getOne(array('id' => 11));
        $coordinate = $this->getCornersCoordinate($longitude, $latitude, $setup->value);
        $near_where = new Where();
        $near_where->notEqualTo('id', $user_id);
        $near_where->equalTo('auth_status', 2);
        $near_where->equalTo('delete', DELETE_FALSE);
        $near_where->between('longitude', $coordinate[0][0], $coordinate[0][1]);
        $near_where->between('latitude', $coordinate[1][0], $coordinate[1][1]);
        
        $near_data = $this->getUserTable()->fetchAll($near_where);
        $task_info = $this->getTaskTable()->getOne(array('id' => $task_id));
        $near_ids = array();
        
        if($near_data) 
        {
            foreach ($near_data as $value)
            {
                $args = new PushArgsItem();
                $from = new PushFromItem();
                $args->id = $task_id;
                $args->number = $this->getDistance($value['latitude'], $value['longitude'], $latitude, $longitude);
                $from->id = $task_id;
                $from->user_id = $user_id;
                $from->type = $task_info->type;
            
                if (1 == $task_info->type)
                {
                    $contentType = 201;
                }
                elseif (2 == $task_info->type)
                {
                    $contentType = 202;
                }
                $this->pushForController($value['id'], $contentType, $args, $from);
            }
        }
    }

    /**
     * 查找字符里是否带有敏感关键字词
     *
     * @author liujun
     * @param string $str
     *            要查找的字符串
     * @return bool|array
     */
    public function findSensitiveWord($str)
    {
        $words = $this->getSensitiveWords();
        
        if ($str && $words)
        {
            $info = array();
            $word = array();
            for ($i = 0; $i < count($words); $i ++)
            {
                $content = substr_count($str, $words[$i]);
                if ($content > 0)
                {
                    $word[] = $words[$i];
                }
            }
            if(count($word)>0)
            {
                $info = implode($word, ',');
            }
            
            return $info;
        }
        else
        {
            return false;
        }
    }

    /**
     * 替换敏感关键词
     *
     * @author liujun
     * @param string $str
     *            要查找替换的内容
     * @return string $str 替换后的内容
     */
    public function replaceSensitiveWords($str)
    {
        $words = $this->getCache('SensitiveWords/words',1);
        $str = strtr($str, (array) $words);
        return $str;
    }

    /**
     * 敏感词写入缓存文件
     *
     * @author liujun
     * @param string $str
     *            格式为 字|字|字 词之间用|线隔开
     */
    public function writtenSensitiveWords($words)
    {   
        $str = array();
        if($words){
            $words = array_unique(explode('|',trim( trim($words,'|'))));      
            foreach ($words as $k => $v)
            {
                $strlen = mb_strlen($v, 'utf-8');
                $star = '';
                for ($i = 0; $i < $strlen; $i ++)
                {
                    $star .= '*';
                }
                $str[$v] = $star;
                $strlen = 0;
            }
        }
        $this->setCache('SensitiveWords/words', $str,1);
    }

    /**
     * 读取缓存文件敏感数据
     * 
     * @author liujun
     * @return array $words
     */
    public function getSensitiveWords()
    {
        (array) $word = $this->getCache('SensitiveWords/words',1, false);
        if(!$word)
        {
            return $word;
        }
        $words = array();
        foreach ($word as $k => $v)
        {
            $words[] = $k;
        }
        return $words;
    }
    
    /**
     * 两次的请求间隔不能太短
     *
     * @version 2015-1-12 WZ
     */
    public function checkTime()
    {
        $param = array('check'=>1,'s'=>$this->session_id);
        $filename_1 = $this->makeCacheFilename($param,1);
        $filename_2 = $this->makeCacheFilename($param);
        $cache_filename = $this->getCacheFilename($filename_1);
        
        $get_1 = false;
        if (is_file($cache_filename) && filemtime($cache_filename) + 2 > time())
        {
            $get_1 = true;
        }
        $get_2 = $this->getCache($filename_2, 2);
        if ($get_1 || $get_2)
        {
            $this->setResponse(array(
                'status' => STATUS_CAN_NOT_RESEND
            ));
            $this->response();
        }
        else 
        {
            $type = 2;
            $set = $this->setCache($filename_2, $param, $type, 2);
            if (! $set)
            {
                $type = 1;
                $set = $this->setCache($filename_1, $param, $type);
            }
        }
        return true;
    }
    
    /**
     * 更新用户积分，并生成积分记录
     * @param 来源 $from 参考数据库文档
     * @param 用户id $user_id
     * @param 积分数量 $integral 部分请求可能直接使用传参的积分数量（例如置顶）
     * @param 外键id $foreign_id 活动id、订单id、信息id
     * @param 其它参数 $param 可能使用到的其它参数，例如（置顶的描述需要用到天数）
     * @version 2015-2-9 WZ
     */
    public function updateUserIntegral($from, $user_id, $integral, $foreign_id = 0, $param = array())
    {
        $income = 0;
        $status = 1; // 已完成
        $description = '';
        switch ($from)
        {
            case 1:
                $income = INCOME_ADD;
                $description = sprintf(INTEGRAL_TYPE_SIGNIN, 0, $integral);
                break;
            case 2:
                // 我不知道，知道和使用到的请直接修改
                break;
            case 3: // 订单使用
                $income = INCOME_SUB;
                $description = sprintf(INTEGRAL_TYPE_SHOP_USE, 0, $integral);
                break;
            case 31: // 取消订单 
                $income = INCOME_ADD;
                $description = sprintf(INTEGRAL_TYPE_SHOP_CANCEL, 0, $integral);
                break;
            case 32: // 后台操作返还积分
                $income = INCOME_ADD;
                $description = sprintf(INTEGRAL_TYPE_SHOP_RETURN, 0, $integral);
                break;
            case 4:
                // 积分购买
                $income = INCOME_ADD;
                $description = sprintf(INTEGRAL_TYPE_RECHARGE, 0, $integral);
                $status = 2; // 未支付
                break;
            case 10: // 临时添加
            case 21:
            case 22:
            case 23:
            case 24:
            case 25:
            case 26:
                $income = INCOME_SUB;
                $description = sprintf(INTEGRAL_TYPE_TOP, $param['day'], $integral);
                break;
            default:
                return array('status' => STATUS_PARAMETERS_INCOMPLETE);
                break;
        }
    
        if ($income && $integral && $description)
        {
            $user_info = $this->getUserTable()->getOne(array('id' => $this->getUserId()));
            if (! $user_info)
            {
                return array('status' => STATUS_NODATA);
            }
            
            if (INCOME_SUB == $income && $user_info['integral'] < $integral)
            {
                return array('status' => STATUS_POINT_NOT_ENOUGH);
            }
            
            if (INCOME_ADD == $income && $user_info['integral'] + $integral > LIMIT_INTEGER)
            {
                return array('status' => STATUS_POINT_TOO_MANY);
            }
            
            $data = array(
                'user_id' => $user_id,
                'income' => $income,
                'status' => $status,
                'from' => $from,
                'foreign_id' => $foreign_id,
                'integral' => $integral,
                'description' => $description,
                'timestamp' => $this->getTime()
            );
            if (1 == $status)
            {
                // 部分操作不立即更新用户积分（例如：积分购买）
                $this->getUserTable()->updateKey($user_id, $income, 'integral', $integral);
            }
            return array('status' => STATUS_SUCCESS, 'id' => $this->getIntegralRecordTable()->insertData($data));
        }
        return array('status' => STATUS_NOT_UPDATE);
    }
    
    /**
     * 列表返回属性统一方法
     * 
     * @param unknown $table
     * @param unknown $where
     * @param unknown $key
     * @param string $columns
     * @param unknown $like
     * @return \Api\Controller\Common\Response
     * @version 2015-3-26 WZ
     */
    public function setListResponse($table, $where, $key, $columns = null, $like = array())
    {
        $data = $this->getAll($table, $where, $columns, $like);
        $response = $this->getAiiResponse();
        $response->total = $data['total'];
        $list = $this->setList($data['list']);
        $response->$key = $list;
        return $response;
    }
    
    /**
     * 商城订单号获取
     * @version 2015-3-11 Waydy
     */
    public function getOrderSn(){
        $time = time();
	    return date("ymd").substr($time,5,5).mt_rand(100,999);
    }
    
    /**
     * 记录无法解决的错误，让管理员手动去处理
     * 
     * @param string $error
     * @version 2015-3-27 WZ
     */
    public function setErrorLog($error)
    {
        $filename = $this->getCacheFilename('error/' . date('YmdHis') . mt_rand(100,999)) . '.txt';
        $file = new File();
        $file->mkFile($filename, $error);
    }
    
    /**
     * 获取微信支付信息
     * 
     * @param number $id 积分记录或订单id
     * @param number $amount 支付金额
     * @return multitype:number string unknown NULL 
     * @version 2015-4-10 WZ
     */
    public function getWxPayInfo($id, $amount,$type=null)
    {
        
       if($type == 1){
           $value = array(
               'order_price' => $amount,
               'product_name' => '快摇红包支付',
               'out_trade_no' => $id
           );
           $wxpay = new AiiWxPay();
           return $wxpay->setValue($value)->getOutParams(1);
       }else if($type == 2){
           $value = array(
               'order_price' => $amount,
               'product_name' => '快摇余额充值',
               'out_trade_no' => $id
           );
           $wxpay = new AiiWxPay();
           return $wxpay->setValue($value)->getOutParams(2);
       }else{
           if (2 == WX_APP_PAY_MODE) {
               $amount = 0.01; // 测试用
           }
           $value = array(
               'order_price' => $amount,
               'product_name' => '快摇名片随身设备',
               'out_trade_no' => $id
           );
           $wxpay = new AiiWxPay();
           return $wxpay->setValue($value)->getOutParams();
       }
        
    }
    
    /**
     * 获取银联支付信息
     *
     * @param number $id 积分记录或订单id
     * @param number $amount 支付金额
     * @param number $type 1PC端；2移动端
     * @return multitype:number string unknown NULL
     * @version 2015-12-21 WZ
     */
    public function getYlPayInfo($id, $amount,$type,$num=null)
    {
        if (2 == YL_APP_PAY_MODE) {
            $amount = 0.01; // 测试用
        }
        $value = array(
            'txnAmt' => $amount,
            'orderId' => $id
        );
       
        $ylpay = new YlPay();
        if ($type == 1){
            $result = $ylpay->setValue($value)->getOutParam();
//             var_dump($result);
            return $result;
        }elseif ($type == 2){
            if($num == 1){
                $result = $ylpay->setValue($value)->getOutParams(1);
            }else if($num == 2){
                $result = $ylpay->setValue($value)->getOutParams(2);
            }else{
                $result = $ylpay->setValue($value)->getOutParams();
            } 
            return $result['tn'];
        }        
        
    }
    
    /**
     * 取消订单，退积分，退优惠券，退库存
     * 
     * @param array $info 数据库读取出来的订单对象
     * @param boolean $auto false：用户触发，true：后台或计划任务
     * @version 2015-4-15 WZ
     */
    public function cancelOrder($info, $auto = false)
    {
        if ($info) {
            if ($info['integral']) { // 如果订单包含积分，返还积分
                $result = $this->updateUserIntegral(31, $info['user_id'], $info['integral']);
                if (STATUS_SUCCESS != $result['status'])
                {
                    return $result['status'];
                }
            }
            
            if ($info['bonus_id']) { // 如果包含优惠券，返回优惠券
                $bonus_set = array(
                    'order_id' => 0,
                    'status' => 0,
                    'use_time' => '0000-00-00 00:00:00'
                );
                $bonus_where = array(
                    'id' => $info['bonus_id'],
                    'user_id' => $info['user_id']
                );
                $this->getShopUserBonusTable()->updateData($bonus_set, $bonus_where);
            }
            
            // 商品库存返还
            $goods_where = array('foreign_id' => $info['id']);
            $goods_data = $this->getShopOrderGoodsTable()->fetchAll($goods_where);
            if ($goods_data) {
                foreach ($goods_data as $goods) {
                    $stock_where = array('goods_id' => $goods['goods_id'], 'attr_id' => $goods['attr_id']);
                    $this->getShopStockTable()->updateKey(null, INCOME_ADD, 'number', $goods['number'], $stock_where); // 库存返还
                    $this->getShopGoodsTable()->updateKey($goods['goods_id'], INCOME_SUB, 'sell_count', $goods['number']); // 销售数量返回
                }
            }
            
            $this->orderTrace($info['id'], SHOP_ORDER_STATUS_CANCEL, $auto); // 订单跟踪
            
            // 订单状态改变
            $set = array(
                'hidden' => $auto ? 0 : 1, // 计划任务只是释放库存积分，不隐藏订单
                'status' => SHOP_ORDER_STATUS_CANCEL,
            );
            $where = array('id' => $info['id']);
            $this->getShopOrderTable()->updateData($set, $where); // 更新
        }
    }
    
    /**
     * 读取运费信息
     * 
     * @param number $seller_id
     * @param number $region 市级以下（省级不行）
     * @version 2015-4-16 WZ
     */
    public function getShippingFee($seller_id, $region)
    {
        $seller_id = (int)$seller_id;
        if ($seller_id < 0) {
            return false;
        }
        $region_string = $this->getRegionList($region);
        if ($region_string) {
            $region_ids = explode(',', $region_string);
            if (! isset($region_ids[0]) || ! isset($region_ids[1]) || ! $region_ids[1] || ! $region_ids[0]) {
                return false;
            }
        }
        else {
            return false;
        }
        $shipping = new ShippingList();
        $data = $shipping->sellerShipping2($seller_id, 2);
        $list = $data['list'];
        $other = $data['other'];
        
        if (array_key_exists($region_ids[1], $list)) {
            return $list[$region_ids[1]];
        }
        elseif (array_key_exists($region_ids[0], $list)) {
            return $list[$region_ids[0]];
        }
        elseif (1 == $other['status']) {
            return $other;
        }
        else {
            return false;
        }
    }
    
    /**
     * 更新商品和订单的维权状态
     *
     * @param unknown $order_goods_id
     * @param unknown $order_id
     * @version 2015-3-31 WZ
     */
    public function orderComplaintsUpdate($order_goods_id, $order_id){
        $this->getShopOrderGoodsTable()->updateData(array('complaints_status' => 2), array('id' => $order_goods_id));
        $complaints_count = $this->getShopOrderGoodsTable()->countData(array('complaints_status'=>1, 'type' => 1, 'foreign_id' => $order_id));
        if (0 == $complaints_count)
        {
            $this->getShopOrderTable()->updateData(array('complaints_status' => 2), array('id' => $order_id));
        }
    }
    
    /**
     * 简单检查输入
     * 
     * @param array $keys key是字段，value包括type
     * @param array $data
     * @param string $break_type admin, api
     * @return multitype:number string 
     * @version 2015-4-23 WZ
     */
    public function checkInput($keys, $data, $break_type = null)
    {
        $return = array();
        if (!$keys || !is_array($keys) || ! $data || !is_array($data)) {
            return $return;
        }
        foreach ($keys as $key => $value)
        {
            switch ($value['type']) {
                case 'int':
                    $return[$key] = isset($data[$key]) ? (int)($data[$key]) : 0;
                    break;
                case 'float':
                    $return[$key] = isset($data[$key]) ? (float)($data[$key]) : 0.00;
                    break;
                case 'string':
                    $p_value = isset($data[$key]) ? trim($data[$key]) : '';
                    $check = $p_value ? $this->findSensitiveWord($p_value) : false;
                    if (! $check && $break_type) {
                        if ('admin' == $break_type) {
                            $this->back($key . "存在敏感词：" . $check);
                        }
                        elseif ('api' == $break_type) {
                            $response = $this->getAiiResponse();
                            $response->description = REAL_DESCRIPTION_1016 . '：'. $check;
                            $response->status = STATUS_SENSITIVE_WORD;
                            $this->response($response);
                        }
                    }
                    $return[$key] = $p_value;
                    break;
                default:
                    break;
            }
        }
        return $return;
    }
    
    /**
     * 发送邮件
     * 
     * @param string $tomail
     * @param string $subject
     * @param string $body
     * @param array $attachment path,name
     * @version 2015-4-24 WZ
     */
    function sendEmail($tomail, $subject, $body, $attachment)
    {
        $mail = new AiiEmail();
        $mail->setQQ(EMAIL_USERNAME, EMAIL_PASSWORD);
        return $mail->send($tomail, $subject, $body, $attachment);
    }
    
    /**
     * 获取图片
     * @param unknown $id
     * @version 2015-8-17 WZ
     */
    public function getImagePath($id) {
        if (isset($this->images[$id])) {
            return $this->images[$id];
        }
        $item = array(
            'id' => $id,
            'path' => '',
        );
        if ($id) {
            $data = $this->getImageTable()->getOne(array('id' => $id));
            if ($data) {
                $item['path'] = $data['path'] . $data['filename'];
            }
        }
        $this->images[$id] = $item;
        return $item;
    }
    
    /**
     * 统一返回系统设置
     * 
     * @return multitype:string 
     * @version 2015-11-23 WZ
     */
    public function getSetting() {
        return array(
            'goodsPrice' => GOODS_PRICE,
            'goodsDiscountPrice' => GOODS_DISCOUNT_PRICE,
            'title' => GOODS_TITLE,
            'content' => GOODS_CONTENT,
        );
    }
    
    /**
     * 生成财务号或订单号
     * 规则为年月日时分秒+两位随机数
     *
     * @return string
     * @version 2015-8-11 WZ
     */
    public function makeSN() {
        $sn = date('YmdHis') . mt_rand(10, 99);
        return (string) $sn;
    }
    
    /**
     * 获取二度好友数据
     * 
     * @version 2015-12-30 WZ
     */
    public function getUserRelation($user_id) {
        $filename =  'relationship/'.$user_id;
        $cache = $this->getCache($filename, 1);
        if (! $cache) {
            $cache = array();
            //查找一度好友
            $user_id = $this->getUserId();
            $relation_a = $this->getUserRelationTable()->fetchAll(array('user_id_1' => $user_id, 'attention' => 3));
            //$relation_a = $this->getAll($this->getViewUserOneRelationTable(), array('user_id_1' => $user_id));
            $user_2_id = array();
            $user_2_id_where = array();
            foreach ($relation_a as $val){
                $user_2_id[$val['user_id_2']] = 0;
                $user_2_id_where[] = $val['user_id_2'];
            }
            if(!$user_2_id_where){
                $cache['deep1'] = $user_2_id;
                $cache['deep2'] = array();
                $this->setCache($filename, $cache, 1);
                return $cache;
            }
            //查找二度好友
            $where = new where();
            $where->in('user_id_1', $user_2_id_where);
            $where->notEqualTo('user_id_2', $user_id);
            $where->equalTo('attention', 3);
            $data = $this->getUserRelationTable()->fetchAll($where);
            $user_1_id = array();
            foreach ($data as $value) {
                if (array_key_exists($value['user_id_2'], $user_2_id)) {
                    $user_2_id[$value['user_id_2']] ++; // 跟好友的共同好友+1
                }
                elseif (array_key_exists($value['user_id_2'], $user_1_id)){
                    $user_1_id[$value['user_id_2']]++; // 跟二度好友的共同好友+1
                }
                else {
                    $user_1_id[$value['user_id_2']] = 1; //第一次发现这位二度好友
                }
            }
            arsort($user_1_id); // 根据二度好友的共同好友数量排序，大到小
            $cache['deep1'] = $user_2_id;
            $cache['deep2'] = $user_1_id;
            
            $this->setCache($filename, $cache, 1);
        }
        return $cache;
        
    }
   public function getConnect($data){
       $ws = new AiiWebSocket();
       $ws->connect(WEBSOCK_HOST,WEBSOCK_PORT);
       $jsons = json_encode($data);
       return $ws->send($jsons);
       
   }
   
   public function statVisit($type,$value, $foreign_id, $foreign_id_1)
   {
       $data['type'] = $type;  
       $data['foreign_id'] = $foreign_id;
       $data['foreign_id_1'] = $foreign_id_1;
       
       $visit = $this->getStatisticsDayTable()->getOne($data);
       if(!$visit){
           $date=date('Y-m-d',strtotime(date('Y-m-d',time())));
           $data['date'] = $date;
           $data['timestamps'] = date("Y-m-d H:i:s",time());
           $this->getStatisticsDayTable()->insertData($data);
           if($type == 8){
               $user = $this->getUserTable()->getOne(array('id' => $foreign_id));
               if($user['socket_switch'] == 1){
                   $json=array('n'=>'Send','q'=>array('type'=>1,'userId'=>$foreign_id,'data'=>array('my_visitors'=>'1')));
                   $this->getConnect($json);
               }else{
                   $this->getUserTable()->updateKey($foreign_id,1,'my_visitors',1);
               }
           }
       }else{
           if($type == 8){
               if($visit['date'] == date("Y-m-d",time())){
                   $this->getStatisticsDayTable()->updateData(array(
                       'timestamps' => date("Y-m-d H:i:s", time()),
                   ),$data);
               }else{
                   $this->getStatisticsDayTable()->updateData(array(
                       'timestamps' => date("Y-m-d H:i:s", time()),
                       'date' => date("Y-m-d H:i:s",time())
                   ),$data);
                   $user = $this->getUserTable()->getOne(array('id' => $foreign_id));
                   if($user['socket_switch'] == 1){
                       $json=array('n'=>'Send','q'=>array('type'=>1,'userId'=>$foreign_id,'data'=>array('my_visitors'=>'1')));
                       $this->getConnect($json);
                   }else{
                       $this->getUserTable()->updateKey($foreign_id,1,'my_visitors',1);
                   }
               }
           }
       }
   
   }
   
   /**
    * 
    * 红包算法
    * 
    * @param unknown $LeftMoneyPackage
    * @param string $leftMoneyPackage
    * @return number*/
   
   public function getRandomMoney($LeftMoneyPackage, $leftMoneyPackage = null)
   {
       // remainSize 剩余的红包数量
       // remainMoney 剩余的钱
       if ($LeftMoneyPackage['remainSize'] == 1) {
           $LeftMoneyPackage['remainSize'] --;
           return (double) round($LeftMoneyPackage['remainMoney'] * 100) / 100;
       }
       (double) $min = 0.01;
       (double) $max = $LeftMoneyPackage['remainMoney'] / $LeftMoneyPackage['remainSize'] * 2;
       (double) $money = $this->randomFloat() * $max;
       $money = $money <= $min ? 0.01 : $money;
       $money = floor($money * 100) / 100;
       $LeftMoneyPackage['remainSize'] --;
       $LeftMoneyPackage['remainMoney'] -= $money;
       return $money;
   }
   
   public function randomFloat($min = 0, $max = 1)
   {
       return $min + mt_rand() / mt_getrandmax() * ($max - $min);
   }
   
   
}

