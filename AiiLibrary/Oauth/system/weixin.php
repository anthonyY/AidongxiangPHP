<?php
class weixin extends WxApi {

    function __construct($access_token = NULL, $refresh_token = NULL) {
       
    }

    function verify() {
        if (isset($_SESSION['wx_token']) && $_SESSION['wx_token'] && isset($_SESSION['wx_token']['uid'])) {
            return true;
        } else {
            return false;
        }
    }

}

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
class WxApi
{
	const appId = WX_AKEY; // 应用ID
	const appSecret = WX_SKEY; // 应用密钥
	const mchid = ""; // 商户号
	const privatekey = ""; // 私钥
	public $parameters = array ();
	public $jsApiTicket = NULL;
	public $jsApiTime = NULL;
	
	public function __construct()
	{
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
	 * 微信获取AccessToken 返回指定微信公众号的at信息
	 * **************************************************
	 */
	public function wxAccessToken($code)
	{
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . self::appId  . "&secret=" .  self::appSecret."&code=".$code."&grant_type=authorization_code";
		$result = $this->wxHttpsRequest ( $url );
		// print_r($result);
		$jsoninfo = json_decode ( $result, true );
		return $jsoninfo;
	}
	
	/**
	 * **************************************************
	 * 微信通过OPENID获取用户信息，返回数组
	 * **************************************************
	 */
	public function wxGetUser($openId,$wxAccessToken)
	{
		$url = "http://api.weixin.qq.com/sns/userinfo?access_token=" . $wxAccessToken . "&openid=" . $openId;
		$result = $this->wxHttpsRequest ( $url );
		$jsoninfo = json_decode ( $result, true );
		return $jsoninfo;
	}

	/**
	 * **************************************************
	 * 微信生成二维码ticket
	 * **************************************************
	 */
	public function wxQrCodeTicket($jsonData)
	{
		$wxAccessToken = $this->wxAccessToken ();
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $wxAccessToken;
		$result = $this->wxHttpsRequest ( $url, $jsonData );
		return $result;
	}
	
	/**
	 * **************************************************
	 * 微信通过ticket生成二维码
	 * **************************************************
	 */
	public function wxQrCode($ticket)
	{
		$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode ( $ticket );
		return $url;
	}
	
	/**
	 * **************************************************
	 * 微信设置OAUTH跳转URL，返回字符串信息 - SCOPE = snsapi_base //验证时不返回确认页面，只能获取OPENID
	 * **************************************************
	 */
	public function wxOauthBase($redirectUrl, $state = "", $appId = NULL)
	{
		$appId = is_null ( $appId ) ? self::appId : $appId;
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appId . "&redirect_uri=" . $redirectUrl . "&response_type=code&scope=snsapi_base&state=" . $state . "#wechat_redirect";
		return $url;
	}
	
	/**
	 * **************************************************
	 * 微信设置OAUTH跳转URL，返回字符串信息 - SCOPE = snsapi_userinfo //获取用户完整信息
	 * **************************************************
	 */
	public function wxOauthUserinfo($redirectUrl, $state = "", $appId = NULL)
	{
		$appId = is_null ( $appId ) ? self::appId : $appId;
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appId . "&redirect_uri=" . $redirectUrl . "&response_type=code&scope=snsapi_userinfo&state=" . $state . "#wechat_redirect";
		return $url;
	}
	
	/**
	 * 拼接请求code 地址
	 * @version 2015年8月26日 
	 * @author liujun
	 */
	public function getAuthorizeURL($callback,$code,$state,$scope)
	{
	    $url = "https://open.weixin.qq.com/connect/qrconnect?appid=" . self::appId . "&redirect_uri=" . urlencode($callback) . "&response_type=".$code."&scope=".$scope."&state=" . $state . "#wechat_redirect";
	    return $url;
	}
	
	/**
	 * **************************************************
	 * 微信OAUTH跳转指定URL
	 * **************************************************
	 */
	public function wxHeader($url)
	{
		header ( "location:" . $url );
	}
	
	/**
	 * **************************************************
	 * 微信通过OAUTH返回页面中获取AT信息
	 * **************************************************
	 */
	public function wxOauthAccessToken($code, $appId = NULL, $appSecret = NULL)
	{
		$appId = is_null ( $appId ) ? self::appId : $appId;
		$appSecret = is_null ( $appSecret ) ? self::appSecret : $appSecret;
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appId . "&secret=" . $appSecret . "&code=" . $code . "&grant_type=authorization_code";
		$result = $this->wxHttpsRequest ( $url );
		// print_r($result);
		$jsoninfo = json_decode ( $result, true );
		// $access_token = $jsoninfo["access_token"];
		return $jsoninfo;
	}
	
	/**
	 * **************************************************
	 * 微信通过OAUTH的Access_Token的信息获取当前用户信息 // 只执行在snsapi_userinfo模式运行
	 * **************************************************
	 */
	public function wxOauthUser($OauthAT, $openId)
	{
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $OauthAT . "&openid=" . $openId . "&lang=zh_CN";
		$result = $this->wxHttpsRequest ( $url );
		$jsoninfo = json_decode ( $result, true );
		return $jsoninfo;
	}
	
	/**
	 * ***************************************************
	 * 生成随机字符串 - 最长为32位字符串
	 * ***************************************************
	 */
	public function wxNonceStr($length = 16, $type = FALSE)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for($i = 0; $i < $length; $i ++)
		{
			$str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
		}
		if ($type == TRUE)
		{
			return strtoupper ( md5 ( time () . $str ) );
		}
		else
		{
			return $str;
		}
	}
	
	/**
	 * *****************************************************
	 * 微信格式化数组变成参数格式 - 支持url加密
	 * *****************************************************
	 */
	public function wxSetParam($parameters)
	{
		if (is_array ( $parameters ) && ! empty ( $parameters ))
		{
			$this->parameters = $parameters;
			return $this->parameters;
		}
		else
		{
			return array ();
		}
	}
	
	/**
	 * *****************************************************
	 * 微信格式化数组变成参数格式 - 支持url加密
	 * *****************************************************
	 */
	public function wxFormatArray($parameters = NULL, $urlencode = FALSE)
	{
		if (is_null ( $parameters ))
		{
			$parameters = $this->parameters;
		}
		$restr = ""; // 初始化空
		ksort ( $parameters ); // 排序参数
		foreach ( $parameters as $k => $v )
		{ // 循环定制参数
			if (null != $v && "null" != $v && "sign" != $k)
			{
				if ($urlencode)
				{ // 如果参数需要增加URL加密就增加，不需要则不需要
					$v = urlencode ( $v );
				}
				$restr .= $k . "=" . $v . "&"; // 返回完整字符串
			}
		}
		if (strlen ( $restr ) > 0)
		{ // 如果存在数据则将最后“&”删除
			$restr = substr ( $restr, 0, strlen ( $restr ) - 1 );
		}
		return $restr; // 返回字符串
	}
	
	/**
	 * *****************************************************
	 * 微信MD5签名生成器 - 需要将参数数组转化成为字符串[wxFormatArray方法]
	 * *****************************************************
	 */
	public function wxMd5Sign($content, $privatekey)
	{
		try
		{
			if (is_null ( $privatekey ))
			{
				throw new \Exception ( "财付通签名key不能为空！" );
			}
			if (is_null ( $content ))
			{
				throw new \Exception ( "财付通签名内容不能为空" );
			}
			$signStr = $content . "&key=" . $privatekey;
			return strtoupper ( md5 ( $signStr ) );
		}
		catch ( \Exception $e )
		{
			die ( $e->getMessage () );
		}
	}
	
	/**
	 * *****************************************************
	 * 微信Sha1签名生成器 - 需要将参数数组转化成为字符串[wxFormatArray方法]
	 * *****************************************************
	 */
	public function wxSha1Sign($content)
	{
		try
		{
			if (is_null ( $content ))
			{
				throw new \Exception("签名内容不能为空" );
			}
			// $signStr = $content;
			return sha1 ( $content );
		}
		catch ( \Exception $e )
		{
			die ( $e->getMessage () );
		}
	}
	
	/**
	 * *****************************************************
	 * 微信jsApi整合方法 - 通过调用此方法获得jsapi数据
	 * *****************************************************
	 */
	public function wxJsapiPackage()
	{
		$jsapi_ticket = $this->wxVerifyJsApiTicket ();
		
		// 注意 URL 一定要动态获取，不能 hardcode.
		$protocol = (! empty ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] !== 'off' || $_SERVER ['SERVER_PORT'] == 443) ? "https://" : "http://";
		$url = $protocol . $_SERVER ["HTTP_HOST"] . $_SERVER ["REQUEST_URI"];
		
		$timestamp = time ();
		$nonceStr = $this->wxNonceStr ();
		
		$signPackage = array (
				"jsapi_ticket" => $jsapi_ticket,
				"nonceStr" => $nonceStr,
				"timestamp" => $timestamp,
				"url" => $url 
		);
		
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$rawString = "jsapi_ticket=$jsapi_ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
		
		// $rawString = $this->wxFormatArray($signPackage);
		$signature = $this->wxSha1Sign ( $rawString );
		
		$signPackage ['signature'] = $signature;
		$signPackage ['rawString'] = $rawString;
		$signPackage ['appId'] = self::appId;
		
		return $signPackage;
	}

}