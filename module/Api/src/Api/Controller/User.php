<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;

class User extends CommonController
{

    /**
     * 普通用户
     *
     * @var 1
     */
    const USER_TYPE_NORMAL = 1;

    /**
     * 商家
     *
     * @var 2
     */
    const USER_TYPE_MERCHANT = 2;

    /**
     * 注册短信
     *
     * @var 1
     */
    const MOBILE_VALIDATE_TYPE_REGISTER = 1;

    /**
     *
     * @var 忘记密码/重置密码
     */
    const MOBILE_VALIDATE_TYPE_LOGIN_RESET = 2;

    /**
     * @var 设置支付密码
     */
    const MOBILE_VALIDATE_TYPE_PAY_CHECK = 3;

    /**
     *
     * @var 订单已经开始配送
     */
    const MOBILE_VALIDATE_TYPE_ORDER_RESET = 4;

    /**
     *
     * @var 修改手机
     */
    const MOBILE_VALIDATE_TYPE_BIND = 5;

    /**
     * 8.用户绑定银行卡V2.0
     * */
    const MOBILE_VALIDATE_TYPE_BIND_BANK = 8;


    /**
     * 9.用户提现V2.0
     * */
    const MOBILE_VALIDATE_TYPE_WITHDRAW = 9;

    /**
     * 11第三方注册 V2.0
     * */
    const THIRD_MOBILE_VALIDATE_TYPE_WITHDRAW = 11;

    /**
     * 临时
     *
     * @var 0
     */
    const MOBILE_VALIDATE_STATUS_TEMP = 0;

    /**
     * 已验证
     *
     * @var unknown
     */
    const MOBILE_VALIDATE_STATUS_USED = 1;

    /**
     * 发送失败
     *
     * @var unknown
     */
    const MOBILE_VALIDATE_STATUS_FAIL = 2;

    /**
     * 查看验证码 是否已经验证
     *
     * @author WZ
     * @param int $type
     *            1.(用户)注册
     * @param int $id
     *            短信验证码id
     * @param string $mobile
     *            手机号码
     * @return Ambigous false|object 短信表记录信息
     * @version 1.0.140325
     */
    public function checkSmsComplete($type, $id, $mobile)
    {
        $status = STATUS_SUCCESS;
        if(!$id || !$mobile){
            $status = STATUS_PARAMETERS_INCOMPLETE;
        }else{
            $sms_table = $this->getSmsCodeTable();
            $sms_table->id = $id;
            $data = $sms_table->getDetails();

            if(!$data || self::MOBILE_VALIDATE_STATUS_USED != $data['status'] || $mobile != $data['mobile'] || $type != $data['type']){

                // 数据不存在 或 未验证 或 手机不匹配 或 请求类型不匹配 返回错误
                $status = STATUS_CAPTCHA_ERROR;
            }
        }

        if(STATUS_SUCCESS != $status){
            $this->response($status);
        }
    }

    /**
     * @param $user_info
     * @param int $user_type
     * @throws \Exception
     * 登录成功之后更新login表和device_user表，用户的话还更新user表的last_time
     */
    public function loginUpdate($user_info, $user_type = self::USER_TYPE_NORMAL)
    {
        // 更新登录信息
        $this->updateLoginTable($user_info, $user_type);

        // 更新设备信息
        $this->updateDeviceUserTable($user_info, $user_type);

        // 用户更新最后登录时间
        $this->updateUserTable($user_info, $user_type);
    }

    /**
     * 更新登录表信息
     * @param $user_info
     * @param int $user_type
     * @return mixed
     */
    private function updateLoginTable($user_info, $user_type = self::USER_TYPE_NORMAL)
    {
        // login表 start
        // 其它session的登录状态设置成（用户）在别处登录
        $this->clearLoginUser($user_info['id'], $user_type);
        $login_table = $this->getLoginTable();
        //再更新login表信息
        $login_table->userId = $user_info['id'];
        $login_table->status = LOGIN_STATUS_LOGIN;
        $login_table->expire = date('Y-m-d H:i:s', time() + 3600 * 24 * 30);
        $login_table->sessionId = $this->getSessionId();
        $login_table->userType = $user_type;
        return $login_table->updateLogin();
        // login表 end
    }

    /**
     * 设置这个用户的其它设备登录状态为（用户）在别处登录
     * 用于登录时候使用。
     *
     * @param unknown $user_id
     */
    private function clearLoginUser($user_id, $user_type = self::USER_TYPE_NORMAL)
    {
        $login_table = $this->getLoginTable();
        $login_table->status = LOGIN_STATUS_OTHER_LOGIN;
        $login_table->userId = $user_id;
        $login_table->userType = $user_type;
        $login_table->updateElsewhereLogin();
    }

    /**
     * 更新用户设备表的数据
     *
     * @param unknown $user_info
     * @param unknown $login
     */
    private function updateDeviceUserTable($user_info, $user_type = self::USER_TYPE_NORMAL)
    {
        $login_table = $this->getLoginTable();
        $login_table->sessionId = $this->getSessionId();
        $login = $login_table->getSessionId();

        // 清除用户与设备的联系，再把这个设备绑定上这个用户。
        $this->clearDeviceUser($user_info['id'], $user_type);
        $device_user_table = $this->getDeviceUserTable();
        $device_user_table->userId =  $user_info['id'];
        $device_user_table->deviceToken = $login['device_token'];
        $device_user_table->userType = $user_type;
        $device_user_table->bindingDevice();
    }

    /**
     * 清除用户与设备的关联，
     * 用于登录时候使用。
     *
     * @param unknown $user_id
     */
    public function clearDeviceUser($user_id, $user_type = self::USER_TYPE_NORMAL)
    {
        $device_user_table = $this->getDeviceUserTable();
        $device_user_table->userId =  $user_id;
        $device_user_table->userType = $user_type;
        $device_user_table->clearDeviceUser();
    }

    /**
     * @param $user_info
     * @param int $user_type
     * @throws \Exception
     */
    private function updateUserTable($user_info, $user_type = self::USER_TYPE_NORMAL)
    {
        if(self::USER_TYPE_NORMAL == $user_type){
            $user_model = $this->getUserTable();
            $user_model->id = $user_info['id'];
            $user_model->lastLoginTime = $this->getTime();
            /*if($user_info['head_image_id'])
            {
                $user_model->headImageId = null;
            }*/
            $user_model->updateData();
        }
        elseif(self::USER_TYPE_MERCHANT == $user_type)
        {
            $MerchantTable = $this->getMerchantTable();
            $MerchantTable->id = $user_info['id'];
            $MerchantTable->updateData();
        }
    }
}
