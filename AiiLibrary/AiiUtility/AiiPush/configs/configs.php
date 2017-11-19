<?php
/*
 * log配置
 */
define('LOG_FILENAME', __DIR__ . '/../log/log_'.date('Ymd').'.txt');

// 信鸽安卓
define('XINGE_ANDROID_ACCESS_ID','2100053995');
define('XINGE_ANDROID_SECRET_KEY','81a39d17388e8839bb8fb75a7d8df958');

// 信鸽安卓商家端
define('XINGE_ANDROID_BIZ_ACCESS_ID','2100053997');
define('XINGE_ANDROID_BIZ_SECRET_KEY','f3a3be8f78777fd6c411860df47f30ca');

// 信鸽iOS 用户端
define('XINGE_IOS_ACCESS_ID','2200053998');
define('XINGE_IOS_SECRET_KEY','b0619f9c6900ef07bda102e02d12836a');
define('XINGE_IOSENV' , 2); //  1 PROD ; 2 DEV

// 信鸽iOS 商家端
define('XINGE_IOS_BIZ_ACCESS_ID','2200130305');
define('XINGE_IOS_BIZ_SECRET_KEY','abbbf3c7a1fdc40b2408bd039a051fd6');


/*
 * 短信发送平台相关参数
 * $url 			网关地址
 * $serialNumber 	序列号,请通过亿美销售人员获取
 * $password		密码,请通过亿美销售人员获取
 * $sessionKey	登录后所持有的SESSION KEY，即可通过login方法时创建
 */
define('SMS_URL','http://sdk999ws.eucp.b2m.cn:8080/sdk/SDKService?wsdl');
define('SMS_SERIALNUMBER','9SDK-EMY-0999-JDWPR');
define('SMS_PASSWOED','19f9c6');
define('SMS_SESSIONKEY','88e883');

/*
 * 新短信平台
 */
// 2015.12.29 改
define('MW_SMS_URL','http://61.130.7.220:8023/MWGate/wmgw.asmx');
define('MW_SMS_USERID','J51077');
define('MW_SMS_PASSWOED','258961');
// define('MW_SMS_URL','http://61.145.229.29:9006/MWGate/wmgw.asmx');
// define('MW_SMS_USERID','JC2291');
// define('MW_SMS_PASSWOED','568100');

/*
 * 状态
 */
define('STATUS_0','id为%s的消息已经全部发送完毕，共%d条，成功%d条，失败%d条。');
define('STATUS_11001','msg：11001，id为%s的消息未完全发送，本次发送类型为%d，共%d条，成功%d条，失败%d条。');
define('STATUS_12001','err：12001，配置文件%s不存在，请检查是否在指定目录。');
define('STATUS_13001','err：13001，%d条短信发送失败，请检测配置文件或者联系供应商。');
define('STATUS_14001','err：14001，缺少%s');
define('STATUS_18000','err：18000，连接数据库失败，请检测地址%s@%s，密码%s，是否正确。');
define('STATUS_18001','err：18001，连接数据库错误，检测sql语句。%s  by getNotification');
define('STATUS_18002','err：18002，连接数据库错误，检测sql语句。%s  by getDevice_tokens');
define('STATUS_18003','err：18003，连接数据库错误，检测sql语句。%s  by updateNotification');
define('STATUS_18004','err：18004，连接数据库错误，检测sql语句。%s  by updateforpush');
define('STATUS_18005','err：18005，连接数据库错误，检测sql语句。%s  by updateforpush');
define('STATUS_20000','err：20000，所有消息都发送完毕。');
define('STATUS_20001','err：20001，id为%s的消息不存在。');
define('STATUS_20002','err：20002，id为%s的消息已经发送完毕，请不要重复发送。');
define('STATUS_20003','err：20003，id为%s的消息已经发送完毕，但是没标记状态为0，现在标记。');

?>