<?php
namespace Core\System\AiiUtility\AiiEncryptVerify;

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
     * 万能钥匙，方便调试
     *
     * @var 111
     */
    const MASTER_KEY = '111';

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

    /**
     * 协议安全校验
     *
     * @param string $json            
     * @return boolean
     * @version 2014-12-12 WZ
     */
    public function check($json, $string = '')
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
        if (self::MASTER_KEY == $json->m)
        {
//             return true;
        }
        
        if (! isset($json->m) || ! $json->m)
        {
            return false;
        }
        
        $md5 = $json->m;
        unset($json->m);
        $data = json_encode($json, JSON_UNESCAPED_UNICODE);
        
        $key = $this->getKey();
        $md5_1 = md5($data);
        $md5_2 = md5($md5_1 . $string . $key);
        
        if (self::DEBUG_SWITCH)
        {
            echo "<pre>";
            echo "请求：" . $json_string . "<br />";
            echo "转数组：" . "<br />";
            var_dump($json);
            echo "再转字符串（去除m后）：" . $data . "<br />";
            echo "Key：" . $key . "<br />";
            echo "额外字符：" . $string . "<br />";
            echo "请求的md5：" . $md5 . "<br />";
            echo "字符串生成md5：" . $md5_1 . "<br />";
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

    /**
     * 获取key，生成Key的方式可以根据项目改变
     *
     * @return string
     * @version 2014-12-12 WZ
     */
    private function getKey()
    {
        $key = date('Ymd');
        $last = $key % 10;
        $mod = $key % 8;
        $array = array();
        
        for ($i = 0; $i < strlen($key) - 1; $i ++)
        {
            if ($i == $mod)
            {
                $array[] = $last;
            }
            $array[] = $key[$i];
        }
        if ($i == $mod)
        {
            $array[] = $last;
        }
        
        $number = implode('', $array);
        $bin = decbin($number);
        if (self::DEBUG_SWITCH)
        {
            echo "<pre>";
            echo "Key的生成：" . "<br />";
            echo "日期：" . $key . "<br />";
            echo "尾数：" . $last . "<br />";
            echo "余数：" . $mod . "<br />";
            echo "转化后：" . $number . "<br />";
            echo "转二进制：" . $bin . "<br />";
            echo "</pre>";
        }
        return $bin;
    }
}
?>
