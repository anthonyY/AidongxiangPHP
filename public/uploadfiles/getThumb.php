<?php
include_once './AiiImage.php';

$project = substr(__DIR__, strlen(dirname(__DIR__)) + 1);

$image = new AiiImage($project);
$water = (object) array();
$water->type = 1;
$water->water_txt = '111';
$water->water_img = __DIR__ . "/../images/common_img.png";
$water->color = '#FF0000';
$water->font = 14;
$water->pos = 9;
$water->alpha = 30;
$water->quality = 85;
$image->setWaterConfig($water)->getThumb();
// $image->getThumb();
