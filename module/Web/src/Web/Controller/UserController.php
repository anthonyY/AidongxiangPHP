<?php
namespace Web\Controller;


use Zend\View\Model\ViewModel;
class UserController extends CommonController
{

    function __construct()
    {
        parent::__construct();
        $this->controller = 'User';
        $this->module = 'web';
    }
    
    function indexAction() {
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $is_logout = isset($_GET['logout']) ? $_GET['logout'] : 0;
        if(!$is_logout)
        {
            $page_type = array(
                'page_type' => 5,
                'page_id' => 0,
                'detail_type' => 0
            );
            $this->checkWebLogin($page_type);
            $data = $this->getModel('User')->getUserIndex();
            $user = $this->getModel('User')->userDetails($_SESSION['user_id']);
            $is_perfect = isset($_SESSION['is_perfect']) ? $_SESSION['is_perfect'] : 0;
        }
        else 
        {
            $data = $this->getModel('User')->getSetup();
        }
        if($data['code']==400){
            $this->showMessage($data['message']);
        }
        if(!$is_logout)
        {
            return $this->setMenu(array('data' => $data,'is_logout' => $is_logout,'is_perfect'=>$is_perfect,'user'=>$user));
        }
        else 
        {
            return $this->setMenu(array('data' => $data,'is_logout' => $is_logout));
        }
        
    }
    
    function favoriteAction() {
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu(array('user_id' => $_SESSION['user_id']));
    }
    
    function ajaxGetFavoriteListAction(){
        $data = $this->getModel('User')->ajaxGetFavoriteList();
        echo json_encode($data);
        die();
    }
    
    function ajaxDeleteCollectAction(){
        $data = $this->getModel('User')->ajaxDeleteCollect();
        echo json_encode($data);
        die();
    }
    
    function ajaxDeleteFavoriteAllAction(){
        $data = $this->getModel('User')->ajaxDeleteFavoriteAll();
        echo json_encode($data);
        die();
    }
    function coursepackAction()
    {
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu();
    }
    
    function ajaxGetCoursepackAction()
    {
        $data = $this->getModel('User')->ajaxGetCoursepack();
        echo json_encode($data);
        die();
    }
    //用户详情
    public function userDetailsAction(){
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $id = $this->params()->fromRoute('id',$_SESSION['user_id']);
        if(!$id){
            $this->showMessage("查询失败！");
        }
        $data = $this->getModel('User')->userDetails($id);
        if($data['code'] != 200){
            $this->showMessage("查询失败！");
        }
        return $this->setMenu(array('data' => $data));
    }
    
    //设置
    public function accountSetAction(){
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu();
    }
    
    //关于觉鸟
    public function aboutNightBirdAction(){
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu();
    }
    
    //账号与安全
    public function accountSafetyAction(){
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $this->checkWebLogin();
        $data = $this->getModel("User")->wallet();
        if($data['code'] != 200)
        {
            $this->showMessage($data['message']);
        }
        return $this->setMenu(array('data'=>$data['data']));
    }
    
    //用户帮助
    public function userHelpAction(){
        $this->action = substr(__FUNCTION__, 0 ,-6);
        // 1 用户帮助 2 功能介绍
        $page = array(
            'page_type'=> 5,
            'page_id' => 14,
            'detail_type' =>0
        );
        $this->addDataDetails($page,date('Y-m-d',time()));
        $type = $this->params()->fromRoute('type',1);
        $data = $this->getModel('User')->userHelp($type);
        if(isset($data['code']) && $data['code'] == 200){
            echo json_encode($data);
            die();
        }
        return $this->setMenu(array('data' => $data,'type' => $type));
    }
    
    //用户帮助/功能介绍
    public function introduceAction(){
        $this->action = substr(__FUNCTION__, 0 ,-6);
        // 1 用户帮助 2 功能介绍
        $type = $this->params()->fromRoute('type',1);
        $id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : $this->params()->fromRoute('id'); 
        if(!$id){
             $this->showMessage("查询失败！");
        }
        $data = $this->getModel('User')->introduce($type,$id);
        return $this->setMenu(array('data' => $data,'type' => $type));
    }
    
    //意见反馈
    public function feedBackAction(){
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu(array('user_id' => $_SESSION['user_id']));
    }
    
    //新增意见反馈
    public function addFeedBackAction(){
        $data = $this->getModel('User')->addFeedBack();
        echo json_encode($data);
        die();
    }
    
    //我爱分享
    public function ishareAction(){
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $this->checkWebLogin();
        //获取分享信息
//        $ticket = '';
        $data = $this->getModel('User')->ishare();
//        $json_data = '{"expire_seconds": 2592000, "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "jueniao"}}}';
        //获取公众号二维码
//        $ticket = $this->getModel('ScanCode')->getWeixinCode($json_data);
        return $this->setMenu(array('data' => $data/*,'ticket' => $ticket*/));
    }
    
    //购买记录
    public function buyRecordAction(){
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu();
    }
    
    //消息通知
    public function messageInformAction(){
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu();
    }
    
    //意见反馈通知
    public function ideaFeedbackAction(){
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu();
    }
    
    //系统通知
    public function systemMassageAction(){
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu();
    }
    
    //系统通知详情
    public function messageDetailsAction(){
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu();
    }
    
    //赠送记录
    public function presentRecordAction(){
        $this->checkWebLogin();
        $type = isset($_GET['type']) ? $_GET['type'] : "";
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu(array('type' => $type));
    }
    
    //修改用户信息
    public function ajaxAmendUserAction(){
        $data = $this->getModel('User')->ajaxAmendUser();
        echo json_encode($data);
        die();
    }
    
    //修改用户名字
     public function changeNameAction(){
         $this->action = substr(__FUNCTION__, 0 ,-6);
         $this->checkWebLogin();
         $id = $this->params()->fromRoute('id');
         $type = $this->params()->fromRoute('type',1);
         $data = $this->getModel('User')->userDetails($id);
         if($type == 1){
             return $this->setMenu(array('data' => $data));
         }else{
             return $this->setMenu(array('data' => $data),'web/User/signature');
         }
         
     }
     
     /**
      * 我的钱包
      */
     public function walletAction()
     {
         $type = $this->params()->fromRoute('type',1);
         $page_type = array();
         if($type == 2){
             $page_type = array(
                 'page_type'=> 5,
                 'page_id' => 23,
                 'detail_type' =>0
             );
         }
         $this->checkWebLogin($page_type);
         $this->action = substr(__FUNCTION__, 0 ,-6);
         $data = $this->getModel("User")->wallet();
         if($data['code'] != 200)
         {
             $this->showMessage($data['message']);
         }
         if($type == 1){
             return $this->setMenu(array('data'=>$data['data']));
         }else{
             return $this->setMenu(array('data'=>$data['data']),'web/User/brokerage');
         }

     }
     
     public function rechargeIndexAction()
     {
         $this->checkWebLogin();
         if(strpos($_SERVER["HTTP_REFERER"],"wallet")){
             $type = 2;
             $url = "";
         }else{
             $type = 1;
             $url = $_SERVER["HTTP_REFERER"];
             $domain = strstr($url, '?');
             
             if(!$domain){
                 $url .= '?type=1';
             }else{
                 $url .= '&type=1';
             }
         }
         $this->action = substr(__FUNCTION__, 0 ,-6);
         $data = $this->getModel("User")->rechargeIndex();
         if($data['code'] != 200)
         {
             $this->showMessage($data['message']);
         }
         return $this->setMenu(array('type' => $type,'url' => $url,'user'=>$data['data']['user'],'top_up'=>$data['data']['top_up'],'moneys_array'=>$data['data']['moneys_array']));
     }
     
     public function financeAction()
     {
         $this->checkWebLogin();
         $this->action = substr(__FUNCTION__, 0 ,-6);
         return $this->setMenu();
     }
     
     public function changeMobileAction()
     {
         $this->action = substr(__FUNCTION__, 0 ,-6);
         return $this->setMenu();
     }
     
     public function captchaAction()
     {
         $this->checkWebLogin();
         header('Access-Control-Allow-Origin: *');
         $mobile = $_GET['tell'] ? $_GET['tell'] : 0;
         $regxPhone = "/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57]|17[0-9])[0-9]{8}$/";
         if(!preg_match ( $regxPhone, $mobile ))
         {
             $this->showMessage("手机号码不正确!");
         }
         $this->action = substr(__FUNCTION__, 0 ,-6);
         return $this->setMenu(array('mobile'=>$mobile));
     }
     
     public function ajaxGetBuyLogAction(){
        $data = $this->getModel("User")->ajaxGetBuyLog();
        echo json_encode($data);
        die();
     }
     
     //获取赠送列表数据
     public function ajaxPresentRecordListAction(){
         $data = $this->getModel('User')->ajaxPresentRecordList();
         echo json_encode($data);
         die();
     }
     
     //赠送详情页
     public function presentRecordDetailAction(){
         $this->checkWebLogin();
         $this->action = substr(__FUNCTION__, 0 ,-6);
         $data = $this->getModel('Index')->getIndex(2);
//          var_dump($data);exit;
         $ads = $data['ads'];
         if($ads)
         {
             foreach ($ads as $v)
             {
                 $url = '';
                 switch ($v['type'])//1 图文消息 2 音频课程 3 视频课程 4 音频包课程 5 视频包课程 6 外部链接
                 {
                     case 2:
                         $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'audio','action' => 'details','type'=>1,'id'=>$v['audio_id']));
                         break;
                     case 3:
                         $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'video','action' => 'details','type'=>1));
                         $url = $url."?id=".$v['audio_id'];
                         break;
                     case 4:
                         $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'audio','action' => 'details','type'=>2,'id'=>$v['audio_id']));
                         break;
                     case 5:
                         $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'video','action' => 'details','type'=>2));
                         $url = $url."?id=".$v['audio_id'];
                         break;
                     case 6:
                         $url = $v['link'];
                         break;
                     default:
                         $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'index','action' => 'articleDetails','id'=>$v['id']));
                         break;
                 }
                 $v['link'] = $url;
             }
         }
         $id = $this->params()->fromRoute('id',0);
         $data = $this->getModel('User')->PresentRecordDetails($id);
         if($data['code'] != 200)
         {
             $this->showMessage($data['message']);
         }
         if(isset($data['return_type']) && $data['return_type'] == 'member'){
             return $this->setMenu(array('ads'=>$ads,'data' => $data),'web/User/presentMember');
         }else{
             return $this->setMenu(array('ads'=>$ads,'data' => $data));
         }         
     }

     public function ajaxGetCourseAction(){
         $data = $this->getModel('User')->ajaxGetCourse();
         echo json_encode($data);
         die();
     }
     
     public function ajaxGetPresentMbmberAction(){
         $data = $this->getModel('User')->ajaxGetPresentMbmber();
         echo json_encode($data);
         die();
     }

     
     public function ajaxGetFinanceAction()
     {
         $this->checkLogin();
         $type = $this->params()->fromRoute('type',1);
         $data = $this->getModel("User")->finance($type);
         echo json_encode($data);
         die();
     }
     
     //消息通知首页ajax
     public function ajaxGetmessageInformAction(){
         $data = $this->getModel("User")->ajaxGetmessageInform();
         if($data['data'])
         {
             foreach ($data['data'] as $v)
             {  
                 $v['timestamp'] = $this->format_date(strtotime($v['timestamp']));
                 if($v['message_type'] == 3)
                 {
                     if($v['type'] == 1)//音频
                     {
                         $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'audio','action' => 'details','type'=>1));
                         $url = $url."?id=".$v['audio_id'];
                         $v['title'] = "发布了音频课程<".$v['title'].">";
                     }
                     else //视频
                     {
                         $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'video','action' => 'details','type'=>1));
                         $url = $url."?id=".$v['audio_id'];
                         $v['title'] = "发布了视频课程<".$v['title'].">";
                     }
                     $v['link'] = $url;
                 }
                 if($v['message_type'] == 2)
                 {
                     $v['title'] = "回复：".$v['title'];
                 }
             }
         }
         echo json_encode($data);
         die();
     }

    public function format_date($time)
    {
        $t = time() - $time;
        $f = array(
            '31536000' => '年',
            '2592000' => '个月',
            '604800' => '星期',
            '86400' => '天',
            '3600' => '小时',
            '60' => '分钟',
            '1' => '秒'
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int) $k)) {
                return $c . $v . '前';
            }
        }
    }
     
    public function ajaxSystemMassageAction(){
        $data = $this->getModel("User")->ajaxSystemMassage();
        if($data['data'])
        {
            foreach ($data['data'] as $v)
            {
                $url = '';
                switch ($v['type'])//1 图文消息 2 音频课程 3 视频课程 4 音频包课程 5 视频包课程 6 外部链接
                {
                    case 2:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'audio','action' => 'details','type'=>1,'id'=>$v['audio_id']));
                        break;
                    case 3:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'video','action' => 'details','type'=>1));
                        $url = $url."?id=".$v['audio_id'];
                        break;
                    case 4:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'audio','action' => 'details','type'=>2,'id'=>$v['audio_id']));
                        break;
                    case 5:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'video','action' => 'details','type'=>2));
                        $url = $url."?id=".$v['audio_id'];
                        break;
                    case 6:
                        $url = $v['link'];
                        break;
                    default:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'index','action' => 'articleDetails','id'=>$v['id'],'type'=>"1"));
                        break;
                }
                $v['link'] = $url;
//                 $v['timestamp'] = substr($v['timestamp'], 14);
                $v['image'] = $v['image'] ? ROOT_PATH.UPLOAD_PATH.$v['image'] : 0;
            }
        }
        echo json_encode($data);
        die();
    }
    
    
    public function ajaxIdeaFeedbackAction(){
        $this->checkWebLogin();
        $data = $this->getModel("User")->ajaxIdeaFeedback();
        echo json_encode($data);
        die();
    }
    
    public function watchRecordAction()
    {
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu();
    }
    
    public function ajaxWatchRecordAction()
    {
        $data = $this->getModel("User")->ajaxWatchRecord();
        echo json_encode($data);
        die();
    }
    
    public function deleteRecordAction()
    {
        $data = $this->getModel("User")->deleteRecord();
        echo json_encode($data);
        die();
    }
    
    public function deleteAllRecordAction()
    {
        $data = $this->getModel("User")->deleteAllRecord();
        echo json_encode($data);
        die();
    }
    
    public function memberAction()
    {
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $data = $this->getModel("User")->getMember();
        $type = $_GET['type'] ? $_GET['type'] : $this->params()->fromRoute('type',1);
        if($type == 1){
            return $this->setMenu(array('data' => $data));
        }else{
            return $this->setMenu(array('data' => $data),'web/User/isMember');  
        }
       
    }
    
    public function regionAction(){
        $type = $this->params()->fromRoute('type',0);
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu(array('type'=>$type));
    }
    
    public function ajaxGetRegionAction(){
        $data = $this->getModel('User')->getRegion();
        echo json_encode($data);
        die();
    }
    
    public function sonRegionAction(){
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $type = $this->params()->fromQuery('type',0);
        $data = $this->getModel('User')->getSonRegion();
        return $this->setMenu(array('data' => $data,'type'=>$type));
    }
    
    public function ajaxAddRegionAction(){
        $data = $this->getModel('User')->ajaxAddRegion();
        echo json_encode($data);
        die();
    }
    
    //充值
    public function ajaxRechargeAction(){
        $data = $this->getModel('User')->ajaxRecharge();
        if($data['code'] == 200){
            if(IS_DEBUG == 1){
                $data['is_debug'] = 1; 
            }else{
                $open_id = $_SESSION['wx_open_id'];
                if(!$open_id){
                    $this->checkWebLogin();
                }
                $data = $this->getWxPayInfo($data['out_trade_no'],$data['price'],$data['message'],$open_id);
                if($data){
                    $data['code'] = 200;
                    $data['is_debug'] = 2;
                }else{
                    $data = array(
                        'code' => 400,
                        'message' => '充值失败！'
                    );
                }
            }
        }
        echo json_encode($data);
        die();
    }
    
    //购买会员
    public function ajaxMemberAction(){
       $data = $this->getModel('User')->ajaxMember();
       if($data['code'] == 200 && $data['pay_ment'] == 1){
           $open_id = $_SESSION['wx_open_id'];
           if(!$open_id){
               $this->checkWebLogin();
           }
           $data = $this->getWxPayInfo($data['out_trade_no'],$data['price'],$data['message'],$open_id);
           if($data){
               $data['code'] = 200;
               $data['pay_ment'] = 1;
           }else{
               $data = array(
                   'code' => 400,
                   'message' => '购买会员失败！'
               );
           }
       }
        echo json_encode($data);
        die();
    }
    
    //购买课程调微信
    public function ajaxWxCoursePayAction(){
        $obj = isset($_POST['obj']) ? (array)$_POST['obj'] : array();
        $data = $this->getModel('User')->ajaxWxCoursePay($obj);
        if($data['code'] == 200){
            $open_id = $_SESSION['wx_open_id'];
            if(!$open_id){
                $this->checkWebLogin();
            }
            $data = $this->getWxPayInfo($data['out_trade_no'],$data['price'],$data['message'],$open_id);
            if($data){
                $data['code'] = 200;
                $data['is_debug'] = 2;
            }else{
                $data = array(
                    'code' => 400,
                    'message' => '课程购买失败！'
                );
            }
        }
        echo json_encode($data);
        die();
    }
    
    
    
    public function ajaxLogoutAction()
    {
        session_destroy();
        echo json_encode(array('code'=>200));
        die();
    }
    
    //获取定位信息
    public function getLocationInfoAction()
    {   
        $location = $this->getModel('User')->getLocationInfo();
        echo json_encode($location);
        die();
    }
    
    public function ajaxUpdateUserInfoAction()
    {
        $data = $this->getModel('User')->ajaxUpdateUserInfo();
        echo json_encode($data);
        die();
    }

    //佣金充值到钱包页面
    public function rechargeWalletAction(){
        $type = $this->params()->fromRoute('type',0);
        if($type == 1){
            $page_type = array(
                'page_type'=> 5,
                'page_id' => 24,
                'detail_type' =>0
            );
        }else{
            $page_type = array(
                'page_type'=> 5,
                'page_id' => 25,
                'detail_type' =>0
            );
        }
        $this->checkWebLogin($page_type);
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $data = $this->getModel("User")->wallet();
        return $this->setMenu(array('user'=>$data['data'],'type' => $type));
    }

    //佣金充值到钱包/提现
    public function ajaxRechargeWalletAction()
    {
        $this->checkWebLogin();
        $type = $this->params()->fromRoute('type',0);
        if(!$type){
            $this->ajax(array('code' => 400,'msg'=>'缺少类型'));
        }
        list($status,$msg) = $this->getModel('User')->ajaxRechargeWallet($type);
        if($status){
            $this->ajax(array('code' => 200,'msg' => 'OK'));
        }else{
            $this->ajax(array('code' => 400,'msg' => $msg));
        }
    }

    //佣金明细
    public function brokerageFinanceAction()
    {
        $page_type = array(
            'page_type'=> 5,
            'page_id' => 26,
            'detail_type' =>0
        );
        $this->checkWebLogin($page_type);
        $this->action = substr(__FUNCTION__, 0 ,-6);
        return $this->setMenu();
    }

    //图片合成生成
    public function getPhotofuniaAction(){
        if(!$img = $this->getModel('User')->getImg()){
            $this->ajax(array('code' => 400));
        }else{
            $this->ajax(array('code' => 200,'img' => $img));
        }
    }

    //分销页面内容
    public function getDisContentAction(){
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $data = $this->getModel('User')->getDisData();
        return $this->setMenu(array('data' => $data));
    }

    //我的推荐页面
    public function recommendAction(){
        $this->checkWebLogin();
        $this->action = substr(__FUNCTION__, 0 ,-6);
        $user = $this->getModel('User')->getUserDetails();
        return $this->setMenu(array('user' => $user));
    }


    //获取我的推荐数据
    public function ajaxGetRecommendAction()
    {
        $this->checkLogin();
        $data = $this->getModel("User")->getRecommend();
        echo json_encode($data);
        die();
    }
}
