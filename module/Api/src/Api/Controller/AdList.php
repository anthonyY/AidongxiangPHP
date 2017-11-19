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
     * 返回一个数组或者Result类
     *
     * @return \Api\Controller\BaseResult
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $position_id = $request->positionId ? (int)$request->positionId : 1;
        $category_id = $request->categoryId ? (int)$request->categoryId : 0;
        $terminalType = $request->terminalType ? (int)$request->terminalType : 1;

        $this->tableObj = $this->getViewAdsTable();
        $this->initModel();
        $this->tableObj->positionId = $position_id;
        $this->tableObj->categoryId = $category_id;
        $this->tableObj->terminalType = $terminalType;

        $data = $this->tableObj->getList();
        $list = array();
        foreach($data['list'] as $v)
        {
            $item = array();
            $item['id'] = $v->id;
            $item['name'] = $v->name;
            $item['link'] = $v->link;
            $item['startTime'] = $v->start_time;
            $item['endTime'] = $v->end_time;
            $item['positionId'] = $v->position_id;
            $item['imagePath'] = $v->path.$v->filename;
            if($position_id == 5)
            {
                $item['categoryId'] = $v->category_id;
            }
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