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
            if(empty($post['sort']) || !is_numeric($_POST['sort'])){
                $this->ajaxReturn(10000, '排序序号不能为空且必须为数字');
            }
            if(empty($post['link'])){
                $this->ajaxReturn(10000, '跳转链接不能为空');
            }
            if(!preg_match('{^https?:\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?$}', $post['link'])){
                $this->ajaxReturn(10000, '自定义链接不符合规则');
            }
            if(empty($post['image_id'])){
                $this->ajaxReturn(10000, '导航图片不能为空');
            }

            //添加数据
            $audio->name = $post['name'];
            $audio->link = $post['link'];
            $audio->icon = $post['image_id'];
            $audio->sort = $post['sort'];

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
}
