<?php
namespace Api\Controller;

use Api\Controller\Request\AudioWhereRequest;

/**
 * 资讯列表
 * @author WZ
 */
class ArticleList extends CommonController
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
        $total = 0;
        $list = array();
        $action = $request->action ? $request->action : 1;//1平台资讯
        if(!in_array($action,array(1)))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $table_where = $this->getTableWhere();
        $search_key = $table_where->search_key;
        $category_id = $table_where->categoryId;
        $this->tableObj = $this->getViewArticleTable();
        $this->initModel();
        $this->tableObj->categoryId = $category_id;
        $this->tableObj->searchKeyWord = $search_key;
        $data = $this->tableObj->getApiList();
        if(isset($data['list']) && $data['list'])
        {
            foreach ($data['list'] as $val) {
                $item = [
                    'id' => $val->id,
                    'title' => $val->title,
                    'imagePath' => $val->path . $val->filename,
                    'timestamp' => $val->timestamp,
                    'categoryName' => $val->categoryName,
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
                $result = "read_num";
                break;
            default:
                $result = "id";
                break;
        }
        return $result;
    }
}
