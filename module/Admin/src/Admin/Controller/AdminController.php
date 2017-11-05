<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;

class AdminController extends CommonController
{
    //protected $table = "mv_admin";

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
       if(isset($_POST['submit']) && $_POST['submit'])
       {
           $list= $this->getModel("Admin")->index($_POST);
           if($list['code'] == 200)
           {
               return $this->redirect()->toRoute('admin', array('controller' => 'Index','action' => 'index'));
           }
           if($list['code'] == 400)
           {
                $this->showMessage("{$list['message']}");
           }
       }
       
       $view = new ViewModel(array(

       ));
       $view->setTemplate('admin/admin/login');
       return $view;
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

    /**
     * 图片上传
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Admin\Controller\CommonController::uploadImageAction()
     */
    public function image1Action()
    {
//         echo 1;exit;
        $list = $this->getModel("Admin")->mobileUpload();
        $this->ajax($list);
    }

}
