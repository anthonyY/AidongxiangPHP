<?php
namespace Api\Controller;
use Api\Controller\Request\ServiceTypeAttributeActionRequest;

/**
 * 地址列表
 */
class ServiceTypeAttributeAction extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new ServiceTypeAttributeActionRequest();
        parent::__construct();
    }
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();

        $ids = (array)$request->ids;
        if(!$ids)
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }

        $this->tableObj = $this->getViewServiceTypeAttributeTable();
        $this->initModel();
        $this->tableObj->serviceTypeId = $ids;
        $data = $this->tableObj->getList();
        $total = $data['total'];

        $list = array();
        foreach($data['list'] as $key => $value){
            if(!isset($list[$value->service_type_id]))
            {
                $item =array('id'=>$value['service_type_id'],'name'=>$value['t_name']);

                foreach($data['list'] as $v)
                {
                    $attr = array();
                    if($v->service_type_id == $value->service_type_id)
                    {
                        $attr = array(
                            'id' => $v['id'],
                            'name' => $v['name'],
                            'type' => $v['type'],
                            'attributeValue' => $v['attribute_value'] ? explode("\n",$v['attribute_value']) : array()//以换行分割
                        );
                        $item['attributes'][]= $attr;
                    }

                }
                $list[$value->service_type_id]= $item;
            }
        }

        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->serviceTypes =array_values($list);
        return $response;
    }

    public function OrderBy($order_by = 1)
    {
        $response = 't_sort asc, sort';
        return $response;
    }

    public function OrderType($order_by = 1)
    {
        $response = 'asc';
        return $response;
    }
}
