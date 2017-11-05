<?php
namespace Admin\Controller;
use Zend\View\Model\ViewModel;
class UserController extends CommonController{
 
    public function indexAction()
    {
        $this->checkLogin("User");
        $page = $this->params()->fromRoute('page',1);//页数
        $key = array('keyword', 'status', 'cid', 'sex');
        $where = array();
        foreach ($key as $k) {
            $where[$k] = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
        }
        $condition = array(
            'controller' => 'User',
            'action' => 'index',
            'where' => $where,
            'page' => $page,
        );
        $list = $this->getModel("AdminUser")->index($condition);
        $position_list = $this->getModel('AdminUser')->getPositionList();
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => $condition,
            'position' => $position_list? $position_list:array(),
        ));
        $view->setTemplate('admin/user/index');
        return $this->setMenu1($view);
    }
    
    /**
     * 冻结/启用(ajax)
     * @return \Zend\View\Model\ViewModel
     * @version YSQ
     */
    public function ajaxDeleteAction()
    {
        $this->checkLogin("User");
        $params = array(
            'id' =>  isset($_REQUEST['id']) ? (int) $_REQUEST['id']  : 0,
            'status' => isset($_POST['status']) ? (int) $_POST['status'] : 1,
        );
        if(!$params['id'] || !in_array($params['status'], array(1,2))){
            $this->showMessage('参数不正确！');
        }
        $list = $this->getModel("AdminUser")->setUserDelete($params);
        $this->ajax($list);
    }
    
    /**
     * 用户详细页面
     * @return \Zend\View\Model\ViewModel
     * @version YSQ
     */
    public function detailsAction(){
        $this->checkLogin("User");
        $id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->showMessage('参数不完整');
        }
        $data = $this->getModel("AdminUser")->getUserDetails($id);
        if($data['code'] != 200){
            $this->showMessage($data['message']);
        }
//         var_dump($list);exit;
        $view = new ViewModel(array(
            'data' => $data['info'],
        ));
        $view->setTemplate('admin/user/details');
        return $this->setMenu1($view);
    }
    
    /**
     * 得到用户的课程列表
     * @version YSQ
     */
    function getUserCourseListAction(){
        $this->checkLogin("User");
        $page = $this->params()->fromRoute('page',1);//页数
        $where['type'] = isset($_REQUEST['type']) && $_REQUEST['type'] ? $_REQUEST['type'] :0;
        $where['id'] = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] :0;
        $condition = array(
            'controller' => 'User',
            'action' => 'getUserCourseList',
            'where' => $where,
            'page' => $page,
            'limit' => 0,
        );
        $list = $this->getModel("AdminUser")->getUserCourseList($condition);
        $page_info = $this->getModel("AdminUser")->getPageSum($page,$list['total'],$condition['limit']);
        $view = new ViewModel(array(
            'list'=>$list['list'],
            'page_info' => $page_info,
            'page' => $page,
            'where' => $where
        ));
        return $view;
    }
    
//     /**
//      * 修改用户专家头衔页面(post)
//      * @version YSQ
//      */
//     public function amendAction()
//     {
//         $this->checkLogin("User");
//         $id =  $this->params()->fromRoute('id') ? (int)$this->params()->fromRoute('id') : 0;
//         $type =  $this->params()->fromRoute('types') ? (int)$this->params()->fromRoute('types') : 0;
//         $keyword =  $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
//         $list = $this->getModel("AdminUser")->amendUserInfo($id,$type,$keyword);
//         if($list['code']==200){
//             return $this->redirect()->toRoute('admin',array('controller'=>'User','action'=>'index'));
//         }
//         $this->showMessage($list['message']);
//     }
    
//     /**
//      * 新增用户页面(post)
//      * @version YSQ
//      */
//     public function addAction()
//     {
//         $this->checkLogin("User");
//         if(isset($_POST) && $_POST){
//             $_POST['jjb'] = '2321';
//             $list = $this->getModel("AdminUser")->addUserInfo();
//             if($list['code']==200){
//                 return $this->redirect()->toRoute('admin',array('controller'=>'User','action'=>'index'));
//             }
//             $this->showMessage($list['message']);
//         }
//         $list = $this->getModel("AdminUser")->getSettingItemUserType();
//         //         $setting_item_list = $this->getSettingItemUserType();
//         $view = new ViewModel(array( 'list' => $list));
//         $view -> setTemplate('admin/user/add');
//         return $this->setMenu1($view);
//     }
    
    /**
     * 下载excel
     * @version YSQ
     */
    public function getExcelAction(){
        $check = $this->checkLogin("User");
        if (! $check) {
            return false;
        }
        $key = array('keyword', 'status', 'cid', 'sex');
        $where = array();
        foreach ($key as $k) {
            $where[$k] = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
        }
        $condition = array(
            'where' => $where,
            'page' => 1,
        );
        $data = $this->getModel('AdminUser')->setExcel($condition);
        $this->ajax($data['message']);
    }
    
    /**
     * 图片上传
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Admin\Controller\CommonController::uploadImageAction()
     */
    public function uploadImageAction()
    {
        $list = $this->getModel("Other")->phoneLoad();
        $this->ajax($list);
    }
    
}
?>