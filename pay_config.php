<?php
include_once 'pay_key.php';
//支付宝配置信息
define('ALIPAY_APP_ID', '2016101802218919');//应用ID,您的APPID。
define('ALIPAY_MERCHANT_PRIVATE_KEY', $alipay_merchant_private_key);//商户私钥，您的原始格式RSA私钥
define('ALIPAY_APP_APP_ID', '2088811275937623');//APP支付应用ID,您的APPID。
define('ALIPAY_APP_MERCHANT_PRIVATE_KEY', $alipay_app_merchant_private_key);//APP支付商户私钥，您的原始格式RSA私钥
define('ALIPAY_APP_SELLER_ID','ktxo2o@126.com');

define('ALIPAY_PUBLIC_KEY', $alipay_public_key); //支付宝H5公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
define('ALIPAY_APP_PUBLIC_KEY', $alipay_app_public_key); //支付宝APP公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。


define('ALIPAY_NOTIFY_URL', 'http://ketx.aiitec.org/api/index/getAlipayNotify');//异步通知地址
define('ALIPAY_RETURN_URL', 'http://ketx.aiitec.org/api/index/getAlipayNotify');//同步回调地址
//微信公众平台配置信息
define('WX_APPID', 'wxfd4107b80c08bcd3'); //绑定支付的APPID（公众平台）APPID（必须配置，开户邮件中可查看）
define('WX_MCHID', '1384063702'); //商户号（必须配置，开户邮件中可查看）
define('WX_KEY', '382eab946a217ddb1729ce65992ac044'); //商户支付密钥
define('WX_APPSECRET', '6d81a453267fe64145cae0f407147671');//公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置）
define("WX_NOTIFY_URL", "http://ketx.aiitec.org/api/index/getWxPayNotify");
define("WX_NOTIFY_URL_GO", "http://www.ktxgo.com/index/getWxPayNotify");

//微信开放台配置信息
define('WX_APPID_K', 'wxac599ace2b88ee76'); //绑定支付的APPID,APPID（必须配置，开户邮件中可查看）
define('WX_MCHID_K', '1483677122'); //商户号（必须配置，开户邮件中可查看）
define('WX_KEY_K', 'b01bb05de1837c88b107255b5e409fa5'); //商户支付密钥


//h5支付参数
define('MWEB_URL', 'http://www.ktxgo.com');//WAP网站URL地址
define('MWEB_NAME', '客天下商城');//WAP 网站名