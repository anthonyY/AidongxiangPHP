<?php
namespace Admin\Controller;

use Zend\Db\Sql\Where;
use Zend\View\Model\ViewModel;

class GroupMemberController extends CommonController
{
    
    /**
     * 集团会员
     * 
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $check = $this->checkLogin("GroupMember");

        $view = new ViewModel(array());
        $view->setTemplate('admin/groupMember/index');
        return $this->setMenu1($view);
    }
    
    function addExcelAction(){
//         $this->basePath();
        if(isset($_FILES["Filedata"]) && $_FILES["Filedata"]['error']==0){
            $path = APP_PATH .'/vendor/Core/System/phpExcel/';//内网不行
            $return = $this->getModel('GroupMember')->ajaxUpdateMeme($path,'B','user');
        }else{
            $this->ajax('请求参数错误');
        }
        echo json_encode($return);exit;
    }
//     /**
//      * 导入
//      *
//      * @version YSQ
//      */
//     public function setExeclAction()
//     {
//         echo '这是excel导入,没有开始';exit;
//         set_time_limit(0);
//         error_reporting(1);
//         $indexModel = $this->getModel('Index');
//         $excelpath = 'e6.xls';
//         $arr = $indexModel->setExecl($excelpath);
//         echo $arr;exit;
//     }
    
    
    /**
     * 下载excel
     * @version YSQ
     */
    public function getExcelAction(){
        $check = $this->checkLogin("GroupMember");
        if (! $check) {
            return false;
        }
        $data = $this->getModel('GroupMember')->getExcel2();
        $this->ajax($data['message']);
    }
    
    /**
     * 下载excel
     * @version YSQ
     */
    public function getExcel3Action(){
        $check = $this->checkLogin("GroupMember");
        if (! $check) {
            return false;
        }
        $page = $this->params()->fromRoute('page',1);//页数
        $key = array('keyword', 'status', 'start', 'end');
        $where = array();
        foreach ($key as $k) {
            $where[$k] = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
        }
        $condition = array(
            'controller' => 'GroupMember',
            'action' => 'index2',
            'where' => $where,
            'page' => $page,
        );
        $data = $this->getModel('GroupMember')->getExcel3($condition);
        $this->ajax($data['message']);
    }
    
    /**
     * 导入历史
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Admin\Controller\CommonController::index2Action()
     */
    public function index2Action()
    {
        $check = $this->checkLogin("GroupMember");
        if (! $check) {
            return false;
        }
        $page = $this->params()->fromRoute('page',1);//页数
        $key = array('keyword', 'status', 'start', 'end');
        $where = array();
        foreach ($key as $k) {
            $where[$k] = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
        }
        $condition = array(
            'controller' => 'GroupMember',
            'action' => 'index2',
            'where' => $where,
            'page' => $page,
        );
        $list = $this->getModel("GroupMember")->index($condition);
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => $condition,
//             'position' => $position_list? $position_list:array(),
        ));
        $view->setTemplate('admin/groupMember/index2');
        return $this->setMenu1($view);
    }
    
    /**
     * 用户详细页面
     * @return \Zend\View\Model\ViewModel
     * @version YSQ
     */
    public function detailsAction(){
        $this->checkLogin("GroupMember");
        $id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->showMessage('参数不完整');
        }
        $data = $this->getModel("GroupMember")->getUserDetails($id);
        if($data['code'] != 200){
            $this->showMessage($data['message']);
        }
        //         var_dump($list);exit;
        $view = new ViewModel(array(
            'data' => $data['info'],
        ));
        $view->setTemplate('admin/groupMember/details');
        return $this->setMenu1($view);
    }
    
//     /**
//      * 得到用户的课程列表
//      * @version YSQ
//      */
//     function getUserCourseListAction(){
//         $this->checkLogin("GroupMember");
//         $page = $this->params()->fromRoute('page',1);//页数
//         $where['type'] = isset($_REQUEST['type']) && $_REQUEST['type'] ? $_REQUEST['type'] :0;
//         $where['id'] = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] :0;
//         //         var_dump($where);exit;
//         $condition = array(
//             'controller' => 'GroupMember',
//             'action' => 'getUserCourseList',
//             'where' => $where,
//             'page' => $page,
//             'limit' => 0,
//         );
//         $list = $this->getModel("GroupMember")->getUserCourseList($condition);
//         $page_info = $this->getModel("GroupMember")->getPageSum($page,$list['total'],$condition['limit']);
//         $view = new ViewModel(array(
//             'list'=>$list['list'],
//             'page_info' => $page_info,
//             'page' => $page,
//             'where' => $where
//         ));
//         return $view;
//     }
}
