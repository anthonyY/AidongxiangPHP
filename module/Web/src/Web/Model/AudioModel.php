<?php
namespace Web\Model;

use Zend\Db\Sql\Where;
use Core\System\AiiUtility\Log;
class AudioModel extends CommonModel
{
    protected $table = '';
    
    public function getAudioList($condition)
    {
        $page = isset($condition['page']) ? $condition['page'] : 1;
        $one_category = isset($condition['one_category']) ? $condition['one_category'] : 0;
        $two_category = isset($condition['two_category']) ? $condition['two_category'] : 0;
        $sk = isset($condition['sk']) ? $condition['sk'] : "";
        if($sk){
            $sql = "(SELECT `id` ,`title`,`sell_type`,NULL AS count,`price`,`original_price`,`cour_sort`,2 AS `type`,`audios_num`,`audios_ids` ,study_num,NULL AS pay_type,NULL AS over_play, NULL AS putaway,NULL AS audio_length ,NULL AS auditions_path,NULL AS full_path,teacher_ids,image,`timestamp`,`timestamp_update` FROM nb_courses WHERE `title` like '%".$sk."%' AND `type` = ".$condition['cou_type']."  AND `delete` = 0 AND `status` = 1  AND `audios_num` > 0)
                    UNION
                    (SELECT `id`,`title`,`sell_type`,`audio_length`,`price`,`original_price`,`sort`,1 AS `type`, NULL,NULL, study_num,pay_type,over_play,putaway,audio_length ,auditions_path,full_path,teacher_id,image,`timestamp`,`timestamp_update` FROM nb_audio WHERE `putaway` >= ".time()." AND  `title` like '%".$sk."%' AND `type` = ".$condition['type']."  AND `delete` = 0 AND `STATUS` = 1 AND `pay_type` != 1)";
        }else if($two_category){
            $sql = "(SELECT `id` ,`title`,`sell_type`,NULL AS count,`price`,`original_price`,`cour_sort`,2 AS `type`,`audios_num`,`audios_ids` ,study_num,NULL AS pay_type,NULL AS over_play, NULL AS putaway,NULL AS audio_length ,NULL AS auditions_path,NULL AS full_path,teacher_ids,image,`timestamp`,`timestamp_update` FROM nb_courses WHERE `courses_two_type` = ".$two_category." AND `delete` = 0 AND `status` = 1  AND `audios_num` > 0)
                    UNION
                    (SELECT `id`,`title`,`sell_type`,`audio_length`,`price`,`original_price`,`sort`,1 AS `type`, NULL,NULL, study_num,pay_type,over_play,putaway,audio_length ,auditions_path,full_path,teacher_id,image,`timestamp`,`timestamp_update` FROM nb_audio WHERE `putaway` >= ".time()." AND  `audio_two_type` = ".$two_category." AND `delete` = 0 AND `STATUS` = 1 AND `pay_type` != 1)";
        }else{
            $sql = "(SELECT `id` ,`title`,`sell_type`,NULL AS count,`price`,`original_price`,`cour_sort`,2 AS `type`,`audios_num`,`audios_ids` ,study_num,NULL AS pay_type, NULL AS over_play,NULL AS putaway,NULL AS audio_length ,NULL AS auditions_path,NULL AS full_path,teacher_ids,image,`timestamp`,`timestamp_update` FROM nb_courses WHERE `courses_one_type` = ".$one_category." AND `delete` = 0 AND `status` = 1 AND `audios_num` > 0)
                    UNION
                    (SELECT `id`,`title`,`sell_type`,`audio_length`,`price`,`original_price`,`sort`,1 AS `type`, NULL,NULL, study_num,pay_type,over_play,putaway,audio_length ,auditions_path,full_path,teacher_id,image,`timestamp`,`timestamp_update` FROM nb_audio WHERE `putaway` >= ".time()." AND  `audio_one_type` = ".$one_category." AND `delete` = 0 AND `STATUS` = 1 AND `pay_type` != 1)";
        }
        $num = isset($condition['num']) ? $condition['num'] : 5;
        $offset = ($page-1)*$num;
//         $sql = $sql ." LIMIT " . $offset .','. $num;
        $sql = $sql. "ORDER BY `type` DESC,`cour_sort` ASC,`timestamp_update` DESC" . " LIMIT " . $offset .','. $num;
        $list = $this->executeSql($sql);
        if($list['list']){
            foreach ($list['list'] as $k => $v){
                if($v['type'] == 1){
                    $teacher = $this->getOne(array('id'=>$v['teacher_ids']),array('name','head_icon'),'teacher');
                    $v['audio_length'] = $this->getShiftTime($v['audio_length']);
                    if(IS_OPEN_HTTPS == 1){
                        preg_match("/^(http).*$/",$v['full_path'],$full_match);
                        preg_match("/^(http).*$/",$v['auditions_path'],$auditions_match);
                        if($full_match){
                            $v['full_path'] = str_replace("http","https", $v['full_path']);
                        }
                        if($auditions_match){
                            $v['auditions_path'] = str_replace("http","https", $v['auditions_path']);
                        }
                    }
                    $watch_record = $this->getOne(array('user_id' => $_SESSION['user_id'],'audio_id' => $v['id']),array('id','time'),'watch_record');
                    if(strpos($v['count'],"时")){
                        $str = preg_replace('/([\d]+)时([\d]+)分([\d]+)秒/', '$1:$2:$3', $v['count']);
                    }else{
                        $str = preg_replace('/([\d]+)分([\d]+)秒/', '00:$1:$2', $v['count']);
                    }
                    $parsed = date_parse($str);
                    $v['count'] = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
                    $v['count'] = $v['count'] ? round($watch_record['time']/$v['count']*100, 0). "%"  : "";
                    $v['time'] = $watch_record ? $watch_record['time'] : 0;
                }else{
//                    $log = new Log('Web');
//                    $log->debug('视频数据：'.var_export($v,true));
                    if($v['audios_num'] > 0){
                        $teahcer_id = explode(',', trim($v['teacher_ids'],','));
                        $audios_ids = explode(',', trim($v['audios_ids'],','));
                        //查看是否有视频数据
                        $audio_where = new where();
                        $audio_where->in('id',$audios_ids);
                        $audio_where->equalTo('status',1);
                        $audio_data = $this->fetchAll($audio_where,array("columns" => array('id')),'audio');
                        if(!$audio_data){
                            unset($list['list'][$k]);
                            continue;
                        }
                        //查询老师信息
                        $teacher = $this->getOne(array('id'=>$teahcer_id['0']),array('name','head_icon'),'teacher');
                        $first_audio = $this->getOne(array('id'=>$audios_ids['0']),array('id','auditions_path','full_path'),'audio');
                        $watch_record = $this->getOne(array('user_id' => $_SESSION['user_id'],'audio_id' => $audios_ids['0']),array('id','time'),'watch_record');
                        $v['time'] = $watch_record ? $watch_record['time'] : 0;
                        if(IS_OPEN_HTTPS == 1){
                            preg_match("/^(http).*$/",$first_audio['full_path'],$full_match);
                            preg_match("/^(http).*$/",$first_audio['auditions_path'],$auditions_match);
                            if($full_match){
                                $first_audio['full_path'] = str_replace("http","https", $first_audio['full_path']);
                            }
                            if($auditions_match){
                                $first_audio['auditions_path'] = str_replace("http","https", $first_audio['auditions_path']);
                            }
                        }
                        
                        $v['auditions_path'] = $first_audio ? $first_audio['auditions_path'] : "";
                        $v['full_path'] = $first_audio ? $first_audio['full_path'] : "";
                        $v['first_audio_id'] = $first_audio ? $first_audio['id'] : 0;
                    }
                }
                if($v['image']){
                    $img = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                }else{
                    $img = $this->getOne(array('id'=>$teacher['head_icon']),array('path','filename'),'image');
                }
                if($v['sell_type'] == 2){
                    $v['price'] = "仅限会员";
                }else{
                    $v['price'] = $v['price'] == '0.00' ? "免费" : $v['price']."元";
                }
                $v['original_price'] = $v['original_price'] ==  '0.00' ? "免费" : $v['original_price']."元";
                
                $v['image_path'] = $img['path'].$img['filename'];
                $v['teacher_name'] = $teacher['name'] ? $teacher['name'] : "";
                $v['putaway'] = date("m-d",strtotime($v['putaway']));
                $v['study_num'] = $this->convertTenThousandNum($v['study_num']);
             
                if($v['type'] == 1)
                {
                    if(in_array($v['id'], $_SESSION['buy_audio_ids']))
                    {
                        $v['full_path'] = $v['full_path'];
                    }
                    else 
                    {
                        if($v['pay_type'] == 3){
                            $v['full_path'] = $v['full_path'];
                        }else{
                            $v['full_path'] = $v['auditions_path'];
                        }

                    }
                }
                else 
                {
                    if(in_array($v['first_audio_id'], $_SESSION['buy_audio_ids']))
                    {
                        $v['full_path'] = $v['full_path'];
                    }
                    else
                    {
                        $v['full_path'] = $v['auditions_path'];
                    }
                }
            }
        }
        $list = $list['list'] ? array_values($list['list']) : array();
        $data = array('code'=>200,'list'=>$list );
        return $data;
    }
    
    //清除订阅消息
    public function deleteSubscibeNew($pid,$id){
        if($pid){
            $data = $this->getOne(array('id' => $pid,'audio_id'=> $id,'is_new' =>2),array('*'),'notification_subscibe');
            if($data){
                $this->updateData(array('is_new' => 1),array('id' => $data['id']),'notification_subscibe');
            }
        }
    }
    
    public function details($id,$type,$one_audio_id = 0)
    {
        $where = new where();
//         $where->equalTo('delete', 0);
//         $where->equalTo('status', 1);
        $where->equalTo('id', $id);
        $where_comment = array();
        $teacher_id = 0;
        $current_audio_id = 0;
        $type = $type == 1 ? 1 : 3;
        $collect = $this->getOne(array('delete' => 0,'type' => $type,'user_id' => $_SESSION['user_id'],'audio_id' => $id),array('*'),'collect_log');
        $audio_ids = array();
        $user = $this->getOne(array('id' => $_SESSION['user_id']),array('id','amount'),'user');
        if($type == 1) //单个课程
        {
            if(isset($_GET['code']) &&  $_GET['code'] == 'qrCode'){
                $_SESSION['is_perfect'] = 1;
            }else{
                $where->equalTo('delete', 0);
                $where->equalTo('status',1);
            }
            $data = $this->getOne($where,array('*'),'audio');
            if($data)
            {
                $one_audio_id = $id;
                $category = $this->getOne(array('id' => $data['audio_one_type']),array('name'),'category');
                $teacher = $this->getOne(array('id' => $data['teacher_id']),array('name','head_icon','title','details_img'),'teacher');
                $audio_img = $this->getOne(array('id'=>$data['image']),array('path','filename'),'image');
                $details_img = $this->getOne(array('id'=>$data['details_image']),array('path','filename'),'image');
                $courses = $this->getOne(array('id' => $data['courses_id']),array('id','price','original_price','sell_type'),'courses');
                $teacher_img = $this->getOne(array('id'=>$teacher['details_img']),array('path','filename'),'image');
                $data['category_name'] = $category['name'] ? $category['name'] : "";
                $data['teacher_name'] = $teacher['name'] ? $teacher['name'] : "";;
                $data['image'] = $audio_img ? $audio_img['path'].$audio_img['filename'] : $teacher_img['path'].$teacher_img['filename'];
                $data['details_image'] = $details_img ? $details_img['path'].$details_img['filename'] : '';
                $data['teacher_img'] = $teacher_img ? $teacher_img['path'].$teacher_img['filename'] : "";
                $data['teacher_title'] = $teacher['title'];
                $watchRecord = $this->getOne(array('user_id' => $_SESSION['user_id'],'audio_id' => $id),array('time'),'watch_record');
                $data['time'] = $watchRecord['time'] ? $watchRecord['time'] : 0;
                $data['courses_price'] = $courses['price'];
                $data['courses_original_price'] = $courses['original_price'];
                $data['courses_id'] = $courses['id'];
                $data['courses_sell_type'] = $courses['sell_type'];     
                $data['study_num'] = $this->convertTenThousandNum($data['study_num']);         
                if($data['courses_id'])
                {
                    $courses_info = $this->getOne(array('id'=>$data['courses_id']),array('audios_ids'),'courses');
                    $audio_ids = explode(",", trim($courses_info['audios_ids'],','));
                }
                if(IS_OPEN_HTTPS == 1){
                    preg_match("/^(http).*$/",$data['full_path'],$full_match);
                    preg_match("/^(http).*$/",$data['auditions_path'],$auditions_match);
                    if($full_match){
                        $data['full_path'] = str_replace("http","https", $data['full_path']);
                    }
                    if($auditions_match){
                        $data['auditions_path'] = str_replace("http","https", $data['auditions_path']);
                    }
                }
                if(!in_array($id, $_SESSION['buy_audio_ids']) && $data['pay_type'] != 3)
                {  
                    if($data['original_price'] == "0.00" && $_SESSION['is_vip'] == 1){
                        $data['full_path'] = $data['full_path'];
                        $data['is_auditions'] = 2;
                        $data['audio_length'] = $this->convertTimeTo2($data['auditions_length']);
                    }else{
                        $data['full_path'] = $data['auditions_path'];
                        $data['is_auditions'] = 1;
                        $data['audio_length'] = $this->convertTimeTo2($data['audio_length']);
                    }
                }
                else
                {
                    $data['is_auditions'] = 2;
                    $data['audio_length'] = $this->convertTimeTo2($data['auditions_length']);
                }           
                $teacher_id = $data['teacher_id'];
                $current_audio_id = $data['id'];
                $str_length = mb_strlen(strip_tags($data['audio_synopsis']),'UTF8'); 
                if($str_length > 20){ 
                    $data['prise'] = mb_substr(strip_tags($data['audio_synopsis']),0,20,'utf-8').'...';
                }else{
                    $data['prise'] = mb_substr(strip_tags($data['audio_synopsis']),0,20,'utf-8');
                } 
            }else{
                return array(
                    'code' => 400,
                );
            }
//             var_dump($data);exit;
        }
        else //课程包
        {
            $data = $this->getOne($where,array('*'),'courses');
            
            if($data)
            {
                $audio_ids = explode(",", trim($data['audios_ids'],','));
                $one_audio_id = $one_audio_id ? $one_audio_id : $audio_ids[0];
                $category = $this->getOne(array('id' => $data['courses_one_type']),array('name'),'category');
                $one_audio = $this->getOne(array('id'=>$one_audio_id),array('*'),'audio');
                $teacher = $this->getOne(array('id' => $one_audio['teacher_id']),array('name','head_icon','title'),'teacher');
                $course_img = $this->getOne(array('id'=>$data['details_image']),array('path','filename'),'image');
                $teacher_img = $this->getOne(array('id'=>$teacher['head_icon']),array('path','filename'),'image');
                $watchRecord = $this->getOne(array('user_id' => $_SESSION['user_id'],'audio_id' => $one_audio_id),array('time'),'watch_record');
                $data['time'] = $watchRecord['time'] ? $watchRecord['time'] : 0;
                $data['full_path'] = $one_audio['full_path'];
                $data['auditions_path'] = $one_audio['auditions_path'];
                if(!in_array($one_audio_id, $_SESSION['buy_audio_ids']) && $one_audio['pay_type'] != 3)
                {
                    $data['full_path'] = $data['auditions_path'] = $one_audio['auditions_path'];
                    $data['is_auditions'] = 1;
                    $data['audio_length'] = $this->convertTimeTo2($one_audio['audio_length']);
                }
                else
                {
                    $data['is_auditions'] = 2;
                    $data['audio_length'] = $this->convertTimeTo2($one_audio['auditions_length']);
                }
                $data['study_num'] = $this->convertTenThousandNum($data['study_num']);
                $data['category_name'] = $category['name'] ? $category['name'] : "";
                $data['teacher_name'] = $teacher['name'] ? $teacher['name'] : "";;
                $data['details_image'] = $course_img ? $course_img['path'].$course_img['filename'] : "";
                $data['teacher_img'] = $teacher_img ? $teacher_img['path'].$teacher_img['filename'] : "";
                $data['teacher_title'] = $teacher['title'];
                $data['audio_synopsis'] = $one_audio['audio_synopsis'];
                $data['outline'] = $one_audio['outline'];
                $data['auditions_length'] = $one_audio['auditions_length'];
                $teacher_id = $one_audio['teacher_id'];
                $data['pay_type'] = $one_audio['pay_type'];
                $current_audio_id = $one_audio_id;

                $where = new Where();
                $where->equalTo('delete', 0)->equalTo('status', 1)->equalTo('courses_two_type', $data['courses_two_type']);
                $where->notEqualTo('id', $id)->equalTo('type', 3);
                $courses = $this->getAll($where,array('columns'=>array('id','title','image','study_num','audios_num'),'limit'=>2),null,2,'courses');
                if($courses['total'])
                {
                    foreach ($courses['list'] as $v)
                    {
                        $image = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                        $v['image'] = $image ? ROOT_PATH.UPLOAD_PATH.$image['path'].$image['filename'] : "";
                        
                    }
                }
                $data['remmend_courses'] = $courses['list'];
                
                $audio_ids = array_filter(explode(',', $data['audios_ids']));
                $data['equality'] = 1;
                foreach ($audio_ids as $v){
                    $video_data = $this->getOne(array('id' => $v,'status' => 1),array('*'),'audio');
                    if($video_data){
                        if(!in_array($v, $_SESSION['buy_audio_ids'])){
                            $data['equality'] = 2;
                        }
                    }

                }
                $str_length = mb_strlen(strip_tags($data['audio_synopsis']),'UTF8');
                if($str_length > 45){
                    $data['prise'] = mb_substr(strip_tags($data['audio_synopsis']),0,45,'utf-8').'...';
                }else{
                    $data['prise'] = mb_substr(strip_tags($data['audio_synopsis']),0,45,'utf-8');
                }
            }
        }
       
        if($audio_ids)
        {
            
            $audio_all = $this->fetchAll(array('id'=>$audio_ids,'status' => 1),array('*'),'audio');
            $audio_all_unpack = $this->fetchAll(array('id'=>$audio_ids),array('*'),'audio');
            $audio_other = array();
            $audio_other_unpack = array();
            if($audio_all || $audio_all_unpack)
            {
               foreach ($audio_all as $v)
                {
//                     if($v['id'] != $one_audio_id)
//                     {
                        $item = array(
                            'id' => $v['id'],
                            'title' => $v['title'],
                            'putaway' => date('m-d',strtotime($v['putaway'])),
                            'audio_length' => $v['audio_length'],
                            'study_num' => $v['study_num']
                        );
                        $other_teacher_info = $this->getOne(array('id' => $v['teacher_id']),array('name'),'teacher');
                        $item['audio_length'] = $this->getShiftTime($item['audio_length']);
                        $other_audio_img = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                        $item['image'] = $other_audio_img ? $other_audio_img['path'].$other_audio_img['filename'] : "";
                        $item['teacher_name'] = $other_teacher_info ? $other_teacher_info['name'] : "";
                        $item['study_num'] = $this->convertTenThousandNum($item['study_num']);
                        $audio_other[] = $item;
                       
//                     }
                } 
                
                foreach ($audio_all_unpack as $v)
                {                 
                    $item = array(
                        'id' => $v['id'],
                        'title' => $v['title'],
                        'putaway' => date('m-d',strtotime($v['putaway'])),
                        'audio_length' => $v['audio_length'],
                        'study_num' => $v['study_num']
                    );
                    $other_teacher_info = $this->getOne(array('id' => $v['teacher_id']),array('name'),'teacher');
                    $other_audio_img = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                    $item['audio_length'] = $this->getShiftTime($item['audio_length']);
                    $item['image'] = $other_audio_img ? $other_audio_img['path'].$other_audio_img['filename'] : "";
                    $item['teacher_name'] = $other_teacher_info ? $other_teacher_info['name'] : "";
                    $item['study_num'] = $this->convertTenThousandNum($item['study_num']);
                    $audio_other_unpack[] = $item;
                }
            }
            $data['audio_other'] = $audio_other;
            $data['audio_all_unpack'] = $audio_other_unpack;
        }
        else 
        {
            $data['audio_other'] = array();
        }

        if($teacher_id)
        {
            //查询该老师的上，下一个课程
            $next_audio_id = $prev_audio_id = 0;
            $where = new Where();
            $where->equalTo('teacher_id', $teacher_id)->equalTo('type', 1)->equalTo('delete', 0)->equalTo('status', 1);
            $last_audio = $this->getOne($where,array('id'),'audio',array('id DESC'));
            if($last_audio)
            {
                $fist_audio  = $this->getOne($where,array('id'),'audio',array('id ASC'));
                if($fist_audio->id == $last_audio->id)//只有一条记录
                {
                    $next_audio_id = $prev_audio_id = $fist_audio->id;
                }
                else 
                {
                    if(($last_audio->id != $current_audio_id) || ($fist_audio->id != $current_audio_id))
                    {
                        $where->greaterThan('id', $current_audio_id);
                        $next_audio = $this->getOne($where,array('id'),'audio',array('id ASC'));//下一条记录
                        $next_audio_id = $next_audio ? $next_audio->id : 0;
                        
                        $where = new Where();
                        $where->equalTo('teacher_id', $teacher_id)->equalTo('type', 1)->equalTo('delete', 0)->equalTo('status', 1);
                        $where->lessThan('id', $current_audio_id);
                        $prev_audio = $this->getOne($where,array('id'),'audio',array('id DESC'));//上一条记录
                        $prev_audio_id = $prev_audio ? $prev_audio->id : 0;
                    }
                    if($last_audio->id == $current_audio_id)//如果最后一条记录就是当前记录
                    {
                        $next_audio_id = $fist_audio->id;
                    }
                    if($fist_audio->id == $current_audio_id)//如果第一条记录就是当前记录
                    {
                        $prev_audio_id = $last_audio->id;
                    }
                }
            }
            $data['prev_audio_id'] = $prev_audio_id;
            $data['next_audio_id'] = $next_audio_id;
        }
        $data['collect'] = $collect ? 1 : 2;
        $data['current_audio_id'] = $current_audio_id;
        $member = $this->getOne(array('type' => 1),array('price'),'member_set');
        $data['vip_price'] = $member['price'];
        $data['amount'] = $user['amount'];
//         var_dump($data);exit;
        if(!$data){
            return array(
                'id' => 400,
                'message' => '该数据不存在！'
            );
        }else{
            return array(
                'code' => 200,
                'data' => $data
            );
        }
        return $data;
    }
    
    //获取评论列表
    public function ajaxgetCommentList($id,$type,$page){
        $data = array(
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'user',
                    'on' => DB_PREFIX . 'user.id = ' . DB_PREFIX . 'comment.user_id',
                    'columns' => array(
                        'user_name' => "name",
                        'user_img' => "img_id",
                        'user_head_icon' => "head_icon",
                    ),
                    'type'=>'left'
                ),
                array(
                    'name' => DB_PREFIX . 'image',
                    'on' => DB_PREFIX . 'user.img_id = ' . DB_PREFIX . 'image.id',
                    'columns' => array(
                        'img_id' => "id",
                        'img_path' => "path",
                        'img_filename' => "filename",
                    ),
                    'type'=>'left'
                ),
            ),
            'order' => array(
                DB_PREFIX.'comment.is_top' => 'ASC',
                DB_PREFIX.'comment.id' => 'DESC',
            ),
            'columns' => array(
                'id' => 'id',
                'audio_id' => "audio_id",
                'user_id' => "user_id",
                'content' => "content",
                'is_top' => "is_top",
                'user_type' => 'user_type',
                'timestamp' => 'timestamp'
            ),
            'need_page' => true,
            'page' => $page
        );
        $where = new Where();
        if($type == 1)
        {
            $where->equalTo('audio_id', $id);
        }
        else 
        {
            $where->equalTo('courses_id', $id);
        }
        $where->equalTo(DB_PREFIX.'comment.delete', "0");
        $where->equalTo(DB_PREFIX.'comment.deep', 1);
        $comment =  $this->getAll($where,$data,$page,PAGE_NUMBER,'comment');
        if($comment['list']){
            foreach ($comment['list'] as $v){
                if($v['img_id']){
                    $v['user_head_icon'] = ROOT_PATH.UPLOAD_PATH.$v['img_path'].$v['img_filename'];
                }
                $praise = $this->getOne(array('comment_id' => $v['id'],'user_id' => $_SESSION['user_id'],'delete' => 0),array('*'),'praise');
                $v['praise'] =  $praise ? 1 : 0;
                unset($data['page'],$data['join'],$data['order']);
                $data['need_page'] = false;
                $son_com = $this->getAll(array('parent_id' =>$v['id'],DB_PREFIX.'comment.delete' => 0,'deep'=>2),$data,null,null,'comment');
                if($son_com['total'])
                {
                    foreach ($son_com['list'] as $m)
                    {
                        if($m['user_type'] == 1)//用户
                        {
                           $user = $this->getOne(array('id'=>$m['user_id']),array('user_name' => "name",'user_img' => "img_id",'user_head_icon' => "head_icon",'img_path'),'user'); 
                           $m['user_head_icon'] = $user && $user['img_path'] ? ROOT_PATH.UPLOAD_PATH.$user['img_path'] : $user['user_head_icon'];
                           $m['user_name'] = $user ? $user['user_name'] : "";
                           
                        }
                        else //管理员
                        {
                            $admin = $this->getOne(array('id'=>$m['user_id']),array('name','image'),'admin');
                            if($admin)
                            {
                                //$image = $this->getOne(array('id'=>$admin['image']),array('id','path','filename'),'image');
                                $m['user_head_icon'] = $admin['image'] ? ROOT_PATH.UPLOAD_PATH.$admin['image'] : "";
                            }
                            else 
                            {
                                $m['user_head_icon'] = "";
                            }
                            $m['user_name'] = $admin ? $admin['name'] : "";
                        }
                        $praise = $this->getOne(array('comment_id' => $m['id'],'user_id' => $_SESSION['user_id'],'delete' => 0),array('*'),'praise');
                        $m['praise'] =  $praise ? 1 : 0;
                    }
                    $v['son_comment'] = $son_com['list'];
                }
                else 
                {
                    $v['son_comment'] = array();
                }
            }
        }
        $comment_list = $comment['total'] > 0 ? $comment['list'] : array();
        return array('code'=>200,'list'=>$comment_list);
    }
    
    //提交评论
    public function commentSubmit($audio_id,$user_id,$audio_type,$content){
        //查找是否有敏感词
        $word = $this->getCache('SensitiveWords/words');
        $str = "";
        if($word){
            foreach ($word as $k => $v)
            {
                if($str){
                    $str = str_replace($k,"***",$str);
                }else{
                    $str = str_replace($k,"***",$content);
                }
            }
        }
        $data = array(
            'audio_id' => $audio_id,
            'user_id' => $user_id,
            'courses_id' => 0,
            'is_top' => 2,
//             'content' => $content,
            'content' => $str ? $str : $content,
            'old_content' => $content,
            'deep' => 1,
            'parent_id' => 0,
            'user_type' => 1,
            'type' => $audio_type == 1 ? 1 : 3,
            'delete' => 0,
            'timestamp' => $this->getTime(),
        );
        if($audio_type == 1)
        {
            $data['audio_id'] = $audio_id;
            $audio_info = $this->getOne(array('id'=>$audio_id),array('courses_id'),'audio');
            $data['courses_id'] = $audio_info ? $audio_info['courses_id'] : 0;
        }
        else 
        {
            $data['audio_id'] = 0;
            $data['courses_id'] = $audio_id;
        }
        
        $row = $this->insertData($data,'comment');
        $user = $this->getOne(array('id' => $user_id),array('name','head_icon','img_id'),'user');
        $user['comment_id'] = $row;
        if($user['img_id']){
            $img = $this->getOne(array('id' => $user['img_id']),array('path','path','filename'),'image');
            $user['head_icon'] = ROOT_PATH.UPLOAD_PATH.$img['path'] . $img['filename'];
        }else{
            $user['head_icon'] = $user['head_icon'];
        }
        $user['time'] = $data['timestamp'];
        if($row){
            if($audio_type == 1)
            {
                $this->updateKey($audio_id, 1, 'comment_num', 1,'audio');
            }
            return array(
                'code' => 200,
                'message' => '评论成功',
                'user' => $user,
            );
        }else{
            return array(
                'code' => 400,
                'message' => '评论失败'
            );
        }
    }
    
    public function ajaxgetPraiseList(){
        $data = $this->getOne(array('comment_id' => $_POST['parId'],'user_id' => $_POST['user_id'],'delete' => 0),array('*'),'praise');
        if(!$data){
            $this->insertData(array(
                'comment_id' => $_POST['parId'],
                'user_id' => $_POST['user_id'],
                'timestamp' => $this->getTime(),
            ),'praise');
            $this->updateKey($_POST['parId'], 1, 'praise_num', 1,'comment');
        }else{
            $this->deleteData(array('id' => $data['id']),'praise',true);
            $this->updateKey($_POST['parId'], 2, 'praise_num', 1,'comment');
        }
    }
    
//回复评论
    public function ajaxReplyComment(){
        $comment_id = $_POST['id'] ? $_POST['id'] : 0;
        $user_id = $_POST['user_id'] ? $_POST['user_id'] : 0;
        $content = $_POST['content'] ? $_POST['content'] : '';
        $audio_type = $_POST['audio_type'] ? $_POST['audio_type'] : 0;
//         var_dump($_POST);exit;
        if(!trim($content))
        {
            return array('code'=>400,'message'=>'内容不能为空');
        }
        $comment = $this->getOne(array('id' => $comment_id,'delete' => 0),array('*'),'comment');
        if(!$comment){
            return array(
                'code' => 400,
                'message' => '评论失败'
            );
        }
        //查找是否有敏感词
        $word = $this->getCache('SensitiveWords/words');
        $str = "";
        if($word){
            foreach ($word as $k => $v)
            {
                if($str){
                    $str = str_replace($k,"***",$str);
                }else{
                    $str = str_replace($k,"***",$content);
                }
            }
        }
        $data = array(
            'audio_id' => $comment['audio_id'],
            'user_id' => $user_id,
            'is_top' => 2,
//             'content' => $content,
            'content' => $str,
            'old_content' => $content,
            'courses_id' => $comment['courses_id'],
            'deep' => 2,
            'parent_id' => $comment_id,
            'user_type' => 1,
            'delete' => 0,
            'type' => $comment['type'],
            'timestamp' => $this->getTime(),
        );
        if($comment['type'] == 3){
            $data['audio_id'] = 0;
        }
        $row = $this->insertData($data,'comment');
        $this->updateKey($comment['audio_id'], 1, 'comment_num', 1,'audio');
        $this->updateKey($comment['id'], 1, 'comment_num', 1,'comment');
        $user = $this->getOne(array('id' => $user_id),array('id','name','head_icon','img_id'),'user');
        $user['comment_id'] = $row;
        if($user['img_id']){
            $img = $this->getOne(array('id' => $user['img_id']),array('path','path','filename'),'image');
            $user['head_icon'] = ROOT_PATH.UPLOAD_PATH.$img['path'] . $img['filename'];
        }else{
            $user['head_icon'] = $user['head_icon'];
        }
        $user['time'] = $data['timestamp'];
        if($row){
            return array(
                'code' => 200,
                'message' => '评论成功',
                'user' => $user,
                'str' => $str,
            );
        }else{
            return array(
                'code' => 400,
                'message' => '评论失败'
            );
        }
    }
    
    
    public function ajaxDeleteAudioComment(){
        $data = $this->getOne(array('id' => $_POST['id'],'delete' => 0),array('*'),'comment');
        if($data){
            $total = 1;
            if($data['deep'] == 1 && $data['parent_id'] == 0){
                $comment_all = $this->getAll(array('delete' => 0,'parent_id' => $_POST['id']),array("columns" => array('id')),null,null,'comment');
                $total += $comment_all['total'];
                foreach ($comment_all['list'] as $v){
                    $this->deleteData(array('id' => $v['id']),'comment',true);
                    $this->deleteData(array('comment_id' => $v['id']),'praise',true);
                }
            }
            $this->deleteData(array('id' => $data['id']),'comment',true);
            $this->deleteData(array('comment_id' => $data['id']),'praise',true);
            if($_POST['audio_type'] == 1)
            {
                $this->updateKey($data['audio_id'], 2, 'comment_num', $total,'audio');
            }
            $this->updateKey($data['parent_id'], 2, 'comment_num', 1,'comment');
        }
    }
    
    //增加学习人数
    public function ajaxAddStudyNum(){
        $audio_id = $_POST['audio_id'] ?  $_POST['audio_id'] : 0;
        $type = isset($_POST['type']) ? $_POST['type'] : 2;
        if($audio_id){
            $audio = $this->getOne(array('delete' => 0,'status' => 1,'id' => $audio_id),array('id','courses_id','teacher_id'),'audio');
            if($audio){
                if($type == 1){
                    $this->updateKey($audio_id, 1, 'over_play', 1,'audio');
                    return array(
                        'code' => 200,
                        'message' => '新增完播量成功',
                    );
                }
                $this->updateKey($audio_id, 1, 'study_num', 1,'audio');
                $this->updateKey($audio['teacher_id'], 1, 'play_num', 1,'teacher');
                $courses = $this->getOne(array('delete' => 0,'status' => 1,'id' => $audio['courses_id']),array('id','study_num'),'courses');
                $_SESSION['study_num'] = 0;
                if($courses){
                    $this->updateKey($courses['id'], 1, 'study_num', 1,'courses');
                }
                return array(
                    'code' => 200,
                    'message' => '新增播放量成功',
                    'study_num' =>$_SESSION['study_num'],
                );
            }else{
                return array(
                    'code' => '400',
                    'message' => '新增播放量失败'
                );
            }
        }else{
            return array(
                'code' => '400',
                'message' => '新增播放量失败'
            );
        }
    }
}