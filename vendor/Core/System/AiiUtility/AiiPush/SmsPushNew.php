<?php
namespace Core\System\AiiUtility\AiiPush;

/**
 * 新短信发送平台
 *
 * @author WZ
 *        
 */
class SmsPushNew extends AiiPushBase
{

    private $url = '';

    private $userid = '';

    private $password = '';

    function __destruct()
    {
    }

    /**
     * 初始化
     */
    public function init()
    {
        $this->url = SMS_URL;
        $this->userid = SMS_USERID;
        $this->password = SMS_PASSWOED;
    }

    /**
     * 发送一个
     *
     * @param array $mobile
     *            电话号码
     * @param string $content
     *            发送的内容
     * @return bool $result
     */
    public function pushSingleDevice($mobile, $content)
    {
        $result = $this->sendMessage(array(
            $mobile
        ), $content);
        if (true !== $result)
        {
            $this->myfile->putAtEnd($result);
        }
        return $result;
    }

    /**
     * 批量发送
     *
     * @param array $deviceTokens
     *            id,device_token
     * @param string $content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function pushCollectionDevice($deviceTokens, $content)
    {
        $res_arr = array(
            'success' => array(),
            'fail' => array(),
            'errcode' => 0
        );
        
        $mobile_arr = array();
        $id_arr = array();
        foreach ($deviceTokens as $value)
        {
            if ($value['device_token'] and strlen($value['device_token']) == 11)
            {
                $mobile_arr[] = $value['device_token'];
            }
            $id_arr[] = $value['id'];
        }
        $result = $this->sendMessage($mobile_arr, $content);
        if ($result === true)
        {
            $res_arr['success'] = $id_arr;
        }
        else
        {
            $res_arr['fail'] = $id_arr;
            $this->myfile->putAtEnd($result);
        }
        return $res_arr;
    }

    /**
     *
     * @param array $mobile_array
     *            设备码
     * @param string $content
     *            推送内容
     * @return $msg ios用的msg
     */
    private function sendMessage($mobile_array, $content)
    {
        $qUrl = $this->url;
        
        $qUrl .= '?userid=' . $this->userid . '&password=' . urlencode($this->password) . '&destnumbers=' . implode(',', $mobile_array) . '&msg=' . urlencode($content); // .'&sendtime='.$sendtime
                                                                                                                                                                         // echo $qUrl;
        if (function_exists('file_get_contents'))
        { // (PHP 4 >= 4.3.0, PHP 5)
            $xmlstring = file_get_contents($qUrl);
        }
        else 
            if (function_exists('fopen'))
            { // (PHP 3, PHP 4, PHP 5)
                $fopenXML = fopen($qUrl, 'r');
                
                if ($fopenXML)
                {
                    while (! feof($fopenXML))
                    {
                        $xmlstring .= fgets($fopenXML, 4096);
                    }
                    fclose($fopenXML);
                }
            }
        
        if ($xmlstring && trim($xmlstring))
        {
            if (function_exists('simplexml_load_string'))
            { // PHP5.0以上版本(PHP 5)
                $xml = simplexml_load_string($xmlstring);
                $retinfo = $xml['return'] . ',' . $xml['info'];
                if ('' . $xml['return'] === '0')
                {
                    $info = '总计号码个数:' . $xml['numbers'] . '<br />';
                    $info .= '总计短信条数:' . $xml['messages'] . '<br />';
                    /*
                     * $info .= '移动号码个数:'.$xml->yd['numbers'].'<br />';
                     * $info .= '移动短信条数:'.$xml->yd['messages'].'<br />';
                     * $info .= '联通号码个数:'.$xml->lt['numbers'].'<br />';
                     * $info .= '联通短信条数:'.$xml->lt['messages'].'<br />';
                     * $info .= '小灵通号码个数:'.$xml->xlt['numbers'].'<br />';
                     * $info .= '小灵通短信条数'.$xml->xlt['messages'].'<br />';
                     */
                    return true;
                }
                else
                {
                    return $retinfo;
                }
            }
        }
        return false;
    }
}
?>