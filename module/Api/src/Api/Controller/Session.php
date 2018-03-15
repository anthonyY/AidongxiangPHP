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
     * @return Common\Response
     * @throws \Exception
     * 返回一个数组或者Result类
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        // 判断数据表中没有记录此设备，若没有，则记录
        $request->action = $request->action ? $request->action : 1;

        $login_table = $this->getLoginTable();
        $login_table->userType = $request->action;
        $login_table->deviceToken = $request->deviceToken;
        $login = $login_table->getDeviceToken();//查询设备号是否存在

        $session_id = $this->getSessionId();
        if($login){
            // 已经有过该设备直接返回session id
            $this->setSessionId($login->session_id);
            $login_table_update = $this->getLoginTable();
            $login_table_update->version= $request->version;
            // 会话id不一样的话，把请求的会话状态更新到当前的会话中
            if($session_id && $session_id != $login->session_id)
            {
                $login_table = $this->getLoginTable();
                $login_table->sessionId = $session_id;
                $login_table->userType = $request->action;
                $login_before = $login_table->getSessionId();
                if($login_before)
                {
                    $login_table_update->userId = $login_before['user_id'];
                    $login_table_update->status = $login_before['status'];
                    $login_table_update->expire = $login_before['expire'];
                    $expire= $login_before['expire'];
                    $login_table = $this->getLoginTable();
                    $login_table->expire = $this->getTime();
                    $login_table->status = LOGIN_STATUS_LOGOUT;
                    $login_table->id = $login_before['id'];
                    // 设置旧的会话为过期
                    $login_table->updateData();
                    // 清除旧的会话的绑定用户
                    $device_user_table = $this->getDeviceUserTable();
                    $device_user_table->deviceToken = $login_before['device_token'];
                    $device_user_table->userType = $request->action;
                    $device_user_table->userId = 0;
                    $device_user_table ->bindingDevice();
                    // 把请求的用户 绑定到 当前的设备上
                    $device_user_table->deviceToken = $request->deviceToken;
                    $device_user_table->userType = $request->action;
                    $device_user_table->userId = $login_before['user_id'];
                    $device_user_table ->bindingDevice();
                }
            }
            $login_table_update->id = $login->id;
            // 更新客户端的版本号
            $login_table_update->updateData();
            // 设置最终结果的值
            $response->status = STATUS_SUCCESS;
            $response->expire = isset($expire) ? $expire : $login->expire;
        }else{
            // 第一次访问没有sessionId的时候生成一个sessionId
            $this->setSessionId($this->makeSessionId());
            $columns = $login_table->getTableColumns();
            foreach($columns as $v)
            {
                if(isset($request->$v) && $request->$v)
                {
                    $login_table->$v = $request->$v;
                }
            }

            $login_table->sessionId = $this->getSessionId();
            $login_table->status = LOGIN_STATUS_TEMP;
            $login_table->userType = $request->action;
            $login_table->timestamp = $this->getTime();

            if($session_id){
                $login_table_select = $this->getLoginTable();
                $login_table_select->sessionId = $session_id;
                $login_table_select->userType = $request->action;
                $login_before = $login_table_select->getSessionId();
                if($login_before){
                    $login_table->userId = $login_before['user_id'];
                    $login_table->status = $login_before['status'];
                    $login_table->expire = $login_before['expire'];
                    $expire= $login_before['expire'];

                    // 设置旧的会话为过期
                    $login_table_update = $this->getLoginTable();
                    $login_table_update->expire = $this->getTime();
                    $login_table_update->status = LOGIN_STATUS_LOGOUT;
                    $login_table_update->id = $login_before['id'];
                    $login_table_update->updateData();
                    // 清除旧的会话的绑定用户
                    $device_user_table = $this->getDeviceUserTable();
                    $device_user_table->deviceToken = $login_before['device_token'];
                    $device_user_table->userType = $request->action;
                    $device_user_table->userId = 0;
                    $device_user_table ->bindingDevice();
                }
            }
            // 插入到login表
            $dbResult =$login_table->addData();

            // 插入到device_user表
            $device_user_table = $this->getDeviceUserTable();
            $device_user_table->deviceToken = $request->deviceToken;
            $device_user_table->deviceType = $request->deviceType;
            $device_user_table->badge = OPEN_TRUE;
            $device_user_table->alert = OPEN_TRUE;
            $device_user_table->sound = OPEN_TRUE;
            $device_user_table->userId = isset($login_before) ? $login_before['user_id'] : 0;
            $device_user_table->userType = $request->action;
            $device_user_table->timestamp = $this->getTime();
            $device_user_table->addData();

            // 设置最终结果的值
            $response->status = ($dbResult ? STATUS_SUCCESS : STATUS_UNKNOWN);
            $response->expire = isset($expire) ? $expire : '';
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

?>