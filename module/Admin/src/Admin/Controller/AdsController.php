<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;

class AdsController extends CommonController
{
    public function relationAction()
    {
        $this->checkLogin("Ads-relation");
        
        $ads = $this->getModel('AdminAds');
        $page = $this->params()->fromRoute('page', 1);
        
        $where = array();
        $key = array('plate','status');
        foreach ($key as $k) {
            $where[$k] = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
        }
        
        $condition = array(
            'controller' => 'ads',
            'action' => 'relation',
            'page' => $page,
            'where' => $where,
        );
        $typeArray = 
        $list = $ads->getRelationList($condition);
        $adsTime = $this->getModel('AdminAds')->getOne(array('type'=>3,'delete'=>0),array('content'),'setup')['content'];
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => $condition,
            'jump' => $this->jumpType(),
            'adsTime' => $adsTime
        ));
        $view->setTemplate('admin/ads/relation');
        return $this->setMenu2($view);
    }
    
    /**
     * 今日推荐
     * 2017.06.13
     * LZW
     */
    public function todayAdsAction()
    {
        $this->checkLogin("Ads-todayAds");
        
        $ads = $this->getModel('AdminAds');
        $page = $this->params()->fromRoute('page', 1);
        
        $where = array();
        $key = array('status');
        foreach ($key as $k) {
            $where[$k] = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
        }
        
        $condition = array(
            'controller' => 'ads',
            'action' => 'IndexAds',
            'page' => $page,
            'where' => $where,
        );
        $list = $ads->getTodayAdsList($condition);
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => $condition,
            'jump' => $this->jumpType(),
        ));
        $view->setTemplate('admin/ads/today-ads');
        return $this->setMenu2($view);
    }
    
    public function relationDetailsAction() {
        $this->checkLogin("Ads-relation");
        
        $id = $this->params()->fromRoute('id');
        $add = $this->params()->fromRoute('add');
		$cid = $this->params()->fromRoute('cid',1);
        $info = false;
        $model = $this->getModel('AdminAds');
        if ($id) {
            $info = $model->getOne(array('id' => $id), null, 'ads');
        }
        if(isset($_POST['ajax']) && $_POST['ajax'] == 2017){
            $add = isset($_POST['add']) ?  $_POST['add'] : 0;
            $id =  isset($_POST['id']) ?  $_POST['id'] : 0;
            if(!$id){
                 $return = $model->addRelationInfo($_POST);
            }else{
                $return = $model->editRelationInfo($_POST);
            }
            if ($return['status'] !=0) {
                $this->showMessage($return['msg']);
            } else {
                if(isset($_POST['plate']) && $_POST['plate'] == 3)
                {
                    return $this->redirect()->toRoute('admin', array(
                        'controller' => 'Ads',
                        'action' => 'todayAds',
                    ));
                }
                else 
                {
                    if(!$add){
                        return $this->redirect()->toRoute('admin', array(
                            'controller' => 'Ads',
                            'action' => 'relation',
                        ));
                    }else{
                        return $this->redirect()->toRoute('admin', array(
                            'controller' => 'Manage',
                            'action' => 'contentList',
                            'types' => 5,
                        ));
                    }
                }
            }
        }

        $view = new ViewModel(array(
            'jump' => $this->jumpType(),
            'add' => $add,
            'info' =>$info,
			'cid' => $cid,
        ));
        $view->setTemplate('admin/ads/relation-details');
        if($add){
            return $this->setMenu1($view);
        }
        return $this->setMenu2($view);
    }
    
    /**
     * 异步查找音频，视频，音频包，视频包的列表
     * @version YSQ
     */
    function ajaxFindListAction(){
        $this->checkLogin("Ads-relation");
        if(isset($_POST['ajax']) && $_POST['ajax']==27){
            $return = $this->getModel('AdminAds')->getFindList();
            $this->ajax($return);
        }
        exit();
    }
    
    /**
     * 编辑广告
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     * @version YSQ
     */
    function saveRelationAction() {
         $this->checkLogin("Ads-relation");
    
        $model = $this->getModel('AdminAds');
        if(isset($_POST['ajax']) && $_POST['ajax'] == 2017){
//             var_dump($_POST);exit;
            $return = $model->editRelationInfo($_POST);
           if ($return['status']) {
                $this->showMessage($return['msg']);
            } else {
//                 return $this->redirect()->toRoute('admin', array(
//                     'controller' => 'Ads',
//                     'action' => 'relation',
// //                     'types' => 5,
//                 ));
                echo "<script type='text/javascript'>history.go(-2);</script>";
                die();
            }
        }
        $this->showMessage('未知错误!');
        //return $this->redirect()->toRoute('admin', array('controller' => 'ads', 'action' => 'relation'));
    }
    
    public function updateRelationAjaxAction() {
        $this->checkLogin("Ads-relation");
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id']  : 0;
        $set = array(
            'status' => isset($_POST['status']) ? (int) $_POST['status'] : 1,
        );
        $ads = $this->getModel('AdminAds');
        $mes = $ads->updateRelation($id, $set);
        $this->ajax($mes);
    }
    
    public function deleteRelationAjaxAction() {
        $this->checkLogin("Ads-relation");
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id']  : 0;
        $set = array(
            'delete' => DELETE_TRUE,
        );
        $ads = $this->getModel('AdminAds');
        $mes = $ads->updateRelation($id, $set);
        $this->ajax($mes);
    }
    
    /**
     * 设置轮播图时长
     */
    public function setAdsTimeAction(){
        if ($_POST){
            $time = $_POST['time'];
            $adsTime = $this->getModel('AdminAds')->getOne(array('type'=>3,'delete'=>0),array('*'),'setup');
            if ($adsTime){
                $this->getModel('AdminAds')->updateData(array('content'=>$time), array('id'=>$adsTime['id'],'type'=>3,'delete'=>0),'setup');
            }else {
                $this->getModel('AdminAds')->insertData(array('type'=>3,'content'=>$time),'setup');
            }
            return $this->redirect()->toRoute('admin', array(
                'controller' => 'Ads',
                'action' => 'relation',
            ));
        }
    }
    
//     public function materialAction() {
//         $this->checkLogin('AdsMaterial');
        
//         $ads = $this->getModel('AdminAds');
//         $page = $this->params()->fromRoute('page', 1);
        
//         $where = array();
        
//         $condition = array(
//             'controller' => 'ads',
//             'action' => 'material',
//             'page' => $page,
//             'where' => $where,
//         );
        
//         $list = $ads->getMaterialList($condition);
//         $data = array(
//             'list'=> $list['list'],
//             'paginator' => $list['paginator'],
//             'condition' => $condition,
//         );
//         return $this->setMenu($data);
//     }
    
//     public function materialDetailsAction() {
//         $this->checkLogin('AdsMaterial');
        
//         $id = $this->params()->fromRoute('id');
//         $info = false;
//         if ($id) {
//             $model = $this->getModel('AdminAds');
//             $info = $model->getOne(array('id' => $id), null, 'ads_material');            
//         }
//         $data = array(
//             'info' => $info
//         );
//         return $this->setMenu($data);
//     }
    
//     function saveMaterialAction() {
//         $this->checkLogin('AdsMaterial');
        
//         $model = $this->getModel('AdminAds');
//         $model->saveAdsMaterial($_POST);
        
//         return $this->redirect()->toRoute('admin', array('controller' => 'ads', 'action' => 'material'));
//     }
    
//     public function deleteMaterialAjaxAction() {
//         $this->checkLogin('AdsMaterial');
        
//         $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id']  : 0;
//         $set = array(
//             'delete' => DELETE_TRUE,
//         );
//         $ads = $this->getModel('AdminAds');
//         $mes = $ads->updateMaterial($id, $set);
//         $this->ajax($mes);
//     }
}
