<?php
namespace Admin\Model;
/**
* 图片
*
* @author 系统生成
*
*/
class ImageGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *
    */
    public $md5;

    /**
    *文件名，时分秒_四位随机数（如：131452_1234.png）
    */
    public $filename;

    /**
    *目录，由日期组成（如：201305/20/）
    */
    public $path;

    /**
    *
    */
    public $width;

    /**
    *
    */
    public $height;

    /**
    *
    */
    public $count;

    /**
    *字段数组
    */
    protected $columns_array = ["id","md5","filename","path","width","height","count","delete","timestamp"];

    public $table = DB_PREFIX . 'image';

}