<?php
namespace Api\Controller;

use Api\Controller\Request\AudioWhereRequest;
use Zend\Db\Sql\Where;

/**
 * 视频/音频列表
 * @author lzw
 */
class AudioList extends CommonController
{
    public function __construct()
    {
        $this->myWhereRequest = new AudioWhereRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action;
        $table_where = $this->getTableWhere();
        $audio_type = $table_where->audioType;//1视频 2音频
        if(!in_array($action,[1,2,3]) || !in_array($audio_type,[1,2])){//a:1全部；2视频收藏 3观看记录
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if(in_array($action,[2,3]))
        {
            $this->checkLogin();
        }

        $category_id = $table_where->categoryId;
        $keyword = $table_where->search_key;

        $list = array();
        $total = 0;

        if($action == 1)
        {
            $this->tableObj = $this->getViewAudioTable();
            $this->initModel();
            $this->tableObj->type = $audio_type;
            $this->tableObj->categoryId = $category_id;
            $this->tableObj->searchKeyWord = $keyword;
            $data = $this->tableObj->getApiList();
            if($data['list'])
            {
                $buyLogTable = $this->getBuyLogTable();
                $buyLogTable->userId = $this->getUserId();
                $buyLogTable->status = 2;
                foreach ($data['list'] as $val) {
                    $hadBuy = false;
                    if($buyLogTable->userId > 0)
                    {
                        $buyLogTable->audioId = $val->id;
                        $res = $buyLogTable->checkUserBuy();
                        if($res)$hadBuy = true;
                    }
                    $item = array(
                        'id' => $val->id,
                        'audioId' => $val->id,
                        'name' => $val->name,
                        'imagePath' => $val->image_filename ? $val->image_path . $val->image_filename : '',
                        'path' => $val->pay_type==2?($hadBuy?$val->full_path:$val->auditions_path):$val->full_path,
                        'playNum' => $val->play_num,
                        'audioLength' => $val->pay_type==2?$val->auditions_length:$val->audio_length,
                        'timestamp' => $val->timestamp,
                    );
                    $list[] = $item;
                }
                $total = $data['total'];
            }
        }
        elseif($action == 2)//2视频收藏
        {
            $this->tableObj = $this->getViewFavoriteTable();
            $this->initModel();
            $this->tableObj->userId = $this->getUserId();
            $this->tableObj->type = $audio_type;
            $this->tableObj->audioType = $audio_type;
            $data = $this->tableObj->getApiList();
            if($data['list'])
            {
                $buyLogTable = $this->getBuyLogTable();
                $buyLogTable->userId = $this->getUserId();
                $buyLogTable->status = 2;
                foreach ($data['list'] as $val) {
                    $hadBuy = false;
                    if($buyLogTable->userId > 0)
                    {
                        $buyLogTable->audioId = $val->audio_id;
                        $res = $buyLogTable->checkUserBuy();
                        if($res)$hadBuy = true;
                    }
                    $item = array(
                        'id' => $val->id,
                        'audioId' => $val->audio_id,
                        'name' => $val->audio_name,
                        'imagePath' => $val->image_filename ? $val->image_path . $val->image_filename : '',
                        'path' => $val->pay_type==2?($hadBuy?$val->full_path:$val->auditions_path):$val->full_path,
                        'playNum' => $val->play_num,
                        'audioLength' => $val->pay_type==2?$val->auditions_length:$val->audio_length,
                        'timestamp' => $val->timestamp,
                    );
                    $list[] = $item;
                }
                $total = $data['total'];
            }
        }
        elseif($action == 3)//3观看记录
        {

        }

        $response->status = STATUS_SUCCESS;
        $response->audios = $list;
        $response->total = $total;
        return $response;
    }

    /**
     * 排序字段
     * @param int $order_by
     * @return string
     */
    public function OrderBy($order_by = 1)
    {
        $result = "id";
        switch ($order_by)
        {
            case 1:
                $result = "id";
                break;
            case 2:
                $result = "play_num";
                break;
            case 3:
                $result = "comment_num";
                break;
            case 4:
                $result = "praise_num";
                break;
            default:
                $result = "id";
                break;
        }
        return $result;
    }
}
