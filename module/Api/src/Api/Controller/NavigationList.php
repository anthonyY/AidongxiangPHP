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
        if(!in_array($action,array(1))){//1首页
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $list = array();
        $total = 0;
        $this->tableObj = $this->getViewNavigationTable();
        $this->tableObj->type = $action;
        $data = $this->tableObj->getApiList();
        if($data['list'])
        {
             foreach ($data['list'] as $val) {
                    $item = array(
                        'id' => $val->id,
                        'icon' => $val->filename ? $val->path . $val->filename : '',
                        'name' => $val->name,
                        'link' => $val->link,
                        'fromType' => $val->from_type,
                        'fromId' => $val->from_id,
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
