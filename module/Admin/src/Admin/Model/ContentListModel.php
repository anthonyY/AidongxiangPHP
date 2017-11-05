<?php
namespace Admin\Model;

use Zend\Db\Sql\Where;
use Core\System\WxApi\WxApi;
use Api\Model\CommonModel;
use Zend\Db\Sql\Expression;
use Zend\Mvc\Controller\Plugin;
use Core\System\AiiUtility\Log;

class ContentListModel extends CommonModel
{

    protected $table = 'teacher';
    
    /**************************************************************
     *                                                             *
     *             会员设置                                                                                                                                *
     *                                                             *
     ***************************************************************/
    function getShareInfo(){
        $info = $this->getOne(array('delete'=>0,'type'=>2),null,'member_set');
        return $info?$info:array();
    }
    
    function editShare(){
        $image = isset($_POST['image']) && $_POST['image']? trim($_POST['image']):'';
        $img_id = isset($_POST['img_id']) && $_POST['img_id']? trim($_POST['img_id']):'';
        $info = $this->getShareInfo();
        $set = array();
        if($info){
            if($image && $image!= $info['img_path']){
                $set['img_path'] = $image;
                $set['img_id'] = $img_id;
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
                'img_path' => $image,
                'img_id' => $img_id,
                'type' => 2,
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
     *         觉鸟导师数据处理                                                                                                                             *
     *                                                             *
     ***************************************************************/
    //老师列表
    public function teacherList($condition)
    {
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'teacher.delete', 0);
        if (isset($condition['types']) &&$condition['types']) {//状态：1正常；2禁用；
            $where->equalTo(DB_PREFIX . 'teacher.status', $condition['types']);
        }
        $data = array(
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'image',
                    'on' => DB_PREFIX . 'image.id = ' . DB_PREFIX . 'teacher.head_icon',
                    'columns' => array(
                        'img_path' => "path",
                        'filename' => "filename",
                    ),
                    'type'=>'left'
                ),
            ),
            'columns'=>array(
                '*'
            ),
            'order' => array(
                DB_PREFIX.'teacher.id' => 'asc',
            ),
            'need_page'=>true,
    
        );
        if (isset($condition['keyword']) &&$condition['keyword']) {
            $data['search_key'] = array(
                DB_PREFIX . 'teacher.name' => $condition['keyword'],
            );
        }
        $list = $this->getAll($where, $data, $condition['page'], null, "teacher");
        $list['where'] = $where;
        $list['keyword'] = $condition['keyword'];
        $list['types'] = $condition['types'];
        $list['page'] = $condition['page'];
        return $list;
    }
    
    //新增老师
    public function addTeacher(){
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $name = isset($_POST['name']) ? $_POST['name'] : "";
        $signature = isset($_POST['signature']) ? $_POST['signature'] : "";
        $title = isset($_POST['title']) ? $_POST['title'] : "";
        $synopsis = isset($_POST['synopsis']) ? $_POST['synopsis'] : "";
        $head_icon = isset($_POST['img_id']) ? $_POST['img_id'] : 0;
        $details_img = isset($_POST['img_id_1']) ? $_POST['img_id_1'] : 0;
        $is_show = isset($_POST['is_show']) ? $_POST['is_show'] : 1;
        if(!$name && !$signature && !$title && !$synopsis && !$head_icon){
            return array(
                'code' => 400,
                'message' => "缺少请求参数"
            );
        }
        $data = array(
            'name' => $name,
            'signature' => $signature,
            'title' => $title,
            'synopsis' => $synopsis,
            'head_icon' => $head_icon,
            'details_img' => $details_img,
            'delete' => 0,
            'is_show' => $is_show,
            'timestamp' => $this->getTime(),
        );
        if($id){
            $data['timestamp_update'] = $this->getTime();
            $row = $this->updateData($data, array('id' => $id),'teacher');
        }else{
            $row = $this->insertData($data,'teacher');
            $url = "https://".SERVER_NAME.ROOT_PATH.'web/tutor/details?id='.$row;
            $this->updateData(array('link' => $url), array('id' => $row),'teacher');
        }
        
       if(!$row){
            return array(
                'code' => 400,
                'message' => '操作失败!',
            );
        }
        
        return array(
            'code' => 200,
            'message' => '操作成功!',
        );
    }
    
    /**
     * 设置冻结/启用(ajax)
     * @param array $params
     * @return array
     * @version YSQ
     */
    public  function ajaxDelete($params){
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
        ), 'teacher');
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
            ), 'teacher');
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
   
    public function teacherDetails($id){
        $list = $this->getOne(array('id' => $id),array('*'),'teacher');
        $img = $this->getOne(array('id' => $list['head_icon']),array('path','filename'),'image');
        $details_img = $this->getOne(array('id' => $list['details_img']),array('path','filename'),'image');
        $list['img'] = $img['path'] . $img['filename'];
        $list['details_img_path'] = $details_img['path'] . $details_img['filename'];
        return $list;
    }
    

    /**************************************************************
     *                                                             *
     *         课程包数据                                                                                                                                        *
     *                                                             *
     ***************************************************************/
    //课程包列表
    public function coursesList($condition)
    {
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'courses.delete', 0);
        if (isset($condition['types']) &&$condition['types']) { //状态：1正常；2禁用；
            $where->equalTo(DB_PREFIX . 'courses.status', $condition['types']);
        }
        if (isset($condition['num']) &&$condition['num']) { //状态：1正常；2禁用；
            $where->equalTo(DB_PREFIX . 'courses.sell_type', $condition['num']);
        }
        if (isset($condition['cid']) &&$condition['cid']) { //类型：1音频；2视频；
            $where->equalTo(DB_PREFIX . 'courses.type', $condition['cid']);
        }
        if (isset($condition['pid']) &&$condition['pid']) { //类型：1音频；2视频；
            $where->equalTo(DB_PREFIX . 'courses.courses_one_type', $condition['pid']);
        }
        $data = array(
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'image',
                    'on' => DB_PREFIX . 'image.id = ' . DB_PREFIX . 'courses.image',
                    'columns' => array(
                        'img_path' => "path",
                        'filename' => "filename",
                    ),
                    'type'=>'left'
                ),
                array(
                    'name' => DB_PREFIX . 'category',
                    'on' => DB_PREFIX . 'category.id = ' . DB_PREFIX . 'courses.courses_one_type',
                    'columns' => array(
                        'category_name' => "name",
                    ),
                    'type'=>'left'
                ),             
            ),
            
            'columns'=>array(
                '*'
            ),
            'order' => array(
                DB_PREFIX.'courses.sort' => 'asc',
            ),
            'need_page'=>true,
    
        );
        if (isset($condition['keyword']) &&$condition['keyword']) {
            $data['search_key'] = array(
                DB_PREFIX . 'courses.title' => $condition['keyword'],
            );
        }
        $list = $this->getAll($where, $data, $condition['page'], null, "courses");
        $list['where'] = $where;
        $list['keyword'] = $condition['keyword'];
        $list['types'] = $condition['types'];
        $list['cid'] = $condition['cid'];
        $list['num'] = $condition['num'];
        $list['page'] = $condition['page'];
        $list['pid'] = $condition['pid'];
        return $list;
    }
    
    //获取分类
    public function getCategory($num = 0)
    {
        $type = isset($_POST['type']) ? $_POST['type'] : 0;
        $genre = isset($_POST['genre']) ? $_POST['genre'] : 0;
        $par_id = isset($_POST['par_id']) ? $_POST['par_id'] : 0;
        if($type == 3){
            $type = 1;
        }else if($type == 4){
            $type = 2;
        }
        if($type){
            $data = array();
            if($genre == 1){
                $data = $this->fetchAll(array('type' => $type,'status' => 1,'delete' => 0,'deep' => 1,'parent_id' => 0),array("columns" => array('id','name'), "order" => array('sort' => 'ASC'),),'category');
            }else{
                $data = $this->fetchAll(array('status' => 1,'delete' => 0,'deep' => 2,'parent_id' => $par_id),array("columns" => array('id','name'), "order" => array('sort' => 'ASC'),),'category');
            }
            if($data){
                $result['num'] = 2;
            }else{
                $result['num'] = 1;
            }
            $result['data'] = $data;
            return $result;
        }else{
            $data = array();
            if($num){
                $data = $this->fetchAll(array('delete' => 0,'deep' => 1,'type' => $num,'parent_id' => 0,'status' => 1),array("columns" => array('id','name'), "order" => array('sort' => 'ASC'),),'category');
                return $data;
            }else{
                $where = new Where();
                $where->greaterThanOrEqualTo('type', 3);
                $where->equalTo('delete', 0);
                $where->equalTo('deep', 1);
                $where->equalTo('parent_id', 0);
                $where->equalTo('status', 1);
                $data = $this->fetchAll($where,array("columns" => array('id','name'), "order" => array('sort' => 'ASC'),),'category');
                return $data;
            }
        }
        
    }
    //新增或者编辑课程包
    public function addCoures(){
        $type = isset($_POST['type']) ? $_POST['type'] : 0;
//         var_dump($_POST);exit;
        $title = isset($_POST['name']) ? $_POST['name'] : "";
        $courses_one_type = isset($_POST['one_type']) ? $_POST['one_type'] : 0;
        $courses_two_type = isset($_POST['two_type']) ? $_POST['two_type'] : 0;
        $price = isset($_POST['price']) ? $_POST['price'] : "0.00";
        $original_price = isset($_POST['original_price']) ? $_POST['original_price'] : "0.00";
        $image = isset($_POST['img_id_0']) ? $_POST['img_id_0'] : 0;
        $details_image = isset($_POST['img_id_1']) ? $_POST['img_id_1'] : 0;
        $audios_synopsis = isset($_POST['synopsis']) ? $_POST['synopsis'] : "";
        $recommend = isset($_POST['recommend']) ? $_POST['recommend'] : 0;
        $sort = isset($_POST['sort']) ? $_POST['sort'] : 0;
        $cour_sort = isset($_POST['cour_sort']) ? $_POST['cour_sort'] : 0;
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $sell_type = isset($_POST['sell_type']) ? $_POST['sell_type'] : 0;
        $code = array();
        if($sell_type == 2){
            $price = '0.00';
        }
        if(!$id){
            if(!$type){
                $code['code'] = 400;
                $code['message'] = "课程包类型不能为空";
            }else if(!$title){
                $code['code'] = 400;
                $code['message'] = "课程包名称不能为空";
            }else if(!$courses_one_type){
                $code['code'] = 400;
                $code['message'] = "课程包分类不能为空";
            }
        }
        if(!$price){
            $code['code'] = 400;
            $code['message'] = "付费金额不能为空";
        }else if(!$audios_synopsis){
            $code['code'] = 400;
            $code['message'] = "课程包介绍不能为空";
        }else if(!$image){
            $code['code'] = 400;
            $code['message'] = "课程包封面不能为空";
        }
        if($code){
            return $code;
        }else{
            $data = array(
                'title' => $title,
                'courses_two_type' => $courses_two_type,
                'price' => $price,
                'sell_type' => $sell_type, 
                'original_price' => $original_price,
                'image' => $image,
                'details_image' => $details_image,
                'audios_synopsis' => $audios_synopsis,
                'status' => 1,
                'recommend' => $recommend,
                'sort' => $sort,
                'cour_sort' => $cour_sort,
                'timestamp' => $this->getTime(),
            );
            if($id){
                $row = $this->updateData($data, array('id' => $id),'courses');
            }else{
                $data['type'] = $type;
                $data['courses_one_type'] =  $courses_one_type;
                $row = $this->insertData($data,'courses');               
                if($type == 3){
                    $url = "https://".SERVER_NAME.ROOT_PATH.'web/audio/details?id='.$row.',2&type=2';
                }else{
                    $url = "https://".SERVER_NAME.ROOT_PATH.'web/video/details?id='.$row.',2&type=2'; 
                }
                $this->updateData(array('link' => $url), array('id' => $row),'courses');
            }
            
            if($row){
                return array(
                    'code' => 200,
                    'message' => "操作成功"
                );
            }else{
                return array(
                    'code' => 400,
                    'message' => "操作失败"
                );
            }
        }

    }
    
    /**
     * 删除课程包
     * @param unknown $id
     * @param string $key
     * @version YSQ
     */
    public function ajaxDeleteCourses($id,$key='',$type=0){
        if(!($id && $key='hdfksje93hjhf89j')){
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
        if($type==1){
            $row = $this->updateData(array('status'=>2),array('id'=>$id), 'courses');
        }else if($type==2){
            $row = $this->updateData(array('status'=>1),array('id'=>$id), 'courses');
        }else{
            $row = $this->updateData(array('delete'=>1),array('id'=>$id), 'courses');
        }
        
        if(!$row){
            return array(
                'code' => 400,
                'message' => '操作失败!',
            );
        }
        return array(
            'code' => 200,
            'message' => '成功!',
        );
    }
    
    /**
     * 课程包详情
     * @param unknown $id
     * 
     * */
    public function coursesDetails($id){
        $where = new Where();
        $where->equalTo('delete', 0);
        $where->equalTo('id', $id);        
        $list = $this->getOne($where,array("*"),'courses');
        $img = $this->getOne(array('id' => $list['image']),array('path','filename'),'image');
        $d_img = $this->getOne(array('id' => $list['details_image']),array('path','filename'),'image');
        $list['img'] = $img['path'] . $img['filename'];
        $list['d_img'] = $d_img['path'] . $d_img['filename'];
        return $list;
    }
    
    /**************************************************************
     *                                                             *
     *         课程数据                                                                                         *
     *                                                             *
     ***************************************************************/
    //课程包列表
    public function videoList($condition)
    {
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'audio.delete', 0);
        $where->equalTo(DB_PREFIX . 'audio.type', $condition['type']);
        if (isset($condition['types']) &&$condition['types']) {
            $where->equalTo(DB_PREFIX . 'audio.status', $condition['types']);
        }
        if (isset($condition['num']) &&$condition['num']) { 
            $where->equalTo(DB_PREFIX . 'audio.sell_type', $condition['num']);
        }
        if (isset($condition['cid']) &&$condition['cid']) { 
            $where->equalTo(DB_PREFIX . 'audio.pay_type', $condition['cid']);
        }
        if (isset($condition['pid']) &&$condition['pid']) { 
            $where->equalTo(DB_PREFIX . 'audio.audio_one_type', $condition['pid']);
        }
        $data = array(
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'image',
                    'on' => DB_PREFIX . 'image.id = ' . DB_PREFIX . 'audio.image',
                    'columns' => array(
                        'img_path' => "path",
                        'filename' => "filename",
                    ),
                    'type'=>'left'
                ),
                array(
                    'name' => DB_PREFIX . 'category',
                    'on' => DB_PREFIX . 'category.id = ' . DB_PREFIX . 'audio.audio_one_type',
                    'columns' => array(
                        'category_name' => "name",
                    ),
                    'type'=>'left'
                ),
                array(
                    'name' => DB_PREFIX . 'teacher',
                    'on' => DB_PREFIX . 'teacher.id = ' . DB_PREFIX . 'audio.teacher_id',
                    'columns' => array(
                        'teacher_name' => "name",
                    ),
                    'type'=>'left'
                ),
                array(
                    'name' => DB_PREFIX . 'courses',
                    'on' => DB_PREFIX . 'courses.id = ' . DB_PREFIX . 'audio.courses_id',
                    'columns' => array(
                        'courses_name' => "title",
                    ),
                    'type'=>'left'
                ),
            ),
    
            'columns'=>array(
                '*'
            ),
            'order' => array(
//                 DB_PREFIX.'audio.id' => 'asc',
            ),
            'need_page'=>true,
    
        );
        if (isset($condition['keyword']) &&$condition['keyword']) {
            $data['search_key'] = array(
                DB_PREFIX . 'audio.title' => $condition['keyword'],
                DB_PREFIX . 'teacher.name' => $condition['keyword'],
            );
        }
        $list = $this->getAll($where, $data, $condition['page'], null, "audio");

        //寫入日誌
//        $log = new Log('admin');
//        $log->info($list['list']);

        $list['where'] = $where;
        $list['keyword'] = $condition['keyword'];
        $list['types'] = $condition['types'];
        $list['type'] = $condition['type'];
        $list['cid'] = $condition['cid'];
        $list['num'] = $condition['num'];
        $list['page'] = $condition['page'];
        $list['pid'] = $condition['pid'];
        return $list;
    }
    
    //获取老师
    public function getTeacher(){
        $data = array();
        $data = $this->fetchAll(array('delete' => 0,'status' => 1,),array("columns" => array('id','name'), "order" => array('id' => 'ASC'),),'teacher');
        return $data;
    }
    
    //获取
    public function getCourses($type){
        $data = array();
        $data = $this->fetchAll(array('delete' => 0,'status' => 1,'type'=> $type),array("columns" => array('id','title'), "order" => array('id' => 'ASC'),),'courses');
        return $data;
    }
    
    //获取
    public function ajaxRealPreview(){
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $type = isset($_POST['type']) ? $_POST['type'] : 1;
        if($type == 1){
            $table = 'audio';
        }else if($type == 2){
            $table = 'courses';
        }
        if($type == 3){
            $data['link'] = "https://".SERVER_NAME.ROOT_PATH.'web/index';
            $data['link'] .= "?code=qrCode";
        }else if($type ==1 || $type == 2){
            $data = $this->getOne(array('id' => $id),array('id','link','status'),$table);
            $data['link'] .= "&code=qrCode";
        }else if($type == 4){
            $data['link'] = "https://".SERVER_NAME.ROOT_PATH.'web/User/presentRecordDetail';  
            $data['link'] .= "?code=qrCode";
        }
        return $data['url'] = $this->generateCode($data['link']);
    }
    
    
    //新增或者编辑数据
    public function addVideos(){
//         var_dump($_POST);exit;
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $teacher_id = isset($_POST['teacher']) ? $_POST['teacher'] : 0;
        $type = $_POST['type'] ? $_POST['type'] : 1;
        $title = isset($_POST['name']) ? $_POST['name'] : "";
        $putaway = isset($_POST['putaway']) ? $_POST['putaway'] : "000-00-00 00:00:00";
        $audio_one_type = isset($_POST['one_type']) ? $_POST['one_type'] : 0;
        $audio_two_type = isset($_POST['two_type']) ? $_POST['two_type'] : 0;
        $pay_type = isset($_POST['recommend']) ? $_POST['recommend'] : 0;
        $sell_type = isset($_POST['sell_type']) ? $_POST['sell_type'] : 0;
        $price = isset($_POST['price']) ? $_POST['price'] : "0.00";
        $original_price = isset($_POST['original_price']) ? $_POST['original_price'] : "0.00";
        $audio_synopsis = isset($_POST['synopsis']) ? $_POST['synopsis'] : "";
        $courses_id = isset($_POST['courses']) ? $_POST['courses'] : 0;
        $outline = isset($_POST['outline']) ? $_POST['outline'] : "";
        $image = isset($_POST['img_id_0']) ? $_POST['img_id_0'] : 0;
        $details_image = isset($_POST['img_id_1']) ? $_POST['img_id_1'] : 0;
        $auditions_path = isset($_POST['auditions_path']) ? $_POST['auditions_path'] : "";
        $full_path = isset($_POST['full_path']) ? $_POST['full_path'] : "";
//         $timestamp = isset($_POST['type']) ? $_POST['type'] : 0;
        $type = isset($_POST['type']) ? $_POST['type'] : 0;
        $recommend_sing = isset($_POST['recommend_sing']) ? $_POST['recommend_sing'] : 0;
        $sort = isset($_POST['sort']) ? $_POST['sort'] : 1;
        if($recommend_sing && ($pay_type != 2 &&  $pay_type != 3)){
            $pay_type = $recommend_sing;
        }
        if($pay_type == 2 || $pay_type == 3){
            $courses_id = 0;
        }
        if($pay_type == 3){
            $sell_type = 0;
        }
        $code = array();
        if(!$title){
            $code['code'] = 400;
            $code['message'] = "课程名称不能为空";
        }else if(!$putaway){
            $code['code'] = 400;
            $code['message'] = "上架时间不能为空";
        }else if(!$audio_one_type){
            $code['code'] = 400;
            $code['message'] = "课程分类不能为空";
        }else if(!$teacher_id){
            $code['code'] = 400;
            $code['message'] = "课程老师不能为空";
        }else if(!$full_path){
            $code['code'] = 400;
            $code['message'] = "完整课程不能为空";
        }else if(!$audio_synopsis){
            $code['code'] = 400;
            $code['message'] = "课程介绍不能为空";
        }else if(!$outline){
            $code['code'] = 400;
            $code['message'] = "课程概述不能为空";
        }
        if($pay_type != 3){
            if(!$auditions_path){
                $code['code'] = 400;
                $code['message'] = "试听课程不能为空";
            }
        }
        if($code){
            return $code;
        }else{
            $teacher_if = $this->getOne(array('id'=>$teacher_id),array('head_icon','name'),'teacher');
            $data = array(
                'teacher_id' => $teacher_id,
                'sort' => $sort,
                'type' => $type,
                'putaway' => $putaway,
                'title' => $title,
                'audio_one_type' => $audio_one_type,
                'audio_two_type' => $audio_two_type,
                'pay_type' => $pay_type,
                'sell_type' => $sell_type, 
                'price' => $price,
                'original_price' => $original_price,
                'audio_synopsis' => $audio_synopsis,
                'courses_id' => $courses_id,
                'outline' => $outline,
                'image' => $image,
                'details_image' => $details_image,
                'auditions_path' => $auditions_path,
                'auditions_length' => isset($_POST['auditions_time'])? $_POST['auditions_time'] : "0.00",
                'full_path' => $full_path,
                'audio_length' => $_POST['full_time'] ? $_POST['full_time'] : "0.00",
                'status' => 1,
                'delete' => 0,
                'timestamp' => $this->getTime(),
            );
            if($putaway > date('Y-m-d H:i:s',time())){
                $data['status'] = 3;
            }
//             if($data['auditions_path']){
//                 $data['auditions_length'] = $this->getFileLength($data['auditions_path']);
//                 if(is_array($data['auditions_length'])){
//                     return $data['auditions_length'];
//                 }
//             }
//             $data['audio_length'] = $this->getFileLength($data['full_path']);
//             if(is_array($data['auditions_length'])){
//                 return $data['auditions_length'];
//             }
        }

        if($data['pay_type'] == 3){
            $data['price'] = "0.00";
            $data['original_price'] = "0.00";
        }else if($data['pay_type'] == 2){
            if($data['sell_type'] == 2){
                $data['price'] = "0.00";
            }
        }else if($data['pay_type'] == 1){
            $data['price'] = "0.00";
            $data['original_price'] = "0.00";
        }
        if($id){
            $video = $this->getOne(array('id' => $id),array('id','courses_id','teacher_id','study_num'),'audio');
            if($video['courses_id'] != $data['courses_id']){
                $this->updateKey($video['courses_id'], 2, 'study_num', $video['study_num'],'courses');
                $this->updateKey($data['courses_id'], 1, 'study_num', $video['study_num'],'courses');
                $this->updateData(array('courses_id' => $data['courses_id']), array('audio_id' => $id),'comment');
            }
            if($type==1){
                $this->updateKey($video['teacher_id'], 2, 'audio_num', 1,'teacher');
            }else{
                $this->updateKey($video['teacher_id'], 2, 'video_num', 1,'teacher');
            }
            if($video['courses_id'] > 0){
                $this->removeVideo($id, $video['courses_id'], $video['teacher_id']);
            }
            $this->updateData($data, array('id' => $id),'audio');
            $courses = $this->getOne(array('id' => $data['courses_id']),array('id','audios_ids','teacher_ids'),'courses');
            $row = $id;
        }else{
            $row = $this->insertData($data,'audio');
            if($type == 1){
                $url = "https://".SERVER_NAME.ROOT_PATH.'web/audio/details?id='.$row.',1'.'&type=1';
            }else{
                $url = "https://".SERVER_NAME.ROOT_PATH.'web/video/details?id='.$row.',1'.'&type=1';
            }
            $courses = $this->getOne(array('id' => $data['courses_id']),array('id','audios_ids','teacher_ids'),'courses');
            //1 是音频 2 视频
            if($putaway <= date('Y-m-d H:i:s',time())){
                $sub_teacher = $this->fetchAll(array('teacher_id' => $data['teacher_id']),array("columns" => array('id','user_id')),'subscription');
                 if($sub_teacher){
                     foreach ($sub_teacher as $v){
                        
                             $this->insertData(array(
                                 'user_id' => $v['user_id'],
                                 'audio_id' => $row,
                                 'type' => $type,
                                 'delete' => 0,
                                 'is_new' => 2,
                                 'timestamp' => $this->getTime(),
                             ),'notification_subscibe');
                        
                         
                         if(SEND_KEY && $data['pay_type'] != 1){
                             $send_user = $this->getOne(array('id' => $v['user_id'],'delete' => 0,'status' => 1),array('id','open_id'),'user');
                             $send_teacher = $this->getOne(array('id' => $data['teacher_id'],'delete' => 0),array('id','name'),'teacher');
                             $send_category = $this->getOne(array('id' => $data['audio_two_type'],'delete' => 0),array('id','name'),'category');
                             $str = date('m',strtotime($data['putaway'])).'月'.date('d',strtotime($data['putaway'])).'日';
                             if(date('H',strtotime($data['putaway'])) > 12){
                                 $str .= " 下午".date('H:i',strtotime($data['putaway']));
                             }else{
                                 $str .= " 上午".date('H:i',strtotime($data['putaway']));
                             }
                             if(!empty($send_user['id']) && !empty($send_user['open_id'])){
                                 $send_data['id'] = isset($row) ? $row : 0;
                                 $send_data['title'] = $data['title'];
                                 $send_data['category'] =  $send_category['name'];
                                 $send_data['teacher_name'] = $send_teacher['name'].'老师';
                                 $send_data['time'] = $str;
                                 $send_data['url'] = $url;
                                 $shop_sendRs = $this->sendTempMessage(2,$send_user['open_id'],$send_data);
                             }
                         }
                     }
                 }
            }
            $this->updateData(array('link' => $url), array('id' => $row),'audio');
        }
        //追加课程包
        if($data['pay_type'] == 1 || $data['pay_type'] == 4){
            if($courses['audios_ids']){
                $au_ids = array_filter(explode(',', $courses['audios_ids']));
                $au_ids[] = $row;
                $au_ids = implode(',',$au_ids);
            }else{
                $au_ids = implode(',',array($row));
            }
            if($courses['teacher_ids']){
                $tea_ids = array_filter(explode(',', $courses['teacher_ids']));
                $tea_ids[] = $data['teacher_id'];
                $tea_ids = implode(',',$tea_ids);
            }else{
                $tea_ids = implode(',',array($data['teacher_id']));
            }
            if($video['courses_id'] > 0){
                $this->removeVideo($id, $video['courses_id'], $video['teacher_id']);
            }
            $this->updateData(array('audios_ids' => ','.$au_ids.',','teacher_ids'=> ','.$tea_ids.','),array('id' => $data['courses_id']),'courses');
            $this->updateKey(array('id' => $data['courses_id']),1, 'audios_num', 1,'courses');
        }
      
        if($type==1){
            $this->updateKey($data['teacher_id'], 1, 'audio_num', 1,'teacher');
        }else{
            $this->updateKey($data['teacher_id'], 1, 'video_num', 1,'teacher');
        }
        if($row){
            return array(
                'code' => 200,
                'message' => "操作成功"
            );
        }else{
            return array(
                'code' => 400,
                'message' => "操作失败"
            );
        }
    }
   /*  public function getFileLength($url){
        $newfname = LOCAL_SAVEPATH.basename($url);
        $file = fopen($url, "rb");
        if ($file) {
            $newf = fopen($newfname, "wb");
            if ($newf)
                while (! feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
        include_once APP_PATH."/vendor/Core/System/getID3/getid3.php";
        //获取音频时间
        $getID3 = new \getID3();
        $ThisFileInfo = $getID3->analyze($newfname);
        unlink ($newfname);
        if($ThisFileInfo['playtime_string']){
            return $ThisFileInfo['playtime_string'];
        }else{
            return array(
                'code'  => 400,
                'message' => '获取不到音频长度！'
            );
        }
        
    } */
    
    /**
     * 删除课程包
     * @param unknown $id
     * @param string $key
     * @version YSQ
     */
    public function ajaxDeleteVideo($id,$key='',$type=0){
        if(!($id && $key='hdfksje93hjhf89j')){
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
//                     var_dump($id);exit;
        $video = $this->getOne(array('id'=>$id),array('id','pay_type','courses_id','teacher_id','type'),'audio');
        if($video['pay_type'] == 1 || $video['pay_type'] == 4){
            if($type==1 || !$type){
                $this->removeVideo($video['id'],$video['courses_id'],$video['teacher_id']);
            }else if($type == 2){
                $courses = $this->getOne(array('id'=>$video['courses_id']),array('id','audios_ids','teacher_ids'),'courses');
                if($courses['audios_ids']){
                    $au_ids = array_filter(explode(',', $courses['audios_ids']));
                    $au_ids[] = $id;
                    $au_ids = implode(',',$au_ids);
                }else{
                    $au_ids = implode(',',array($video['courses_id']));
                }
                if($courses['teacher_ids']){
                    $tea_ids = array_filter(explode(',', $courses['teacher_ids']));
                    $tea_ids[] = $video['teacher_id'];
                    $tea_ids = implode(',',$tea_ids);
                }else{
                    $tea_ids = implode(',',array($video['teacher_id']));
                }
                if($video['courses_id'] > 0){
                    $this->removeVideo($id, $video['courses_id'], $video['teacher_id']);
                }
                $this->updateData(array('audios_ids' => ','.$au_ids.',','teacher_ids'=> ','.$tea_ids.','),array('id' => $video['courses_id']),'courses');
                $this->updateKey(array('id' => $video['courses_id']),1, 'audios_num', 1,'courses');
            }
        }

        if($type==1){
            $row = $this->updateData(array('status'=>2),array('id'=>$id), 'audio'); 
        }else if($type==2){
            $row = $this->updateData(array('status'=>1),array('id'=>$id), 'audio'); 
        }else{
            $row = $this->updateData(array('delete'=>1),array('id'=>$id), 'audio');
            if($video['type']==1){
                $this->updateKey($video['teacher_id'], 2, 'audio_num', 1,'teacher');
            }else{
                $this->updateKey($video['teacher_id'], 2, 'video_num', 1,'teacher');
            }
        }
        $info_type = $this->getOne(array('id'=>$id), array('type'), 'audio');
        if(!$row){
            return array(
                'code' => 400,
                'message' => '删除失败!',
            );
        }
        return array(
            'code' => 200,
            'message' => '成功!',
            'type' => $info_type['type'],
        );
    }
    
    /**
     * 课程包详情
     * @param unknown $id
     *
     * */
    public function videoDetails($id){
        $where = new Where();
        $where->equalTo('delete', 0);
        $where->equalTo('id', $id);
        $list = $this->getOne($where,array("*"),'audio');
        $img = $this->getOne(array('id' => $list['image']),array('path','filename'),'image');
        $d_img = $this->getOne(array('id' => $list['details_image']),array('path','filename'),'image');
        $list['img'] = $img['path'] . $img['filename'];
        $list['d_img'] = $d_img['path'] . $d_img['filename'];
//         var_dump($list);exit;
        return $list;
    }
    
    //移除老师和音频ID
    public function removeVideo($video_id,$courses_id,$teacher_id){
        $courses = $this->getOne(array('id' => $courses_id),array('id','audios_ids','teacher_ids'),'courses');
        $au_ids =  array_filter(explode(',', $courses['audios_ids']));
        $tea_ids =array_filter(explode(',', $courses['teacher_ids']));
 
        for($i=1;$i<=count($au_ids);$i++){
            if($au_ids[$i] == $video_id && $tea_ids[$i] == $teacher_id){
                unset($au_ids[$i]);
                unset($tea_ids[$i]);
                $this->updateKey(array('id' => $courses_id),2, 'audios_num', 1,'courses');
            }
        }
        $au_ids = implode(',',$au_ids);
        $tea_ids = implode(',',$tea_ids);
        if($au_ids && $tea_ids){
            $this->updateData(array('audios_ids' => ','.$au_ids.',','teacher_ids'=> ','.$tea_ids.','),array('id' => $courses_id),'courses');
        }else{
            $this->updateData(array('audios_ids' => $au_ids,'teacher_ids'=> $tea_ids),array('id' => $courses_id),'courses');
        }
        
    }
    
    /**
     * 批量删除/下架
     * @param unknown $id
     *
     * */
    public function ajaxDeleteData(){
        $ids = $_POST['ids'] ? array_filter(explode(',', $_POST['ids'])) : 0;
        $type = $_POST['type'] ? $_POST['type'] : 0;
        $audio_type = isset($_POST['audio_type']) ? $_POST['audio_type'] : 0;
        if(!$ids){
            return array(
                'code' => 400,
                'msg' => '缺少请求参数'
            );
        }
        if(!$type){
            return array(
                'code' => 400,
                'msg' => '缺少请求参数'
            );
        }
        $where = new Where();
        $where->in('id',$ids);
        if($audio_type){
            if($type == 1){
                $row = $this->updateData(array('delete' => 1), $where,'courses');
            }else if($type == 2){
                $row = $this->updateData(array('status' => 2), $where,'courses');
            }
        }else{
            if($type == 1){
                $row = $this->updateData(array('delete' => 1), $where,'audio');
            }else if($type == 2){
                $row = $this->updateData(array('status' => 2), $where,'audio');
            } 
        }
       
        if($row){
            return array(
                'code' => 200,
                'msg' => '操作成功', 
            );
        }else{
            return array(
                'code' => 400,
                'msg' => '操作失败',
            );
        }
    }
    //敏感词管理
    public function sensitiveWords(){
        $word = $this->getCache('SensitiveWords/words');
        if(!$word)
        {
            return "";
        }
        $words = array();
        foreach ($word as $k => $v)
        {
            $words[] = $k;
        }
        $words = implode($words, '|');
        return $words;
    }
    
    //新增或者编辑敏感词
    public function ajaxSensitiveWords(){
        if(!$_POST['content']){
             return array(
                'code' => 200,
                'message' => '敏感词不能为空!',
            ); 
        }
        $words = array_unique(explode('|',trim( trim($_POST['content'],'|'))));
        foreach ($words as $k => $v)
        {
            $strlen = mb_strlen($v, 'utf-8');
            $star = '';
            for ($i = 0; $i < $strlen; $i ++)
            {
                $star .= '*';
            }
            $str[$v] = $star;
            $strlen = 0;
        }
        $this->setCache('SensitiveWords/words', $str);
        return array(
            'code' => 200,
            'message' => '操作成功',
        );
    }

    //获取首页免费视频信息
    public function homePageSetting(){
        $data = $this->fetchAll(array(),array("columns" => array('id','audio_id','teacher_name_hide','putaway','type')),'audio_setting');
        if($data){
            foreach($data  as $v){
                if($v['type'] == 1){
                    $one_data[] = array(
                        'id' => $v['id'],
                        'audio_id' => $v['audio_id'],
                        'hide' => $v['teacher_name_hide'],
                        'type' => $v['type'],
                    );
                }else{
                    $two_data[] = array(
                        'id' => $v['id'],
                        'audio_id' => $v['audio_id'],
                        'hide' => $v['teacher_name_hide'],
                        'putaway' => $v['putaway'],
                        'type' => $v['type'],
                    );
                }
            }
        }

        return array(
            'one_data' => isset($one_data) ? $one_data : array(),
            'two_data' => isset($two_data) ? $two_data : array()
        );
    }

    public function freeOfCharge(){
        $where = array(
            'pay_type' => 3,
            'delete' => 0,
            'type' => 1
        );
        $data = $this->fetchAll($where,array("columns" => array('id','title')),'audio');
        return $data;
    }

    public function addPageSetting($data){
        foreach($data['infos'] as $k=>$v){
            $set = array(
                'audio_id' => $v['audio_id'],
                'type' => $v['type'],
                'teacher_name_hide' => isset($v['teacher_name_hide']) ? $v['teacher_name_hide'] : 2,
                'putaway' => isset($v['putaway']) ? $v['putaway'] : $this->getTime(),
            );
            if(!$v['id']){
                $this->insertData($set,'audio_setting');
            }else{
                $this->updateData($set,array('id' => $v['id']),'audio_setting');
            }
        }
        return true;
    }

    public function addSearch($data){
        $search = array(
           'search_word' => $data['search_word'],
            'content' => $data['content'],
        );
        $this->setCache('SensitiveWords/search', serialize($search));
    }

    public function selectSearch(){
        $word = $this->getCache('SensitiveWords/search');
        return unserialize($word[0]);
    }
}
?>