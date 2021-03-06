<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* 分类表
*
* @author 系统生成
*
*/
class CategoryGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *分类类型：1视频分类，2音频分类，3资讯分类 4举报分类
    */
    public $type;

    /**
    *分类名
    */
    public $name;

    /**
    *分类对应的icon_id(图片表ID)
    */
    public $icon;

    /**
    *排序（升序，1排在2前面）
    */
    public $sort;

    /**
    *审核状态：1正常，2禁用
    */
    public $status;

    /**
    *父ID
    */
    public $parentId;

    /**
     * 是否需要分页 1是 2否
     */
    public $needPage = 1;

    /**
    *字段数组
    */
    protected $columns_array = ["id","type","name","icon","sort","status","parentId","delete","timestamp"];

    public $table = DB_PREFIX . 'category';

    public function updateData()
    {
        return parent::updateData(); // TODO: Change the autogenerated stub
    }

    public function deleteData()
    {
        return parent::deleteData(); // TODO: Change the autogenerated stub
    }

    public function addData()
    {
        return parent::addData(); // TODO: Change the autogenerated stub
    }

    public function getList()
    {
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->equalTo('type',$this->type);
        if($this->status)
        {
            $where->equalTo('status',$this->status);
        }
        if($this->needPage != 2)
        {
            return $this->getAll($where,['name']);
        }
        else
        {
            return $this->fetchAll($where,['*'],['name']);
        }

    }

    /**
     * @param $sort_array
     * @return array
     * @throws \Exception
     * 保存分类排序
     */
    public function saveSort($sort_array)
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
        if(!is_array($sort_array) || !$sort_array)
        {
            return ['s'=>10000,'d'=>'操作失敗'];
        }
        foreach ($sort_array as $id=>$sort) {
            $this->id = $id;
            $this->sort = $sort;
            $res = $this->updateData();
            if($res === false)
            {
                $this->adapter->getDriver()->getConnection()->rollback();
                return ['s'=>10000,'d'=>'操作失敗'];
            }
        }
        $this->adapter->getDriver()->getConnection()->commit();
        return ['s'=>0,'d'=>'操作成功'];
    }

    //更新视频分类状态
    public function updateVideoCategoryStatus()
    {
        if(!$this->id || !in_array($this->status,[1,2]))
        {
            return ['s'=>10000,'d'=>'参数错误'];
        }

        if($this->status == 2)
        {

            $audio = new AudioGateway($this->adapter);
            $video_exist = $audio->getOne(['category_id'=>$this->id]);
            if($video_exist)
            {
                return ['s'=>10000,'d'=>'该分类下还有视频，不能下架'];
            }
        }
        if($this->updateData())
        {
            return ['s'=>0,'d'=>'操作成功'];
        }
        else
        {
            return ['s'=>0,'d'=>'操作失败'];
        }
    }

    //更新音频分类状态
    public function updateAudioCategoryStatus()
    {
        if(!$this->id || !in_array($this->status,[1,2]))
        {
            return ['s'=>10000,'d'=>'参数错误'];
        }

        if($this->status == 2)
        {

            $audio = new AudioGateway($this->adapter);
            $video_exist = $audio->getOne(['category_id'=>$this->id]);
            if($video_exist)
            {
                return ['s'=>10000,'d'=>'该分类下还有音频，不能下架'];
            }
        }
        if($this->updateData())
        {
            return ['s'=>0,'d'=>'操作成功'];
        }
        else
        {
            return ['s'=>0,'d'=>'操作失败'];
        }
    }

    //更新资讯分类状态
    public function updateArticleCategoryStatus()
    {
        if(!$this->id || !in_array($this->status,[1,2]))
        {
            return ['s'=>10000,'d'=>'参数错误'];
        }

        if($this->status == 2)
        {

            $article = new ArticleGateway($this->adapter);
            $article_exist = $article->getOne(['category_id'=>$this->id]);
            if($article_exist)
            {
                return ['s'=>10000,'d'=>'该分类下还有资讯，不能下架'];
            }
        }
        if($this->updateData())
        {
            return ['s'=>0,'d'=>'操作成功'];
        }
        else
        {
            return ['s'=>0,'d'=>'操作失败'];
        }
    }

    //删除视频分类
    public function videoCategoryDel()
    {
        if(!$this->id)
        {
            return ['s'=>10000,'d'=>'参数错误'];
        }
        $audio = new AudioGateway($this->adapter);
        $video_exist = $audio->getOne(['delete'=>DELETE_FALSE,'category_id'=>$this->id]);
        if($video_exist)
        {
            return ['s'=>10000,'d'=>'该分类下还有视频，不能删除'];
        }
        $this->delete = DELETE_TRUE;
        if($this->updateData())
        {
            return ['s'=>0,'d'=>'操作成功'];
        }
        else
        {
            return ['s'=>0,'d'=>'操作失败'];
        }
    }

    //删除音频分类
    public function audioCategoryDel()
    {
        if(!$this->id)
        {
            return ['s'=>10000,'d'=>'参数错误'];
        }
        $audio = new AudioGateway($this->adapter);
        $video_exist = $audio->getOne(['delete'=>DELETE_FALSE,'category_id'=>$this->id]);
        if($video_exist)
        {
            return ['s'=>10000,'d'=>'该分类下还有音频，不能删除'];
        }
        $this->delete = DELETE_TRUE;
        if($this->updateData())
        {
            return ['s'=>0,'d'=>'操作成功'];
        }
        else
        {
            return ['s'=>0,'d'=>'操作失败'];
        }
    }

    //删除资讯分类
    public function articleCategoryDel()
    {
        if(!$this->id)
        {
            return ['s'=>10000,'d'=>'参数错误'];
        }
        $article = new ArticleGateway($this->adapter);
        $article_exist = $article->getOne(['delete'=>DELETE_FALSE,'category_id'=>$this->id]);
        if($article_exist)
        {
            return ['s'=>10000,'d'=>'该分类下还有资讯，不能删除'];
        }
        $this->delete = DELETE_TRUE;
        if($this->updateData())
        {
            return ['s'=>0,'d'=>'操作成功'];
        }
        else
        {
            return ['s'=>0,'d'=>'操作失败'];
        }
    }

}