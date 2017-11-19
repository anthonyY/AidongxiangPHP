<?php
namespace Api\Controller;
use Api\Controller\Request\CategoryRequest;

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
        $this->myRequest = new CategoryRequest();
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
        $merchant_id = (int)$request->merchantId;
        $type = (int)$request->type;//门店类型：1自营 2加盟 a!=4时
        $id = $request->id;//分类父ID，有可能是数组PC
        if(!$action || !in_array($action ,array(1,2,3,4,5)))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        if($action !=4 && $action !=5)
        {
            $this->tableObj = $this->getViewCategoryTable();
            $this->initModel();
            $this->tableObj->orderBy = 'sort ASC';
            $this->tableObj->type = $action;
            $this->tableObj->mUuid = $merchant_id;
            $this->tableObj->mType = $type == 1 ? 2 : 1;
            $this->tableObj->parentId = $id;
            $data = $this->tableObj->getApiList();
            $list = array();
            foreach($data['list'] as $v)
            {
                $item = array();
                $item['id'] = $v->id;
                $item['name'] = $v->name;
                $item['imagePath'] = $v->path.$v->filename;
                $item['parentId'] = $v->parent_id;
                $list[] = $item;
            }
        }
        else
        {
            if($action == 4)
            {
                $list[] = array("1"=>'商品与描述不符','2'=>'少件漏发','3'=>'卖家发错货','4'=>'未按约定时间发货','5'=>'其它原因');
            }
            else
            {
                $list[] = array("1"=>'我不想买了','2'=>'信息填写错误，重新拍','3'=>'其它原因');
            }
        }
        $response->status = STATUS_SUCCESS;
        $response->total = isset($data['total'])  ?  $data['total'] : count($list);
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