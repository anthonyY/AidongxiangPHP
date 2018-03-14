<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\View\Model\ViewModel;

class CommentController extends CommonController
{
    //评论列表
    public function indexAction()
    {
        $this->checkLogin('admin_comment_index');
        $page = $this->params("page");
        $ViewComment = $this->getViewCommentTable();
        $ViewComment->page = $page;

        $get = [];
        $keyword = '';
        $display = 0;
        $type = 0;
        $search_type = 1;
        if($_GET){
            $get = $_GET;
            $ViewComment->searchKeyWord = $get['keyword']??'';
            $ViewComment->display = $get['display']??0;
            $ViewComment->type = $get['type']??0;
            $type = $get['type']??0;
            $display = $get['display']??0;
            $keyword = $get['keyword']??'';
            $search_type = $get['search_type']??1;
        }
        $search_key = $search_type==1?['content']:['nick_name'];

        $list = $ViewComment->getList($search_key);

        $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'index'), 'where'=>$get,'keyword'=>$keyword,'type'=>$type,'display'=>$display,'search_type'=>$search_type));
        $view->setTemplate("admin/comment/index");
        return $this->setMenu($view);
    }

    //评论删除
    public function delAction(){
        $this->checkLogin('admin_comment_index');
        $id = $_POST['id'];
        $comment = $this->getCommentTable();
        $comment->id = $id;
        $res = $comment->delComment();
        $this->ajaxReturn($res['s'], $res['d']);
    }

    //改变评论显示状态
    public function changeDisplayAction(){
        $this->checkLogin('admin_comment_index');
        $id = $_POST['id'];
        $display = $_POST['display'];
        $comment = $this->getCommentTable();
        $comment->id = $id;
        $comment->display = $display;
        $res = $comment->changeDisplay();
        $this->ajaxReturn($res['s'], $res['d']);
    }

}
