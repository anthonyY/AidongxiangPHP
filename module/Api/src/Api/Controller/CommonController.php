<?php
namespace Api\Controller;

use AiiLibrary\UploadFile\UploadfileApi;
use AiiLibray\WxPayApi\AiiWxPay;
use AiiLibrary\UploadFile\File;
use Admin\Controller\Table;
use Api\Controller\Common\Login;
use Api\Controller\Common\Structure;
use Api\Controller\Common\Request;
use Api\Controller\Common\TableRequest;
use Api\Controller\Common\WhereRequest;
use Api\Controller\Common\Response;
use Admin\Controller\CommonController as AdminController;

class CommonController extends Table
{

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
     * MD5验证时间长度
     *
     * @var unknown
     */
    const TIME_LIMIT = 60;

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
     * @var string
     *      JSON
     *      或
     *      HTML
     */
    public $output = '';

    /**
     * 缓存时间，缓存协议用到
     *
     * @var date
     *      格式：????-??-??
     *      ??:??:??
     */
    public $timestampLeast;

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

    /**
     * 用户对象
     */
    public $login;

    /**
     * 协议开始运行时间
     * @var microtime();
     */
    public $startTime;

    /**
     * model 模型对像
     * @var
     */
    protected $tableObj;

    public function __construct()
    {
        parent::__construct();

        $structure = $this->initializeStructure(); // 初始化读取结构
        $this->setStructure($structure);
        $this->setRequest(); // 获取POST的数据，初始化一些数据

        if(!$this->login){
            $this->login = new Login();
        }
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
        if(!$this->myRequest){
            $this->myRequest = new Request();
        }

        if($this->myTableRequest){
            $this->myRequest->table = $this->myTableRequest;
        }elseif(!$this->myRequest->table){
            $this->myRequest->table = new TableRequest();
        }
        $this->myTableRequest = $this->myRequest->table;

        if($this->myWhereRequest){
            $this->myRequest->table->where = $this->myWhereRequest;
        }elseif(!$this->myRequest->table->where){
            $this->myRequest->table->where = new WhereRequest();
        }
        $this->myWhereRequest = $this->myRequest->table->where;

        $structure->query = $this->myRequest;
        $this->query = $this->myRequest;

        return $structure;
    }

    /**
     * 设置参数结构，转化POST的传参。
     *
     * @param array $structure
     *            结构数组
     */
    public function setStructure($structure)
    {
        array_push($this->structure, $structure);
    }

    /**
     * 获取JSON
     * 自动将参数写入属性
     */
    public function setRequest()
    {
        $json = isset($_REQUEST['json']) ? $_REQUEST['json'] : false;
        if(!$json){
            return false;
        }
        $jsonArray = json_decode($json);
        if(!$jsonArray){
            $jsonArray = json_decode(base64_decode($json));
        }
        foreach($this->structure as $childStructure){
            $this->getJson($this, $jsonArray, $childStructure);
        }
        unset($this->structure);

        /*
         * 分页属性给默认值
         */
        $this->query->table->limit = !empty($this->query->table->limit) ? $this->query->table->limit : '20';
        $this->query->table->page = !empty($this->query->table->page) ? $this->query->table->page : '1';
        $this->query->table->order_type = !empty($this->query->table->order_type) ? $this->query->table->order_type : '1';
        $this->query->table->order_by = !empty($this->query->table->order_by) ? $this->query->table->order_by : '1';
    }

    /**
     * Json对象的属性转化成本对象的属性
     *
     * @param Object $obj
     * @param Object $query
     * @param array $structure
     * @author
     *         WZ
     * @version
     *          1.0.140514
     *          WZ
     */
    public function getJson($this_obj, $json_query, $structure)
    {
        foreach($structure as $key => $item){
            if(!$item){
                // echo
                // $key
                // .
                // "1<br
                // />";
                $this_obj->$key = isset($json_query->$key) ? $json_query->$key : "";
            }elseif('string' == gettype($item)){
                // echo
                // $key
                // .
                // "2<br
                // />";
                $this_obj->$key = isset($json_query->$item) ? $json_query->$item : "";
            }elseif('object' == gettype($item)){
                // echo
                // $key
                // .
                // "3<br
                // />";
                if(!isset($this_obj->$key) || !is_object($this_obj->$key)){
                    $this_obj->$key = (object)array();
                }

                $special_key = array('query' => 'q', 'table' => 'ta', 'where' => 'w');
                if(array_key_exists($key, $special_key)){
                    $json_key = $special_key[$key];
                }else{
                    $json_key = $key;
                }
                $this->getJson($this_obj->$key, isset($json_query->$json_key) ? $json_query->$json_key : "", $item);
            }
        }
    }

    /**
     * 检查用户是否已经登录
     *
     * @param $check 是否必须登录
     * @author
     *         WZ
     * @version
     *          1.0.140513
     *          WZ
     *          简化判断流程
     */
    protected function checkLogin($check = true)
    {
        // 查看是否空
        if($this->login->user_id != 'userId' && $this->login->user_id && LOGIN_STATUS_LOGIN == $this->login->status){
            $login = $this->login;
            $user_model = $this->getUserTable();
            $user_model->id = $login->user_id;
            $user_info = $user_model->getDetails();

            if(!$user_info){
                // 用户不存在
                $this->response(STATUS_USER_NOT_EXIST);
            }
            if(STATUS_STOP == $user_info->status){
                // 禁用用户
                $this->response(STATUS_USER_CANCEL);
            }
            return $this->login;
        }

        $session_id = $this->getSessionId();

        if(empty($session_id)){
            // session
            // 为空
            if($check){
                $this->response(STATUS_SESSION_EMPTY);
            }else{
                return false;
            }
        }else{
            // 非空就查
            $login_model = $this->getLoginTable();
            $login_model->sessionId = $session_id;
            $login = $login_model->getDetails();
        }

        if($login){
            if(LOGIN_STATUS_OTHER_LOGIN == $login->status && $check){
                // 用户在别处登录
                // 1107
                $this->response(STATUS_USER_OTHER_LOGIN);
            }
            if(LOGIN_STATUS_LOGIN != $login->status && $check){
                // 非登录状态
                // 1100
                $this->response(STATUS_USER_NOT_LOGIN);
            }
            if($login->expire < $this->getTime() && $check){
                // session
                // id
                // 会话过期
                // 1012
                $this->response(STATUS_SESSION_TIMEOUT);
            }
            if($login->user_id && $check){
                $user_model = $this->getUserTable();
                $user_model->id = $login->user_id;
                $user_info = $user_model->getDetails();

                if(!$user_info){
                    // 用户不存在
                    $this->response(STATUS_USER_NOT_EXIST);
                }
                if(STATUS_STOP == $user_info->status){
                    // 禁用用户
                    $this->response(STATUS_USER_LOCKED);
                }
            }

            $this->setLoginInfo($login); // 记录登录信息
            if(isset($user_info)){
                return $user_info;
            }
        }elseif($check){
            // session
            // id
            // 为空或不存在
            // 1002
            $this->response(STATUS_SESSION_EMPTY);
        }
    }

    /**
     * 协议结果json类型输出，区分公共属性和其它属性，其它属性都在default
     *
     * @param
     *            number|object
     *            参数：状态或对象
     * @version
     *          1.0.140513
     *          WZ
     *          修改了返回结果，描述根据status返回。
     */
    public function response($param = null)
    {
        if($param){
            $this->setResponse($param);
        }
        $response = array('n' => $this->namespace, 's' => $this->session_id);
        $response['q'] = array();
        if(!$this->myResponse){
            $this->initializeResponse();
        }
        foreach($this->myResponse as $key => $item){
            switch($key){
                case 'status':
                    $response['q']['s'] = $item;
                    $response['q']['d'] = (!isset($response['q']['d']) || empty($response['q']['d'])) ? $this->api_err($response['q']['s']) : '';
                    break;
                case 'description':
                    if($item){
                        $response['q']['d'] = $item;
                    }
                    break;
                case 'timestamp':
                case 'total':
                case 'id':
                    if($item !== null){
                        $response['q'][$key] = $item;
                    }
                    break;
                default:
                    $response['q'][$key] = $item;
                    break;
            }
        }
        if(!isset($response['q']['s'])){
            // 防止忘记返回状态码
            $response['q']['s'] = STATUS_NOSTATUS;
        }
        if(!isset($response['q']['d'])){
            // 防止忘记返回状态描述
            $response['q']['s'] = $this->api_err($response['q']['s']);
        }
        if(!isset($response['q']['t'])){
            $response['q']['t'] = $this->getTime();
        }

        if($this->startTime){
            list($usecStart, $secStart) = explode(' ', $this->startTime);
            list($usecEnd, $secEnd) = explode(' ', microtime());
            $response['tt'] = ($secEnd - $secStart) * 1000 + (int)(($usecEnd - $usecStart) * 1000);
        }
        $json = json_encode($response);
        $json = str_replace('null', '""', $json);
        die($json);
    }

    /**
     * 设置返回结果
     *
     * @param array|object $response
     */
    public function setResponse($response)
    {
        $this->initializeResponse();
        if($response instanceof Response){
            foreach($response as $key => $item){
                $this->myResponse->$key = $item;
            }
        }elseif(is_array($response)){
            foreach($response as $key => $item){
                $this->myResponse->$key = $item;
            }
        }elseif(is_string($response) || is_int($response)){
            $this->myResponse->status = $response;
        }
    }

    /**
     * 初始化返回结果，对象化初始结果
     */
    private function initializeResponse()
    {
        if(!$this->myResponse){
            $this->myResponse = new Response();
        }
    }

    /**
     * 协议返回信息描述处理
     *
     * @param string $err_type
     *            返回的状态码
     * @param string $err_info
     *            自定义返回信息
     * @version 1.0.140513
     *
     *          WZ
     *          由数组定义返回结果描述，改变成用常量定义
     *          详见根目录status_config.php
     */
    private function api_err($err_type, $err_info = '')
    {
        // 状态列表详见status_config.php
        if(2 == IS_DEBUG){
            $prefix = 'REAL_DESCRIPTION_';
        }else{
            $prefix = 'DESCRIPTION_';
        }

        $description = (defined($prefix . $err_type) ? // 是否已经定义的描述
            constant($prefix . $err_type) : constant($prefix . STATUS_NOSTATUS)); // 忘记定义状态

        $err_info = $err_info ? // 自定义返回信息
            $err_info : $description;
        return $err_info;
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
     * 获得会话Id，session_id
     *
     * @return String
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * 设置基本的用户属性
     *
     * @param unknown $user_info
     */
    private function setLoginInfo($login)
    {
        if(!$this->login){
            $this->login = new Login();
        }
        foreach($this->login as $key => $item){
            $this->login->$key = isset($login->$key) ? $login->$key : 0;
        }
    }

    /**
     * 版本判断
     *
     * @return bool
     *         true
     *
     *         版本通过
     *
     *         false
     *
     *         版本不通过
     * @param $version_check 需要判断的版本
     * @param $version 当前移动端提交的版本
     * @version
     *          2015年10月12日
     * @author
     *         liujun
     */
    public function versionJudgment($version_check)
    {
        /*  if ($this->login->expiry < $this->getTime()) { // 会话过期就返回错误
             return $this->response(STATUS_SESSION_TIMEOUT);
         } */
        $version = str_replace('.', '', $this->login->version); // 当前协议提交的版本
        $version_check = str_replace('.', '', $version_check);

        $version = $version < 100 ? $version . '0' : $version;//小于三位数加0补足
        $version_check = $version_check < 100 ? $version_check . '0' : $version_check;

        $version = $version > 999 ? substr($version, 0, 3) : $version;//大于三位数截取前三位
        $version_check = $version_check > 999 ? substr($version_check, 0, 3) : $version_check;
        if($version_check > $version){ //版本判断
            return false;
        }
        return true;
    }

    /**
     * 2014/3/21
     * 给列表增加key
     *
     * @author
     *         WZ
     * @param array $list
     * @param string $key
     * @return string
     */
    public function addKey($list, $key)
    {
        $formatList = array();
        foreach($list as $item){
            $formatList[][$key] = $item;
        }
        return $formatList;
    }

    /**
     * 2014/3/21
     * 给列表去除key
     *
     * @author
     *         WZ
     * @param array $list
     * @param string $key
     * @return multitype:unknown
     */
    public function removeKey($list, $key)
    {
        $formatList = array();
        foreach($list as $item){
            $formatList[] = $item[$key];
        }
        return $formatList;
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

    public function getLogin()
    {
        return $this->login;
    }

    /**'
     * 设置用户登录信息
     * @param int $user_id
     * @param number $user_type 1用户 2商家
     * @version 2016年3月3日
     * @author liujun
     */
    public function setUserId($user_id, $user_type = 1)
    {
        $user_id = (int)$user_id;
        $user_model = $this->getUserTable();
        $user_model->id = $user_id;
        $user = $user_model->getDetails();
        if($user)
        {
            $this->login->user_id = $user_id;
            $this->login->status = LOGIN_STATUS_LOGIN;
            $this->login->user_type = $user_type; // 用户
            $this->login->user_name = $user->name;
            $this->login->version = API_VERSION;
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

    /**
     * 获取Post过来的Query对象，防止
     *
     * @return \Api\Controller\Common\Request
     */
    public function getAiiRequest()
    {
        return $this->query;
    }

    /**
     * 获取Post过来的Query对象里面的table的where对象
     *
     * @return \Api\Controller\Common\WhereRequest
     */
    public function getTableWhere()
    {
        foreach($this->myWhereRequest as $key => $value){
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
     * 获取json数据，以数组的形式返回，尽量不要使用这个方法获取json数组。
     * 除非遇到传送的变量数量不确定的时候。
     *
     * @return boolean
     *         array
     */
    public function getJsonObject()
    {
        $json = isset($_REQUEST['json']) ? $_REQUEST['json'] : false;
        if(!$json){
            return false;
        }
        $json = json_decode($json);
        return $json;
    }

    /**
     * 检查缓存是否可用，可用立即退出。
     *
     * @param unknown $filename
     * @return Ambigous
     *         <boolean,
     *         \Api\Controller\mixed,
     *         mixed>
     */
    public function checkCacheFile($filename)
    {
        $timestampLeast = $this->getTimestampLeast();
        $ctime = $this->getCacheTime($filename); // 缓存更新时间
        if(!$ctime){
            return false;
        }
        if(strtotime($timestampLeast) >= $ctime){
            // 缓存时间大于文件生成时间就不用返回整个列表啦
            $this->response(STATUS_CACHE_AVAILABLE); // 1020
            // 缓存数据可用
        }
        $cache = $this->getCache($filename);
        return $cache;
    }

    public function getTimestampLeast()
    {
        return $this->timestampLeast ? $this->timestampLeast : '0000-00-00 00:00:00';
    }

    /**
     * 获取文件的修改时间
     *
     * @param string $filename
     * @return boolean|number
     */
    public function getCacheTime($filename)
    {
        $filename = $this->getCacheFilename($filename);
        if(!is_file($filename)){
            return false;
        }
        return filemtime($filename);
    }

    /**
     * 生成文件路径
     *
     * @param string $filename
     * @return string
     */
    public function getCacheFilename($filename)
    {
        return APP_PATH . '/Cache/' . $filename . '.php';
    }

    /**
     * 获得缓存
     *
     * @param unknown $filename
     *            文件名格式
     *            region
     *            或
     *            Admin/category
     * @return boolean
     *         mixed
     */
    public function getCache($filename)
    {
        $filename = $this->getCacheFilename($filename);
        if(!is_file($filename)){
            return false;
        }
        $data = file_get_contents($filename);
        if($data){
            $param = json_decode($data);
            return $param;
        }

        return false;
    }

    /**
     * 2014/3/28
     * 根据坐标给出一个方形四角的经纬度
     *
     * @author
     *         WZ
     * @param float $centerX
     *            经度
     * @param float $centerY
     *            纬度
     * @param number $type
     *            周边距离_N
     * @return array
     *         二维数组
     */
    public function getCornersCoordinate($centerX, $centerY, $type)
    {
        if(!$centerX || !$centerY){
            return false;
        }
        $length = "";
        switch($type){
            case 1:
                $length = DISTANCE_1;
                break;
            case 2:
                $length = DISTANCE_2;
                break;
            case 3:
                $length = DISTANCE_3;
                break;
            default:
                $length = DISTANCE_1;
                break;
        }

        $diffCoordinateX = $this->getCoordinatesDifference($length, "x", $centerY); // 经度
        $diffCoordinateY = $this->getCoordinatesDifference($length, "y"); // 纬度

        $positionLeft = round($centerX - $diffCoordinateX, 6); // 方形左侧经度
        $positionRight = round($centerX + $diffCoordinateX, 6); // 方形右侧经度
        $positionDown = round($centerY - $diffCoordinateY, 6); // 方形下侧纬度
        $positionUp = round($centerY + $diffCoordinateY, 6); // 方形上侧纬度

        return array(array($positionLeft, $positionRight), array($positionDown, $positionUp));
    }

    /**
     * 2014/3/28
     * 根据长度获取度数差
     *
     * @author
     *         WZ
     * @param float $length
     *            长度
     * @param string $type
     *            x表示经度
     *            y表示纬度
     * @param string $value
     *            计算经度的时候需要用到纬度
     * @return number
     *         返回度数差
     */
    public function getCoordinatesDifference($length, $type, $value = "")
    {
        $diffCoordinate = 0.00;
        switch($type){
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
     * 排序
     * ，从大到小，冒泡
     *
     * @author
     *         WZ
     * @param array $list
     *            longitude
     *            和
     *            latitude
     *            必须要有
     * @param float $centerX
     *            longitude
     *            经度
     * @param float $centerY
     *            latitude
     *            纬度
     * @return array
     */
    public function sortCoordinates($list, $centerX, $centerY)
    {
        $total = count($list);
        for($i = 0; $i < $total - 1; $i++){
            for($j = $i + 1; $j < $total; $j++){
                $length_i = sqrt(pow($list[$i]["longitude"] - $centerX, 2) + pow($list[$i]["latitude"] - $centerY, 2)); // i下标到中心的距离
                $length_j = sqrt(pow($list[$j]["longitude"] - $centerX, 2) + pow($list[$j]["latitude"] - $centerY, 2)); // j下标到中心的距离
                if($length_i > $length_j){
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
     * @return float
     *         千米
     */
    public function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $radLat1 = $this->rad($lat1);
        $radLat2 = $this->rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($lng1) - $this->rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * EARTH_RADIUS;
        $s = round($s, 1);
        // $s
        // =
        // round($s,
        // 2);
        return $s; // 千米
    }

    public function rad($d)
    {
        return $d * M_PI / 180.0;
    }


    /**
     * 初始化一个model
     * @return object
     */
    public function initModel()
    {
        $query_table = $this->getTable();
        $this->tableObj->page = $query_table->page;
        $this->tableObj->limit = $query_table->limit;
        $this->tableObj->orderBy = $this->OrderBy($query_table->order_by) . ' ' . $this->OrderType($query_table->order_type);
        return $this->tableObj;
    }

    /**
     * 获取Post过来的Query对象里面的table
     *
     * @return \Api\Controller\Common\TableRequest
     */
    public function getTable()
    {
        foreach($this->myTableRequest as $key => $value){
            $this->myTableRequest->$key = $this->query->table->$key;
        }
        return $this->myTableRequest;
    }

    /**
     *
     * @author
     *         hexin
     *
     * @date
     *         2014.3.18
     * @abstract
     *           order_by
     *           1、timestamp
     * @param number $order_by
     * @return string
     */
    public function OrderBy($order_by = 1)
    {
        switch($order_by){
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
     *
     * @author
     *         hexin
     *
     * @date
     *         2014.3.18
     * @abstract
     *           order_by
     *           1、DESC
     *           2、ASC
     * @param number $order_type
     * @return string
     */
    public function OrderType($order_type = 1)
    {
        if($order_type == 1){
            $result = 'DESC';
        }elseif($order_type == 2){
            $result = 'ASC';
        }else{
            $result = 'DESC';
        }
        return $result;
    }

    /**
     * 生成随机字符串
     *
     * @param number $length
     *            长度
     * @param number $type
     *            类型
     *            1大写；2小写；3大小写混合；4数字；5大写+数字；6小写+数字；7大小写+数字；
     * @return string
     */
    public function makeCode($length, $type)
    {
        $uppercase_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // 1
        $lowercase_chars = 'abcdefghijklmnopqrstuvwxyz'; // 2
        $number_chars = '0123456789'; // 4

        $chars = '';
        if($type & self::CODE_TYPE_UPPERCAS){
            $chars .= $uppercase_chars;
        }
        if($type & self::CODE_TYPE_LOWERCASE){
            $chars .= $lowercase_chars;
        }
        if($type & self::CODE_TYPE_NUMBER){
            $chars .= $number_chars;
        }

        $code = '';
        for($i = 0; $i < $length; $i++){
            $key = mt_rand(0, strlen($chars) - 1);
            $code .= $chars[$key];
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
        foreach($item as $key => $value){
            $item->$key = '';
        }
    }

    /**
     * @param $file_key
     * @return multitype|array
     * 上传文件总入口
     */
    public function uploadImageForController($file_key)
    {
        $this->file_key = $file_key;
        $data = array();
        if (! isset($_FILES[$this->file_key])) {
            return array(
                'ids' => array(),
                'files' => array(),
            );
        }
        if(is_array($_FILES[$this->file_key]['name']))
        {
            foreach($_FILES[$this->file_key]['name'] as $key => $value)
            {
                if(! $_FILES[$this->file_key]['error'][$key])
                {
                    $source_file = array(
                        $this->file_key => array(
                            'name' => array($_FILES[$this->file_key]['name'][$key]),
                            'type' => array($_FILES[$this->file_key]['type'][$key]),
                            'tmp_name' => array($_FILES[$this->file_key]['tmp_name'][$key]),
                            'error' => array($_FILES[$this->file_key]['error'][$key]),
                            'size' => array($_FILES[$this->file_key]['size'][$key])
                        )
                    );
                    $data[] = $this->checkFileMd5($source_file);
                }
            }
        }
        else
        {
            if(! $_FILES[$this->file_key]['error'])
            {
                $source_file = array(
                    $this->file_key => array(
                        'name' => array($_FILES[$this->file_key]['name']),
                        'type' => array($_FILES[$this->file_key]['type']),
                        'tmp_name' => array($_FILES[$this->file_key]['tmp_name']),
                        'error' => array($_FILES[$this->file_key]['error']),
                        'size' => array($_FILES[$this->file_key]['size'])
                    )
                );
                $data[] = $this->checkFileMd5($source_file);
            }
        }

        $files = $this->saveFileInfo($data);
        return $files;
    }

    /**
     * 通过对图片的md5验证，查看图片是否存在，<br />
     * 如果存在返回数据库中的图片信息，<br />
     * 如果不存在，上传新图片，再返回图片信息<br />
     *
     * @param array $source_file
     * @return array|Ambigous <multitype:NULL number string >
     * @version 2014-12-6 WZ
     */
    public function checkFileMd5($source_file)
    {
        if (is_array($source_file[$this->file_key]['tmp_name']))
        {
            if(isset($source_file[$this->file_key]['data'][0]))
            {
                $content = $source_file[$this->file_key]['data'][0];
            }
            else
            {
                $content = $this->getUrlImage($source_file[$this->file_key]['tmp_name'][0]);
                $source_file[$this->file_key]['data'][0] = $content;
            }
        }
        else
        {
            if(isset($source_file[$this->file_key]['data']))
            {
                $content = $source_file[$this->file_key]['data'];
            }
            else
            {
                $content = $this->getUrlImage($source_file[$this->file_key]['tmp_name']);
                $source_file[$this->file_key]['data'] = $content;
            }
        }
        $md5 = md5($content);
        $image_table = $this->getImageTable();
        $image_table->md5 = $md5;
        $data = $image_table->getMd5();
        if ($data)
        {
            return (array)$data;
        }
        else
        {
            $data = $this->Uploadfile(LOCAL_SAVEPATH, true, 1, 8192, $source_file);
            return $data[0];
        }
    }

    /**
     * 获取图片内容
     * @param unknown $path
     * @return mixed
     * @version 2014-12-16 WZ
     */
    public function getUrlImage($path)
    {
        if (preg_match('/http\:\/\//i', $path))
        {
            $cookie_file = tempnam('./temp','cookie');
            $url = $path;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
            $content = curl_exec($ch);
        }
        else {
            $content = file_get_contents($path);
        }

        return $content;
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
        $image_table = $this->getImageTable();
        foreach ($data as $key => $value)
        {

            if(! isset($value['id']) && isset($value['filename']) && isset($value['path']) && $value['filename'] && $value['path'])
            {
                $image_table->filename = $value['filename'];
                $image_table->path = $value['path'];
                $id = $image_table->addData($value);
                $ids[] = $id;
                $files[] = array(
                    'id' => $id,
                    'path' => $value['path'].$value['filename'],
                );
            }
            else
            {
                $image_table->updateKey($value['id'], 1, 'count', 1);
                $ids[] = $value['id'];
                $files[] = array(
                    'id' => $value['id'],
                    'path' => $value['path'].$value['filename'],
                );
            }
        }

        return array(
            'ids' => $ids,
            'files' => $files
        );
    }

    /**
     * 上传文件处理
     *
     * @author liujun
     * @param string $pash
     *            要上传到的文件夹 默认为public 下的uploadfiles/年月命名的文件夹（此文件夹为大图文件夹）
     * @param boolean $is_thumb
     *            是否生成缩略图 默认为否false，true为是
     * @param integer $filetype
     *            1,为图片类;2,swf类;3,音频类;4,文本文件类;5,可执行文件类; 默认为 1图片类
     * @param integer $size
     *            设置上传最大文件的大小（与PHP配置文件有关）此项默认为：2M
     * @return array $array array('filename','path','size','mime','extension')
     */
    public function Uploadfile($path = LOCAL_SAVEPATH, $is_thumb = true, $filetype = 1, $size = 2048, $source_file = array())
    {
        set_time_limit(0);
        $upload = new UploadfileApi($path, $size, $filetype, 'Ymd');
        if ($source_file)
        {
            $upload->setFiles($source_file);
        }
        $expression = $upload->uploadfile($path);
        $filename = $upload->getUploadFileInfo();
        $path = $upload->imgPath;
        // $extension = substr($name, (strrpos($name, '.') + 1));
        $results = array();

        if (! is_array($filename[$this->file_key]['new_name']))
        {
            foreach ($filename[$this->file_key] as $f_key => $f_value)
            {
                $filename[$this->file_key][$f_key] = array($f_value);
            }
        }
        foreach ($filename[$this->file_key]['new_name'] as $key => $value)
        {
            $name = substr($filename[$this->file_key]['new_name'][$key], strrpos($filename[$this->file_key]['new_name'][$key], '/') + 1);

            if($filename[$this->file_key]['size'][$key] > 0)
            {
                $results[] = array(
                    'filename' => $name,
                    'path' => $path,
                    'md5' => $filename[$this->file_key]['md5'][$key],
                    'width' => isset($filename[$this->file_key]['width']) ? $filename[$this->file_key]['width'][$key] : 0,
                    'height' => isset($filename[$this->file_key]['height']) ? $filename[$this->file_key]['height'][$key] : 0,
                    'count' => 1
                );
            }
        }

        return $results;
    }


    /**
     * 格式化
     * region_info
     * 字段（转为JSON，用于插入数据库）
     *
     * @author
     *         liujun
     * @param integer $county
     *            区域ID
     * @param integer $city
     *            城市ID
     * @param integer $province
     *            省份直辖市ID
     * @return string
     *         region_info
     *         JSON数据
     *
     */
    protected function encode($county, $city, $province)
    {
        $region_info = array();
        if($province > 1){
            $res = $this->getRegionTable()->getOne(array('id' => $province));
            $province_info = array("id" => $res->id, "name" => $res->name, "parent_id" => 1, "pinyin" => $res->pinyin);
            $region_info[] = array("region" => $province_info);
        }
        if($city > 1){
            $res = $this->getRegionTable()->getOne(array('id' => $city));
            $city_info = array("id" => $res->id, "name" => $res->name, "parent_id" => $res->parent_id, "pinyin" => $res->pinyin);
            $region_info[] = array("region" => $city_info);
        }
        if($county > 1){
            $res = $this->getRegionTable()->getOne(array('id' => $county));
            $county_info = array("id" => $res->id, "name" => $res->name, "parent_id" => $res->parent_id, "pinyin" => $res->pinyin);
            $region_info[] = array("region" => $county_info);
        }
        return $this->JSON($region_info);
    }

    /**
     *
     * 将数组转换为JSON字符串（兼容中文）
     *
     * @param array $array
     * @return string
     * @access
     *         public
     */
    protected function JSON($array)
    {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }

    /**
     *
     * 使用特定function对数组中所有元素做处理
     *
     * @param
     *            string
     *            &$array
     *            要处理的字符串
     * @param string $function
     * @return boolean
     * @access
     *         public
     *
     *
     */
    protected function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if(++$recursive_counter > 1000){
            die('possible deep recursion attack');
        }
        foreach($array as $key => $value){
            if(is_array($value)){
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            }else{
                $array[$key] = $function($value);
            }

            if($apply_to_keys_also && is_string($key)){
                $new_key = $function($key);
                if($new_key != $key){
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }

    /**
     * 查找字符里是否带有敏感关键字词
     *
     * @author
     *         liujun
     * @param string $str
     *            要查找的字符串
     * @return bool|array
     */
    public function findSensitiveWord($str)
    {
        $words = $this->getSensitiveWords();

        if($str && $words){
            $info = array();
            $word = array();
            for($i = 0; $i < count($words); $i++){
                $content = substr_count($str, $words[$i]);
                if($content > 0){
                    $word[] = $words[$i];
                }
            }
            if(count($word) > 0){
                $info = implode($word, ',');
            }

            return $info;
        }else{
            return false;
        }
    }

    /**
     * 读取缓存文件敏感数据
     *
     * @author
     *         liujun
     * @return array
     *         $words
     */
    public function getSensitiveWords()
    {
        (array)$word = $this->getCache('SensitiveWords/words');
        if(!$word){
            return $word;
        }
        $words = array();
        foreach($word as $k => $v){
            $words[] = $k;
        }
        return $words;
    }

    /**
     * 替换敏感关键词
     *
     * @author
     *         liujun
     * @param string $str
     *            要查找替换的内容
     * @return string
     *         $str
     *         替换后的内容
     */
    public function replaceSensitiveWords($str)
    {
        $words = $this->getCache('SensitiveWords/words');
        $str = strtr($str, (array)$words);
        return $str;
    }

    /**
     * 敏感词写入缓存文件
     *
     * @author
     *         liujun
     * @param string $str
     *            格式为
     *            字|字|字
     *            词之间用|线隔开
     */
    public function writtenSensitiveWords($words)
    {
        $str = array();
        if($words){
            $words = array_unique(explode('|', trim($words)));
            foreach($words as $k => $v){
                $strlen = mb_strlen($v, 'utf-8');
                $star = '';
                for($i = 0; $i < $strlen; $i++){
                    $star .= '*';
                }
                $str[$v] = $star;
                $strlen = 0;
            }
        }
        $this->setCache('SensitiveWords/words', $str);
    }

    /**
     * 缓存写入文件
     *
     * @param string $filename
     *            文件名格式
     *            region
     *            或
     *            Admin/category
     * @param array $param
     *            数组，需要缓存的内容
     * @return boolean
     */
    public function setCache($filename, $param)
    {
        $filename = $this->getCacheFilename($filename);
        $file = new File();
        // if
        // (!
        // is_file($filename))
        // {
        // touch($filename);
        // chmod($filename,
        // 0777);
        // }
        if(!is_array($param)){
            $param = array($param);
        }
        $data = json_encode($param);
        // file_put_contents($filename,
        // $data);
        $file->mkFile($filename, $data, true, 0777);
        return true;
    }

    /**
     *
     * @param array $param
     * @version
     *          2015-1-9
     *          WZ
     */
    public function checkMd5($param)
    {
        $string = $param;
        if(is_array($param)){
            $string = implode(',', $param);
        }
        $md5 = md5($string);
        return $md5;
    }


    /**
     * 生成微信预付单并返回支付信息
     *
     * @param number $pqy_type 交易类型 1用户充值 2商品订单 3服务订单
     * @param number $id 消费记录ID或财务流水号
     * @param number $amount 支付金额
     * @param number $type 1. 微信公众号支付 2 .微信H5支付,3微信PC扫码支付,4 App微信支付
     * @param string $body 自定义支付描述
     * @return multitype:number string unknown NULL
     * @version  2015-07-14
     */
    public function getWxPayInfo($pqy_type, $id, $amount, $type = 1, $body = '')
    {
        $list = array(1 => '客天下购商城充值', 2 => '客天下购商城商品订单', 3 => '客天下购商城服务订单');
        if(array_key_exists($pqy_type, $list) && $id && $amount){
            $test_amount = '0.0' . $amount * 100;

            $value = array(
                'total_fee' => IS_DEBUG == 1 ? round($test_amount,2) : $amount,//临时改动上线记得移除
                'body' => $body ? $body : $list[$pqy_type] . $amount . '元', 'out_trade_no' => $id);

            $user_type = $this->getUserType();
            require_once 'AiiLibrary/WxPay/AiiWxPay.php';
            $wxpay = new AiiWxPay();
            if($type == 1){

                return $wxpay->setValue($value, 1)->getJsPay();
            }
            if($type == 2)
            {
                return $wxpay->setValue($value, 1)->getMwebPay();
            }
            if($type == 3)
            {
                return $wxpay->setValue($value, 1)->getNative();
            }
            if($type == 4)
            {
                return $wxpay->setValue($value,1)->getAppParams();
            }
        }else{
            return array();
        }
    }

    public function getUserType()
    {
        $login = $this->login;
        return isset($login->user_type) ? $login->user_type : '';
    }

    /**
     * 生成支付宝请求数据
     *
     * @param number $pqy_type 交易类型 1用户充值 2商品订单 3服务订单
     * @param number $id 支付流水号
     * @param number $amount 支付金额
     * @param string $body 自定义支付描述
     * @return multitype:number string unknown NULL
     * @version  2016-10-11
     */
    public function getAlipayQueryData($pqy_type, $id, $amount, $body = '')
    {
        $list = array(1 => '客天下购商城充值', 2 => '客天下购商城商品订单', 3 => '客天下购商城服务订单');
        if(array_key_exists($pqy_type, $list) && $id && $amount){
            $test_amount = '0.0' . $amount * 100;
            $value = array(
                'total_amount' => IS_DEBUG == 1 ? $test_amount : $amount,
                //临时改动上线记得移除
                'body' => $body ? $body : $list[$pqy_type] . $amount . '元', 'out_trade_no' => $id, 'subject' => $list[$pqy_type] . $amount . '元',);

            $alipaApi = new \AlipayApi();
            foreach($value as $k => $v){
                $alipaApi->$k = $v;
            }
            return $alipaApi->submitPay();
        }else{
            return array();
        }
    }

    /**
     * 支付宝，微信回调业务处理
     *
     * @param integer $out_trade_no 支付流水号
     * @param integer $payment_order 第三方支付号
     * @param integer $pay_type 1微信支付 2支付宝 3余额
     * @return boolean
     * @version 2017年8月16日
     * @author  liujun
     */
    public function notifyTransacting($out_trade_no, $payment_order = '', $pay_type = 0)
    {
        $pay_log_table =  $this->getPayLogTable();
        $pay_log_table->outTradeNo = $out_trade_no;
        $pay_log_table->paymentOrder = $payment_order;
        $pay_log_table->paymentType = $pay_type;
        $res = $pay_log_table->notifyTransacting();
        return $res;
    }

    /**
     * 2014/3/31
     * 根据用户和用户类型发送推送
     *
     * @author
     *         WZ
     * @param number $user_id
     *            用户id数组
     * @param number $user_type
     *            用户类型（1用户，2商家）
     * @param number $contentType
     *            模版编号
     * @param array $args
     *            模版中内容参数
     * @param array $template
     *            自定义模版内容content,title,push_args
     * @return array
     *         (success
     *         ,
     *         fail)
     * @version
     *          1.0.14515
     *          WZ
     *          添加更多插入记录的信息
     * @version
     *          1.0.141103
     *          WZ
     *          添加自定义模版内容
     */
    public function pushForController($user_id, $user_type, $contentType, array $args = array(), $template = array())
    {
        $file = new AiiMyFile(); // 记录日志的类
        $file->setFileToPublicLog();

        if($user_id && $user_type){
            $this->getDeviceUserTable()->updateData(array('notice_number' => new Expression('notice_number + 1')), array('user_id' => $user_id, 'user_type' => $user_type, 'delete' => DELETE_FALSE));     //更新app端的推送信息数量
        }

        $deviceCollection = $this->getDeviceForUser($user_id, $user_type); // 根据id和用户类型查找设备号与设备类型

        if(!$template){
            $template = $this->pushTemplate($contentType, $args); // 根据模版编号获得推送的title和content
        }
        $result = '';
        if($user_id && $template["content"]){
            // 保存到数据库
            $data = array('title' => $template['title'], 'content' => $template['content'], 'from' => $user_type, 'user_id' => $user_id, 'timestamp' => $this->getTime());
            //$res = $this->aiiPush($user_type . ',' . $user_id, $template);
            $res = '';
            $this->getNotificationRecordsTable()->insertData($data);

        }
        if($deviceCollection && $template["content"]){
            // 找到设备和模版信息没问题就开始推送
            if(PUSH_SWITCH && !$res){
                // 开启推送功能
                $result = $this->pushForDeviceCollection($deviceCollection, $template["content"], $template["title"], $template["push_args"]);

            }else{
                // 没开启推送功能
                $result = array('success' => array(1), 'fail' => array());
            }
            if(PUSH_LOG_SWITCH){
                foreach($deviceCollection as $value){
                    $content = "推送" . (PUSH_SWITCH ? '开启' : '没开启') . "， user_id ：$value[user_id] ， user_type ：$value[user_type] ， title ： $template[title] ， content ： $template[content] ，deviceToken：$value[device_token]";
                    $file->putAtStart($content);
                }
            }
        }else{
            // 发送失败也记录
            $result = false;
            if(PUSH_LOG_SWITCH){
                $contentType = (string)$contentType;
                $string_args = (string)implode(",", $args);
                if(!$deviceCollection){
                    $content = "推送，msg：找不到对应的设备号，或对应设备号关闭推送功能，user_id：" . $user_id . "，user_type：" . $user_type . " ，参数：" . $string_args . "，模版：" . $contentType;
                    $file->putAtStart($content);
                }
                if(!$template["content"]){
                    $content = "推送，msg：不能生成content，检查模版类型和参数是否相对应， user_id：" . $user_id . "，user_type：" . $user_type . "，参数：" . $string_args . "，模版：" . $contentType;
                    $file->putAtStart($content);
                }
            }
        }
        return $result;
    }

    /**
     * 2014/3/31
     * 根据用户信息查找用户的设备号和设备类型
     *
     * @author
     *         WZ
     * @param array $user_id
     *            用户id或者司机id，数组
     * @param array $user_type
     *            对应id的类型，数组
     * @return multitype:
     */
    public function getDeviceForUser($user_id, $user_type)
    {
        $where = array('user_id' => $user_id, 'user_type' => $user_type, 'delete' => DELETE_FALSE);
        $data = $this->getDeviceUserTable()->fetchAll($where);
        return $data;
    }

    /**
     * 2014/3/31
     * 推送内容模版设置
     *
     * @author
     *         WZ
     * @param number $type
     * @param
     *            array
     *            其它参数
     * @return array
     *         {string
     *         content
     *         ,string
     *         title}
     *         内容和标题（标题是安卓推送需要的）
     */
    public function pushTemplate($type, array $args = array())
    {
        $template = "";
        $content = "";
        $title = "";
        $push_args = array();
        switch($type){
            case 201:
            case 202:
            case 203:
            case 204:
            case 301:
            case 303:
                $need = 1; // 手动修改，用来判断参数是否足够，不足够会出问题。
                // TEMPLATE_PUSH_TITLE_201
                // TEMPLATE_PUSH_CONTENT_201
                $title = constant('TEMPLATE_PUSH_TITLE_' . $type);
                $template = constant('TEMPLATE_PUSH_CONTENT_' . $type);
                $content = sprintf($template, $args[0]);
                break;
            case 3010:
                $title = constant('TEMPLATE_PUSH_TITLE_' . $type);
                $template = constant('TEMPLATE_PUSH_CONTENT_' . $type);
                $content = sprintf($template, $args[0], $args[1]);
                break;
            case 302:
                $title = constant('TEMPLATE_PUSH_TITLE_' . $type);
                $template = constant('TEMPLATE_PUSH_CONTENT_' . $type);
                $content = sprintf($template, $args[0], $args[1]);
                break;
            case 304:
                $title = constant('TEMPLATE_PUSH_TITLE_' . $type);
                $template = constant('TEMPLATE_PUSH_CONTENT_' . $type);
                $content = sprintf($template, $args[0], $args[1]);
                break;
            case 3040:
                $title = constant('TEMPLATE_PUSH_TITLE_' . $type);
                $template = constant('TEMPLATE_PUSH_CONTENT_' . $type);
                $content = sprintf($template, $args[0], $args[1], $args[2]);
                break;
            case 306:
                $title = constant('TEMPLATE_PUSH_TITLE_' . $type);
                $template = constant('TEMPLATE_PUSH_CONTENT_' . $type);
                $content = sprintf($template, $args[0], $args[1]);
                break;
            case 1000:
                $title = constant('TEMPLATE_PUSH_TITLE_' . $type);
                $template = constant('TEMPLATE_PUSH_CONTENT_' . $type);
                $content = $template;
                $push_args = $args;
                $push_args['type'] = 1000;
                break;
            case 1001:
                $title = constant('TEMPLATE_PUSH_TITLE_' . $type);
                $template = constant('TEMPLATE_PUSH_CONTENT_' . $type);
                $content = $template;
                $push_args = $args;
                $push_args['type'] = 1001;
                break;
            case 1002:
                $title = constant('TEMPLATE_PUSH_TITLE_' . $type);
                $template = constant('TEMPLATE_PUSH_CONTENT_' . $type);
                $content = $template;
                $push_args = $args;
                $push_args['type'] = 1002;
                break;
        }
        return array("content" => $content, "title" => $title, "push_args" => $push_args);
    }

    /**
     * 2014/3/31
     * 根据设备列表，把内容推送出去
     *
     * @author
     *         WZ
     * @param array $device_collection
     *            (id
     *            ,
     *            device_token
     *            ,
     *            device_type
     *            ,user_type)
     * @param string $content
     *            内容
     * @param string $title
     *            标题
     * @param array $push_args
     *            推送用自定义参数
     * @return array
     *         (success
     *         ,
     *         fail)
     */
    public function pushForDeviceCollection($deviceCollection, $content, $title, $push_args = array())
    {
        $push = new AiiPush();
        if($deviceCollection && $content && $title){
            $result = $push->pushCollectionDevice($deviceCollection, $content, $title, $push_args);
        }
        return $result;
    }

    /**
     * 获取银行列表
     * @return multitype:NULL
     * @version 2015年11月4日
     * @author liujun
     */
    public function getBankList()
    {
        $bank_list = $this->getBankListTable()->fetchAll(null, array('id asc'));
        $bank_array = array();
        foreach($bank_list as $v){
            $bank_array[$v->id]['name'] = $v->name;
            $bank_array[$v->id]['image_path'] = $v->image_path;
            $bank_array[$v->id]['id'] = $v->id;
        }
        return $bank_array;
    }

    /**
     * 解析
     * region_info
     * 字段（转为数组，用于模板数据）
     *
     * @author
     *         liujun
     * @param string $result
     *            数据库region_info
     *            JSON数据
     * @return array
     *         array('province'=>省信息数组,'city'=>市信息数组，'county'=>区信息数组)
     */
    protected function decode($result)
    {
        $result_info = array();
        $result = json_decode($result);
        if(isset($result[0]->region->id)){
            $province = array('id' => $result[0]->region->id, 'name' => $result[0]->region->name, 'parentId' => '1', 'pinyin' => $result[0]->region->pinyin);
            $result_info[]['region'] = $province;
        }

        if(isset($result[1]->region->id)){
            $city = array('id' => $result[1]->region->id, 'name' => $result[1]->region->name, 'parentId' => $result[1]->region->parent_id, 'pinyin' => $result[1]->region->pinyin);
            $result_info[]['region'] = $city;
        }

        if(isset($result[2]->region->id)){
            $county = array('id' => $result[2]->region->id, 'name' => $result[2]->region->name, 'parentId' => $result[2]->region->parent_id, 'pinyin' => $result[2]->region->pinyin);
            $result_info[]['region'] = $county;
        }

        return $result_info;
    }

    /**
     * @return AdminController
     * 获取admin模块的公共类
     */
    public function getAdminCommonController()
    {
        return new AdminController();
    }


    /**
     * 打印数据
     */
    public function dump($data)
    {
        echo '<pre>';
        return var_dump($data);
    }

    /**
     * @param $begin_timestamp 开始时间戳
     * @param $end_timestamp 结束时间戳
     * @return string
     * 计算时间差
     */
    public function getTimeDiff($begin_timestamp,$end_timestamp)
    {
        if($end_timestamp <= $begin_timestamp)
        {
            $res = array("day" => '00',"hour" => '00',"min" => '00',"sec" => '00');
        }


        $time_diff = $end_timestamp - $begin_timestamp;
        //计算天数
        $days = intval($time_diff/86400);
        //计算小时数
        $remain = $time_diff%86400;
        $hours = intval($remain/3600);
        $hours = $hours > 9 ? $hours : '0'.$hours;
        //计算分钟数
        $remain = $remain%3600;
        $min = intval($remain/60);
        $min = $min > 9 ? $min : '0'.$min;
        //计算秒数
        $secs = $remain%60;
        $secs = $secs > 9 ? $secs : '0'.$secs;
        $res = array("day" => $days,"hour" => $hours,"min" => $min,"sec" => $secs);
        return $res;
    }

}
