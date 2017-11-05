<?php
namespace Web\Controller;

use Zend\View\Model\ViewModel;
class AudioController extends CommonController
{

    function __construct()
    {
        parent::__construct();
        $this->controller = 'audio';
        $this->module = 'web';
    }
    
    
    /* 
     * 输出音频首页页面
     */
    function indexAction() {
        $page_type = array(
            'page_type'=> 2,
            'page_id' => 0,
            'detail_type' =>0
        );
        $this->checkWebLogin($page_type);
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $data = $this->getModel('Video')->getIndex(1);
        $user = $this->getModel('User')->userDetails($_SESSION['user_id']);
//         var_dump($user['position_id']);exit;
        if($data['code']==400){
            $this->showMessage($data['message']);
        }
        $is_perfect = isset($_SESSION['is_perfect']) ? $_SESSION['is_perfect'] : 0;
        $url_name = isset($_GET['n']) ? $_GET['n'] : "";
        return $this->setMenu(array('data'=>$data['category'],'is_perfect'=>$is_perfect,'user'=>$user,'url_name'=> $url_name));
    }
    
    function detailsAction()
    {
        $type = isset($_GET['type']) ? $_GET['type'] : $this->params()->fromRoute('type',0);
        $id = explode(',',$_GET['id']);
        if(!$type){
            $type = isset($id['1']) ? $id['1'] : 0;
        }
        $id = $id ? $id[0] : $this->params()->fromRoute('id',0);
        $page_type = array(
            'page_type'=> 2,
            'page_id' => $id,
            'detail_type' => $type
        );
        $this->checkWebLogin($page_type);
        $one_audio_id = isset($_GET['uid']) ? $_GET['uid'] : $this->params()->fromRoute('uid',0); 
        $types = isset($_GET['types']) ? $_GET['types'] : 0;
        $pid = isset($_GET['pid']) ? $_GET['pid'] : 0;
        if($types && $types=='subscibe' && $pid){
             $data = $this->getModel('Audio')->deleteSubscibeNew($pid,$id);
        }
        if(!$id)
        {
            $this->showMessage('请求参数错误');
        }
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $data = $this->getModel('Audio')->details($id,$type,$one_audio_id);

        if($data['code'] != 200){
            return $this->redirect()->toRoute('web',array('controller'=>'index','action'=>'index'));
//             $this->showMessage($data['message']);
        }
        $is_perfect = isset($_SESSION['is_perfect']) ? $_SESSION['is_perfect'] : 0;
        $user = $this->getModel('User')->userDetails($_SESSION['user_id']);
        $goback = isset($_GET['go_type']) ? $_GET['go_type'] : 0;
        return $this->setMenu(array('data'=>$data['data'],'goback' => $goback,'type'=>$type,'user_id'=>$_SESSION['user_id'],'is_perfect'=>$is_perfect,'user'=> $user));
    }
    
    
    /**
     * 获取音频列表信息
     */
    public function getAudioListAction()
    {
        $one_category = isset($_POST['one_category']) ? $_POST['one_category'] : 0;
        $two_category = isset($_POST['two_category']) ? $_POST['two_category'] : 0;
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $condition = array(
            'one_category' => $one_category,
            'two_category' => $two_category,
            'page' => $page,
        );
        if(!$one_category)
        {
            $this->ajax(array('code'=>400,'message'=>'参数错误'));
            die();
        }
        $data = $this->getModel('Audio')->getAudioList($condition);
        $this->ajax($data);
        die();
    }
    
    public function ajaxgetCommentListAction(){
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $type = isset($_POST['audio_type']) ? $_POST['audio_type'] : 0;
        if(!$id || !$type)
        {
            die($this->ajax(array('code'=>400,'message'=>'参数错误')));
        }
        $data = $this->getModel('Audio')->ajaxgetCommentList($id,$type,$page);
        echo json_encode($data);
        die();
    }
    
    public function commentSubmitAction(){
        $audio_id = $_POST['id'] ? $_POST['id'] : 0;
        $user_id = $_POST['user_id'] ? $_POST['user_id'] : 0;
        $audio_type = $_POST['audio_type'] ? $_POST['audio_type'] : 0;
        $content = $_POST['content'] ? $_POST['content'] : '';
        if(!trim($content))
        {
            $this->ajax(array('code'=>400,'message'=>'参数错误'));
            die();
        }
        $data = $this->getModel('Audio')->commentSubmit($audio_id,$user_id,$audio_type,$content);
        echo json_encode($data);
        die();
    }
    
    public function ajaxgetPraiseAction(){
        $data = $this->getModel('Audio')->ajaxgetPraiseList();
        die();
    }
    
    //回复评论
    public function ajaxReplyCommentAction()
    {
        $data = $this->getModel('Audio')->ajaxReplyComment();
        echo $this->ajax($data);
        die();
    }
    
    //删除回复
    public function ajaxDeleteCommentAction(){
        $data = $this->getModel('Audio')->ajaxDeleteAudioComment();
        echo json_encode($data);
        die();
    }
    
    public function ajaxAddStudyNumAction(){
        $data = $this->getModel('audio')->ajaxAddStudyNum();
        $_SESSION['study_num'] = 0;
        echo json_encode($data);
        die();
    }

}
