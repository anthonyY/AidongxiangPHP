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

    //编辑广告
    public function adsEditAction(){
        $this->checkLogin('admin_business_adsList');
        $request = $this->getRequest();
        $ads_id = $this->params('id');
        $ads = $this->getAdsTable();
        $ads->id = $ads_id;
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
            if(!$ads->updateData()){
                $this->ajaxReturn(10000, '编辑失败');
            }
            else{
                //跳转到广告列表页
                $this->ajaxReturn(0, '编辑成功', $this->url()->fromRoute('admin-business', ['action'=>'adsList']));
            }
        }

        $adsView = $this->getViewAdsTable();
        $adsView->id = $ads_id;
        $ads_info = $adsView->getDetails();

        $view = new ViewModel(['ads_info'=>$ads_info]);
        $view->setTemplate("admin/business/adsEdit");
        return $this->setMenu($view);
    }

    public function navigationAction()
    {
        $this->checkLogin('admin_business_navigation');
        $page = $this->params("page");
        $viewNavigation = $this->getViewNavigationTable();
        $viewNavigation->page = $page;

        $get = [];
        if($_GET){
            $get = $_GET;
            $viewNavigation->type = $get['type']??0;
        }

        $list = $viewNavigation->getList();

        $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'navigation'), 'where'=>$get));
        $view->setTemplate("admin/business/navigation");
        return $this->setMenu($view);
    }

    //导航删除
    public function navigationDelAction(){
        $this->checkLogin('admin_business_index');
        $navigation_id = $_POST['id'];
        $navigation = $this->getNavigationTable();
        $navigation->type = 1;
        $navigation->id = $navigation_id;

        if($navigation->deleteData()){
            $this->ajaxReturn(0, '删除成功');
        }
        else{
            $this->ajaxReturn(10000, '删除失败');
        }
    }

    //新增导航
    public function navigationAddAction(){
        $this->checkLogin('admin_business_index');
        $request = $this->getRequest();
        $navigation = $this->getNavigationTable();
        $post = $request->getPost()->toArray();
        if($request->isPost()){
            //验证数据
            if(empty($post['name'])){
                $this->ajaxReturn(10000, '导航名称不能为空');
            }
            if(empty($post['sort']) || !is_numeric($_POST['sort'])){
                $this->ajaxReturn(10000, '排序序号不能为空且必须为数字');
            }
            if(empty($post['image_id'])){
                $this->ajaxReturn(10000, '导航图片不能为空');
            }

            $from_type = $post['from_type'];
            if(!in_array($from_type,[1,2,3,4]))
            {
                $this->ajaxReturn(10000, '非法操作');
            }

            if($from_type == 1)//外部链接
            {
                if(empty($post['link'])){
                    $this->ajaxReturn(10000, '跳转链接不能为空');
                }
                if(!preg_match('{^https?:\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?$}', $post['link'])){
                    $this->ajaxReturn(10000, '自定义链接不符合规则');
                }
                $navigation->link = $post['link'];
            }
            else
            {
                if(!$post['from_id'])
                {
                    $this->ajaxReturn(10000, '来源不能为空');
                }

                if($from_type == 4)//资讯
                {
                    $model = $this->getArticleTable();
                    $model->id = $post['from_id'];
                }
                else //视频|音频
                {
                    $model = $this->getAudioTable();
                    $model->id = $post['from_id'];
                }
                $info = $model->getDetails();
                if(!$info)
                {
                    $this->ajaxReturn(10000, '来源不存在');
                }
                $navigation->fromId = $post['from_id'];
            }

            //添加数据
            $navigation->fromType = $post['from_type'];
            $navigation->name = $post['name'];
            $navigation->icon = $post['image_id'];
            $navigation->sort = $post['sort'];

            //保存
            if(!$navigation->addData()){
                $this->ajaxReturn(10000, '添加失败');
            }
            else{
                //跳转到导航列表页
                $this->ajaxReturn(0, '添加成功', $this->url()->fromRoute('admin-business', ['action'=>'navigation']));
            }
        }

        $view = new ViewModel();
        $view->setTemplate("admin/business/navigationAdd");
        return $this->setMenu($view);
    }

    //导航详情
    public function navigationDetailsAction(){
        $this->checkLogin('admin_business_index');
        $request = $this->getRequest();
        $navigation_id = $this->params('id');
        $navigationView = $this->getViewNavigationTable();
        $post = $request->getPost()->toArray();
        $navigation = $this->getNavigationTable();
        $navigation->id = $navigation_id;
        $navigationView->id = $navigation_id;
        if($request->isPost()){
            //验证数据
            if(empty($post['name'])){
                $this->ajaxReturn(10000, '导航名称不能为空');
            }
            if(empty($post['sort']) || !is_numeric($_POST['sort'])){
                $this->ajaxReturn(10000, '排序序号不能为空且不能为数字');
            }
            if(empty($post['image_id'])){
                $this->ajaxReturn(10000, '导航图片不能为空');
            }

            $from_type = $post['from_type'];
            if(!in_array($from_type,[1,2,3,4]))
            {
                $this->ajaxReturn(10000, '非法操作');
            }

            if($from_type == 1)//外部链接
            {
                if(empty($post['link'])){
                    $this->ajaxReturn(10000, '跳转链接不能为空');
                }
                if(!preg_match('{^https?:\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?$}', $post['link'])){
                    $this->ajaxReturn(10000, '自定义链接不符合规则');
                }
                $navigation->link = $post['link'];
            }
            else
            {
                if(!$post['from_id'])
                {
                    $this->ajaxReturn(10000, '来源不能为空');
                }

                if($from_type == 4)//资讯
                {
                    $model = $this->getArticleTable();
                    $model->id = $post['from_id'];
                }
                else //视频|音频
                {
                    $model = $this->getAudioTable();
                    $model->id = $post['from_id'];
                }
                $info = $model->getDetails();
                if(!$info)
                {
                    $this->ajaxReturn(10000, '来源不存在');
                }
                $navigation->fromId = $post['from_id'];
            }

            //添加数据
            $navigation->fromType = $post['from_type'];
            $navigation->name = $post['name'];
            $navigation->icon = $post['image_id'];
            $navigation->sort = $post['sort'];

            //保存
            if(!$navigation->updateData()){
                $this->ajaxReturn(10000, '编辑失败');
            }
            else{
                //跳转到导航列表页
                $this->ajaxReturn(0, '编辑成功', $this->url()->fromRoute('admin-business', ['action'=>'navigation']));
            }
        }

        $navigation_info = $navigationView->getDetails();
        $source = [];
        if($navigation_info->from_type != 1)
        {
            if($navigation_info->from_type == 4)//资讯
            {
                $article = $this->getArticleTable();
                $article->id = $navigation_info->from_id;
                $info = $article->getDetails();
                if($info)$source = ['id'=>$navigation_info->from_id,'name'=>$info->title];
            }
            else
            {
                $audio = $this->getAudioTable();
                $audio->id = $navigation_info->from_id;
                $info = $audio->getDetails();
                if($info)$source = ['id'=>$navigation_info->from_id,'name'=>$info->name];
            }
        }


        $view = new ViewModel(['navigation_info'=>$navigation_info,'source'=>$source]);
        $view->setTemplate("admin/business/navigationDetails");
        return $this->setMenu($view);
    }

    /**
     * 搜索列表（视频，音频，资讯）
     */
    public function searchListAction()
    {
        $request = $this->getRequest();
        $post = $request->getPost()->toArray();
        $from_type = $post['from_type'];
        $search_key = $post['search_key'];
        $data = [];
        if(!in_array($from_type,[2,3,4]) || !$search_key){
            die(json_encode($data));
        }
        if($from_type == 4)//资讯
        {
            $article = $this->getViewArticleTable();
            $article->searchKeyWord = $search_key;
            $res = $article->getList();
            foreach ($res['list'] as $v) {
                $item = [
                    'id' =>$v->id,
                    'name' => $v->title,
                ];
                $data[] = $item;
            }
        }
        else
        {
            $audio = $this->getViewAudioTable();
            $audio->type = $from_type==2?1:2;
            $audio->status = 1;
            $audio->searchKeyWord = $search_key;
            $res = $audio->getList();
            foreach ($res['list'] as $v) {
                $item = [
                    'id' =>$v->id,
                    'name' => $v->name,
                ];
                $data[] = $item;
            }
        }
        die(json_encode($data));
    }

    //手机申诉列表
    public function mobileAppealListAction()
    {
        $this->checkLogin('admin_business_mobileAppeal');
        $page = $this->params("page");
        $ViewMobileAppeal = $this->getViewMobileAppealTable();
        $ViewMobileAppeal->page = $page;
        $status = 0;

        $get = [];
        if($_GET){
            $get = $_GET;
            $status = isset($get['status'])?$get['status']:0;
            $ViewMobileAppeal->status = $status;
        }

        $list = $ViewMobileAppeal->getList();
        $statusArr = [
            0=>'全部',
            1=>'待审核',
            2=>'审核通过',
            3=>'审核失败'
        ];

        $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'mobileAppealList'),'status'=>$status, 'where'=>$get,'statusArr'=>$statusArr));
        $view->setTemplate("admin/business/mobileAppealList");
        return $this->setMenu($view);
    }

    //手机申诉处理
    public function mobileAppealAction()
    {
        $id = $_POST['id'];
        $action = $_POST['action'];
        if(!$id || !in_array($action,['SUCCESS','FAIL']))
        {
            $this->ajaxReturn(10000,'非法操作');
        }
        $mobileAppeal = $this->getMobileAppealTable();
        $mobileAppeal->id = $id;
        $res = $mobileAppeal->mobileAppeal($action);
        $this->ajaxReturn($res['s'],$res['d']);
    }
}

