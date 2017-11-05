<?php
namespace Admin\Controller;
use Zend\View\Model\ViewModel;
class SystemController extends CommonController{
    /******预览start********/
    //首页预览
    function indexPreview2Action() {
       $image = isset($_POST['image']) && $_POST['image'] ? $_POST['image'] : '';
       $plate = isset($_POST['plate']) && $_POST['plate'] ? $_POST['plate'] : 1;
       $type = isset($_POST['type']) && $_POST['type'] ? $_POST['type'] : '';
       if($type == 1){
           $title = isset($_POST['title']) && $_POST['title'] ? $_POST['title'] : '';
           $content = isset($_POST['content']) && $_POST['content'] ? $_POST['content'] : '';
           $_SESSION['ads_title'] = $title;
           $_SESSION['ads_content'] = $content;
           $url = $this->url()->fromRoute('admin',array('controller'=>'System','action'=>'articleDetails2'));
       }else if($type == 6){
           $url = isset($_POST['link']) && $_POST['link'] ? $_POST['link'] : '';
       }else if(in_array($type, array(2,4))){
           $audio_id = isset($_POST['audio_id']) && $_POST['audio_id'] ? $_POST['audio_id'] : '';
           if($type == 2){
               $types = 1;
           }else{
               $types = 2;
           }
           $url = $this->url()->fromRoute('web-common',array('controller'=>'Audio','action'=>'details','type'=>$types)).'?id='.$audio_id;
       }else if(in_array($type, array(3,5))){
           $audio_id = isset($_POST['audio_id']) && $_POST['audio_id'] ? $_POST['audio_id'] : '';
           if($type == 3){
               $types = 1;
           }else{
               $types = 2;
           }
           $url = $this->url()->fromRoute('web-common',array('controller'=>'video','action'=>'details','type'=>$types,'id'=>$audio_id));
       }
       if($plate == 1){
           $review = 'admin/preview/indexPreview2';
       }else{
           $review = 'admin/preview/presentRecordDetail';
       }
        return $this->setMenu(
            array(
                'type' => $type,
                'url' => $url,
                'image' => $image
            ),$review);
    }
    function articleDetails2Action()
    {
        $content = isset($_SESSION['ads_content']) && $_SESSION['ads_content'] ? $_SESSION['ads_content'] : '';
        $title = isset($_SESSION['ads_title']) && $_SESSION['ads_title'] ? $_SESSION['ads_title'] : '';
        $data = array(
            'title' => $title,
            'content' => $content,
        );
        return $this->setMenu(array('data'=>$data),'admin/preview/articleDetails');
    }
    
    //音频,视频预览
    function detailsPreviewAction()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : $this->params()->fromRoute('id',0);
        $one_audio_id = $this->params()->fromRoute('uid',0);
        $type = isset($_GET['type']) ? $_GET['type'] : $this->params()->fromRoute('types',1);
        $teacher_id = isset($_POST['teacher']) && $_POST['teacher'] ? $_POST['teacher'] : '';
        $teacher = $this->getModel('Audio')->getOne(array('id' => $teacher_id),array('name','head_icon','title'),'teacher');
        $teacher_img = $this->getModel('Audio')->getOne(array('id'=>$teacher['head_icon']),array('path','filename'),'image');
        $data['teacher_name'] = $teacher['name'];
        $data['teacher_img'] = $teacher_img ? $teacher_img['path'].$teacher_img['filename'] : "";
        $data['teacher_title'] = $teacher['title'];
        
        $one_type = isset($_POST['one_type']) && $_POST['one_type'] ? $_POST['one_type'] : '';
        $data['category_name'] = $this->getModel('Audio')->getOne(array('id' => $one_type),array('name'),'category')['name'];
        $data['image'] = isset($_POST['img_path']) && $_POST['img_path'] ? $_POST['img_path'] : '';
        $full_time = isset($_POST['full_time']) && $_POST['full_time'] ? $_POST['full_time'] : '0';
        $full_time  = str_replace('时', ":",  $full_time);
        $full_time  = str_replace('分', ':',  $full_time);
        $data['full_time']  = str_replace('秒', '',  $full_time);
        $data['full_path'] = isset($_POST['full_path']) && $_POST['full_path'] ? $_POST['full_path'] : '';
        $data['auditions_time'] = isset($_POST['auditions_time']) && $_POST['auditions_time'] ? $_POST['auditions_time'] : '0';
        $data['price'] = isset($_POST['price']) && $_POST['price'] ? $_POST['price'] : '';
        $data['original_price'] = isset($_POST['original_price']) && $_POST['original_price'] ? $_POST['original_price'] : '';
        
        $data['audio_synopsis'] = isset($_POST['synopsis']) && $_POST['synopsis'] ? $_POST['synopsis'] : '';
        $data['outline'] = isset($_POST['outline']) && $_POST['outline'] ? $_POST['outline'] : '';
        $data['original_price'] = isset($_POST['original_price']) && $_POST['original_price'] ? $_POST['original_price'] : '';
        return $this->setMenu(array('data'=>$data,'type'=>$type,'user_id'=>1),'admin/preview/detailsPreview');
    }
    //充值预览
    public function rechargeIndexAction()
    {
        $top_up['top_content'] = isset($_POST['top']) && $_POST['top'] ? $_POST['top'] : '';
        $user['amount'] = 0;
        $moneys_array = isset($_POST['price']) && $_POST['price'] ? $_POST['price'] : '';
        $top_up['bottom_content'] = isset($_POST['bottom']) && $_POST['bottom'] ? $_POST['bottom'] : '';
        return $this->setMenu(array('user'=>$user,'top_up'=>$top_up,'moneys_array'=>$moneys_array),'admin/preview/rechargeIndex');
    }
    //首页预览
    function indexPreviewAction() {
        $data = $this->getModel('Index')->getIndex();
        $ads = $data['ads'];
        $four_free_audios = $data['four_free_audios'];
        if($ads)
        {
            foreach ($ads as $v)
            {
                $url = '';
                $v['link'] = $url;
            }
        }
        $name_array = $link_array = array();
        $first_name = $second_name = "";
        $name_array = isset($_POST['name']) && $_POST['name'] ? $_POST['name'] : '';
        $link_array = isset($_POST['link']) && $_POST['link'] ? $_POST['link'] : '';
        $image_array = isset($_POST['paths']) && $_POST['paths'] ? $_POST['paths'] : '';
        $first_name = isset($_POST['first_name']) && $_POST['first_name'] ? $_POST['first_name'] : '';
        $second_name = isset($_POST['second_name']) && $_POST['second_name'] ? $_POST['second_name'] : '';
        if($four_free_audios)
        {
            foreach ($four_free_audios as $m)
            {
                $timestamp = strtotime($m['timestamp']);
                $today_end = strtotime(date("Y-m-d 23:59:59"));
                $today_start = strtotime(date("Y-m-d 00:00:00"));
                $m['is_today'] = $timestamp < $today_end && $timestamp > $today_start ? 1 : 0;
            }
        }
        return $this->setMenu(
            array(
                'ads'=>$ads,
                'four_free_audios'=>$four_free_audios,
                'name_array'=>$name_array,
                'link_array'=> isset($link_array) && $link_array ? $link_array : array(),
                'image_array' => isset($image_array) && $image_array ? $image_array : array(),
                'first_name'=>$first_name,
                'second_name'=>$second_name,
            ),'admin/preview/indexPreview');
    }
    //分享预览
    public function sharePreviewAction()
    {
        $path = isset($_POST['image']) && $_POST['image'] ? $_POST['image'] : '';
        $data = array(
            'img_path'=>$path,
        );
        return $this->setMenu(array('data' => $data),'admin/preview/sharePreview');
    }
    //用户帮助预览
    public function memberPreviewAction()
    {
        $price = isset($_POST['price']) && $_POST['price'] ? $_POST['price'] : '';
        $time = isset($_POST['time']) && $_POST['time'] ? $_POST['time'] : '';
        $path = isset($_POST['image']) && $_POST['image'] ? $_POST['image'] : '';
        $data = array(
            'time'=>$time,
            'price'=>$price,
            'img_path'=>$path,
        );
        return $this->setMenu(array('data' => $data),'admin/preview/memberPreview');
    }
    /**
     * 详情预览
     */
    function articleDetailsAction()
    {
        $content = isset($_POST['content']) && $_POST['content'] ? $_POST['content'] : '';
        $title = isset($_POST['title']) && $_POST['title'] ? $_POST['title'] : '';
        $data = array(
            'title' => $title,
            'content' => $content,
        );
        return $this->setMenu(array('data'=>$data),'admin/preview/articleDetails');
    }
    
   /******预览end********/
    
    public function systemAction($type=1){
        $this->checkLogin("System-system");
        $condition = array(
            'page' => $this->params()->fromRoute('page',1),//页数
            'start' => isset($_REQUEST['start']) ? $_REQUEST['start'] : '',
            'end' => isset($_REQUEST['end']) ? $_REQUEST['end'] : '',
            'type' => isset($_REQUEST['type']) ? $_REQUEST['type'] : 0,
            'keyword' => isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : $this->params()->fromRoute('keyword',''),//关键字
        );
        $list = $this->getModel("System")->userSystem($condition);//所有帮助信息
        $position_list = $this->getModel('AdminUser')->getPositionList();//用户职务
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'System',
                'action' => 'userHelp',
                'where' => $list['where'],
                'page' => $condition['page'],
                'keyword' => $condition['keyword'],
            ),
            'position' => $position_list,
            'jump' => $this->jumpType(),
            'page' => $condition['page'],
            'where' => $list['where'],
            'keyword' => $condition['keyword'],
            'start' => $condition['start'],
            'end' => $condition['end'],
            'type' => $condition['type']
        ));
        $view->setTemplate('admin/system/system');
        return $this->setMenu2($view);
    }
    public function systemDetailsAction() {
        $this->checkLogin("System-system");
        $id = $this->params()->fromRoute('id');
        $info = array();
        $model = $this->getModel('System');
        if ($id) {
            $info = $model->getOne(array('id' => $id), null, 'notification_system');
            $info['audio'] = '';
            if($info['type']==2 || $info['type']==3){
                $info['audio'] = $model->getOne(array('id' => $info['audio_id']), null, 'audio');
            }elseif($info['type']==4 || $info['type']==5){
                $info['audio'] = $model->getOne(array('id' => $info['audio_id']), null, 'courses');
            }
          
        }
        if(isset($_POST['ajax']) && $_POST['ajax'] == 2017){
            $return = $model->setSystemDetails($info);
            if ($return['code']) {
                $this->showMessage($return['message']);
            } else {
                return $this->redirect()->toRoute('admin', array(
                    'controller' => 'System',
                    'action' => 'system',
                ));
            }
        }
        $view = new ViewModel(array(
            'info' => $info,
            'jump' => $this->jumpType(),
            'position' => $this->getModel('AdminUser')->getPositionList(),//用户职务,
        ));
        if($info && $info['send_status'] == 2){
            $view->setTemplate('admin/system/systemDetails2');
        }else{
            $view->setTemplate('admin/system/systemDetails');
        }
        return $this->setMenu2($view);
    }
    
    function ajaxDeleteSystemAction(){
        
    }
    
    /**************************************************************
     *                                                             *
     *             充值设置                                                                               *
     *                                                             *
     ***************************************************************/
    function setTopUpAction(){
        $this->checkLogin("System-setTopUp");
        if(isset($_POST['ajax']) && $_POST['ajax'] == 03){
            $return = $this->getModel("System")->editTopUp();
            if ($return['code'] != 200){
                $this->showMessage($return['message']);
            }
            return $this->redirect()->toRoute('admin', array(
                'controller' => 'System',
                'action' => 'setTopUp',
            ));
        }
        $info = $this->getModel('System')->getTopUpInfo();
        $view = new ViewModel(array(
            'info' =>$info,
        ));
        $view->setTemplate('admin/system/topUp');
        return $this->setMenu2($view);
    }
    /**************************************************************
     *                                                             *
     *             会员设置                                                                               *
     *                                                             *
     ***************************************************************/
    function setMemberAction(){
        $this->checkLogin("System-setMember");
        if(isset($_POST['ajax']) && $_POST['ajax'] == 03){
            $return = $this->getModel("System")->editMember();
            if ($return['code'] != 200){
                $this->showMessage($return['message']);
            }
            return $this->redirect()->toRoute('admin', array(
                'controller' => 'System',
                'action' => 'setMember',
//                 'types' => 6,
            ));
        }
        $info = $this->getModel('System')->getMemberInfo();
        $view = new ViewModel(array(
            'info' =>$info,
        ));
        $view->setTemplate('admin/system/member');
        return $this->setMenu2($view);
    }
    
    /**************************************************************
     *                                                             *
     *              首页管理                                                                                  *
     *                                                             *
     ***************************************************************/
    /**
     * 首页管理      
     * @version YSQ
     */
    public function indexAction(){
        $this->checkLogin("System-index");
        if(isset($_POST['ajax']) && $_POST['ajax'] == 16){
            $return = $this->getModel("System")->editHomeManage();
            if ($return['code'] != 200){
                $this->showMessage($return['message']);
            }
            return $this->redirect()->toRoute('admin', array(
                'controller' => 'System',
                'action' => 'index'
            ));
        }
        $this->getModel('Index')->setHomeManage();
        $data = $this->getModel('System')->getHomeManageInfo();

        $view = new ViewModel(array(
            'data' =>$data['info'],
        ));
        $view->setTemplate('admin/system/index');
        return $this->setMenu2($view);
    }
    
    /**************************************************************
     *                                                             *
     *          管理员数据处理                                                                                                                             *
     *                                                             *
     ***************************************************************/
    /**
     * 管理员列表
     * @version YSQ
     */
    public function roleListAction(){
        $this->checkLogin("System-roleList");
        $condition = array(
            'page' => $this->params()->fromRoute('page',1),//页数
            'cid' => isset($_REQUEST['cid']) ? $_REQUEST['cid'] : $this->params()->fromRoute('cid',0),//账号状态
            'types' => isset($_REQUEST['types']) ? $_REQUEST['types'] : $this->params()->fromRoute('types',0),//角色
            'keyword' => isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : $this->params()->fromRoute('keyword',''),//关键字
        );
        $role_list = $this->getModel("System")->adminRoleList();//所有角色信息
        $list = $this->getModel("System")->roleList($condition);

        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'System',
                'action' => 'roleList',
                'where' => $list['where'],
                'page' => $list['page'],
                'keyword' => $list['keyword'],
                'cid' => $list['cid'],
            ),
            'keyword' => $list['keyword'],
            'cid' => $list['cid'],
            'types' => $list['types'],
            'role_list' =>$role_list,
        ));
        $view->setTemplate('admin/system/adminList');
        return $this->setMenu2($view);
    }
    
    
    /**
     * 删除管理员
     * @version YSQ
     */
    public function ajaxDeleteAdminAction(){
        $this->checkLogin("System-roleList");
        $id = isset($_POST['id']) && $_POST['id']? $_POST['id']:0;
        if(!$id){
            $this->showMessage('参数不完整!');
        }else{
            $key = 'hdfksje93hjhf89j';
            $return = $this->getModel('System')->ajaxDeleteAdmin($id,$key);
            if($return['code'] != 200){
                $this->showMessage($return['message']);
            }
        }
        return $this->redirect()->toRoute('admin', array('controller' => 'System','action' => 'roleList'));
    }
    
    /**
     * 冻结/启用(ajax)
     * @return \Zend\View\Model\ViewModel
     * @version YSQ
     */
    public function ajaxDeleteAction()
    {
        $this->checkLogin("System-roleList");
        $params = array(
            'id' => (int)$this->params()->fromRoute('id',0),
            'types' => (int)$this->params()->fromRoute('types',0),
        );
        if(!$params['id'] || !in_array($params['types'], array(1,2))){
            $this->showMessage('参数不正确！');
        }
        $list = $this->getModel("System")->setUserDelete($params);
        if($list['code'] == "200"){
            return $this->redirect()->toRoute('admin', array('controller' => 'System','action' => 'roleList'));
        }
        $this->showMessage($list['message']);
    }
    
    /**
     * 管理员详情 添加/修改
     * @return \Zend\View\Model\ViewModel
     */
    public function adminDetailsAction()
    {
        $this->checkLogin("System-roleList");
        if (isset($_POST) && $_POST)
        {
            $return = $this->getModel("System")->setAdminDetails();
            if($return['code'] !=200){
                $this->showMessage($return['message']);
            }
            if(isset($_POST['id']) && $_POST['id'] && $_POST['id'] == $_SESSION['role_nb_admin_id']){
                echo "<script type='text/javascript'>parent.location.href='".ROOT_PATH."admin/Admin/alogout';</script>";
                die();
            }
            echo "<script type='text/javascript'>history.go(-2);</script>";
            die();
        }
        $id = (int) $this->params()->fromRoute('id',0);
        $cid = (int) $this->params()->fromRoute('cid',0);
    
        $admin = array();
        if($id){
            $admin =  $this->getModel("System")->getAdminDetails($id);
            if($admin['code'] != 200){
                $this->showMessage($admin['message']);
            }
            $admin = (array)$admin['info'];
        }
        $role_list = $this->getModel("System")->adminRoleList();//所有角色信息
        $view = new ViewModel(array(
            'admin' => $admin,
            'role_list' => $role_list,
            'cid' => $cid,
        ));
        $view->setTemplate('admin/system/adminDetails');
        return $this->setMenu2($view);
    }
    
    
    /**************************************************************
     *                                                             *
     *          职务数据处理                                                                                                                                 *
     *                                                             *
     ***************************************************************/
    
    /**
     * 职务列表
     * @version YSQ
     */
    public function roleAction(){
        $this->checkLogin("System-role");
        $condition = array(
            'page' => $this->params()->fromRoute('page',1),//页数
        );
        $list = $this->getModel("System")->role($condition);
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'System',
                'action' => 'role',
                'where' => $list['where'],
                'page' => $list['page'],
            ),
            'page' => $list['page'],
            'where' => $list['where'],
        ));
        $view->setTemplate('admin/system/roleList');
        return $this->setMenu2($view);
    }
    

    /**
     * 职务详情
     * @version YSQ
     */
    public function roleDetailAction(){
        $this->checkLogin("System-role");
        $id = (int) $this->params()->fromRoute('id',0);
        $info = array();
        $authority = '';
        if($id){
            $info = $this->getModel("System")->roleInfo($id,null);

            if($info['code'] !=200){
                $this->showMessage($info['message']);
            }
            $info = $info['info'];
            $authority = $info['manage'] == 'all' ? 'all' : json_decode($info['manage'],true);
        }
        $roleList = $this->getRoleAuthority();
        $view = new ViewModel(array(
            'info' => $info,
            'authority' => $authority,
            'roleList' => $roleList['role'],
        ));
        $view->setTemplate('admin/system/role');
        return $this->setMenu2($view);
    }
    
    /**
     * 删除角色
     * 要考虑有管理员的角色不可删除
     * @version YSQ
     */
    public function roleDeleteAction(){
        $this->checkLogin("System-role");
        $id = isset($_POST['id']) && $_POST['id']? $_POST['id']:0;
        $type = isset($_POST['type']) && $_POST['type']? $_POST['type']:0;
        if(!$id){
            $this->showMessage('参数不完整!');
        }else{
            $key = 'dhfjhkadslj32432';
            $return = $this->getModel('System')->roleDelete($id,$key);
            if($type=='1'){
                if($return['code']==200){
                    return $this->redirect()->toRoute('admin', array('controller'=>'System','action'=>'role'));
                }
            }
            $this->showMessage($return['message']);
        }
    }

    /**
     * 添加/修改角色
     */
    public function addRoleAction(){
        $this->checkLogin("System-role");
        $return = $this->getModel("System")->addRole();
        if($return['code'] !=200){
            $this->showMessage($return['message']);
        }
        echo "<script type='text/javascript'>history.go(-2);</script>";
        die();
    }
    
    
    /**
     * 异步得到权限详情
     * @version YSQ
     */
    public function ajaxAdminRoleAction(){
        $id = isset($_POST['id']) && $_POST['id']? (int)$_POST['id']:0;
        if(!$id){
            $this->showMessage('请求参数不完整!');
        }
        $roles = $this->getRoleAuthority();
        $info = $this->getModel("System")->roleInfo($id, $roles);
        if($info['code'] !=200){
            $this->showMessage($info['message']);
        }
        echo  json_encode($info['info']);exit;
    }
    
    
    /**************************************************************
     *                                                             *
     *          注册协议管理                                                                                                                                *
     *                                                             *
     ***************************************************************/
    function protocolAction(){
        $this->checkLogin("System-protocol");
        if($_POST){
            $return= $this->getModel('System')->setProtocolInfo();
            echo json_encode($return);exit;
        }
        $list = $this->getModel('System')->getAboutmyShare();
        $list = isset($list['list'])? $list['list']:array();
        $view = new ViewModel(array(
            'users' => $list,
        ));         
        $view->setTemplate('admin/system/protocol');
        return $this->setMenu2($view);
    }
    
    /**************************************************************
     *                                                             *
     *          用户寄语管理                                                                                                                                *
     *                                                             *
     ***************************************************************/
    function sendWordAction(){
        $this->checkLogin("System-sendWord");
        if($_POST){
            $type = 2;
            $return= $this->getModel('System')->setProtocolInfo($type);
            echo json_encode($return);exit;
        }
        $type = 2;
        $list = $this->getModel('System')->getAboutmyShare($type);
        if($list['code'] != 200){
            $list['list'] = array();
        }
        $view = new ViewModel(array(
            'list' => $list['list'],
        ));
        $view->setTemplate('admin/system/sendWord');
        return $this->setMenu2($view);
    }
    
    /**************************************************************
     *                                                             *
     *          用户职位管理                                                                                                                                *
     *                                                             *
     ***************************************************************/
    
    function userJobAction(){
        $this->checkLogin("System-userJob");
        $condition = array(
            'page' => $this->params()->fromRoute('page',1),//页数
        );
        $list = $this->getModel("System")->userJob($condition);
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'System',
                'action' => 'userJob',
                'where' => $list['where'],
                'page' => $list['page'],
            ),
            'page' => $list['page'],
            'where' => $list['where'],
        ));
        $view->setTemplate('admin/system/userJob');
        return $this->setMenu2($view);
    }
    
    function addUserJobAction(){
         $this->checkLogin("System-userJob");
        $id = isset($_POST['id']) && $_POST['id']? $_POST['id']:0;
        $content = isset($_POST['content']) && $_POST['content']? $_POST['content']:0;
        $list = $this->getModel('System')->addUserJob($id,$content);
        if($list['code'] !=200){
            $this->showMessage($list['message']);
        }
        echo  json_encode($list);exit;
    }
    

    public function userJobDeleteAction(){
        $this->checkLogin("System-userJob");
        $id = isset($_POST['id']) && $_POST['id']? $_POST['id']:0;
        if(!$id){
            $this->showMessage('参数不完整!');
        }else{
            $key = 'dhfjhkadslj32432';
            $return = $this->getModel('System')->userJobDelete($id,$key);
            $this->showMessage($return['message']);
            
        }
    }
    
    /**************************************************************
     *                                                             *
     *          用户帮助                                                                                                                                         *
     *                                                             *
     ***************************************************************/

    public function userHelpAction($type=1){//1:  用户帮助 ;2:功能介绍
        $this->checkLogin("System-userHelp");
        $condition = array(
            'page' => $this->params()->fromRoute('page',1),//页数
            'start' => isset($_REQUEST['start']) ? $_REQUEST['start'] : '',
            'end' => isset($_REQUEST['end']) ? $_REQUEST['end'] : '',
            'keyword' => isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : $this->params()->fromRoute('keyword',''),//关键字
        );
        $list = $this->getModel("System")->userHelpList($condition,$type);//所有帮助信息
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'System',
                'action' => 'userHelp'.($type==2?2:''),
                'where' => $list['where'],
                'page' => $list['page'],
                'keyword' => $list['keyword'],
            ),
            'page' => $list['page'],
            'where' => $list['where'],
            'keyword' => $list['keyword'],
            'start' => $condition['start'],
            'end' => $condition['end'],
            'type' => $type
        ));
        $view->setTemplate('admin/system/helpList');
        return $this->setMenu2($view);
    }
   
    public function userHelp2Action(){//1:  用户帮助 ;2:功能介绍
       return $this->userHelpAction(2);
    }
    public function helpDetailsAction()
    {
         $this->checkLogin("System-userHelp");
        $type =  $this->params()->fromRoute('types',1);//页数
        if ($_POST)
        {
            $return = $this->getModel("System")->setHelpDetails($type);
            if($return['code'] !=200){
                $this->showMessage($return['message']);
            }
           
            echo "<script type='text/javascript'>history.go(-2);</script>";
            die();
        }
        $id = (int) $this->params()->fromRoute('id',0);
    
        $help = array();
        if($id){
            $help =  $this->getModel("System")->getHelpDetails($id);
            if($help['code'] != 200){
                $this->showMessage($help['message']);
            }
            $help = $help['info'];
        }
    
        $view = new ViewModel(array(
            'help' => $help,
            'type' => $type
        ));
        $view->setTemplate('admin/system/helpDetails');
        return $this->setMenu2($view);
    
    }
    
    public function ajaxDeleteHelpAction() {
        $this->checkLogin("System-userHelp");
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id']  : 0;
        if(!$id){
            $this->showMessage('请求数据不完整！');
        }
        $set = array(
            'delete' => DELETE_TRUE,
        );
        $ads = $this->getModel('System');
        $mes = $ads->updateHelp($id, $set);
        $this->showMessage($mes['message']);
    }
    
    public function ajaxDelete2Action() {
        $this->checkLogin("System-system");
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id']  : 0;
        if(!$id){
            $this->showMessage('请求数据不完整！');
        }
        $set = array(
            'delete' => DELETE_TRUE,
        );
        $ads = $this->getModel('System');
        $mes = $ads->updateRelation($id, $set);
        $this->showMessage($mes['message']);
    }

    /**************************************************************
     *                                                             *
     *          营销工具-->生成二维码                                *                                                                                                         *
     *                                                             *
     ***************************************************************/
    /**
     * 列表
     */
    public function marketingAction()
    {
        $this->checkLogin("System-marketing");
        $condition = array(
            'limit' => 10,
            'page' => $this->params()->fromRoute('page',1),//页数
        );
        $list = $this->getModel("System")->marketingList($condition);
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'System',
                'action' => 'marketing',
                'where' => $list['where'],
                'page' => $condition['page'],
            ),
        ));
        $view->setTemplate('admin/system/marketing');
        return $this->setMenu2($view);
    }
    
    /**
     * 新增二维码的记录
     */
    public function addMarketingAction()
    {
        $this->checkLogin("System-marketing");
        if ($_POST)
        {
            $return = $this->getModel("System")->addMarketing();
            if($return['code'] !=200){
                $this->showMessage($return['message']);
            }
            return $this->redirect()->toRoute('admin', array('controller' => 'System','action' => 'marketing'));
        }
        $view = new ViewModel(array(
        ));
        $view->setTemplate('admin/system/addMarketing');
        return $this->setMenu2($view);
    }
    
    /**
     * 查看详情
     */
    public function getMarketingAction()
    {
        $this->checkLogin("System-marketing");
        $info = array();
        $return = array();
        $id = isset($_GET['id']) ? $_GET['id'] : $this->params()->fromRoute('id',0);
        if(!$id){
            $this->showMessage('请求参数不完整');
        }
        $return = $this->getModel("System")->getMarketing($id);
        if(isset($return['code'])){
            if($return['code'] !=200){
                $this->showMessage($return['message']);
            }else{
                $info = $return['info'];
            }
        }
        $view = new ViewModel(array(
            'info' => $info
        ));
        $view->setTemplate('admin/system/getMarketing');
        return $this->setMenu2($view);
    }
    
    /**
     * 异步打包下载二维码
     */
    function packQrcodeAction(){
        set_time_limit(0);
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        if(!$id){
            $this->showMessage('请求参数不完整');
        }
        $return = $this->getModel('System')->prckQrcode($id);
        exit;
    }
    
    /**************************************************************
     *                                                             *
     *          版块管理                                                                                                                                         *
     *                                                             *
     ***************************************************************/
    public function categoryAction()
    {
        $this->checkLogin("System-category");
        $condition = array(
            'page' => $this->params()->fromRoute('page',1),//页数
            'cid' => isset($_POST['cid']) ? $_POST['cid'] : $this->params()->fromRoute('cid',0),//账号状态
        );
        $list = $this->getModel("System")->categoryList($condition);
        $category_list = array(1=>'音频课程/音频课程包','视频课程/视频课程包');
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'System',
                'action' => 'category',
                'where' => $list['where'],
                'page' => $list['page'],
                'cid' => $list['cid'],
            ),
            'category_list' => $category_list,
            'cid' => $list['cid'],
        ));
        $view->setTemplate('admin/system/category');
        return $this->setMenu2($view);
    }
    
    /**
     * 禁用和启用
     * 
     * */
    public function categoryDeleteAction()
    {
        $this->checkLogin("System-category");
        $params = array(
            'id' => (int)$this->params()->fromRoute('id',0),
            'types' => (int)$this->params()->fromRoute('types',0),
        );
        $cid = $this->params()->fromRoute('cid',0);
        $pid = $this->params()->fromRoute('pid',0);
        if(!$params['id'] || !in_array($params['types'], array(1,2))){
            $this->showMessage('参数不正确！');
        }
        $list = $this->getModel("System")->setcategoryDelete($params);
        if($list['code'] == "200"){
            if($cid){
                return $this->redirect()->toRoute('admin', array('controller' => 'System','action' => 'categoryDetails','id' => $pid));
            }
            return $this->redirect()->toRoute('admin', array('controller' => 'System','action' => 'category'));
        }
        $this->showMessage($list['message']);
    }
    
    public function ajaxDeleteCategoryAction()
    {
        $this->checkLogin("System-category");
        $id = isset($_POST['id']) && $_POST['id']? $_POST['id']:0;
        $cid = $this->params()->fromRoute('cid',0);
        $pid = $this->params()->fromRoute('pid',0);
        if(!$id){
            $this->showMessage('参数不完整!');
        }else{
            $key = 'hdfksje93hjhf89j';
            $return = $this->getModel('System')->ajaxDeleteCategory($id,$key,$cid,$pid);
            if($return['code'] != 200){
                $this->showMessage($return['message']);
            }
        }
        if($cid){
            return $this->redirect()->toRoute('admin', array('controller' => 'System','action' => 'categoryDetails','id' => $pid));
        }
        return $this->redirect()->toRoute('admin', array('controller' => 'System','action' => 'category'));
    }
    
    public function addCategoryAction()
    {
         $this->checkLogin("System-category");
        if ($_POST)
        {
            $return = $this->getModel("System")->addCategory();
            if($return['code'] !=200){
                $this->showMessage($return['message']);
            }
            return $this->redirect()->toRoute('admin', array('controller' => 'System','action' => 'category'));
        }
        $category_list = array(1=>'音频课程/音频课程包','视频课程/视频课程包');
        $view = new ViewModel(array(
            'category_list' => $category_list,
        ));
        $view->setTemplate('admin/system/addCategory');
        return $this->setMenu2($view);
    }
    
    public function categoryDetailsAction()
    {
         $this->checkLogin("System-category");
        $id =  $this->params()->fromRoute('id');
        $condition = array(
            'page' => $this->params()->fromRoute('page',1),//页数
        );
        if ($_POST)
        {
            $return = $this->getModel("System")->addCategory();
            if($return['code'] !=200){
                $this->showMessage($return['message']);
            }
            echo "<script type='text/javascript'>history.go(-2);</script>";
            die();
        }
        $list = $this->getModel("System")->categoryDetails($condition,$id);
        $category_list = array(1=>'音频课程/音频课程包','视频课程/视频课程包');
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => array(
                'controller' => 'System',
                'action' => 'categoryDetails',
                'where' => $list['where'],
                'page' => $list['page'],
            ),
            'cate_list' => $list['cate_data'],
            'category_list' => $category_list,
        ));
        $view->setTemplate('admin/system/categoryDetails');
        return $this->setMenu2($view);
    }
    
    function addTwoCategoryAction(){
        $this->checkLogin("System-category");
        $list = $this->getModel('System')->addTwoCategory();
        if($list['code'] !=200){
            $this->showMessage($list['message']);
        }
        echo  json_encode($list);exit;
    }


    /*
     * 进入分销页面
     **/
    public function distributionAction()
    {
        $this->checkLogin("System-distribution");
        $page = $this->params()->fromRoute('page',1);//页数
        $where = array();
        $condition = array(
            'controller' => 'System',
            'action' => 'distribution',
            'where' => $where,
            'page' => $page,
        );
        $list = $this->getModel("System")->getDistribution($condition,true);
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => $condition,
        ));
        $view->setTemplate('admin/distribution/index');
        return $this->setMenu2($view);
    }

    /*
     *
     * 分销海报设置
     *
     **/
    public function posterAction()
    {
        $this->checkLogin("System-distribution");
        if(isset($_POST) && $_POST){
            $return = $this->getModel('System')->addPoster($_POST);
            if($return['code'] !=200){
                $this->showMessage($return['msg']);
            }
            echo "<script type='text/javascript'>history.go(-2);</script>";
            die();
        }
        $info = $this->getModel('System')->getPosterData();
        $view = new ViewModel(array('info' => $info));
        $view->setTemplate('admin/distribution/poster');
        return $this->setMenu2($view);
    }

    /*
   *
   * 分销等级奖励设置
   *
   **/
    public function awardAction()
    {
        $this->checkLogin("System-distribution");
        $page = $this->params()->fromRoute('page',1);//页数
        $where = array();
        $condition = array(
            'controller' => 'System',
            'action' => 'award',
            'where' => $where,
            'page' => $page,
        );
        $list = $this->getModel("System")->getAward($condition);
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => $condition,
        ));
        $view->setTemplate('admin/distribution/award');
        return $this->setMenu2($view);
    }

    /*
     * 新增分销等级数据
     *
     * */
    public function gradeAction()
    {
        $this->checkLogin("System-distribution");
        if(isset($_POST) && $_POST){
            $return = $this->getModel('System')->addGrade($_POST);
            if($return['code'] !=200){
                $this->showMessage($return['msg']);
            }
            echo "<script type='text/javascript'>history.go(-2);</script>";
            die();
        }
        $view = new ViewModel();
        $view->setTemplate('admin/distribution/grade');
        return $this->setMenu2($view);
    }

    /*
     * 删除分销等级
     *
     * */
    public function deleteAwardAjaxAction()
    {
        $this->checkLogin("System-distribution");
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id']  : 0;
        $set = array(
            'delete' => DELETE_TRUE,
        );
        $mes = $this->getModel('System')->deleteAwardAjax($id, $set);
        $this->ajax($mes);
    }

    /**
     * 下载分销数据excel
     * @version YSQ
     */
    public function getExcelAction(){
        $this->checkLogin("System-distribution");
        $condition = array(
            'page' => 1,
        );
        $data = $this->getModel('System')->setExcel($condition);
        $this->ajax($data['message']);
    }

}
?>