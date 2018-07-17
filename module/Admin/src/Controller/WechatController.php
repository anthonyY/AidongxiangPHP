<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use AiiLibrary\WechatMp\Wechat;

require_once 'AiiLibrary/WechatMp/wechat.class.php';
require_once 'AiiLibrary/phpqrcode/qrlib.php';

class WechatController extends CommonController
{
    const TOKEN = "xiaobao";
    const APPID = 'wxa030f5ac680d6c59';
    const APPSECRET = 'b929889c7ae27bd967768160feffeb69';
    private $redirectUri = 'http://test.aidongxiang.com/admin/wechat/agetUserInfo';
    private $wechat;
    private $openid;

    public function __construct()
    {
        $this->wechat = new Wechat(['token'=>self::TOKEN,'appid'=>self::APPID,'appsecret'=>self::APPSECRET]);
        parent::__construct();
    }

    public function indexAction()
    {
        $res = $this->wechat->valid();
        if(!$res)$this->wechat->text('认证失败')->reply();
        $revType = $this->wechat->getRev()->getRevType();
        switch ($revType){
            case 'event':
                $event = $this->wechat->getRevEvent();
                if($event && isset($event['event'])){
                    $path = UPLOAD_PATH;
                    $file = $this->createQrcodeByLink('http://baidu.com',$path);
                    if(!$file)$this->wechat->text('生成二维码失败')->reply();
                    $res = $this->uploadMedia($file);
                    if(!$res)$this->wechat->text('上传素材失败')->reply();
                    if($event['event'] === 'CLICK' && $event['key'] === 'V1001_spread'){
                        $this->wechat->image($res['media_id'])->reply();
                    }
                }
                break;
        }
    }

    /**
     * @param $menu
     * @return bool
     * $menu = array (
            'button' => array (
                0 => array (
                    "type"=>"click",
                    "name"=>"推广",
                    'key' => 'V1001_spread',
                ),
                1 => array (
                    'name' => '二级菜单',
                    'sub_button' => array (
                        0 => array (
                            'type' => 'pic_sysphoto',
                            'name' => '系统拍照发图',
                            'key' => 'rselfmenu_1_0',
                        ),
                        1 => array (
                            'type' => 'pic_photo_or_album',
                            'name' => '拍照或者相册发图',
                            'key' => 'rselfmenu_1_1',
                        )
                    ),
                ),
            ),
    );
     */
    public function createMenuAction($menu){
        return $this->wechat->createMenu($menu);
    }

    /**
     * @param $link
     * @param string $path
     * @param string $filename
     * @return bool|string
     * 根据url生成二维码
     */
    public function createQrcodeByLink($link,$path='',$filename=''){
        if(!$link)return false;
        if(!$filename)$filename = md5($link).'.png';
        $file = $path.$filename;
        if(!is_file($file)){
            \QRcode::png($link, $file, 'H', 10, 2);
        }
        return $file;
    }

    /**
     * @param $filename
     * @param string $type
     * @return array|bool
     * 上传素材到微信
     */
    public function uploadMedia($filename,$type='image'){
        if(!$filename)return false;
        $data = ["media"=>'@'.$filename];
        return $this->wechat->uploadMedia($data,'image');
    }

    /**
     * 授权跳转接口,微信服务器会带上code跳转到回调url
     */
    public function locateOauthRedirectAction(){
        $url = $this->wechat->getOauthRedirect($this->redirectUri,self::TOKEN);
        header("Location:".$url);
        exit;
    }

    /**
     * @return array|bool
     * 获取关注用户的详情
     */
    public function getUserInfoAction(){
        $this->getOauthAccessToken();
        if(!$this->openid){
            return false;
        }
        return $this->wechat->getUserInfo($this->openid);
    }

    /**
     * 发送模板消息
     * @param array $data 消息结构
     * ｛
            "touser":"OPENID",
            "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
            "url":"http://weixin.qq.com/download",
            "topcolor":"#FF0000",
            "data":{
                "参数名1": {
                    "value":"参数",
                    "color":"#173177"	 //参数颜色
                },
                "Date":{
                    "value":"06月07日 19时24分",
                    "color":"#173177"
                },
                "CardNumber":{
                    "value":"0426",
                    "color":"#173177"
                },
                "Type":{
                    "value":"消费",
                    "color":"#173177"
                }
            }
    }
     * @return boolean|array
     */
    public function sendTemplateMessage($templateId,$toUserOpenId,$url='',$topColor='#FF0000',$data=[]){
        if(!$templateId || !$toUserOpenId){
            return false;
        }
        $param_array = [
            'touser' => $toUserOpenId,
            'template_id' => $templateId,
            'topcolor' => $topColor?$topColor:'#FF0000',
        ];
        if($url)$param_array['url'] = $url;
        if($data)$param_array['data'] = $data;
        return $this->wechat->sendTemplateMessage($param_array);
    }

    /**
     * @return array {access_token,expires_in,refresh_token,openid,scope}
     * 通过code获取Access Token
     */
    public function getOauthAccessToken(){
        $result = $this->wechat->getOauthAccessToken();
        if($result){
            $this->openid = $result['openid'];
        }
        return $result;
    }

    /**
     * @param $content
     * @return bool|int
     * 记录日志
     */
    public function putFileLog($content){
        $filename = APP_PATH."/public/push_log/wechatMp.log";
        $data = is_array($content)?json_encode($content):$content;
        return file_put_contents($filename,date('Y-m-d H:i:s').PHP_EOL.$data.PHP_EOL,FILE_APPEND);
    }
}
