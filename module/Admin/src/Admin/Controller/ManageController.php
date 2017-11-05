<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;
/**
 * 系统管理模块
 * 
 * */
class ManageController extends CommonController
{
    //protected $table = "mv_admin";

    /**
     * 系统设置
     * @return ViewModel
     */
    public function indexAction()
    {
//        $this->checkLogin("Manage-index");
       $this->checkLogin(array('ContentList-videoList', 'ContentList-videoList2','ContentList-coursesList','ContentList-teacherList','Ads-relation','System-sendWord','ContentList-share'));
       $view = new ViewModel();
       $view->setTemplate('admin/manage/index');
       return $this->setMenu1($view);
    }
    
    /**
     * 内容列表
     * @return ViewModel
     */
    public function contentListAction()
    {
//         $this->checkLogin("Manage-index");
        $this->checkLogin(array('System-userHelp', 'System-userJob', 'System-system', 'System-userHelp2', 'System-category', 'System-setMember','System-index','System-setTopUp','System-protocol','System-role','System-roleList',));
        $id = $this->params()->fromRoute('types',1);//打开那个页面
//         var_dump($id);exit;
        $view = new ViewModel(array('id'=>$id));
        $view->setTemplate('admin/manage/contentList');
        return $this->setMenu1($view);
    }

    /**
     * logout
     */
    public function logoutAction()
    {
        $this->quit();
    }
    /**
     * 修改密码
     */
    public function changeAction()
    {
        $list = $this->getModel("Admin")->change($_POST);
        $this->ajax($list);
    }
    
    /**
     * 管理员列表
     * */
    
    public function adminListAction()
    {
        $list = $this->getModel("Admin")->change($_POST);
        $this->ajax($list);
    }
    

}
