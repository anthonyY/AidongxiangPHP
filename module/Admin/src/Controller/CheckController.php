<?php
    
    /**
     * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
     * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     */
    
    namespace Admin\Controller;
    use Admin\Controller\CommonController;
    use Zend\View\Model\ViewModel;
    
    class CheckController extends CommonController
    {
        //商品分类审核
        public function productCategoryAction()
        {
            $this->checkLogin('platform_check_productCategory');
            $page = isset($_GET['page'])?$_GET['page']:$this->params("page",1);
            $category = $this->getViewCategoryTable();
            $category->page =$page;
            $status = 0;
            $data = array();
            if ($_GET) {
                $data = $_GET;
                $status = $data['status'];
                $category->status = $status;
            }
            unset($data['page']);
            $category->type = 1;
            $category->status = $status;
            $list = $category->getList();
//            $where = array('status'=>$status,'page'=>$page);
            $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'productCategory','where'=>$data),'status'=>$status,'page'=>$page));
            $view->setTemplate("platform/check/productCategory");
            return $this->setMenu($view);
        }

        //商品审核通过/审核不通过
        public function productAuditAction(){
            $this->checkLogin('platform_check_productCategory');
            $category = $this->getCategoryTable();
//            $p = $this->params('page');
            $id = $_POST['id'];
            $status = $_POST['status'];
            $category->reason = '';
            if($status==2){
                $reason = $_POST['reason'];
                $category->reason = $reason;
            }
            $category->id = $id;
            if($status==1){
                $category->status = 2;
            }
            if($status==2){
                $category->status = 3;
            }
            if($category->updateData()){
                $records = $this->getNotificationRecordsTable();
                $records->userType = 2;
                $records->type = 5;//分类
                $records->merchantStatus = 1;//未读
                $records->fromId = $id;
                $info = $category->getDetails();
                $records->merchantId = $info['merchant_id'];
                $records->title = '商品分类审核';
                if($status==1){
                    $s = '审核通过';
                }else{
                    $s = '审核不通过,不通过原因：' . ($info->reason);
                }
                $content = $info->name  . '-' . ($s);
                $records->content = $content;
                if($records->addData()){
                    if($status==1){
//                    return $this->redirect()->toRoute('platform-check',['action'=>'productCategory','page'=>$p]);
                        echo 2;die();
                    }
                    if($status==2){
                        echo 1;die();
                    }
                }else{
                    $this->ajaxReturn(0,'服务器错误，请重试...');
                }
//                $url = $this->url()->fromRoute('platform-check',['action'=>'productCategory']);

            }else{
                $this->ajaxReturn(0,'服务器错误，请重试...');
            }
        }


        //服务分类审核
        public function serviceCategoryAction()
        {
            $this->checkLogin('plarform_chenk_serviceCategory');
            $page = isset($_GET['page'])?$_GET['page']:$this->params("page",1);
            $category = $this->getViewCategoryTable();
            $category->page =$page;
            $status = 0;
            $data = array();
            if ($_GET) {
                $data = $_GET;
                $status = $data['status'];
                $category->status = $status;
            }
            $category->type = 3;
            $category->status = $status;
            $list = $category->getList();
            unset($data['page']);
//            $where = array('status'=>$status,'page'=>$page);
            $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'serviceCategory','where'=>$data),'status'=>$status,'page'=>$page));
            $view->setTemplate("platform/check/serviceCategory");
            return $this->setMenu($view);
        }

        //服务审核通过/审核不通过
        public function serviceAuditAction(){
            $this->checkLogin('plarform_chenk_serviceCategory');
            $category = $this->getCategoryTable();
            $id = $_POST['id'];
//            $p = $this->params('page');
            $status = $_POST['status'];
            $category->reason = '';
            if($status==2){
                $reason = $_POST['reason'];
                $category->reason = $reason;
            }
            $category->id = $id;
            if($status==1){
                $category->status = 2;
            }
            if($status==2){
                $category->status = 3;
            }
            if($category->updateData()){
                $records = $this->getNotificationRecordsTable();
                $records->userType = 2;
                $records->type = 5;//分类
                $records->merchantStatus = 1;//未读
                $records->fromId = $id;
                $info = $category->getDetails();
                $records->merchantId = $info['merchant_id'];
                $records->title = '商品分类审核';
                if($status==1){
                    $s = '审核通过';
                }else{
                    $s = '审核不通过,不通过原因：' . ($info->reason);
                }
                $content = $info->name  . '-' . ($s);
                $records->content = $content;
                if($records->addData()){
                    //$url = $this->url()->fromRoute('platform-check',['action'=>'serviceCategory']);
                    if($status==1){
//                    return $this->redirect()->toRoute('platform-check',['action'=>'serviceCategory']);
                        echo 2;die();
                    }
                    if($status==2){
                        echo 1;die();
                    }
                }

            }else{
                $this->ajaxReturn(0,'服务器错误，请重试...');
            }
        }

        
        //活动审核
        public function activityAction()
        {
            $this->checkLogin('platform_check_activity');
            $page = isset($_GET['page'])?$_GET['page']:$this->params("page",1);
            $activity = $this->getViewMerchantActivityTable();
            $activity->page =$page;
            $status = 0;
            $data = array();
            if ($_GET) {
                $data = $_GET;
                $status = $data['status'];
                $activity->status = $status;
            }
            unset($data['page']);
            $list = $activity->getList();
//            $where = array('status'=>$status,'page'=>$page);
            $view = new ViewModel(array('list'=>$list['list'],'paginator'=>$list['paginator'],'condition'=>array("action"=>'activity','where'=>$data),'status'=>$status,'page'=>$page));
            $view->setTemplate("platform/check/activity");
            return $this->setMenu($view);
        }
        //审核通过/审核不通过
        public function auditAction(){
            $this->checkLogin('platform_check_activity');
            $id = $_POST['id'];
            //$p = $this->params('page');
//            $status = $this->params('status');
            $status = $_POST['status'];
            $activity = $this->getMerchantActivityTable();
            $activity->reason = '';
            if($status==2){
//                $id = $_POST['id'];
//                $status = $_POST['status'];
                $reason = $_POST['reason'];
                $activity->reason = $reason;
            }
            $activity->id = $id;
            if($status==1){
                $activity->status = 2;
            }
            if($status==2){
                $activity->status = 3;
            }
            if($activity->updateStatus()){
                $records = $this->getNotificationRecordsTable();
                $records->userType = 2;
                $records->type = 4;//活动
                $records->merchantStatus = 1;//未读
                $info = $activity->getDetails();
                $act = $this->getActivityTable();
                $act->setTableColumns(['name']);
                $act->id = $info->activity_id;
                $actInfo = $act->getDetails();
                $records->fromId = $info->activity_id;
                $records->merchantId = $info['merchant_id'];
                $records->title = '活动审核';
                if($status==1){
                    $s = '审核通过';
                }else{
                    $s = '-审核不通过,不通过原因：' . ($info->reason);
                }
                $content = $actInfo->name  . ($s);
                $records->content = $content;
                if($records->addData()){
                    //$url = $this->url()->fromRoute('platform-check',['action'=>'activity']);
                    if($status==1){
                        echo 2;die();
                    }
                    if($status==2){
                        echo 1;die();
                    }
                }
//
            }else{
                $this->ajaxReturn(0,'服务器错误，请重试...');
            }
        }

        //提现发送短信验证码
        public function sendMsgAction(){
            $smscode = $this->getSmsCodeTable();
            $smscode->type = 10;
            $smscode->mobile = $_POST['mobile'];
            $res = $smscode->smsCodeOperation(1);
            echo json_encode($res);
            die();
        }
        //门店加盟申请  rongxiyi
        public function leagueAction(){
            $page = isset($_GET['page'])?$_GET['page']:$this->params("page",1);
            $status = isset($_GET['status'])?$_GET['status']:$this->params()->fromRoute('status','');
            $MerchantApply =$this->getViewMerchantApplyTable();
            $MerchantApply->page=$page;
            $MerchantApply->status=$status;
            $list =$MerchantApply->getList();

            $view = new ViewModel(array(
                'paginator'=>$list['paginator'],
                'condition'=>array(
                    "action"=>'league',
                    'where'=>array(
                        'status'=>$status,
                    )),

                'list'=>$list['list'],
                'status'=>$status,
            ));
            $view->setTemplate("platform/check/league");
            return $this->setMenu($view);
        }

        //门店加盟审核  rongxiyi
        public function leagueCheckAction(){
            $this->checkLogin('plarform_chenk_league');
            if($_POST){
                $data = $_POST;
                $MerchantApply = $this->getMerchantApplyTable();
                $MerchantApply->id=$data['id'];
                $MerchantApplyInfo = $MerchantApply->getDetails();
                $MerchantApply->status=$data['status'];
                $MerchantApply->reason=$data['remark']?$data['remark']:'';
                $MerchantApply->updateData();
                $records = $this->getNotificationRecordsTable();
                $records->userType = 1;
                $records->type = 11;//门店加盟审核
                $records->status = 1;//未读
                $records->fromId = $data['id'];
                $records->title = '商家入驻申请审核通知';
                $records->userId=$MerchantApplyInfo->user_id;
                if($data['status']==2){
                    $s = '-尊敬的客户，您的商家入驻申请已通过平台的综合评审，我们将于7个工作日内与您联系入驻事项，请您保持电话畅通。';
                }else{
                    $s = "-尊敬的客户，很抱歉您的商家入驻申请因“{$data['remark']}”未通过平台的综合评审，请您核对资料重新提交。若有疑问可联系0753-2109666。" ;
                }
                $content = $MerchantApplyInfo->name  . ($s);
                $records->content = $content;
                $records->addData();
                $smscode = $this->getSmsCodeTable();
                $smscode->type = 10;
                $smscode->mobile = $MerchantApplyInfo->mobile;
//                $smscode->mobile = 15202021391;
                $res = $smscode->smsCodeOperation(1);
                $url = $this->url()->fromRoute('platform-check',['action'=>'league']);
                $this->ajaxReturn(1,$data['status']==2?'审核通过':'审核不通过！',$url);
            }
            $id = $this->params()->fromRoute('id');
            $MerchantApply =$this->getViewMerchantApplyTable();
            $MerchantApply->id=$id;
            $MerchantApplyInfo = $MerchantApply->getDetails();
            $region = $this->getRegionInfoArray($MerchantApplyInfo['community_id']);
            $regionInfo =json_decode($region['region_info'],true);
            $img = $this->getViewAlbumTable();
            $img->fromId = $id;
            $img->type = 5;
            $image_info = $img->getListWithSize();

            $regionInfos=isset($regionInfo[0]['region']['name'])?$regionInfo[0]['region']['name']:"";
            $regionInfos.=isset($regionInfo[1]['region']['name'])?$regionInfo[1]['region']['name']:"";
            $regionInfos.=isset($regionInfo[2]['region']['name'])?$regionInfo[2]['region']['name']:"";
            $regionInfos.=isset($regionInfo[3]['region']['name'])?$regionInfo[3]['region']['name']:"";
            $regionInfos.=isset($regionInfo[4]['region']['name'])?$regionInfo[4]['region']['name']:"";
            $view = new ViewModel(array(
                'id'=>$id,
                'MerchantApplyInfo'=>$MerchantApplyInfo,
                'regionInfo'=>$regionInfos,
                'image'=>$image_info
            ));
            $view->setTemplate("platform/check/edit");
            return $this->setMenu($view);
        }

        //权益保障申请  rongxiyi
        public function protectionAction(){
            $this->checkLogin('plarform_chenk_sprotection');
            if($_POST){
                $data =$_POST;
                $setup = $this->getConsumerRightsProtectionApplyTable();
                $setup->id=$data['id'];
                $setup->status=$data['status'];
                $setup->reason=$data['reason']?$data['reason']:'';
                $setup->updateData();

                $protection = $this->getViewConsumerRightsProtectionApplyTable();
                $protection->id=$data['id'];
                $info = $protection->getDetails();
                $records = $this->getNotificationRecordsTable();
                $records->userType = 2;
                $records->type = 9;//权益保障申请审核
                $records->merchantStatus = 1;//未读
                $records->fromId = $data['id'];
                $records->merchantId = $info['merchant_id'];
                $records->title = '权益保障申请审核';
                if($data['status']==2){
                    $s = '审核通过';
                    $merchant= $this->getMerchantTable();
                    $merchant->id=$info['merchant_id'];
                    $merchant->isConsumerRightsProtection = 2;
                    $merchant->updateData();

                }else{
                    $s = '-审核不通过,不通过原因：' . ($data['reason']);
                }
                $content = $info['merchant_name']  . ($s);
                $records->content = $content;
                $records->addData();

                echo 2;die();
            };
            $page = isset($_GET['page'])?$_GET['page']:$this->params("page",1);
            $status = isset($_GET['status'])?$_GET['status']:$this->params()->fromRoute('status','');
            $protect =$this->getViewConsumerRightsProtectionApplyTable();
            $protect->page=$page;
            $protect->status=$status;
            $list =$protect->getList();

            $view = new ViewModel(array(
                'paginator'=>$list['paginator'],
                'condition'=>array(
                    "action"=>'protection',
                    'where'=>array(
                    'status'=>$status,
                )),

                'list'=>$list['list'],
                'status'=>$status,
            ));
            $view->setTemplate("platform/check/protection");
            return $this->setMenu($view);
        }
    }
    
    
    
    
    
    
     
    