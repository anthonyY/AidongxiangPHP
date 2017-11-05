<?php
include_once './AiiImage.php';

$project = substr(__DIR__, strlen(dirname(__DIR__)) + 1);

$image = new AiiImage($project);

$image->imageDecode();