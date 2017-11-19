<?php
/**
 * 环境配置
 *1测试 2生产
 * @var boolen
 */
define("IS_DEBUG", 1);

//目录路径配置
define("SYS_PATH", dirname(__DIR__));			//根目录
define("APP_PATH", __DIR__);					//系统目录//相对路径
@define('ROOT_PATH','/');//相对路径
@define('IMAGE_PATH','http://'.$_SERVER['SERVER_NAME'].'/uploadfiles/');//相对路径

define("UPLOAD_PATH","public/uploadfiles/");   //上传文件的路径
define("PLUGIN_URL","/public/plugins/");   //上传文件的路径
define('MODULE_API', 'api.ktx.com' == $_SERVER['SERVER_NAME'] ? '' : '/api');
define('MODULE_ADMIN', 'admin.ktx.com' == $_SERVER['SERVER_NAME'] ? '' : '/admin');
define('MODULE_WAP', 'wap.ktx.com' == $_SERVER['SERVER_NAME'] ? '' : '/wap');
define('MODULE_SELLER', 'seller.ktx.com' == $_SERVER['SERVER_NAME'] ? '' : '/seller');
define('MODULE_MAIL', 'mail.ktx.com' == $_SERVER['SERVER_NAME'] ? '' : '/mail');
define('MODULE_ADMINWAP', 'adminwap.ktx.com' == $_SERVER['SERVER_NAME'] ? '' : '/adminwap');
define('MODULE_WWW', 'www.ktx.com' == $_SERVER['SERVER_NAME'] ? '' : '/www');
define('SITE_URL', 'http://'.$_SERVER['SERVER_NAME']);

define("DB_HOST", '127.0.0.1');     //数据链接IP
define("DB_NAME", 'aidongxiang');     //数据库名
define("DB_USER", 'root');     //数据库用户名
define("DB_PASSWORD", '');//192.168.1.14数据库密码
define("DB_PREFIX", 'dx_');     //数据表前缀
define("DB_CHARSET", 'utf8mb4'); // 数据库编码utf8mb4
define("DB_SET_NAME", 'UTF8MB4'); // 数据库编码UTF8MB4

/**
 * 腾讯云对象存储配置
 */
define("COS_APP_ID", '1255490419');     //数据链接IP
define("COS_SECRET_ID", 'AKIDqaBfy5lx7i7KKApFhLq9KI7jDVcjmgU7');
define("COS_SECRET_KEY", '6RcaMvMtIfjeZR9e3PxMYYL4g6A2mOsA');
define("COS_REGION", 'gz'); // bucket所属地域：华北 'tj' 华东 'sh' 华南 'gz'
define("COS_TIMEOUT", 60);

//周边设置
define('EARTH_RADIUS', '6378.137'); // 地球半径
define('DISTANCE_1','10'); // 周边距离1
define('DISTANCE_2','30'); // 周边距离2
define('DISTANCE_3','40'); // 周边距离3

define("SELF_MERCHANT", 1); // 自营商家ID

//分页设置
define('PAGE_NUMBER',15);//每页显示

/** 高德地图key @var string */
define('AMAP_KEY', '39a53becaeb73f0bd3f59fbe2889f86e');

define('PC_URL','http://127.0.0.1');//PC首页
define('DOWN_URL','http://127.0.0.1/download/KingCat.apk');//下载APP页面
define('API_URL','http://127.0.0.1/web/maowangV4.1/public/api/index');//接口路径
define('WAP_URL','http://127.0.0.1/maowangV4.1/public/wap/');//手机版网站路径
define('WEIXIN_URL','http://weixin.mwvip.com');//网站微信模块路径
define("API_VERSION", '4.1');//协议版本号，用于wap端请求协议用
define('COOKIE_TIME',30);//cookie时长
define('PLAN_URL', API_URL);

//加密盐值
define('SECURITY_SALT', 'hdjagkghkashj');

//IM通讯配置
define('IM_SKDAPPID',1400037277);
define('IM_ROOT','system'); //账号管理员
define('IM_ACCOUNTTYPE',14483); //accountType

/*
*1本地保存，2服务器保存，3=1+2
*/
define('IMAGE_SAVE_MODE', 1);
/**
 * 服务器保存地址
 *
 * @todo
 * 上线后要调整
 */
//define('IMAGE_SERVER','http://192.168.1.29/image/project/ninihui/');
define('IMAGE_SERVER', '');
/**
 * 本地保存图片的目录
 * @var unknown
 */
define('LOCAL_SAVEPATH', APP_PATH . '/public/uploadfiles/');

//推送配置
// 手机端setting需要的参数
/**
 * 协议是否开启md5验证
 * @var boolean
 */
define('CHECK_API_MD5' , false);

/**
 * 协议是否开启md5验证
 * @var boolean
 */
define('API_KEY' , "ktxMallKey98765#$%");

/**
 * 推送的开关
 * true：可以触发推送
 * false：不可以推送，都反馈推送成功。
 * @var true|false
 */
define('PUSH_SWITCH' , false);
/**
 * 推送与短信的记录日志开关。
 * @var true|false
 */
define('PUSH_LOG_SWITCH' , true);
/**
 * 短信的开关
 * true：可以发送短信
 * false：不发送短信，都反馈发送成功
 * @var true|false
 */
define('SMSCODE_SWITCH' , false);
/**
 * true：验证短信，false：短信验证（无论输什么）都会通过。
 * @var true|false
 */
define('CHECK_SMSCODE' , true);
/**
 * 短信有效时间，单位：秒
 * @var Number
 */
define('SMSCODE_EXPIRE' , 60);
/**
 * 是否启用快速短信验证码
 *
 * @var boolen
 */
define('QUICK_SMSCODE_SWITCH', false);

/**
 * 客天下API API加密KEY
 */
define("KTX_API_KEY", "ktxMallKey98765#$%");

/**
 * 客天下API 请求地址
 */
define("KTX_API_URL", "http://120.76.84.158:8080/ktx-inter/inter/mallapi");

include_once 'status_config.php';
include_once 'message_template.php';
include_once 'pay_config.php';
include_once 'oauth_config.php';