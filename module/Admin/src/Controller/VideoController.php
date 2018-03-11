<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Admin\Controller\CommonController;
use Zend\View\Model\ViewModel;

class VideoController extends CommonController
{
    //视频列表
    public function indexAction()
    {
        $this->checkLogin('admin_video_index');
        $page = $this->params("page");
        $ViewAudio = $this->getViewAudioTable();
        $ViewAudio->page = $page;
        $ViewAudio->type = 1;
        $category = $this->getCategoryTable();
        $category->type = 1;
        $category->needPage = 2;

        $get = [];
        $category_id = 0;
        $status = 0;
        $keyword = '';
        if($_GET){
            $get = $_GET;
            $ViewAudio->searchKeyWord = $get['keyword']??'';
            $ViewAudio->categoryId = $get['category_id']??0;
            $ViewAudio->status = $get['status']??0;
            $category_id = $get['category_id']??0;
            $keyword = $get['keyword']??'';
            $status = $get['status']??0;
        }

        $list = $ViewAudio->getList();
        $category_list = $category->getList();

        $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'index'), 'where'=>$get,'keyword'=>$keyword,'category_id'=>$category_id,'status'=>$status,'categoryList'=>$category_list['list']));
        $view->setTemplate("admin/video/index");
        return $this->setMenu($view);
    }

    //视频删除
    public function videoDelAction(){
        $this->checkLogin('admin_video_index');
        $video_id = $_POST['id'];
        $audio = $this->getAudioTable();
        $audio->id = $video_id;
        if($audio->deleteData()){
            $this->ajaxReturn(0, '删除成功');
        }
        else{
            $this->ajaxReturn(10000, '删除失败');
        }
    }

    //改变视频状态
    public function changeStatusAction(){
        $this->checkLogin('admin_video_index');
        $video_id = $_POST['id'];
        $status = $_POST['status'];
        $audio = $this->getAudioTable();
        $audio->id = $video_id;
        $audio->status = $status;
        if($audio->updateData()){
            $this->ajaxReturn(0, '操作成功');
        }
        else{
            $this->ajaxReturn(10000, '操作失败');
        }
    }

    //添加视频
    public function videoAddAction(){
        $this->checkLogin('admin_video_index');
        $request = $this->getRequest();
        $audio = $this->getAudioTable();
        $post = $request->getPost()->toArray();
        if($request->isPost()){
            //验证数据
            if(empty($post['name'])){
                $this->ajaxReturn(10000, '视频名称不能为空');
            }
            if(empty($post['category_id'])){
                $this->ajaxReturn(10000, '视频分类不能为空');
            }
            if($post['pay_type']  == 2 && empty($post['price'])){
                $this->ajaxReturn(10000, '收费视频，请填写视频价格');
            }
            if(empty($post['image_id'])){
                $this->ajaxReturn(10000, '视频封面不能为空');
            }
            if(empty($post['size'])){
                $this->ajaxReturn(10000, '视频大小错误');
            }
            if(empty($post['filename'])){
                $this->ajaxReturn(10000, '视频原文件名称不能为空');
            }
            if(empty($post['full_path'])){
                $this->ajaxReturn(10000, '视频完整路径不能为空');
            }
            if(empty($post['audio_length'])){
                $this->ajaxReturn(10000, '视频时长错误');
            }
            if(empty($post['auditions_path'])){
                $this->ajaxReturn(10000, '试播视频路径不能为空');
            }
            if(empty($post['auditions_length'])){
                $this->ajaxReturn(10000, '试播视频时长错误');
            }
            if(empty($post['description'])){
                $this->ajaxReturn(10000, '视频简介不能为空');
            }

            //添加数据
            $audio->name = $post['name'];
            $audio->categoryId = $post['category_id'];
            if($post['pay_type'] == 2)
            {
                $audio->payType = 2;
                $audio->price = $post['price'];
            }
            $audio->imageId = $post['image_id'];
            $audio->size = $post['size'];
            $audio->filename = $post['filename'];
            $audio->fullPath = $post['full_path'];
            $audio->audioLength = $post['audio_length'];
            $audio->auditionsPath = $post['auditions_path'];
            $audio->auditionsLength = $post['auditions_length'];
            $audio->description = $post['description'];

            //保存
            if(!$audio->addData()){
                $this->ajaxReturn(10000, '添加失败');
            }
            else{
                //跳转到导航列表页
                $this->ajaxReturn(0, '添加成功', $this->url()->fromRoute('admin-video', ['action'=>'index']));
            }
        }

        $category = $this->getCategoryTable();
        $category->type = 1;
        $category->needPage = 2;
        $category_list = $category->getList();
        $data = [
            'category_list'=>$category_list['list'],
            'appid' => COS_APP_ID,
            'bucket' => COS_BUCKET,
            'region' => COS_REGION
        ];
        $view = new ViewModel($data);
        $view->setTemplate("admin/video/videoAdd");
        return $this->setMenu($view);
    }

    //视频详情
    public function videoDetailsAction(){
        $this->checkLogin('admin_video_index');
        $request = $this->getRequest();
        $id = $this->params('id');
        $viewAudio = $this->getViewAudioTable();
        $viewAudio->id = $id;
        $post = $request->getPost()->toArray();
        $info = $viewAudio->getDetails();
        if($request->isPost()){
            $audio = $this->getAudioTable();
            $audio->id = $post['id'];
            //验证数据
            if(empty($post['name'])){
                $this->ajaxReturn(10000, '视频名称不能为空');
            }
            if(empty($post['category_id'])){
                $this->ajaxReturn(10000, '视频分类不能为空');
            }
            if($post['pay_type']  == 2 && empty($post['price'])){
                $this->ajaxReturn(10000, '收费视频，请填写视频价格');
            }
            if(empty($post['image_id'])){
                $this->ajaxReturn(10000, '视频封面不能为空');
            }
            if(empty($post['size'])){
                $this->ajaxReturn(10000, '视频大小错误');
            }
            if(empty($post['filename'])){
                $this->ajaxReturn(10000, '视频原文件名称不能为空');
            }
            if(empty($post['full_path'])){
                $this->ajaxReturn(10000, '视频完整路径不能为空');
            }
            if(empty($post['audio_length'])){
                $this->ajaxReturn(10000, '视频时长错误');
            }
            if(empty($post['auditions_path'])){
                $this->ajaxReturn(10000, '试播视频路径不能为空');
            }
            if(empty($post['auditions_length'])){
                $this->ajaxReturn(10000, '试播视频时长错误');
            }
            if(empty($post['description'])){
                $this->ajaxReturn(10000, '视频简介不能为空');
            }

            //添加数据
            $audio->name = $post['name'];
            $audio->categoryId = $post['category_id'];
            if($post['pay_type'] == 2)
            {
                $audio->payType = 2;
                $audio->price = $post['price'];
            }
            $audio->imageId = $post['image_id'];
            $audio->size = $post['size'];
            $audio->filename = $post['filename'];
            $audio->fullPath = $post['full_path'];
            $audio->audioLength = $post['audio_length'];
            $audio->auditionsPath = $post['auditions_path'];
            $audio->auditionsLength = $post['auditions_length'];
            $audio->description = $post['description'];

            //保存
            if(!$audio->updateData()){
                $this->ajaxReturn(10000, '编辑失败');
            }
            else{
                //跳转到导航列表页
                $this->ajaxReturn(0, '编辑成功', $this->url()->fromRoute('admin-video', ['action'=>'index']));
            }
        }

        $category = $this->getCategoryTable();
        $category->type = 1;
        $category->needPage = 2;
        $category_list = $category->getList();
        $data = [
            'category_list'=>$category_list['list'],
            'appid' => COS_APP_ID,
            'bucket' => COS_BUCKET,
            'region' => COS_REGION,
            'info' => $info
        ];
        $view = new ViewModel($data);
        $view->setTemplate("admin/video/videoDetails");
        return $this->setMenu($view);
    }

    //视频分类列表
    public function videoCategoryListAction()
    {
        $this->checkLogin('admin_video_categoryList');
        $ViewCategory = $this->getViewCategoryTable();
        $ViewCategory->type = 1;//视频分类
        $ViewCategory->orderBy = 'sort desc';
        $get = [];
        $status = 0;
        $keyword = '';
        if($_GET){
            $get = $_GET;
            $ViewCategory->searchKeyWord = $get['keyword']??'';
            $ViewCategory->status = $get['status']??0;
            $keyword = $get['keyword']??'';
            $status = $get['status']??0;
        }
        $list = $ViewCategory->getList();
        $data = [
            'list' => $list['list'],
            'keyword' => $keyword,
            'status' => $status
        ];

        $view = new ViewModel($data);
        $view->setTemplate("admin/video/videoCategoryList");
        return $this->setMenu($view);
    }

    /**
     * @throws \Exception
     * 保存廣告排序
     */
    public function saveCategorySortAction()
    {
        $sort_array = $_POST['sortObject'];
        if(!is_array($sort_array) || !$sort_array)
        {
            $this->ajaxReturn(10000, '删除失败');
        }
        $category = $this->getCategoryTable();
        $res = $category->saveSort($sort_array);
        $this->ajaxReturn($res['s'],$res['d']);
    }

    //改变视频分类状态
    public function changeCategoryStatusAction(){
        $id = $_POST['id'];
        $status = $_POST['status'];
        $category = $this->getCategoryTable();
        $category->id = $id;
        $category->status = $status;
        $res = $category->updateVideoCategoryStatus();
        if($res['s'] == 0){
            $this->ajaxReturn(0, $res['d']);
        }
        else{
            $this->ajaxReturn(10000, $res['d']);
        }
    }

    //视频分类删除
    public function videoCategoryDelAction(){
        $id = $_POST['id'];
        $category = $this->getCategoryTable();
        $category->id = $id;
        $res = $category->videoCategoryDel();
        $this->ajaxReturn($res['s'], $res['d']);
    }

    //编辑添加分类
    public function editCategoryAction(){
        $id = $_POST['id'];
        $name = $_POST['name'];
        $sort = $_POST['sort'];
        $category = $this->getCategoryTable();
        $category->name = $name;
        $category->sort = $sort;
        $category->type = 1;
        if($id)
        {
            $category->id = $id;
            $res = $category->updateData();
        }
        else
        {
            $res = $category->addData();
        }
        if($res === false)
        {
            $this->ajaxReturn(10000, '操作失败');
        }
        else
        {
            $this->ajaxReturn(0, '操作成功');
        }
    }
}
