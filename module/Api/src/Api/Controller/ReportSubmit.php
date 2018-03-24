<?php
namespace Api\Controller;

use Api\Controller\Request\ReportRequest;

/**
 * 举报
 * @author WZ
 * @version 1.0.140515 WZ
 *
 */
class ReportSubmit extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new ReportRequest();
        parent::__construct();
    }

    /**
     *
     * @return \Api\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $action = $request->action;
        $id = $request->id;
        $category_id = $request->categoryId;
        $content = addslashes(strip_tags($request->content));
        if(!in_array($action,[1,2]) || !$id || !$content || !$category_id)
        {
            STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $reportTable = $this->getReportTable();
        $reportTable->categoryId = $category_id;
        $reportTable->fromId = $id;
        $reportTable->content = $content;
        $reportTable->type = $action;
        $reportTable->userId = $this->getUserId();
        $res = $reportTable->reportSubmit();
        $response->status = $res['s'];
        if(isset($res['d']))$response->description = $res['d'];
        return $response;
    }
}
