<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* VIEW
*
* @author 系统生成
*
*/
class ViewFavoriteGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *用户id
    */
    public $userId;

    /**
    *音频id
    */
    public $audioId;

    /**
    *1 视频 2 音频
    */
    public $type;

    /**
    *类型1 视频 2 音频
    */
    public $audioType;

    /**
    *音频/视频名称
    */
    public $audioName;

    /**
    *原文件名称
    */
    public $audioFilename;

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
    public $audioImageId;

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
    *音频大小
    */
    public $size;

    /**
    *状态：1 正常 2 已下架
    */
    public $audioStatus;

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
    *
    */
    public $audioDelete;

    /**
    *
    */
    public $audioTimestamp;

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
    *昵称
    */
    public $nickName;

    /**
    *真实姓名
    */
    public $realName;

    /**
    *移动电话（登录账号）
    */
    public $mobile;

    /**
    *用户头像ID
    */
    public $headImageId;

    /**
    *字段数组
    */
    protected $columns_array = ["id","userId","audioId","type","delete","timestamp","audioType","audioName","audioFilename","payType","price","memberPrice","description","audioImageId","auditionsPath","fullPath","audioLength","auditionsLength","size","audioStatus","commentNum","playNum","praiseNum","categoryId","audioDelete","audioTimestamp","imageFilename","imagePath","categoryName","nickName","realName","mobile","headImageId"];

    public $table = 'view_favorite';

    public function getApiList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('user_id',$this->userId);
        if($this->type)$where->equalTo('type',$this->type);
        if($this->audioType)$where->equalTo('audio_type',$this->audioType);
        return $this->getAll();
    }

}