<?php
namespace Core\System\AiiUtility\AiiWxPayV3;

/**
 * ******************************************************
 *
 * @author Kyler You <QQ:2444756311>
 * @link http://mp.weixin.qq.com/wiki/home/index.html
 * @version 2.2
 * @uses $wxApi = new WxApi();
 * @package 微信API接口 陆续会继续进行更新
 *          ******************************************************
 */
class WxApi
{

    private $appId = WEIXIN_APP_ID; // 应用ID

    private $appSecret = WEIXIN_APP_SECRET; // 应用密钥

    private $mchid = WEIXIN_MCHID; // 商户号

    private $privatekey = WEIXIN_PRIVATEKEY; // 私钥

    public $parameters = array();

    public $jsApiTicket = null;

    public $jsApiTime = null;

    private $cachePath = null;

    public function __construct($appId, $appSecret, $mchid, $privatekey)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->mchid = $mchid;
        $this->privatekey = $privatekey;
        $this->cachePath = defined('APP_PATH') ? APP_PATH . '/Cache/' : __DIR__ . '/';
    }

    /**
     * **************************************************
     * 微信提交API方法，返回微信指定JSON
     * **************************************************
     */
    public function HttpsRequest($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (! empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * **************************************************
     * 微信带证书提交数据 - 微信红包使用
     * **************************************************
     */
    public function HttpsRequestPem($url, $vars, $second = 30, $aHeader = array())
    {
        $ch = curl_init();
        // 超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 这里设置代理，如果有的话
        // curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');//HTTP代理通道。
        // curl_setopt($ch,CURLOPT_PROXYPORT, 8080);//代理服务器的端口。端口也可以在CURLOPT_PROXY中进行设置。
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//CURLOPT_SSL_VERIFYPEER禁用,需要被设置FALSE。
        
        // 以下两种方式需选择一种
        
        // 第一种方法，cert 与 key 分别属于两个.pem文件
        // 默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');//证书的类型。支持的格式有"PEM" (默认值), "DER"和"ENG"。
        curl_setopt($ch, CURLOPT_SSLCERT, __DIR__ . '/pem/apiclient_cert.pem');//一个包含PEM格式证书的文件名。
        // 默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型，支持的密钥类型为"PEM"(默认值)、"DER"和"ENG"。
        curl_setopt($ch, CURLOPT_SSLKEY, __DIR__ . '/pem/apiclient_key.pem');//包含SSL私钥的文件名。
        
        curl_setopt($ch, CURLOPT_CAINFO, 'PEM');//设置证书
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/pem/rootca.pem');//一个保存着1个或多个用来让服务端验证的证书的文件名。这个参数仅仅在和CURLOPT_SSL_VERIFYPEER一起使用时才有意义。 .
        
        // 第二种方式，两个文件合成一个.pem文件
        // curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
        
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }

    /**
     * **************************************************
     * 微信获取AccessToken 返回指定微信公众号的at信息
     * **************************************************
     */
    public function AccessToken($appId = NULL, $appSecret = NULL)
    {
        $filename = $this->cachePath . "access_token.json";
        if (is_file($filename)) {
            $data = file_get_contents($filename);
            $data = json_decode($data, true);
            if (isset($data['access_token']) && isset($data['expire_time']) && $data['expire_time'] > time()) {
                return $data['access_token'];
            }
        }
        $appId = is_null($appId) ? $this->appId : $appId;
        $appSecret = is_null($appSecret) ? $this->appSecret : $appSecret;
        // 如果是企业号用以下URL获取access_token
        // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=" . $appId . "&corpsecret=" . $appSecret;
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
        $result = $this->HttpsRequest($url);
        // print_r($result);
        $jsoninfo = json_decode($result, true);
        $access_token = $jsoninfo["access_token"];
        $data = array(
            'expire_time' => time() + 7000,
            'access_token' => $access_token
        );
        @file_put_contents($filename, json_encode($data));
        return $access_token;
    }

    /**
     * **************************************************
     * 微信获取ApiTicket 返回指定微信公众号的at信息
     * **************************************************
     */
    public function JsApiTicket($appId = NULL, $appSecret = NULL)
    {
        $filename = $this->cachePath . "jsapi_ticket.json";
        if (is_file($filename)) {
            $data = file_get_contents($filename);
            $data = json_decode($data, true);
            if (isset($data['jsapi_ticket']) && isset($data['expire_time']) && $data['expire_time'] > time()) {
                return $data['jsapi_ticket'];
            }
        }
        $appId = is_null($appId) ? $this->appId : $appId;
        $appSecret = is_null($appSecret) ? $this->appSecret : $appSecret;
        
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=" . $this->AccessToken();
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        $ticket = $jsoninfo['ticket'];
        // echo $ticket . "<br />";
        $data = array(
            'expire_time' => time() + 7000,
            'jsapi_ticket' => $ticket
        );
        @file_put_contents($filename, json_encode($data));
        return $ticket;
    }

    public function VerifyJsApiTicket($appId = NULL, $appSecret = NULL)
    {
        if (! empty($this->jsApiTime) && intval($this->jsApiTime) > time() && ! empty($this->jsApiTicket)) {
            $ticket = $this->jsApiTicket;
        } else {
            $ticket = $this->JsApiTicket($appId, $appSecret);
            $this->jsApiTicket = $ticket;
            $this->jsApiTime = time() + 7200;
        }
        return $ticket;
    }

    /**
     * **************************************************
     * 微信通过OPENID获取用户信息，返回数组
     * **************************************************
     */
    public function GetUser($openId)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $wxAccessToken . "&openid=" . $openId . "&lang=zh_CN";
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信生成二维码ticket
     * **************************************************
     */
    public function QrCodeTicket($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        return $result;
    }

    /**
     * **************************************************
     * 微信通过ticket生成二维码
     * **************************************************
     */
    public function QrCode($ticket)
    {
        $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode($ticket);
        return $url;
    }

    /**
     * **************************************************
     * 微信通过指定模板信息发送给指定用户，发送完成后返回指定JSON数据
     * **************************************************
     */
    public function SendTemplate($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        return $result;
    }

    /**
     * **************************************************
     * 发送自定义的模板消息
     * **************************************************
     */
    public function SetSend($touser, $template_id, $url, $data, $topcolor = '#7B68EE')
    {
        $template = array(
            'touser' => $touser,
            'template_id' => $template_id,
            'url' => $url,
            'topcolor' => $topcolor,
            'data' => $data
        );
        $jsonData = urldecode(json_encode($template));
        echo $jsonData;
        $result = $this->SendTemplate($jsonData);
        return $result;
    }

    /**
     * **************************************************
     * 微信设置OAUTH跳转URL，返回字符串信息 - SCOPE = snsapi_base //验证时不返回确认页面，只能获取OPENID
     * **************************************************
     */
    public function OauthBase($redirectUrl, $state = "", $appId = NULL)
    {
        $appId = is_null($appId) ? $this->appId : $appId;
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appId . "&redirect_uri=" . $redirectUrl . "&response_type=code&scope=snsapi_base&state=" . $state . "#wechat_redirect";
        return $url;
    }

    /**
     * **************************************************
     * 微信设置OAUTH跳转URL，返回字符串信息 - SCOPE = snsapi_userinfo //获取用户完整信息
     * **************************************************
     */
    public function OauthUserinfo($redirectUrl, $state = "", $appId = NULL)
    {
        $appId = is_null($appId) ? $this->appId : $appId;
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appId . "&redirect_uri=" . $redirectUrl . "&response_type=code&scope=snsapi_userinfo&state=" . $state . "#wechat_redirect";
        return $url;
    }

    /**
     * **************************************************
     * 微信OAUTH跳转指定URL
     * **************************************************
     */
    public function Header($url)
    {
        header("location:" . $url);
    }

    /**
     * **************************************************
     * 微信通过OAUTH返回页面中获取AT信息
     * **************************************************
     */
    public function OauthAccessToken($code, $appId = NULL, $appSecret = NULL)
    {
        $appId = is_null($appId) ? $this->appId : $appId;
        $appSecret = is_null($appSecret) ? $this->appSecret : $appSecret;
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appId . "&secret=" . $appSecret . "&code=" . $code . "&grant_type=authorization_code";
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信通过OAUTH的Access_Token的信息获取当前用户信息 // 只执行在snsapi_userinfo模式运行
     * **************************************************
     */
    public function OauthUser($OauthAT, $openId)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $OauthAT . "&openid=" . $openId . "&lang=zh_CN";
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 创建自定义菜单
     * **************************************************
     */
    public function MenuCreate($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 获取自定义菜单
     * **************************************************
     */
    public function MenuGet()
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 删除自定义菜单
     * **************************************************
     */
    public function MenuDelete()
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 获取第三方自定义菜单
     * **************************************************
     */
    public function MenuGetInfo()
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信客服接口 - Add 添加客服人员
     * **************************************************
     */
    public function ServiceAdd($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/customservice/kfaccount/add?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信客服接口 - Update 编辑客服人员
     * **************************************************
     */
    public function ServiceUpdate($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/customservice/kfaccount/update?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信客服接口 - Delete 删除客服人员
     * **************************************************
     */
    public function ServiceDelete($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/customservice/kfaccount/del?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信客服接口 - 上传头像
     * *****************************************************
     */
    public function ServiceUpdateCover($kf_account, $media = '')
    {
        $wxAccessToken = $this->AccessToken();
        // $data['access_token'] = $wxAccessToken;
        $data['media'] = '@D:\\workspace\\htdocs\\yky_test\\logo.jpg';
        $url = "https:// api.weixin.qq.com/customservice/kfaccount/uploadheadimg?access_token=" . $wxAccessToken . "&kf_account=" . $kf_account;
        $result = $this->HttpsRequest($url, $data);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信客服接口 - 获取客服列表
     * **************************************************
     */
    public function ServiceList()
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信客服接口 - 获取在线客服接待信息
     * **************************************************
     */
    public function ServiceOnlineList()
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信客服接口 - 客服发送信息
     * **************************************************
     */
    public function ServiceSend($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信客服会话接口 - 创建会话
     * **************************************************
     */
    public function ServiceSessionAdd($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/customservice/kfsession/create?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信客服会话接口 - 关闭会话
     * **************************************************
     */
    public function ServiceSessionClose()
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/customservice/kfsession/close?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信客服会话接口 - 获取会话
     * **************************************************
     */
    public function ServiceSessionGet($openId)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/customservice/kfsession/getsession?access_token=" . $wxAccessToken . "&openid=" . $openId;
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信客服会话接口 - 获取会话列表
     * **************************************************
     */
    public function ServiceSessionList($kf_account)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/customservice/kfsession/getsessionlist?access_token=" . $wxAccessToken . "&kf_account=" . $kf_account;
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信客服会话接口 - 未接入会话
     * **************************************************
     */
    public function ServiceSessionWaitCase()
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/customservice/kfsession/getwaitcase?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信摇一摇 - 申请设备ID
     * **************************************************
     */
    public function DeviceApply($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/shakearound/device/applyid?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信摇一摇 - 编辑设备ID
     * **************************************************
     */
    public function DeviceUpdate($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/shakearound/device/update?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信摇一摇 - 本店关联设备
     * **************************************************
     */
    public function DeviceBindLocation($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/shakearound/device/bindlocation?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信摇一摇 - 查询设备列表
     * **************************************************
     */
    public function DeviceSearch($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/shakearound/device/search?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信摇一摇 - 新增页面
     * **************************************************
     */
    public function PageAdd($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/shakearound/page/add?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信摇一摇 - 编辑页面
     * **************************************************
     */
    public function PageUpdate($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/shakearound/page/update?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信摇一摇 - 查询页面
     * **************************************************
     */
    public function PageSearch($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/shakearound/page/search?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信摇一摇 - 删除页面
     * **************************************************
     */
    public function PageDelete($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/shakearound/page/delete?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * 微信摇一摇 - 上传图片素材
     * 
     * @param string $imgPath            
     * @return mixed
     * @version 2015-5-29
     * @author liujun
     */
    public function MaterialAdd($imgPath = '')
    {
        $wxAccessToken = $this->AccessToken();
        // $data['access_token'] = $wxAccessToken;
        
        $data['media'] = '@' . LOCAL_SAVEPATH . $imgPath;
        $url = "https://api.weixin.qq.com/shakearound/material/add?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $data);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信摇一摇 - 配置设备与页面的关联关系
     * **************************************************
     */
    public function DeviceBindPage($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/shakearound/device/bindpage?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信摇一摇 - 获取摇周边的设备及用户信息
     * **************************************************
     */
    public function GetShakeInfo($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/shakearound/user/getshakeinfo?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * **************************************************
     * 微信摇一摇 - 以设备为维度的数据统计接口
     * **************************************************
     */
    public function GetShakeStatistics($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/shakearound/statistics/device?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * ***************************************************
     * 生成随机字符串 - 最长为32位字符串
     * ***************************************************
     */
    public function NonceStr($length = 16, $type = FALSE)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        if ($type == TRUE) {
            return strtoupper(md5(time() . $str));
        } else {
            return $str;
        }
    }

    /**
     * *****************************************************
     * 微信商户订单号 - 最长28位字符串
     * *****************************************************
     */
    public function MchBillno($mchid = NULL)
    {
        if (is_null($mchid)) {
            if ($this->mchid == "" || is_null($this->mchid)) {
                $mchid = time();
            } else {
                $mchid = $this->mchid;
            }
        } else {
            $mchid = substr(addslashes($mchid), 0, 10);
        }
        return date("Ymd", time()) . time() . $mchid;
    }

    /**
     * *****************************************************
     * 微信格式化数组变成参数格式 - 支持url加密
     * *****************************************************
     */
    public function SetParam($parameters)
    {
        if (is_array($parameters) && ! empty($parameters)) {
            $this->parameters = $parameters;
            return $this->parameters;
        } else {
            return array();
        }
    }

    /**
     * *****************************************************
     * 微信格式化数组变成参数格式 - 支持url加密
     * *****************************************************
     */
    public function FormatArray($parameters = NULL, $urlencode = FALSE)
    {
        if (is_null($parameters)) {
            $parameters = $this->parameters;
        }
        $restr = ""; // 初始化空
        ksort($parameters); // 排序参数
        foreach ($parameters as $k => $v) { // 循环定制参数
            if (null != $v && "null" != $v && "sign" != $k) {
                if ($urlencode) { // 如果参数需要增加URL加密就增加，不需要则不需要
                    $v = urlencode($v);
                }
                $restr .= $k . "=" . $v . "&"; // 返回完整字符串
            }
        }
        if (strlen($restr) > 0) { // 如果存在数据则将最后“&”删除
            $restr = substr($restr, 0, strlen($restr) - 1);
        }
        return $restr; // 返回字符串
    }

    /**
     * *****************************************************
     * 微信MD5签名生成器 - 需要将参数数组转化成为字符串[wxFormatArray方法]
     * *****************************************************
     */
    public function Md5Sign($content, $privatekey)
    {
        try {
            if (is_null($privatekey)) {
                throw new \Exception("财付通签名key不能为空！");
            }
            if (is_null($content)) {
                throw new \Exception("财付通签名内容不能为空");
            }
            $signStr = $content . "&key=" . $privatekey;
            return strtoupper(md5($signStr));
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * *****************************************************
     * 微信Sha1签名生成器 - 需要将参数数组转化成为字符串[wxFormatArray方法]
     * *****************************************************
     */
    public function Sha1Sign($content)
    {
        try {
            if (is_null($content)) {
                throw new \Exception("签名内容不能为空");
            }
            // $signStr = $content;
            return sha1($content);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * *****************************************************
     * 微信jsApi整合方法 - 通过调用此方法获得jsapi数据
     * *****************************************************
     */
    public function JsapiPackage()
    {
        $jsapi_ticket = $this->VerifyJsApiTicket();
        
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $protocol . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        
        $timestamp = time();
        $nonceStr = $this->NonceStr();
        
        $signPackage = array(
            "jsapi_ticket" => $jsapi_ticket,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url
        );
        
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $rawString = "jsapi_ticket=$jsapi_ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        
        // $rawString = $this->FormatArray($signPackage);
        $signature = $this->Sha1Sign($rawString);
        
        $signPackage['signature'] = $signature;
        $signPackage['rawString'] = $rawString;
        $signPackage['appId'] = $this->appId;
        
        return $signPackage;
    }

    /**
     * *****************************************************
     * 微信卡券：JSAPI 卡券Package - 基础参数没有附带任何值 - 再生产环境中需要根据实际情况进行修改
     * *****************************************************
     */
    public function CardPackage($cardId, $timestamp = '')
    {
        $api_ticket = $this->VerifyJsApiTicket();
        if (! empty($timestamp)) {
            // $timestamp = $timestamp;
        } else {
            $timestamp = time();
        }
        
        $arrays = array(
            $this->appSecret,
            $timestamp,
            $cardId
        );
        sort($arrays, SORT_STRING);
        // print_r($arrays);
        // echo implode("",$arrays)."<br />";
        $string = sha1(implode($arrays));
        // echo $string;
        $resultArray['cardId'] = $cardId;
        $resultArray['cardExt'] = array();
        $resultArray['cardExt']['code'] = '';
        $resultArray['cardExt']['openid'] = '';
        $resultArray['cardExt']['timestamp'] = $timestamp;
        $resultArray['cardExt']['signature'] = $string;
        // print_r($resultArray);
        return $resultArray;
    }

    /**
     * *****************************************************
     * 微信卡券：JSAPI 卡券全部卡券 Package
     * *****************************************************
     */
    public function CardAllPackage($cardIdArray = array(), $timestamp = '')
    {
        $reArrays = array();
        if (! empty($cardIdArray) && (is_array($cardIdArray) || is_object($cardIdArray))) {
            // print_r($cardIdArray);
            foreach ($cardIdArray as $value) {
                // print_r($this->CardPackage($value,$openid));
                $reArrays[] = $this->CardPackage($value, $timestamp);
            }
            // print_r($reArrays);
        } else {
            $reArrays[] = $this->CardPackage($cardIdArray, $timestamp);
        }
        return strval(json_encode($reArrays));
    }

    /**
     * *****************************************************
     * 微信卡券：获取卡券列表
     * *****************************************************
     */
    public function CardListPackage($cardType = "", $cardId = "")
    {
        // $api_ticket = $this->VerifyJsApiTicket();
        $resultArray = array();
        $timestamp = time();
        $nonceStr = $this->NonceStr();
        // $strings =
        $arrays = array(
            $this->appId,
            $this->appSecret,
            $timestamp,
            $nonceStr
        );
        sort($arrays, SORT_STRING);
        $string = sha1(implode($arrays));
        
        $resultArray['app_id'] = $this->appId;
        $resultArray['card_sign'] = $string;
        $resultArray['time_stamp'] = $timestamp;
        $resultArray['nonce_str'] = $nonceStr;
        $resultArray['card_type'] = $cardType;
        $resultArray['card_id'] = $cardId;
        return $resultArray;
    }

    /**
     * *****************************************************
     * 将数组解析XML - 微信红包接口
     * *****************************************************
     */
    public function ArrayToXml($parameters = NULL)
    {
        if (is_null($parameters)) {
            $parameters = $this->parameters;
        }
        
        if (! is_array($parameters) || empty($parameters)) {
            die("参数不为数组无法解析");
        }
        
        $xml = "<xml>";
        foreach ($parameters as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * *****************************************************
     * 微信卡券：上传LOGO - 需要改写动态功能
     * *****************************************************
     */
    public function CardUpdateImg()
    {
        $wxAccessToken = $this->AccessToken();
        // $data['access_token'] = $wxAccessToken;
        $data['buffer'] = '@D:\\workspace\\htdocs\\yky_test\\logo.jpg';
        $url = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $data);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
        // array(1) { ["url"]=> string(121) "http://mmbiz.qpic.cn/mmbiz/ibuYxPHqeXePNTW4ATKyias1Cf3zTKiars9PFPzF1k5icvXD7xW0kXUAxHDzkEPd9micCMCN0dcTJfW6Tnm93MiaAfRQ/0" }
    }

    /**
     * *****************************************************
     * 微信卡券：获取颜色
     * *****************************************************
     */
    public function CardColor()
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/card/getcolors?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信卡券：拉取门店列表
     * *****************************************************
     */
    public function BatchGet($offset = 0, $count = 0)
    {
        $jsonData = json_encode(array(
            'offset' => intval($offset),
            'count' => intval($count)
        ));
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/card/location/batchget?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信卡券：创建卡券
     * *****************************************************
     */
    public function CardCreated($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/card/create?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信卡券：查询卡券详情
     * *****************************************************
     */
    public function CardGetInfo($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/card/get?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信卡券：设置白名单
     * *****************************************************
     */
    public function CardWhiteList($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/card/testwhitelist/set?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信卡券：消耗卡券
     * *****************************************************
     */
    public function CardConsume($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/card/code/consume?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信卡券：删除卡券
     * *****************************************************
     */
    public function CardDelete($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/card/delete?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信卡券：选择卡券 - 解析CODE
     * *****************************************************
     */
    public function CardDecryptCode($jsonData)
    {
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/card/code/decrypt?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信卡券：更改库存
     * *****************************************************
     */
    public function CardModifyStock($cardId, $increase_stock_value = 0, $reduce_stock_value = 0)
    {
        if (intval($increase_stock_value) == 0 && intval($reduce_stock_value) == 0) {
            return false;
        }
        
        $jsonData = json_encode(array(
            "card_id" => $cardId,
            'increase_stock_value' => intval($increase_stock_value),
            'reduce_stock_value' => intval($reduce_stock_value)
        ));
        
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/card/modifystock?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信卡券：查询用户CODE
     * *****************************************************
     */
    public function CardQueryCode($code, $cardId = '')
    {
        $jsonData = json_encode(array(
            "code" => $code,
            'card_id' => $cardId
        ));
        
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/card/code/get?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信上传临时素材接口
     * 
     * @author liujun
     *         *****************************************************
     */
    public function UploadMedia($imgPath, $type = "image")
    {
        $jsonData = array(
            "media_id " => '@' . LOCAL_SAVEPATH . $imgPath
        );
        $wxAccessToken = $this->AccessToken();
        echo $wxAccessToken;
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $wxAccessToken . "&type=" . $type;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     * *****************************************************
     * 微信获取临时素材接口
     * 
     * @author liujun
     *         *****************************************************
     */
    public function GetMedia($media_id)
    {
        $jsonData = json_encode(array(
            "media_id " => $media_id
        ));
        $wxAccessToken = $this->AccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=" . $wxAccessToken;
        $result = $this->HttpsRequest($url, $jsonData);
        $jsoninfo = json_decode($result, true);
        return $jsoninfo;
    }

    /**
     *
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
     *
     * @return 用户的openid
     */
    public function GetOpenid($baseUrl = '')
    {
        // 通过code获得openid
        if (! isset($_GET['code'])) {
            // 触发微信返回code码
            $baseUrl = $baseUrl ? $baseUrl : urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
            // $baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REDIRECT_URL']);
            $url = $this->OauthUserinfo($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            // 获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->GetOpenidFromMp($code);
            return $openid;
        }
    }

    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * 
     * @param string $code
     *            微信跳转回来带上的code
     *            
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $data = $this->OauthAccessToken($code);
        // 取出openid
        $this->data = $data;
        $openid = $data['openid'];
        return $openid;
    }

    /**
     * 通过授权的方式获取open_id
     *
     * @return openid
     * @version 2016-6-7 WZ
     */
    public function GetOpenidByUserinfo()
    {
        // 通过code获得openid
        if (! isset($_GET['code'])) {
            // 触发微信返回code码
            $baseUrl = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
            $url = $this->OauthUserinfo($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            // 获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            return $openid;
        }
    }

    /**
     * 转移钱到用户上
     *
     * @param unknown $param            
     * @version 2016-6-12 WZ
     * @see https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
     */
    public function transfersMoney($param)
    {
        $field = array(
            'open_id',
            'amount',
            'desc',
//             're_user_name'
        );
        $data = array();
        foreach ($field as $v) {
            if (! isset($param[$v]) || ! $param[$v]) {
                return array(
                    'status' => 1,
                    'msg' => '填写信息不全'
                );
            }
            $data[$v] = $param[$v];
        }
        
        $parameters = array(
            'mch_appid' => $this->appId,
            'mchid' => $this->mchid,
            'nonce_str' => $this->NonceStr(32),
            'partner_trade_no' => date('YmdHis').mt_rand(10000,99999),
            'openid' => $data['open_id'],
            'check_name' => 'NO_CHECK', // NO_CHECK, FORCE_CHECK, OPTION_CHECK
            // 're_user_name' => '',
            'amount' => $data['amount'],
            'desc' => $data['desc'],
            'spbill_create_ip' => $this->getServerIp()
        );
        $rawString = $this->FormatArray($parameters);
        $signature = $this->Md5Sign($rawString, $this->privatekey);//微信MD5签名生成器 - 需要将参数数组转化成为字符串[wxFormatArray方法
        $parameters['sign'] = $signature;
        
        $data = $this->ArrayToXml($parameters);//将数组解析XML 
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $result = $this->HttpsRequestPem($url, $data);//微信带证书提交数据
        $result = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($result['result_code'] == 'FAIL') {
            return array('status' => 2, 'msg' => $result['return_msg']);
        }
        return array('status' => 0, 'msg' => 'ok');
    }

    /**
     * 获取服务器端IP地址
     * 
     * @return string
     */
    function getServerIp()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR']) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            } else {
                $server_ip = $_SERVER['LOCAL_ADDR'];
            }
        } else {
            $server_ip = getenv('SERVER_ADDR');
        }
        return $server_ip;
    }
}