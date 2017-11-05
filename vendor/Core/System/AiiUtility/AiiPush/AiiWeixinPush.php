<?php
namespace Core\System\AiiUtility\AiiPush;

/**
 * 微信推送
 *
 * @author WZ
 *        
 */
class AiiWeixinPush extends AiiPushBase
{
    /**
     * 获取token的url
     */
    private $token_url = 'https://api.weixin.qq.com/cgi-bin/token';
    
    /**
     * 推送单条的url
     */
    private $push_single_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send';
    
    /**
     * 重发一次
     */
    private $isRetry = false;

    /**
     * 初始化
     */
    public function init()
    {
        $this->_access_id = PUSH_WEIXIN_APPID;
        $this->_secret_key = PUSH_WEIXIN_APPSECRET;
    }

    /**
     * 获取AccessToken
     *
     * @return boolean|unknown
     * @version 2014-11-6 WZ
     */
    public function getAccessToken()
    {
        if (! $this->_access_id || ! $this->_secret_key)
        {
            $this->myfile->putAtEnd('weixin没有设置id和key');
            return false;
        }
        $filename = 'Weixin/AccessToken';
        $cache_file = $this->myfile->getCacheTime($filename);
        $json = false;
        
        if ($cache_file && $cache_file > time() - 3600)
        {
            $json = $this->myfile->getCache($filename);
            $json = $json[0];
        }
        
        if (! $json)
        {
            $param = array(
                'grant_type' => 'client_credential',
                'appid' => $this->_access_id,
                'secret' => $this->_secret_key
            );
            $result = $this->http_get($this->token_url, $param);
            if (! $result)
            {
                $this->myfile->putAtEnd('请求获取AccessToken失败');
                return false;
            }
            
            $json = json_decode($result);
            $this->myfile->setCache($filename, $json); // 缓存到文件
        }
        
        if (! $json)
        {
            $this->myfile->putAtEnd('获取AccessToken，解析json失败');
            return false;
        }
        $access_token = $json->access_token;
        
        return $access_token;
    }
    
    /**
     * 清除AccessToken
     * 
     * @version 2014-11-7 WZ
     */
    private function clearAccessToken()
    {
        $filename = 'Weixin/AccessToken';
        $this->myfile->setCache($filename, false);
    }

    /**
     * 发送单条微信
     *
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function pushSingleDevice($deviceToken, $content, $title, $args = array())
    {
        $content .= $this->getLink($args['id']);
        $access_token = $this->getAccessToken();
        if (! $access_token)
        {
            return false;
        }
        $url = $this->push_single_url . '?access_token=' . $access_token;
        $weixin_item = array(
            'touser' => $deviceToken,
            'msgtype' => 'text',
            'text' => array(
                'content' => urlencode($content)
            )
        );
        $json = json_encode($weixin_item); // JSON_UNESCAPED_UNICODE
        $json = urldecode($json);
        $ret = $this->http_post($url, $json);
        $ret_object = json_decode($ret); // {"errcode":0,"errmsg":"ok"}
        if(40001 == $ret_object->errcode && !$this->isRetry)
        {
            //invalid credential
            $this->clearAccessToken();
            $this->isRetry = true;
            $this->pushSingleDevice($deviceToken, $content, $title, $args);
        }
        return $ret_object;
    }
    
    /**
     * 推送给所有微信设备
     *
     * @param string $content            
     * @param string $title            
     * @param array $args            
     * @param number $user_type            
     * @version 2014-11-5 WZ
     */
    public function pushAllDevices($content, $title = '', $args = array())
    {
        $content .= $this->getLink(900);
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$access_token;
        $weixin_item = array(
            'filter' => array(
                'group_id' => '0'
            ),
            'msgtype' => 'text',
            'text' => array(
                'content' => urlencode($content)
            )
        );
        $json = json_encode($weixin_item); // JSON_UNESCAPED_UNICODE
        $json = urldecode($json);
        $ret = $this->http_post($url, $json);
        $ret_object = json_decode($ret); // {"errcode":0,"errmsg":"ok"}
        if(40001 == $ret_object->errcode && !$this->isRetry)
        {
            //invalid credential
            $this->clearAccessToken();
            $this->isRetry = true;
            $this->pushAllDevices($content, $title, $args);
        }
        return $ret_object;
    }
    
    /**
     * 获取微信分组
     * 
     * @return string
     * @version 2014-11-7 WZ
     */
    public function getGroup()
    {
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token='.$access_token;
        $result = $this->http_get($url, '');
        return $result;
    }

    /**
     * 批量发送
     *
     * @param array $deviceTokens
     *            id,device_token
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function pushCollectionDevice($deviceTokens, $content, $title = '', $args = array())
    {
        $res_arr = array(
            'success' => array(),
            'fail' => array()
        );
        
        foreach ($deviceTokens as $value)
        {
            $result = $this->pushSingleDevice($value['device_token'], $content, $title, $args);
            if ($result)
            {
                if (isset($result->errcode) && 0 === $result->errcode)
                {
                    $res_arr['success'][] = $value['id'];
                }
                else
                {
                    $this->myfile->putAtEnd(json_encode($result));
                    $res_arr['fail'][] = $value['id'];
                }
            }
            else
            {
                $res_arr['fail'][] = $value['id'];
                $this->myfile->putAtEnd('没有收到回调，或请求参数有误');
            }
        }
        return $res_arr;
    }
    
    /**
     * 根据推送类型获取链接
     *
     * @version 2014-11-7 WZ
     */
    private function getLink($type)
    {
        $link_array = array(
            ' <a href=\'http://gladmin.kuaiying.me/wap/my_task.php?type=2\'>进入我的任务</a>查看',
            ' <a href=\'http://gladmin.kuaiying.me/wap/my_task.php?type=3\'>进入我的任务</a>查看',
            ' <a href=\'http://gladmin.kuaiying.me/wap/my_task.php?type=4\'>进入我的任务</a>查看',
            ' <a href=\'http://gladmin.kuaiying.me/wap/my_task.php?type=5\'>进入我的任务</a>查看',
            ' <a href=\'http://gladmin.kuaiying.me/wap/financial.php\'>进入财务记录</a>查看',
            ' <a href=\'http://gladmin.kuaiying.me/wap/data.php\'>重新修改个人资料</a>提交审核',
            ' <a href=\'http://gladmin.kuaiying.me/wap/index.php\'>进入我的中心</a>',
            ' <a href=\'http://gladmin.kuaiying.me/wap/search.php\'>进入搜任务</a>查看',
            ' <a href=\'http://gladmin.kuaiying.me/wap/integral.php\'>进入积分记录</a>查看',
            ' <a href=\'http://www.kuaiying.me/d/\'>快应-校园任务发布神器</a>'
        );
    
        if(in_array($type, explode(',', '101')))
        {
            return $link_array[0];
        }
        elseif(in_array($type, explode(',', '112')))
        {
            return $link_array[1];
        }
        elseif(in_array($type, explode(',', '103,117')))
        {
            return $link_array[2];
        }
        elseif(in_array($type, explode(',', '102,104')))
        {
            return $link_array[3];
        }
        elseif(in_array($type, explode(',', '106,107,401')))
        {
            return $link_array[4];
        }
        elseif(in_array($type, explode(',', '109')))
        {
            return $link_array[5];
        }
        elseif(in_array($type, explode(',', '113,114,115,116')))
        {
            return $link_array[6];
        }
        elseif(in_array($type, explode(',', '201')))
        {
            return $link_array[7];
        }
        elseif(in_array($type, explode(',', '301,303,304,305,307,308,311,315')))
        {
            return $link_array[8];
        }
        elseif(in_array($type, explode(',', '900')))
        {
            return $link_array[9];
        }
    }
}
?>