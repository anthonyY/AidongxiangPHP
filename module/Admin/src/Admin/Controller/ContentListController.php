<?php
namespace Admin\Controller;
use Zend\View\Model\ViewModel;
class ContentListController extends CommonController{
    
    /**************************************************************
     *                                                             *
     *             分享设置                                                                               *
     *                                                             *
     ***************************************************************/
    function shareAction(){
        $this->checkLogin("ContentList-share");
        if(isset($_POST['ajax']) && $_POST['ajax'] == 03){
            $return = $this->getModel("ContentList")->editShare();
            if ($return['code'] != 200){
                $this->showMessage($return['message']);
            }
            return $this->redirect()->toRoute('admin', array(
                'controller' => 'ContentList',
                'action' => 'share',
                //                 'types' => 6,
            ));
        }
        $info = $this->getModel('ContentList')->getShareInfo();
        //         if ($data['code'] != 200){
        //             $this->showMessage($data['message']);
        //         }
        $view = new ViewModel(array(
            'info' =>$info,
        ));
        $view->setTemplate('admin/contentList/share');
        return $this->setMenu2($view);
    }
    /**************************************************************
     *                                                             *
     *        觉鸟导师                                                                                                                                               *
     *                                                             *
     ***************************************************************/
    //导师列表
    public function teacherListAction(){
        $this->checkLogin("ContentList-teacherList");
        $condition = array(
            'page' => $this->params()->fromRoute('page',1),//页数
            'types' => isset($_REQUEST['types']) ? $_REQUEST['types'] : $this->params()->fromRoute('types',0),//角色
            'keyword' => isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : $this->params()->fromRoute('keyword',''),//关键字
        );
        $list = $this->getModel("ContentList")->teacherList($condition); 
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'ContentList',
                'action' => 'teacherList',
                'where' => $list['where'],
                'page' => $list['page'],
                'keyword' => $list['keyword'],
            ),
            'types' => $list['types'],
            'keyword' => $list['keyword'],
        ));
        $view->setTemplate('admin/contentList/teacherList');
        return $this->setMenu2($view);
    }
    
    /**
     * 冻结/启用(ajax)
     * @return \Zend\View\Model\ViewModel
     * @version YSQ
     */
    public function ajaxDeleteAction()
    {
        $this->checkLogin("ContentList-teacherList");
        $params = array(
            'id' => (int)$this->params()->fromRoute('id',0),
            'types' => (int)$this->params()->fromRoute('types',0),
        );
        if(!$params['id'] || !in_array($params['types'], array(1,2))){
            $this->showMessage('参数不正确！');
        }
        $list = $this->getModel("ContentList")->ajaxDelete($params);
        if($list['code'] == "200"){
            return $this->redirect()->toRoute('admin', array('controller' => 'contentList','action' => 'teacherList'));
        }
        $this->showMessage($list['message']);
    }
    
    /**
     * 管理员详情 添加/修改
     * @return \Zend\View\Model\ViewModel
     */
    public function teacherDetailsAction()
    {
         $this->checkLogin("ContentList-teacherList");
        if (isset($_POST) && $_POST)
        {
            $add = $_POST['add'] ?  $_POST['add'] : 0;
            $return = $this->getModel("ContentList")->addTeacher();
            if($return['code'] !=200){
                $this->showMessage($return['message']);
            }
            if(!$add){
//                 return $this->redirect()->toRoute('admin', array('controller' => 'ContentList','action' => 'teacherList'));
                echo "<script type='text/javascript'>history.go(-2);</script>";
                die();
            }else{
                return $this->redirect()->toRoute('admin', array('controller' => 'Manage','action' => 'contentList','types'=>4));
            }
           
        }
        
        $id = (int) $this->params()->fromRoute('id',0);
        $add  = $this->params()->fromRoute('add',0);
        $list = array();
        if($id){
            $list =  $this->getModel("ContentList")->teacherDetails($id);
        }
        $view = new ViewModel(array(
            'list' => $list,
            'id' => $id,
            'add' => $add
        ));
        $view->setTemplate('admin/contentList/teacherDetails');
        if($add){
            return $this->setMenu1($view);
        }
        return $this->setMenu2($view);
    }
    
    /**************************************************************
     *                                                             *
     *        课程包                                                                                                                                                   *
     *                                                             *
     ***************************************************************/
    //导师列表
    public function coursesListAction(){
        $this->checkLogin("ContentList-coursesList");
        $condition = array(
            'page' => $this->params()->fromRoute('page',1),//页数
            'types' => isset($_REQUEST['types']) ? $_REQUEST['types'] : $this->params()->fromRoute('types',0),//角色
            'cid' => isset($_REQUEST['cid']) ? $_REQUEST['cid'] : $this->params()->fromRoute('cid',0),//课程包类型
            'pid' => isset($_REQUEST['pid']) ? $_REQUEST['pid'] : $this->params()->fromRoute('pid',0),//分类
            'num' => isset($_REQUEST['num']) ? $_REQUEST['num'] : $this->params()->fromRoute('num',0),//分类
            'keyword' => isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : $this->params()->fromRoute('keyword',''),//关键字
        );
        $list = $this->getModel("ContentList")->coursesList($condition);
        $data = $this->getModel("ContentList")->getCategory();
        $type = array(3=>"音频包",4=>"视频包");
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'contentList',
                'action' => 'coursesList',
                'where' => $list['where'],
                'page' => $list['page'],
                'keyword' => $list['keyword'],
                'types' => $list['types'],
                'type' => $type,
                'cid' => $list['cid'],
                'pid' => $list['pid'],
                'num' => $list['num'],
            ),
            'types' => $list['types'],
            'type' => $type,
            'cid' => $list['cid'],
            'pid' => $list['pid'],
            'num' => $list['num'],
            'keyword' => $list['keyword'],
            'data' => $data
        ));
        $view->setTemplate('admin/contentList/coursesList');
        return $this->setMenu2($view);
    }
    
    public function addCouresAction(){
        $this->checkLogin("ContentList-coursesList");
        if ($_POST)
        {
            $add = $_POST['add'] ?  $_POST['add'] : 0;
            $return = $this->getModel("ContentList")->addCoures();
            if($return['code'] !=200){
                $this->showMessage($return['message']);
            }
            if(!$add){
                echo "<script type='text/javascript'>history.go(-2);</script>";
                die();
//                 return $this->redirect()->toRoute('admin', array('controller' => 'ContentList','action' => 'coursesList'));
            }else{
                return $this->redirect()->toRoute('admin', array('controller' => 'Manage','action' => 'contentList','types'=>3));
            }
          
        }
        $id = $this->params()->fromRoute('id',0);//页数
        $add  = $this->params()->fromRoute('add',0);
        $list = array();
        if($id){
            $list = $this->getModel("ContentList")->coursesDetails($id);
        }
        $category_list = array(1=>'音频课程','视频课程','音频课程包','视频课程包');
        $view = new ViewModel(array(
            'category_list' => $category_list,
            'id' => $id,
            'list' => $list,
            'add' => $add,
        ));
        $view->setTemplate('admin/contentList/addCoures');
        if($add){
            return $this->setMenu1($view);
        }
        return $this->setMenu2($view);
    }

    public function getcategoryAction(){
        $data = $this->getModel("ContentList")->getcategory();
        echo json_encode($data);
        die;
    }
    
    public function ajaxDeleteCoursesAction(){
        $this->checkLogin("ContentList-coursesList");
        $id = isset($_POST['id']) && $_POST['id']? $_POST['id']:0;
        $type = $this->params()->fromRoute('types',0);//页数
        if(!$id){
            $this->showMessage('参数不完整!');
        }else{
            $key = 'hdfksje93hjhf89j';
            $return = $this->getModel('ContentList')->ajaxDeleteCourses($id,$key,$type);
            if($return['code'] != 200){
                $this->showMessage($return['message']);
            }
        }
//         return $this->redirect()->toRoute('admin', array('controller' => 'contentList','action' => 'coursesList'));
        echo "<script type='text/javascript'>history.go(-1);</script>";
        die();
    }
    
    /**************************************************************
     *                                                             *
     *        音频课程                                                                                                                                               *
     *                                                             *
     ***************************************************************/
    //音频列表
    public function videoListAction(){
        $this->checkLogin("ContentList-videoList");
        $condition = array(
            'page' => $this->params()->fromRoute('page',1),//页数
            'types' => isset($_REQUEST['types']) ? $_REQUEST['types'] : $this->params()->fromRoute('types',0),//角色
            'type' => 1,//角色
            'cid' => isset($_REQUEST['cid']) ? $_REQUEST['cid'] : $this->params()->fromRoute('cid',0),//课程包类型
            'pid' => isset($_REQUEST['pid']) ? $_REQUEST['pid'] : $this->params()->fromRoute('pid',0),//分类
            'num' => isset($_REQUEST['num']) ? $_REQUEST['num'] : $this->params()->fromRoute('num',0),//分类
            'keyword' => isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : $this->params()->fromRoute('keyword',''),//关键字
        );
        $list = $this->getModel("ContentList")->videoList($condition);
        $data = $this->getModel("ContentList")->getCategory($condition['type']);
        $type = array(3=>"音频",4=>"视频");
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'ContentList',
                'action' => 'videoList',
                'a_type' => $list['type'],
                'where' => $list['where'],
                'page' => $list['page'],
                'keyword' => $list['keyword'],
                'cid' => $list['cid'],
                'pid' => $list['pid'],
                'num' => $list['num'],
                'type' => $list['type'],
                'types' => $list['types'],
            ),
            'types' => $list['types'],
//             'type' => $type,
            'cid' => $list['cid'],
            'pid' => $list['pid'],
            'num' => $list['num'],
            'type' => $list['type'],
            'keyword' => $list['keyword'],
            'data' => $data
        ));
        $view->setTemplate('admin/contentList/videoList');
        return $this->setMenu2($view);
    }
    
    //视频列表
    public function videoList2Action(){
        $this->checkLogin("ContentList-videoList2");
        $condition = array(
            'page' => $this->params()->fromRoute('page',1),//页数
            'types' => isset($_REQUEST['types']) ? $_REQUEST['types'] : $this->params()->fromRoute('types',0),//角色
            'type' => 2,//角色
            'cid' => isset($_REQUEST['cid']) ? $_REQUEST['cid'] : $this->params()->fromRoute('cid',0),//课程包类型
            'pid' => isset($_REQUEST['pid']) ? $_REQUEST['pid'] : $this->params()->fromRoute('pid',0),//分类
            'num' => isset($_REQUEST['num']) ? $_REQUEST['num'] : $this->params()->fromRoute('num',0),//分类
            'keyword' => isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : $this->params()->fromRoute('keyword',''),//关键字
        );
        $list = $this->getModel("ContentList")->videoList($condition);
        $data = $this->getModel("ContentList")->getCategory($condition['type']);
        $type = array(3=>"音频",4=>"视频");
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'ContentList',
                'action' => 'videoList2',
                'a_type' => $list['type'],
                'where' => $list['where'],
                'page' => $list['page'],
                'keyword' => $list['keyword'],
                'cid' => $list['cid'],
                'pid' => $list['pid'],
                'num' => $list['num'],
                'type' => $list['type'],
                'types' => $list['types'],
            ),
            'types' => $list['types'],
            //             'type' => $type,
            'cid' => $list['cid'],
            'pid' => $list['pid'],
            'num' => $list['num'],
            'type' => $list['type'],
            'keyword' => $list['keyword'],
            'data' => $data
        ));
        $view->setTemplate('admin/contentList/videoList');
        return $this->setMenu2($view);
    }
    //二维码生成
    public function ajaxRealPreviewAction(){
        $return = $this->getModel("ContentList")->ajaxRealPreview();
        echo json_encode($return);
        exit;
    }
    //新增音频
    public function addVideoAction(){
        $type  = $this->params()->fromRoute('a_type',1);
        if($type == 1){
            $this->checkLogin('ContentList-videoList');
        }else{
            $this->checkLogin('ContentList-videoList2');
        }
        if ($_POST)
        {
            $type = $_POST['type'] ?  $_POST['type'] : 1;
            $add = $_POST['add'] ?  $_POST['add'] : 0;
            $return = $this->getModel("ContentList")->addVideos();
            if($return['code'] !=200){
                $this->showMessage($return['message']);
            }
            if(!$add){
//                 var_dump(11);exit;
//                 $this->showMessage("保存成功");
                echo "<script type='text/javascript'>history.go(-2);</script>";
                die();
//                 if($type == 1){
//                     return $this->redirect()->toRoute('admin', array('controller' => 'ContentList','action' => 'videoList'));
//                 }else{
//                     return $this->redirect()->toRoute('admin', array('controller' => 'ContentList','action' => 'videoList2'));
//                 }
            }else{
                if($type == 1){
                    return $this->redirect()->toRoute('admin', array('controller' => 'Manage','action' => 'contentList'));
                }else{
                    return $this->redirect()->toRoute('admin', array('controller' => 'Manage','action' => 'contentList','types'=>2));
                }
            }
        }
        $id = $this->params()->fromRoute('id',0);//页数
        $add  = $this->params()->fromRoute('add',0);
        $list = array();
        if($id){
            $list = $this->getModel("ContentList")->videoDetails($id);
        }
        $teacher = $this->getModel("ContentList")->getTeacher();
        $courses = $this->getModel("ContentList")->getCourses($type+2);
        $category_list = array(1=>'音频课程','视频课程','音频课程包','视频课程包');
//         var_dump($list,$id);exit;
        $view = new ViewModel(array(
            'category_list' => $category_list,
            'id' => $id,
            'list' => $list,
            'type' => $type,
            'teacher' => $teacher,
            'courses' => $courses,
            'add' => $add,
        ));
        $view->setTemplate('admin/contentList/addVideo');
        if($add){
            return $this->setMenu1($view);
        }
        return $this->setMenu2($view);
    }
    
    //新增音频
    public function addVideoOldAction(){
        $type  = $this->params()->fromRoute('a_type',1);
        if($type == 1){
            $this->checkLogin('ContentList-videoList');
        }else{
            $this->checkLogin('ContentList-videoList2');
        }
        if ($_POST)
        {
            $type = $_POST['type'] ?  $_POST['type'] : 1;
            $add = $_POST['add'] ?  $_POST['add'] : 0;
            $return = $this->getModel("ContentList")->addVideos();
            if($return['code'] !=200){
                $this->showMessage($return['message']);
            }
            if(!$add){
                if($type == 1){
                    return $this->redirect()->toRoute('admin', array('controller' => 'ContentList','action' => 'videoList'));
                }else{
                    return $this->redirect()->toRoute('admin', array('controller' => 'ContentList','action' => 'videoList2'));
                }
            }else{
                if($type == 1){
                    return $this->redirect()->toRoute('admin', array('controller' => 'Manage','action' => 'contentList'));
                }else{
                    return $this->redirect()->toRoute('admin', array('controller' => 'Manage','action' => 'contentList','types'=>2));
                }
            }
        }
        $id = $this->params()->fromRoute('id',0);//页数
        $add  = $this->params()->fromRoute('add',0);
        $list = array();
        if($id){
            $list = $this->getModel("ContentList")->videoDetails($id);
        }
        $teacher = $this->getModel("ContentList")->getTeacher();
        $courses = $this->getModel("ContentList")->getCourses($type+2);
        $category_list = array(1=>'音频课程','视频课程','音频课程包','视频课程包');
        //         var_dump($list,$id);exit;
        $view = new ViewModel(array(
            'category_list' => $category_list,
            'id' => $id,
            'list' => $list,
            'type' => $type,
            'teacher' => $teacher,
            'courses' => $courses,
            'add' => $add,
        ));
        $view->setTemplate('admin/contentList/addVideoTwoOld2');
        if($add){
            return $this->setMenu1($view);
        }
        return $this->setMenu2($view);
    }
    
    
    
    public function ajaxDeleteVideoAction(){
        $id = isset($_POST['id']) && $_POST['id']? $_POST['id']:0;
        $type = $this->params()->fromRoute('types',0);
        if($type == 1){
            $this->checkLogin('ContentList-videoList');
        }else{
            $this->checkLogin('ContentList-videoList2');
        }
        if(!$id){
            $this->showMessage('参数不完整!');
        }else{
            $key = 'hdfksje93hjhf89j';
            $return = $this->getModel('ContentList')->ajaxDeleteVideo($id,$key,$type);
            if($return['code'] != 200){
                $this->showMessage($return['message']);
            }
        }
//         var_dump($return);exit;
        if($return['type'] == 2){
//             return $this->redirect()->toRoute('admin', array('controller' => 'contentList','action' => 'videoList2'));
        }
//         return $this->redirect()->toRoute('admin', array('controller' => 'contentList','action' => 'videoList'));
        echo "<script type='text/javascript'>history.go(-1);</script>";
        die();
    }
    
    //批量删除 或者下架
    public function ajaxDeleteDataAction(){
        $return = $this->getModel('ContentList')->ajaxDeleteData();
        echo json_encode($return); 
        die();
    }
    
    /**************************************************************
     *                                                             *
     *            敏感词设置                                       *
     *                                                             *
     ***************************************************************/
    function sensitiveWordsAction(){
        $this->checkLogin("ContentList-sensitiveWords");   
        $list = $this->getModel('ContentList')->sensitiveWords();
        $view = new ViewModel(array(
            'list' =>$list,
        ));
        $view->setTemplate('admin/contentList/sensitiveWord');
        return $this->setMenu2($view);
    }
    
    //提交敏感词
    function ajaxSensitiveWordsAction(){
        $list = $this->getModel('ContentList')->ajaxSensitiveWords();
        echo json_encode($list);
        die();
    }

    /**************************************************************
     *                                                             *
     *            首页设置                                          *
     *                                                             *
     ***************************************************************/
    function homePageSettingAction(){
        $this->checkLogin("ContentList-homePageSetting");
        $list = $this->getModel('ContentList')->homePageSetting();
        $data = $this->getModel('ContentList')-> freeOfCharge();
        if(isset($_POST['sub']) && $_POST['sub']){
            $add = $this->getModel('ContentList')->addPageSetting($_POST);
            return $this->redirect()->toRoute('admin', array('controller' => 'ContentList','action' => 'homePageSetting'));
        }
        $view = new ViewModel(array(
            'data' => $data,
            'one_data' => $list['one_data'],
            'two_data' => $list['two_data']
        ));
        $view->setTemplate('admin/contentList/homePageSetting');
        return $this->setMenu2($view);
    }

    /**************************************************************
     *                                                             *
     *            搜索词词设置                                      *
     *                                                             *
     ***************************************************************/
    function searchAction(){
        $this->checkLogin("ContentList-search");
        $list = $this->getModel('ContentList')->selectSearch();
        if(isset($_POST['sub']) && $_POST['sub']){
            $add = $this->getModel('ContentList')->addSearch($_POST);
            return $this->redirect()->toRoute('admin', array('controller' => 'ContentList','action' => 'search'));
        }
        $view = new ViewModel(array('list' => $list));
        $view->setTemplate('admin/contentList/search');
        return $this->setMenu2($view);
    }

}
?>