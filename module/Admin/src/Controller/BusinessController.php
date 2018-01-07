<?php
namespace Admin\Controller;

use Admin\Controller\CommonController;
use Zend\View\Model\ViewModel;

class BusinessController extends CommonController
{
    //广告列表
    public function adsListAction()
    {
        $this->checkLogin('admin_business_adsList');
        $page = $this->params("page",1);
        $ads = $this->getViewAdsTable();
        $ads->page = $page;

        $get = [];
        if($_GET){
            $get = $_GET;
            $ads->position = $get['position']??0;
        }

        $list = $ads->getList();
        $positionArr = array(0=>'全部',1=>'首页',2=>'视频首页', 3=>'音频首页');
        $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'advertList'),'positions'=>$positionArr, 'where'=>$get));
        $view->setTemplate("admin/business/advertList");
        return $this->setMenu($view);
    }

    //广告删除
    public function adsDelAction(){
        $this->checkLogin('admin_business_adsList');
        $advert_id = $_POST['id'];
        $advert = $this->getAdsTable();
        $advert->id = $advert_id;

        if($advert->deleteData()){
            $this->ajaxReturn(0, '删除成功');
        }else{
            $this->ajaxReturn(10000, '删除失败');
        }
    }

    /**
     * @throws \Exception
     * 保存廣告排序
     */
    public function saveAdsSortAction()
    {
        $sort_array = $_POST['sortObject'];
        if(!is_array($sort_array) || !$sort_array)
        {
            $this->ajaxReturn(10000, '删除失败');
        }
        $ads = $this->getAdsTable();
        $res = $ads->saveSort($sort_array);
        $this->ajaxReturn($res['s'],$res['d']);
    }
}
