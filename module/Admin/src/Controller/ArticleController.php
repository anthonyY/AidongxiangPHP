<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Admin\Controller\CommonController;
use Zend\View\Model\ViewModel;

class ArticleController extends CommonController
{
    //资讯列表
    public function indexAction()
    {
        $this->checkLogin('admin_article_index');
        $page = $this->params("page");
        $ViewArticle = $this->getViewArticleTable();
        $ViewArticle->page = $page;
        $category = $this->getCategoryTable();
        $category->type = 3;
        $category->needPage = 2;

        $get = [];
        $category_id = 0;
        $keyword = '';
        if($_GET){
            $get = $_GET;
            $ViewArticle->searchKeyWord = $get['keyword']??'';
            $ViewArticle->categoryId = $get['category_id']??0;
            $category_id = $get['category_id']??0;
            $keyword = $get['keyword']??'';
        }

        $list = $ViewArticle->getList();
        $category_list = $category->getList();

        $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'index'), 'where'=>$get,'keyword'=>$keyword,'category_id'=>$category_id,'categoryList'=>$category_list['list']));
        $view->setTemplate("admin/article/index");
        return $this->setMenu($view);
    }

    //资讯删除
    public function delAction(){
        $this->checkLogin('admin_article_index');
        $id = $_POST['id'];
        $article = $this->getArticleTable();
        $article->id = $id;
        if($article->deleteData()){
            $this->ajaxReturn(0, '删除成功');
        }
        else{
            $this->ajaxReturn(10000, '删除失败');
        }
    }

    //资讯详情
    public function detailsAction(){
        $this->checkLogin('admin_article_index');
        $request = $this->getRequest();
        $id = $this->params('id');
        $ViewArticle = $this->getViewArticleTable();
        $ViewArticle->id = $id;
        $post = $request->getPost()->toArray();
        $info = $ViewArticle->getDetails();
        if($request->isPost()){
            $article = $this->getArticleTable();
            $article->id = $post['id'];
            //验证数据
            if(empty($post['title'])){
                $this->ajaxReturn(10000, '资讯标题不能为空');
            }
            if(empty($post['category_id'])){
                $this->ajaxReturn(10000, '资讯分类不能为空');
            }
            if(empty($post['image_id'])){
                $this->ajaxReturn(10000, '资讯封面不能为空');
            }
            if(empty($post['content'])){
                $this->ajaxReturn(10000, '资讯正文不能为空');
            }
            if(empty($post['abstract'])){
                $this->ajaxReturn(10000, '资讯概述不能为空');
            }

            //添加数据
            $article->title = $post['title'];
            $article->categoryId = $post['category_id'];
            $article->imageId = $post['image_id'];
            $article->content = $post['content'];
            $article->abstract = $post['abstract'];

            //保存
            if(!$article->updateData()){
                $this->ajaxReturn(10000, '编辑失败');
            }
            else{
                //跳转到导航列表页
                $this->ajaxReturn(0, '编辑成功', $this->url()->fromRoute('admin-article', ['action'=>'index']));
            }
        }

        $category = $this->getCategoryTable();
        $category->type = 3;
        $category->needPage = 2;
        $category_list = $category->getList();
        $data = [
            'category_list'=>$category_list['list'],
            'info' => $info
        ];
        $view = new ViewModel($data);
        $view->setTemplate("admin/article/details");
        return $this->setMenu($view);
    }

    //资讯详情
    public function articleAddAction(){
        $this->checkLogin('admin_article_index');
        $request = $this->getRequest();
        $post = $request->getPost()->toArray();
        if($request->isPost()){
            $article = $this->getArticleTable();
            //验证数据
            if(empty($post['title'])){
                $this->ajaxReturn(10000, '资讯标题不能为空');
            }
            if(empty($post['category_id'])){
                $this->ajaxReturn(10000, '资讯分类不能为空');
            }
            if(empty($post['image_id'])){
                $this->ajaxReturn(10000, '资讯封面不能为空');
            }
            if(empty($post['content'])){
                $this->ajaxReturn(10000, '资讯正文不能为空');
            }
            if(empty($post['abstract'])){
                $this->ajaxReturn(10000, '资讯概述不能为空');
            }

            //添加数据
            $article->title = $post['title'];
            $article->categoryId = $post['category_id'];
            $article->imageId = $post['image_id'];
            $article->content = $post['content'];
            $article->abstract = $post['abstract'];
            $article->adminId = $_SESSION['admin_id'];

            //保存
            if(!$article->addData()){
                $this->ajaxReturn(10000, '添加失败');
            }
            else{
                //跳转到导航列表页
                $this->ajaxReturn(0, '添加成功', $this->url()->fromRoute('admin-article', ['action'=>'index']));
            }
        }

        $category = $this->getCategoryTable();
        $category->type = 3;
        $category->needPage = 2;
        $category_list = $category->getList();
        $data = [
            'category_list'=>$category_list['list'],
        ];
        $view = new ViewModel($data);
        $view->setTemplate("admin/article/articleAdd");
        return $this->setMenu($view);
    }

    //资讯分类列表
    public function articleCategoryListAction()
    {
        $this->checkLogin('admin_article_categoryList');
        $ViewCategory = $this->getViewCategoryTable();
        $ViewCategory->type = 3;//视频分类
        $ViewCategory->orderBy = 'sort desc';
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
        $view->setTemplate("admin/article/articleCategoryList");
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

    //改变资讯分类状态
    public function changeCategoryStatusAction(){
        $id = $_POST['id'];
        $status = $_POST['status'];
        $category = $this->getCategoryTable();
        $category->id = $id;
        $category->status = $status;
        $res = $category->updateArticleCategoryStatus();
        if($res['s'] == 0){
            $this->ajaxReturn(0, $res['d']);
        }
        else{
            $this->ajaxReturn(10000, $res['d']);
        }
    }

    //资讯分类删除
    public function articleCategoryDelAction(){
        $id = $_POST['id'];
        $category = $this->getCategoryTable();
        $category->id = $id;
        $res = $category->articleCategoryDel();
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
        $category->type = 3;
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
