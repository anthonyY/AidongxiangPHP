<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Admin\Controller\CommonController;
use Zend\View\Model\ViewModel;

class MicroblogController extends CommonController
{
    //微博列表
    public function indexAction()
    {
        $this->checkLogin('admin_microblog_index');
        $page = $this->params("page");
        $Microblog = $this->getViewMicroblogTable();
        $Microblog->page = $page;

        $get = [];
        $display = 0;
        $keyword = '';
        $search_type = 1;
        if($_GET){
            $get = $_GET;
            $Microblog->searchKeyWord = $get['keyword']??'';
            $Microblog->display = $get['display']??0;
            $keyword = $get['keyword']??'';
            $display = $get['display']??0;
            $search_type = $get['search_type']??1;
        }
        $search_key = $search_type!=1?['nick_name']:['content'];
        $list = $Microblog->getList($search_key);

        $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'index'), 'where'=>$get,'keyword'=>$keyword,'display'=>$display,'search_type'=>$search_type));
        $view->setTemplate("admin/microblog/index");
        return $this->setMenu($view);
    }

    //微博删除
    public function microblogDelAction(){
        $this->checkLogin('admin_microblog_index');
        $id = $_POST['id'];
        $microblog = $this->getMicroblogTable();
        $microblog->id = $id;
        if($microblog->deleteData()){
            $this->ajaxReturn(0, '删除成功');
        }
        else{
            $this->ajaxReturn(10000, '删除失败');
        }
    }

    //改变微博显示状态
    public function changeDisplayAction(){
        $this->checkLogin('admin_microblog_index');
        $id = $_POST['id'];
        $display = $_POST['display'];
        $microblog = $this->getMicroblogTable();
        $microblog->id = $id;
        $microblog->display = $display;
        if($microblog->updateData()){
            $this->ajaxReturn(0, '操作成功');
        }
        else{
            $this->ajaxReturn(10000, '操作失败');
        }
    }
}
