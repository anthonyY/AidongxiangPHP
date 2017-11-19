<?php
namespace Admin\Model;
/**
* 标签表
*
* @author 系统生成
*
*/
class LabelGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *标签类型：1资讯标签
    */
    public $type;

    /**
    *标签名
    */
    public $name;

    /**
    *排序（升序，1排在2前面）
    */
    public $sort;

    /**
    *显示状态：1显示，2隐藏
    */
    public $display;

    /**
    *字段数组
    */
    protected $columns_array = ["id","type","name","sort","display","delete","timestamp"];

    public $table = DB_PREFIX . 'label';

}