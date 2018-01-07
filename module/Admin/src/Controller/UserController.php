<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\View\Model\ViewModel;

class UserController extends CommonController
{
    //用户列表
    public function indexAction()
    {
        $this->checkLogin('admin_user_index');
        $page = $this->params("page",1);
        $user = $this->getViewUserTable();
        $user->page =$page;
        $status = 0;
        $keyword = '';
        $type = 0;
        $get = [];
        if ($_GET) {
            $get = $_GET;
            $type = isset($get['type'])?$get['type']:0;
            $keyword = isset($get['keyword'])?$get['keyword']:'';
            $status = isset($get['status'])?$get['status']:0;
            $user->status = $status;
            $user->searchKeyWord = $keyword;
        }
        $statusArr = array(0=>'全部',1=>'正常',2=>'停用');
        $list = $user->getList($type);
        $view = new ViewModel(['list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>["action"=>'index'],'type'=>$type,'keyword'=>$keyword,'status'=>$status,'statusArr'=>$statusArr,'where'=>$get]);
        $view->setTemplate("admin/user/index");
        return $this->setMenu($view);
    }

    //修改用户状态
    public function changeStatusAction(){
        $this->checkLogin('admin_user_index');
        $userId = $_POST['id'];
        $user = $this->getUserTable();
        $user->id = $userId;
        $userInfo= $user->getDetails();
        if($userInfo['status'] == 1){
            $user->status = 2;
        }else{
            $user->status = 1;
        }
        $res = $user->updateStatus();
        if($res){
            die(json_encode(['s'=>0,'d'=>'操作成功']));
        }else{
            die(json_encode(['s'=>10000,'d'=>'操作失败']));
        }
    }

    //用户详细信息
    public function detailsAction()
    {
        $this->checkLogin('admin_user_index');
        $userId = $this->params('id');
        $user = $this->getViewUserTable();
        $user->id = $userId;
        $userInfo= $user->getDetails();
        $sexArr = array(1=>'男',2=>'女',3=>'保密');
        $userInfo['sex'] = $sexArr[$userInfo['sex']];
        if($userInfo['head_image_id']){
            $image = $this->getImageTable();
            $image->id = $userInfo['head_image_id'];
            $userImage = $image->getDetails();
        }

        $region = [];
        if($userInfo->region_info)
        {
            $region = json_decode($userInfo->region_info,true);
        }
        $view = new ViewModel(['userInfo'=>$userInfo,'region'=>$region]);
        $view->setTemplate("admin/user/details");
        return $this->setMenu($view);
    }
}
