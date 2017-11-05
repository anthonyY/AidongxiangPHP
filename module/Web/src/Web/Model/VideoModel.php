<?php
namespace Web\Model;

use Zend\Db\Sql\Where;

class VideoModel extends CommonModel
{

    protected $table = '';

    public function getIndex($type = 1)
    {
        $category = $this->fetchAll(array(
            'status' => 1,
            'delete' => 0,
            'deep' => 1,
            'parent_id' => 0,
            'type' => $type
        ), array(
            "columns" => array(
                'id',
                'name'
            ),
            "order" => array(
                'sort' => 'ASC'
            )
        ), 'category');
        if($category){
            return array(
                'code' => 200,
                'category' => $category,
            );
        }
        return array(
            'code' => 400,
            'message' => '没有数据/数据删除'
        );
    }
    //获取子分类
    public function getSonCategory(){
        $id = $_POST['id'] ? $_POST['id'] : 0;
        if(!$id){
           return array(); 
        }
        $category = $this->fetchAll(array(
            'status' => 1,
            'delete' => 0,
            'deep' => 2,
            'parent_id' => $id,
        ), array(
            "columns" => array(
                'id',
                'name'
            ),
            "order" => array(
                'sort' => 'ASC'
            )
        ), 'category');
        return $category;
    }
    
    //获取视频数据
    public function ajaxgetDataList(){
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $one_id = $_POST['oid'] ? $_POST['oid'] : 0;
        $two_id = $_POST['tid'] ? $_POST['tid'] : 0;
        $where = new Where();
        
        if($two_id){
            $sql = "(SELECT `id` ,`title`,`sell_type`,NULL AS count,`price`,`original_price`,`cour_sort`,2 AS `type`,`audios_num` ,study_num, NULL AS audio_length ,teacher_ids,image,`timestamp`,`timestamp_update`,`audios_ids` FROM nb_courses WHERE  `courses_two_type` = ".$two_id." AND `delete` = 0 AND `status` = 1  AND `audios_num` > 0)
                    UNION
                    (SELECT `id`,`title`,`sell_type`,`audio_length`,`price`,`original_price`,`sort`,1 AS `type`, NULL, study_num,audio_length ,teacher_id,image,`timestamp`,`timestamp_update`,NULL FROM nb_audio WHERE `putaway` >= ".time()." AND  `audio_two_type` = ".$two_id." AND `delete` = 0 AND `STATUS` = 1 AND `pay_type` != 1)";
        }else{
            $sql = "(SELECT `id` ,`title`,`sell_type`,NULL AS count,`price`,`original_price`,`cour_sort`,2 AS `type`,`audios_num` ,study_num, NULL AS audio_length ,teacher_ids,image,`timestamp`,`timestamp_update`,`audios_ids` FROM nb_courses WHERE `courses_one_type` = ".$one_id." AND `delete` = 0 AND `status` = 1 AND `audios_num` > 0)
                    UNION
                    (SELECT `id`,`title`,`sell_type`,`audio_length`,`price`,`original_price`,`sort`,1 AS `type`, NULL, study_num,audio_length ,teacher_id,image,`timestamp`,`timestamp_update` ,NULL FROM nb_audio WHERE `putaway` >= ".time()." AND `audio_one_type` = ".$one_id." AND `delete` = 0 AND `STATUS` = 1 AND `pay_type` != 1)";
        }
       
        $num = 5;
        $offset = ($page-1)*$num; 
        $sql = $sql. "ORDER BY `type` DESC,`cour_sort` ASC,`timestamp_update` DESC" . " LIMIT " . $offset .','. $num;
//         echo $sql;exit;
        $list = $this->executeSql($sql);
       
        if($list['list']){
            foreach ($list['list'] as $k=>$v){
                if($v['type'] == 1){
                    $teacher = $this->getOne(array('id'=>$v['teacher_ids']),array('name','head_icon'),'teacher');
                    $v['audio_length'] = $this->getShiftTime($v['audio_length']);
                    $watch_record = $this->getOne(array('user_id' => $_SESSION['user_id'],'audio_id' => $v['id']),array('id','time'),'watch_record');
                    if(strpos($v['count'],"时")){
                        $str = preg_replace('/([\d]+)时([\d]+)分([\d]+)秒/', '$1:$2:$3', $v['count']);
                    }else{
                        $str = preg_replace('/([\d]+)分([\d]+)秒/', '00:$1:$2', $v['count']);
                    }
                    $parsed = date_parse($str);
                    $v['count'] = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
                    $v['count'] = $v['count'] ? round($watch_record['time']/$v['count']*100, 0). "%"  : "";
                }else{
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
                        $teacher = $this->getOne(array('id'=>$teahcer_id['0']),array('name','head_icon'),'teacher');
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
                $v['teacher_name'] = $teacher['name'];
                $v['study_num'] = $this->convertTenThousandNum($v['study_num']);
            }
        }
        return $list['list'] ? array_values($list['list']) : array();
    }
    
    //获取视频详情
    public function getDetails($type,$id){
        if(!$id){
            if(!isset($_REQUEST['id'])){
                return array(
                    'code' => 400,
                    'message' => '该数据不存在！'
                );
            }
        }
        $where = new where();
//         $where->equalTo('delete', 0);
//         $where->equalTo('status', 1);
        
        if(!$id){
            $id = $_REQUEST['id'];
            $where->equalTo('id', $id);
        }else{
            $where->equalTo('id', $id);
        }
        if($type == 1){
           $collect = $this->getOne(array('delete' => 0,'type' => 2,'user_id' => $_SESSION['user_id'],'audio_id' => $id),array('*'),'collect_log');
        }else if($type == 2){
            $collect = $this->getOne(array('delete' => 0,'type' => 4,'user_id' => $_SESSION['user_id'],'audio_id' => $id),array('*'),'collect_log');
        }
        $user = $this->getOne(array('id' => $_SESSION['user_id']),array('id','amount'),'user');
        $data = array();
        if($type == 1){
            if(isset($_GET['code']) &&  $_GET['code'] == 'qrCode'){
                $_SESSION['is_perfect'] = 1;
            }else{
                $where->equalTo('delete', 0);
                $buy_log = $this->getOne(array('audio_id'=> $id,'user_id' => $_SESSION['user_id'] ),array('id'),'buy_log');
                if(!$buy_log){
                    $where->equalTo('status',1);
                }

            }

            $data = $this->getOne($where,array('*'),'audio');
//
            if(!$data){
                return array(
                    'code' => 400,
                    'message' => '该数据不存在！'
                );
            }
            $category = $this->getOne(array('id' => $data['audio_one_type']),array('name'),'category');
            $teacher = $this->getOne(array('id' => $data['teacher_id']),array('name','head_icon','title','details_img'),'teacher');
            $teacher_img = $this->getOne(array('id'=>$teacher['details_img']),array('path','filename'),'image');
            $courses = $this->getOne(array('id' => $data['courses_id']),array('id','price','original_price','sell_type'),'courses');
            $img = $this->getOne(array('id'=>$data['details_image']),array('path','filename'),'image');
            $data['details_image'] = $img['path'] . $img['filename'];
            $data['category_name'] = $category['name'];
            $data['teacher_name'] = $teacher['name'];
            $data['teacher_img'] = $teacher_img['path'].$teacher_img['filename'];
            $data['teacher_title'] = $teacher['title'];
            $watchRecord = $this->getOne(array('user_id' => $_SESSION['user_id'],'audio_id' => $id),array('time'),'watch_record');
            $data['time'] = $watchRecord['time'] ? $watchRecord['time'] : 0;
            $data['courses_price'] = $courses['price'];
            $data['courses_original_price'] = $courses['original_price'];
            $data['courses_id'] = $courses['id'];
            $data['courses_sell_type'] = $courses['sell_type'];
            $data['study_num'] = $this->convertTenThousandNum($data['study_num']);
            $courses = $this->fetchAll(array(
                'type' => $data['type'],
                'courses_id' => $data['courses_id'],
                'delete' => 0,
                'status' => 1
            ), array(
                "columns" => array(
                    'id',
                    'title',
                    'teacher_id',
                    'courses_id',
                    'image',
                    'audio_length',
                    'study_num',
                )
            ), 'audio');  
            $courses_unpack = $this->fetchAll(array(
                'type' => $data['type'],
                'courses_id' => $data['courses_id'],
                'delete' => 0,
                'status' => 1
            ), array(
                "columns" => array(
                    'id',
                    'title',
                    'teacher_id',
                    'courses_id',
                    'image',
                    'audio_length',
                    'study_num',
                )
            ), 'audio');
            if(strpos($data['audio_length'],"时")){
                $str = preg_replace('/([\d]+)时([\d]+)分([\d]+)秒/', '$1:$2:$3', $data['audio_length']);
            }else if(strpos($data['audio_length'],"分")){
                $str = preg_replace('/([\d]+)分([\d]+)秒/', '00:$1:$2', $data['audio_length']);
            }else if(strpos($data['audio_length'],"秒")){
                $str = preg_replace('/([\d]+)秒/', '00:00:$1', $data['audio_length']);
            }
            $parsed = date_parse($str);
            $data['seconds'] = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
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
            if(in_array($id, $_SESSION['buy_audio_ids']))
            {
                $data['play_path'] = $data['full_path'];
                $data['is_auditions'] = 1;
            }
            else
            {
                if($data['original_price'] == "0.00" && $_SESSION['is_vip'] == 1){
                    $data['play_path'] = $data['full_path'];
                }else if($data['pay_type'] == 3){
                    $data['play_path'] = $data['full_path'];
                    $data['is_auditions'] = 1;
                }else{
                    $data['play_path'] = $data['auditions_path'];
                }
                $data['is_auditions'] = 2;
            }      
            foreach ($courses as $k => $v){
                if($v['id'] != $data['id']){
                    $order_audio_img = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');                    
                    $v['img'] = ROOT_PATH.UPLOAD_PATH.$order_audio_img['path'] . $order_audio_img['filename'];
                    $order_teacher = $this->getOne(array('id'=>$v['teacher_id']),array('id','name'),'teacher');
                    $v['tacher_name'] = $order_teacher['name'];
                    $v['audio_length'] = $this->getShiftTime($v['audio_length']);
                }else{
                    unset($courses[$k]);
                }
            }
            foreach ($courses_unpack as $k=>$v){
                $order_audio_img = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                $v['img'] = ROOT_PATH.UPLOAD_PATH.$order_audio_img['path'] . $order_audio_img['filename'];
                $order_teacher = $this->getOne(array('id'=>$v['teacher_id']),array('id','name'),'teacher');
                $v['tacher_name'] = $order_teacher['name'];
                $v['audio_length'] = $this->getShiftTime($v['audio_length']);
            }
            $data['order'] = $courses;
            $data['order_unpack'] = $courses_unpack;
            $str_length = mb_strlen(strip_tags($data['audio_synopsis']),'UTF8');
            if($str_length > 20){ 
                $data['prise'] = mb_substr(strip_tags($data['audio_synopsis']),0,20,'utf-8').'...';
            }else{
                $data['prise'] = mb_substr(strip_tags($data['audio_synopsis']),0,20,'utf-8'); 
            }
            $cour_img = $this->getImagePath($data['image']);
            $data['cour_img'] = $cour_img['path'];
            
        }else{
            $data = $this->getOne($where,array('*'),'courses');
            $img = $this->getOne(array('id'=>$data['details_image']),array('path','filename'),'image');
            $data['details_image'] = $img['path'] . $img['filename'];
            $audio_ids = array_filter(explode(',',$data['audios_ids']));
            $teacher_ids = array_filter(explode(',',$data['teacher_ids']));
            $data['category'] = $this->getOne(array('id' => $data['courses_one_type']),array('name'),'category');
            for($i=1;$i<=count($audio_ids);$i++){
                $data['order_audio'][$i] = $this->getOne(array('id' => $audio_ids[$i],'status' => 1),array('id','title','study_num','image','auditions_path','full_path','audio_length'),'audio');
                if(!$data['order_audio'][$i]){
                    unset($data['order_audio'][$i]);
                    continue;
                }
                $img = $this->getOne(array('id' => $data['order_audio'][$i]['image']),array('path','filename'),'image');
                $data['order_audio'][$i]['img_path'] = ROOT_PATH.UPLOAD_PATH.$img['path'] . $img['filename'];
                $data['order_audio'][$i]['teacher'] = $this->getOne(array('id' => $teacher_ids[$i]),array('id','name'),'teacher');
                $data['order_audio'][$i]['audio_length'] = $this->getShiftTime($data['order_audio'][$i]['audio_length']);
                $data['order_audio'][$i]['study_num'] = $this->convertTenThousandNum($data['order_audio'][$i]['study_num']);
            }
           
            $array = new Where();
            $array->equalTo('courses_two_type', $data['courses_two_type']);
            $array->greaterThan('audios_num', 0);
//             $array->greaterThan('audios_num', 0);
            $array->notEqualTo('id', $data['id']);
            $array->equalTo('delete', 0);
            $array->equalTo('status', 1);
            $alike_category = $this->fetchAll($array,array("columns" => array('id','title','audios_num','study_num','image','teacher_ids'),"limit" => 2),'courses');
            foreach ($alike_category as $v){
                $teacher_ids = array_filter(explode(',',$v['teacher_ids']));
                $v['teacher_name'] = $this->getOne(array('id' => $teacher_ids[1]),array('name'),'teacher');
                $img = $this->getOne(array('id' => $v['image']),array('path','filename'),'image');
                $v['img_path'] = ROOT_PATH.UPLOAD_PATH.$img['path'] . $img['filename'];
                $v['study_num'] = $this->convertTenThousandNum($v['study_num']);
            }
            $data['study_num'] = $this->convertTenThousandNum($data['study_num']);
            $data['alike_category'] = $alike_category;
            if(isset($data['order_audio']) && $data['order_audio']){
                if(IS_OPEN_HTTPS == 1){
                    preg_match("/^(http).*$/",$data['order_audio'][1]['full_path'],$full_match);
                    preg_match("/^(http).*$/",$data['order_audio'][1]['auditions_path'],$auditions_match);
                    if($full_match){
                        $data['order_audio'][1]['full_path'] = str_replace("http","https", $data['order_audio'][1]['full_path']);
                    }
                    if($auditions_match){
                        $data['order_audio'][1]['auditions_path'] = str_replace("http","https", $data['order_audio'][1]['auditions_path']);
                    }
                }
                if(in_array($data['order_audio'][1]['id'], $_SESSION['buy_audio_ids']))
                {
                    $data['play_path'] = $data['order_audio'][1]['full_path'];
                    $data['is_auditions'] = 1;
                }
                else
                {
                    $data['play_path'] = $data['order_audio'][1]['auditions_path'];
                    $data['is_auditions'] = 2;
                }
            }
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
            $str_length = mb_strlen(strip_tags($data['audios_synopsis']),'UTF8');
            if($str_length > 45){
                $data['prise'] = mb_substr(strip_tags($data['audios_synopsis']),0,45,'utf-8').'...';
            }else{
                $data['prise'] = mb_substr(strip_tags($data['audios_synopsis']),0,45,'utf-8');
            }
            $cour_img = $this->getImagePath($data['image']);
            $data['cour_img'] = $cour_img['path'];
        }
        $member = $this->getOne(array('type' => 1),array('price'),'member_set');
        $data['vip_price'] = $member['price'];
        $data['collect'] = $collect ? 1 : 2;
        $data['amount'] = $user['amount'];
        
        if(!$data){
            return array(
                'code' => 400,
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
    
    //提交评论
    public function commentVideoSubmit(){
        $audio_id = $_POST['id'] ? $_POST['id'] : 0;
        $user_id = $_POST['user_id'] ? $_POST['user_id'] : 0;
        $content = $_POST['content'] ? $_POST['content'] : '';
        if(!$content){
            return array(
                'code' => 400,
                'message' => '评论内容不能为空',
            );
        }else{
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
                'is_top' => 2,
                'content' => $str ? $str : $content,
                'old_content' => $content,
                'deep' => 1,
                'parent_id' => 0,
                'user_type' => 1,
                'type' => 2,
                'delete' => 0,
                'timestamp' => $this->getTime(),
            );
            $audio = $this->getOne(array('id' => $data['audio_id']),array('courses_id'),'audio');
            if($audio['courses_id']){
                $data['courses_id'] = $audio['courses_id'];
            }
            $row = $this->insertData($data,'comment');
            $this->updateKey($audio_id, 1, 'comment_num', 1,'audio');
            $user = $this->getOne(array('id' => $user_id),array('name','head_icon','img_id'),'user');
            $user['comment_id'] = $row;
            if($user['img_id']){
                $img = $this->getOne(array('id' => $user['img_id']),array('path','filename'),'image');
                $user['head_icon'] = ROOT_PATH.UPLOAD_PATH.$img['path'] . $img['filename'];
            }else{
                $user['head_icon'] = $user['head_icon'];
            }
            $user['time'] = $data['timestamp'];
        }
        if($row){
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
    
    //获取评论列表
    public function ajaxGetVideoCommentList(){
        $page = isset($_POST['page']) ? $_POST['page'] : 0;
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
                DB_PREFIX.'comment.timestamp_update' => 'DESC',
            ),
            'columns' => array(
                'id' => 'id',
                'audio_id' => "audio_id",
                'user_id' => "user_id",
                'content' => "content",
                'is_top' => 'is_top',
                'timestamp' => 'timestamp'
            ),
//             'limit' => $p['limit'],
            'need_page' => true,
            'page' => $page
        );
        $where = new Where();
        $num = isset($_POST['num']) && $_POST['num'] ? $_POST['num'] : 1;
        if($num == 1){
            $where->equalTo('audio_id', $_POST['id']);
        }else{
            $where->equalTo('courses_id', $_POST['id']);
        }
        $where->equalTo(DB_PREFIX.'comment.delete', "0");
        $where->equalTo(DB_PREFIX.'comment.deep', "1");
        $comment =  $this->getAll($where,$data,$data['page'],0,'comment');
        if($comment['list']){
            foreach ($comment['list'] as $v){
                if($v['img_id']){
                    $v['user_head_icon'] = ROOT_PATH.UPLOAD_PATH.$v['img_path'].$v['img_filename'];
                }
                $praise = $this->getOne(array('comment_id' => $v['id'],'user_id' => $_SESSION['user_id'],'delete' => 0),array('*'),'praise');
                $v['praise'] =  $praise ? 1 : 0;
                $data = array( 
                    'order' => array(
                        DB_PREFIX.'comment.id' => 'DESC',
                    ),
                    'columns' => array(
                        'id' => 'id',
                        'audio_id' => "audio_id",
                        'user_id' => "user_id",
                        'content' => "content",
                        'user_type' => 'user_type',
                        'timestamp' => 'timestamp'
                    ),
                    'need_page' => true,
                );
                $son_com =  $this->getAll(array('parent_id' =>$v['id'],DB_PREFIX.'comment.delete' => 0,'deep'=>2),$data,$data['need_page'],0,'comment');
                $v['son_com'] = $son_com['list'];
                foreach ($son_com['list'] as $n){
                   if($n['user_type'] == 1)//用户
                    {
                       $user = $this->getOne(array('id'=>$n['user_id']),array('user_name' => "name",'user_img' => "img_id",'user_head_icon' => "head_icon",'img_path'),'user');
                       $n['user_head_icon'] = $user && $user['img_path'] ? ROOT_PATH.UPLOAD_PATH.$user['img_path'] : $user['user_head_icon'];
                       $n['user_name'] = $user ? $user['user_name'] : "";
                       
                    }
                    else //管理员
                    {
                        $admin = $this->getOne(array('id'=>$n['user_id']),array('name','image'),'admin');
                        if($admin)
                        {
                            //$image = $this->getOne(array('id'=>$admin['image']),array('id','path','filename'),'image');
                            $n['user_head_icon'] = $admin['image'] ? ROOT_PATH.UPLOAD_PATH.$admin['image'] : "";
                        }
                        else 
                        {
                            $n['user_head_icon'] = "";
                        }
                        $n['user_name'] = $admin ? $admin['name'] : "";
                    } 
                    $son_praise = $this->getOne(array('comment_id' => $n['id'],'user_id' => $_SESSION['user_id'],'delete' => 0),array('*'),'praise');
                    $n['praise'] =  $son_praise ? 1 : 0;
                }
            }
        } 
        return $comment ? $comment['list'] : array();
    }
    
    public function ajaxGetVideoPraiseList(){
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
    
    public function ajaxReplyVideoComment(){
        $comment_id = $_POST['id'] ? $_POST['id'] : 0;
        $user_id = $_POST['user_id'] ? $_POST['user_id'] : 0;
        $content = $_POST['content'] ? $_POST['content'] : '';
        if(!$content){
            return array(
                'code' => 400,
                'message' => '回复评论内容不能为空',
            );
        }else{
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
            $row = $this->insertData($data,'comment');
            $this->updateKey($comment['audio_id'], 1, 'comment_num', 1,'audio');
            $this->updateKey($comment['id'], 1, 'comment_num', 1,'comment');
            $user = $this->getOne(array('id' => $user_id),array('id','name','head_icon','img_id'),'user');
            $user['comment_id'] = $row;
            if($user['img_id']){
                $img = $this->getOne(array('id' => $user['img_id']),array('path','filename'),'image');
                $user['head_icon'] = ROOT_PATH.UPLOAD_PATH.$img['path'] . $img['filename'];
            }else{
                $user['head_icon'] = $user['head_icon'];
            }
            $user['time'] = $data['timestamp'];
        }
        if(isset($row) && $row){
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
    
    public function ajaxDeleteVideoComment(){        
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
            $this->updateKey($data['audio_id'], 2, 'comment_num', $total,'audio');   
            $this->updateKey($data['parent_id'], 2, 'comment_num', 1,'comment');
        }
    }
    
    //课程包提交评论
    public function coursesCommentSubmit(){
        $courses_id = $_POST['id'] ? $_POST['id'] : 0;
        $user_id = $_POST['user_id'] ? $_POST['user_id'] : 0;
        $content = $_POST['content'] ? $_POST['content'] : '';
        if(!$content){
            return array(
                'code' => 400,
                'message' => '评论内容不能为空',
            );
        }else{
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
                'audio_id' => 0,
                'user_id' => $user_id,
                'courses_id' => $courses_id,
                'is_top' => 2,
                'content' => $str,
                'old_content' => $content,
                'deep' => 1,
                'parent_id' => 0,
                'user_type' => 1,
                'type' => 4,
                'delete' => 0,
                'timestamp' => $this->getTime(),
            );
            $row = $this->insertData($data,'comment');
            $user = $this->getOne(array('id' => $user_id),array('name','head_icon','img_id'),'user');
            $user['comment_id'] = $row;
            if($user['img_id']){
                $img = $this->getOne(array('id' => $user['img_id']),array('path','filename'),'image');
                $user['head_icon'] = ROOT_PATH.UPLOAD_PATH.$img['path'] . $img['filename'];
            }else{
                $user['head_icon'] = $user['head_icon'];
            }
            $user['time'] = $data['timestamp'];
        }
        if(isset($row) && $row){
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
    
    //课程包回复
    public function ajaxReplyCoursesComment(){
        $comment_id = $_POST['id'] ? $_POST['id'] : 0;
        $user_id = $_POST['user_id'] ? $_POST['user_id'] : 0;
        $content = $_POST['content'] ? $_POST['content'] : '';
        if(!$content){
            return array(
                'code' => 400,
                'message' => '回复评论内容不能为空',
            );
        }else{
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
                'courses_id' => $comment['courses_id'],
                'is_top' => 2,
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
            $row = $this->insertData($data,'comment');
            $this->updateKey($comment['id'], 1, 'comment_num', 1,'comment');
            $user = $this->getOne(array('id' => $user_id),array('id','name','head_icon','img_id'),'user');
            $user['comment_id'] = $row;
            if($user['img_id']){
                $img = $this->getOne(array('id' => $user['img_id']),array('path','filename'),'image');
                $user['head_icon'] = ROOT_PATH.UPLOAD_PATH.$img['path'] . $img['filename'];
            }else{
                $user['head_icon'] = $user['head_icon'];
            }
            $user['time'] = $data['timestamp'];
        }
        if(isset($row) && $row){
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
    
    //删除课程包回复
    public function ajaxDeleteCoursesComment(){
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
            $this->updateKey($data['parent_id'], 2, 'comment_num', 1,'comment');
        }
    }
    
    //新增加或者删除课程
    public function ajaxAddCollect(){
        $id = isset($_POST['id']) ? $_POST['id'] : 0; //课程或者课程包id
        $type = isset($_POST['type']) ? $_POST['type'] : 0; //1 课程 2 课程包
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;
        if(!$id && !$type && !$user_id){
            return array(
                'code' => 400,
                'message' => '操作失败',
            );
        }
        $data = $this->getOne(array('delete' => 0,'type' => $type,'user_id' => $user_id,'audio_id' => $id),array('*'),'collect_log');
        if(!$data){
            $row = $this->insertData(array(
                'delete' => 0,
                'type' => $type,
                'user_id' => $user_id,
                'audio_id' => $id,
                'delete' => 0,
                'timestamp' => $this->getTime()
            ), 'collect_log');
           if($row){
               return array(
                   'code' => 200,
                   'message' => '操作成功',
                   'num' => 1  
               );
           }
        }else{
            $row = $this->deleteData(array('id' => $data['id']),'collect_log',true);
            if($row){
                return array(
                    'code' => 200,
                    'message' => '操作成功',
                    'num' => 2
                );
            }
        }
        return array(
            'code' => 400,
            'message' => '操作失败'
        );
    }
    
    //增加学习人数
    public function ajaxAddStudyNum(){
        $id = $_POST['id'] ?  $_POST['id'] : 0;
        $type = $_POST['type'] ? $_POST['type'] : 2;
        if($id){    
            $audio = $this->getOne(array('delete' => 0,'status' => 1,'id' => $id),array('id','courses_id','teacher_id'),'audio');
            if($audio){
                if($type == 1){
                    $this->updateKey($id, 1, 'over_play', 1,'audio');
                    return array(
                        'code' => 200,
                        'message' => '新增完播量成功',
                    );
                }
                $this->updateKey($id, 1, 'study_num', 1,'audio');
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
    
    
    //获取视频数据
    public function ajaxgetFreeVideoList(){
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $one_id = $_POST['oid'] ? $_POST['oid'] : 0;
        $two_id = $_POST['tid'] ? $_POST['tid'] : 0;
        $where = new Where();
        if($two_id){
            $sql = "SELECT `id`,`title`,1 AS `type`, NULL, study_num,audio_length ,teacher_id,image,`timestamp` FROM nb_audio WHERE `audio_two_type` = ".$two_id." AND `delete` = 0 AND `STATUS` = 1 AND `pay_type` = 3 AND type = 2 ORDER BY id DESC";
        }else{
            $sql = "SELECT `id`,`title`,1 AS `type`, NULL, study_num,audio_length ,teacher_id,image,`timestamp` FROM nb_audio WHERE `audio_one_type` = ".$one_id." AND `delete` = 0 AND `STATUS` = 1 AND `pay_type` = 3 AND type = 2 ORDER BY id DESC";
        }
        $num = 5;
        $offset = ($page-1)*$num;
        $sql = $sql ." LIMIT " . $offset .','. $num;
        $list = $this->executeSql($sql);
//         var_dump($list['list']);exit;
        if($list['list']){
            foreach ($list['list'] as $v){
                $teacher = $this->getOne(array('id'=>$v['teacher_id']),array('name','head_icon'),'teacher');
                if($v['image']){
                    $img = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                }else{
                    $img = $this->getOne(array('id'=>$teacher['head_icon']),array('path','filename'),'image');
                }
                $v['image_path'] = $img['path'].$img['filename'];
                $v['teacher_name'] = $teacher['name'];
            }
        }
        return $list['list'] ? $list['list'] : array();
    }
    
    //获取视频数据
    public function ajaxWatchRecord(){
        $time = isset($_POST['time']) ? $_POST['time'] : "";
        $id = $_POST['id'] ? $_POST['id'] : 0;
        $type = $_POST['type'] ? $_POST['type'] : 0;
        if(!$time && !$id){
            return array(
                'code' => 400,
                'message' => '数据不存在！'
            );
        }else{
            $user_id = $_SESSION['user_id'];
            $data = array(
                'user_id' => $user_id,
                'audio_id' => $id,
                'time' => $time,
                'delete' => 0,
                'type' => $type,
                'timestamp' => $this->getTime(),
            );
            $watch = $this->getOne(array('user_id' => $user_id,'audio_id' => $id),array('*'),'watch_record');
            if($watch){
                $row = $this->updateData($data, array('id' => $watch['id']),'watch_record');
            }else{
                $row = $this->insertData($data,'watch_record');
            }
            if($row){
                return array(
                    'code' => 200,
                    'message' => '录入成功！'
                );
            }else{
                return array(
                    'code' => 400,
                    'message' => '数据不存在！'
                );
            }
        }
    }

    public function coursesOrderSubmit($data=array()){
        $price = isset($_POST['price']) ? $_POST['price'] : 0;
        $pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : 0;
        $num = isset($_POST['num']) ? $_POST['num'] : 1;
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $transfer_way = isset($_POST['transfer_way']) ? $_POST['transfer_way'] : 0;
        $original_price =isset($_POST['original_price']) ? $_POST['original_price'] : 0;
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $vip = isset($_SESSION['is_vip']) ? $_SESSION['is_vip']  : 0;
        $audio_type = 4;
        if($data){
            $price = $data['pay_price'];
            $pay_type = $data['pay_type'];
            $num = $data['number'];
            $type = 2;
            $transfer_way = $data['transfer_way'];
            $user_id = $data['user_id'];
            $id = $data['audio_id'];
            $audio_type = $data['audio_type'];
            $vip = 1;
        }
        if(!$price && !$id && !$transfer_way){
            return array(
                'code' => 400,
                'message' => '课程购买失败！',
            );
        }

        $data = array(
            'type' => 1,
            'pay_type' => $pay_type,
            'number' => $num,
            'genre' => 2,
            'audio_id' => $id,
            'amount' => $price,
            'status' => 2,
            'audio_type' => $audio_type,
            'transfer_no' => $this->makeSN().'-1',
            'transfer_way' => $transfer_way,
            'user_id' => $user_id,
            'vip_pay' => $vip,
            'delete' => 0,
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime(),
        );

        $row = $this->insertData($data,'pay_log');
        $user_data = $this->getOne(array('id' => $user_id),array('freeze_amount','id'),'user');
        if($row){
            if($data['pay_type'] == 2){
                //赠送的
                $giving = array(
                    'user_id' => $data['user_id'],
                    'audio_id' => $data['audio_id'],
                    'type' => $data['audio_type'],
                    'num' => $data['number'],
                    'remain_num' => 0,
                    'price' => $data['amount'],
                    'delete' => 0,
                    'timestamp' => $this->getTime(),
                );
                $giving_log_id = $this->insertData($giving,'giving_log');
                $user = $this->updateKey($data['user_id'], 2, 'amount',$data['amount'],'user');
                $this->updateKey($data['user_id'], 1, 'consumption',$data['amount'],'user');

                if($user_data['freeze_amount'] <= $data['amount']){
                    $this->updateData(array('freeze_amount' => 0), array('id' => $data['user_id']),'user');
                }else{
                    $this->updateKey($data['user_id'], 2, 'freeze_amount',$data['amount'],'user');
                }

                $pay_log = $this->updateData(array('status' => 1), array('transfer_no' => $data['transfer_no']),'pay_log');
                $fin_type = 2;
            }else if($data['pay_type'] == 1){
                //购买的
                $user = $this->updateKey($data['user_id'], 2, 'amount',$data['amount'],'user');
                if($user_data['freeze_amount'] <= $data['amount']){
                    $this->updateData(array('freeze_amount' => 0), array('id' => $data['user_id']),'user');
                }else{
                    $this->updateKey($data['user_id'], 2, 'freeze_amount',$data['amount'],'user');
                }
                $this->updateKey($data['user_id'], 1, 'consumption',$data['amount'],'user');
                $courese = $this->getOne(array('id' => $data['audio_id']),array('*'),'courses');
                $courses_ids = array_filter(explode(',', $courese['audios_ids']));
                foreach ($courses_ids as $v){
                    $video_data = $this->getOne(array('id' => $v,'status' => 1),array('*'),'audio');
                    if($video_data){
                        $buy_data = array(
                            'user_id' => $data['user_id'],
                            'audio_id' => $v,
                            'is_giving' => 1,
                            'delete' => 0,
                            'timestamp' => $this->getTime(),
                        );
                        $this->insertData($buy_data,'buy_log');
                    }
                }
                $pay_log = $this->updateData(array('status' => 1), array('transfer_no' => $data['transfer_no']),'pay_log');
                $fin_type = 1;
            }
            //财务表修改
            $financial_data = array(
                'type' => $fin_type,
                'amount' => $data['amount'],
                'income' => 2,
                'transfer_no' => $this->makeSN(),
                'transfer_way' => 2,
                'remark' => '',
                'user_id' => $user_id,
                'pay_log_id' => $row,
                'vip_pay' =>  $vip,
                'delete' => 0,
                'timestamp_update' => $this->getTime(),
                'timestamp' => $this->getTime(),
            );
            $this->insertData($financial_data,'financial');
            return array(
                'code' => 200,
                'transfer_way' => 2,
                'is_debug' => 1,
                'giving_log_id' => isset($giving_log_id) && $giving_log_id ? $giving_log_id : 0,
                'message' => "支付成功"
            );
        }else{
            return array(
                'code' => 400,
                'message' => '充值失败'
            );
        }
    }


    public function audioOrderSubmit($data=array()){
        $price = isset($_POST['price']) ? $_POST['price'] : 0;
        $pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : 0;
        $num = isset($_POST['num']) ? $_POST['num'] : 1;
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $transfer_way = isset($_POST['transfer_way']) ? $_POST['transfer_way'] : 0;
        $original_price =isset($_POST['original_price']) ? $_POST['original_price'] : 0;
        $type = isset($_POST['type']) ? $_POST['type'] : 2;
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $vip = isset($_SESSION['is_vip']) ? $_SESSION['is_vip']  : 0;
        if($data){
            $price = $data['pay_price'];
            $pay_type = $data['pay_type'];
            $num = $data['number'];
            $type = $data['audio_type'];
            $transfer_way = $data['transfer_way'];
            $user_id = $data['user_id'];
            $id = $data['audio_id'];
            $vip = 1;
        }
        if(!$price && !$id && !$transfer_way){
            return array(
                'code' => 400,
                'message' => '课程购买失败！',
            );
        }
        if($type == 3){
            $genre = 2;
        }else{
            $genre = 1;
        }
        $data = array(
            'type' => 1,
            'pay_type' => $pay_type,
            'number' => $num,
            'genre' => $genre,
            'audio_id' => $id,
            'amount' => $price,
            'audio_type' => $type,
            'status' => 2,
            'transfer_no' => $this->makeSN().'-1',
            'transfer_way' => $transfer_way,
            'vip_pay' => $vip,
            'user_id' => $user_id,
            'delete' => 0,
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime(),
        );
        $row = $this->insertData($data,'pay_log');
        $user_data = $this->getOne(array('id' => $data['user_id']),array('freeze_amount','id'),'user');
        if($row){
            if($data['pay_type'] == 2){
                //赠送的
                $giving = array(
                    'user_id' => $data['user_id'],
                    'audio_id' => $data['audio_id'],
                    'type' => $data['audio_type'],
                    'num' => $data['number'],
                    'remain_num' => 0,
                    'price' => $data['amount'],
                    'delete' => 0,
                    'timestamp' => $this->getTime(),
                );
                $giving_log_id = $this->insertData($giving,'giving_log');
                $user = $this->updateKey($data['user_id'], 2, 'amount',$data['amount'],'user');
                $this->updateKey($data['user_id'], 1, 'consumption',$data['amount'],'user');

                if($user_data['freeze_amount'] <= $data['amount']){
                    $this->updateData(array('freeze_amount' => 0), array('id' => $data['user_id']),'user');
                }else{
                    $this->updateKey($data['user_id'], 2, 'freeze_amount',$data['amount'],'user');
                }
                $pay_log = $this->updateData(array('status' => 1), array('transfer_no' => $data['transfer_no']),'pay_log');
                $fin_type = 2;
            }else if($data['pay_type'] == 1){
                //余额支付  单买
                $user = $this->updateKey($data['user_id'], 2, 'amount',$data['amount'],'user');

                if($user_data['freeze_amount'] <= $data['amount']){
                    $this->updateData(array('freeze_amount' => 0), array('id' => $data['user_id']),'user');
                }else{
                    $this->updateKey($data['user_id'], 2, 'freeze_amount',$data['amount'],'user');
                }
                $this->updateKey($data['user_id'], 1, 'consumption',$data['amount'],'user');
                if($data['audio_type'] == 3){
                    $courese = $this->getOne(array('id' => $data['audio_id']),array('*'),'courses');
                    $courses_ids = array_filter(explode(',', $courese['audios_ids']));
                    foreach ($courses_ids as $v){
                        $buy_data = array(
                            'user_id' => $data['user_id'],
                            'audio_id' => $v,
                            'is_giving' => 1,
                            'delete' => 0,
                            'timestamp' => $this->getTime(),
                        );
                        $this->insertData($buy_data,'buy_log');
                    }
                }else{
                    $buy_log = $this->insertData(array('user_id' => $data['user_id'],'audio_id' => $data['audio_id'],'is_giving'=>1,'delete' => 0,'timestamp' => $this->getTime()),'buy_log');
                }

                $pay_log = $this->updateData(array('status' => 1), array('transfer_no' => $data['transfer_no']),'pay_log');
                $fin_type = 1;
            }
            //财务表修改
            $financial_data = array(
                'type' => $fin_type,
                'amount' => $data['amount'],
                'income' => 2,
                'transfer_no' => $this->makeSN(),
                'transfer_way' => 2,
                'remark' => '',
                'user_id' => $data['user_id'],
                'pay_log_id' => $row,
                'vip_pay' => $vip,
                'delete' => 0,
                'timestamp_update' => $this->getTime(),
                'timestamp' => $this->getTime(),
            );
            $this->insertData($financial_data,'financial');
            return array(
                'code' => 200,
                'transfer_way' => 2,
                'is_debug' => 1,
                'giving_log_id' => isset($giving_log_id) && $giving_log_id ? $giving_log_id : 0,
                'message' => "支付成功"
            );
        }else{
            return array(
                'code' => 400,
                'message' => '充值失败'
            );
        }
    }
}