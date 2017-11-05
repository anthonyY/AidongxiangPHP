<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
  //ini_set('display_errors','on');
  //error_reporting(E_ALL);// 打开错误提示

chdir(dirname(__DIR__));
// Setup autoloading
require 'init_autoloader.php';
require 'config.php';
// error_reporting(0);
/* require 'UploadHandler.php';
$uploadhandler = new UploadHandler(); */
// ini_set('session.save_path', dirname(__DIR__).'/Cache/Session');
session_start();

header('Content-Type:text/html; charset=utf-8');
date_default_timezone_set('PRC');  
// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();

