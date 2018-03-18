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
     * @return Common\Response|string
     * @throws \Exception
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $session_id = $this->getSessionId();
        if(!$session_id){
            return STATUS_SESSION_EMPTY;
        }

        if(!$request->mobile || !$request->password){
            return STATUS_PASSWORD_EMPTY;
        }
        $user_table = $this->getUserTable();
        $user_table->password = md5($request->password);
        $user_table->mobile = $request->mobile;
        $user_info =$user_table->userLogin();

        if(!$user_info){
            return STATUS_PASSWORD_ERROR;
        }
        elseif(STATUS_STOP == $user_info['status']){

            return STATUS_USER_LOCKED;
        }
        else
        {
            $this->loginUpdate($user_info, 1);
            $response->status = STATUS_SUCCESS;
            $response->id = $user_info['id'];
        }
        return $response;
    }
}