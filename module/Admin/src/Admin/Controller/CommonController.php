<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/23
 * Time: 15:53
 */
namespace Admin\Controller;

use Web\Model\ScanCodeModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Captcha\Image as imageCaptcha;
use Api\Model\SessionModel;
use Web\Model\IndexModel;
use Web\Model\AudioModel;
use Web\Model\VideoModel;
use Web\Model\UserModel;
use Web\Model\TutorModel;
use Api\Model\SMSCodeModel;
use Admin\Model\SystemModel;
use Admin\Model\ContentListModel;
use Admin\Model\IndexAdminModel;


class CommonController extends AbstractActionController
{
    protected $albumTable;
    protected $action;
    protected $module;
    protected $controller;

    /**
     * @return IndexModel
     * @return VideoModel
     * @return AudioModel
     * @return UserModel
     * @return SMSCodeModel
     * @return SystemModel
     * @return ContentListModel
     * @return ScanCodeModel
     * @return IndexAdminModel
     * @version 2016-9-2
     */
    public function getModel($name)
    {

        if (!isset($this->albumTable[$name])) {
            $sm = $this->getServiceLocator();
            $this->albumTable[$name] = $sm->get($name);
        }
        return $this->albumTable[$name];
    }


    /**
     * 设置模板跟菜单
     *
     * @param unknown $mainView
     * @param string $controller
     * @return \Zend\View\Model\ViewModel
     */
    protected function setMenu($data = array(), $template = '', $layout = array())
    {
        $data = (array) $data;
        $data ['action'] = $this->action;
        $data ['module'] = $this->module;
        $data ['controller'] = $this->controller;
        $contentView = new ViewModel($data);
        if ($template) {
            $contentView->setTemplate($template);
        }
        else {
            $contentView->setTemplate($this->module . '/' . $this->controller . '/' . $this->action);
        }

        return $contentView;
    }
    

    /**
     * 设置模板跟菜单(后台专用)
     *
     * @param unknown $mainView
     * @param string $controller
     * @return \Zend\View\Model\ViewModel
     */
    protected function setMenu1($mainView, $controller = '', $module = 1)
    {
        $layout = new ViewModel(array(

        ));
        $layout->setTemplate('admin/layout');
        $count = array();
        $menuView = new ViewModel(array(
        ));

        $menuView->setTemplate('admin/menu');
        $menuView->addChild($mainView, 'main');
        $layout->addChild($menuView, 'content');
        return $layout;
    }

    /**
     * 设置模板跟菜单(后台专用)
     *
     * @param unknown $mainView
     * @param string $controller
     * @return \Zend\View\Model\ViewModel
     */
    protected function setMenu2($mainView, $controller = '', $module = 1)
    {
        $layout = new ViewModel(array(
    
        ));
        $layout->setTemplate('admin/layout');
        $count = array();
        $menuView = new ViewModel(array(
        ));
    
        $menuView->setTemplate('admin/menu2');
        $menuView->addChild($mainView, 'main');
        $layout->addChild($menuView, 'content');
        return $layout;
    }
    
    protected function weixinTemplate($templatePath, $array=null)
    {
        $view = new ViewModel($array);
        $data ['action'] = $this->action;
        $data ['module'] = $this->module;
        $data ['controller'] = $this->controller;
        $layout['module'] = $this->module;
        if ($templatePath) {
            $view->setTemplate($templatePath);
        }
        else {
            $view->setTemplate($this->module . '/' . $this->controller . '/' . $this->action);
        }
        $mianView = new ViewModel($layout);
        $mianView->setTemplate('layout/layout-web');
        $mianView->addChild($view,'content');
        return $mianView;
    }
    
    public function showMessage($message, $type = true)
    {
        $location = $type ? "history.back(-1);" : '';
        echo "<script type='text/javascript'>alert('{$message}');{$location}</script>";
        die();
    }

    public function apiExit()
    {
        echo "success";
        die;
    }

    public function p($mes=null)
    {
        echo "<pre>";
        if($mes)
        {
            print_r($mes);
        }
        echo "<pre>";die;
    }

    public function ajax($mes)
    {
        echo json_encode($mes);die;
    }

    /**
     * 验证码
     */
    public function index2Action()
    {
        include_once APP_PATH . '/vendor/Core/System/IdentifyCode.php';
        $checkCode='';
        $chars='abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPRSTUVWXYZ23456789';
        for($i=0;$i<4;$i++){
            $checkCode.=substr($chars,mt_rand(0,strlen($chars)-1),1);
        }
        $_SESSION['code']=strtolower($checkCode);// 记录session
        ImageCode($checkCode,60);// 显示GIF动画
        die;
    }
    
    /**
     * 验证码生成方法2
     */
     public function generateCaptchaAction()
    {
        $captcha = new imageCaptcha();
        $number = 2;
        $language = __DIR__ . "/../../../language/$number.ttf";
        $captcha->setFont($language); // 字体路径
        $captcha->setImgDir('public/' . UPLOAD_PATH . 'captcha/'); // 验证码图片放置路径
        $captcha->setImgUrl(ROOT_PATH . 'uploadfiles/captcha/');
        $captcha->setWordlen(5);
        $captcha->setFontSize(30);
        $captcha->setLineNoiseLevel(4); // 随机线
        $captcha->setDotNoiseLevel(40); // 随机点
        $captcha->setExpiration(10); // 图片回收有效时间
        $captcha->generateSimple(); // 生成验证码
        $_SESSION['code'] = $captcha->getWord();
        echo $captcha->getImgUrl() . $captcha->getId() . $captcha->getSuffix(); // 图片路径
        die();
    }
    
    public function clearCookies() {
        $_SESSION('admin_mobile','',time() - 600,ROOT_PATH);
        $_SESSION('admin_name','',time() - 600,ROOT_PATH);
        $_SESSION('admin_id','',time() - 600,ROOT_PATH);
        $_SESSION('admin_real_name','',time() - 600,ROOT_PATH);
    }

    protected function quit()
    {
        session_destroy();
        return $this->redirect()->toRoute('admin', array('controller' => 'admin', 'action' => 'index',));die;
    }

    /**
     * 判断登录和权限
     * @param string $moduleName
     * @param string $controller
     * @return boolean|Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     * @version 2016-9-5 WZ
     */
    public function checkLogin($controller='')
    {
        if(isset($_SESSION['role_nb_admin_id']) && isset($_SESSION['role_nb_admin_name']))
        {
            if($controller)
            {
                $list = $this->getModel("Admin")->authority($controller);
                if($list['code']==400)
                {
                    $this->showMessage("{$list['message']}");
                }
                else{
                    return true;
                }
            }else{
                return false;
            }
        }
        else
        {
            return $this->redirect()->toRoute('admin', array('controller' => 'admin','action' => 'index'));
        }
    }
    
                
    /**
     * 上传文件
     * @version YSQ
     */
    public function uploadImageAction()
    {
        $list = $this->getModel("Other")->uploadImage('Filedata');
        $this->ajax($list);
    }
    
    /**
     * ajax请求短信验证码和验证
     *
     * @version 2015-10-16 WZ
     */
    public function ajaxSmscodeAction() {
        $action = isset($_POST['action']) ? (int) $_POST['action'] : 1; // 1发送；2验证；
        $type = isset($_POST['type']) ? (int) $_POST['type'] : 1; // 1注册；
        $code = isset($_POST['code']) ? $_POST['code'] : '';
        $captcha = isset($_POST['captcha']) ? $_POST['captcha'] : '';
        $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
        if ($action == 1) {
            // 发送
            if (empty($_SESSION['code'])) {
                $result = array('code' => '400', 'message' => '请刷新页面');
            }else
            if ($_SESSION['code'] != strtolower($captcha)) {
                $result = array('code' => '401', 'message' => '图形验证码不正确');
            }
            else {
                $smscode = $this->getModel('SMSCode');
                $result = $smscode->sendCode($mobile, $type);
                $_SESSION['code'] = null;
            }
        }
        else {
            // 验证
            if (! $code) {
                echo array('code' => '402', 'message' => '请输入图形验证码');
            }
            else {
                $smscode = $this->getModel('SmSCode');
                $result = $smscode->checkMobileCode($type, $mobile, $code);
            }
        }
        
        echo json_encode($result);
        exit;
    }
    
/**
     * 跳转到上一页
     * 
     * @param string $controller
     * @param string $action
     * @version 2015-12-8 WZ
     */
    public function redirectReferer($controller = 'user', $action = 'index') {
        if (isset($_SESSION['WEB_HTTP_REFERER']) && $_SESSION['WEB_HTTP_REFERER']) {
            $url = $_SESSION['WEB_HTTP_REFERER'];
            $_SESSION['WEB_HTTP_REFERER'] = '';
            return $this->redirect()->toUrl($url);
        }
        return $this->redirect()->toRoute($this->module, array('controller' => $controller, 'action' => $action));
    }
    
    /**
     * 设置上一页
     * 
     * @version 2015-12-10 WZ
     */
    public function setReferer() {
        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';// REDIRECT_URL
        $pass_keys = array(
            'login', // 登录
            'register', // 注册
        );
        foreach ($pass_keys as $key) {
            if (strpos($url, $key) !== false) {
                $url = '';
            }
        }
        if ($url) {
            $_SESSION['WEB_HTTP_REFERER'] = $url;
        }
    }
   
    /**
     * 返回角色权限模块
     *
     * @author arong
     */
    public function getRoleAuthority(){
        return array(
            'role' => array(
                '主页'  => array(
                    '主页' => 'Index',
                ),
                '新增'  => array(
                    '新增' => 'Index-addList',
                ),
                '内容列表' => array(
                    '音频课程' => 'ContentList-videoList',
                    '视频课程' => 'ContentList-videoList2',
                    '课程包' => 'ContentList-coursesList',
                    '觉鸟导师' => 'ContentList-teacherList',
                    '轮播图' => 'Ads-relation',
                    '用户寄语' => 'System-sendWord',
                    '今日推荐' => 'Ads-todayAds',
                    '分享设置' => 'ContentList-share',
                    '敏感词管理' => 'ContentList-sensitiveWords',
                    '首页设置' => 'ContentList-homePageSetting',
                    '搜索词管理' => 'ContentList-search',
                ),
                '评论管理'  => array(
                   '评论管理' => 'Comment',
                ),
                '意见反馈' => array(
                   '意见反馈' => 'Feedback',
                ),
                '用户管理' => array(
                    '用户管理' => 'User',
                ),
                '财务管理' => array(
                    '财务管理' => 'Order',
                ),
                '系统管理' => array(
                    '用户帮助' => 'System-userHelp',
                    '用户职位' => 'System-userJob',
                    '系统消息' => 'System-system',//
                    '营销工具' => 'System-marketing',
                    '分销设置' => 'System-distribution',
                    '功能介绍' => 'System-userHelp2',
                    '板块管理' => 'System-category',
                    '会员管理' => 'System-setMember',
                    '首页管理' => 'System-index',
                    '充值管理' => 'System-setTopUp',
                    '注册协议' => 'System-protocol',
                    '职务管理' => 'System-role',
                    '管理员' => 'System-roleList',
                ),
                '集团会员' => array(
                    '集团会员' => 'GroupMember',
                ),
            ),
        );
    }

    /**
     * 跳转类型
     * @return array
     * @version YSQ
     */
    function jumpType(){
        return array(
            '1' => '图文消息',
            '2' => '音频',
            '3' => '视频',
            '4' => '音频包',
            '5' => '视频包',
            '6' => '外部链接',
        );
    }
}