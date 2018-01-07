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
        $ads->orderBy = 'sort DESC';

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

    //新增广告
    public function adsAddAction(){
        $this->checkLogin('admin_business_adsList');
        $request = $this->getRequest();
        $ads = $this->getAdsTable();
        if($request->isPost()){
            $post = $request->getPost()->toArray();

            //验证数据
            if(empty($post['name'])){
                $this->ajaxReturn(0, '廣告名称不能为空');
            }
            if(empty($post['sort']) || !is_numeric($_POST['sort'])){
                $this->ajaxReturn(0, '排序序号不能为空且必须为数字');
            }
            if(empty($post['start_time'])){
                $this->ajaxReturn(0, '开始时间不能为空');
            }
            if(empty($post['end_time'])){
                $this->ajaxReturn(0, '结束时间不能为空');
            }
            if(empty($post['image_id'])){
                $this->ajaxReturn(0, '广告图片不能为空');
            }
            if($post['type'] == 3){
                if(empty($post['content'])){
                    $this->ajaxReturn(0, '图文消息不能为空');
                }
            }
            else if($post['type'] == 4){
                if(empty($post['link'])){
                    $this->ajaxReturn(0, '自定义链接不能为空');
                }
                if(!preg_match('{^https?:\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?$}', $post['link'])){
                    $this->ajaxReturn(0, '自定义链接不符合规则');
                }
            }

            //添加数据
            $ads->name = $post['name'];
            $ads->sort = $post['sort'];
            $ads->startTime = $post['start_time'];
            $ads->endTime = $post['end_time'];
            $ads->imageId = $post['image_id'];
            $ads->position = $post['position'];
            $ads->type = $post['type'];
            if($post['type'] == 3){
                $ads->content = $post['content'];
            }
            elseif($post['type'] == 4) {
                $ads->content = $post['link'];
            }

            //保存
            if(!$ads->addData()){
                $this->ajaxReturn(10000, '添加失败');
            }
            else{
                //跳转到广告列表页
                $this->ajaxReturn(0, '添加成功', $this->url()->fromRoute('admin-business', ['action'=>'adsList']));
            }
        }
        $view = new ViewModel();
        $view->setTemplate("admin/business/adsAdd");
        return $this->setMenu($view);
    }
}
