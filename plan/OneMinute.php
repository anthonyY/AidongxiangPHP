<?php
include_once __DIR__.'/config.php';

$params = array();
$method = 'get';
$extra_conf = array(
    CURLOPT_USERAGENT => 'ForPlan'
);
$result = array();
// 轮播图结束时间,改变状态
$result[] = urlExec(PLAN_URL . 'adsStatus', $params, $method, $extra_conf);
$result[] = urlExec(PLAN_URL . 'aduioStatus', $params, $method, $extra_conf);
$result[] = urlExec(PLAN_URL . 'notificationStatus', $params, $method, $extra_conf);
if (false) {
    highlight_string(implode("\n", $result));
}

$filename = APP_PATH . '/Cache/plan_count.txt';
if(! is_file($filename)) {
    touch($filename);
}
$data = file_get_contents($filename);
if (! $data) {
    $data = array('FiveMinute' => array('last_time' => '', 'count' => 0));
}
else {
    $data = json_decode($data, true);
}
isset($data['FiveMinute']) || $data['FiveMinute'] = array('count' => 0);
$data['FiveMinute']['count'] ++;
$data['FiveMinute']['last_time'] = date('Y-m-d H:i:s');
file_put_contents($filename, json_encode($data));