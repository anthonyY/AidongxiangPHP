<?php
namespace Core\System\AiiUtility\AiiPush;

/**
 * 推送接口
 *
 * @author WZ
 *        
 */
class AiiPushBase
{

    /**
     * 文件类
     */
    public $myfile;

    /**
     * 推送接口的id
     * 
     * @var unknown
     */
    public $_access_id;

    /**
     * 推送接口的key
     * 
     * @var unknown
     */
    public $vibrate;

    /**
     * iOS的使用版本
     * 1 PROD ; 2 DEV
     * 
     * @var number
     */
    public $_iosenv;

    /**
     * 构造函数
     */
    function __construct()
    {
        require_once __DIR__ . '/config/config.php';
        $this->myfile = new AiiMyFile();
        $this->init();
    }

    /**
     * 设置参数，子类注意改写此方法
     */
    public function init()
    {
    }

    /**
     * 模拟post进行url请求
     * 
     * @param string $url            
     * @param string $param            
     */
    public function http_post($url = '', $param = '')
    {
        if (empty($url) || empty($param))
        {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); // 初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); // 抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); // 设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); // post提交方式
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 超时时间
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch); // 运行curl
        curl_close($ch);
        return $data;
    }
    
    
    function sendCurlPost($url, $dataObj) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($curl);
        if (false == $ret) {
            // curl_exec failed
            $result = "{ \"result\":" . -2 . ",\"errmsg\":\"" . curl_error($curl) . "\"}";
        } else {
            $rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "{ \"result\":" . -1 . ",\"errmsg\":\"". $rsp . " " . curl_error($curl) ."\"}";
            } else {
                $result = $ret;
            }
        }
        curl_close($curl);
        return $result;
    }

    /**
     * 暂时没写，用到再写
     * 模拟get操作回去内容
     * 
     * @param string $url 地址
     * @param string $param 参数，字符串或数组
     * @version 2014-11-6 WZ
     */
    public function http_get($url = '', $param = '')
    {
        if(is_array($param))
        {
            $new_param = '';
            foreach($param as $key => $value)
            {
                $new_param .= ($new_param ? '&' : '') . $key.'='.$value;
            }
        }
        elseif(is_string($param))
        {
            $new_param = $param;
        }
        
        $new_url = $url . ($new_param ? ('?' . $new_param) : '');
        return file_get_contents($new_url);
    }
}
?>