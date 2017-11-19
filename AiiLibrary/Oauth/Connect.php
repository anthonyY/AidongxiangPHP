<?php
namespace AiiLibray\Oauth;
include_once "system/core.php";
class Connect {

    public $url;
    public $callbackUrl;

    function __construct() {

        $this->qq = new \QQ();
        $this->wx = new \weixin();
    }

    /**
     * 创建新浪微博的授权链接
     */
    function weibo_token() {
        $state = uniqid('weibo_', true);
        $callback = "{$this->url}?method=weibo_callback";
        $url = $this->weibo->getAuthorizeURL($callback, 'code', $state);
        $_SESSION['weibo_state'] = $state;
        go($url);
    }

    /**
     * 新浪网微博授权回调
     */
    function weibo_callback() {
        $state = $_REQUEST['state'];
        $code = $_REQUEST['code'];
        if (!isset($_SESSION['weibo_state']) || $state !== $_SESSION['weibo_state'])
            exit("参数错误！请返回重新使用新浪微博登录！");
        $keys = array('code' => $code, 'redirect_uri' => "{$this->url}?method=weibo_callback");
        $token = $this->weibo->getAccessToken('code', $keys);
        if (isset($token['error']))
            exit("验证错误！请返回重新使用新浪微博登录！");
        $_SESSION['weibo_token'] = $token;
        go("connect.php?method=callback&app=weibo");
    }

    /**
     * 创建QQ的授权链接
     */
    function qq_token()
    {
        $state = uniqid('qq_', true);
        $callback = "{$this->url}?method=qq_callback&is_wap=";
        $_SESSION['qq_state'] = $state;
        //setcookie('qq_state',$state,time()+60,'/');
        $url = $this->qq->getAuthorizeURL($callback, 'code', $state, QQ_SCOPE);
        go($url);
    }

    /**
     * QQ授权回调
     */
    function qq_callback() {
        $state = $_REQUEST['state'];
        $code = $_REQUEST['code'];
        if (!isset($_SESSION['qq_state']) || $state !== $_SESSION['qq_state'])
            exit("参数错误！请返回重新使用QQ登录！");
        $keys = array('code' => $code, 'redirect_uri' => "{$this->url}?method=qq_callback");
        $token = $this->qq->getAccessToken('code', $keys);

        if (isset($token['error']))
        {
            exit("验证错误！请返回重新使用QQ登录！");
        }
        $_SESSION['qq_token'] = $token;
        $openid = $this->qq->getOpenID();
        $_SESSION['qq_token'] = array('access_token' => $token['access_token'], 'openid' => $openid, 'uid' => $openid, 'expires_in' => $token['expires_in']);
        go($this->url."?method=callback&app=qq");
    }

    /**
     * 创建weixin的授权链接
     */
    function weixin_token() {
        $state = uniqid('wx_', true);
        $callback = "{$this->url}?method=weixin_callback";
        $url = $this->wx->getAuthorizeURL($callback, 'code', $state, WX_SCOPE);
        $_SESSION['wx_state'] = $state;
        go($url);
    }
    
    /**
     * weixin授权回调
     */
    function weixin_callback() {
        $state = $_REQUEST['state'];
        $code = $_REQUEST['code'];
        if (!isset($_SESSION['wx_state']) || $state !== $_SESSION['wx_state'])
            exit("参数错误！请返回重新使用微信登录！");
        $token = $this->wx->wxAccessToken($code);
        if (isset($token['errcode']))
        {
            exit("验证错误！请返回重新使用微信登录！");
        }
        $_SESSION['wx_token'] = array('access_token' => $token['access_token'], 'openid' => $token['openid'], 'expires_in' => $token['expires_in']);
        go($this->url."?method=callback&app=weixin");
    }
    
    /**
     * 创建人人的授权链接
     */
    function renren_token() {
        $state = uniqid('renren_', true);
        $callback = "{$this->url}?method=renren_callback";
        $url = $this->renren->getAuthorizeURL($callback, 'code', $state, RR_SCOPE);
        $_SESSION['renren_state'] = $state;
        go($url);
    }

    /**
     * 人人授权回调
     */
    function renren_callback() {
        $state = $_REQUEST['state'];
        $code = $_REQUEST['code'];
        if (!isset($_SESSION['renren_state']) || $state !== $_SESSION['renren_state'])
            exit("参数错误！请返回重新使用renren登录！");
        $keys = array('code' => $code, 'redirect_uri' =>"{$this->url}?method=renren_callback");
        $token = $this->renren->getAccessToken('code', $keys);
        if (isset($token['error']))
            exit("验证错误！请返回重新使用renren登录！");
        $token['uid'] = $token['user']['id'];
        $_SESSION['renren_token'] = $token;
        $exprie = $token['expires_in'] + time();
        setcookie($this->renren->client_id . '_access_token', $token['access_token'], $exprie, '/');
        setcookie($this->renren->client_id . '_user', $token['user']['id'], $exprie, '/');
        setcookie($this->renren->client_id . '_expires', $exprie, $exprie, '/');
        go("connect.php?method=callback&app=renren");
    }

    //验证成功后的跳转地址
    function callback() {
        $app = isset($_GET['app']) ? $_GET['app'] : '';
        if (in_array($app, array('renren', 'weibo', 'qq','weixin'))) {
            $_SESSION[$app] = get_app_info($app);
        }
        $type = array('weixin'=>'1','qq'=>'2');
        if(isset($_SESSION['oauth_type']) && $_SESSION['oauth_type'] ){
            go($this->callbackUrl.'/'.$type[$app]);
        }
        else
        {
            go($this->callbackUrl.'/'.$type[$app]);
        }
    }

    /**
     * 发布一条微博或feed信息
     */
    function postone() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $sync = isset($_POST['sync']) ? $_POST['sync'] : '';
            $content = htmlspecialchars($_POST["content"]);
            $imgurl = "http://blog.iplaybus.com/uploads/2011/10/20111019224959.jpg";
            //如果有上传图片则将图片上传到uploads文件夹下
            //没有图片则使用预先定义好的图片
            if (isset($_FILES['image']) && $_FILES['image']['name']) {
                $filename = $_FILES['image']['name'];
                $ext = substr($filename, (strrpos($filename, '.') + 1));
                $newname = time() . "." . $ext;
                $imgdir = ROOTPATH . "/uploads/";
                if (!is_dir($imgdir)) {
                    @mkdir($imgdir);
                }
                $imgpath = $imgdir . $newname;
                @rename($_FILES['image']['tmp_name'], $imgpath);
                if (is_file($imgpath)) {
                    $imgurl = $this->url = "http://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']) . "/uploads/" . $newname;
                }
            }
            if (strlen($content) > 280) {
                exit("内容不能超过140个汉字");
            } else if (!$content) {
                exit("内容不能为空");
            }
            $sync = is_array($sync) ? $sync : array();
            foreach ($sync as $m => $n) {
                //检测是否已经登录
                if (check_app_login($m)) {
                    if ($m == 'weibo') {
                        $this->weiboclient = new weiboclient($_SESSION['weibo_token']['access_token']);
                        $result = $this->weiboclient->upload($content, $imgurl);
                    } else if ($m == 'qq') {
                        $this->qqclient = new qqclient($_SESSION['qq_token']['access_token'], $_SESSION['qq_token']['openid']);
                        $data = array('title' => $content, 'url' => 'http://blog.iplaybus.com', 'site' => 'http://blog.iplaybus.com', 'fromurl' => 'http://blog.iplaybus.com', 'images' => $imgurl);
                        $result = $this->qqclient->add_share($data);
                    } else if ($m == 'renren') {
                        $result = $this->renren->upload($content, $imgurl);
                    }
                }
            }
            exit("更新成功！<a href='javascript:history.go(-1);'>返回</a>");
        }
    }

    /**
     * 退出/注销
     */
    function clear() {
        $app = isset($_GET['app']) ? $_GET['app'] : '';
        if (in_array($app, array('renren', 'weibo', 'qq'))) {
            $_SESSION['app'][$app] = '';
            $_SESSION["{$app}_token"] = '';
        }
        go("index.php");
    }

}