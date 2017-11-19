<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;

/**
 * 导航列表
 * @author lzw
 */
class NavigationList extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();

        $action = $request->action;
        if(!in_array($action,array(1,2,3,4,5))){//1首页；2商城首页；3服务首页；4门店首页，5自营商城首页
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $list = array();
        $total = 0;
        $this->tableObj = $this->getViewNavigationTable();
        $this->tableObj->type = $action;
        $data = $this->tableObj->getList();
        if($data['list'])
        {
             foreach ($data['list'] as $val) {
                    $item = array(
                        'id' => $val->id,
                        'icon' => $val->path && $val->filename ? $val->path . $val->filename : '',
                        'name' => $val->name,
                        'link' => $val->link
                    );
                    $list[] = $item;
             }
             $total = $data['total'];
        }
        $response->status = STATUS_SUCCESS;
        $response->navigations = $list;
        $response->total = $total;
        return $response;
    }
}
