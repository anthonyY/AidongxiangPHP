<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewArticleGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *标题
    */
    public $title;

    /**
    *内容
    */
    public $content;

    /**
    *内容概述
    */
    public $abstract;

    /**
    *阅读量
    */
    public $readNum;

    /**
    *资讯封面图片id
    */
    public $imageId;

    /**
    *操作管理员id
    */
    public $adminId;

    /**
    *标签id
    */
    public $labelId;

    /**
    *分类id
    */
    public $categoryId;

    /**
    *文件名，时分秒毫秒+用户id（如：13145200010000.png）
    */
    public $filename;

    /**
    *目录，由日期组成（如：20130520/）
    */
    public $path;

    /**
    *分类名
    */
    public $categoryName;

    /**
    *标签名称
    */
    public $labelName;

    /**
    *显示状态：1显示，2隐藏
    */
    public $labelDisplay;

    /**
    *标签类型：1资讯标签
    */
    public $labelType;

    /**
    *分类类型：1视频分类，2音频分类，3资讯分类 4举报分类
    */
    public $categoryType;

    /**
    *分类对应的icon_id(图片表ID)
    */
    public $categoryIcon;

    /**
    *用户名，>= 6个字符
    */
    public $adminName;

    /**
    *字段数组
    */
    protected $columns_array = ["id","title","content","abstract","readNum","imageId","adminId","labelId","categoryId","delete","timestamp","filename","path","categoryName","labelName","labelDisplay","labelType","categoryType","categoryIcon","adminName"];

    public $table = 'view_article';

    public function getList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE);
        if($this->categoryId)
        {
            $where->equalTo('category_id',$this->categoryId);
        }
        return $this->getAll($where,['title']);
    }

    public function getApiList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE);
        if($this->categoryId)
        {
            $where->equalTo('category_id',$this->categoryId);
        }
        return $this->getAll($where,['title']);
    }

}