<?php
$url = 'http://api.ktxgo.com';
$task_one = $url.'/index/autoCompletePlan';// 平台收入天统计
echo file_get_contents($task_one);