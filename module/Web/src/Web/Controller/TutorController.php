<?php
namespace Web\Controller;

use Zend\View\Model\ViewModel;
class TutorController extends CommonController
{

    function __construct()
    {
        parent::__construct();
        $this->controller = 'tutor';
        $this->module = 'web';
    }

    function indexAction() {
        $page_type = array(
            'page_type'=> 4,
            'page_id' => 0,
            'detail_type' =>0
        );
        $this->checkWebLogin($page_type);
        $user = $this->getModel('User')->userDetails($_SESSION['user_id']);
        $is_perfect = isset($_SESSION['is_perfect']) ? $_SESSION['is_perfect'] : 0;
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu(array('is_perfect'=>$is_perfect,'user'=>$user));
    }
    
    function ajaxGetTutorListAction()
    {
        $data = $this->getModel('Tutor')->ajaxGetTutorList();
        echo json_encode($data);
        die();
    }
    
    function detailsAction()
    {
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $id = $this->params()->fromRoute('id');
        $page_type = array(
            'page_type'=> 4,
            'page_id' => $id ? $id : $_GET['id'],
            'detail_type' =>0
        );
        $this->checkWebLogin($page_type);
        $data = $this->getModel('Tutor')->details($id);
        if($data['code'] != 200)
        {
            $this->showMessage($data['message']);
        }
        return $this->setMenu(array('data'=>$data['data'],'user_id'=>$_SESSION['user_id']));
    }
    
    function ajaxGetTutorCourseListAction()
    {
        $data = $this->getModel('Tutor')->ajaxGetTutorCourseList();
        echo json_encode($data);
        die();
    }
    
    function ajaxAttentionAction()
    {
        $data = $this->getModel('Tutor')->ajaxAttention();
        echo json_encode($data);
        die();
    }
    
    function subscriptionTutorListAction() {
        $page_type = array(
            'page_type'=> 6,
            'page_id' => 0,
            'detail_type' =>0
        );
        $this->checkWebLogin($page_type);
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu();
    }
    
    function ajaxGetSubscriptionTutorListAction()
    {
        $data = $this->getModel('Tutor')->ajaxGetSubscriptionTutorList();
        echo json_encode($data);
        die();
    }

}
