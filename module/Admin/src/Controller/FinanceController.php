<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Admin\Controller\CommonController;
use Zend\View\Model\ViewModel;

class FinanceController extends CommonController
{
    //财务列表
    public function indexAction()
    {
        $this->checkLogin('admin_financial_index');
        $page = $this->params("page");
        $ViewFinancial = $this->getViewFinancialTable();
        $ViewFinancial->page = $page;

        $get = [];
        $type = 0;//1 购买音频 2购买视频 3充值
        $search_type = 1;//1流水号 2用户昵称
        $start_time = '';
        $end_time = '';
        $keyword = '';
        if($_GET){
            $get = $_GET;
            $ViewFinancial->searchKeyWord = $get['keyword']??'';
            $ViewFinancial->type = $get['type']??0;
            $ViewFinancial->startTime = $get['start_time']??'';
            $ViewFinancial->endTime = $get['end_time']??'';
            $type = $get['type']??0;
            $keyword = $get['keyword']??'';
            $search_type = $get['search_type']??1;
            $start_time = $get['start_time']??'';
            $end_time = $get['end_time']??'';
        }
        $search_key = $search_type==1?['transfer_no']:['nick_name'];
        $list = $ViewFinancial->getList($search_key);

        $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'index'), 'where'=>$get,'keyword'=>$keyword,'type'=>$type,'search_type'=>$search_type,'start_time'=>$start_time,'end_time'=>$end_time));
        $view->setTemplate("admin/finance/index");
        return $this->setMenu($view);
    }
}
