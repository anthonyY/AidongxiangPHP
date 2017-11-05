<?php
namespace Web\Controller;

use Zend\View\Model\ViewModel;
class SearchController extends CommonController
{

    function __construct()
    {
        parent::__construct();
        $this->controller = 'search';
        $this->module = 'web';

    }


    /*
     * 输出搜索页面
     *
     * **/
    function indexAction() {
        $this->checkWebLogin($this->getPageTaype());
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $data = $this->getModel('Search')->getSearch();
        $my_search = array();
        if(isset($_COOKIE[$_SESSION['user_id']]) && $_COOKIE[$_SESSION['user_id']]){
            $my_search = unserialize($_COOKIE[$_SESSION['user_id']]);
        }
        return $this->setMenu(array('data' => $data,'my_search' => $my_search));
    }

    /*
     * 获取音频列表
     *
     * */
    function searchResultAction(){
        $this->checkWebLogin($this->getPageTaype());
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $sk = $_REQUEST['sk'] ? $_REQUEST['sk'] : '';
        if(!$sk){
            $this->showMessage('搜索信息不能为空');
        }
        if(isset($_COOKIE[$_SESSION['user_id']]) && $_COOKIE[$_SESSION['user_id']]){
            $search_array = unserialize($_COOKIE[$_SESSION['user_id']]);
            array_push($search_array,$_REQUEST['sk']);
            setcookie($_SESSION['user_id'], serialize(array_unique($search_array)), time() + 10 * 365 * 24 * 60 * 60,'/');
        }else{
            $search_array = array($_REQUEST['sk']);
            setcookie($_SESSION['user_id'], serialize($search_array), time() + 10 * 365 * 24 * 60 * 60,'/');
        }
        $teacher_list = $this->getModel('Search')->getTacherList($sk,3);
        $audio_list = $this->getModel('Audio')->getAudioList(array('sk' => $sk,'type' => 1,'cou_type'=>3,'num' => 3));
        $video_list = $this->getModel('Audio')->getAudioList(array('sk' => $sk,'type' => 2,'cou_type'=>4,'num' => 3));
        $data = array(
            'teacher' => $teacher_list ? $teacher_list : array(),
            'audio_list' => $audio_list['list'] ? $audio_list['list'] : array(),
            'video_list' => $video_list['list'] ? $video_list['list'] : array(),
        );
        return $this->setMenu(array('sk' => $sk,'data'=>$data));
    }

    /*
    * 获取音频列表
    *
    * */
    function getAduioListAction(){
        $this->checkWebLogin($this->getPageTaype());
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $sk = $_REQUEST['sk'] ? $_REQUEST['sk'] : '';
        return $this->setMenu(array('sk' => $sk),'web/search/searchAudio');
    }

    /*
    * 获取音频/视频列表数据
    *
    * */
    function ajaxGetAudiolistAction(){
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $condition['sk'] = $_REQUEST['key_word'] ? $_REQUEST['key_word'] : '';
        $condition['page'] = $_REQUEST['page'] ? $_REQUEST['page'] : 1;
        $condition['type'] = $_REQUEST['type'] ? $_REQUEST['type'] : 1;
        if($_REQUEST['type'] == 1){
            $condition['cou_type'] = 3;
        }else{
            $condition['cou_type'] = 4;
        }
        $data = $this->getModel('Audio')->getAudioList($condition);
        echo json_encode($data);
        die;
    }

    /*
   * 获取音频列表
   *
   * */
    function getVideoListAction(){
        $this->checkWebLogin($this->getPageTaype());
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $sk = $_REQUEST['sk'] ? $_REQUEST['sk'] : '';
        return $this->setMenu(array('sk' => $sk),'web/search/searchVideo');
    }

    /*
     * 获取老师列表
     *
     * */
    function getTeacherListAction(){
        $this->checkWebLogin($this->getPageTaype());
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $sk = $_REQUEST['sk'] ? $_REQUEST['sk'] : '';
        return $this->setMenu(array('sk' => $sk),'web/search/searchTeacher');
    }

    function ajaxGetTutorListAction()
    {
        $data = $this->getModel('Tutor')->ajaxGetTutorList(true);
        echo json_encode($data);
        die();
    }

    /*
     * 删除数据
     *
     * */
    function ajaxDeleteSearchAction(){
        setcookie($_SESSION['user_id'], "", time()-1,'/');
        die();
    }

    function getPageTaype(){
        return array(
            'page_type'=> 7,
            'page_id' => 0,
            'detail_type' =>0
        );
    }
}
