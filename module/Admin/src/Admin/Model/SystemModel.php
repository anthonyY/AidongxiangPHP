<?php
namespace Admin\Model;

use Zend\Db\Sql\Where;
use Api\Model\CommonModel;

class SystemModel extends CommonModel
{

    protected $table = 'admin';
    /**************************************************************
     *                                                             *
     *          系统信息                                                                                                                                       *
     *                                                             *
     ***************************************************************/
    public function updateRelation($id, $set) {
        $id = (int) $id;
        $where = array('id' => $id);
        $update = false;
        if ($id && $set) {
            $update = $this->updateData($set, $where, 'notification_system');
        }
        if ($update) {
            return array('code' => STATUS_SUCCESS, 'message' => '操作成功');
        }
        return array('code' => STATUS_NOT_UPDATE, 'message' => '更新失败');
    }
    /**
     * 得到所有系统信息  
     * @param unknown $condition
     * @return unknown
     * @version YSQ
     */
    public function userSystem($condition){
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'notification_system.delete', 0);
        if(isset($condition['start']) && $condition['start'])
        {
            $where->greaterThanOrEqualTo(DB_PREFIX . 'notification_system.send_time', $condition['start'].' 00:00:00');
        }
        if(isset($condition['end']) && $condition['end'])
        {
            $where->lessThanOrEqualTo(DB_PREFIX . 'notification_system.send_time', date('Y-m-d 00:00:00',strtotime($condition['end'].' +1 day')));
        }
        if(isset($condition['type']) && $condition['type'])
        {
            $where->equalTo(DB_PREFIX . 'notification_system.type', $condition['type']);
        }
//         $where->lessThanOrEqualTo(DB_PREFIX . 'notification_system.type', 2);
        $data = array(
            'columns' => array(
                'id',
                'send_time',
                'title',
                'position',
                'type',
                'send_status'
            ),
            'join' => array(
                array(
                    'name' => DB_PREFIX.'admin',
                    'on' => DB_PREFIX.'admin.id = '.DB_PREFIX.'notification_system.send_id',
                    'columns' => array(
                        'a_name' => 'name',
                    ),
                    'type' => 'left'
                ),
            ),
//             'order' => array(
//                 DB_PREFIX.'user_help.sort' => 'asc',
//             ),
            'need_page' => true
        );
    
        if (isset($condition['keyword']) &&$condition['keyword']) {
            $data['search_key'] = array(
                DB_PREFIX . 'notification_system.title' => $condition['keyword'],
            );
        }
        $list = $this->getAll($where, $data, $condition['page'], null, "notification_system");
        $list['where'] = $where;
//         $list['keyword'] = $condition['keyword'];
//         $list['page'] = $condition['page'];
        return $list;
    }
    
    
//     /**
//      * 详情
//      * @param unknown $id
//      * @version YSQ
//      */
//     function getSystemDetails($id){
//         if(!$id){
//             return array(
//                 'code' => 400,
//                 'message' => '参数不完整!',
//             );
//         }
//         $help_info = (array)$this->getOne(array('id'=>$id,'delete'=>0),array('*'),'user_help');
//         $admin_info = (array)$this->getOne(array('id'=>$help_info['admin_id'],'delete'=>0),array('name','login_name'),'admin');
    
//         if(!$help_info){
//             return array(
//                 'code' => 400,
//                 'message' => '获取帮助信息失败!',
//             );
//         }
    
//         if(!$admin_info){
//             return array(
//                 'code' => 400,
//                 'message' => '获取帮助信息的发送人信息失败!',
//             );
//         }
//         $help_info['sender'] = $admin_info['name'];
//         return array(
//             'code' => 200,
//             'info' => $help_info,
//         );
//     }
    
    /**
     * 新增/修改
     */
    function setSystemDetails($info){
       
        $data = array();
        $id = (int) (isset($_POST['id']) ? $_POST['id'] : 0);
        $title = addslashes(trim(isset($_POST['title']) ? $_POST['title'] : ''));
        $position = (int) (isset($_POST['position']) ? $_POST['position'] : 0);//用户职务(通知对象)
        $send_time = trim(isset($_POST['send_time']) ? $_POST['send_time'] : '');
        $image = trim(isset($_POST['image']) ? $_POST['image'] : '');
        $outline = trim(isset($_POST['outline']) ? $_POST['outline'] : '');
        $type = (int)isset($_POST['type']) ? $_POST['type'] : '';
        $content = trim(isset($_POST['content']) ? $_POST['content'] : '');
        $audio_id = trim(isset($_POST['audio_id']) ? $_POST['audio_id'] : '');
        $link = trim(isset($_POST['link']) ? $_POST['link'] : '');
       
        if(!$id){//新增
//             var_dump($info,$id);exit;
//             var_dump($title && $position && $send_time && $image && $outline);exit;
            if(!($title  && $send_time && $outline &&  in_array($type, array(1,2,3,4,5,6)))){
                return array('code' => STATUS_UNKNOWN, 'message' => '请求数据不完整1！');
            }
            if($type == 1){
                if(!$content){
                    return array('code' => STATUS_UNKNOWN, 'message' => '请求数据不完整！');
                }
            }elseif(2<=$type && $type  <=  5){
                if(!$audio_id){
                    return array('code' => STATUS_UNKNOWN, 'message' => '请求数据不完整！');
                }
            }elseif($type == 6){
                if(!$link){
                    return array('code' => STATUS_UNKNOWN, 'message' => '请求数据不完整！');
                }
            }
            $data = array(
                'title'=>$title,
                'position'=>$position,
                'send_time'=>$send_time,
                'image'=>$image,
                'outline'=>$outline,
                'type'=>$type,
                'content'=>$content,
                'audio_id'=>$audio_id,
                'link'=>$link,
                'send_id' => $_SESSION['role_nb_admin_id'],
                'send_status' => 1,
                'timestamp' => $this->getTime(),
            );
           
            if(strtotime($send_time) <= time()){
                $data['send_status'] = 2;
                
                if(!$data['position']){
                    $sql =  "update nb_user set notification_num=notification_num+1 where `delete` = 0;";
                }else{
                    $sql =  "update nb_user set notification_num=notification_num+1 where `delete` = 0 and position = ".$data['position'];
                }
                $result = $this->executeSql($sql,'update');
            }
           
            $id = $this->insertData($data,'notification_system');
            if($id){
                return array('code' => STATUS_SUCCESS, 'message' => '新增成功！');
            }
            return array('code' => STATUS_UNKNOWN, 'message' => '新增失败！');
        }else{
            $set = array();
            if($title && $title != $info['title']){
                $set['title'] = $title;
            }
            if($position && $position != $info['position']){
                $set['position'] = $position;
            }
            if($send_time && $send_time != $info['send_time']){
                $set['send_time'] = $send_time;
            }
            if(strtotime($send_time) <= time()){
                $set['send_status'] = 2;
            }
            if($image && $image != $info['image']){
                $set['image'] = $image;
            }
            if($outline && $outline != $info['outline']){
                $set['outline'] = $outline;
            }
            if($type == 1){
                if($content && $content!=$info['content']){
                    $set['content'] = $content;
                }
                $set['link'] = '';
                $set['audio_id'] = '';
            }elseif(2<=$type && $type <= 5){
                if($audio_id && $audio_id!=$info['audio_id']){
                    $set['audio_id'] = $audio_id;
                }
                $set['link'] = '';
                $set['content'] = '';
            }elseif($type == 6){
                if($link && $link!=$info['link']){
                    $set['link'] = $link;
                }
                $set['audio_id'] = '';
                $set['content'] = '';
            }
            if(!$set){
                return array('code' => STATUS_SUCCESS, 'message' => '修改成功！');
            }
            $row = $this->updateData($set, array('id'=>$id),'notification_system');
            if($row){
                return array('code' => STATUS_SUCCESS, 'message' => '修改成功！');
            }
            return array('code' => STATUS_UNKNOWN, 'message' => '修改失败1！');
        }
    }
    
    /**
     * 删除
     * @param unknown $id
     * @param string $key
     * @version YSQ
     */
    public function ajaxDeleteSystem($id,$key=''){
        if(!($id && $key='hdfksje93hjhf89j')){
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
    
        $row = $this->updateData(array('delete'=>1),array('id'=>$id), 'user_help');
        if(!$row){
            return array(
                'code' => 400,
                'message' => '删除失败!',
            );
        }
        return array(
            'code' => 200,
            'message' => '成功!',
        );
    }
    
    /**************************************************************
     *                                                             *
     *             充值设置          top-up                                                                     *
     *                                                             *
     ***************************************************************/
    function getTopUpInfo(){
        $info = $this->getOne(array('delete'=>0),null,'top_up');
        if($info){
            $info['price'] = json_decode($info['top_up_price'],true);
        }
        return $info?$info:array();
    }
    
    function editTopUp(){
        $bottom = isset($_POST['bottom']) && $_POST['bottom']? trim($_POST['bottom']):'';
        $top = isset($_POST['top']) && $_POST['top']? trim($_POST['top']):'';
        $price = isset($_POST['price']) && $_POST['price']? json_encode((array)$_POST['price']):'';
        $content = isset($_POST['content']) && $_POST['content']? trim($_POST['content']):'';
        $info = $this->getTopUpInfo();
        $set = array();
        if($info){
            if($bottom && $bottom!= $info['bottom_content']){
                $set['bottom_content'] = $bottom;
            }
            if($top && $top!= $info['top_content']){
                $set['top_content'] = $top;
            }
            if($price && $price!= $info['top_up_price']){
                $set['top_up_price'] = $price;
            }
            if($content && $content!= $info['account_content']){
                $set['account_content'] = $content;
            }
            if(!$set){
                return array(
                    'code' => 200,
                    'message' => '没有修改!',
                );
            }
            $row = $this->updateData($set,array('id' => $info['id']),'top_up');
        }else{
            $set = array(
                'top_content' => $top,
                'bottom_content' => $bottom,
                'top_up_price' => $price,
                'timestamp' => $this->getTime(),
            );
            $row = $this->insertData($set,'top_up');
        }
        if($row){
            return array(
                'code' => 200,
                'message' => '成功!',
            );
        }
        return array(
            'code' => 400,
            'message' => '失败!',
        );
    }
    /**************************************************************
     *                                                             *
     *             会员设置                                                                               *
     *                                                             *
     ***************************************************************/
    function getMemberInfo(){
        $info = $this->getOne(array('delete'=>0,'type'=>1),null,'member_set');
        return $info?$info:array();
    }
    
    function editMember(){
        $time = isset($_POST['time']) && $_POST['time']? trim($_POST['time']):'';
        $price = isset($_POST['price']) && $_POST['price']? trim($_POST['price']):'';
        $image = isset($_POST['image']) && $_POST['image']? trim($_POST['image']):'';
        $img_id = isset($_POST['img_id']) && $_POST['img_id']? trim($_POST['img_id']):'';
        $content = isset($_POST['content']) && $_POST['content']? trim($_POST['content']):'';
        $info = $this->getMemberInfo();
        $set = array();
        if($info){
            if($time && $time!= $info['number']){
                $set['number'] = $time;
            }
            if($price && $price!= $info['price']){
                $set['price'] = $price;
            }
            if($image && $image!= $info['img_path']){
                $set['img_path'] = $image;
                $set['img_id'] = $img_id;
            }
            if($content && $content != $info['content']){
                $set['content'] = $content;
            }
            if(!$set){
                return array(
                    'code' => 200,
                    'message' => '没有修改!',
                );
            }
            $row = $this->updateData($set,array('id' => $info['id']),'member_set');
        }else{
            $set = array(
                'number' => $time,
                'price' => $price,
                'img_path' => $image,
                'img_id' => $img_id,
                'type' => 1,
                'content' => $content,
                'timestamp' => $this->getTime(),
            );
            $row = $this->insertData($set,'member_set');
        }
        if($row){
            return array(
                'code' => 200,
                'message' => '成功!',
            );
        }
        return array(
            'code' => 400,
            'message' => '失败!',
        );
    }
    /**************************************************************
     *                                                             *
     *              首页管理                                                                                  *
     *                                                             *
     ***************************************************************/
    /**
     * 首页管理详情
     * @version YSQ
     */
    function getHomeManageInfo(){
        $where = array('delete'=>0);
        $info = $this->getOne($where,array('*'),'home_manage');
        if(!$info){
            return array(
                'code' => 300,
                'message' => '查询首页管理信息失败!',
                 'info' => array(),
            );
        }
        $info['name'] = json_decode($info['name'],true);
        $info['link'] = json_decode($info['link'],true);
        $info['image'] = json_decode($info['image'],true);
        $info['first_url_link'] = json_decode($info['first_url_link'],true);
        return array(
            'code' => 200,
            'message' => '成功!',
            'info' => $info 
        );
    }
    
    /**
     * 编辑首页管理
     * @return multitype:number string |Ambigous <multitype:number string , multitype:number string Ambigous <boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown> >
     * @version YSQ
     */
    function editHomeManage(){
//         $id = isset($_POST['id']) && $_POST['id'] ? trim($_POST['id']) : null;
        $images = isset($_POST['img_id']) && $_POST['img_id'] ? (array)$_POST['img_id'] : null;
        $paths = isset($_POST['paths']) && $_POST['paths'] ? (array)$_POST['paths'] : null;
        $name = isset($_POST['name']) && $_POST['name'] ? (array)$_POST['name'] : null;
        $link = isset($_POST['link']) && $_POST['link'] ? (array)$_POST['link'] : null;
        $first_name = isset($_POST['first_name']) && $_POST['first_name'] ? trim($_POST['first_name']) : null;
        $second_name = isset($_POST['second_name']) && $_POST['second_name'] ? trim($_POST['second_name']) : null;
        $first_url_link = isset($_POST['first_url_link']) && $_POST['first_url_link'] ? trim($_POST['first_url_link']) : null;
        $to_stay_on = isset($_POST['to_stay_on']) && $_POST['to_stay_on'] ? trim($_POST['to_stay_on']) : null;
        $second_url_link = isset($_POST['second_url_link']) && $_POST['second_url_link'] ? trim($_POST['second_url_link']) : null;
        if (!($images && $paths && $name && $link && $first_name && $second_name && $first_url_link && $second_url_link)){
            return array(
                'code' => 1,
                'message' => '请求数据不完整！',
            );
        }
        $info = $this->getHomeManageInfo();
        $data = array();
        foreach ($images as $k=>$v){
            $data[$k] = array('id'=>$v,'path'=>$paths[$k]);
        }
        if($info['info']){//编辑
            $set = array();
            if($name != $info['info']['name']){
                $set['name'] = json_encode($name);
            }
            if($data != $info['info']['image']){
                $set['image'] = json_encode($data);
            }
            if($link != $info['info']['link']){
                $set['link'] = json_encode($link);
            }
            if($first_name != $info['info']['first_name']){
                $set['first_name'] = $first_name;
            }
            if($second_name != $info['info']['second_name']){
                $set['second_name'] = $second_name;
            }
            $set['first_url_link'] = json_encode(array(
                'first_url_link' => $first_url_link,
                'to_stay_on' => $to_stay_on,
                'stay_time' => $_POST['stay_time'],
            ));
            if($second_url_link != $info['info']['second_url_link']){
                $set['second_url_link'] = $second_url_link;
            }
            if(!$set){
                return array(
                    'code' => 200,
                    'message' => '成功！',
                );
            }
            $row = $this->updateData($set, array('id' =>$info['info']['id']),'home_manage');
        }else{//新增
            $set = array(
                'name' => json_encode($name),
                'image' => json_encode($data),
                'link' => json_encode($link),
                'first_name' => $first_name,
                'second_name' => $second_name,
                'first_url_link' => json_encode(array($first_url_link,$to_stay_on)),
                'second_url_link' => $second_url_link,
                'timestamp' => $this->getTime(),
            );
            $row = $this->insertData($set,'home_manage');
        }
        if(!$row){
            return array(
                'code' => 3,
                'message' => '您并未修改任何信息，请确认!',
            );
        }
        return array(
            'code' => 200,
            'message' => '成功！',
        );
    }
    /**************************************************************
     *                                                             *
     *          管理员数据处理                                                                                                                             *
     *                                                             *
     ***************************************************************/
    /**
     * 管理员列表
     * */
    public function roleList($condition)
    {
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'admin.delete', 0);
        
        if (isset($condition['cid']) &&$condition['cid']) {//角色
            $where->equalTo(DB_PREFIX . 'admin.role_id', $condition['cid']);
        }
        if (isset($condition['types']) &&$condition['types']) {//状态：1正常；2禁用；
            $where->equalTo(DB_PREFIX . 'admin.status', $condition['types']);
        }
        $data = array(
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'image',
                    'on' => DB_PREFIX . 'image.id = ' . DB_PREFIX . 'admin.image',
                    'columns' => array(
                        'img_path' => "path",
                        'filename' => "filename",
                    ),
                    'type'=>'left'
                ),
                array(
                    'name' => DB_PREFIX . 'admin_role',
                    'on' => DB_PREFIX . 'admin.role_id = ' . DB_PREFIX . 'admin_role.id',
                    'columns' => array(
                        'admin_type' => "name",
                        'admin_manage' => "manage",
                    ),
                    'type'=>'left'
                )
            ),
            'columns'=>array(
                '*'
            ),
            'order' => array(
                DB_PREFIX.'admin.id' => 'asc',
            ),
            'need_page'=>true,
        
        );
        if (isset($condition['keyword']) &&$condition['keyword']) {
            $data['search_key'] = array(
                DB_PREFIX . 'admin.mobile' => $condition['keyword'],
                DB_PREFIX . 'admin.name' => $condition['keyword'],
            );
        }
        $list = $this->getAll($where, $data, $condition['page'], null, "admin");
        $list['where'] = $where;
        $list['keyword'] = $condition['keyword'];
        $list['types'] = $condition['types'];
        $list['cid'] = $condition['cid'];
        $list['page'] = $condition['page'];
        return $list;
    }
    
    
    
    /**
     * 删除管理员
     * @param unknown $id
     * @param string $key
     * @version YSQ
     */
    public function ajaxDeleteAdmin($id,$key=''){
        if(!($id && $key='hdfksje93hjhf89j')){
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
        if($_SESSION['role_nb_admin_id'] == $id){
            return array(
                'code' => '401',
                'message' => '不能删除自己!'
            );
        }
        $row = $this->updateData(array('delete'=>1),array('id'=>$id), 'admin');
        if(!$row){
            return array(
                'code' => 400,
                'message' => '删除失败!',
            );
        }
        return array(
            'code' => 200,
            'message' => '成功!',
        );
    }
    
    /**
     * 设置冻结/启用(ajax)
     * @param array $params
     * @return array
     * @version YSQ
     */
    public  function setUserDelete($params){
        $id = isset($params['id']) ? (int) $params['id'] : 0; // 用户ID
        $status = isset($params['types']) ? (int) $params['types'] : 0; // 1启用；2禁用；
        if (!$id || !in_array($status,array(1,2))) {
            return array(
                'code' => '300',
                'message' => '请求参数不正确!'
            );
        }
        $admin_info = $this->getOne(array(
            'id' => $id
        ), array(
            'status'
        ), 'admin');
        
        if($_SESSION['role_nb_admin_id'] == $id){
            return array(
                'code' => '401',
                'message' => '不能禁用自己!'
            );
        }
        if (in_array($admin_info['status'],array(1,2))) {
            if ($admin_info['status'] == $status) {
                return array(
                    'code' => '400',
                    'message' => '错误操作!'
                );
            }
            $row = $this->updateData(array(
                'status' => $status
            ), array(
                'id' => $id
            ), 'admin');
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
     * 管理员详情
     * @param unknown $id
     * @version YSQ
     */
    function getAdminDetails($id){
        if(!$id){
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
        $admin_info = $this->getOne(array('id'=>$id,'delete'=>0),array('*'),'admin');
        if(!$admin_info){
            return array(
                'code' => 400,
                'message' => '获取管理员信息失败!',
            );
        }
    
        return array(
            'code' => 200,
            'info' => $admin_info,
        );
    }
    
    /**
     * 管理员的新增/修改
     */
    function setAdminDetails(){
        $data = array();
        $id = (int) (isset($_POST['id']) ? $_POST['id'] : 0);
        $mobile = addslashes(trim(isset($_POST['mobile']) ? $_POST['mobile'] : ''));
        $login_name = addslashes(trim(isset($_POST['login_name']) ? $_POST['login_name'] : ''));
        $image = addslashes(trim(isset($_POST['img_path']) ? $_POST['img_path'] : ''));
        $name = addslashes(trim(isset($_POST['name']) ? $_POST['name'] : ''));
        $role = (int) (isset($_POST['role']) ? $_POST['role'] : 0);

        if (! $name) {
            return array(
                'code' => 400,
                'message' => '用户名不能为空!',
            );
        }
    
        // 登录帐号不能重复
        $where = new Where();
        if ($id) {
            $where->notEqualTo('id', $id);
        }
        $where->equalTo('name', $name);
        $where->equalTo('delete', 0);
        $check_name = $this->getOne($where,array('id'),'admin');
        if ($check_name) {
            return array(
                'code' => 400,
                'message' => '登录帐号已存在!',
            );
        }
        // 手机号码不能重复
        $where = new Where();
        if ($id) {
            $where->notEqualTo('id', $id);
        }
        $where->equalTo('mobile', $mobile);
        $check_name = $this->getOne($where,array('id'),'admin');
        if ($check_name) {
            return array(
                'code' => 400,
                'message' => '手机号码已存在!',
            );
        }
    
        if ($_POST['password'] != '')
        {
            $password = md5(md5(trim($_POST['password'])));
            $data['password'] = $password;
        }
        if($id){
            $user_exist = $this->getOne(array('id' => $id),array('*'),'admin');
            $role_exist = $this->getOne(array('id' => $user_exist['role_id']),array('*'),'admin_role');
            if(!$user_exist){
                return array(
                    'code' => 400,
                    'message' => '管理员id没有得到对应信息!',
                );
            }          
            if($role_exist['manage'] == 'all' && $_SESSION['role_nb_admin_manage'] != 'all'){
                return array(
                    'code' => 400,
                    'message' => '普通管理员不能修改超级管理员!',
                );
            }
           
            if($name != $user_exist['name']){
                $data['name'] = $name;
            }
            if($mobile != $user_exist['mobile']){
                $data['mobile'] = $mobile;
            }
            if($login_name != $user_exist['login_name']){
                $data['login_name'] = $login_name;
            }
            
            if($role != $user_exist['role_id']){
                if($role_exist['manage'] != 'all'){
                    $data['role_id'] = $role;
                }
            }
            if($image != $user_exist['image']){
                $data['image'] = $image;
            }
            if(!$data){
                return array(
                    'code' => 400,
                    'message' => '参数没有修改!',
                );
            }
        }else{
            if(!(isset($_POST['password']) && $_POST['password'])){
                return array(
                    'code' => 400,
                    'message' => '密码不能为空!',
                );
            }
            $data['name'] = $name;
            $data['role_id'] = $role;
            $data['mobile'] = $mobile;
            $data['login_name'] = $login_name;
            $data['image'] = $image;
            $user_exist =array();
        }
        if ($data) {
            if ($user_exist) {
                $row = $this->updateData($data, array('id'=>$id),'admin');
                if($row){
                    return array(
                        'code' => 200,
                        'message' => '修改成功!',
                    );
                }
            }else{
                // 管理员才可以添加用户
                $data['status'] = 1;
                $data['last_login_time'] = $this->getTime();
                $data['timestamp'] = $this->getTime();
                $admin_id = $this->insertData($data,'admin');
                if($admin_id){
                    return array(
                        'code' => 200,
                        'message' => '新增成功!',
                    );
                }
            }
        }
        return array(
            'code' => 400,
            'message' => '操作失败!',
        );
    }
    
    
    

    /**************************************************************
     *                                                             *
     *          职务数据处理                                                                                                                                 *
     *                                                             *
     ***************************************************************/
    
    /**
     *  职务列表
     * @param unknown $condition
     * @return Ambigous <multitype:, multitype:unknown Ambigous <string, \Zend\Paginator\Paginator, multitype:unknown > , multitype:multitype:mixed  number >
     * @version YSQ
     */
    public function role($condition)
    {
        $where = new Where();
        $where->equalTo('delete', 0);
        $data = array(
            'columns' => array(
                'id',
                'name',//名称：例如超级管理员、专家、客服、管理员、财务
                'manage',//权限：例如，user,car,article或者用ALL表示超管权限
            ),
            'order' => array(
                'id' => 'asc'
            ),
             
            'need_page' => true
        );
        $list = $this->getAll($where, $data, $condition['page'], null, "admin_role");
        $list['where'] = $where;
        $list['page'] = $condition['page'];
        return $list;
    }
    
    /**
     * 角色详情
     * @param unknown $id
     * @return multitype:number string |multitype:number Ambigous <boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown>
     * @version YSQ
     */
    public function roleInfo($id, $roles){
        if(!$id){
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
        $role_info = $this->getOne(array('id'=>$id,'delete'=>0),array('*'),'admin_role');
        if(!$role_info){
            return array(
                'code' => 400,
                'message' => '获取职务信息失败!',
            );
        }
        if($roles){
            $manage = $role_info['manage'] == 'all' ? 'all' : json_decode($role_info['manage'],true);
            if (! $manage) {
                return array();
            }
            if($manage == 'all'){
                return array(
                    'code' => 200,
                    'info' => $roles['role'],
                );
            }
          //一维数组变二维数组
            $list = array();
            foreach($roles['role'] as $key => $value) {
                if (count(array_diff($value, $manage)) < count($value)) {
                    foreach($value as $k => $v) {
                        if (in_array($v, $manage)) {
                            $list[$key][$k] = $v;
                        }
                    }
                }
            }
        }
        return array(
            'code' => 200,
            'info' => $roles ? $list : $role_info,
        );
    }
    
    /**
     * 返回角色权限模块
     *
     * @author arong
     */
    public function allArr(){
          return array(
            '主页' => 'Index',
            '音频课程' => 'video',
            '视频课程' => 'Familys',
            '课程包' => 'Courses',
            '评论管理' => 'Comment',
            '老师管理' => 'Teacher',
            '用户管理' => 'User',
            '意见反馈' => 'Feedback',
            '广告管理' => 'Ads',
            '系统通知' => 'NotificationSystem',
            '用户帮助' => 'UserHelp',
            '功能介绍' => 'Function',
            '财务管理' => 'Financial',
            '首页管理' => 'Home',
            '板块管理' => 'Plate',
            '注册协议' => 'Register',
            '职务管理' => 'Duty',
            '管理员' => 'System',
            '系统管理' => 'Manage'
        );
    }
    
    
    /**
     * 添加/修改角色
     * @version YSQ
     */
    public function addRole(){
        if (isset($_POST) && $_POST)
        {
           
            if(isset($_POST['manage']) && $_POST['manage']){
                if($_POST['manage'] == $this->allArr()){
                    $manage = 'all';
                }else{
                    $manage = json_encode($_POST['manage']);
                }
            }else{
                $manage = null;
            }
    
            $name =  trim($_POST['name']);
            $data =array(
                'name' => $name,
                'manage' => $manage,
            );
            if ($_POST['id'])
            {
                $row = $this->updateData($data, array('id' => $_POST['id']),'admin_role');
                if(!$row){
                    return array(
                        'code' => 400,
                        'message' => '修改角色失败!',
                    );
                }
            }
            else
            {
                $role_name = $this->getOne(array('name'=>$name),array('id'),'admin_role');
                if($role_name){
                    $data['delete'] = 0;
                    $a_id = $this->updateData($data, array('id'=>$role_name['id']),'admin_role');
                }else{
                    if($manage == 'all'){
                        $root = $this->getOne(array('manage'=>'all'),array('id'),'admin_role');
                        if($root){
                            return array(
                                'code' => 400,
                                'message' => '超级管理员已经存在!',
                            );
                        }
                    }
                    $data['timestamp'] = $this->getTime();
                    $a_id = $this->insertData($data,'admin_role');
                }
                if(!$a_id){
                    return array(
                        'code' => 400,
                        'message' => '添加角色失败!',
                    );
                }
            }
        }else{
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
        return array(
            'code' => 200,
            'message' => '成功!',
        );
    }

    /**
     * 删除角色
     * @param unknown $id
     * @param string $key
     * @version YSQ
     */
    public function roleDelete($id,$key=''){
        if(!($id && $key='dhfjhkadslj32432')){
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
        $admin_info = $this->getOne(array('role_id'=>$id,'delete'=>0),array('id'),'admin');
        if($admin_info){
            return array(
                'code' => 400,
                'message' => '角色已被分配给管理员。请先调整管理员后再删除角色!',
            );
        }else{
            $row = $this->updateData(array('delete'=>1),array('id'=>$id), 'admin_role');
            if(!$row){
                return array(
                    'code' => 400,
                    'message' => '删除失败!',
                );
            }
            return array(
                'code' => 200,
                'message' => '删除成功!',
            );
        }
    }
    
    /**
     * 得到所有角色的信息
     * @version YSQ
     */
    function adminRoleList(){
        $return = array();
        $return = $this->fetchAll(array('delete'=>0),array('columns'=>array('id','name') ,'order'=>array('id asc')),'admin_role');
        return $return;
    }

    /**************************************************************
     *                                                             *
     *          注册协议据处理                                                                                                                              *
     *                                                             *
     ***************************************************************/
    
    /**
     * 查询注册协议
     * */
    public function getAboutmyShare($type=1){
        $list = $this->getOne(array('type' => $type,'delete' => 0),array('*'),'setup');
        if($list){
            return array(
                'code' => 200,
                'list' => $list,
            );
        }
        return array(
            'code' => 400,
            'message' => '没有数据/数据删除',
        );
    }

    /**
     * 修改协议说明
     * @return multitype:number string
     * @version YSQ
     */
    public function setProtocolInfo($type=1){
        if($_POST){
            $postData = $_POST;
            $article_info = $this->getOne(array('id'=>$postData['id'],'type' => $type),array('*'),'setup');
            if(!$article_info){
               $row = $this->insertData(array('content'=>$postData['content'],'type' => $type,'timestamp'=>$this->getTime()),'setup');
                 if(!$row){
                return array(
                    'code' => 400,
                    'message' => ' 保存失败!',
                    );
                }
                return array(
                    'code' => 200,
                    'message' => '保存成功!',
                );
            }
            if(($article_info['content'] == $postData['content']) && $postData['content']){
                return array(
                    'code' => 400,
                    'message' => '没有修改任何数据!',
                );
            }
            $row =$this->updateData(array('content'=>$postData['content'],'type' => $type), array('id'=>$postData['id']),'setup');
            if(!$row){
                return array(
                    'code' => 400,
                    'message' => '修改失败!',
                );
            }
            return array(
                'code' => 200,
                'message' => '修改成功!',
            );
        }
        return array(
            'code' => 400,
            'message' => '请求参数不完整!',
        );
    }
    
    /**************************************************************
     *                                                             *
     *          用户职位管理                                                                                                                                *
     *                                                             *
     ***************************************************************/
  
    /**
     * 查找职务列表
     * */
    public function userJob($condition)
    {
        $where = new Where();
        $where->equalTo('delete', 0);
        $data = array(
            'columns' => array(
                'id',
                'name',//名称：例如超级管理员、专家、客服、管理员、财务
            ),
            'order' => array(
                'id' => 'asc'
            ),
            'need_page' => true
        );
        $list = $this->getAll($where, $data, $condition['page'], null, "position");
        $list['where'] = $where;
        $list['page'] = $condition['page'];
        return $list;
    }
    /**
     * 新增或者修改职务
     * */
    public function addUserJob($id,$content){
        if(!$content){
            return array(
                'code' => 400,
                'message' => '操作失败',
            );
        }
        if($id){
            $row = $this->updateData(array('name'=>$content),array('id' => $id),'position');
        }else{
            $row = $this->insertData(array('name'=>$content,"timestamp" => $this->getTime()),'position');
        }
        
        if($row){
            return array(
                'code' => 200,
                'message' => "操作成功",
            );
        }
        return array(
            'code' => 400,
            'message' => '操作失败',
        );
    }
    
    /**
     * 删除职务
     * */
    function userJobDelete($id,$key){
        if(!($id && $key='dhfjhkadslj32432')){
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
        $admin_info = $this->getOne(array('id'=>$id,'delete'=>0),array('id'),'position');
        if(!$admin_info){
            return array(
                'code' => 400,
                'message' => '删除失败！',
            );
        }else{
            $user = $this->getOne(array('position' => $id),array('*'),'user');
            if($user){
                return array(
                    'code' => 400,
                    'message' => '该职位已有用户选择，无法删除!',
                );
            }
            $row = $this->updateData(array('delete'=>1),array('id'=>$id), 'position');
            if(!$row){
                return array(
                    'code' => 400,
                    'message' => '删除失败!',
                );
            }
            return array(
                'code' => 200,
                'message' => '删除成功!',
            );
        }
    }
    
    /**************************************************************
     *                                                             *
     *          用户帮助                                                                                                                                         *
     *                                                             *
     ***************************************************************/
    /**
     * 得到所有用户帮助的信息
     * @param unknown $condition
     * @return unknown
     * @version YSQ
     */
    public function userHelpList($condition,$type){    
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'user_help.delete', 0);
        $where->equalTo(DB_PREFIX . 'user_help.type',$type);
        if($condition['start'] && $condition['end'])
        {
            $where->greaterThanOrEqualTo(DB_PREFIX . 'user_help.timestamp_update', date("Y-m-d 00:00:00",strtotime($condition['start'])));
            $where->lessThanOrEqualTo(DB_PREFIX . 'user_help.timestamp_update', date("Y-m-d 23:59:59",strtotime($condition['end'])));
        }
        $data = array(
            'columns' => array(
                'id',
                'sort',
                'title',
                'timestamp_update',
            ),
            'join' => array(
                array(
                    'name' => DB_PREFIX.'admin',
                    'on' => DB_PREFIX.'admin.id = '.DB_PREFIX.'user_help.admin_id',
                    'columns' => array(
                        'a_id' => 'id',
                        'a_name' => 'login_name',
                        'admin_name' => 'name'
                    ),
                    'type' => 'left'
                ),
            ),
            'order' => array(
                DB_PREFIX.'user_help.sort' => 'asc',
            ),
            'need_page' => true
        );
    
        if (isset($condition['keyword']) &&$condition['keyword']) {
            $like['title'] = $condition['keyword'];
            $data['search_key'] = array(
                DB_PREFIX . 'user_help.title' => $condition['keyword'],
            );
        }
        $list = $this->getAll($where, $data, $condition['page'], null, "user_help");
        $list['where'] = $where;
        $list['keyword'] = $condition['keyword'];
        $list['page'] = $condition['page'];
        return $list;
    }
    

    /**
     * 帮助详情
     * @param unknown $id
     * @version YSQ
     */
    function getHelpDetails($id){
        if(!$id){
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
        $help_info = (array)$this->getOne(array('id'=>$id,'delete'=>0),array('*'),'user_help');
        $admin_info = (array)$this->getOne(array('id'=>$help_info['admin_id'],'delete'=>0),array('name','login_name'),'admin');
    
        if(!$help_info){
            return array(
                'code' => 400,
                'message' => '获取帮助信息失败!',
            );
        }
    
        if(!$admin_info){
            return array(
                'code' => 400,
                'message' => '获取帮助信息的发送人信息失败!',
            );
        }
        $help_info['sender'] = $admin_info['name'];
        return array(
            'code' => 200,
            'info' => $help_info,
        );
    }
    
    /**
     * 帮助的新增/修改
     */
    function setHelpDetails($type){
        $data = array();
        $id = (int) (isset($_POST['id']) ? $_POST['id'] : 0);
        $title = addslashes(trim(isset($_POST['title']) ? $_POST['title'] : ''));
        $sort = (int) (isset($_POST['sort']) ? $_POST['sort'] : 0);
        $content = trim(isset($_POST['content']) ? $_POST['content'] : '');
        if (! $title) {
            return array(
                'code' => 400,
                'message' => '帮助标题不能为空!',
            );
        }
    
    
        if (! $sort) {
            return array(
                'code' => 400,
                'message' => '排序序号不能为空!',
            );
        }
    
        if (! $content) {
            return array(
                'code' => 400,
                'message' => '帮助内容不能为空!',
            );
        }
    
        if($id){
            $where = new Where();
            $where->equalTo('title', $title);
            $where->equalTo('delete', 0);
            $where->equalTo('type',$type);
            $where->notEqualTo('id', $id);
            $same = $this->getOne($where,array('id','title'),'user_help');
            if ($same) {
                if($type == 1){
                    return array(
                        'code' => 400,
                        'message' => "已有". $same['title'] ."该用户帮助标题!!",
                    );
                }else{
                    return array(
                        'code' => 400,
                        'message' => "已有". $same['title'] ."该功能介绍标题!!",
                    );
                }
            }
    
            $help_exist = $this->getOne(array('id' => $id,'type' => $type),array('*'),'user_help');
            if(!$help_exist){
                return array(
                    'code' => 400,
                    'message' => '没有这条帮助信息!',
                );
            }
            if($sort != $help_exist['sort']){
                $data['sort'] = $sort;
            }
            if($title != $help_exist['title']){
                $data['title'] = $title;
            }
            if($content != $help_exist['content']){
                $data['content'] = $content;
            }
            
            if(!$data){
                return array(
                    'code' => 400,
                    'message' => '参数没有修改!',
                );
            }
        }else{
            $same = $this->getOne(array('title' => $title,'delete'=>0,'type'=>$type),array('id','title'),'user_help');
            if ($same) {
              if($type == 1){
                  return array(
                      'code' => 400,
                      'message' => "已有". $same['title'] ."该用户帮助标题!!",
                  );
              }else{
                  return array(
                      'code' => 400,
                      'message' => "已有". $same['title'] ."该功能介绍标题!!",
                  );
              }
            }
            $data['title'] = $title;
            $data['sort'] = $sort;
            $data['content'] = $content;
            $help_exist =array();
        }
        
        if ($data) {
            $data['type'] = $type;
            $data['timestamp_update'] = $this->getTime();
            if ($help_exist) {
                $data['admin_id'] = $_SESSION['role_nb_admin_id'];
                $row = $this->updateData($data, array('id'=>$id),'user_help');
                if($row){
                    return array(
                        'code' => 200,
                        'message' => '修改成功!',
                    );
                }
            }else{
                $data['admin_id'] = $_SESSION['role_nb_admin_id'];
                $data['timestamp'] = $this->getTime();
                $help_id = $this->insertData($data,'user_help');
                if($help_id){
                    return array(
                        'code' => 200,
                        'message' => '新增成功!',
                    );
                }
            }
        }
        return array(
            'code' => 400,
            'message' => '操作失败!',
        );
    }
    
    /**
     * 删除帮助信息
     * @param unknown $id
     * @param array $set
     * @version YSQ
     */
    public function updateHelp($id, $set){
        $id = (int) $id;
        $where = array('id' => $id);
        $update = false;
        if ($id && $set) {
            $update = $this->updateData($set, $where, 'user_help');
        }
        if ($update) {
            return array('code' => STATUS_SUCCESS, 'message' => '操作成功');
        }
        return array('code' => STATUS_NOT_UPDATE, 'message' => '更新失败');
    }
    /**************************************************************
     *                                                             *
     *          版块管理                                                                                                                                         *
     *                                                             *
     ***************************************************************/
    
    /**
     * 管理员列表
     * */
    public function categoryList($condition)
    {
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'category.delete', 0);
        $where->equalTo(DB_PREFIX . 'category.deep',1);
        if (isset($condition['cid']) && $condition['cid']) {//角色
            $where->equalTo(DB_PREFIX . 'category.type', $condition['cid']);
        }
        $data = array(
            'columns' => array(
                '*'
            ),
            'order' => array(
                'sort' => 'asc'
            ),
            'need_page' => true
        );
        $list = $this->getAll($where, $data, $condition['page'], null, "category");
        $list['where'] = $where;
        $list['cid'] = $condition['cid'];
        $list['page'] = $condition['page'];
        return $list;
    }
    
    /**
     * 设置冻结/启用(ajax)
     * @param array $params
     * @return array
     * @version YSQ
     */
    public  function setcategoryDelete($params){
        $id = isset($params['id']) ? (int) $params['id'] : 0; 
        $status = isset($params['types']) ? (int) $params['types'] : 0;
        if (!$id || !in_array($status,array(1,2))) {
            return array(
                'code' => '300',
                'message' => '请求参数不正确!'
            );
        }
        $admin_info = $this->getOne(array(
            'id' => $id
        ), array(
            'status'
        ), 'category');
        if (in_array($admin_info['status'],array(1,2))) {
            if ($admin_info['status'] == $status) {
                return array(
                    'code' => '400',
                    'message' => '错误操作!'
                );
            }
            $row = $this->updateData(array(
                'status' => $status
            ), array(
                'id' => $id
            ), 'category');
            if ($row) {
                return array(
                    'code' => '200',
                    'message' => '操作成功!',
                    'id' => $id,
                );
            }
        }
        return array(
            'code' => '400',
            'message' => '未知错误!'
        );
    }
    

    /**
     * 删除版块
     * @param unknown $id
     * @param string $key
     * @version YSQ
     */
    public function ajaxDeleteCategory($id,$key='',$cid,$pid){
        $category = $this->getOne(array('parent_id' => $id,'delete' => 0),array('*'),'category');
        if($category){
            return array(
                'code' => 400,
                'message' => '该类型有子类,删除失败!',
            );
        }
        if($id && $pid){
            $category = $this->getOne(array('id' => $id,'delete' => 0),array('type'),'category');
            if($category){
                $video = $this->fetchAll(array('type' => $category['type'],'audio_one_type' => $pid,'audio_two_type' => $id,'delete' => 0),array('id'),'audio');
                $courses = $this->fetchAll(array('type' => ($category['type']+2),'courses_one_type' => $pid,'courses_two_type' => $id,'delete' => 0),array('id'),'courses');
            }
            if($video || $courses){
                return array(
                    'code' => 400,
                    'message' => '该子类型有课程或者课程包,删除失败!',
                );
            }
            
        }
        if(!($id && $key='hdfksje93hjhf89j')){
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
        $row = $this->updateData(array('delete'=>1),array('id'=>$id), 'category');
        if(!$row){
            return array(
                'code' => 400,
                'message' => '删除失败!',
            );
        }
        $this->updateKey($pid, 2, 'number', 1,'category');
        return array(
            'code' => 200,
            'message' => '成功!',
        );
    }
    

    /**
     * 新增或者编辑版块
     * @param unknown $id
     * @param string $key
     * @version YSQ
     */
    public function addCategory(){
        $name = isset($_POST['name']) ? $_POST['name'] : "";
        $type = isset($_POST['type']) ? $_POST['type'] : "";
        $sort = isset($_POST['sort']) ? $_POST['sort'] : "";
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        if(!$name && !$type && !$sort){
            return array(
                'code' => 400,
                'message' => '缺少参数!',
            );
        }else{
            $data = array(
                'name' => $name,
                'type' => $type,
                'sort' => $sort,
                'deep' => 1,
                'status' => 1,
                'parent_id' => 0,
                'delete' => 0,
                'timestamp' => $this->getTime()
            );
        }
        if($id){
            $row = $this->updateData($data, array('id' => $id),'category');
        }else{
            $row = $this->insertData($data,'category');
        }
        if(!$row){
            return array(
                'code' => 400,
                'message' => '保存失败!',
            );
        }
        return array(
            'code' => 200,
            'message' => '成功!',
        );
    }
    
    public function categoryDetails($condition,$id)
    {
        $cate_data = $this->getOne(array('id' => $id,'delete' => 0),array('*'),'category');
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'category.parent_id',$id);
        $where->equalTo(DB_PREFIX . 'category.delete',0);
        $data = array(
            'columns' => array(
                '*'
            ),
            'order' => array(
                'sort' => 'asc'
            ),
            'need_page' => true
        );
        $list = $this->getAll($where, $data, $condition['page'], null, "category");
        $list['where'] = $where;
        $list['page'] = $condition['page'];
        $list['cate_data'] = $cate_data;
        return $list;
    }
    
    public function addTwoCategory(){
        $name = isset($_POST['name']) ? $_POST['name'] : "";
        $status = isset($_POST['status']) ? $_POST['status'] : "";
        $sort = isset($_POST['sort']) ? $_POST['sort'] : "";
        $parent_id = isset($_POST['parid']) ? $_POST['parid'] : 0;
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        if($id){
            $cate_data = $this->updateData(array(
                'name' => $name,
                'status' => $status,
                'sort' => $sort,
                'timestamp_update' => $this->getTime()
            ), array(
                'id' => $id
            ),'category');
            if(!$cate_data){
                return array(
                    'code' => 400,
                    'message' => '操作失败!',
                );
            }
        }else{
            $cate_data = $this->getOne(array('id' => $parent_id,'delete' => 0),array('id','type'),'category');
            $data = array(
                'name' => $name,
                'type' => $cate_data['type'],
                'sort' => $sort,
                'deep' => 2,
                'status' => $status,
                'parent_id' => $parent_id,
                'delete' => 0,
                'timestamp' => $this->getTime()
            );
            $row = $this->insertData($data,'category');
            if(!$row){
                return array(
                    'code' => 400,
                    'message' => '操作失败!',
                );
            } 
            $this->updateKey($cate_data['id'], 1, 'number', 1,'category');
        }
        return array(
            'code' => 200,
            'message' => '操作成功!',
        );
    }
    /**************************************************************
     *                                                             *
     *          营销工具-->生成二维码                                                                                                                                  *
     *                                                             *
     ***************************************************************/
    /**
     * 列表
     */
    function marketingList($condition){
        $where = array();
        $data = array(
            'columns' => array(
                '*',
            ),
            'need_page' => true,
        );
        $list = $this->getAll($where,$data,$condition['page'],$condition['limit'],'qrcode_log');
        $list['where'] = $where;
        return $list;
    }
    
    /**
     *新增
     */
    function addMarketing(){
        $price = isset($_POST['price']) && $_POST['price'] ? $_POST['price'] : 0;
        $days = isset($_POST['days']) && $_POST['days'] ? $_POST['days'] : 0;
        $time = isset($_POST['time']) && $_POST['time'] ? $_POST['time'] : 0;
        $number = isset($_POST['number']) && $_POST['number'] ? $_POST['number'] : 0;
        $content = isset($_POST['content']) && $_POST['content'] ? $_POST['content'] : 0;
        if(!(int)$price){
            return array(
                'code' => 300,
                'message' => '奖励金额不能为空',
            );
        }
        if(!(int)$days){
            return array(
                'code' => 300,
                'message' => '会员天数不能为空',
            );
        }
        if(strtotime($time) < time()){
            return array(
                'code' => 300,
                'message' => '有效期不能小于当前时间',
            );
        }
        $time = date('Y-m-d H:i:s',strtotime($time));

        if(!(int)$number){
            return array(
                'code' => 300,
                'message' => '生成数量不可为空',
            );
        }
        $data = array(
            'price' => $price,
            'day' => $days,
            'effective_time' => $time,
            'num' => $number,
            'remark' => $content,
            'timestamp' => $this->getTime(),
        );
        $id = $this->insertData($data,'qrcode_log');
        if($id){
            for($i=0;$i<$number;$i++){
                $timestamp = $this->getTime();
                list($usec, $sec) = explode(" ", microtime());
                $code =  $this->makeCode(6,7);
                $code = $code.$sec.substr($usec, -6).$i;
                $set = array(
                    'qrcode_log_id' => $id,
                    'code' => $code,
                    'link' => QRCODE_URL.$code.','.md5(md5($code.$timestamp).QRCODE_TOKEN),
                    'timestamp' => $timestamp,
                );
                $qid = $this->insertData($set,'qrcode');
            }
            return array(
                'code' => 200,
                'message' => '操作成功',
            );
        }
        return array(
            'code' => 400,
            'message' => '操作失败',
        );
    }
    
    /**
     * 查看详情
     */
    function getMarketing($id){
        $info  = $this->getOne(array('id'=>$id),array('*'),'qrcode_log');
        if($info){
            return array(
                'code' => 200,
                'message' => '操作成功',
                'info' => $info,
            );
        }
        return array(
            'code' => 400,
            'message' => '操作失败',
        );
    }
    
    /**
     * 异步打包二维码
     */
    function prckQrcode($id){
        if(!$id) return false;
        include_once APP_PATH . '/vendor/Core/System/phpqrcode/phpqrcode.php';
        include_once APP_PATH . '/vendor/Core/System/phpqrcode/compress.php';
        $level = "L";
        $size = "8";
        $margin = "1";
        $file = LOCAL_SAVEPATH.'qrcode/'.date('YmdH');
        $list = $this->fetchAll(array('qrcode_log_id'=>$id),array('columns'=>array('*')),'qrcode');
        if($list){
            foreach ($list as $k=>$v){
                if($v['status'] == 1){
                    $name = $k.'-未使用';
                }else{
                    $name = $k.'-已使用';
                }
                //生成二维码
                $value=$v['link'];
                $path = $file.'/'.iconv("UTF-8", "GBK", $name).'.jpg';
                if(!is_dir($file)){
                    mkdir($file,'0777',true);
                    chmod($file, 0777);
                }
                \QRcode::png($value,$path, $level, $size,$margin);
            }
            $zip = new \Zipdown($file);
            $zip->index();
        }else{
            return false;
        }
    }

    /*
     * 添加/修改分銷設置
     *
     **/
    public function addPoster($data){
        if(!isset($data['content']) || !$data['content']){
            return array('code' => 400 ,'msg' => '请填写分享打开页面内容');
        }

        if(!isset($data['explain']) || !$data['explain']){
            return array('code' => 400 ,'msg' => '请填写佣金说明');
        }
        if(!isset($data['rule']) || !$data['rule']){
            return array('code' => 400 ,'msg' => '请填写分销规则');
        }
        if(!isset($data['big_img_ids'])){
            return array('code' => 400 ,'msg' => '请填写分销海报大图片');
        }
        if(!isset($data['small_img_ids'])){
            return array('code' => 400 ,'msg' => '请填写分销海报小图片');
        }
        $set = array(
            'is_open' => $data['is_open'],
            'rank' => $data['rank'],
            'button_name' => $data['button_name'],
            'explain' => $data['explain'],
            'rule' => $data['rule'],
            'heading' => $data['heading'],
            'subheading' => $data['subheading'],
            'img_id' => $data['img_id_1'],
            'img_path' => $data['img_path_1'],
            'content' => $data['content'],
            'big_img_ids' => implode(',',$data['big_img_ids']),
            'big_img_paths' => implode(',',$data['bit_paths']),
            'small_img_ids' => implode(',',$data['small_img_ids']),
            'small_img_paths' => implode(',',$data['small_paths']),
            'timestamp' => $this->getTime(),
        );

        if($data['id']){
            $this->updateData($set,array('id'=>$data['id']),'distribut_set');
        }else{
            $this->insertData($set,'distribut_set');
        }
        return array('code' => 200,'msg' => 'ok');
    }

    /*
     * 獲取分銷數據
     *
     * */
    public function getPosterData(){
        $info =  $this->getOne(array(),array('*'),'distribut_set');
        if($info['big_img_ids'] ){
            $info['big_img_ids'] = explode(',',$info['big_img_ids']);
            $info['big_img_paths'] = explode(',',$info['big_img_paths']);
            for($i=0;$i<count($info['big_img_ids']);$i++){
                $info['big_img'][$info['big_img_ids'][$i]] = $info['big_img_paths'][$i];
            }
            unset($info['big_img_ids']);
            unset($info['big_img_paths']);
        }

        if($info['small_img_ids']){
            $info['small_img_ids'] = explode(',',$info['small_img_ids']);
            $info['small_img_paths'] = explode(',',$info['small_img_paths']);
            for($i=0;$i<count($info['small_img_ids']);$i++){
                $info['small_img'][$info['small_img_ids'][$i]] = $info['small_img_paths'][$i];
            }
            unset($info['small_img_ids']);
            unset($info['small_img_paths']);
        }

        return $info;
    }

    /*
     * 新增分销等级
     *
     * */
    public function addGrade($data){
        if($this->checkNum($data['start'])){
            return array('code' => 400,'msg' => '该人数下限已在范围内');
        };
        if($data['end']){
            if($data['end'] <= $data['start']){
                return array('code' => 400,'msg' => '该人数下限与人数上限有冲突');
            }
            if($this->checkNum($data['end'])){
                return array('code' => 400,'msg' => '该人数上限已在范围内');
            }
        }

        $data = array(
            'start' => $data['start'],
            'end' => $data['end'] ? $data['end'] : '999999999',
            'stair_brokerage' => $data['stair_brokerage'],
            'second_brokerage' => $data['second_brokerage']
        );

        $this->insertData($data,'distribut_rank');
        return array('code' => 200,'msg' => 'ok');
    }

    //验证数字是否在上限
    public function checkNum($num){
        $where = new where();
        $where->greaterThanOrEqualTo('end',$num);
        $where->lessThanOrEqualTo('start',$num);
        $where->equalTo('delete','0');
        $data = $this->getOne($where,array('*'),'distribut_rank');

        return $data ? true : false;
    }

    //获取分销等级数据
    public function getAward($condition){
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'distribut_rank.delete', 0);
        $data = array(
            'join' => array(
            ),
            'columns'=>array(
                '*'
            ),
            'order' => array(
                DB_PREFIX.'distribut_rank.id' => 'asc',
            ),
            'need_page'=>true,
        );
        $list = $this->getAll($where, $data, $condition['page'], null, "distribut_rank");
        $list['where'] = $where;
        $list['page'] = $condition['page'];
        return $list;
    }

    //删除等级
    public function deleteAwardAjax($id, $set) {
        $id = (int) $id;
        $where = array('id' => $id);
        $update = false;
        if ($id && $set) {
            $update = $this->updateData($set, $where, 'distribut_rank');
        }
        if ($update) {
            return array('status' => STATUS_SUCCESS, 'msg' => '操作成功');
        }
        return array('status' => STATUS_NOT_UPDATE, 'msg' => '更新失败');
    }

    //获取分销奖励数据
    public function getDistribution($condition,$boolean){
        $where = new Where();
        $where->greaterThan('stair_num', 0);
        $data = array(
            'columns' => array(
                '*'
            ),
            'join' => array(
            ),
            'order' => array(
                'id' => 'DESC'
            ),
            'need_page' => $boolean,
        );
        $list = $this->getAll($where, $data, $condition['page'], null, "view_distribut");
        return $list;
    }

    /**
     * 导出分销数据excel
     *
     * @version YSQ
     */
    public function setExcel($condition){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能导入
        $list = $this->getDistribution($condition,false);
        $time = $this->getTime();
        foreach ($list['list'] as $k => $v){
            $arr[$k]['name'] = $v['name'];
            $arr[$k]['mobile'] = $v['mobile'];
            $arr[$k]['first_name'] = $v['first_name'];
            $arr[$k]['first_mobile'] = $v['first_mobile'];
            $arr[$k]['stair_num'] = $v['stair_num'];
            $arr[$k]['second_num'] = $v['second_num'];
            $arr[$k]['stair_brokerage'] = $v['stair_brokerage'] .'元';
            $arr[$k]['second_brokerage'] = $v['second_brokerage'] .'元';
            $arr[$k]['total'] = $v['stair_brokerage'] + $v['second_brokerage'];
        }
        $allRoomList = $arr;

        $filename='分销返利详情';
        $headArr=array("用户名","手机号码","上线用户名","上线手机号码","一级下线数","二级下线数","一级佣金","二级佣金","佣金合计");
        $return = $this->getExcel($filename,$headArr,$allRoomList);
        if($return){
            return array(
                'code' =>200,
                'message' =>'成功',
            );
        }
    }
}
?>