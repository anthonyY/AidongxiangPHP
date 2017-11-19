<?php
namespace Api\Controller;

/**
 * 业务，文章详情
 */
class ArticleDetails extends CommonController
{
    private $registerSetupId = 20;

    private $secretSetupId = 21;

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $id = $request->id; // 资讯ID
        $action = $request->action ? $request->action : 1;//类型：1资讯；2注册协议 ,3隐私协议，4系统消息，5个人消息,6广告图文消息
        if(!in_array($action,[1,2,3,4,5,6]) || (!in_array($action,[2,3]) && !$id))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if(in_array($action,[1,4,5,6]))
        {
            if($action == 1)
            {
                $this->tableObj = $this->getViewArticleTable();
            }
            elseif($action == 4)
            {
                $this->tableObj = $this->getViewNotificationTable();
            }
            elseif($action == 5)
            {
                $this->tableObj = $this->getViewNotificationRecordsTable();
            }
            elseif($action == 6)
            {
                $this->tableObj = $this->getAdsTable();

            }
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

        $article = '';
        $details = $this->tableObj->getDetails();

        if($details)
        {
            $article = array(
                'id' => $details->id,
                'timestamp' => $details->timestamp,
            );
            if(in_array($action,[1,4,5,6]))
            {
                $article['title'] = $action != 6 ? $details->title : $details->name;
                $article['content'] = $details->content;
            }
            else
            {
                $article['title'] = $details->text;
                $article['content'] = $details->value;
            }
        }

        $response->status = STATUS_SUCCESS;
        $response->article = $article;
        return $response;
    }
}
