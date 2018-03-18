<?php
namespace Api\Controller;

/**
 * 分类
 *
 * @author Administrator
 *
 */
class CategoryList extends CommonController
{

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 返回一个数组或者Result类
     * @return \Api\Controller\BaseResult
     */
    public function index()
    {
        $request = $this->getAiiRequest();

        $response = $this->getAiiResponse();
        $action =(int) $request->action;
        if(!in_array($action,[1,2,3,4]))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $this->tableObj = $this->getViewCategoryTable();
        $this->initModel();
        $this->tableObj->orderBy = 'sort DESC';
        $this->tableObj->type = $action;
        $this->tableObj->status = 1;
        $data = $this->tableObj->getApiList();
        $list = array();
        foreach($data['list'] as $v)
        {
            $item = [
                'id' => $v->id,
                'name' => $v->name,
                'imagePath' => $v->path.$v->filename
            ];

            $list[] = $item;
        }

        $response->status = STATUS_SUCCESS;
        $response->total = $data['total'];
        $response->categorys = $list;
        return $response;
    }

    public function OrderBy($order_by = 1)
    {

        $result = "sort asc";
        return $result;
    }

    public function OrderType($order_type = 2)
    {
        return parent::OrderType($order_type);
    }

}