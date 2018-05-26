<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewReportGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
     * 举报补充内容
     */
    public $content;

    /**
     * 外键id，根据type
     */
    public $fromId;

    /**
     *1微博，2评论
     */
    public $type;

    /**
    *用户id
    */
    public $userId;

    /**
    *分类id
    */
    public $categoryId;

    /**
     *昵称
     */
    public $nickName;

    /**
     *移动电话（登录账号）
     */
    public $mobile;

    /**
    *分类名
    */
    public $categoryName;

    /**
    *字段数组
    */
    protected $columns_array = ["id","content","fromId","type","userId","categoryId","delete","timestamp","categoryName","nickName","mobile"];

    public $table = 'view_report';

    public function getList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE);
        if($this->type)$where->equalTo('type',$this->type);
        if($this->categoryId)$where->equalTo('category_id',$this->categoryId);
        return $this->getAll($where);
    }

}