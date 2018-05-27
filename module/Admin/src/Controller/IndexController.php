<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\View\Model\ViewModel;

class IndexController extends CommonController
{
    /**
     * 前端接收表单文件域传过来文件
     * 用于上传文件处理
     * 4:3
     * @return string 用于模板页面JS处理
     */
    public function getAdminFileAction()
    {
        if (isset($_FILES) && $_FILES['Filedata']['error'] == 0 && $this->check_file_type($_FILES['Filedata']['tmp_name']))
        {
            $file = $this->Uploadfile();
            $path = ROOT_PATH . 'uploadfiles/' . $file['path'] . $file['filename'];
            $image_model = $this->getImageTable();
            foreach ($file as $key=>$val) {
                $image_model->$key = $val;
            }

            $image_id = $image_model->addData();

            if (! $file)
            {
                $error = '上传失败，未知错误！';
            }
            else
            {
                $error = '';
            }
            echo json_encode(['path'=>$path, 'image_id'=>$image_id]);
            die();
        }
        else
        {
            $error = '文件类型不正确，或未选择上传图片！';
            $path = '';
            $image_id = '';
            echo json_encode(['path'=>$path, 'image_id'=>$image_id]);
            die();
        }
        die();
    }

    public function indexAction()
    {
//        $this->checkLogin('admin_index_index');
        $view = new ViewModel();
        $view->setTemplate("admin/index/index");
        return $this->setMenu($view);
    }

    /**
     * 管理员登录
     */
    public function loginAction()
    {
        if($_POST){
            if(isset($_POST['checkCode'])){
                $code = isset($_SESSION['captcha']) ? $_SESSION['captcha'] : '';
                if(strtolower($_POST['checkCode']) != $code){
                    $this->ajaxReturn(0,'图形验证码错误！');
                }
            }
            unset($_SESSION['captcha']);
            if(!$_POST['name'] || !$_POST['password']){
                $this->ajaxReturn(0,'请输入帐号和密码！');
            }

            $admin = $this->getAdminTable();
            $admin->name = $_POST['name'];
            $admin->password = md5($_POST['password']);
            $res = $admin->adminLogin();
            if($res['s'] === 0){
                if(isset($_POST['remember'])){
                    setcookie("admin_name",$_POST['name'],time()+7200);
                }else{
                    setcookie("admin_name",'');
                }
                $action_list = $_SESSION['action_list'];
                $url = $this->url()->fromRoute('admin');
                $this->ajaxReturn(1,'登录成功！',$url);
            }else{
                if(isset($_SESSION['login_error_time'])){
                    $_SESSION['login_error_time']++;
                }else{
                    $_SESSION['login_error_time'] = 1;
                }
                $this->ajaxReturn(0,$res['d']);
            }
        }
        $error_time = array_key_exists('login_error_time',$_SESSION)?$_SESSION['login_error_time']:'';
        $checkCode = $this->generateCaptchaAction();
        $adminName = '';
        if(isset($_COOKIE['admin_name'])){
            $adminName = $_COOKIE['admin_name'];
        }
        $this->layout('layout/blank');
        $view = new ViewModel(['loginErrorTime'=>$error_time,'adminName'=>$adminName,'checkCode'=>$checkCode,'adminName'=>$adminName]);
        $view->setTemplate("admin/index/login");
        return $view;
    }

    //退出登录
    public function logoutAction(){
        $this->quit();
    }

    //点击切换验证码图片
    public function getChechCodeAction(){
        $checkCode = $this->generateCaptchaAction();
        echo json_encode($checkCode);
        exit;
    }

    //修改密码
    public function changePasswordAction()
    {
        $this->checkLogin('admin_setting_changePassword');
        if ($_POST) {
            if (!$_POST['oldpassword']) {
                $this->ajaxReturn(0, '请输入旧密码！');
            }
            if (!$_POST['password']) {
                $this->ajaxReturn(0, '请输入新密码！');
            }
            if (!$_POST['repassword']) {
                $this->ajaxReturn(0, '请输入确认密码！');
            }
            if ($_POST['password'] != $_POST['repassword']) {
                $this->ajaxReturn(0, '两次密码输入不一致！');
            }
            $adminId = $_SESSION['admin_id'];
            $admin = $this->getAdminTable();
            $admin->id = $adminId;
            $adminInfo = $admin->getDetails();
            if (md5($_POST['oldpassword']) != $adminInfo->password) {
                $this->ajaxReturn(0, '旧密码输入错误！');
            }
            $admin->password = $_POST['password'];
            if ($admin->updateData()) {
                $url = $this->url()->fromRoute('admin', ['action' => 'login']);
                session_destroy();
                $this->ajaxReturn(1, '修改成功，请重新登录！', $url);
            } else {
                $this->ajaxReturn(0, '修改密码失败！');
            }

        }
        $view = new ViewModel();
        $view->setTemplate("admin/index/changePassword");
        return $this->setMenu($view);
    }

    //密码找回
    private function passRetrievalAction(){
        if($_POST){
            $code = $_SESSION['captcha'];
            if(strtolower($_POST['checkCode']) != $code){
                $this->ajaxReturn(0,'验证码错误！');
            }
            unset($_SESSION['captcha']);
            $smscode = $this->getSmsCodeTable();
            $smscode->type = 7;
            $smscode->mobile = $_POST['mobile'];
            $smscode->code = $_POST['mobileCode'];
            $msgCode = $smscode->smsCodeOperation(2);
            if($msgCode['s']!=0){
                $this->showMessage($msgCode['d']);
            }
            $smscode->id = $_POST['msgCodeId'];
            $smscode_info = $smscode->getDetails();
            if($_POST['mobileCode'] !== $smscode_info->code){
                $this->ajaxReturn(0,'手机验证码不正确！');
            }
            $admin = $this->getAdminTable();
            $admin->mobile = $_POST['mobile'];
            $res = $admin->getByMobile();
            if(!$res){
                $this->ajaxReturn(0,'用户不存在，检查手机号是否正确！！');
            }else if($res->status == 2){
                $this->ajaxReturn(0,'用户被禁用！');
            }
            if(empty($_POST['password']) || $_POST['password'] != $_POST['repassword']){
                $this->ajaxReturn(0,'两次密码输入不一致！');
            }
            $admin->id = $res->id;
            $admin->password = $_POST['password'];
            if($admin->updateData()){
                $url = $this->url()->fromRoute('platform-user', ['action'=>'login']);
                $this->ajaxReturn(1,'修改密码成功！',$url);
            }else{
                $this->ajaxReturn(0,'修改密码失败！');
            }
        }
        $checkCode = $this->generateCaptchaAction();
        $this->layout('layout/blank');
        $view = new ViewModel(['checkCode'=>$checkCode]);
        $view->setTemplate("admin/index/passRetrieval");
        return $view;
    }

}
