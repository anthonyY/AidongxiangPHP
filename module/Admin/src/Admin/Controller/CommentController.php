<?php
namespace Admin\Controller;

use Zend\Db\Sql\Where;
use Zend\View\Model\ViewModel;

class CommentController extends CommonController
{
    
//     function __construct(){
//          $this->controller = 'comment';
//     }
    /**
     * 评论列表
     * 
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $check = $this->checkLogin("Comment");
//         if($check['code']!=200){
//             $this->showMessage($check['message']);
//         }
        $page = $this->params()->fromRoute('page', 1);
        $key = array('keyword','teacher_id','type','id');
        $where = array();
        foreach ($key as $k) {
            $where[$k] = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
        }
        
        $condition = array(
            'controller' => 'Comment',
            'action' => 'index',
            'page' => $page,
            'where' => $where,
        );
        $list = $this->getModel('AdminComment')->getReviewList($condition);
//         var_dump($list['list']);exit;

        $teacher_list = $this->getModel('AdminComment')->getTeacherList();
        //今天评论数,今天评论用户数,今天官方回复数
          $today_count = $this->getModel('AdminComment')->getTodayCommentCount();
        
        //本月评论数
        $thisMonth_count = $this->getModel('AdminComment')->getThisMonthCommentCount();
        //本月官方回复
        
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => $condition,
            'teacher' => $teacher_list,
            'today' => $today_count,
            'month' => $thisMonth_count,
        ));
        $view->setTemplate('admin/comment/index');
//         if($where['id']){
//             return $this->setMenu2($view);
//         }else{
            return $this->setMenu1($view);
//         }
    }
    
    /**
     * 隐藏/显示(ajax)
     * @return \Zend\View\Model\ViewModel
     * @version YSQ
     */
    public function ajaxDeleteAction()
    {
        $check = $this->checkLogin("Comment");
        $params = array(
            'id' =>  isset($_REQUEST['id']) ? (int) $_REQUEST['id']  : 0,
            'status' => isset($_POST['status']) ? (int) $_POST['status'] : 0,
        );
        if(!$params['id'] || !in_array($params['status'], array(1,0))){
            $this->showMessage('参数不正确！');
        }
        $list = $this->getModel("AdminComment")->setCommentDelete($params);
        $this->ajax($list);
    }
    
    /**
     * 取消置顶/置顶(ajax)
     * @return \Zend\View\Model\ViewModel
     * @version YSQ
     */
    public function ajaxTopAction()
    {
        $check = $this->checkLogin("Comment");
        $params = array(
            'id' =>  isset($_REQUEST['id']) ? (int) $_REQUEST['id']  : 0,
            'status' => isset($_POST['status']) ? (int) $_POST['status'] : 1,
        );
        if(!$params['id'] || !in_array($params['status'], array(1,2))){
            $this->showMessage('参数不正确！');
        }
        $list = $this->getModel("AdminComment")->setCommentTop($params);
        $this->ajax($list);
    }
    

    /**
     * 评论详细页面
     * @return \Zend\View\Model\ViewModel
     * @version YSQ
     */
    public function detailsAction(){
        $check = $this->checkLogin("Comment");
        $id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->showMessage('参数不完整');
        }
        if(isset($_POST['ajax']) && $_POST['ajax'] = 2017){
            $return = $this->getModel("AdminComment")->editComment();
            if ($return['code'] != 200){
                $this->showMessage($return['message']);
            }
//             return $this->redirect()->toRoute('admin', array(
//                 'controller' => 'Comment',
//                 'action' => 'index',
//             ));
            echo "<script type='text/javascript'>history.go(-2);</script>";
            die();
        }
        $data = $this->getModel("AdminComment")->getCommentDetails($id);
        if($data['code'] != 200){
            $this->showMessage($data['message']);
        }
        //         var_dump($list);exit;
        $view = new ViewModel(array(
            'data' => $data['info'],
        ));
        $view->setTemplate('admin/comment/details');
        return $this->setMenu1($view);
    }
    
   public function details2Action(){
        $check = $this->checkLogin("Comment");
        $id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->showMessage('参数不完整');
        }
        $data = $this->getModel("AdminComment")->getCommentDetails($id);
        $this->ajax($data);
   }
    
    /**
     * 得到评论的回复列表
     * @version YSQ
     */
    function getCommentReplyListAction(){
        $check = $this->checkLogin("Comment");
        $page = $this->params()->fromRoute('page',1);//页数
        $where['type'] = isset($_REQUEST['type']) && $_REQUEST['type'] ? $_REQUEST['type'] :0;
        $where['id'] = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] :0;
        //         var_dump($where);exit;
        $condition = array(
            'controller' => 'User',
            'action' => 'getCommentReplyList',
            'where' => $where,
            'page' => $page,
            'limit' => 0,
        );
        $list = $this->getModel("AdminComment")->getCommentReplyList($condition);
        $page_info = $this->getModel("AdminUser")->getPageSum($page,$list['total'],$condition['limit']);
        $view = new ViewModel(array(
            'list'=>$list['list'],
            //             'paginator'=> $list['paginator'],
        //             'condition' =>$condition,
            'page_info' => $page_info,
            'page' => $page,
            'where' => $where
        ));
        return $view;
    }
}
