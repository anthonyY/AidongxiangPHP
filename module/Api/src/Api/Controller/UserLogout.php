<?php
namespace Api\Controller;

/**
 * 退出登录
 *
 * @author WZ
 *
 */
class UserLogout extends User
{

    /**
     *
     * @return string
     */
    public function index()
    {
        $response = $this->getAiiResponse();
        // 检查登录状态
        //         $this->checkLogin();
        // 退出登录
        $this->userLogout();

        return STATUS_SUCCESS;
    }

    /**
     * 退出登录
     *
     * @author WZ
     */
    private function userLogout()
    {
        if(LOGIN_STATUS_LOGIN == $this->getUserStatus()){
            $this->clearDeviceUser($this->getUserId(), $this->getUserType());
        }
        // 再把登录表的状态改变成登出状态。
        $login_table =  $this->getLoginTable();
        $login_table->status = LOGIN_STATUS_LOGOUT;
        $login_table->userId= 0;
        $login_table->sessionId= $this->getSessionId();
        $login_table->updateLogout();
    }
}
