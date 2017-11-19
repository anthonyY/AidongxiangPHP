<?php
namespace Api\Controller;

use Api\Controller\Request\UserRequest;
use Platform\Model\LogGateway;
use Zend\Db\Sql\Where;
use Zend\Stdlib\ArrayUtils;
use Core\System\AiiUtility\AiiEasemobApi\AiiEasemobApi;

/**
 * 用户登录，返回用户id
 *
 * @author WZ
 *
 */
class UserLogin extends User
{

    public function __construct()
    {
        $this->myRequest = new UserRequest();
        parent::__construct();
    }

    /**
     *
     * @return string|\Api\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $session_id = $this->getSessionId();
        if(!$session_id){
            return STATUS_SESSION_EMPTY;
        }

        if(!$request->name || !$request->password){
            return STATUS_PASSWORD_EMPTY;
        }
        $user_table = $this->getUserTable();
        $user_table->password = strtoupper(md5(strtoupper($request->password)));
        $user_table->mobile = $request->name;
        $user_info =$user_table->userLogin();

        if(!$user_info){
            return STATUS_PASSWORD_ERROR;
        }
        elseif(STATUS_STOP == $user_info['status']){

            return STATUS_USER_LOCKED;
        }
        else
        {
            // 更新各个表
            $mallQueryAccountBalanceScore = new mallQueryAccountBalanceScore();
            $mallQueryAccountBalanceScore->mobileNo = $user_info->mobile;
            $mallQueryAccountBalanceScore->userId = $user_info->user_id;
            $res = $mallQueryAccountBalanceScore->submit();
            $respond = $mallQueryAccountBalanceScore->getRespCode();
            if($respond['respCode'] == 0)
            {
                $user_table->cash = $res->balance;
                $user_table->points = $res->score;
                $user_table->id = $user_info['id'];
                $user_table->updateData();
            }
            $this->loginUpdate($user_info, 1);
            $response->status = STATUS_SUCCESS;
            $response->id = $user_info['uuid'];
            $_SESSION['user_id'] = $user_info['id'];
            $_SESSION['user_name'] = $user_info['name'];
            //记录用户登录日志
            $logModel = new LogGateway($this->adapter);
            $logModel->setUserLog($user_info->id, '手机wap网站');
        }
        return $response;
    }
}