<?php
namespace Core\System\AiiUtility\AiiEasemobApi;
include_once __DIR__ . '/config.php';

/**
 * PHP对环信API请求专用类
 * 
 * @author WZ
 *
 */
class AiiEasemobApi
{
    private $http = 'https';

    const METHOD_DELETE = 'DELETE';

    const METHOD_GET = 'GET';

    const METHOD_POST = 'POST';

    const METHOD_PUT = 'PUT';
    
    private $type;
    
    private $params;
    
    private $method;
    
    private $extra_conf;
    
    private $getNewToken = false;

    /**
     * 删除用户
     *
     * @param string $username            
     * @version 2015-2-5 WZ
     */
    function userDelete($username)
    {
        $params = array();
        $method = self::METHOD_DELETE;
        $type = 'users/' . $username;
        return $this->getResult($type, $params, $method);
    }

    /**
     * 获取用户信息
     *
     * @param string $username            
     * @return json
     * @version 2015-2-5 WZ
     */
    function userGetInfo($username)
    {
        $params = array();
        $method = self::METHOD_GET;
        $type = 'users/' . $username;
        return $this->getResult($type, $params, $method);
    }

    /**
     * 注册用户
     *
     * @param string $username            
     * @param string $password            
     * @return json
     * @version 2015-2-5 WZ
     */
    function userRegister($username, $password)
    {
        $params = array(
            'username' => $username,
            'password' => $password
          );
        $method = self::METHOD_POST;
        return $this->getResult('users', $params, $method);
    }

    /**
     * 修改用户昵称
     *
     * @param string $username            
     * @param string $nickname            
     * @version 2015-2-5 WZ
     */
    function userUpdateNickname($username, $nickname)
    {
        $method = self::METHOD_PUT;
        $params = array(
            'nickname' => $nickname
        );
        $type = 'users/' . $username;
        return $this->getResult($type, $params, $method);
    }

    /**
     * 修改用户密码
     *
     * @param string $username            
     * @param string $password            
     * @return mixed
     * @version 2015-2-5 WZ
     */
    function userUpdatePassword($username, $password)
    {
        $method = self::METHOD_PUT;
        $params = array(
            'newpassword' => $password
        );
        $type = 'users/' . $username . '/password';
        return $this->getResult($type, $params, $method);
    }

    /**
     * 发送消息
     * 
     * @param string $from            
     * @param string $target            
     * @param string $msg            
     * @return mixed
     * @version 2015-2-5 WZ
     */
    function messageTxt($from, $target, $msg)
    {
        $method = self::METHOD_POST;
        $params = array(
            'target_type' => 'users',
            'target' => (array) $target,
            'msg' => array(
                'type' => 'txt',
                'msg' => $msg
            ),
            'from' => $from
        );
        $type = 'messages';
        return $this->getResult($type, $params, $method);
    }

    /**
     * 获取聊天记录
     *
     * @param 整合分页 $page true:多分页整合数据在一齐,false:返回环信的结果
     * @param 开始时间 $time_from
     *            时间戳time()格式
     * @param 结束时间 $time_to
     *            时间戳time()格式
     * @param 分页数量 $limit
     *            全部
     * @param string $cursor
     *            游标用于分页
     * @version 2015-2-5 WZ
     */
    function getChatMessages($page = 0, $time_from = null, $time_to = null, $limit = 0, $cursor = "")
    {
        $method = self::METHOD_GET;
        $params = array();
        $where = array();
        if ($time_from || $time_to)
        {
            if ($time_from && is_numeric($time_from))
            {
                $where[] = "timestamp>" . $time_from . '000';
            }
            if ($time_to && is_numeric($time_to))
            {
                $where[] = "timestamp<" . $time_to . '000';
            }
        }
        else
        {
            $filename = __DIR__ . '/' . 'chatmessages_time.txt';
            $time_from = 1;
            if (is_file($filename))
            {
                $time_from = file_get_contents($filename);
            }
            file_put_contents($filename, time());
            @ chmod($filename, 0777);
            $where[] = "timestamp>" . $time_from . '000';
        }
        if ($where)
        {
            $ql = "select+*+where+" . implode('+and+', $where);
            $params['ql'] = $ql;
        }
        if ($limit)
        {
            $params['limit'] = $limit;
        }
        if ($cursor)
        {
            $params['cursor'] = $cursor;
        }
        $type = 'chatmessages';
        
        $json = $this->getResult($type, $params, $method);
        if ($page)
        {
            // 多分页整合
            $data = json_decode($json, true);
            if (isset ($data['cursor']))
            {
                $result = $this->getChatMessages($page, $time_from, $time_to, $limit, $data['cursor']);
                $sub_data = json_decode($result, true);
                $return = json_encode(array(
                    'entities' => array_merge($data['entities'], $sub_data['entities']),
                    'count' => $data['count'] + $sub_data['count'],
                ));
            }
            else 
            {
                $return = json_encode(array(
                    'entities' => $data['entities'],
                    'count' => $data['count'],
                ));
            }
            
            return $return;
        }
        else 
        {
            // 环信的返回
            return $json;
        }
    }

    /**
     * 获取token
     * 
     * @return string|Ambigous mixed>
     * @version 2015-2-5 WZ
     */
    private function getToken()
    {
        $filename = __DIR__ . '/' . 'easemob_token.txt';
        if (! $this->getNewToken)
        {
            if (is_file($filename))
            {
                $ftime = filemtime($filename);
                if (time() < $ftime + 5184000 / 2)
                {
                    $token = file_get_contents($filename);
                    return $token;
                }
            }
        }
        $token = $this->flashToken();
        return $token;
    }
    
    /**
     * 刷新token
     * 
     * @version 2015-4-20 WZ
     */
    public function flashToken() {
        $filename = __DIR__ . '/' . 'easemob_token.txt';
        $params = array(
            'grant_type' => 'client_credentials',
            'client_id' => HUANXIN_CLIENT_ID,
            'client_secret' => HUANXIN_CLIENT_SECRET
        );
        $method = self::METHOD_POST;
        $data = $this->getResult('token', $params, $method);
        $json = json_decode($data, true);
        if ($data)
        {
            if (isset($json['access_token']))
            {
                $token = $json['access_token'];
                file_put_contents($filename, $token);
                @ chmod($filename, 0777);
            }
        }
        return $token;
    }
    
    /**
     * 获取图片
     * 
     * @param unknown $type
     * @version 2015-2-13 WZ
     */
    public function getFile($url, $secret)
    {
        $method = self::METHOD_GET;
//         $type = str_replace(HUANXIN_URL, '', $url);
        $type = 'chatfiles/' . substr($url, strrpos($url, "/") + 1);
        $extra_conf = array(
            CURLOPT_HEADER => array(
                'share-secret: ' . $secret,
                'Accept: application/octet-stream'
            )
        );
        $params = array();
        return $this->getResult($type, $params, $method);
    }

    /**
     * 发送请求，获取结果
     *
     * @param 类型/地址 $type            
     * @param 参数 $params            
     * @param curl类型 $method            
     * @return mixed
     * @version 2015-2-5 WZ
     */
    private function getResult($type, $params, $method, $extra_conf = array())
    {
        $url = $this->http . '://' . HUANXIN_URL . $type;
        if (self::METHOD_GET != $method && $params)
        {
            $params = json_encode($params);
        }
        $token = '';
        if ('token' != $type)
        {
            $token = $this->getToken();
            $this->type = $type;
            $this->params = $params;
            $this->method = $method;
            $this->extra_conf = $extra_conf;
        }
    
        $curl_conf = array(
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            )
        );
     
        // 添加额外的配置
        foreach ($extra_conf as $k => $v)
        {
            if (isset($curl_conf[$k]) && is_array($curl_conf[$k]))
            {
                foreach ($extra_conf[$k] as $value)
                {
                    $curl_conf[$k][] = $value;
                }
            }
            else
            {
                $curl_conf[$k] = $v;
            }
        }
        return $this->urlExec($url, $params, $method, $curl_conf);
    }

    /**
     * 发送curl请求
     *
     * @param unknown $url            
     * @param unknown $params            
     * @param string $method            
     * @param unknown $extra_conf            
     * @throws \Exception
     * @return mixed
     * @version 2015-2-5 WZ
     */
    private function urlExec($url, $params = array(), $method = 'GET', $extra_conf = array())
    {
        $params = is_array($params) ? http_build_query($params) : $params;
        // 如果是get请求，直接将参数附在url后面
        if ($method == self::METHOD_GET)
        {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $params;
            $params = '';
        }
        // 默认配置
        $curl_conf = array(
            CURLOPT_URL => $url, // 请求url
            CURLOPT_HEADER => false, // 不输出头信息
            CURLOPT_RETURNTRANSFER => true, // 不输出返回数据
            CURLOPT_CONNECTTIMEOUT => 3
        ); // 连接超时时间
           
        // 配置post请求额外需要的配置项
        if ($method == self::METHOD_POST)
        {
            // 使用post方式
            $curl_conf[CURLOPT_POST] = true;
        }
        if ($params)
        {
            // post参数
            $curl_conf[CURLOPT_POSTFIELDS] = $params;
        }
        
        // 添加额外的配置
        foreach ($extra_conf as $k => $v)
        {
            $curl_conf[$k] = $v;
        }
        $data = false;
        try
        {
            // 初始化一个curl句柄
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            // 设置curl的配置项
            curl_setopt_array($ch, $curl_conf);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            // 发起请求
            $data = curl_exec($ch);
    
            if ($data === false)
            {
                throw new \Exception('CURL ERROR: ' . curl_error($ch));
            }
            $result = json_decode($data, true);
            if (isset($result['error']) && ! $this->getNewToken)
            {
                $this->getNewToken = true;
                $data = $this->getResult($this->type, $this->params, $this->method, $this->extra_conf);
            }
        }
        catch (\Exception $e)
        {
            echo $e->getMessage();
        }
        
        return $data;
    }
}
