<?php
namespace Admin\Controller;

use Zend\Db\Sql\Where;
use Zend\View\Model\ViewModel;

class FeedbackController extends CommonController
{
    /**
     * 意见反馈列表
     * 
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $check = $this->checkLogin("Feedback");
//         if($check['code']!=200){
//             $this->showMessage($check['message']);
//         }
        $page = $this->params()->fromRoute('page', 1);
        $key = array('keyword','start','end','status');
        $where = array();
        foreach ($key as $k) {
            $where[$k] = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
        }
        $condition = array(
            'controller' => 'Feedback',
            'action' => 'index',
            'page' => $page,
            'where' => $where,
        );
        $list = $this->getModel('AdminFeedback')->getFeedbackList($condition);
//         $teacher_list = $this->getModel('AdminFeedback')->getTeacherList();
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => $condition,
        ));
        $view->setTemplate('admin/feedback/index');
        return $this->setMenu1($view);
    }
    
   
//     /**
//      * 评论详细页面
//      * @return \Zend\View\Model\ViewModel
//      * @version YSQ
//      */
//     public function detailsAction(){
//         $check = $this->checkLogin("Feedback");
//         $id = $this->params()->fromRoute('id', 0);
//         if (!$id) {
//             $this->showMessage('参数不完整');
//         }
//         if(isset($_POST['ajax']) && $_POST['ajax'] = 2017){
//             $return = $this->getModel("AdminFeedback")->editFeedback();
//             if ($return['code'] != 200){
//                 $this->showMessage($return['message']);
//             }
//             return $this->redirect()->toRoute('admin', array(
//                 'controller' => 'Feedback',
//                 'action' => 'index',
//             ));
//         }
//         $data = $this->getModel("AdminFeedback")->getFeedbackDetails($id);
//         if($data['code'] != 200){
//             $this->showMessage($data['message']);
//         }
//         //         var_dump($list);exit;
//         $view = new ViewModel(array(
//             'data' => $data['info'],
//         ));
//         $view->setTemplate('admin/feedback/details');
//         return $this->setMenu1($view);
//     }
    
   public function details2Action(){
        $check = $this->checkLogin("Feedback");
        $id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->showMessage('参数不完整');
        }
        if(isset($_POST['ajax']) && $_POST['ajax'] = 2017){
            $return = $this->getModel("AdminFeedback")->editFeedback();
            if ($return['code'] != 200){
                $this->showMessage($return['message']);
            }
            return $this->redirect()->toRoute('admin', array(
                'controller' => 'Feedback',
                'action' => 'index',
            ));
        }
        $data = $this->getModel("AdminFeedback")->getFeedbackDetails($id);
        $this->ajax($data);
   }
    
    /**
     * 得到评论的回复列表
     * @version YSQ
     */
    function getCommentReplyListAction(){
        $check = $this->checkLogin("Feedback");
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
        $list = $this->getModel("AdminFeedback")->getCommentReplyList($condition);
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
