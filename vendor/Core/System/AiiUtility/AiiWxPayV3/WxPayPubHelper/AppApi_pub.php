<?php
/** APP_API支付类 (鲁铁辉2015-04-21). */
include_once("WxPayPubHelper.php");
	
/**
* AppAPI支付——第三方APP调起支付接口
*/
class AppApi_pub extends Common_util_pub
{
	var $parameters;//appapi参数，格式为array()
	var $prepay_id;//使用统一支付接口得到的预支付id
	var $curl_timeout;//curl超时时间

	function __construct() 
	{
		//设置curl超时时间
		$this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
	}
	
	/**
	 * 	作用：设置prepay_id
	 */
	function setPrepayId($prepayId)
	{
		$this->prepay_id = $prepayId;
	}

	/**
	 * 	作用：设置appapi的参数
	 */
	public function getParameters($trade_type)
	{
	    if ('APP' == $trade_type) {
	        //输出参数列表
	        $appApiObj = array();
	        $appApiObj['appid']			= WxPayConf_pub::APPID;
	        $appApiObj['partnerid']		= WxPayConf_pub::MCHID;
	        $appApiObj['prepayid']		= $this->prepay_id;
	        $appApiObj['package']		= 'Sign=WXPay';
	        $appApiObj['noncestr']		= $this->createNoncestr();
	        $appApiObj['timestamp']		= time() . '';
	        
	        $sign = $this->getSign($appApiObj);
	        $appApiObj['sign']			= $sign;
	        $this->parameters = $appApiObj;
	    }
		elseif ('JSAPI' == $trade_type) {
		    $jsApiObj = array();
		    $jsApiObj['appId']          = WxPayConf_pub::APPID;
		    $jsApiObj['timeStamp']		= time() . '';
		    $jsApiObj['nonceStr']       = $this->createNoncestr();
		    $jsApiObj['package']        = 'prepay_id=' . $this->prepay_id;
		    $jsApiObj['signType']       = 'MD5';
		    $sign = $this->getSign($jsApiObj);
		    $jsApiObj['paySign']        = $sign;
		    
		    $this->parameters = $jsApiObj;
		}
		
		return $this->parameters;
	}
}

?>