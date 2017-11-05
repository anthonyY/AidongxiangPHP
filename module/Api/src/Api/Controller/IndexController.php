<?php
namespace Api\Controller;

use Core\System\AiiUtility\AiiEncryptVerify\AiiEncryptVerify;
use Core\System\tls_sig_api_linux_64\TLSSig;
use Zend\Db\Sql\Where;
use Core\System\AiiUtility\AiiPush\AiiMyFile;
use Core\System\AiiUtility\AiiWxPayV3\AiiWxPayNotify;

class IndexController extends CommonController
{
    public function indexAction()
    { // APP接收总入口
        $json = isset($_REQUEST['json']) ? $_REQUEST['json'] : false;
        if (! $json) {
            die(1);
        }
        $json_object = json_decode($json, true); // 把JSON编码文件转为变量
        if (! $json_object) {
            $this->setResponse(STATUS_INCORRECT_FORMAT);
            $this->response(); // 发送到记录表
        }
        
        $className = isset($json_object['n']) ? trim($json_object['n']) : '';
        
        $this->namespace = isset($json_object['n']) ? $json_object['n'] : '';
        $this->session_id = isset($json_object['s']) ? $json_object['s'] : '';
        
        if (! $this->checkSubmit() ) {
            $this->response(STATUS_TOO_FAST);
        }
        
        // 验证协议安全性
        define('CHECK_API_MD5', false);
        // defined('CHECK_API_DEBUG_SWITCH') ? '' : define('CHECK_API_DEBUG_SWITCH', false); // 开启调试
        
        if (CHECK_API_MD5) {
            $AiiEncryptVerify = new AiiEncryptVerify();
            $check = $AiiEncryptVerify->check($json); // 验证
            if (! $check) {
                $this->response(STATUS_MD5);
            }
        }
        
        switch ($className) {
            case 'Session':
                $obj = new Session();
                break;
            case 'UploadFiles':
                $obj = new UploadFiles();
                break;
            case 'SMSCode':
                 $obj = new SMSCode();
                 break;
             case 'Setting':
                 $obj = new Setting();
                 break;
            default:
                $this->response(STATUS_NO_PROTOCOL);
                break;
        }
        
        $sm = $this->getServiceLocator();
        $obj->setServiceLocator($sm);
        
        $obj->initRequest();
        // $obj->startTime = $startTime;
        $response = $obj->index();
        if ($response) {
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
    function checkSubmit() {
        $json = $_REQUEST['json'];
        $key = md5(json_encode($json));
        //         $key = $json['s'] . '-' . $json['n'];
        if (isset($_SESSION[$key]) && time() - $_SESSION[$key] < 1) {
            return false;
        }
        else {
            $_SESSION[$key] = time();
            session_write_close();
            session_start();
            return true;
        }
    }
}
