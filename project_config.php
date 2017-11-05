<?php

define("PRODUCT_NAME", "一起聚餐");

// 
/** 高德地图key @var string */
define('AMAP_KEY', 'd4e906f2d3b338bc0fa31cba41b4c5ca');
// 信鸽安卓
define('XINGE_ANDROID_ACCESS_ID','2100244327');
define('XINGE_ANDROID_SECRET_KEY','98dcd684d9e84f49386a7191e841130f');

// 信鸽iOS
define('XINGE_IOS_ACCESS_ID','2200244328');
define('XINGE_IOS_SECRET_KEY','750c8590923a1fe4ebd999daae0fdb29');
define('XINGE_IOSENV' , 2); //  1 PROD ; 2 DEV

// 微信
define('PUSH_WEIXIN_APPID', '');
define('PUSH_WEIXIN_APPSECRET', '');

/*
 * 短信平台
*/
define('SMS_SERVICE', 6); // 1亿美；2梦网；3未知名字的平台  http://211.147.244.114:9801/CASServer/SmsAPI/SendMessage.jsp
                          // 4云测；5企信；6腾讯云；7短信宝
define('SMS_URL', 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms');
define('SMS_USERID','1400019082'); // 4:apiKey
define('SMS_PASSWOED', 'd332ba33175bb09b0f74c7d8e16c11de'); // 4:secretKey
define('SMS_NUMBER', '');// 4:短信模板

/*
 * 短信限制
 */
/** 同一个IP，24小时内可以获取验证码的数量（0表示不限制） */
define('SMS_LIMIT_IP', 50);
/** 同一个手机号码，24小时内可以获取验证码的数量（0表示不限制） */
define('SMS_LIMIT_MOBILE', 5);
/** 同一个SessionId可以获取验证码的数量（0表示不限制） */
define('SMS_LIMIT_SESSION_ID', 5);
/** 24小时内可以获取验证码的数量（0表示不限制） */
define('SMS_LIMIT_DAY', 1000);

// 微信API配置 聚餐
//define('WEIXIN_APP_ID', 'wx38a572b2aa8359c2');//微信应用ID
//define('WEIXIN_APP_SECRET','d666f84ec1ba5089df08472049c7da27');//微信应用钥匙
//define('WEIXIN_MCHID','1458762402'); //商户号
//define('WEIXIN_PRIVATEKEY','yqjc2017yqjc2017yqjc2017yqjc2017');

//公司的微信服务号
define('WEIXIN_APP_ID', 'wxfd4107b80c08bcd3');//微信应用ID
define('WEIXIN_APP_SECRET','6d81a453267fe64145cae0f407147671');//微信应用钥匙
define('WEIXIN_MCHID','1384063702'); //商户号
define('WEIXIN_PRIVATEKEY','382eab946a217ddb1729ce65992ac044');

define('WX_NOTIFY_URL','http://' . SERVER_NAME . ROOT_PATH . 'web/index/getWxPayNotify'); //异步回调
define('WX_TEST_PAY', true); // 支付测试，true只支付1分
define('WX_IOSENV', 2); // 1 跳转获取（key与域名不一致）; 2 本地获取（key与域名一致）；3网页Cookies模拟open_id；
define('SEND_KEY', false);//是否推送 true 为推送  false 不推送
/**
 * 环境配置
 *1测试 2生产
 * @var boolen
 */
define("IS_DEBUG",1);
define("PAY_DEBUG",1);

/**
 * 二维码链接token
 * @var Number
 */
define('QRCODE_TOKEN' , 'AiitecJUEniao');
/**
 * 二维码链接
 */
define('QRCODE_URL','https://' . SERVER_NAME . ROOT_PATH . 'web/ScanCode/getActvity?qrCode=');
define('DIS_QRCODE_URL','https://' . SERVER_NAME . ROOT_PATH . 'web?disId=');