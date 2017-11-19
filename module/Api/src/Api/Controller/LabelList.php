<?php
namespace Api\Controller;

/**
 * 标签列表
 * @author WZ
 */
class LabelList extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $total = 0;
        $list = array();
        $action = $request->action ? $request->action : 1;
        $this->tableObj = $this->getGoodsLabelTable();
        $this->initModel();
        $this->tableObj->orderBy = 'sort ASC';
        $data = $this->tableObj->getApiList($action);
        if(isset($data['list']) && $data['list'])
        {
            foreach ($data['list'] as $val) {
                $item = [
                    'id' => $val->id,
                    'name' => $val->name,
                ];
                $list[] = $item;
            }
            $total = $data['total'];
        }

        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->Labels = $list;
        return $response;
    }
}
