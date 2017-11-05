<?php
namespace Web\Controller;


use Zend\View\Model\ViewModel;
class VideoController extends CommonController
{

    function __construct()
    {
        parent::__construct();
        $this->controller = 'video';
        $this->module = 'web';
    }
    
    function indexAction() {
        $page_type = array(
            'page_type'=> 3,
            'page_id' => 0,
            'detail_type' =>0
        );
        $this->checkWebLogin($page_type);
        $this->action = substr(__FUNCTION__, 0 ,-6);    
        $data = $this->getModel('Video')->getIndex(2);
//         $data = array('code' => 200);
        if($data['code']==400){
            $this->showMessage($data['message']);
        }
        $user = $this->getModel('User')->userDetails($_SESSION['user_id']);
        $is_perfect = isset($_SESSION['is_perfect']) ? $_SESSION['is_perfect'] : 0;
        $url_name = isset($_GET['n']) ? $_GET['n'] : "";
        return $this->setMenu(array('data'=>$data['category'],'is_perfect'=>$is_perfect,'user'=>$user,'url_name'=> $url_name));
    }
    
    function getCategoryAction(){
        $data = $this->getModel('Video')->getSonCategory();
        echo json_encode($data);
        die();
    }
    
    function ajaxgetDataListAction(){       
        $data = $this->getModel('Video')->ajaxgetDataList();
        echo json_encode($data);
        die();
    }
    
    function detailsAction() {
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $type = isset($_GET['type']) && $_GET['type'] ? $_GET['type'] : $this->params()->fromRoute('type');
        $id = explode(',',$_GET['id']);
        if(!$type){
            $type = isset($id['1']) ? $id['1'] : 0;
        }
        $id = $id ? $id[0] : $this->params()->fromRoute('id',0);
        $page_type = array(
            'page_type'=> 3,
            'page_id' => $id,
            'detail_type' => $type
        );
        $this->checkWebLogin($page_type);
        $data = $this->getModel('Video')->getDetails($type,$id);
//         var_dump($data);exit;
        if($data['code'] == 400){
            return $this->redirect()->toRoute('web',array('controller'=>'index','action'=>'index'));
        }
        $types = isset($_GET['types']) ? $_GET['types'] : 0;
        $pid = isset($_GET['pid']) ? $_GET['pid'] : 0;
        if($types && $types=='subscibe' && $pid){
           $this->getModel('Audio')->deleteSubscibeNew($pid,$id);
        }
        if($data['code']==400){
            $this->showMessage($data['message']);
        }
        $_SESSION['study_num'] = 1;
        $is_perfect = isset($_SESSION['is_perfect']) ? $_SESSION['is_perfect'] : 0;
        $user = $this->getModel('User')->userDetails($_SESSION['user_id']);
        $goback = isset($_GET['go_type']) ? $_GET['go_type'] : 0;
        if($type == 1){
            return $this->setMenu(array(
                'data' => $data['data'],
                'user_id' => $_SESSION['user_id'],
                'type' => $type,
                'is_perfect'=>$is_perfect,
                'user'=> $user,
                'goback' => $goback,
            ));
        }else{
            return $this->setMenu(array(
                'data' => $data['data'],
                'user_id' => $_SESSION['user_id'],
                'type' => $type,
                'is_perfect'=>$is_perfect,
                'user'=> $user,
                'goback' => $goback,
            ),'web/video/coursedetail');
        }
        
    }
    
    //提交评论
    public function commentSubmitAction(){
        $data = $this->getModel('video')->commentVideoSubmit();
        echo json_encode($data);
        die();            
    }
    
    //获取评论列表
    public function ajaxgetCommentListAction(){
        $data = $this->getModel('video')->ajaxGetVideoCommentList();
        echo json_encode($data);
        die();
    }
    
    //新增/删除点赞
    public function ajaxgetPraiseAction(){
        $data = $this->getModel('video')->ajaxGetVideoPraiseList();             
        die();
    }
    
    //新增回复
    public function ajaxReplyCommentAction(){
        $data = $this->getModel('video')->ajaxReplyVideoComment();
        echo json_encode($data);
        die();
    }
    
    //删除回复
    public function ajaxDeleteCommentAction(){
        $data = $this->getModel('video')->ajaxDeleteVideoComment();
        echo json_encode($data);
        die();
    }
    
    //课程包评论提交
    public function coursesCommentSubmitAction(){
        $data = $this->getModel('video')->coursesCommentSubmit();
        echo json_encode($data);
        die();
    }
    
    //新增回复
    public function ajaxReplyCoursesCommentAction(){
        $data = $this->getModel('video')->ajaxReplyCoursesComment();
        echo json_encode($data);
        die();
    }
    
    //删除课程包回复
    public function ajaxDeleteCoursesCommentAction(){
        $data = $this->getModel('video')->ajaxDeleteCoursesComment();
        echo json_encode($data);
        die();
    }
    
    //收藏或者删除课程
    public function ajaxAddCollectAction(){
        $data = $this->getModel('video')->ajaxAddCollect();
        echo json_encode($data);
        die();
    }
    
    public function ajaxAddStudyNumAction(){
        $data = $this->getModel('video')->ajaxAddStudyNum();
        $_SESSION['study_num'] = 0;
        echo json_encode($data);
        die();
    }
    
    public function freeVideoListAction() {
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $data = $this->getModel('Video')->getIndex(2);
        if($data['code']==400){
            $this->showMessage($data['message']);
        }
        return $this->setMenu(array('data'=>$data['category']));
    }
    
    public function ajaxgetFreeVideoListAction(){
        $data = $this->getModel('Video')->ajaxgetFreeVideoList();
        echo json_encode($data);
        die();
    }
    
   public function ajaxWatchRecordAction(){
        $data = $this->getModel('Video')->ajaxWatchRecord();
        echo json_encode($data);
        die();
    }
    
    //购买课程包
    public function coursesOrderSubmitAction(){
        $obj = isset($_POST['obj']) ? (array)$_POST['obj'] : array();
        $data = $this->getModel('Video')->coursesOrderSubmit($obj);
        echo json_encode($data);
        die();
    }
    
    //购买课程
    public function audioOrderSubmitAction(){
        $obj = isset($_POST['obj']) ? (array)$_POST['obj'] : array();
        $data = $this->getModel('Video')->audioOrderSubmit($obj);
        if($data['code'] != 200){
            $data = array(
                'code' => 400,
                'message' => '购买失败！'
            );
        }
        echo json_encode($data);
        die();
    }

}
