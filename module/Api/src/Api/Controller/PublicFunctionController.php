<?php
namespace Api\Controller;

use Core\System\File;
use Core\System\UploadfileApi;
use Core\System\Image;
use Core\System\AiiUtility\AiiPush\AiiMyFile;
use Api\Controller\Item\PushArgsItem;
use Api\Controller\Item\PushTemplateItem;
use Core\System\AiiUtility\AiiPush\AiiPush;
use Core\System\AiiUtility\AiiWxPayV3\AiiWxPay;
use Api\Model\Table;
use Core\System\AiiUtility\AiiWxPayV3\WxApi;

class PublicFunctionController extends Table
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
    
    private $file_key;
    

    /**
     * 缓存写入
     *
     * @param unknown $filename
     *            文件名格式 region 或 Admin/category
     * @param unknown $param
     *            数组
     * @param integer $type
     *            1.文件 2.内存
     * @return boolean
     */
    public function setCache($filename, $param, $type, $cache_time = 600)
    {
        if ($type == 1)
        {
            // 文件缓存
            $filename = $this->getCacheFilename($filename);
            
            if ($param)
            {
                if (! is_file($filename))
                {
                    @touch($filename);
                    @chmod($filename, 0777);
                }
                
                $data = $param;
                if (is_array($param) || is_object($param))
                {
                    $data = json_encode($param);
                }
                // @file_put_contents($filename, $data);
                $file = new File();
                $file->mkFile($filename, $data, true);
            }
            else
            {
                @unlink($filename);
            }
            return true;
        }
        return false;
    }

    /**
     * 获得缓存
     * 
     * @param unknown $filename
     *            文件名格式 region 或 Admin/category
     * @param integer $type
     *            1 文件缓存 2 内存缓存
     * @return boolean mixed
     */
    public function getCache($filename, $type, $timestampLeast = 0)
    {
        if ($type == 1)
        {
            // 文件缓存
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
            if (strtotime($timestampLeast) >= $ctime)
            {
                // 缓存时间大于文件生成时间就不用返回整个列表啦
                return STATUS_CACHE_AVAILABLE;
            }
            else
            {
                $data = file_get_contents($filename);
                if ($data)
                {
                    $param = json_decode($data, true);
                    if ($param)
                    {
                        return $param;
                    }
                    else
                    {
                        return $data;
                    }
                }
            }
        }
        return false;
    }

    public function getCacheTime($filename)
    {
        $filename = $this->getCacheFilename($filename);
        if (! is_file($filename))
        {
            return false;
        }
        
        $ctime = filemtime($filename); // 缓存更新时间
        return $ctime;
    }

    /**
     * 清除文件缓存
     *
     * @param string $filename            
     * @param number $type
     *            1删除文件夹，2删除文件
     * @version 2014-12-5 WZ
     */
    public function clearCache($filename, $type = 1)
    {
        $file = new File();
        if (1 == $type)
        {
            $cache_path = $this->getCacheFilename($filename);
            if (is_dir($cache_path))
            {
                $file->delDir($cache_path, true);
            }
            else
            {
                $cache_path = substr($cache_path, 0, strrpos($cache_path, '/'));
                $file->delDir($cache_path);
            }
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
        if (1 == $type)
        {
            $filename = ($namespace ? $namespace : $this->getNamespace()) . '/';
            if (is_array($param))
            {
                foreach ($param as $key => $value)
                {
                    $filename .= $key . $value . '/';
                }
            }
            elseif (is_string($param))
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
        
        return $filename;
    }

    public function getCacheFilename($filename)
    {
        return APP_PATH . '/Cache/' . $filename . '';
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
        if ($method == 'get')
        {
            $params = is_array($params) ? http_build_query($params) : $params;
            $url .= (strpos($url, '?') === false ? '?' : '&') . $params;
        }
        
        // 默认配置
        $curl_conf = array(
            CURLOPT_URL => $url, // 请求url
            CURLOPT_HEADER => false, // 不输出头信息
            CURLOPT_RETURNTRANSFER => true, // 不输出返回数据
            CURLOPT_CONNECTTIMEOUT => 3
        ) // 连接超时时间
;
        
        // 配置post请求额外需要的配置项
        if ($method == 'post')
        {
            // 使用post方式
            $curl_conf[CURLOPT_POST] = true;
            // post参数
            $curl_conf[CURLOPT_POSTFIELDS] = $params;
        }
        
        // 添加额外的配置
        foreach ($extra_conf as $k => $v)
        {
            $curl_conf[$k] = $v;
        }
        
        $data = false;
        try
        {
            // 初始化一个curl句柄
            $curl_handle = curl_init();
            // 设置curl的配置项
            curl_setopt_array($curl_handle, $curl_conf);
            $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
            if ($ssl)
            {
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);
            }
            // 发起请求
            $data = curl_exec($curl_handle);
            if ($data === false)
            {
                throw new \Exception('CURL ERROR: ' . curl_error($curl_handle));
            }
        }
        catch (\Exception $e)
        {
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
        if (! isset($_FILES[$this->file_key]))
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
    
    /**
     * 保存网络图片
     * 
     * @param unknown $url
     * @return Ambigous <\Api\Controller\multitype:multitype:multitype:multitype:unknown, multitype:multitype:multitype:multitype:unknown    multitype:string number  >
     * @version 2016-6-7 WZ
     */
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
        $data = $this->getTable('image')->getOne(array(
            'md5' => $md5
        ));
        if ($data)
        {
            return (array) $data;
        }
        else
        {
            $data = $this->Uploadfile(LOCAL_SAVEPATH, true, 1, 2048, $source_file);
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
                $value['timestamp'] = $this->getTime();
                $id = $this->getTable('image')->insertData($value);
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
                $this->getTable('image')->updateKey($value['id'], 1, 'count', 1);
                $ids[] = $value['id'];
                $files[] = array(
                    $this->file_key => array(
                        'id' => $value['id'],
                        'path' => $value['path'],
                        'filename' => $value['filename']
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
        set_time_limit(0);
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
            if (count($word) > 0)
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
        if ($words)
        {
            $words = array_unique(explode('|', trim(trim($words, '|'))));
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
        if (! $word)
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
            $data = $this->getTable('image')->getOne(array('id' => $id));
            if ($data) {
                $item['path'] = $data['path'] . $data['filename'];
            }
        }
        $this->images[$id] = $item;
        return $item;
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
            'county' => 0,
            'region_index' => '',
        );
        if (! $region_id)
        {
            return $result;
        }
        $count = 0;
        $region_array = array();
        $region_data = array();
        // 开始获取数据
        while($region_info = $this->getTable('region')->getOne(array('id' => $region_id))) {
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
                    'parentId' => $region_item->parent_id,
                );
            }
        }
        $result['region_info'] = json_encode($region_list); // JSON_UNESCAPED_UNICODE 5.4才兼容
        $result['region_index'] = implode(',', $region_array);
        return $result;
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
                if (isset($value['region'])) {
                    $list [] = $value['region']['name'];
                }
            }
            if ($list) {
                $string = implode(" ", $list);
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
    public function regionInfoToArray($regionInfo) {
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
     * 2014/3/31
     * 推送内容模版设置
     *
     * @author WZ
     * @param number $type
     * @param PushArgsItem 其它参数
     * @return PushTemplateItem {string content ,string title} 内容和标题（标题是安卓推送需要的）
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
                $template->content = sprintf($template->content, $args->number, $args->name);
            }
        }
    
        $template->push_args['type'] = $type;
        if($args && $args->id) {
            $template->push_args['id'] = $args->id;
        }
        if (isset($args->nid) && $args->nid) {
            $template->push_args['nid'] = $args->nid;
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
    public function getDeviceForUser($user_id, $user_type)
    {
        $where = array(
            'user_id' => $user_id,
            'user_type' => $user_type,
            'delete' => DELETE_FALSE,
        );
        $data = $this->getDeviceUserTable()->getOne($where);
        
        return $data;
    }
    
    /**
     * 
     * @param number $user_id 用户id
     * @param number $user_type 用户类型
     * @param number $type 模版id
     * @param PushArgsItem $args 额外参数
     * @param PushTemplateItem $template 
     * @version 2015-8-25 WZ
     */
    public function pushForNow($user_id, $user_type, $type, PushArgsItem $args = null, PushTemplateItem $template = null) {
        $myfile = new AiiMyFile();
        $myfile->setFileToPublicLog();
        if (! $user_id || ! $type) {
            return ;
        }
        if (! $template) {
            $template = $this->pushTemplate($type, $args); // 根据模版编号获得推送的title和content
        }
        
        $device = $this->getDeviceForUser($user_id, $user_type); // 根据id和用户类型查找设备号与设备类型
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
                $result = $push->pushSingleDevice($device['device_token'], $device['device_type'], $template->content, $template->title, $push_args, $nid, $device['environment']);
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
                    $content = "推送, msg：找不到对应的设备号 , 或对应设备号关闭推送功能 , user_id：" . $user_id  . " ,user_type:" . $user_type . " , args：" . json_encode($template->push_args) . ", 模版：" . $type;
                }
                elseif (! $template->content) {
                    $content = "推送 , msg：不能生成content , 检查模版类型和参数是否相对应 ,  user_id：" . $user_id . " ,user_type:" . $user_type . " , args：" . json_encode($template->push_args) . ", 模版：" . $type;
                }
                else {
                    $content = "推送, 未知错误";
                }
                $myfile->putAtStart($content);
            }
        }
        
        if ($template->content) {
            // 保存到数据库
            $data = array(
                'title' => $template->title,
                'content' => $template->content,
                'type' => $type,
                'parameter' => json_encode($template->push_args),
                'status' => $status,
                'user_type' => $user_type,
                'user_id' => $user_id,
            );
            $this->getNotificationRecordsTable()->insertData($data);
        }
    }
    
    /**
     * 推送方法改为直接查数据库
     *
     * @version 2015-1-5 WZ
     */
    public function pushForDeviceCollection()
    {
        $table = new Table();
        $where = array('status' => 2);
    
        $order_by = array('id' => 'desc');
        $data = $table->getViewNotificationRecordsTable()->getAll($where,null,$order_by,true,1,100); // 一次发送100条
        if ($data['total'] > 0)
        {
            $time_now = date('H:i:s');
            $push = new AiiPush();
            $set_5 = array('status' => 5); // 免打扰
            $set_1 = array('status' => 1); // 成功
            $set_4 = array('status' => 4); // 失败
            foreach ($data['list'] as $value)
            {
                $where = array('id' => $value->id);
                if ($value->parameter)
                {
                    $args = json_decode($value->parameter, true);
                    if(! $args)
                    {
                        $args = array();
                    }
                }
    
//                 if (OPEN_FALSE == $value->notification || $time_now > $value->quiet_start_time || $time_now < $value->quiet_end_time)
//                 {
//                     $table->getNotificationRecordsTable()->updateData($set_5, $where);
//                 }
//                 else
//                 {
                    $value['ring'] = $value['sound'] == OPEN_FALSE ? 0 : 1;
                    $value['vibrate'] = $value['vibrate'] == OPEN_FALSE ? 0 : 1;
                    $nid = 0;
                    if (isset($args['nid']) && $args['nid']) {
                        $nid = $args['nid'];
                        unset($args['nid']);
                    }
                    $result = $push->pushSingleDevice($value['device_token'], $value['device_type'], $value['content'], $value['title'], $args, $nid);
    
                    if ($result['success'])
                    {
                        $table->getNotificationRecordsTable()->updateData($set_1, $where);
                    }
                    elseif ($result['fail'])
                    {
                        $table->getNotificationRecordsTable()->updateData($set_4, $where);
                    }
//                 }
            }
        }
        return;
    }
    
    public function pushForPlan() {
        
    }
    

    
    /**
     * 后台接收表单文件域传过来文件
     * 用于上传文件处理
     *
     * @author liujun
     * @return string 用于模板页面JS处理
     */
    public function ajaxGetFilesAction()
    {
        $file = $this->uploadImageForController('Filedata');
        if ($file['ids']) {
            $file = $file['files'][0]['Filedata'];
            echo ROOT_PATH . UPLOAD_PATH . $file['path'] . $file['filename'] . ',' . $file['id'];
        }
        else {
            $error = '上传失败，未知错误！';
        }
        die();
    }
    
    /**
     * 接收前端文件编码
     * 用于上传文件处理
     * 
     * @version 2016-5-13 WZ
     */
    public function ajaxGetDataAction() {
        $baseStr = isset($_POST['baseStr']) ? $_POST['baseStr'] : '';
        $file = $this->saveImage($baseStr);
        if (isset($file['files']) && $file['files']) {
            $file = $file['files'][0]['ajax'];
//             $return = array(
//                 'error' => '',
//                 'path' => $file['path'] . $file['filename'],
//                 'imgid' => $file['id']
//             );原来
            //ysq改
            $return = array(
                'error' => '',
                'img' => array(
                    'path' => $file['path'] . $file['filename'],
                    'id' => $file['id']
                )
            );
            echo json_encode($return);
            die();
        }
        else {
            echo json_encode(array(
                'error' => '上传失败，未知错误！',));
            exit;
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
    function saveImage($data, $path = LOCAL_SAVEPATH, $type = 1) {//define('LOCAL_SAVEPATH', APP_PATH . '/public/uploadfiles/');是本地保存图片的目录       define("APP_PATH", __DIR__);//系统目录
        if(!$data){
            return false;
        }
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
     * 获取所有子分类id
     * @param unknown $cid
     * @param number $deep
     * @version 2015-11-12 WZ
     */
    function getCategoryChildren($cid, $deep = 1) {
        $data = $this->getCategoryTable()->fetchAll(array('parent_id' => $cid), array('sort_order' => 'asc'));
        $category = array();
        if ($data) {
            foreach ($data as $value) {
                $category [] = $value['cat_id'];
            }
            
            foreach ($data as $value) {
                $category = array_merge($category, $this->getCategoryChildren($value['cat_id'], $deep + 1));
            }
        }
        array_unique($category);
        return $category;
    }
    
    /**
     * 获得用户的真实IP地址
     *
     * @access  public
     * @return  string
     */
    function realIp()
    {
        static $realip = NULL;
    
        if ($realip !== NULL)
        {
            return $realip;
        }
    
        if (isset($_SERVER))
        {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    
                /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
                foreach ($arr AS $ip)
                {
                    $ip = trim($ip);
    
                    if ($ip != 'unknown')
                    {
                        $realip = $ip;
    
                        break;
                    }
                }
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP']))
            {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }
            else
            {
                if (isset($_SERVER['REMOTE_ADDR']))
                {
                    $realip = $_SERVER['REMOTE_ADDR'];
                }
                else
                {
                    $realip = '0.0.0.0';
                }
            }
        }
        else
        {
            if (getenv('HTTP_X_FORWARDED_FOR'))
            {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            }
            elseif (getenv('HTTP_CLIENT_IP'))
            {
                $realip = getenv('HTTP_CLIENT_IP');
            }
            else
            {
                $realip = getenv('REMOTE_ADDR');
            }
        }
    
        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
        $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    
        return $realip;
    }
    
    /**
     * 输出并结束
     * 
     * @param unknown $return
     * @param string $type
     * @version 2015-11-20 WZ
     */
    public function returnMsg($return, $type = 'json') {
        if ('json' == $type) {
            echo json_encode($return);
            exit;
        }
    }
    
    /**
     * 获取微信支付信息
     * @param number $amount 金额
     * @param number $id 记录id或订单order_sn
     * @param number $type 1-2 1参与活动；2多个订单购买；3单个订单支付；
     * @param string $name 名称；
     * @param string $trade_type 使用类型，APP, JSAPI
     * @return multitype:number string |multitype:
     * @version 2015-11-20 WZ
     */
    public function getWxPayInfo($amount, $id, $type = 1, $name = '', $trade_type = 'JSAPI', $open_id = '')
    {
        $open_id = $open_id ? $open_id : (isset($_SESSION['wx_open_id']) ? $_SESSION['wx_open_id'] : '');
        $list = array(
            1 => '参与活动支付',
            2 => '订单支付',
            3 => '订单支付'
        );
        if (in_array(WX_IOSENV, array(1,2)) && array_key_exists($type, $list) && $id && $amount)
        {
            $value = array(
                'order_price' => $amount,
                'product_name' => $list[$type] . $name . $amount . '元',
                'out_trade_no' => $id.$type
            );
            if (WX_TEST_PAY) {
                $value ['order_price'] = 0.01; // 测试支付用
            }
    
            $wxpay = new AiiWxPay();
            return $wxpay->setValue($value)->getOutParams($trade_type, $open_id);
        }
        else {
            return false;
        }
    }
    
    /**
     * 支付宝支付
     * 
     * @param unknown $amount
     * @param unknown $id
     * @param unknown $type
     * @param unknown $name
     * @version 2015-12-16 WZ
     */
    public function getAlipayInfo($amount, $id, $type, $subject = '', $body = '', $param = '') {
//         return "暂停使用";
        
        include_once APP_PATH . '/vendor/Core/System/alipay/alipayapi.php';
        $alipay = new \alipayapi();
        $alipay->total_fee = $amount; // 付款金额
        $alipay->out_trade_no = $type.$id; // 订单号
        $alipay->subject = $subject;
        $alipay->body = $body;
        if (1 == $type) {
            $alipay->return_url = 'http://' . SERVER_NAME . ROOT_PATH . 'web/cperson/amywallet';
        }
        if (2 == $type) {
            $alipay->return_url = 'http://' . SERVER_NAME . ROOT_PATH . 'web/corder/asucceed/i'.$param.'/s4';
        }
        elseif (3 == $type) {
            $alipay->return_url = 'http://' . SERVER_NAME . ROOT_PATH . 'web/corder/asucceed/s3';
        }
        return $alipay->PostAlipay();
    }
    
    /**
     * 跳转获取openid
     * @return unknown
     * @version 2015-12-1 WZ
     */
    public function getOpenid()
    {
        //通过code获得openid
        if (3 == WX_IOSENV) {
            if (isset($_COOKIE['wx_open_id']) && $_COOKIE['wx_open_id']) {
                // 直接读取并返回
                setcookie('wx_open_id',$_COOKIE['wx_open_id'],time()+3600*24*30,'/');
                return $_COOKIE['wx_open_id'];
            }
            else {
                $open_id = $this->makeCode(32, 6);
                // 写入cookie并返回
                setcookie('wx_open_id',$open_id,time()+3600*24*30,'/');
                return $open_id;
            }
        }
        if(2 == WX_IOSENV) {
            $wxapi = new WxApi();
            return $wxapi->GetOpenid('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REDIRECT_URL']);
        }
        elseif (1 == WX_IOSENV) {
            if (!isset($_GET['open_id'])){
                //触发微信返回code码
                $baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REDIRECT_URL']);
                $url = "http://xgx.aiitec.net/geaiche/public/wxpay/openid.php?redirect_uri=" . $baseUrl;
                Header("Location: $url");
                exit();
            } else {
                //获取code码，以获取openid
                $openid = $_GET['open_id'];
                return $openid;
            }
        }
    }
}
