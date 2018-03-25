<?php
namespace Api\Controller;

/**
 * 业务，文章详情
 */
class ArticleDetails extends CommonController
{
    private $registerSetupId = 1;

    private $secretSetupId = 2;

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $id = $request->id; // 资讯ID
        $action = $request->action ? $request->action : 1;//类型：1资讯；2注册协议 ,3隐私协议
        if(!in_array($action,[1,2,3]) || ($action == 1 && !$id))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if($action == 1)
        {
            $this->tableObj = $this->getViewArticleTable();
            $this->tableObj->id = $id;
        }
        else
        {
            $this->tableObj = $this->getSetupTable();
            if($action == 2)
            {
                $this->tableObj->id = $this->registerSetupId;
            }
            if($action == 3)
            {
                $this->tableObj->id = $this->secretSetupId;
            }
        }

        $details = $this->tableObj->getDetails();
        if(!$details)
        {
            return STATUS_NODATA;
        }

        $article = array(
            'id' => $details->id,
            'timestamp' => $details->timestamp,
        );
        if($action == 1)
        {
            $article['title'] = $action != 6 ? $details->title : $details->name;
            $article['content'] = $details->content;
        }
        else
        {
            $article['title'] = $details->text;
            $article['content'] = $details->value;
        }

        $response->status = STATUS_SUCCESS;
        $response->article = $article;
        return $response;
    }
}
