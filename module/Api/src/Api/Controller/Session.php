<?php
namespace Api\Controller;
use Api\Controller\Request\SessionRequest;

/**
 * 获取会话ID，根据设备号生成32位由小写英文字母与数字组成的随机字符串。<br />
 * 设备号与Session Id是一对一关系。
 *
 * @author WZ
 * @version 1.0.140717 WZ
 */
class Session extends CommonController
{
    /**
     * 设备号长度限制
     * 
     * @var number
     */
    const DEVICE_LENGTH = 16;
    
    /**
     * 生成Session的长度
     * 
     * @var number
     */
    const SESSION_LENGTH = 32;

    public function __construct()
    {
        $this->myRequest = new SessionRequest();
        parent::__construct();
    }

    /**
     * 返回一个数组或者Result类
     *
     * @return \Api21\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();

        // 判断数据表中没有记录此设备，若没有，则记录
        if (strlen($request->device_token) < self::DEVICE_LENGTH)
        {
            $request->device_token = $this->makeSessionId();
            $request->device_type = 32;

        }
        
        $model = $this->getModel('Session');
        $where = array(
            'device_token' => $request->device_token
        );
        $login = $model->getOne($where, null, 'login');
        
        $user = false;
        $old_session = $this->getSessionId();
        if ($old_session && (! $login || $login['session_id'] != $old_session)) {
            // 有旧session，清除旧seesion的用户信息，添加到新session中
            $user = $model->getOne(array('session_id' => $old_session), null, 'login');
            $model->updateData(array('user_id' => 0, 'user_name' => ''), array('id' => $user['id']), 'login');
        }
        if ($login)
        {
            // 已经有过该设备直接返回session id
            $this->setSessionId($login->session_id);
            $set = array(
                'version' => $request->version,
            );
            if ($user) {
                $set['user_id'] = $user['user_id'];
                $set['user_name'] = $user['user_name'];
            }
            // 更新客户端的版本号
            $model->updateData($set, array(
                'id' => $login->id
            ), 'login');
            
            $set = array(
                'environment' => $request->environment
            );
            $model->updateData($set, array(
                'device_token' => $request->device_token
            ), 'device_user');
            
            // 设置最终结果的值
            $response->status = STATUS_SUCCESS;
        }
        else
        {
            // 第一次访问没有sessionId的时候生成一个sessionId
            $this->setSessionId($this->makeSessionId());
            
            $keys = array(
                'model',
                'version',
                'resolution',
                'device_token',
                'device_type',
                'info'
            );
            $data = $request->getValues($keys);
            $data['session_id'] = $this->getSessionId();
            $data['status'] = LOGIN_STATUS_TEMP;
            if ($user) {
                $data['user_id'] = $user['user_id'];
                $data['user_name'] = $user['user_name'];
            }
            // 插入到login表
            $dbResult = $model->insertData($data, 'login');
            
            if (SINGLE_SIGN_ON_TYPES)
            {
                $sso_device_type = explode(',', SINGLE_SIGN_ON_TYPES);
                if (in_array($data['device_type'], $sso_device_type))
                {
                    $device_data = array(
                        'device_token' => $data['device_token'],
                        'device_type' => $data['device_type'],
                        'badge' => OPEN_TRUE,
                        'alert' => OPEN_TRUE,
                        'sound' => OPEN_TRUE,
                        'environment' => $request->environment,
                        'user_id' => 0,
                        'user_type' => 0,
                        'delete' => DELETE_FALSE,
                    );
                    $model->saveDeviceUser($device_data);
                }
            }
            
            // 设置最终结果的值
            $response->status = ($dbResult ? STATUS_SUCCESS : STATUS_UNKNOWN);
        }
        return $response;
    }
    
    /**
     * 给session_id赋值
     *
     * @param string $session_id            
     * @author WZ
     * @version 1.0.140514 WZ
     */
    private function setSessionId($session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * 生成随机字符串
     *
     * @param int $length            
     * @return string
     */
    private function makeSessionId()
    {
        return $this->makeCode(self::SESSION_LENGTH, self::CODE_TYPE_LOWERCASE + self::CODE_TYPE_NUMBER);
    }
}