<?php
namespace Admin\Model;

use Zend\Db\Sql\Where;
use Api\Model\CommonModel;

class AdminModel extends CommonModel{
    protected $table="nb_admin";

    public function index($condition)
    {
        
        $user = trim($condition['username']);
        $pass = MD5(MD5(trim($condition['password'])));
        $code = isset($condition['code']) ? trim($condition['code']) : '';
        if(!$user)
        {
            return array(
                'code' => 400,
                'message' => '请填写登录账号',
            );
        }
        if(!$pass)
        {
            return array(
                'code' => 400,
                'message' => '请填写登录密码',
            );
        }

        if(! empty($_SESSION['admin_login_count']) && $_SESSION['admin_login_count'] > 2)
        {
            if(!$code)
            {
                return array(
                    'code' => 400,
                    'message' => '请填写验证码',
                );
            }
            
            if (strtoupper($code)!=strtoupper($_SESSION['code'])) {
                return array(
                    'code' => 400,
                    'message' => '验证码错误',
                );
            }
        }
       
        $where = new Where();
        $where -> equalTo('delete', 0);
        $where -> equalTo('login_name', $user);
        $where -> equalTo('password', $pass);
        $list = $this->getOne($where, array("*"), 'admin');
        if($list)
        {
            if($list['status'] == 1){
                $this->updateData(array('last_login_time' => $this->getTime()), array('id' => $list['id']), 'admin');
                $role = $this->getOne(array('id' => $list['role_id'], 'delete' => '0'), array('*'), 'admin_role');
                if (! $role) {
                   return array(
                       'code' => 400,
                       'message' => '角色被删除，请联系超级管理员',
                   );
                }
                $_SESSION['admin_login_count'] = 0;
                $_SESSION['role_nb_admin_id']=$list['id'];
                $_SESSION['role_nb_admin_name']=$list['login_name'];
                $_SESSION['role_nb_admin_mobile']=$list['mobile'];
                $_SESSION['role_nb_admin_manage']=$role['manage'] == 'all' ? 'all' : json_decode($role['manage'], true);
                return array(
                    'code' => 200,
                    'message' => '操作成功',
                );
            }else{
                $_SESSION['admin_login_count'] = isset($_SESSION['admin_login_count']) ?  $_SESSION['admin_login_count'] + 1 : 1;
                return array(
                    'code' => 400,
                    'message' => '你的帐号已被冻结',
                    'identify' => ''
                );
            }
        }
        else{
            $_SESSION['admin_login_count'] = isset($_SESSION['admin_login_count']) ?  $_SESSION['admin_login_count'] + 1 : 1;
            return array(
                'code' => 400,
                'message' => '请检查你的登录账号和密码',
                'identify' => ''
            );
        }
    }

/**
     * 权限
     * authority
     * @param $controller
     * @return array
     */
    public function authority($controllerName)
    {
        $admin = $this->getOne(array(
            'id' => $_SESSION['role_nb_admin_id'],
            'login_name' => $_SESSION['role_nb_admin_name']
        ), array(
            "role_id"
        ), 'admin');
        if (! $admin['role_id']) {
            return array(
                'code' => 400,
                'message' => '无权限操作'
            );
        }
        $role = $this->getOne(array('id' => $admin['role_id']), array('manage'), 'admin_role');
       $role_manage = $role['manage'] == 'all' ? 'all' : json_decode($role['manage'], true);
        //var_dump($role_manage);exit;
        if(!is_array($controllerName)){
            if ( $role_manage == 'all' || in_array($controllerName, $role_manage)) {
                return array(
                    'code' => 200,
                    'message' => '操作成功'
                );
            }
            else {
                return array(
                    'code' => 400,
                    'message' => '无权限操作'
                );
            }
        }else{
            if ( $role_manage == 'all' || array_intersect($controllerName, $role_manage)) {
                return array(
                    'code' => 200,
                    'message' => '操作成功'
                );
            }
            else {
                return array(
                    'code' => 400,
                    'message' => '无权限操作'
                );
            }
        }
    }

    /**
     * 修改密码
     * @param unknown $params
     * @return multitype:number string 
     * @version YSQ
     */
    public function change($params)
    {
        $admin_id = $_SESSION['role_nb_admin_id'];
        $lis = $this->getOne(array('id' => $admin_id),array("*"), 'admin');
        $old  = trim($params['old']);
        $new  = trim($params['new1']);
        $tnew = trim($params['tnew']);
        if($new=='' || $old=='' || $tnew=='')
        {
            return array(
                'code' => 400,
                'message' => '字段填写不完整'
            );
        }

        if($lis['password'] != md5(md5($old)))
        {
            return array(
                'code' => 400,
                'message' => '旧密码不正确'
            );
        }

        if($new != $tnew)
        {
            return array(
                'code' => 400,
                'message' => '两次输入的密码不一致'
            );
        }

        $back = $this->updateData(array('password' => md5(md5($new)), 'timestamp_update' => $this->getTime()), array('id' => $admin_id),'admin');
        if($back)
        {
            return array(
                'code' => 200,
                'message' => '密码修成功'
            );
        }
        else
        {
            return array(
                'code' => 400,
                'message' => '密码修失败'
            );
        }

    }

}
