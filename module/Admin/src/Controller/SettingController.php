<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Admin\Controller\CommonController;
use Zend\View\Model\ViewModel;

class SettingController extends CommonController
{
    //管理员列表
    public function adminListAction()
    {
        $this->checkLogin('admin_setting_adminList');
        $page = $this->params("page",1);
        $admin = $this->getViewAdminTable();
        $admin->page =$page;
        $list = $admin->getList();
        $view = new ViewModel(['list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'adminList')]);
        $view->setTemplate("admin/setting/adminList");
        return $this->setMenu($view);
    }

    //删除管理员
    public function deleteAdminAction(){
        $this->checkLogin('admin_setting_adminList');
        $id = $_POST['id'];
        $admin = $this->getAdminTable();
        $admin->id = $id;
        $res = $admin->deleteData();
        if($res){
            die(json_encode(['s'=>0,'d'=>'操作成功']));
        }else{
            die(json_encode(['s'=>10000,'d'=>'删除失败']));
        }
    }

    //编辑管理员帐号状态
    public function banAdminAction(){
        $this->checkLogin('admin_setting_adminList');
        $id = $_POST['id'];
        $admin = $this->getAdminTable();
        $admin->id = $id;
        $info = $admin->getDetails();
        if($info['status'] == 1){
            $admin->status = 2;
        }else{
            $admin->status = 1;
        }
        $res = $admin->updateData();
        if($res){
            die(json_encode(['s'=>0,'d'=>'操作成功']));
        }else{
            die(json_encode(['s'=>10000,'d'=>'操作失败']));
        }
    }

    //职务列表
    public function adminCategoryAction()
    {
        $this->checkLogin('admin_setting_adminCategory');
        $page = $this->params("page",1);
        $category = $this->getAdminCategoryTable();
        $category->page =$page;
        $list = $category->getList();
        $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'index')));
        $view->setTemplate("admin/setting/adminCategory");
        return $this->setMenu($view);
    }

    //新增职务
    public function addRoleAction(){
        $this->checkLogin('admin_setting_adminCategory');
        $role = $this->getAdminCategoryTable();
        if($_POST){
            if(empty($_POST['name']) || $_POST['name'] == '超级管理员'){
                $this->ajaxReturn(10000,'职务名称不能为空或超级管理员！');
            }
            if(empty($_POST['actionLists'])){
                $this->ajaxReturn(10000,'权限菜单不能为空！');
            }
            $actionList = array_unique(explode('|',implode('|',$_POST['actionLists'])));
            $_POST['actionList'] =  implode('|',$actionList);
            foreach ($_POST as $k=> $v)
            {
                if(in_array($k,$role->getTableColumns()))
                {
                    $role->$k = $v;
                }
            }
            $res = $role->addData();
            if($res){
                $url = $this->url()->fromRoute('admin-setting',['action'=>'adminCategory']);
                $this->ajaxReturn(0,'添加成功！',$url);
            }else{
                $this->ajaxReturn(10000,'添加失败！');
            }
        }
        $action = $this->getModuleTable();
        $action->orderBy = 'id ASC';
        $actionList = $action->getListTree();
        $view = new ViewModel(['actionList'=>$actionList['list']]);
        $view->setTemplate("admin/setting/addRole");
        return $this->setMenu($view);
    }

    //查看职务详情
    public function viewRoleAction(){
        $this->checkLogin('admin_setting_adminCategory');
        $roleId = $this->params('id');
        $role = $this->getAdminCategoryTable();
        $role->id = $roleId;
        if($_POST){
            if(empty($_POST['name']) || $_POST['name'] == '超级管理员'){
                $this->ajaxReturn(10000,'职务名称不能为空或超级管理员！');
            }
            if(empty($_POST['actionLists'])){
                $this->ajaxReturn(10000,'权限菜单不能为空！');
            }
            $actionList = array_unique(explode('|',implode('|',$_POST['actionLists'])));
            $_POST['actionList'] =  implode('|',$actionList);
            foreach ($_POST as $k=> $v)
            {
                if(in_array($k,$role->getTableColumns()))
                {
                    $role->$k = $v;
                }
            }
            $res = $role->updateData();
            if($res){
                $url = $this->url()->fromRoute('admin-setting',['action'=>'adminCategory']);
                $this->ajaxReturn(0,'修改成功！',$url);
            }else{
                $this->ajaxReturn(10000,'修改失败！');
            }
        }
        $roleInfo = $role->getDetails();
        $action = $this->getModuleTable();
        $action->orderBy = 'id ASC';
        $actionList = $action->getListTree();
        $actionArr = explode('|',$roleInfo->action_list);
        $view = new ViewModel(['roleInfo'=>$roleInfo,'actionList'=>$actionList['list'],'actionArr'=>$actionArr]);
        $view->setTemplate("admin/setting/viewRole");
        return $this->setMenu($view);
    }
    
    //删除职务
    public function deleteRoleAction(){
        $this->checkLogin('admin_setting_adminCategory');
        $roleId = $_POST['id'];
        $admin = $this->getAdminTable();
        $admin->adminCategoryId = $roleId;
        $res = $admin->getByCategoryId(); //检查该职务下有无管理员
        if($res){
            die(json_encode(['s'=>10000,'d'=>'该职务下有管理员，无法删除！']));
        }
        $role = $this->getAdminCategoryTable();
        $role->id = $roleId;
        $res = $role->deleteData();
        if($res){
            die(json_encode(['s'=>0,'d'=>'操作成功']));
        }else{
            die(json_encode(['s'=>10000,'d'=>'职务删除失败']));
        }
        die();
    }

    //新增管理员
    public function addAdminAction(){
        $this->checkLogin('admin_setting_adminList');
        $admin = $this->getAdminTable();
        if($_POST){
            if(empty($_POST['realName'])){
                $this->ajaxReturn(10000,'姓名不能为空！');
            }
            if(empty($_POST['mobile'])){
                $this->ajaxReturn(10000,'手机号码不能为空！');
            }
            if(empty($_POST['name'])){
                $this->ajaxReturn(10000,'登录帐号不能为空！');
            }
            if(empty($_POST['password'])){
                $this->ajaxReturn(10000,'密码不能为空！');
            }
            if(empty($_POST['repassword'])){
                $this->ajaxReturn(10000,'确认密码不能为空！');
            }
            if(empty($_POST['adminCategoryId'])){
                $this->ajaxReturn(10000,'职务不能为空！');
            }
            if($_POST['password'] != $_POST['repassword']){
                $this->ajaxReturn(10000,'两次密码不一致！');
            }
            $admin->mobile = $_POST['mobile'];
            $admin->name = $_POST['name'];
            if($admin->queryName()){
                $this->ajaxReturn(10000,'登录帐号已存在！');
            }
            if($admin->queryMobile()){
                $this->ajaxReturn(10000,'手机号码已被绑定！');
            }
            foreach ($_POST as $k=> $v)
            {
                if(in_array($k,$admin->getTableColumns()))
                {
                    $admin->$k = $v;
                }
            }
            $res = $admin->addData();
            if($res){
                $url = $this->url()->fromRoute('admin-setting',['action'=>'adminList']);
                $this->ajaxReturn(0,'添加成功！',$url);
            }else{
                $this->ajaxReturn(10000,'添加失败！');
            }
        }
        $role = $this->getAdminCategoryTable();
        $roleList = $role->getList();
        $action = $this->getModuleTable();
        $action->orderBy = 'id ASC';
        $actionList = $action->getListTree();
        $view = new ViewModel(['roleList'=>$roleList['list'],'actionList'=>$actionList['list']]);
        $view->setTemplate("admin/setting/addAdmin");
        return $this->setMenu($view);
    }

    //编辑管理员
    public function viewAdminAction(){
        $this->checkLogin('admin_setting_adminList');
        $id = $this->params('id');
        $admin = $this->getAdminTable();
        $admin->id = $id;
        $adminInfo = $admin->getDetails();
        if($_POST){
            if(empty($_POST['realName'])){
                $this->ajaxReturn(10000,'姓名不能为空！');
            }
            if(empty($_POST['mobile'])){
                $this->ajaxReturn(10000,'手机号码不能为空！');
            }
            if(empty($_POST['name'])){
                $this->ajaxReturn(10000,'登录帐号不能为空！');
            }
            if(empty($_POST['adminCategoryId'])){
                $this->ajaxReturn(10000,'职务不能为空！');
            }
            $admin->mobile = $_POST['mobile'];
            $admin->name = $_POST['name'];
            if($admin->queryName()){
                $this->ajaxReturn(10000,'登录帐号已存在！');
            }
            if($admin->queryMobile()){
                $this->ajaxReturn(10000,'手机号码已被绑定！');
            }
            foreach ($_POST as $k=> $v)
            {
                if(in_array($k,$admin->getTableColumns()))
                {
                    $admin->$k = $v;
                }
            }
            $res = $admin->updateData();
            if($res){
                $url = $this->url()->fromRoute('admin-setting',['action'=>'adminList']);
                $this->ajaxReturn(0,'修改成功！',$url);
            }else{
                $this->ajaxReturn(10000,'修改失败！');
            }
        }
        $role = $this->getAdminCategoryTable();
        $roleList = $role->getList();
        $action = $this->getModuleTable();
        $action->orderBy = 'id ASC';
        $actionList = $action->getListTree();
        $view = new ViewModel(['adminInfo'=>$adminInfo,'roleList'=>$roleList['list'],'actionList'=>$actionList['list']]);
        $view->setTemplate("admin/setting/viewAdmin");
        return $this->setMenu($view);
    }


    //协议管理
    public function agreementAction()
    {
        $this->checkLogin('admin_setting_agreement');
        $setup = $this->getSetupTable();
        $setup->id = 1;
        if($_POST){
            $_POST['value'] = $_POST['content'];
            foreach ($_POST as $k=> $v)
            {
                if(in_array($k,$setup->getTableColumns()))
                {
                    $setup->$k = $v;
                }
            }
            $res = $setup->updateData();
            if($res){
                $url = $this->url()->fromRoute('admin-setting',['action'=>'agreement']);
                $this->ajaxReturn(0,'修改成功！',$url);
            }else{
                $this->ajaxReturn(10000,'修改失败！');
            }
        }
        $info = $setup->getDetails();
        $view = new ViewModel(['info'=>$info]);
        $view->setTemplate('admin/setting/agreement');
        return $this->setMenu($view);
    }

    //隐私协议
    public function privacyAgreementAction()
    {
        $this->checkLogin('admin_setting_privacyAgreement');
        $setup = $this->getSetupTable();
        $setup->id = 2;
        if($_POST){
            $_POST['value'] = $_POST['content'];
            foreach ($_POST as $k=> $v)
            {
                if(in_array($k,$setup->getTableColumns()))
                {
                    $setup->$k = $v;
                }
            }
            $res = $setup->updateData();
            if($res){
                $url = $this->url()->fromRoute('admin-setting',['action'=>'privacyAgreement']);
                $this->ajaxReturn(0,'修改成功！',$url);
            }else{
                $this->ajaxReturn(10000,'修改失败！');
            }
        }
        $info = $setup->getDetails();
        $view = new ViewModel(['info'=>$info]);
        $view->setTemplate('admin/setting/privacyAgreement');
        return $this->setMenu($view);
    }

    /*
     * 管理员页面获取职务权限
     * */
    public function getPermissionAction(){
        $id = $_POST['categoryId'];
        $category = $this->getAdminCategoryTable();
        $category->id = $id;
        $info = $category->getDetails();
        if($info->action_list){
            $actionArr = explode('|',$info->action_list);
            $module = $this->getModuleTable();
            foreach ($actionArr as $v){
                $module->actionCode = $v;
                $arr[] = $module->getOneByCode();
            }
            foreach ($arr as $v){
                if($v->parent_id){
                    $module->id = $v->parent_id;
                    $parent = $module->getDetails();
                    $arr2[$parent->name][] = $v;
                }
            }
            echo json_encode($arr2);
        }else{
            echo json_encode('all');
        }
        exit;
    }

    /**
     * 举报类型列表
     */
    public function reportCategoryListAction()
    {
        $this->checkLogin('admin_setting_reportCategoryList');
        $ViewCategory = $this->getViewCategoryTable();
        $ViewCategory->type = 4;//音频分类
        $ViewCategory->orderBy = 'sort desc';
        $get = [];
        $keyword = '';
        if($_GET){
            $get = $_GET;
            $ViewCategory->searchKeyWord = $get['keyword']??'';
            $keyword = $get['keyword']??'';
        }
        $list = $ViewCategory->getList();
        $data = [
            'list' => $list['list'],
            'keyword' => $keyword,
        ];

        $view = new ViewModel($data);
        $view->setTemplate("admin/setting/reportCategoryList");
        return $this->setMenu($view);
    }

    /**
     * @throws \Exception
     * 保存廣告排序
     */
    public function saveCategorySortAction()
    {
        $sort_array = $_POST['sortObject'];
        if(!is_array($sort_array) || !$sort_array)
        {
            $this->ajaxReturn(10000, '删除失败');
        }
        $category = $this->getCategoryTable();
        $res = $category->saveSort($sort_array);
        $this->ajaxReturn($res['s'],$res['d']);
    }

    //改变举报分类状态
    /*public function changeCategoryStatusAction(){
        $id = $_POST['id'];
        $status = $_POST['status'];
        $category = $this->getCategoryTable();
        $category->id = $id;
        $category->status = $status;
        $res = $category->updateAudioCategoryStatus();
        if($res['s'] == 0){
            $this->ajaxReturn(0, $res['d']);
        }
        else{
            $this->ajaxReturn(10000, $res['d']);
        }
    }*/

    //举报分类删除
    public function reportCategoryDelAction(){
        $id = $_POST['id'];
        $category = $this->getCategoryTable();
        $category->id = $id;
        if(!$category->deleteData())
        {
            $this->ajaxReturn(10000, '删除失败');
        }
        $this->ajaxReturn(0, '删除成功');
    }

    //编辑添加分类
    public function editCategoryAction(){
        $id = $_POST['id'];
        $name = $_POST['name'];
        $sort = $_POST['sort'];
        $category = $this->getCategoryTable();
        $category->name = $name;
        $category->sort = $sort;
        $category->type = 4;
        if($id)
        {
            $category->id = $id;
            $res = $category->updateData();
        }
        else
        {
            $res = $category->addData();
        }
        if($res === false)
        {
            $this->ajaxReturn(10000, '操作失败');
        }
        else
        {
            $this->ajaxReturn(0, '操作成功');
        }
    }
}
