<?php
namespace Api\Controller;

/**
 * 资讯列表
 * @author WZ
 */
class ArticleList extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $total = 0;
        $list = array();
        $action = $request->action ? $request->action : 1;//1平台资讯，2自营资讯
        if(!in_array($action,array(1,2)))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $this->tableObj = $this->getViewArticleTable();
        $this->tableObj->from = $action;
        $this->initModel();
        $data = $this->tableObj->getApiList();
        if(isset($data['list']) && $data['list'])
        {
            foreach ($data['list'] as $val) {
                $item = [
                    'id' => $val->id,
                    'title' => $val->title,
                    'imagePath' => $val->path . $val->filename,
                    'timestamp' => $val->timestamp,
                    'abstract' => $val->abstract,
                ];
                $list[] = $item;
            }
            $total = $data['total'];
        }

        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->articles = $list;
        return $response;
    }
}
