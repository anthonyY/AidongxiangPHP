<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/23
 * Time: 15:55
 */
namespace Api\Model;

use Core\System\Image;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Core\System\UploadfileApi;
use Core\System\UploadfileFromBase64;
use Zend\Db\Sql\Where;
use Api\Controller\Item\PushTemplateItem;
use Api\Controller\Item\PushArgsItem;
use Api\Controller\Item\PushFromItem;
use Core\System\AiiUtility\AiiPush\AiiPush;
use Core\System\AiiUtility\AiiPush\AiiMyFile;
use Core\System\AiiUtility\AiiWxPayV3\AiiWxPay;
use \Zend\Mvc\Controller\Plugin\Url;
use Zend\Db\Sql\Expression;
use Core\System\WxApi\WxApi;


class CommonModel extends PublicTable
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

    private $file_key;
    protected $search;
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        
        $this->initialize();
    }
    
    private $user_types = array();
    
    private $user_lv = array();
    
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
     * 给列表增加key去掉id
     *
     * @author WZ
     * @param array $list
     * @param string $key
     * @return string
     */
    public function addKeyDeleteId($list, $key)
    {
        $formatList = array();
        if (is_array($list)) {
            foreach ($list as $item)
            {
                unset($item->id);
                $item->path = 'thumb/0X0X1/'.$item->path;
                $formatList[][$key] = $item;
            }
        }
        return $formatList;
    }

    /**
     * 发起一个get或post请求
     *
     * @param $url 请求的url            
     * @param string $method
     *            请求方式
     * @param array $params
     *            请求参数
     * @param array $extra_conf
     *            curl配置, 高级需求可以用, 如
     *            $extra_conf = array(
     *            CURLOPT_HEADER => true,
     *            CURLOPT_RETURNTRANSFER = false
     *            )
     * @return bool|mixed
     * @throws Exception
     */
    public static function urlExec($url, $params = array(), $method = 'get', $extra_conf = array())
    {
        // 如果是get请求，直接将参数附在url后面
        if ($method == 'get') {
            $params = is_array($params) ? http_build_query($params) : $params;
            $url .= (strpos($url, '?') === false ? '?' : '&') . $params;
        }
        
        // 默认配置
        $curl_conf = array(
            CURLOPT_URL => $url, // 请求url
            CURLOPT_HEADER => false, // 不输出头信息
            CURLOPT_RETURNTRANSFER => true, // 不输出返回数据
            CURLOPT_CONNECTTIMEOUT => 3
        ); // 连接超时时间

        
        // 配置post请求额外需要的配置项
        if ($method == 'post') {
            // 使用post方式
            $curl_conf[CURLOPT_POST] = true;
            // post参数
            $curl_conf[CURLOPT_POSTFIELDS] = $params;
        }
        
        // 添加额外的配置
        foreach ($extra_conf as $k => $v) {
            $curl_conf[$k] = $v;
        }
        
        $data = false;
        try {
            // 初始化一个curl句柄
            $curl_handle = curl_init();
            // 设置curl的配置项
            curl_setopt_array($curl_handle, $curl_conf);
            $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
            if ($ssl) {
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);
            }
            // 发起请求
            $data = curl_exec($curl_handle);
            if ($data === false) {
                throw new \Exception('CURL ERROR: ' . curl_error($curl_handle));
            }
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        return $data;
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
        if ($type & self::CODE_TYPE_UPPERCAS) {
            $chars .= $uppercase_chars;
        }
        if ($type & self::CODE_TYPE_LOWERCASE) {
            $chars .= $lowercase_chars;
        }
        if ($type & self::CODE_TYPE_NUMBER) {
            $chars .= $number_chars;
        }
        
        $code = '';
        for ($i = 0; $i < $length; $i ++) {
            $code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $code;
    }
    
    /**
     * 基于base64保存图片（配合js插件）
     * 
     * @param unknown $data
     * @return array|Ambigous <>
     * @version 2016-9-7 WZ
     */
    public function uploadImageForBase64($data) {
        $content = base64_decode($data);
        
        $md5 = md5($content);
        $info = $this->getOne(array(
            'md5' => $md5
        ), array("*"), "image");
        
        if (! $info) {
            $upload = new UploadfileFromBase64(LOCAL_SAVEPATH);
            $info = $upload->save($content);
            $info['id'] = $this->insertData($info, "image");
        }
        return array('id' => $info['id'], 'path' => $info['path'] . $info['filename']);
    }

    /**
     * 上传文件总入口
     *
     * @param $_FILES $file
     * @param string $file_key
     *            post过来的key
     * @return Ambigous <\Api\Controller\multitype:multitype:multitype:multitype:unknown, multitype:multitype:multitype:multitype:unknown multitype:string unknown >
     * @version 2014-12-6 WZ
     */
    public function uploadImageForController($file_key)
    {
        $this->file_key = $file_key;
        $data = array();
        if (!isset($_FILES[$this->file_key]))
        {
            return array(
                'ids' => array(),
                'files' => array()
            );
        }
        if (is_array($_FILES[$this->file_key]['name']))
        {
            foreach ($_FILES[$this->file_key]['name'] as $key => $value)
            {
                if (! $_FILES[$this->file_key]['error'][$key])
                {
                    $source_file = array(
                        $this->file_key => array(
                            'name' => array(
                                $_FILES[$this->file_key]['name'][$key]
                            ),
                            'type' => array(
                                $_FILES[$this->file_key]['type'][$key]
                            ),
                            'tmp_name' => array(
                                $_FILES[$this->file_key]['tmp_name'][$key]
                            ),
                            'error' => array(
                                $_FILES[$this->file_key]['error'][$key]
                            ),
                            'size' => array(
                                $_FILES[$this->file_key]['size'][$key]
                            )
                        )
                    );
                    $data[] = $this->checkFileMd5($source_file);
                }
            }
        }
        else
        {
            if (! $_FILES[$this->file_key]['error'])
            {
                $source_file = array(
                    $this->file_key => array(
                        'name' => array(
                            $_FILES[$this->file_key]['name']
                        ),
                        'type' => array(
                            $_FILES[$this->file_key]['type']
                        ),
                        'tmp_name' => array(
                            $_FILES[$this->file_key]['tmp_name']
                        ),
                        'error' => array(
                            $_FILES[$this->file_key]['error']
                        ),
                        'size' => array(
                            $_FILES[$this->file_key]['size']
                        )
                    )
                );
                $data[] = $this->checkFileMd5($source_file);
            }
        }


        $files = $this->saveFileInfo($data);
        return $files;
    }

    public function getImageForController($url) {
        $this->file_key = 'file';
        $result = array(
            'ids' => array(),
            'files' => array()
        );
        $data = array();
        if($url) {
            $source_file = array(
                $this->file_key => array(
                    'name' => array(
                        'random'
                    ),
                    'type' => array(
                        ''
                    ),
                    'tmp_name' => array(
                        $url
                    ),
                    'error' => array(
                        0
                    ),
                    'size' => array(
                        1
                    )
                )
            );
            $data[] = $this->checkFileMd5($source_file);
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
     * @return array|Ambigous number string >
     * @version 2014-12-6 WZ
     */
    public function checkFileMd5($source_file)
    {
        if (is_array($source_file[$this->file_key]['tmp_name']))
        {

            if (isset($source_file[$this->file_key]['data'][0]))
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

            if (isset($source_file[$this->file_key]['data']))
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
        $data = $this->getOne(array(
            'md5' => $md5
        ), array("id"), "image");

        if ($data)
        {
            return (array) $data;
        }
        else
        {
            $data = $this->Uploadfile(LOCAL_SAVEPATH, true, 1, 8192, $source_file);
            return $data[0];
        }
    }

    /**
     * 获取图片内容
     *
     * @param unknown $path
     * @return mixed
     * @version 2014-12-16 WZ
     */
    public function getUrlImage($path)
    {
        if (preg_match('/http\:\/\//i', $path))
        {
            $cookie_file = tempnam('./temp', 'cookie');
            $url = $path;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
            $content = curl_exec($ch);
        }
        else
        {
            $content = file_get_contents($path);
        }

        return $content;
    }

    /**
     * 结果保存到数据库
     *
     * @param unknown $data
     * @return multitype:multitype:multitype:multitype:unknown multitype:string unknown
     * @version 2014-12-6 WZ
     */
    public function saveFileInfo($data)
    {
        $ids = array();
        $files = array();
        foreach ($data as $key => $value)
        {

            if (! isset($value['id']) && isset($value['filename']) && isset($value['path']) && $value['filename'] && $value['path'])
            {
                $value['timestamp'] = date("Y-m-d H:i:s");
                $id = $this->insertData($value, "image");
                $ids[] = $id;
                $files[] = array(
                    $this->file_key => array(
                        'id' => $id,
                        'path' => $value['path'] . $value['filename'],
                    )
                );
            }
            else
            {
                $this->updateKey($value, 1, 'count', 1, "image");
                $info = $this->getOne(array("id" => $value), array("path", "filename"), "image");
                $ids[] = $value['id'];
                $files[] = array(
                    $this->file_key => array(
                        'id' => $value['id'],
                        'path' => $info['path'] .$info['filename']
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
        set_time_limit(0);//会从零开始重新启动超时计数器。换句话说，如果超时默认是30秒，在脚本运行了了25秒时调用 set_time_limit(20)，那么，脚本在超时之前可运行总时间为45秒。
        $upload = new UploadfileApi($path, $size, $filetype, 'Ym/d');
        if ($source_file)
        {
            $upload->setFiles($source_file);
        }
        $upload->uploadfile();
        $filename = $upload->getUploadFileInfo();
        $path = $upload->imgPath;
        // $extension = substr($name, (strrpos($name, '.') + 1));

        $results = array();

        $thumb = new Image();
        if (! $this->file_key) {
            foreach ($_FILES as $key => $value) {
                $this->file_key = $key;break;
            }
        }
        if (! is_array($filename[$this->file_key]['new_name']))
        {
            foreach ($filename[$this->file_key] as $f_key => $f_value)
            {
                $filename[$this->file_key][$f_key] = array(
                    $f_value
                );
            }
        }
        foreach ($filename[$this->file_key]['new_name'] as $key => $value)
        {
            $name = substr($filename[$this->file_key]['new_name'][$key], strrpos($filename[$this->file_key]['new_name'][$key], '/') + 1);

            if ($filename[$this->file_key]['size'][$key] > 0)
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
        
        if ($str && $words) {
            $info = array();
            $word = array();
            for ($i = 0; $i < count($words); $i ++) {
                $content = substr_count($str, $words[$i]);
                if ($content > 0) {
                    $word[] = $words[$i];
                }
            }
            if (count($word) > 0) {
                $info = implode($word, ',');
            }
            
            return $info;
        }
        else {
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
        $words = $this->getCache('SensitiveWords/words', 1);
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
        if ($words) {
            $words = array_unique(explode('|', trim(trim($words, '|'))));
            foreach ($words as $k => $v) {
                $strlen = mb_strlen($v, 'utf-8');
                $star = '';
                for ($i = 0; $i < $strlen; $i ++) {
                    $star .= '*';
                }
                $str[$v] = $star;
                $strlen = 0;
            }
        }
        $this->setCache('SensitiveWords/words', $str, 1);
    }

    /**
     * 读取缓存文件敏感数据
     *
     * @author liujun
     * @return array $words
     */
    public function getSensitiveWords()
    {
        (array) $word = $this->getCache('SensitiveWords/words', 1, false);
        if (! $word) {
            return $word;
        }
        $words = array();
        foreach ($word as $k => $v) {
            $words[] = $k;
        }
        return $words;
    }

    /**
     * 获取regionInfo
     *
     * @param unknown $region_id            
     * @return multitype:string number |multitype:string number NULL unknown
     * @version 2015-8-15 WZ
     */
    public function getRegionInfo($region_id)
    {
        $result = array(
            'region_info' => "[]",
            'province' => 0,
            'city' => 0,
            'county' => 0
        );
        if (! $region_id) {
            return $result;
        }
        $count = 0;
        $region_array = array();
        $region_data = array();
        // 开始获取数据
        while ($region_info = $this->getOne(array(
            'id' => $region_id
        ),array("*"),'region')) {
            $region_array[] = $region_id;
            $region_data[$region_id] = $region_info;
            $region_id = $region_info['parent_id'];
            if (1 == $region_info['parent_id'] || 990000 == $region_info['parent_id']) { // 省级就退出或海外的国家
                break;
            }
            if (++ $count > 4) { // 防死循环
                break;
            }
        }
        if (! $region_array) {
            return $result;
        }
        $region_array = array_reverse($region_array);
        
        // 开始整理数据
        $item = array(
            0 => 'province',
            1 => 'city',
            2 => 'county'
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
                    'parentId' => $region_item->parent_id
                );
            }
        }
        $result['region_info'] = $this->json_encode($region_list);
        return $result;
    }

    /**
     * 根据region_info 提取省市区
     * 
     * @param unknown $regionInfo            
     * @return string
     * @version 2015-8-18 WZ
     */
    public function regionInfoToString($regionInfo,$interval=" ")
    {
        $string = "";
        if (is_string($regionInfo)) {
            $regionInfo = json_decode($regionInfo, true);
        }
        if ($regionInfo && is_array($regionInfo)) {
            $list = array();
            foreach ($regionInfo as $value) {
                $list[] = $value['region']['name'];
            }
            if ($list) {
                $string = implode($interval, $list);
            }
        }
        return $string;
    }

    /**
     * 把json字符串转成PHP用数组
     *
     * @param unknown $regionInfo            
     * @return multitype:unknown
     * @version 2015-8-20 WZ
     */
    public function regionInfoToArray($regionInfo)
    {
        $list = array();
        if (is_string($regionInfo)) {
            $regionInfo = json_decode($regionInfo, true);
        }
        if ($regionInfo && is_array($regionInfo)) {
            foreach ($regionInfo as $value) {
                $list[] = $value['region'];
            }
        }
        return $list;
    }

    /**
     * 接收前端文件编码
     * 用于上传文件处理
     *
     * @version 2016-5-13 WZ
     */
    public function ajaxGetDataAction()
    {
        $baseStr = $_POST['baseStr'];
        $file = $this->saveImage($baseStr);
        if ($file['files']) {
            $file = $file['files'][0]['ajax'];
            $return = array(
                'error' => '',
                'path' => $file['path'] . $file['filename'],
                'imgid' => $file['id']
            );
            echo json_encode($return);
            die();
        }
        else {
            echo '上传失败，未知错误！';
        }
    }    

    /**
     * 获得用户的真实IP地址
     *
     * @access public
     * @return string
     */
    function realIp()
    {
        static $realip = NULL;
        
        if ($realip !== NULL) {
            return $realip;
        }
        
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                
                /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    
                    if ($ip != 'unknown') {
                        $realip = $ip;
                        
                        break;
                    }
                }
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }
            else {
                if (isset($_SERVER['REMOTE_ADDR'])) {
                    $realip = $_SERVER['REMOTE_ADDR'];
                }
                else {
                    $realip = '0.0.0.0';
                }
            }
        }
        else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            }
            elseif (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            }
            else {
                $realip = getenv('REMOTE_ADDR');
            }
        }
        
        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
        $realip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        
        return $realip;
    }

    /**
     * 输出并结束
     *
     * @param unknown $return            
     * @param string $type            
     * @version 2015-11-20 WZ
     */
    public function returnMsg($return, $type = 'json')
    {
        if ('json' == $type) {
            echo json_encode($return);
            exit();
        }
    }

    /**
     * 获取微信支付信息
     *
     * @param number $type
     *            1-2 1余额充值，2充值水晶
     * @param number $id
     *            积分记录或订单id
     * @param number $amount
     *            支付金额
     * @return multitype:number string unknown NULL
     * @version 2015-4-10 WZ
     */
    /**
     * 获取微信支付信息
     * 
     * @param number $amount
     *            金额
     * @param number $id
     *            记录id或订单order_sn
     * @param number $type
     *            1-2 1充值；2购买；
     * @param string $name
     *            名称；
     * @param string $trade_type
     *            使用类型，APP, JSAPI
     * @return multitype:number string |multitype:
     * @version 2015-11-20 WZ
     */
//     public function getWxPayInfo($amount, $id, $type = 1, $name = '', $trade_type = 'JSAPI', $open_id = '')
//     {
//         $list = array(
//             1 => '充值余额',
//             2 => '购买服务',
//             3 => '追加服务'
//         );
//         if (array_key_exists($type, $list) && $id && $amount) {
//             $value = array(
//                 'order_price' => $amount,
//                 'product_name' => $list[$type] . $name . $amount . '元',
//                 'out_trade_no' => $type . $id
//             );
//             if (WX_TEST_PAY) {
//                 $value['order_price'] = 0.01; // 测试支付用
//             }
            
//             $wxpay = new AiiWxPay();
//             return $wxpay->setValue($value)->getOutParams($trade_type, $open_id);
//         }
//         else {
//             return array();
//         }
//     }

    /**
     * 支付宝支付
     *
     * @param unknown $amount            
     * @param unknown $id            
     * @param unknown $type            
     * @param unknown $name            
     * @version 2015-12-16 WZ
     */
   /* public function getAlipayInfo($amount, $id, $type, $subject = '', $body = '', $param = '')
    {
        // return "暂停使用";
        include_once APP_PATH . '/vendor/Core/System/alipay/alipayapi.php';
        $alipay = new \alipayapi();
        $alipay->total_fee = $amount; // 付款金额
        $alipay->out_trade_no = $type . $id; // 订单号
        $alipay->subject = $subject;
        $alipay->body = $body;
        if (1 == $type) {
            $alipay->return_url = 'http://' . SERVER_NAME . ROOT_PATH . 'web/cperson/amywallet';
        }
        if (2 == $type) {
            $alipay->return_url = 'http://' . SERVER_NAME . ROOT_PATH . 'web/corder/asucceed/i' . $param . '/s4';
        }
        elseif (3 == $type) {
            $alipay->return_url = 'http://' . SERVER_NAME . ROOT_PATH . 'web/corder/asucceed/s3';
        }
        return $alipay->PostAlipay();
    }*/

    /**
     * 跳转获取openid
     * 
     * @return unknown
     * @version 2015-12-1 WZ
     */
    public function getOpenid()
    {
        // 通过code获得openid
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) { // 是否来自微信浏览器
            if (2 == WX_IOSENV) {
                $wxapi = new WxApi();
                return $wxapi->GetOpenid();
            }
        }
        else {
            if (isset($_COOKIE['wx_open_id']) && $_COOKIE['wx_open_id']) {
                // 直接读取并返回
                setcookie('wx_open_id', $_COOKIE['wx_open_id'], time() + 3600 * 24 * 30, '/');
                return $_COOKIE['wx_open_id'];
            }
            else {
                $open_id = $this->makeCode(32, 6);
                // 写入cookie并返回
                setcookie('wx_open_id', $open_id, time() + 3600 * 24 * 30, '/');
                return $open_id;
            }
        }
    }



    public function responseData($list, $state=200, $message="success")
    {
        return array('data' => $list, 'code' => $state, 'message' => $message);
    }

    public function responseError($list=null, $state=400, $message="faile")
    {
        return array('data' => $list, 'code' => $state, 'message' => $message);
    }

    public function p($mes=null)
    {
        echo "<pre>";
        if($mes)
        {
            print_r($mes);
        }
        echo "<pre>";die;
    }


    /**
     * 检验是否为邮箱
     */
    function is_mail($email) {
        $regxMail = "/^[a-zA-Z0-9-._]{1,50}@[a-zA-Z0-9-]{1,65}.(com|net|org|info|biz|([a-z]{2,5}.[a-z]{2}))$/i";
        return !!preg_match ( $regxMail, $email );
    }

    /**
     * 检查是否为手机号码
     */
    function is_mobile_phone($phone) {
        $regxPhone = "/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57]|17[0-9])[0-9]{8}$/";
        return !!preg_match ( $regxPhone, $phone );
    }

    public function getTime()
    {
        return date("Y-m-d H:i:s");
    }
    
    /**
     * 获取缓存的城市信息
     * @param int 城市Id 空为返回所有城市
     * @return 返回一条或多条城市是信息
     * @author arong
     */
    public function getCityInfo($city_id=''){
        //缓存
        $cityData = $this->getCache('cityData.txt',1);
        if(! $cityData){
            $city = $this->fetchAll(array('deep'=>2), array('columns' => array('id', 'name', 'parent_id', 'pinyin', "sort"), "order" => "sort desc"), 'e_region');
            $cityData = array();
            foreach ($city as $k=>$v){
                $cityData[$v['id']] = $v;
            }
            $this->setCache('cityData.txt', $cityData, 1);
        }
        if($city_id){
            return @$cityData[$city_id];
        }else{
            return $cityData;
        }    
    }

    public function getProvince($keyword=''){
        //缓存
        $provinceData = $this->getCache('provinceData.txt',1);
        if(!$provinceData){
            $province = $this->fetchAll(array(
                'deep' => 1
            ), array(
                'columns' => array(
                    'id',
                    'name',
                    'parent_id',
                    'pinyin'
                ),
                'order' => array(
                    'id' => 'asc'
                )
            ), 'region');
            $provincedata = array();
            foreach ($province as $k=>$v){
                $provinceData[$v['id']] = $v;
            }
            $this->setCache('provinceData.txt', $provinceData, 1);
        }
        if($keyword){
            $data = array();
            foreach ($provinceData as $k => $v) {
                preg_match("/$keyword/",$v['name'],$result);
                if ($result) {
                    $data[] = $provinceData[$k];
                }
            }
            return $data;
        }else{
            return $provinceData;
        }
    }
    
    public function getCity($keyword=''){
        //缓存
        $cityData = $this->getCache('cityData.txt',1);
        if(!$cityData){
            $city = $this->fetchAll(array(
                'deep' => 2
            ), array(
                'columns' => array(
                    'id',
                    'name',
                    'parent_id',
                    'pinyin'
                ),
                'order' => array(
                    'id' => 'asc'
                )
            ), 'region');
            $cityData = array();
            foreach ($city as $k=>$v){
                $cityData[$v['id']] = $v;
            }
            $this->setCache('cityData.txt', $cityData, 1);
        }
        if($keyword){
            $data = array();
            foreach ($cityData as $k => $v) {
                preg_match("/$keyword/",$v['name'],$result);
                if ($result) {
                    $data[] = $cityData[$k];
                }
            }
            return $data;
        }else{
            return $cityData;
        }
    }


    /**计算时间差
     * @param $start
     * @param $end
     * @return string
     */
    public function handleDate($start, $end){
        $t=strtotime($end)-strtotime($start);
        if(0!=$all_day=floor($t/86400)){
            $y = floor($all_day/365) ? abs(floor($all_day/365))."年" : '';
            $extral = $all_day%365;
            $m = floor($extral/30) ? abs(floor($extral/30))."月" : '';
            $d = abs(($extral%30))."天";
            if($y){
                return $y.$m;
            }else{
                return $y.$m.$d;
            }
        }
    }

    /**
     * 时间格式转换
     * @param $ctime  格式化时间
     * @return string
     */
    public function convertTime($ctime) {
        $unixTime = time() - strtotime($ctime);
        if ($unixTime < 60) {
            return $unixTime . "秒前";
        } elseif ($unixTime < 3600) {
            return floor($unixTime/60) . "分钟前";
        } elseif ($unixTime < 86400) {
            return floor($unixTime/3600) . "小时前";
        } elseif ($unixTime < 31536000) {
            return floor($unixTime/86400) . "天前";
        } else {
            return floor($unixTime/31536000) . "年前";
        }

    }
    /**
     * 时间格式转换
     * @param $ctime  格式化时间
     * @return string
     */
    public function convertTimeTo($unixTime) {
        if ($unixTime < 60) {
            return $unixTime . "秒";
        } elseif ($unixTime < 3600) {
            return floor($unixTime/60) . "分钟";
        } elseif ($unixTime < 86400) {
            return floor($unixTime/3600) . "小时";
        } elseif ($unixTime < 31536000) {
            return floor($unixTime/86400) . "天";
        } else {
            return floor($unixTime/31536000) . "年";
        }

    }
    
    /**
     * 年份数组
     * @return multitype:string multitype:number
     */
    public function yearArray()
    {
        $nowYear = date('Y');
        $yearArray = array();
//         $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
//         $userInfo = $this->getOne(array('id' => $userId), array('birth'), 'e_user');
//         $s_year = substr($userInfo['birth'], 0, 4);
        for ($i=$nowYear-100; $i<$nowYear+1; $i++) {
            $yearArray[] = $i;
        }
        return array(
            'code' => '200',
            'message' => 'success',
            'data' => $yearArray
        );
    }
    
    /**
     * 月份数组
     * @return multitype:string multitype:string
     */
    public function monthArray()
    {
        $monthArray = array();
        for ($i=1; $i<13; $i++) {
            $monthArray[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        return array(
            'code' => '200',
            'message' => 'success',
            'data' => $monthArray
        );
    }
    
    /**
     * 婚姻状态数组
     * 
     * @return multitype:string 
     * @version 2016-6-30 WZ
     */
    public function maritalStatusArray() {
        $list = array(
            1 => '已婚',
            2 => '未婚',
            3 => '保密'
        );
        return $list;
    }

    /**
     * 计算面试评论平均分
     * @param $grade
     * @param $count
     * @return array
     */
    public function commentAverageGrade($grade, $count){
        if(empty($count)){
            return array(
                "averageGrade" => "0分",
                "averageGradeWidth" => 0
            );
        }
        $averageGrade = round(($grade * 2) /$count)/2;
        $averageGradeWidth = $averageGrade * 20 + round($averageGrade-1)*10;
        return array(
            "averageGrade" => $averageGrade ."分",
            "averageGradeWidth" => $averageGradeWidth
        );
    }
    
    /**
     * php获取中文字符拼音首字母
     * @param $str 中文字符
     * @return null|string
     */
    function getFirstCharter($str) {
        if(empty($str)) {return '';}
        $fchar = ord($str{0});
        if($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    
        if($asc >= -20319 && $asc <= -20284) return 'A';
        if($asc >= -20283 && $asc <= -19776) return 'B';
        if($asc >= -19775 && $asc <= -19219) return 'C';
        if($asc >= -19218 && $asc <= -18711) return 'D';
        if($asc >= -18710 && $asc <= -18527) return 'E';
        if($asc >= -18526 && $asc <= -18240) return 'F';
        if($asc	>= -18239 && $asc <= -17923) return 'G';
        if($asc	>= -17922 && $asc <= -17418) return 'H';
        if($asc	>= -17417 && $asc <= -16475) return 'J';
        if($asc	>= -16474 && $asc <= -16213) return 'K';
        if($asc	>= -16212 && $asc <= -15641) return 'L';
        if($asc	>= -15640 && $asc <= -15166) return 'M';
        if($asc	>= -15165 && $asc <= -14923) return 'N';
        if($asc	>= -14922 && $asc <= -14915) return 'O';
        if($asc	>= -14914 && $asc <= -14631) return 'P';
        if($asc	>= -14630 && $asc <= -14150) return 'Q';
        if($asc	>= -14149 && $asc <= -14091) return 'R';
        if($asc	>= -14090 && $asc <= -13319) return 'S';
        if($asc	>= -13318 && $asc <= -12839) return 'T';
        if($asc	>= -12838 && $asc <= -12557) return 'W';
        if($asc	>= -12556 && $asc <= -11848) return 'X';
        if($asc	>= -11847 && $asc <= -11056) return 'Y';
        if($asc	>= -11055 && $asc <= -10247) return 'Z';
        return null;
    }

    public function mobileUpload() {
        $baseStr = $_POST['baseStr'];
        $file = $this->saveImage($baseStr);

        if ($file['files']) {
            $file = $file['files'][0]['ajax'];
            $return = array(
                'error' => '',
                'path' => $file['path'] . @$file['filename'],
                'imgid' => $file['id']
            );
            echo json_encode($return);
            die();
        }
        else {
            echo '上传失败，未知错误！';
        }
    }

    /**
     * 保存那个js生成的图片
     *
     * @param unknown $data
     * @param $type 1，js插件；2，微信图片
     * @return Ambigous <multitype:, multitype:multitype:NULL number string  >
     * @version 2015-11-13 WZ
     */
    function saveImage($data, $path = LOCAL_SAVEPATH, $type = 2) {
        if ($type == 1) {
            // js插件
            $baseStr = explode(';', $data);
            $type = explode(':', $baseStr[0]);
            $type = $type[1];
            $data = explode(',', $baseStr[1]);
            $data = $data[1];
            $data = base64_decode($data);
        }
        elseif ($type == 2) {
            // 微信图片
            $data = $this->getUrlImage($data);
        }

        $this->file_key = 'ajax';
        $source_file = array(
            $this->file_key => array(
                'name' => array(
                    'ajax'
                ),
                'type' => array(
                    $type
                ),
                'tmp_name' => array(
                    'ajax'
                ),
                'error' => array(
                    0
                ),
                'size' => array(
                    strlen($data)
                ),
                'data' => array(
                    $data
                )
            )
        );
        $save = array();
        $save[] = $this->checkFileMd5($source_file);
        $files = $this->saveFileInfo($save);
        return $files;
    }
    /**
     * 根据pid拿省市区
     * @param number $pid
     */
    public function area($pid=1)
    {
        $data = array(
            'columns' => array(
                'id',
                'name',
                'parent_id',
            ),
        );
        $area = $this->fetchAll(array('parent_id' => $pid), $data, 'e_region');
        return array(
            'code' => '200',
            'message' => '查询成功',
            'data' => $area
        );
    }

    public function mobileUpload1() {
        $baseStr = $_POST['baseStr'];
        $file = $this->saveImage($baseStr);

        if ($file['files']) {
            $file = $file['files'][0]['ajax'];
            $return = array(
                'error' => '',
                'path' => $file['path'] . @$file['filename'],
                'imgid' => $file['id']
            );
            return $return;
            die();
        }
        else {
            echo '上传失败，未知错误！';
        }
    }
    
    /**
     * 使用JSON_UNESCAPED_UNICODE的json_encode，
     * 
     * @version 2016-9-1 WZ
     */
    function json_encode($data) {
        if (PHP_VERSION > '5.4') {
            return json_encode($data,JSON_UNESCAPED_UNICODE);
        }
        else {
            return json_encode($data);
        }
    }
    
    /**
     * 得到当前时间
     */
    public function makeTime(){
        return date('Y-m-d H:i:s',time());
    }
    
    /**
     * 判断是否已赞：1是；0否；
     * @param unknown $where
     * @param unknown $table
     * @return number
     * @version YSQ
     */
    public function getIsPraise($where,$table){
        /*这个注释的方法查一表,判断就行*/
//         $praisse_log_list =$this->fetchAll($p_where,array('columns'=>array('target_id')),'praise_log');
//         $praise_list = array();
//         foreach ($praisse_log_list as $v) {
//             $praise_list[] = $v['target_id'];
//         }
//         if($praise_list && in_array($val['id'], $praise_list)){
//             $item[$k]['review']['isPraise'] = 1;
//         }else{
//             $item[$k]['review']['isPraise'] = 0;
//         }
        /*这个方法要每次查表*/
        $praise_log_info = $this->getOne($where,array('*'),$table);//8.	赞记录表
        if($praise_log_info){
            return 1;
        }else{
            return 0;
        }
    }
    
    /**
     * 判断是否已购买：1是；0否；（免费返回已购买)
     * @param unknown $isfree
     * @param unknown $p_where
     * @param unknown $table
     * @return number
     * @version YSQ
     */
    public function getIsPay($isfree,$p_where,$table){
        if(!$isfree){//是否免费：1是；0否
            $buy_log_info = $this->getOne($p_where,array('*'),$table);//6.	购买记录
            if($buy_log_info && $buy_log_info['price']){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 1;
        }
    }
    /**
     * 根据user_id的数组得到 用户信息列表
     * @param unknown $array user_id的数组
     * @return multitype:multitype:string unknown Ambigous <>
     * @version YSQ
     */
    public function getToUserInfo($array){
        $list = array();
        foreach ($array as $k => $v){
            $user_info = $this->getOne(array('id'=>$v),array('*'),'user');//提问人
            $list[$k] = array(
                'id' => $user_info['id'],
                'nickname' => $user_info['nickname'],
                'imagePath' => $user_info['image'],//头像
                'rank' => $this->getUserLv($user_info['id']),//$user_info['timestamp'],//等级，例如：Lv9
                'typeName' => $this->getUserTypeName($user_info['type']),//me$user_info['timestamp'],//用户类型：店长
            );
        }
        return $list;
    }

    /**
     * 获取项分组id
     */
    public function getSettingId($keyword){
        if (empty($keyword)) {
            return false;
        }
        $result = $this->getOne(array('key' => $keyword), array('id'), 'setting');
        if ($result) {
            return $result['id'];
        }
        return false;
    }

    /**
     * 获取用户等级
     */
    public function getUserLv($user_id){
        $user_id = (int)$user_id;
        if (empty($user_id)) {
            return '';
        }
        
        $count_info = $this->getOne(array('user_id' => $user_id), array('stat_online'), 'user_stat');
        if (!$count_info) {
            return false;
        }
        $online = floor($count_info['stat_online'] / 60);
        
        if (empty($this->user_lv)) {
            $ug_id = $this->getSettingId('LV');
            if (! $ug_id) {
                return '';
            }
            
            $types = $this->fetchAll(array('setting_group_id' => $ug_id), null, 'setting_item');
            if ($types) {
                $this->user_lv = array();
                foreach ($types as $value) {
                    $this->user_lv[$value['name']] = $value['value'];
                }
                ksort($this->user_lv);
            }
        }
        
        $lv = 'Lv1';
        foreach ($this->user_lv as $key => $value) {
            if ($value < $online) {
                $lv = $key;
            }
            else {
                break;
            }
        }
        return $lv;
    }

    /**
     * 获取用户类型名称
     */
    public function getUserTypeName($typeid){
        $typeid = (int)$typeid;
        if (empty($this->user_types)) {
            if (empty($typeid)) {
                return '';
            }
            $tg_id = $this->getSettingId('USER_TYPE');
            if (!$tg_id) {
                return '';
            }
            
            $types = $this->fetchAll(array('setting_group_id' => $tg_id), null, 'setting_item');
            if ($types) {
                $this->user_types = array();
                foreach ($types as $value) {
                    $this->user_types[$value['id']] = $value['name'];
                }
            }
        }
        
        return isset($this->user_types[$typeid]) ? $this->user_types[$typeid] : '';
        
        // 下方旧方法
        $result = $this->getOne(array('id' => $typeid, 'setting_group_id' => $tg_id), array('name'), 'setting_item');
        if ($result) {
            return $result['name'];
        }
        return '';
    }
    
  
    /**
     * 获取省份/直辖市/特别行政区信息
     *
     */
    public function getProvinceInfo($region_id)
    {
        $region = $this->getRegionInfo($region_id);
        $address = $this->regionInfoToString($region['region_info']);
        $addressArr = explode(' ',$address);
        return $addressArr['0'];
        //return $address;
    }
    
 
    
    /**
     * 2016/3/31
     * 根据用户和用户类型发送推送
     *
     * @author WZ
     * @param array $ids
     *            用户id数组
     * @param number $contentType
     * @param PushArgsItem $args
     *            模版中内容参数
     * @param PushFromItem $from user_id,type,id
     *            模版编号
     * @version 1.0.14515 WZ 添加更多插入记录的信息
     */
    public function pushForController($user_id, $type, PushArgsItem $args = null, PushTemplateItem $template = null, PushFromItem $from = null)
    {
        $myfile = new AiiMyFile();
        $myfile->setFileToPublicLog();
        if (! $user_id || ! $type) {
            return ;
        }
        if (! $template) {
            $template = $this->pushTemplate($type, $args); // 根据模版编号获得推送的title和content
        }
        $device = $this->getDeviceForUser($user_id); // 根据id和用户类型查找设备号与设备类型
        $status = 2; // 未发送
        if ($device && $template->content) {
            // 找到设备和模版信息没问题就开始推送
            if (PUSH_SWITCH) {
                // 开启推送功能
                $push = new AiiPush();
                //                 $result = $this->pushForDeviceCollection();
                $push_args = $template->push_args;
                $nid = 0;
                if (isset($push_args['nid'])) {
                    $nid = $push_args['nid'];
                    unset($push_args['nid']);
                }
                $result = $push->pushSingleDevice($device['device_token'], $device['device_type'], $template->content, $template->title, $push_args, $nid);
                if ($result['success'])
                {
                    $status = 1;
                }
                elseif ($result['fail'])
                {
                    $status = 3;
                }
            }
    
            if (PUSH_LOG_SWITCH) {
                $content = "推送" . ($status == 1 ? "成功" : "失败") . ",".(2 == $device['delete'] ? '用户关闭推送,':'')." user_id :$device[user_id] , title : ".$template->title." , content : ".$template->content." , args：" . json_encode($template->push_args) . ", 设备号：$device[device_token]";
                $myfile->putAtStart($content);
            }
        }
        else {
            // 发送失败也记录
            if (PUSH_LOG_SWITCH) {
                if (! $device) {
                    $content = "推送, msg：找不到对应的设备号 , 或对应设备号关闭推送功能 , user_id：" . $user_id . " , args：" . json_encode($template->push_args) . ", 模版：" . $type;
                }
                elseif (! $template->content) {
                    $content = "推送 , msg：不能生成content , 检查模版类型和参数是否相对应 ,  user_id：" . $user_id . " , args：" . json_encode($template->push_args) . ", 模版：" . $type;
                }
                else {
                    $content = "推送, 未知错误";
                }
                $myfile->putAtStart($content);
            }
        }
        if(isset($template->push_args['action']) && $template->push_args['action']==2){
            return;
        }
        if ($template->content) {
            // 保存到数据库
            $data = array(
                'title' => $template->title,
                'content' => $template->content,
                'type' => $type,
                'parameter' => json_encode($template->push_args),
                'status' => $status,
                'user_id' => $user_id,
            );
            $this->insertData($data,'notification_records');
        }
    }
    
    /**
     * 2014/3/31
     * 推送内容模版设置
     *
     * @author WZ
     * @param number $type
     * @param
     *            array 其它参数
     * @return array {string content ,string title} 内容和标题（标题是安卓推送需要的）
     */
    public function pushTemplate($type, PushArgsItem $args = null)
    {
        $template = new PushTemplateItem();
        if(defined('TEMPLATE_PUSH_TITLE_' . $type) && defined('TEMPLATE_PUSH_CONTENT_' . $type))
        {
            $template->title = constant('TEMPLATE_PUSH_TITLE_' . $type);
            $template->content = constant('TEMPLATE_PUSH_CONTENT_' . $type);
    
            if($args)
            {
                switch ($type)
                {
                    //需两个参数的
                    case '1':
                        //社团F码审核发放成功
                    case '6':
                        //纪念日提醒(前一天）
                    case '7':
                        //纪念日提醒(当天）
                    case '12':
                        //开通爱情见证号
                    case '14':
                        //密聊被接受
                    case '16':
                        //开通爱情见证号确认后，但未付款
                    case '19':
                        //接受密聊邀请
                        $template->content = sprintf($template->content, $args->param1, $args->param2);
                        break;

                    //不需要参数的
                    case '2':
                        //社团F码审核不通过
                    case '8':
                        //让爱成书审核通过
                    case '9':
                        //让爱成书已成书
                    case '10':
                        //让爱成书已邮寄
                    case '11':
                        //让爱成书审核不通过
                        break;
                        
                    //需一个参数的
                    case '3':
                        //社团F码过期
                    case '4':
                        //意见反馈后台回复
                    case '5':
                        //关注日中上了全国排行/省份排行
                    case '13':
                        //密聊被拒绝
                    case '15':
                        //密聊关系解除
                    case '17':
                        //被邀请密聊
                    case '18':
                        //您有新评论消息
                        $template->content = sprintf($template->content, $args->param1);
                        break;
                }
               
            }
        }
    
        $template->push_args['type'] = $type;
        if($args && $args->id) {
            $template->push_args['id'] = $args->id;
        }
        if ($args && $args->nid) {
            $template->push_args['nid'] = $args->nid;
        }
        if($args && $args->action){
            $template->push_args['action'] = $args->action;
        }
        return $template;
    }
    
    
    /**
     * 2014/3/31
     * 根据用户信息查找用户的设备号和设备类型
     *
     * @author WZ
     * @param array $ids
     *            用户id或者司机id，数组
     * @param array $types
     *            对应id的类型，数组
     * @return multitype:
     */
    public function getDeviceForUser($user_id)
    {
        $where = array('delete' => DELETE_FALSE, 'user_id' => $user_id);
        $device = $this->getOne($where,array('*'),'device_user');
        return $device;
    }

  
    
    /**
     * 回调业务处理
     * $id 财务流水号
     * $transaction_id 第三方支付平台生成的订单流水号
     * $pay_type 支付方式：1微信 2支付宝 3银联支付 4线下支付
     */
    public function notifyTransacting($id,$transaction_id=null,$pay_type)
    {  
        set_time_limit(0);
        
    }
    
    /**
     * 下载excel
     * @param $fileName
     * @param $headArr
     * @param $data
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public  function getExcel($fileName,$headArr,$data) {
//         System/
        include_once APP_PATH .'/vendor/Core/PHPExcel.class.php';
        include_once APP_PATH .'/vendor/Core/PHPExcel/Writer/Excel5.php';
        include_once APP_PATH .'/vendor/Core/PHPExcel/IOFactory.php';
        ob_end_clean();
        ini_set("memory_limit","-1");
        set_time_limit(0);
        //检查文件名
        if(empty($fileName)) {
            exit;
        }

        $time = date("Y_m_d",time());
        $fileName .= "_{$time}.xls";
    
        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(14);
        //设置单元格宽度
        $objActSheet->getColumnDimension('A')->setWidth(15);
        //设置默认行高
        $objActSheet->getDefaultRowDimension()->setRowHeight(20);

        //设置表头
        $key = ord("A");
        foreach($headArr as $v) {
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        foreach($data as $key => $rows) { //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){ // 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j.$column, ' '.$value, \PHPExcel_Cell_DataType::TYPE_STRING);//显示的指定数据类型
                $span++;
            }
            $column++;
            unset($rows);
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        $objPHPExcel->getActiveSheet()->setTitle('Simple');//test;Simple;
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
    
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        return true;
    }
    
    /**
     * 模板消息推送
     * @params class WxApi $api  微信api
     * @params string $openId 发送的微信openId
     * @params int $type    1用户支付订单后，2用户确认收货后，3用户取消订单，4用户发表评价，5平台处理投诉，6审核不通过，7审核通过,8商家取消订单，9用户催单，10提现申请通过，11提现申请不通过
     *                      12用户订单创建，13用户支付完成，14商家接单，15用户订单完成
     * @params array $data 发送的数据
     * @return bool
     * @author 2016-12-13 Lyndon
     * */
    public function sendTempMessage($type, $openId, $data = array())
    {
         
        $sendData['touser'] = $openId;
        //         $sendData['url'] = 'http://'.$_SERVER["HTTP_HOST"]."/index/repairorder/details/".$data['id'].".html";
        if($type == 1){
            //会员到期通知
            $sendData['url'] = 'https://'.$_SERVER["HTTP_HOST"]."/web/user/rechargeIndex";
            $sendData['template_id'] = '-NphE7VWtzWJYhdLzb6CfFYlfRx5wGV8cwEt1mIRNpk';
            $sendData['data']['first']['value'] = '亲，你的会员学习卡还有1天就到期~';
            $sendData['data']['keyword1']['value'] = $data['open_time'];
            $sendData['data']['keyword2']['value'] = $data['over_time'];
            $sendData['data']['remark']['value'] = '学习改变命运，续充会员请点击>>>';
        }else if($type == 2){
            //课程更新提醒
            $sendData['url'] = $data['url'];
            $sendData['template_id'] = 'HiAnqB7XlWfuyMdXDCADkFe6jv4dZ1g8oKEeG4ksyJU';
            $sendData['data']['first']['value'] = '你关注的老师，有新课程更新啦！';
            $sendData['data']['keyword1']['value'] = $data['title'];
            $sendData['data']['keyword1']['color'] = "#d7000f";
            $sendData['data']['keyword2']['value'] = $data['category'];
            $sendData['data']['keyword3']['value'] = $data['teacher_name'];
            $sendData['data']['keyword4']['value'] = $data['time'];
            $sendData['data']['remark']['value'] = '点击马上学习>>>';
            $sendData['data']['remark']['color'] = "#d7000f";
        }
//         $myfile = new AiiMyFile();//日记
//         $myfile->setFileToPublicLog()->putAtStart(json_encode($sendData, JSON_UNESCAPED_UNICODE));
        $api = new WxApi();
        $reJson = $api->wxSendTemplate(json_encode($sendData));
        $resule = json_decode($reJson);
        if($resule->errcode == '0'){
            return true;
        }else{
            //            var_dump($reJson);
            return  false;
        }
    }
    
    /**
     * 二维码生成
     */
    public function generateCode($str)
    {
        if (! $str) {
            die();
        }
        $url = "http://api.wwei.cn/wwei.html?data=" . urlencode($str) . "&version=1.0&apikey=20160922105810";
        $json_data = file_get_contents($url);
        $data = json_decode($json_data);
        if (isset($data->status) && $data->status == 1) {
            $img_url = $data->data->qr_filepath;
        } else {
            $img_url = '';
        }
        return ($img_url);
    }

}