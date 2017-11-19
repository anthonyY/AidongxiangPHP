<?php
use AiiUtility\AiiWebSocket\AiiWebSocket;

include_once '../autoload.php';
ini_set('date.timezone', 'Asia/Shanghai');
set_time_limit(0);
header("Content-type:text/html;charset=utf-8");

$ws = new AiiWebSocket();
$ws->connect('192.168.1.20', '9501');

// 单词发送请求测试
echo $ws->send('{"action":"sendToAll","data":{"content":"中文'.mt_rand(1000, 9999).'","userId":"1"}}');
// 测试结果：基本没问题

// 多次发送请求测试
// for($i = 0;$i < 100; $i++) {
//     echo $ws->send('{"action":"sendToAll","data":{"content":"中文'.$i.'","userId":"3"}}');
// }
// 测试结果：基本都不能完全发送100次，请求会在某一次之后中断，前面的都是连续的
// 测试结果2：send加入read之后，请求100%成功
// 测试结果3：假如服务器没有返回，使用read会导致阻塞
die;