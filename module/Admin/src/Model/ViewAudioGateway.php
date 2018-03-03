<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewAudioGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *类型1 视频 2 音频
    */
    public $type;

    /**
    *音频/视频名称
    */
    public $name;

    /**
    *1免费，2收费
    */
    public $payType;

    /**
    *普通成员价格
    */
    public $price;

    /**
    *专享会员价格
    */
    public $memberPrice;

    /**
    *音频课程介绍
    */
    public $description;

    /**
    *音频封面
    */
    public $imageId;

    /**
    *试听音频目录+文件名
    */
    public $auditionsPath;

    /**
    *完整音频目录+文件名
    */
    public $fullPath;

    /**
    *音频课程长度
    */
    public $audioLength;

    /**
    *音频试听长度
    */
    public $auditionsLength;

    /**
    *状态：1 正常 2 已下架
    */
    public $status;

    /**
    *评论数
    */
    public $commentNum;

    /**
    *播放量
    */
    public $playNum;

    /**
    *点赞数
    */
    public $praiseNum;

    /**
    *分类id
    */
    public $categoryId;

    /**
    *文件名，时分秒毫秒+用户id（如：13145200010000.png）
    */
    public $imageFilename;

    /**
    *目录，由日期组成（如：20130520/）
    */
    public $imagePath;

    /**
    *分类名
    */
    public $categoryName;

    /**
    *分类类型：1视频分类，2音频分类，3资讯分类 4举报分类
    */
    public $categoryType;

    /**
    *分类对应的icon_id(图片表ID)
    */
    public $categoryIcon;

    /**
    *审核状态：1正常，2禁用
    */
    public $categoryStatus;

    /**
    *父id
    */
    public $categoryParentId;

    /**
    *排序（倒序，1排在2后面）
    */
    public $categorySort;

    /**
    *字段数组
    */
    protected $columns_array = ["id","type","name","payType","price","memberPrice","description","imageId","auditionsPath","fullPath","audioLength","auditionsLength","status","commentNum","playNum","praiseNum","categoryId","delete","timestamp","imageFilename","imagePath","categoryName","categoryType","categoryIcon","categoryStatus","categoryParentId","categorySort"];

    public $table = 'view_audio';

    public function getList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('type',$this->type);
        if($this->status)
        {
            $where->equalTo('status',$this->status);
        }
        if($this->categoryId)
        {
            $where->equalTo('category_id',$this->categoryId);
        }
        return $this->getAll($where,['name']);
    }
}