<?php

namespace Core\System\WxApi;

/**
 * ******************************************************
 *
 * @author Kyler You <QQ:2444756311>
 * @link http://mp.weixin.qq.com/wiki/home/index.html
 * @version 2.2
 * @uses $wxApi = new WxApi();
 * @package 微信API接口 陆续会继续进行更新
 *          ******************************************************
 */
class WxAppApi
{
    const appId = WECHATAPP_APPID; // 应用ID
    const appSecret = WECHATAPP_APPSECRET; // 应用密钥
    const mchid = WX_MCHID_K; // 商户号
    const privatekey = WX_KEY_K; // 私钥
    const token = 'nissan'; // Token
    const encodeingkey = 'nissan'; // 加密字符串
    public $parameters = array ();
    public $jsApiTicket = NULL;
    public $jsApiTime = NULL;

    public function __construct()
    {
        $this->cachePath = defined('APP_PATH') ? APP_PATH . '/Cache/' : __DIR__ . '/';
    }

    /**
     * **************************************************
     * 微信提交API方法，返回微信指定JSON
     * **************************************************
     */
    public function wxHttpsRequest($url, $data = null)
    {
        $curl = curl_init ();
        curl_setopt ( $curl, CURLOPT_URL, $url );
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        if (! empty ( $data ))
        {
            curl_setopt ( $curl, CURLOPT_POST, 1 );
            curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data );
        }
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
        $output = curl_exec ( $curl );
        curl_close ( $curl );
        return $output;
    }

    /**
     * **************************************************
     * 微信带证书提交数据 - 微信红包使用
     * **************************************************
     */
    public function wxHttpsRequestPem($url, $vars, $second = 30, $aHeader = array())
    {
        $ch = curl_init ();
        // 超时时间
        curl_setopt ( $ch, CURLOPT_TIMEOUT, $second );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        // 这里设置代理，如果有的话
        // curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
        // curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );

        // 以下两种方式需选择一种

        // 第一种方法，cert 与 key 分别属于两个.pem文件
        // 默认格式为PEM，可以注释
        curl_setopt ( $ch, CURLOPT_SSLCERTTYPE, 'PEM' );
        curl_setopt ( $ch, CURLOPT_SSLCERT, getcwd () . '/apiclient_cert.pem' );
        // 默认格式为PEM，可以注释
        curl_setopt ( $ch, CURLOPT_SSLKEYTYPE, 'PEM' );
        curl_setopt ( $ch, CURLOPT_SSLKEY, getcwd () . '/apiclient_key.pem' );

        curl_setopt ( $ch, CURLOPT_CAINFO, 'PEM' );
        curl_setopt ( $ch, CURLOPT_CAINFO, getcwd () . '/rootca.pem' );

        // 第二种方式，两个文件合成一个.pem文件
        // curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');

        if (count ( $aHeader ) >= 1)
        {
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, $aHeader );
        }

        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $vars );
        $data = curl_exec ( $ch );
        if ($data)
        {
            curl_close ( $ch );
            return $data;
        }
        else
        {
            $error = curl_errno ( $ch );
            echo "call faild, errorCode:$error\n";
            curl_close ( $ch );
            return false;
        }
    }

    /**
     * **************************************************
     * 微信获取AccessToken 返回指定微信公众号的at信息
     * **************************************************
     */
    public function wxAccessToken($appId = NULL, $appSecret = NULL ,$reSet = false)
    {
        $appId = is_null($appId) ? self::appId : $appId;
        $filename = $this->cachePath . $appId . "_access_token.json";
        if (is_file($filename)) {
            $data = file_get_contents($filename);
            $data = json_decode($data, true);
            if (isset($data['access_token']) && isset($data['expire_time']) && $data['expire_time'] > time() && !$reSet) {
                return $data['access_token'];
            }
        }
        $appSecret = is_null($appSecret) ? self::appSecret : $appSecret;
        // 如果是企业号用以下URL获取access_token
        // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=" . $appId . "&corpsecret=" . $appSecret;
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
        $result = $this->wxHttpsRequest($url);
        // print_r($result);
        $jsoninfo = json_decode($result, true);
        $access_token = $jsoninfo["access_token"];
        $data = array(
            'expire_time' => time() + $jsoninfo['expires_in'] - 600,
            'access_token' => $access_token
        );
        @file_put_contents($filename, json_encode($data));
        return $access_token;
    }

    /**
     * **************************************************
     * 微信获取ApiTicket 返回指定微信公众号的at信息
     * **************************************************
     */
    public function wxJsApiTicket($appId = NULL, $appSecret = NULL)
    {
        $appId = is_null ( $appId ) ? self::appId : $appId;
        $appSecret = is_null ( $appSecret ) ? self::appSecret : $appSecret;

        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=" . $this->wxAccessToken ();
        $result = $this->wxHttpsRequest ( $url );
        $jsoninfo = json_decode ( $result, true );
        $ticket = $jsoninfo ['ticket'];
        // echo $ticket . "<br />";
        return $ticket;
    }

    /**
     * **************************************************
     * 微信小程序登录获取openid和session_key
     * **************************************************
     */
    public function wxLogin($code, $appId = null, $appSecret = null)
    {
        $appId = is_null ( $appId ) ? self::appId : $appId;
        $appSecret = is_null ( $appSecret ) ? self::appSecret : $appSecret;
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appId}&secret={$appSecret}&js_code={$code}&grant_type=authorization_code";
        $result = $this->wxHttpsRequest ( $url );
        return json_decode ( $result, true );
    }


    /**
     * **************************************************
     * 微信通过指定模板信息发送给指定用户，发送完成后返回指定JSON数据
     * **************************************************
     */
    public function sendTemplate($jsonData)
    {
//        var_dump($wxAccessToken);exit();
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $this->wxAccessToken();
        $result = $this->wxHttpsRequest($url, $jsonData);
        $result = json_decode($result);
        if($this->checkResult($result)){
            $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $this->wxAccessToken();
            $result = $this->wxHttpsRequest($url, $jsonData);
            $result = json_decode($result);
        };
        return $result;
    }


    public function checkSignature($str='')
    {
        $signature = isset($_GET["signature"])?$_GET["signature"]:'';
        $signature = isset($_GET["msg_signature"])?$_GET["msg_signature"]:$signature; //如果存在加密验证则用加密验证段
        $timestamp = isset($_GET["timestamp"])?$_GET["timestamp"]:'';
        $nonce = isset($_GET["nonce"])?$_GET["nonce"]:'';
        $token = self::token;
        $tmpArr = array($token, $timestamp, $nonce,$str);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }


    public function getRev()
    {
        $data = file_get_contents("php://input");
        return json_decode($data);
    }


    public function checkResult($rs)
    {
        if($rs->errcode == 40001){
            $this->wxAccessToken(null, null, true);
            return 40001;
        }
        return 0;
    }
    
    
    /**
     * 回复客服消息
     * */
    public function sendCustomMsg($jsonData)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->wxAccessToken();
        $result = $this->wxHttpsRequest($url, $jsonData);
        $result = json_decode($result);
        if($this->checkResult($result)){
            $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->wxAccessToken();
            $result = $this->wxHttpsRequest($url, $jsonData);
            $result = json_decode($result);
        };
        return $result;
    }



}