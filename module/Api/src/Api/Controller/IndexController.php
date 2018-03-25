<?php
namespace Api\Controller;

use AiiLibrary\AiiEncryptVerify\AiiEncryptVerify;
use AiiLibray\AiiUtility\AiiPush\AiiMyFile;
use AiiLibray\WxPayApi\PayNotifyCallBack;
use Zend\View\Model\ViewModel;
use Zend\Captcha\Image as Captcha;
class IndexController extends CommonController
{
    //不需要全部设置跨域的域名
    public $httpOriginArray = ['http://ketxtest.aiitec.org','http://ketx.aiitec.org','http://localhost:8080', 'http://ketx.dev'];

    public function indexAction()
    {
        header("Access-Control-Allow-Origin:*"); //*号表示所有域名都可以访问
        $json = isset($_REQUEST['json']) ? $_REQUEST['json'] : false;
        if(!$json){
            die(111111);
        }
        $jsonArray = json_decode($json, true);
        if(!$jsonArray){
            $jsonArray = json_decode(base64_decode($json), true);
        }
        if(!$jsonArray){
            $this->response(STATUS_INCORRECT_FORMAT);
        }

        if(!$this->checkSubmit($jsonArray)){
            $this->namespace = isset($jsonArray['n']) ? $jsonArray['n'] : '';
            $this->session_id = isset($jsonArray['s']) ? $jsonArray['s'] : '';
            $this->response(STATUS_TOO_FAST);
        }

         //define('CHECK_API_DEBUG_SWITCH', true); // 开启调试
         require_once 'AiiLibrary/AiiEncryptVerify/AiiEncryptVerify.php';
         $AiiEncryptVerify = new AiiEncryptVerify();
         $check = $AiiEncryptVerify->check($json); // 验证
         if(!$check){
             $this->namespace = isset($jsonArray['n']) ? $jsonArray['n'] : '';
             $this->session_id = isset($jsonArray['s']) ? $jsonArray['s'] : '';
             $this->response(STATUS_MD5);
         }

        $className = isset($jsonArray['n']) ? trim($jsonArray['n']) : '';
        switch($className){
            case 'Session':
                $obj = new Session();
                break;
            case 'MessageSubmit':
                $obj = new MessageSubmit();
                break;
            case 'SMSCode':
                $obj = new SMSCode();
                break;
            case 'AdList':
                $obj = new AdList();
                break;
            case 'Setting':
                $obj = new Setting();
                break;
            case 'RegionList':
                $obj = new RegionList();
                break;
            case 'UploadImage':
                $obj = new UploadImage();
                break;
            case 'DeleteAction':
                $obj = new DeleteAction();
                break;
            case 'CategoryList':
                $obj = new CategoryList();
                break;
            case 'UserLogin':
                $obj = new UserLogin();
                break;
            case 'UserLogout':
                $obj = new UserLogout();
                break;
            case 'UserRegister':
                $obj = new UserRegister();
                break;
            case 'UserUpdateMobile':
                $obj = new UserUpdateMobile();
                break;
            case 'UserUpdatePassword':
                $obj = new UserUpdatePassword();
                break;
            case 'UserResetPassword':
                $obj = new UserResetPassword();
                break;
            case 'UserDetails':
                $obj = new UserDetails();
                break;
            case 'UserUpdateImage':
                $obj = new UserUpdateImage();
                break;
            case 'UserUpdate':
                $obj = new UserUpdate();
                break;
            case 'FavoritesSwitch':
                $obj = new FavoritesSwitch();
                break;
            case 'NavigationList':
                $obj = new NavigationList();
                break;
            case 'NewsList':
                $obj = new NewsList();
                break;
            case 'PaySubmit':
                $obj = new PaySubmit();
                break;
                break;
            case "ArticleList":
                $obj = new ArticleList();
                break;
            case "UserPartnerLogin":
                $obj = new UserPartnerLogin();
                break;
            case 'LabelList':
                $obj = new LabelList();
                break;
            case 'MobileAppealSubmit':
                $obj = new MobileAppealSubmit();
                break;
            case 'CommentSubmit':
                $obj = new CommentSubmit();
                break;
            case 'MicroblogList':
                $obj = new MicroblogList();
                break;
            case 'AudioList':
                $obj = new AudioList();
                break;
            case 'AudioDetails':
                $obj = new AudioDetails();
                break;
            case 'CommentList':
                $obj = new CommentList();
                break;
            case 'FansList':
                $obj = new FansList();
                break;
            case 'PraiseSwitch':
                $obj = new PraiseSwitch();
                break;
            case 'ScreenSwitch':
                $obj = new ScreenSwitch();
                break;
            case 'ReportSubmit':
                $obj = new ReportSubmit();
                break;
            case 'FocusSwitch':
                $obj = new FocusSwitch();
                break;
            case 'ArticleDetails':
                $obj = new ArticleDetails();
                break;
            default:
                $this->namespace = isset($jsonArray['n']) ? $jsonArray['n'] : '';
                $this->session_id = isset($jsonArray['s']) ? $jsonArray['s'] : '';
                $this->response(STATUS_NO_PROTOCOL);
                break;
        }

        $response = $obj->index();
        if($response){
            $obj->setResponse($response);
        }
        $obj->response();
        exit();
    }


    /**
     * 防止一秒请求多次
     *
     * @param unknown $json
     * @return boolean
     * @version 2015-5-26 WZ
     */
    function checkSubmit($json)
    {
        $key = md5(json_encode($json));
        //         $key = $json['s'] . '-' . $json['n'];
        if(isset($_SESSION[$key]) && time() - $_SESSION[$key] < 1){
            return false;
        }else{
            $_SESSION[$key] = time();
            session_write_close();
            @session_start();
            return true;
        }
    }

    /**
     * App支付宝异布回调
     *
     * @version 2014-12-24 liujun
     */
    public function getAlipayNotifyAction()
    {
        require_once 'AiiLibrary/AiiUtility/AiiPush/AiiMyFile.php';
        $myfile = new AiiMyFile();
        $myfile->setFileToPublicLog();
        $myfile->setFile(APP_PATH . '/public/uploadfiles/alipay_pay_' . date("Y-m-d") . '.txt');
        $content = var_export($_POST, TRUE);
        $myfile->putAtStart($content);//写日志
        require_once 'AiiLibrary/AliPay/AliPayApi.php';
        $alipay = new \AlipayApi();
        $verify_result = $alipay->notifyCheck();
        if($verify_result){ // 验证成功
            $myfile->putAtStart('支付状态：成功');//写日志
            // 商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            // 支付宝交易号
            $trade_no = $_POST['trade_no'];
            // 交易金额
            $total_fee = $_POST['total_amount'];
            // 交易状态
            $trade_status = $_POST['trade_status'];
            // 买家支付宝帐号
            $buyer_account = $_POST['buyer_email'];

            if(in_array($trade_status,array('TRADE_FINISHED','TRADE_SUCCESS')))
            {
                $res = $this->notifyTransacting($out_trade_no, $trade_no, 2);//回调业务处理
                if($res)
                {
                    echo "success"; // 请不要修改或删除
                }
            }
        }else{
            $myfile->putAtStart('支付状态：失败');//写日志
            // 验证失败
            echo "fail";
        }
        die();
    }


    /**
     * 微信异布回调
     *
     * @version 2015-4-10 liujun
     */
    public function getWxPayNotifyAction()
    {
        require_once 'AiiLibrary/WxPay/notify.php';
        require_once 'AiiLibrary/AiiUtility/AiiPush/AiiMyFile.php';

        $notify = new PayNotifyCallBack();
        $result = $notify->Handle(false);
        $myfile = new AiiMyFile();
        $myfile->setFileToPublicLog();
        $myfile->setFile(APP_PATH . '/public/uploadfiles/wx_pay_' . date("Y-m-d") . '.txt');
        $content = var_export($result, TRUE);
        $myfile->putAtStart($content);//写日志
        if($result['result_code'] == "SUCCESS" && $result['out_trade_no']){
            $myfile->putAtStart('支付状态：成功');//写日志
            $this->notifyTransacting($result['out_trade_no'], $result['transaction_id'], 1);//回调业务处理
        }else{
            $myfile->putAtStart('支付状态：失败');//写日志
        }
        die();
    }

    public function updatePayAction()
    {
        $res = $this->notifyTransacting(201710171908283333, 2017082421834469475, 1);//回调业务处理
        die('end');
    }

    //计划任务入口
    public function autoCompletePlanAction(){
        $file  = APP_PATH.'/public/uploadfiles/log.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个
        $content = 'execute the task - '.date('Y-m-d H:i:s')."\r\n";
        file_put_contents($file, $content,FILE_APPEND);
        //财务统计
        $financial = $this->getViewFinancialTable();
        $financial->getListForStatistics();
        //更新商品库存
        $goods = $this->getGoodsTable();
        $goods->stockType = 2;
        $goods->setTableColumns(array('id'));
        $goods->getUpdateStockList();

        //预购商品结束时间小于当前时间，下架预购商品
        $goods->goodsOffShelf();

        //自动收货
        $order = $this->getOrderTable();
        $order->autoCompleteRecive();
        //未支付订单自动取消订单
        $order->autoCancelOrder();
        //自动推送
        $notification = $this->getNotificationTable();
        $notification->autoPush();

        $ViewUserGroupBuyingTable = $this->getViewUserGroupBuyingTable();
        //拼团订单自动取消订单
        $ViewUserGroupBuyingTable->autoCancelGroupBuyingOrder();
//        $this->autoCompleteAction();
        die;
    }

    /**
     * 验证码生成
     *
     * @author liujun
     */
    public function getCaptchaAction()
    {
        $http_origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if (in_array($http_origin,$this->httpOriginArray))
        {
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Origin: $http_origin");
        }
        else
        {
            header("Access-Control-Allow-Origin:*"); //*号表示所有域名都可以访问
        }

        $session_id = isset($_GET['session_id']) ? $_GET['session_id'] : '';
        if(!$session_id)
        {
            die('请求参数不完整');
        }
        $captcha = new Captcha();
        $number = rand(1, 6);
        $language = __DIR__ . "/../../../language/$number.ttf";
        $captcha->setFont($language); // 字体路径
        $captcha->setImgDir(APP_PATH.'/public/uploadfiles/tmp/'); // 验证码图片放置路径
        $captcha->setImgUrl( APP_PATH.'/public/uploadfiles/tmp/');
        $captcha->setWordlen(4);
        $captcha->setFontSize(30);
        $captcha->setLineNoiseLevel(3); // 随机线
        $captcha->setDotNoiseLevel(30); // 随机点
        $captcha->setExpiration(10); // 图片回收有效时间
        $captcha->setUseNumbers(false);//设置验证码生成类型，true 字母加数字，false 字母
        $captcha->generate(); // 生成验证码
        //实例化redis
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->set($session_id,$captcha->getWord(),300);//将图形验证码写入redis
        echo '/uploadfiles/tmp/' . $captcha->getId() . $captcha->getSuffix(); // 图片路径
        die();
    }
}
