<?php
namespace Admin\Model;
/**
* 举报
*
* @author 系统生成
*
*/
class ReportGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *举报补充内容
    */
    public $content;

    /**
    *用户ID
    */
    public $userId;

    /**
    *微博ID|评论ID
    */
    public $fromId;

    /**
    *1 微博  2 评论
    */
    public $type;

    /**
    *举报分类ID
    */
    public $categoryId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","content","userId","fromId","type","categoryId","delete","timestamp"];

    public $table = DB_PREFIX . 'report';

    public function reportSubmit()
    {
        switch ($this->type)
        {
            case 1:
                $model = new MicroblogGateway($this->adapter);
                break;
            case 2:
                $model = new CommentGateway($this->adapter);
                break;
        }
        $info = $model->getOne(['id'=>$this->fromId],['id']);
        if(!$info)return ['s'=>STATUS_NODATA,'d'=>'对象数据不存在'];
        $category_table = new CategoryGateway($this->adapter);
        $category = $category_table->getOne(['id'=>$this->categoryId,'type'=>4],['id']);
        if(!$category)return ['s'=>STATUS_NODATA,'d'=>'举报分类不存在'];
        $res = $this->addData();
        return $res?['s'=>STATUS_SUCCESS,'d'=>'操作成功']:['s'=>STATUS_UNKNOWN,'d'=>'提交失败'];
    }

}