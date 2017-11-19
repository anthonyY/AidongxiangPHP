<?php
/**
 * 核心公用配置
 * @author  Karson
 * @name    system/core.php
 * @since   2013-1-24 21:28:00
 */
ini_set('date.timezone', 'Asia/Shanghai');
if (!function_exists('curl_init')) {
    echo '您的服务器不支持 PHP 的 Curl 模块，请安装或与服务器管理员联系。';
    exit;
}
//定义系统核心目录
define('COREPATH', dirname(__FILE__) . '/');
//定义网站根目录
define('ROOTPATH', dirname(COREPATH) . '/');
include_once COREPATH . "../../../config.php";
include_once COREPATH . "function.php";
include_once COREPATH . "qq.php";
include_once COREPATH . "weixin.php";
set_exception_handler("exception_error");
