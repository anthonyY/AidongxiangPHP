<?php
include_once __DIR__.'/config.php';

$params = array();
$method = 'get';
$extra_conf = array(
    CURLOPT_USERAGENT => 'ForPlan'
);
$result = array();
// 会员提醒
$result[] = urlExec(PLAN_URL . 'memberStatus', $params, $method, $extra_conf);

if (false) {
    highlight_string(implode("\n", $result));
}

$filename = APP_PATH . '/Cache/plan_count.txt';
if(! is_file($filename)) {
    touch($filename);
}
$data = file_get_contents($filename);
if (! $data) {
    $data = array('Month' => array('last_time' => '', 'count' => 0));
}
else {
    $data = json_decode($data, true);
}
isset($data['Month']) || $data['Month'] = array('count' => 0);
$data['Month']['count'] ++;
$data['Month']['last_time'] = date('Y-m-d H:i:s');
file_put_contents($filename, json_encode($data));