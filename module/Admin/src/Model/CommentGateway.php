<?php
namespace Admin\Model;
use Zend\Db\Sql\Where;

/**
* 评论
*
* @author 系统生成
*
*/
class CommentGateway extends BaseGateway {
    /**
    *主键、自动增长
    */
    public $id;

    /**
    *音频/微博id
    */
    public $fromId;

    /**
    *用户的id
    */
    public $userId;

    /**
    *评论内容
    */
    public $content;

    /**
    *点赞总数
    */
    public $praiseNum;

    /**
    *回复总数
    */
    public $commentNum;

    /**
    *显示状态：1显示，2隐藏
    */
    public $display;

    /**
    *上级
    */
    public $parentId;

    /**
    *评论所属1 音频 2 视频 3微博
    */
    public $type;

    /**
    *字段数组
    */
    protected $columns_array = ["id","fromId","userId","content","praiseNum","commentNum","display","parentId","type","delete","timestamp"];

    public $table = DB_PREFIX . 'comment';

    public function delComment()
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
        $info = $this->getDetails();
        if(!$info)
        {
            return ['s'=>10000,'d'=>'数据不存在'];
        }
        $this->deleteData();
        if($info->from_id && $info->display == 1)
        {
            switch ($info->type)
            {
                case 1:
                case 2:
                    $audio = new AudioGateway($this->adapter);
                    $details = $audio->getOne(['id'=>$info->from_id],['id','comment_num']);
                    if($details)
                    {
                        $audio->update(['comment_num'=>($details->comment_num-1)<0?0:($details->comment_num-1)],['id'=>$details->id]);
                    }
                    break;
                case 3:
                    $microblog = new MicroblogGateway($this->adapter);
                    $details = $microblog->getOne(['id'=>$info->from_id],['id','comment_num']);
                    if($details)
                    {
                        $microblog->update(['comment_num'=>($details->comment_num-1)<0?0:($details->comment_num-1)],['id'=>$details->id]);
                    }
                    break;
            }
        }
        $this->adapter->getDriver()->getConnection()->commit();
        return ['s'=>0,'d'=>'删除成功'];
    }

    public function changeDisplay()
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
        $info = $this->getDetails();
        if(!$info)
        {
            return ['s'=>10000,'d'=>'数据不存在'];
        }
        $this->updateData();
        if($info->from_id)
        {
            switch ($info->type)
            {
                case 1:
                case 2:
                    $audio = new AudioGateway($this->adapter);
                    $details = $audio->getOne(['id'=>$info->from_id],['id','comment_num']);
                    if($details)
                    {
                        $update = $this->display == 1?['comment_num'=>$details->comment_num+1]:['comment_num'=>($details->comment_num-1)<0?0:($details->comment_num-1)];
                        $audio->update($update,['id'=>$details->id]);
                    }
                    break;
                case 3:
                    $microblog = new MicroblogGateway($this->adapter);
                    $details = $microblog->getOne(['id'=>$info->from_id],['id','comment_num']);
                    if($details)
                    {
                        $update = $this->display == 1?['comment_num'=>$details->comment_num+1]:['comment_num'=>($details->comment_num-1)<0?0:($details->comment_num-1)];
                        $microblog->update($update,['id'=>$details->id]);
                    }
                    break;
            }
        }
        $this->adapter->getDriver()->getConnection()->commit();
        return ['s'=>0,'d'=>'操作成功'];
    }

    public function deleteByIds($ids)
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
        foreach ($ids as $id) {
            $this->id = $id;
            $info = $this->getOne(['delete'=>DELETE_FALSE,'id'=>$id,'user_id'=>$this->userId]);
            if(!$info)
            {
                return ['s'=>10000,'d'=>'数据不存在'];
            }
            $this->deleteData();
            if($info->from_id && $info->display == 1)
            {
                switch ($info->type)
                {
                    case 1:
                    case 2:
                        $audio = new AudioGateway($this->adapter);
                        $details = $audio->getOne(['id'=>$info->from_id],['id','comment_num']);
                        if($details)
                        {
                            $audio->update(['comment_num'=>($details->comment_num-1)<0?0:($details->comment_num-1)],['id'=>$details->id]);
                        }
                        break;
                    case 3:
                        $microblog = new MicroblogGateway($this->adapter);
                        $details = $microblog->getOne(['id'=>$info->from_id],['id','comment_num']);
                        if($details)
                        {
                            $microblog->update(['comment_num'=>($details->comment_num-1)<0?0:($details->comment_num-1)],['id'=>$details->id]);
                        }
                        break;
                }
            }
        }
        $this->adapter->getDriver()->getConnection()->commit();
        return ['s'=>0,'d'=>'删除成功'];
    }

    /**
     * 1音频评论，2视频评论，3微博评论
     * 评论提交
     */
    public function CommentSubmit()
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
        switch ($this->type)//1音频评论，2视频评论，3微博评论
        {
            case 1:
            case 2:
                $model = new AudioGateway($this->adapter);
                $detail = $model->getOne(['id'=>$this->fromId,'type'=>$this->type==1?2:1],['id','comment_num']);
                break;
            case 3:
                $model = new MicroblogGateway($this->adapter);
                $detail = $model->getOne(['id'=>$this->fromId],['id','comment_num']);
                break;
            default:
                return ['s'=>STATUS_PARAMETERS_CONDITIONAL_ERROR];
                break;
        }
        if(!$detail)
        {
            return ['s'=>STATUS_NODATA];
        }

        $id = $this->addData();
        $update = ['comment_num'=>$detail->comment_num+1];
        $model->update($update,['id'=>$detail->id]);
        $this->adapter->getDriver()->getConnection()->commit();
        return ['s'=>STATUS_SUCCESS];
    }
}