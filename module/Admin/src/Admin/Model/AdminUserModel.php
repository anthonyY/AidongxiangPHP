<?php
namespace Admin\Model;

use Zend\Db\Sql\Where;
use Core\System\WxApi\WxApi;
use Api\Model\CommonModel;

class AdminUserModel extends CommonModel
{

    protected $table = 'user';

    /**
     *  管理后台用户列表
     * @param unknown $condition
     * @return Ambigous <multitype:, multitype:unknown , multitype:multitype:mixed  number >
     * @version YSQ
     */
 public function index($condition,$boolean=true)
    {
        $where = new Where();
        $where->equalTo(DB_PREFIX.'user.delete', 0);
//         $where->notEqualTo(DB_PREFIX.'user.group_status', 2);
        
        if(isset($condition['where']['cid']) && $condition['where']['cid']) {//用户职务
            $where->equalTo(DB_PREFIX.'position.id',  $condition['where']['cid']);
        }
        if (isset($condition['where']['sex']) && $condition['where']['sex']) {//性别
            $where->equalTo(DB_PREFIX.'user.sex', $condition['where']['sex']);
        }
        if (isset($condition['where']['status']) && $condition['where']['status']) {//账号状态:1正常;2禁用
            $where->equalTo(DB_PREFIX.'user.status', $condition['where']['status']);
        }
        
        $data = array(
//             'columns' => array(
//                 'u_id' => 'id',
//                 'u_name' => 'nickname',
//                 'u_mobile' => 'mobile',
//                 'u_type' => 'type',
//                 'u_status' => 'status',
//             ),
            'join' => array(
                array(
                    'name' => DB_PREFIX.'position',
                    'on' => DB_PREFIX.'position.id = '.DB_PREFIX.'user.position',
                    'columns' => array(
                        'p_name' => 'name'//用户职务
                    ),
                    'type' => 'left'
                ),
            ),
            'order' => array(
              DB_PREFIX.'user.id' => 'DESC'
            ),
            'need_page' => $boolean
        );
        
        if (isset($condition['where']['keyword']) &&$condition['where']['keyword']) {
            $data['search_key'] = array(
                DB_PREFIX . 'user.name' => $condition['where']['keyword'],
                DB_PREFIX . 'user.mobile' => $condition['where']['keyword'],
            );
        }
        $list = $this->getAll($where, $data, $condition['page'], null, "user");

        return $list;
    }
    
    function getPositionList(){
        $list = $this->getDataByIn(array('delete'=>0),array('order'=>array('id'=>'asc')),'position');
        return $list?$list:array();
    }
    
    /**
     * 导出excel
     *
     * @version YSQ
     */
    public function setExcel($condition){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能导入
        $list = $this->index($condition,false);
        $time = $this->getTime();
        foreach ($list['list'] as $k => $v){
            $arr[$k]['name'] = $v['name'];
            $arr[$k]['p_name'] = $v['p_name'];
            $arr[$k]['sex'] = $v['sex']==1?'男':'女';
            $arr[$k]['mobile'] = $v['mobile'];
            $arr[$k]['region'] = $this->regionInfoToString($v['region_info'],'-');
            $arr[$k]['good_type'] = $v['member_time']>$time?'是（'.$v['member_time'].'到期）':'不是';
            $arr[$k]['amount'] = $v['amount'] .'元';
            $arr[$k]['consumption'] = $v['consumption'] .'元';
            $arr[$k]['timestamp'] = $v['timestamp'];
            $arr[$k]['referrer_num'] = $v['referrer_num'] .'人';
            $arr[$k]['signature'] = $v['signature'];
            $arr[$k]['status'] = $v['status']==1?'正常':'禁用';
        }
        $allRoomList = $arr;
    
        $filename='用户信息列表';
        $headArr=array("用户名","职务","性别","手机号码","所在地区","是否会员","钱包余额","消费总额","注册时间","推荐人数","个性签名","帐号状态");
        $return = $this->getExcel($filename,$headArr,$allRoomList);
        if($return){
            return array(
                'code' =>200,
                'message' =>'成功',
            );
        }
    }
    /**
     * 设置冻结/启用(ajax)
     * @param array $params
     * @return array
     * @version YSQ
     */
    public  function setUserDelete($params){
        $id = isset($params['id']) ? (int) $params['id'] : 0; // 用户ID
        $status = isset($params['status']) ? (int) $params['status'] : 0; // 1启用；2禁用；
        if (!$id || !in_array($status,array(1,2))) {
            return array(
                'code' => '300',
                'message' => '请求参数不正确!'
            );
        }
        $user_info = $this->getOne(array(
            'id' => $id
        ), array(
            'status'
        ), 'user');
        if (in_array($user_info['status'],array(1,2))) {
            if ($user_info['status'] == $status) {
                return array(
                    'code' => '400',
                    'message' => '错误操作!'
                );
            }
            $row = $this->updateData(array(
                'status' => $status
            ), array(
                'id' => $id
            ), 'user');
            if ($row) {
                return array(
                    'code' => '200',
                    'message' => '操作成功!'
                );
            }
        }
        return array(
            'code' => '400',
            'message' => '未知错误!'
        );
    }
    
    /**
     * 得到用户详情
     * @param unknown $id
     * @return multitype:number string Ambigous <boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown> |multitype:number string
     * @version YSQ
     */
    function getUserDetails($id){
        if (!$id) {
            return array(
                'code' => '300',
                'message' => '请求参数不完整!'
            );
        }
        $user_info = $this->getOne( array('id'=>$id,'delete'=>0),array('*'),'user');
        
        if($user_info)
        {
            $user_position_info = $this->getOne( array('id'=>$user_info['position'],'delete'=>0),array('*'),'position');
//             if(!$user_position_info){
//                 return array(
//                     'code' => 400,
//                     'message' => '用户职务信息获取失败',
//                 );
//             }
            $user_info['p_name'] =  $user_position_info['name'];
            if($user_info['region_info']){
                $user_info['region'] = $this->regionInfoToString($user_info['region_info'],'-');
            }else{
                $user_info['region'] = '';
            }
        }else{
            return array(
                'code' => 400,
                'message' => '用户信息获取失败',
            );
        }
            return array(
                'code' => 200,
//                 'message' => '操作成功',
                'info' => $user_info,
            );
    }
    
    
    /**
     * 得到页码数
     * @param int $page
     * @param int $total
     * @return array
     * @version YSQ
     */
    public function getPageSum($page, $total2, $limit=0){
        $limit = $limit == 0 ? PAGE_NUMBER : $limit;
        $total = ceil($total2/$limit);
        $page_info= array(
            'total' => $total,
            'page' => $page,
            'page_1' => $page-1,
            'page_2' => $page+1,
            'previous' => $page<=1?true:false,
            'net' => $page>=$total?true:false,
        );
        
//         $page_info['pagesInRange'] = $this->getPageSum($page, $total);
        if($page <= 4){
            for($i=1;$i<=8;$i++){
                $pagesInRange[] = $i;
                if($i>=$total){
                    break;
                }
            }
        }elseif($page>($total-4)){
            if(($total-8)<=0){
                for($i=1;$i<=$total;$i++){
                    $pagesInRange[] = $i;
                }
            }else{
                for($i=$total-8;$i<=$total;$i++){
                    $pagesInRange[] = $i;
                    if($i>=$total){
                        break;
                    }
                }
            }
        }else{
            for($i=$page-3;$i<=$page+4;$i++){
                $pagesInRange[] = $i;
                if($i>=$total){
                    break;
                }
            }
        }
        $page_info['pagesInRange'] = $pagesInRange;
        return $page_info;
    }
    
    /**
     * 用户课程列表
     * @param unknown $condition
     * @version YSQ
     */
    function getUserCourseList($condition){
        $s_where = $condition['where'];
        if(!$s_where['id']){
            return array(
                'code' => '300',
                'message' => '请求参数不完整!'
            );
        }
        $where = new Where();
        $where->equalTo(DB_PREFIX.'buy_log.delete', 0);
        $where->equalTo(DB_PREFIX.'buy_log.user_id', $s_where['id']);
        if($s_where['type']){
            $where->equalTo(DB_PREFIX.'audio.type', $s_where['type']);
        }
        $data = array(
            'columns'=>array('audio_id'),
            'join'=>array(
                array(
                    'name' => DB_PREFIX.'audio',
                    'on' => DB_PREFIX.'audio.id = '.DB_PREFIX.'buy_log.audio_id',
                    'columns' => array(
                        '*'
                    ),
                    'type' => 'left'
                ),
                array(
                    'name' => DB_PREFIX.'teacher',
                    'on' => DB_PREFIX.'teacher.id = '.DB_PREFIX.'audio.teacher_id',
                    'columns' => array(
                        't_name' => 'name',
                    ),
                    'type' => 'left'
                )
            ),
            'need_page' => true,
        );
        $list = $this->getAll($where,$data,$condition['page'],$condition['limit'],'buy_log');
        return $list;
    }
    
//     /**
//      * 修改用户专家头衔
//      * @param unknown $id
//      * @version YSQ
//      */
//     function amendUserInfo($id,$type,$keyword){
//         if(!$id){
//             return array(
//                 'code' =>'300',
//                 'message' => '请求参数不完整!'
//             );
//         }
//         if($type==0){
//             if(!$keyword){
//                 return array(
//                     'code' =>'300',
//                     'message' => '请求参数不完整!'
//                 );
//             }
//         }
//        $user_info = $this->getOne(array('id'=>$id),array('title'),'user');
// //        var_dump($keyword);exit;
//        if($user_info && ($user_info['title']==$keyword)){
//            return array(
//                'code' =>'400',
//                'message' => '参数没有改变!'
//            );
//        }
//         $user_row = $this->updateData(array('title'=>$keyword), array('id'=>$id),'user');
//         if(!$user_row){
//             return array(
//                 'code' =>'400',
//                 'message' => '修改用户专家头衔失败!'
//             );
//         }
//         return array(
//             'code' =>'200',
//             'message' => '操作成功!'
//         );
//     }
    
//     /**
//      * 新增用户页面(post)
//      * @version YSQ
//      */
//     function addUserInfo(){
//      if( $_POST['jjb'] == 2321){
//             $name = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';//用户名
//             $mobile = isset($_POST['mobile']) ? (string)$_POST['mobile'] : '';//移动电话（登录账号）
//             $password = isset($_POST['password']) ? trim($_POST['password']) : '';//	密码，md5加密\
//             $title = isset($_POST['title']) ? (string)$_POST['title'] : '';//专家头衔
//             $image = isset($_POST['img_path']) ? (string)$_POST['img_path'] : '';//	管理员头像
            
//             if(!($mobile && $name && $password && $title && $image)){
//                 return array(
//                     'code' =>'300',
//                     'message' => '请求参数不完整!'
//                 );
//             }
//             $info = $this->getOne(array('mobile'=>$mobile,'delete'=>0),array('id'),'user');
//             if(!$info){
//                 $data =array();
//                 $password_s = md5($password);
//                 $data =array(
//                     'nickname' => $name,
//                     'mobile' => $mobile,
//                     'password' => $password_s,
//                     'image' => $image,
//                     'title' => $title,
// //                     'type' => 11,
//                 );
//                 $id = $this->insertData($data,'user');
//                 if($id){
//                     $stat_id = $this->insertData(array('user_id'=>$id),'user_stat');
//                     return array(
//                         'code' =>'200',
//                         'message' => '成功!'
//                     );
//                 }
//                 return array(
//                     'code' =>'400',
//                     'message' => '新增用户失败!',
//                 );
//             }
//             return array(
//                 'code' =>'400',
//                 'message' => '该登录账号已注册,请注册其他号码!',
//             );
//         }
//     }

//     /**
//      * 查询用户详情
//      *
//      * @param number $id            
//      * @return boolean|Ambigous mixed>
//      * @version 2016-8-29 WZ
//      */
//     public function getUserInfo($id = 0)
//     {
//         if (! $id) {
//             $id = empty($_SESSION['user_id']) ? 0 : $_SESSION['user_id'];
//         }
//         if (! $id) {
//             return false;
//         }
        
//         $where = array(
//             'id' => $id
//         );
//         $info = $this->getOne($where, array(
//             '*'
//         ), 'q_user');
//         return $info;
//     }

//     /**
//      * 检查登录、自动登录
//      *
//      * @return multitype:number string |boolean|multitype:number string unknown Ambigous <>
//      * @version 2016-8-29 WZ
//      */
//     public function checkLogin()
//     {
//         // $_SESSION['user_id'] = 1; // @todo 上线的时候要删除，先用用户1
//         // $_SESSION['user_id'] = 0;
//         $result = array(
//             'code' => 200,
//             'message' => '登录成功',
//             'data' => ''
//         );
//         if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
//             $result['user_id'] = $_SESSION['user_id'];
//             return $result;
//         }
//         else {
//             return array('code' => '404', 'message' => '未登录');
//         }
//         // 未开启自动登录，若要开启，注释上方三行代码
//         $_SESSION['openid'] = 'test';
//         // $_SESSION['openid'] = $this->getOpenid();
//         if ($_SESSION['openid']) {
//             $user_partner = $this->getOne(array(
//                 'open_id' => $_SESSION['openid']
//             ), array(
//                 '*'
//             ), 'q_user_partner');
            
//             if ($user_partner) {
//                 if ($user_partner['user_id']) {
//                     $user = $this->getOne(array(
//                         'id' => $user_partner['user_id'],
//                         'delete' => DELETE_FALSE
//                     ), array(
//                         '*'
//                     ), 'q_user');
                    
//                     if ($user) {
//                         if ($user['status'] == 1) {
//                             return array(
//                                 'code' => '200',
//                                 'user_id' => $user_partner['user_id']
//                             );
//                         } elseif ($user['status'] == 2) {
//                             return array(
//                                 'code' => '401',
//                                 'message' => '待审核',
//                                 'user_id' => $user_partner['user_id']
//                             );
//                         } elseif ($user['status'] == 3) {
//                             return array(
//                                 'code' => '402',
//                                 'message' => '已拒绝',
//                                 'user_id' => $user_partner['user_id']
//                             );
//                         }
//                     } else {
//                         // 用户已删除
//                         $this->updateData(array(
//                             'user_id' => 0
//                         ), array(
//                             '*'
//                         ), 'q_user_partner'); // 清除微信绑定
//                         return array(
//                             'code' => '404',
//                             'message' => '用户不存在/用户未绑定',
//                             'user_id' => 0
//                         );
//                     }
//                 } else {
//                     return array(
//                         'code' => '404',
//                         'message' => '用户不存在/用户未绑定',
//                         'user_id' => 0
//                     );
//                 }
//             } else {
//                 // 没有第三方记录
//                 if ($_SESSION['openid'] == 'test') {
//                     $user['nickname'] = '测试';
//                     $user['headimgurl'] = 'https://ss1.baidu.com/6ONXsjip0QIZ8tyhnq/it/u=2028306100,2201833418&fm=80';
//                 } else {
//                     $user = $this->getWeixinUserInfo($_SESSION['openid']);
//                 }
//                 $data = array(
//                     'open_id' => $_SESSION['openid'],
//                     'nickname' => empty($user['nickname']) ? $user['nickname'] : '',
//                     'image_url' => empty($user['headimgurl']) ? $user['headimgurl'] : '',
//                     'partner' => 3,
//                     'user_id' => 0,
//                     'timestamp' => $this->getTime()
//                 );
//                 $this->insertData($data, 'q_user_partner');
//                 return array(
//                     'code' => '404',
//                     'message' => '用户不存在/用户未绑定',
//                     'user_id' => 0
//                 );
//             }
//         }
        
//         return array(
//             'code' => '500',
//             'message' => '异常，不能获取openid'
//         );
//     }
    
//     /**
//      * 登录
//      * 
//      * @param unknown $param  mobile, password密码不要加密
//      * @version 2016-9-5 WZ
//      */
//     function login($param) {
//         $where = array(
//             'mobile' => empty($param['mobile']) ? '' : $param['mobile'],
//             'password' => empty($param['password']) ? '' : md5($param['password']),
//             'delete' => 0
//         );
//         $info = $this->getOne($where, array('*'), 'q_user');
//         return $info;
//     }

//     /**
//      * 获取用户第三方信息
//      *
//      * @param string $openid            
//      * @return Ambigous <boolean, mixed>
//      * @version 2016-8-30 WZ
//      */
//     public function getUserPartner($openid)
//     {
//         return $this->getOne(array(
//             'open_id' => $openid
//         ), array(
//             '*'
//         ), 'q_user_partner');
//     }

//     /**
//      * 微信绑定用户
//      *
//      * @param string $openid            
//      * @param string $mobile            
//      * @version 2016-8-30 WZ
//      */
//     public function partnerBindUser($openid, $mobile)
//     {
//         $user = $this->getOne(array(
//             'mobile' => $mobile
//         ), array(
//             '*'
//         ), 'q_user');
//         if (! $user) {
//             $user_partner = $this->getOne(array(
//                 'open_id' => $openid
//             ), array(
//                 '*'
//             ), 'q_user_partner');
//             if (! $user_partner) {
//                 return false;
//             }
//             $image = $this->getImageForController($user_partner['image_url']);
            
//             $user = array(
//                 'department_id' => 0,
//                 'username' => '',
//                 'mobile' => $mobile,
//                 'password' => md5(mt_rand(10000000, 99999999)),
//                 'nickname' => $user_partner['nickname'],
//                 'img' => empty($image['files'][0]) ? '' : $image['files'][0]['file']['path'],
//                 'status' => 2,
//                 'delete' => DELETE_FALSE,
//                 'timestamp' => $this->getTime()
//             );
//             $user['id'] = $this->insertData($user, 'q_user');
//         }
//         $this->updateData(array(
//             'user_id' => $user['id']
//         ), array(
//             'open_id' => $openid
//         ), 'q_user_partner');
//     }

//     /**
//      * 根据企业微信号查找用户信息
//      *
//      * @return multitype:number string multitype: Ambigous <\Core\System\WxApi\mixed, mixed>
//      * @version 2016-8-23 WZ
//      */
//     public function getWeixinUserInfo($openid)
//     {
//         $wxapi = new WxApi();
//         return $user = $wxapi->GetUser($openid);
//     }

//     public function AddUser($params)
//     {
//         $department_id = isset($params['department_id']) ? $params['department_id'] : 0;
//         $password = $params['password']; // 密码，传过来之前不要加密
//         $mobile = $params['mobile']; // 手机号码
//         $nickname = $params['nickname']; // 昵称，姓名
//         $img = empty($params['path']) ? '' : $params['path']; // 头像

//         $province=$params['province'];
//         $city=$params['city'];
//         if (! empty($params['image'])) {
//             // 2016.09.07 前端改为插件上传
//             $upload = $this->uploadImageForBase64($params['image']);
//             $img = $upload['path'];
//         }
//         $status = isset($params['status']) ? $params['status'] : 1; // 1可用，2待审核，3已拒绝
        
//         $info = $this->getOne(array('mobile' => $mobile,'delete'=>0), array('*'), 'q_user');
//         if ($info) {
//             return array(
//                 'code' => 400,
//                 'message' => '用户已存在'
//             );
//         }
        
//         $set = array(
//             'username' => '',
//             'department_id' => $department_id,
//             'password' => $password,
//             'mobile' => $mobile,
//             'nickname' => $nickname,
//             'img' => $img,
//             'status' => $status,
//             'timestamp' => $this->getTime(),
//             'p_id'=>$province,
//             'c_id'=>$city,
//         );
//         if (! $set['password']) {
//             unset($set['password']);
//         } else {
//             $set['password'] = md5($set['password']);
//         }
//         $back = $this->insertData($set, "q_user");
        
//         if ($back) {
//             return array(
//                 'code' => 200,
//                 'message' => '用户增加成功',
//                 'user_id' => $back
//             );
//         } else {
//             return array(
//                 'code' => 400,
//                 'message' => '用户增加失败'
//             );
//         }
//     }

//     /**
//      * 更新用户个人信息
//      */
//     function updateUser($params)
//     {
//         $key = array(
//             'department_id',
//             'username',
//             'mobile',
//             'password',
//             'nickname',
//             'img',
//             'status',
//             'delete'
//         );
//         $set = array();
//         $id = empty($params['id']) ? 0 : (int) $params['id'];
//         foreach ($key as $k) {
//             if (isset($params[$k])) {
//                 $set[$k] = $params[$k];
//             }
//         }
//         if (isset($params['province'])) {
//             $set['p_id'] = $params['province'];
//         }
//         if (isset($params['city'])) {
//             $set['c_id'] = $params['city'];
//         }
//         if ($set && $id) {
//             $where = array(
//                 'id' => $id
//             );
//             $this->updateData($set, $where, 'q_user');
//         }
//         return array(
//             'code' => '200',
//             'message' => ''
//         );
//     }
    
//     /*
//      * 用户详情
//      *
//      */
//     public function UserDetail($id)
//     {
//         $user = $this->getOne(array(
//             'id' => $id
//         ), array(
//             "*"
//         ), "q_user");

//         $where = new Where();
//         $where->equalTo('q_department.delete', 0);

//         $category = $this->getAll($where, null, null, null, 'q_department');
//         foreach ($category['list'] as $k => $v) {
//             $cate[$v['id']] = $v['name'];
//         }
        
//         $list = array(
//             'list' => $user,
//             'category' => $cate
//         );
//         return $list;
//     }

//     public function UserModify($params)
//     {
//         // $this->p($params);
//         $name = $params['name'];
//         $password = $params['password'];
//         $mobile = $params['mobile'];
        
//         $department = $params['department'];
//         $nickname = $params['nickname'];
        
//         $img = $params['path'];
        
//         $timestamp = $this->getTime();
        
//         $set = array(
//             'username' => $name,
//             'password' => $password,
//             'department_id' => $department,
//             'mobile' => $mobile,
//             'img' => $img,
//             'nickname' => $nickname,
//             'timestamp' => $timestamp
//         );
//         if (isset($params['province'])) {
//             $set['p_id'] = $params['province'];
//         }
//         if (isset($params['city'])) {
//             $set['c_id'] = $params['city'];
//         }
//         if (! $set['password']) {
//             unset($set['password']);
//         } else {
//             $set['password'] = md5($set['password']);
//         }
        
//         $where = array(
//             'id' => $params['id']
//         );
//         $back = $this->updateData($set, $where, "q_user");
        
//         if ($back) {
//             return array(
//                 'code' => 200,
//                 'message' => '用户编辑成功'
//             );
//         } else {
//             return array(
//                 'code' => 400,
//                 'message' => '用户编辑失败'
//             );
//         }
//     }
    
//     /**
//      * 重置用户密码
//      * 
//      * @param unknown $mobile
//      * @param unknown $password
//      * @version 2016-9-5 WZ
//      */
//     public function updateUserPassword($mobile, $password) {
//         $set = array('password' => md5($password));
//         $where = array('mobile' => $mobile);
//         $this->updateData($set, $where, 'q_user');
//     }

//     /**
//      * 删除用户
//      */
//     public function DeleteUserInfo($id)
//     {
//         $back = $this->deleteData($id, "q_user");
        
//         if ($back) {
//             return array(
//                 'code' => 200,
//                 'message' => '已成功删除资料'
//             );
//         } else {
//             return array(
//                 'code' => 400,
//                 'message' => '资料删除失败'
//             );
//         }
//     }

//     /**
//      * 意见反馈显示
//      */
//     public function ShowOpinionInfo($condition)
//     {
//         $where = new Where();
//         $where->equalTo('q_feedback.status', 2);
//         $where->equalTo('q_feedback.delete', 0);
        
//         $data = array(
//             'join' => array(
                
//                 array(
//                     'name' => 'q_user',
//                     'on' => 'q_user.id = q_feedback.user_id',
//                     'columns' => array(
//                         'u_nickname' => 'nickname'
//                     )
//                 )
//             ),
//             'order' => array(
//                 'q_feedback.id' => 'DESC'
//             ),
//             'columns' => array(
//                 'f_content' => 'content',
//                 'f_timestamp'=>'timestamp'
//             ),
//             'need_page' => true
//         );
        
//         $list = $this->getAll($where, $data, $condition['page'], null, "q_feedback");
//         $list['where'] = $where;
//         $list['page'] = $condition['page'];
        
//         return $list;
//     }
    
//     /**
//      *   显示未读意见反馈数
//      * 
//      */
//     public function countOpinionInfo()
//     {
//         $where = new Where();
//         $where->equalTo('q_feedback.status', 1);
//         $where->equalTo('q_feedback.delete', 0);
//         $number = $this-> countData($where, 'q_feedback');
//         return $number;
//     }
    
//     public function updateOpinion()
//     {
//         $where = new Where();
//         $where->equalTo('q_feedback.status', 1);
//         $set['status']=2;
//         $back = $this->updateData($set, $where, "q_feedback");
//         return  $back ;
//     }

}
