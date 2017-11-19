<?php
namespace AiiLibrary\AiiEncryptVerify;

defined('CHECK_API_MD5') ? "" : define('CHECK_API_MD5', true); // 有一个常量控制开关，如果没有，默认是开
defined('CHECK_API_DEBUG_SWITCH') ? "" : define('CHECK_API_DEBUG_SWITCH', false); // 有一个常量控制开关，如果没有，默认是关

/**
 * 协议安全验证类
 *
 * @author WZ
 * @version 1.0.150116
 */
class AiiEncryptVerify
{

    /**
     * 开关
     *
     * @var boolen
     */
    const CHECK_SWITCH = CHECK_API_MD5;

    /**
     * 调试开关
     *
     * @var boolen
     */
    const DEBUG_SWITCH = CHECK_API_DEBUG_SWITCH;

    const API_KEY = API_KEY;

    /**
     * 协议安全校验
     *
     * @param string $json            
     * @return boolean
     * @version 2014-12-12 WZ
     */
    public function check($json)
    {
        if (! self::CHECK_SWITCH)
        {
            return true;
        }
        $json_string = $json;
        $json = json_decode($json);
        if (! $json || ! isset($json->m))
        {
            return false;
        }
        
        if (! isset($json->m) || ! $json->m)
        {
            return false;
        }
        
        $md5 = $json->m;
        unset($json->m);
        $data = json_encode($json, JSON_UNESCAPED_UNICODE);
        $md5_2 = md5($data . API_KEY);
        
        if (self::DEBUG_SWITCH)
        {
            echo "<pre>";
            echo "请求：" . $json_string . "<br />";
            echo "再转字符串（去除m后）：" . $data . "<br />";
            echo "请求的m：" . $md5 . "<br />";
            echo "带Key生成md5：" . $md5_2 . "<br />";
            echo "比对结果：" . (($md5 == $md5_2) ? 'true' : 'false') . "<br />";
            echo "</pre>";
        }
        if ($md5 == $md5_2)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
?>
