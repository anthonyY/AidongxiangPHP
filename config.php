<?php
//目录路径配置
define("SYS_PATH", dirname(__DIR__));			//根目录
define("APP_PATH", __DIR__);					//系统目录
define('ROOT_PATH', isset($_SERVER['REDIRECT_BASE']) ? $_SERVER['REDIRECT_BASE'] : '/');//相对路径
define("PBL_NAME", '/'.'企业内部培训系统');                //项目名称
define("UPLOAD_PATH","uploadfiles/");   //上传文件的路径
define("THUMB_IMAGE_PATH",APP_PATH."/public/uploadfiles/thumb"); // 物理缩略图路径
define("SERVER_NAME",isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : ''); // 域名

/**
 * module配置
 */

define('MODULE_ADMIN', '' == SERVER_NAME ? '' : '/admin');
define('MODULE_API', '' == SERVER_NAME ? '' : '/api');
define('MODULE_WEB', '' == SERVER_NAME ? '' : '/web');


define('PAGE_NUMBER', 10);//每页默认条数

// //数据库配置
define("DB_HOST",'192.168.1.14');     //数据链接IP    192.168.1.14
define("DB_NAME",'night_bird');     //数据库名
define("DB_USER",'root');     //数据库用户名
define("DB_PASSWORD",'aiitecphp2009');     //数据密码  aiitecphp2009
define("DB_PREFIX",'nb_');     //数据表前缀
define("DB_CHARSET", 'utf8mb4'); // 数据库编码utf8mb4
define("DB_SET_NAME", 'UTF8MB4'); // 数据库编码UTF8MB4
// define('API_URL','http://192.168.1.14/nightbird/public/api/index');//接口路径
define('API_URL','http://192.168.1.14/nightbird/public/api/index');//接口路径

/**
 * 1本地保存，2服务器保存，3=1+2
 */
define('IMAGE_SAVE_MODE', 1);
/**
 * 服务器保存地址
 * @todo 上线后要调整
 */
define('IMAGE_SERVER', '');
/**
 * 本地保存图片的目录
 * @var APP_PATH . '/public/uploadfiles/'
 */

define('LOCAL_SAVEPATH', APP_PATH . '/public/uploadfiles/');

define("SHOP_PATH", preg_replace('/\/[\w]+\/[\w]+\/$/', '', ROOT_PATH)); // 网页访问商城图片的路径
/**
 * 推送的开关
 * true：可以触发推送
 * false：不可以推送，都反馈推送成功。
 * @var true|false
 */
define('PUSH_SWITCH' , true);
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
define('CHECK_SMSCODE' , false);
/**
 * 短信有效时间，单位：秒
 * @var Number
 */
define('SMSCODE_EXPIRE' , 600);
/**
 * 是否启用快速短信验证码
 *
 * @var boolen
 */
define('QUICK_SMSCODE_SWITCH', true);
/**
 * 单点登录设备类型
 */
define('SINGLE_SIGN_ON_TYPES' , '1,2,4'); // 1,2,4 字符串,逗号分割 1 iOS;2 Android;4 windows phone;8 微信;16 手机网站;32 临时;

/**
 * 电话的正则表达式
 */
define('PHONE_REGULAR', '/^((\d{1,5})?(1\d{10}))$/');
/**
 * 邮箱的正则表达式
 */
define('EMAIL_REGULAR', '/^[a-zA-Z0-9-._]{1,50}@[a-zA-Z0-9-]{1,65}.(com|net|org|info|biz|([a-z]{2,5}.[a-z]{2}))$/i');
/**
 * 是否开启https 1 开启  2 不开启
 * @var Number
 */
define('IS_OPEN_HTTPS' , 1);



include_once 'status_config.php';
include_once 'message_template.php';
include_once 'project_config.php';
