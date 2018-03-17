<?php
namespace Api\Controller;

use Api\Controller\Request\AdListRequest;
use Zend\Db\Sql\Where;

/**
 * 广告协议
 *
 * @author WZ
 * @version 1.0.140515 这版本不需要，屏蔽
 */
class AdList extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new AdListRequest();
        parent::__construct();
    }

    /**
     * @return Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $position_id = $request->positionId ? (int)$request->positionId : 1;
        if(!in_array($position_id,[1,2,3]))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $this->tableObj = $this->getViewAdsTable();
        $this->initModel();
        $this->tableObj->position = $position_id;
        $data = $this->tableObj->getApiList();
        $list = array();
        foreach($data['list'] as $v)
        {
            $item = array(
                'id' => $v->id,
                'name' => $v->name,
                'type' => $v->type,
                'objectId' => $v->audio_id,
                'link' => $v->type == 4?$v->scontent:'',
                'startTime' => $v->start_time,
                'endTime'=> $v->end_time,
                'imagePath' => $v->path.$v->filename,
            );

            $list[] = $item;
        }
        $response->total =  $data['total'] . '';
        $response->ads = $list;
        return $response;
    }

    /**
     * 排序字段
     * @param int $order_by
     * @return string
     */
    public function OrderBy($order_by = 1)
    {
        $result = "sort";
        return $result;
    }
}