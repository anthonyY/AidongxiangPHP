<?php
namespace Web\Model;

use Zend\Db\Sql\Where;
class TutorModel extends CommonModel
{
    protected $table = '';
    public function ajaxGetTutorList($type = false)
    {
        $page = $_POST['page'] ? $_POST['page'] : 1;
        $sk = isset($_POST['key_word']) && $_POST['key_word'] ? $_POST['key_word'] : "";
        $data = array(
            'columns' => array('id','name','signature','head_icon','play_num'),
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'image',
                    'on' => DB_PREFIX . 'teacher.head_icon = ' . DB_PREFIX . 'image.id',
                    'columns' => array(
                        'img_id' => "id",
                        'img_path' => "path",
                        'img_filename' => "filename",
                    ),
                    'type'=>'left'
                ),
            ),
            'need_page' => true,
        );
        $where = new Where();
        $where->equalTo('delete',0);
        $where->equalTo('status',1);
        if($type && $sk){
            $where->like('name','%'.$sk.'%');
        }else{
            $where->equalTo('is_show',1);
        }
        $teacher_list = $this->getAll($where,$data,$page,PAGE_NUMBER,'teacher');
        $teachers = array();
        if($teacher_list['total'] > 0)
        {
            foreach ($teacher_list['list'] as $v)
            {
                $item = array(
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'signature' => $v['signature'],
                );
                $audio_info = $this->getOne(array('teacher_id'=>$v['id'],'delete' => 0,'status' => 1),array('timestamp'),'audio',array('timestamp DESC'));
//                 $item['timestamp'] = $audio_info ? $this->convertTime($audio_info->timestamp) : 0;
                $item['timestamp'] = $audio_info ? strtotime($audio_info->timestamp) : 0;
                $item['play_num'] = $this->convertTenThousandNum($v['play_num']);
                $item['head_icon'] = $v['img_path'] ? ROOT_PATH.UPLOAD_PATH.$v['img_path'].$v['img_filename'] : "";
                $teachers[] = $item;
            }
            if($teachers)
            {
                $sort = array(
                     'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
                     'field'     => 'timestamp', //排序字段
                    );
                $teacherSort = array();
                foreach($teachers AS $uniqid => $row){
                    foreach($row AS $key=>$value){
                        $teacherSort[$key][$uniqid] = $value;
                    }
                }
                if($sort['direction']){
                    array_multisort($teacherSort[$sort['field']], constant($sort['direction']), $teachers);
                }
                
                foreach ($teachers as &$m)
                {
                    $m['timestamp'] = $m['timestamp'] ? $this->convertTime(date("Y-m-d H:i:s",$m['timestamp'])) : "";
                }
            }
        }
        return array('code'=>200,'list'=>$teachers);
    }
    
    /**
     * 导师详情
     */
    public function details($id)
    {
        if(!$id)
        {  
            $id = $_GET['id'] ? $_GET['id'] : 0;
            if(!$id){
                return array('code'=>400,'message'=>'参数错误');
            }            
        }
        $teacher_info = $this->getOne(array('id'=>$id,'delete'=>0),array('*'),'teacher');
        $subscription = $this->getOne(array('teacher_id'=>$id,'user_id'=>$_SESSION['user_id'],'delete'=>0),array('*'),'subscription');
        if(!$teacher_info)
        {
            return array('code'=>400,'message'=>'数据不存在');
        }
        $head_icon = $this->getOne(array('id'=>$teacher_info['head_icon']),array('id','path','filename'),'image');
        $teacher_info['head_icon'] = $head_icon ? ROOT_PATH.UPLOAD_PATH.$head_icon['path'].$head_icon['filename'] : "";
        $teacher_info['subscription_num'] = $this->convertTenThousandNum($teacher_info['subscription_num']);
        $teacher_info['play_num'] = $this->convertTenThousandNum($teacher_info['play_num']);
        $teacher_info['subscription']  = $subscription ? 1 : 0;
        return array('code'=>200,'data'=>$teacher_info);
    }
    
    /**
     * 导师的课程包列表
     */
    public function ajaxGetTutorCourseList()
    {
        $page = $_POST['page'] ? $_POST['page'] : 1;
        $teacher_id = $_POST['teacher_id'] ? $_POST['teacher_id'] : 0;
        $audio_type = $_POST['audio_type'] ? $_POST['audio_type'] : 3;
        if(!$teacher_id)
        {
            return array('code'=>400,'message'=>'参数错误');
        }
        if($audio_type == 3){
            $one_audio_type = 1;
            $two_audio_type = 3;
        }else{
            $one_audio_type = 2;
            $two_audio_type = 4;
        }
        $sql = "SELECT `sell_type`,`audio_length` AS `count`,`price`,`original_price`, `nb_audio`.sort AS `sort`, nb_audio.`id` AS `audio_id`,nb_audio.`type` AS `type`,NULL AS audios_ids,`nb_audio`.`title` AS `audio_name`,`nb_audio`.`study_num` AS `study_num` ,`nb_audio`.`audio_length` AS `audio_length`, `nb_audio`.`image` AS `image` , `nb_audio`.`teacher_id` AS `teacher_id` , NULL AS `audio_num` ,`timestamp` AS `time` FROM nb_audio WHERE teacher_id = ".$teacher_id." AND `delete` = 0 AND `status`=1 AND `pay_type`!=1 AND `type` = ".$one_audio_type."
                UNION
                SELECT `sell_type`,NULL AS count,`price`,`original_price`, `nb_courses`.cour_sort AS `sort`, nb_courses.id AS `audio_id`,nb_courses.`type` AS `type`,nb_courses.audios_ids AS audios_ids ,nb_courses.title AS `audio_name`,`nb_courses`.`study_num` AS `study_num`,  NULL AS `audio_length`,`nb_courses`.`image` AS `image`,`nb_courses`.`teacher_ids` AS `teacher_id`,`nb_courses`.`audios_num` AS `audio_num`,`timestamp` AS `time` FROM nb_courses WHERE  `teacher_ids` LIKE '%,".$teacher_id.",%' AND `delete` = 0 AND `status`=1 AND `type` =  ".$two_audio_type." ORDER BY `type` DESC,`sort` ASC,`time` DESC";
        $num = 5;
        $offset = ($page-1)*$num;
        $sql = $sql ." LIMIT " . $offset .','. $num;
        $courses_list = $this->executeSql($sql);
        if($courses_list['total'])
        {
            foreach ($courses_list['list'] as $v)
            {
              $v['timestamp'] = date('m-d',strtotime($v['time']));
                if($v['type'] == 2 || $v['type'] == 1){                   
                    $teacher = $this->getOne(array('id'=>$v['teacher_id']),array('name','head_icon'),'teacher');
                    $v['audio_length'] = $this->getShiftTime($v['audio_length']);
                    if($v['type'] == 2){
                        $img = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                    }else{
                        if($v['image']){
                            $img = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                        }else{
                            $img = $this->getOne(array('id'=>$teacher['head_icon']),array('path','filename'),'image');
                        }
                    }
                    $v['teacher_name'] = $teacher['name'];
                    $v['img'] = ROOT_PATH.UPLOAD_PATH.$img['path'].$img['filename'];
                
                    //观看的百分比
                    $watch_record = $this->getOne(array('user_id' => $_SESSION['user_id'],'audio_id' => $v['audio_id']),array('id','time'),'watch_record');
                    if(strpos($v['count'],"时")){
                        $str = preg_replace('/([\d]+)时([\d]+)分([\d]+)秒/', '$1:$2:$3', $v['count']);
                    }else{
                        $str = preg_replace('/([\d]+)分([\d]+)秒/', '00:$1:$2', $v['count']);
                    }
                    $parsed = date_parse($str);
                    $v['count'] = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
                    $v['count'] = $v['count'] ? round($watch_record['time']/$v['count']*100, 0). "%"  : "";
                }else if($v['type'] == 3 || $v['type'] == 4){
                    $img = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                    $v['img'] = ROOT_PATH.UPLOAD_PATH.$img['path'].$img['filename'];
                }
                if($v['sell_type'] == 2){
                    $v['price'] = "仅限会员";
                }else{
                    $v['price'] = $v['price'] == '0.00' ? "免费" : $v['price']."元";
                }
                $v['original_price'] = $v['original_price'] ==  '0.00' ? "免费" : $v['original_price']."元";
            }
        }
        return array('code'=>200,'list' => $courses_list['list']);
//         $where = new Where();
//         $where->equalTo('delete', 0)->equalTo('status', 1);
//         $where->equalTo('type', $audio_type);
//         $where->greaterThan('audios_num', 0);
//         $where->like("teacher_ids", "%,".$teacher_id.",%");
//         $data = array(
//             'columns' => array('id','type','title','image','audios_synopsis','study_num','audios_num','audios_ids'),
//             'join' => array(
//                 array(
//                     'name' => DB_PREFIX . 'image',
//                     'on' => DB_PREFIX . 'courses.image = '.DB_PREFIX . 'image.id',
//                     'columns' => array('img_id'=>'id','img_path'=>'path','img_filename'=>'filename'),
//                     'type'
//                 ),
//             ),
//             'need_page' => true,
//             'limit' => PAGE_NUMBER
//         );
//         $courses_list = $this->getAll($where,$data,$page,PAGE_NUMBER,'courses');
//         if($courses_list['total'])
//         {
//             foreach ($courses_list['list'] as $v)
//             {
//                 $v['course_image'] = isset($v['img_filename']) ? ROOT_PATH.UPLOAD_PATH.$v['img_path'].$v['img_filename'] : "";
//                 $v['study_num'] = $this->convertTenThousandNum($v['study_num']);
                
//                 $audios_ids = explode(",", trim($v['audios_ids'],','));
//                 $audio_info = $this->getOne(array('id'=>$audios_ids[0]),array('id','full_path','auditions_path'),'audio');
//                 if(in_array($audio_info['id'], $_SESSION['buy_audio_ids']))
//                 {
//                     $v['full_path'] = $audio_info ? $audio_info->full_path : "";
//                 }
//                 else 
//                 {
//                     $v['full_path'] = $audio_info ? $audio_info->auditions_path : "";
//                 }
                
//                 $v['first_audio_id'] = $audio_info ? $audio_info['id'] : 0;
//             }
//         }
//         return array('code'=>200,'list' => $courses_list['list']);
    }
    
    //订阅老师
    public function ajaxAttention(){
        $teacher_id = isset($_POST['teacher_id']) ? $_POST['teacher_id'] : 0; //课程或者课程包id
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0; //1 课程 2 课程包
        if(!$teacher_id && !$user_id){
            return array(
                'code' => 400,
                'message' => '操作失败',
            );
        }
        $teacher_info = $this->getOne(array('id'=>$teacher_id),array('*'),'teacher');
        $data = $this->getOne(array('delete' => 0,'teacher_id' => $teacher_id,'user_id' => $user_id),array('*'),'subscription');
        if(!$data){
            $row = $this->insertData(array(
                'teacher_id' => $teacher_id,
                'user_id' => $user_id,
                'delete' => 0,
                'timestamp' => $this->getTime()
            ), 'subscription');
            if($row){
                $this->updateData(array('subscription_num'=>$teacher_info['subscription_num'] + 1), array('id'=>$teacher_id),'teacher');
                return array(
                    'code' => 200,
                    'message' => '操作成功',
                    'num' => 1,
                    'subscription_num' => $teacher_info['subscription_num'] + 1
                );
            }
        }else{
            $row = $this->deleteData(array('id' => $data['id']),'subscription',true);
            $teacher_data = array(
                'join' => array(
                    array(
                        'name' => DB_PREFIX . 'audio',
                        'on' => DB_PREFIX . 'notification_subscibe.audio_id = '.DB_PREFIX . 'audio.id',
                        'columns' => array(
                            'teacher_id'=>'teacher_id',
                            'audio_id'=>'id'
                        )
                    ),
                ),
                'columns' => array('*'),
                'need_page' => false,
                'limit' => PAGE_NUMBER
            );
            $where = new where();
            $where->equalTo(DB_PREFIX . 'notification_subscibe.is_new', 2);
            $where->equalTo(DB_PREFIX . 'audio.teacher_id', $teacher_id);
            $teacher_list = $this->getAll($where,$teacher_data,$teacher_data['need_page'],PAGE_NUMBER,'notification_subscibe');
            if($teacher_list['list']){
                foreach ($teacher_list['list'] as $v){
                    $this->updateData(array('is_new' => 1), array('id' => $v['id']),'notification_subscibe');
                }
            }
            if($row){
                $this->updateData(array('subscription_num'=>(($teacher_info['subscription_num'] - 1) < 0) ? 0 : ($teacher_info['subscription_num'] - 1)), array('id'=>$teacher_id),'teacher');
                return array(
                    'code' => 200,
                    'message' => '操作成功',
                    'num' => 2,
                    'subscription_num' => (($teacher_info['subscription_num'] - 1) < 0) ? 0 : ($teacher_info['subscription_num'] - 1)
                );
            }
        }
        return array(
            'code' => 400,
            'message' => '操作失败'
        );
    }
    
    /* 订阅老师*/
    public function ajaxGetSubscriptionTutorList()
    {
        $page = $_POST['page'] ? $_POST['page'] : 1;
        $user_id = $_SESSION['user_id'];
        $data = array(
            'columns' => array('teacher_id'),
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'teacher',
                    'on' => DB_PREFIX . 'subscription.teacher_id = ' . DB_PREFIX . 'teacher.id',
                    'columns' => array('id','name','signature','head_icon','play_num'),
                    'type'=>'left'
                ),
                array(
                    'name' => DB_PREFIX . 'image',
                    'on' => DB_PREFIX . 'teacher.head_icon = ' . DB_PREFIX . 'image.id',
                    'columns' => array(
                        'img_path' => "path",
                        'img_filename' => "filename",
                    ),
                    'type'=>'left'
                ),
            ),
            'need_page' => true,
        );
        $teacher_list = $this->getAll(array(DB_PREFIX . 'subscription.delete'=>0,DB_PREFIX . 'subscription.user_id'=>$user_id),$data,$page,PAGE_NUMBER,'subscription');
        $this->updateData(array('is_new' => 1),array('user_id' => $_SESSION['user_id']),'notification_subscibe');
        $teachers = array();
        if($teacher_list['total'] > 0)
        {
            foreach ($teacher_list['list'] as $v)
            {
                $item = array(
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'signature' => $v['signature'],
                );
                $audio_info = $this->getOne(array('teacher_id'=>$v['id']),array('timestamp'),'audio',array('timestamp DESC'));
                //                 $item['timestamp'] = $audio_info ? $this->convertTime($audio_info->timestamp) : 0;
                $item['timestamp'] = $audio_info ? strtotime($audio_info->timestamp) : 0;
                $item['play_num'] = $this->convertTenThousandNum($v['play_num']);
                $item['head_icon'] = $v['img_path'] ? ROOT_PATH.UPLOAD_PATH.$v['img_path'].$v['img_filename'] : "";
                $teachers[] = $item;
            }
            if($teachers)
            {
                $sort = array(
                    'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
                    'field'     => 'timestamp', //排序字段
                );
                $teacherSort = array();
                foreach($teachers AS $uniqid => $row){
                    foreach($row AS $key=>$value){
                        $teacherSort[$key][$uniqid] = $value;
                    }
                }
                if($sort['direction']){
                    array_multisort($teacherSort[$sort['field']], constant($sort['direction']), $teachers);
                }
    
                foreach ($teachers as &$m)
                {
                    $m['timestamp'] = $m['timestamp'] ? $this->convertTime(date("Y-m-d H:i:s",$m['timestamp'])) : "";
                }
            }
        }
        return array('code'=>200,'list'=>$teachers);
    }
}