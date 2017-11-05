<?php
namespace Web\Model;

use Zend\Db\Sql\Where;
use Api\Controller\Common\WhereRequest;
use Core\System\AiiUtility\Log;
use Core\System\WxPayApi\WXHongBao;
use Core\System\Image;
use Core\System\Compound;

class UserModel extends CommonModel
{

    protected $table = ''; 

    public function getUserIndex()
    {
       $user_id = $_SESSION['user_id'] ? $_SESSION['user_id'] : 0;
       if(!$user_id){
           return array(
               'code' => 400,
               'message' =>  '用户数据不存在',
           );
       }
       $user = $this->getOne(array('id' => $user_id,'status'=>1,'delete' => 0),array('id','name','head_icon','img_id','img_path','member_time','amount','brokerage'),'user');
       if(!$user){
           return array(
               'code' => 400,
               'message' =>  '用户数据不存在',
           );
       }else{
           if($user['img_id']){
               $user['head_icon'] = ROOT_PATH.UPLOAD_PATH.$user['img_path'];
           }
           $setup = $this->getOne(array('type' => 2),array('id','content'),'setup');
           if(!$setup){
               $user['setup'] = "";
           }else{
               $user['setup'] = $setup['content'];
           }
       }
       $user['code'] = 200;
       return $user;
    }

    
    //获取视频数据
    public function ajaxGetFavoriteList(){
        $type = isset($_POST['num']) ? $_POST['num'] : 1;
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        if($type == 1){
            $collect_log_one = 1;
            $collect_log_two = 3;
        }else{
            $collect_log_one = 2;
            $collect_log_two = 4;
        }
        $sql = "SELECT `nb_collect_log`.*,`sell_type`,`audio_length` AS `count`,`price`,`original_price`, `nb_audio`.`id` AS `audio_id`, `nb_audio`.`title` AS `audio_name` , `nb_audio`.`study_num` AS `study_num` ,`nb_audio`.`audio_length` AS `audio_length`, `nb_audio`.`image` AS `image` , `nb_audio`.`teacher_id` AS `teacher_id` , NULL AS `audio_num`  FROM `nb_collect_log` LEFT JOIN `nb_audio` ON `nb_audio`.`id` = `nb_collect_log`.`audio_id` WHERE ((`nb_collect_log`.`type` = `nb_audio`.`type`) and `nb_collect_log`.`type` = ".$collect_log_one." and `nb_audio`.`delete` = 0 and `nb_audio`.`status` = 1 and `nb_collect_log`.`user_id` = ".$_SESSION['user_id'].") UNION SELECT `nb_collect_log`.*,`sell_type`,NULL AS COUNT,`price`,`original_price`, `nb_courses`.`id` AS `courses_id`, `nb_courses`.`title` AS `audio_name` , `nb_courses`.`study_num` AS `study_num` , NULL , `nb_courses`.`image` AS `image` , NULL, `nb_courses`.`audios_num` AS `audios_num` FROM `nb_collect_log` LEFT JOIN `nb_courses` ON `nb_courses`.`id` = `nb_collect_log`.`audio_id` WHERE ((`nb_collect_log`.`type` = `nb_courses`.`type`) and `nb_courses`.`delete` = 0 and `nb_courses`.`status` = 1 and `nb_collect_log`.`type` = ".$collect_log_two." and `nb_collect_log`.`user_id` = ".$_SESSION['user_id'].") ORDER BY `type` DESC";
        $num = 10;
        $offset = ($page-1)*$num; 
        $sql = $sql ." LIMIT " . $offset .','. $num;
        $list = $this->executeSql($sql);
        if($list['list']){
            foreach ($list['list'] as $v){  
                $v['study_num'] = $this->convertTenThousandNum($v['study_num']);
                $v['timestamp'] = date('m-d',strtotime($v['timestamp']));
                if($v['type'] == 2 || $v['type'] == 1){      
                    $v['audio_length'] = $this->getShiftTime($v['audio_length']);
                    $teacher = $this->getOne(array('id'=>$v['teacher_id']),array('name','head_icon'),'teacher');
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
        return $list['list'] ? $list['list'] : array();
    }
    
    public function ajaxDeleteCollect(){
       $id = $_POST['id'] ? $_POST['id'] : 0;
       if(!$id){
           return array(
               'code' => 400,
               'message' => '删除失败'
           );
       }else{
          $row = $this->deleteData(array('id' => $id),'collect_log',true);
          if($row){
              return array(
                  'code' => 200,
                  'message' => '删除成功'
              );
          }else{
              return array(
                  'code' => 400,
                  'message' => '删除失败'
              );
          }
       }   
    }
    
    public function ajaxDeleteFavoriteAll(){
        $num = $_POST['num'] ? $_POST['num'] : 0;
        $user_id = $_POST['user_id'] ? $_POST['user_id'] : 0;
        $where = new Where();
        $where->equalTo('user_id', $user_id);
        if($num == 1){
            $where->equalTo('type', 1)->OR->equalTo('type', 3);
        }else{
            $where->equalTo('type', 2)->OR->equalTo('type', 4);
        }
        
        $row = $this->deleteData($where,'collect_log',true);
        if($row){
            return array(
                'code' => 200,
                'message' => '删除成功'
            );
        }else{
            return array(
                'code' => 400,
                'message' => '删除失败'
            );
        }
    }
    public function ajaxGetCoursepack()
    {
        $pack_type = $_POST['pack_type'] ? $_POST['pack_type'] : 3;
        $page = $_POST['page'] ? $_POST['page'] : 1;
        $data = array(
            'columns' => array('id','type','title','image','study_num','audios_num','audios_ids'),
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'image',
                    'on' => DB_PREFIX . 'courses.image = ' . DB_PREFIX . 'image.id',
                    'columns' => array('path','filename'),
                ),
            ),
            'need_page' => true,
        );
        $where = new Where();
        $where->equalTo('delete', 0)->equalTo('status', 1)->equalTo('recommend', 1)->equalTo('type', $pack_type);
        $where->greaterThan('audios_num', 0);
        $courses_list = $this->getAll($where,$data,$page,PAGE_NUMBER,'courses');
        if($courses_list['total'])
        {
            foreach ($courses_list['list'] as $v)
            {
                if($pack_type == 3)
                {
                    $audios_ids_array = explode(",", trim($v['audios_ids'],','));
                    $audio = $this->getOne(array('id'=>$audios_ids_array[0]),array('id','auditions_path','full_path'),'audio');
                    if(in_array($audio['id'], $_SESSION['buy_audio_ids']))
                    {
                        $v['full_path'] = $audio ? $audio['full_path'] : "";
                    }
                    else 
                    {
                        $v['full_path'] = $audio ? $audio['auditions_path'] : "";
                    }
                    
                    $v['first_audio_id'] = $audio ? $audio['id'] : 0;
                }
                $v['head_icon'] = $v['filename'] ? ROOT_PATH.UPLOAD_PATH.$v['path'].$v['filename'] : "";
                $v['study_num'] = $this->convertTenThousandNum($v['study_num']);
            }
        }
        return array('code'=>200,'list'=>$courses_list['list']);
    }
    //用户帮助
    public function userHelp($type)
    {
        $type = isset($_POST['type']) && $_POST['type'] ? $_POST['type'] : $type;
        $content = isset($_POST['content']) && $_POST['content'] ? $_POST['content'] : "";
        $where = new Where();
        $where->equalTo('delete', 0);
        $where->equalTo('type', $type);
        if (isset($content) && $content) {
            $where->like('title', '%'.$content.'%');
        }
        $list = $this->fetchAll($where, array(
            "columns" => array(
                'id',
                'title',
                'sort',
            ),
            "order" => array(
                'sort' => 'ASC'
            ),
            "limit" => 10,
        ), "user_help");
        $list ? $list : array();
        if (isset($content) && $content){
            return array(
                'code' => 200,
                'list' => $list,
            );
        }
        return $list;
    }
    
    //用户帮助/功能介绍详情
    public function introduce($type,$id)
    {
        $list = $this->getOne(array('id' => $id,'type' => $type),array('*'),'user_help');
        return $list;
    }
    
    //意见反馈新增
    public function addFeedBack()
    {
        $content = $_POST['content'] ? $_POST['content'] : "";
        $user_id = $_POST['user_id'] ? $_POST['user_id'] : 0;
        if(!$content || !$user_id){
            return array(
                'code' => 400,
                'message' => '提交失败！'
            );
        }else{
           $data = array(
               'user_id' => $user_id,
               'content' => $content,
               'timestamp' => $this->getTime(),
           );
           $row = $this->insertData($data,'notification_feedback');
           if($row){
               return array(
                   'code' => 200,
                   'message' => '提交成功！'
               );
           }else{
               return array(
                   'code' => 400,
                   'message' => '提交失败！'
               );
           }
        }
    }
    
    //用户帮助/功能介绍详情
    public function ishare()
    {
        $seach = array('rule','button_name','heading','subheading','img_id','img_path','small_img_ids','small_img_paths');
        $list = $this->getOne(array(),$seach,'distribut_set');
        $list['img_path'] = ROOT_PATH.UPLOAD_PATH.$list['img_path'];
        if($list['small_img_paths']){
            $list['small_img_ids'] = explode(',',$list['small_img_ids']);
            $list['small_img_paths'] = explode(',',$list['small_img_paths']);
            for($i=0;$i<count($list['small_img_paths']);$i++){
                $list['small_img_paths'][$i] = array(
                    'id' => $list['small_img_ids'][$i],
                    'val' => ROOT_PATH.UPLOAD_PATH.$list['small_img_paths'][$i]
                );
            }
        }
        //获取分享二维码
        $list['path'] = $this->getIshareQrCode();
        //获取二维码过期时间
//        $time = time() + 3600*24*30;
//        $list['time'] = date('Y'.'年'.'m'.'月'.'d'.'日',$time);
        return $list;
        //旧海报数据
//        $list = $this->getOne(array('type' => 2,'delete' => 0),array('img_id','img_path'),'member_set');
//        $list['img_path'] = ROOT_PATH.UPLOAD_PATH.$list['img_path'];
//        return $list;
    }

    //获取分享二维码
    public function getIshareQrCode(){
        $log = new Log('web');
        //查询数据是否存在
        $data = $this->getOne(array('md5' => md5($_SESSION['user_id'].'_qrcode.jpg')),array('*'),'image');
        if($data){
            $log->info(__CLASS__.'<-->'.__FUNCTION__.$_SESSION['user_id'].'数据库读取二维码成功,行号为：'.__LINE__.'行');
            return ROOT_PATH.UPLOAD_PATH.$data['path'].$data['filename'];
        }
        include_once APP_PATH . '/vendor/Core/System/phpqrcode/phpqrcode.php';
        $file = LOCAL_SAVEPATH.'qrcode/code/';
        $value = DIS_QRCODE_URL.$_SESSION['user_id'];
        //生成文件夹
        if(!is_dir($file)){
            mkdir($file,'0777',true);
            chmod($file, 0777);
        }
        $file = $file.$_SESSION['user_id'].'_qrcode.jpg';
        $ext = pathinfo($file);
        //添加到数据库
        $set = array(
            'md5' => md5($ext['basename']),
            'filename' => $ext['basename'],
            'path' => 'qrcode/code/',
            'timestamp' => $this->getTime(),
        );

        $this->insertData($set,'image');
        //生成二维码
        \QRcode::png($value,$file, 'L',4,1);
        if($file){
            $log->info(__CLASS__.'<-->'.__FUNCTION__.$_SESSION['user_id'].'生成二维码成功,行号为：'.__LINE__.'行');
            return ROOT_PATH.UPLOAD_PATH.$set['path'].$set['filename'];
        }else{
            $log->err(__CLASS__.'<-->'.__FUNCTION__.$_SESSION['user_id'].'生成二维码失败,行号为：'.__LINE__.'行');
        }
        return $file;
    }
    
    //用户详情
    public function userDetails($id)
    {
        $user = $this->getOne(array('id' => $id,'delete' => 0,'status' => 1),array('*'),'user');
        $position = $this->fetchAll(array('delete' => 0),array("columns" => array('id','name')),'position');
        if(!$user){
            return array(
                'code' => 400,
                'message' =>  '用户数据不存在',
            );
        }else{
            if($user['img_id']){
                $user['head_icon'] = ROOT_PATH.UPLOAD_PATH.$user['img_path'];
            }
        }
        if ($user['position']){
            $user['current_position_id'] = $user['position'];
        }else {
            $user['current_position_id'] = $position[0]['id'];
            $user['position_name'] = $position[0]['name'];
        }
        
        if($position){
            foreach ($position as $v){
                if($v['id'] == $user['position']){
                    $user['position_name'] = $v['name'];
                }
                
                $user['position_id'][$v['id']] = $v['name'];
                $data[] = array(
                    'label' => $v['name'],
                    'value' => $v['id']
                );
            }
            $user['position'] = json_encode($data);
            $user['position_id'] = json_encode($user['position_id']);
        }
        
        if($user['signature'] && !strstr($_SERVER['REQUEST_URI'],'changeName')){
            $lenght = mb_strlen($user['signature'],'utf-8');
            if($lenght>2){
                $user['signature'] = mb_substr($user['signature'],0,2,'utf-8').'...';
            }else{
                $user['signature'] = mb_substr($user['signature'],0,2,'utf-8');
            }
        }
        
        $feedback_total = $this->countData(array('user_id'=>$user['id'],'is_new'=>2,'delete'=>0,'reply_status'=>1),'notification_feedback');
        $subscibe_total = $this->countData(array('user_id'=>$user['id'],'is_new'=>2,'delete'=>0),'notification_subscibe');
        $user['total'] = ($feedback_total ? $feedback_total : 0) + ($subscibe_total ? $subscibe_total : 0) + $user['notification_num'];
        $user['subscibe_total'] = $subscibe_total ? $subscibe_total : 0;
        $user['region'] = $this->regionInfoToString($user['region_info']);
        
        $user['selset_sex'] = json_encode(array(1 => '男',2 => '女'));
        $user['code'] = 200;
//         var_dump($user);exit;
        return $user;
    }
    
    //修改用户信息
    public function ajaxAmendUser(){
        $text = $_POST['text'] ? $_POST['text'] : "";
        $id = $_POST['id'] ? $_POST['id'] : 0;
        $type = $_POST['type'] ? $_POST['type'] : 0;
        if(!$id){
            return array(
                'code' => 400,
                'message' => '修改失败'
            );
        }
        if($type == 1){
            $where = array('position' => $text);
        }else if($type == 2){
            $where = array('sex' => $text);
        }else if($type == 3){
            $where = array('name' => $text);
            $img_data = $this->fetchAll(array('user_id' => $_SESSION['user_id']),array('columns' => array('id','filename','path')),'distribut_img');
            if($img_data){
                foreach($img_data as $v){
                    $img = LOCAL_SAVEPATH.$v['path'].$v['filename'];
                    unlink($img);
                    $this->deleteData(array('id' => $v['id']),'distribut_img',true);
                }
            }
        }else if($type == 4){
            $where = array('signature' => $text);
        }else if($type == 5){
            $where = array('img_id' => $text,'img_path' => $_POST['path']);
        }
        $this->updateData($where,array('id' => $id),'user');
        return array(
            'code' => 200,
            'message' => '修改成功'
        );
    }
    
    /**
     * 我的钱包
     */
    public function wallet()
    {
        $user = $this->getOne(array('id'=>$_SESSION['user_id'],'delete'=>0),array('id','head_icon','brokerage','img_path','amount','stair_num','mobile','brokerage'),'user');
        $top_up = $this->getOne(array(),array('*'),'top_up');
        //佣金设置
        $d_set = $this->getOne(array(),array('explain'),'distribut_set');
        $user['explain'] = $d_set['explain'] ? $d_set['explain'] : '';
        if(!$user)
        {
            return array('code'=>400,'message'=>'用户不存在');
        }
        $user['account_content'] = $top_up['account_content'] ? $top_up['account_content'] : "";
        return array('code'=>200,'data'=>$user);
    }
   
    /**
     * 充值页面
     */
    public function rechargeIndex()
    {
        $user = $this->getOne(array('id'=>$_SESSION['user_id'],'delete'=>0),array('id','head_icon','img_path','amount'),'user');
        $top_up = $this->getOne(array(),array('*'),'top_up');
        if(!$user)
        {
            return array('code'=>400,'message'=>'用户不存在');
        }
        if(!$top_up)
        {
            return array('code'=>400,'message'=>'页面不存在');
        }
        $moneys_array = json_decode($top_up['top_up_price'],true);
        $data = array('user'=>$user,'top_up'=>$top_up,'moneys_array'=>$moneys_array);
        return array('code'=>200,'data'=>$data);
    }
    
    /**
     * 财务明细
     */
    public function finance($type)
    {
        $page = $_POST['page'] ? $_POST['page'] : 1;
        if($type == 1){
            $financial = $this->getAll(array('user_id'=>$_SESSION['user_id'],'delete'=>0),array('columns'=>array('*'),'need_page' => true), $page,PAGE_NUMBER,'financial');
            if($financial['list']){
                foreach ($financial['list'] as $v){
                    $pay = $this->getOne(array('id' => $v['pay_log_id']),array('id','user_id','audio_id','audio_type','pay_type'),'pay_log');
                    if($v['type'] == 1 || $v['type'] == 2){
                        if($pay['audio_type'] == 1 || $pay['audio_type'] == 2){
                            $audio_data = $this->getOne(array('id' => $pay['audio_id'],'type' => $pay['audio_type']),array('id','image','title','teacher_id'),'audio');
                            if($audio_data['image']){
                                $path =  $this->getImagePath($audio_data['image']);
                            }else{
                                $teacher_head_icon = $this->getOne(array('id' => $audio_data['teacher_id']),array('id','head_icon'),'teacher');
                                $path =  $this->getImagePath($teacher_head_icon['head_icon']);
                            }
                            $v['image_path'] = ROOT_PATH.UPLOAD_PATH.$path['path'];
                            $v['title'] = $audio_data['title'];
                        }else if($pay['audio_type'] == 3 || $pay['audio_type'] == 4){
                            $courses_data = $this->getOne(array('id' => $pay['audio_id'],'type' => $pay['audio_type']),array('id','image','title'),'courses');
                            $path =  $this->getImagePath($courses_data['image']);
                            $v['image_path'] = ROOT_PATH.UPLOAD_PATH.$path['path'];
                            $v['title'] = $courses_data['title'];
                        }
                    }else if($v['type'] == 4){
                        $v['pay_type_menber'] = $pay['pay_type'];
                    }
                }
            }
        }else{
            $financial = $this->getAll(array('user_id'=>$_SESSION['user_id']),array('columns'=>array('id','user_id','to_user_id','grade','amount','income','type','timestamp'),'need_page' => true), $page,PAGE_NUMBER,'brokerage_log');
            if($financial['list']){
                foreach($financial['list'] as $v){
                    if($v['type'] == 1){
                       $user = $this->getOne(array('id' => $v['to_user_id']),array('img_id','img_path','head_icon'),'user');
                       if($user['img_id']){
                           $head_icon = $this->getOne(array('id'=>$user['img_id']),array('path','filename'),'image');
                           $v['image_path'] = ROOT_PATH.UPLOAD_PATH.$head_icon['path'].$head_icon['filename'];
                       }else{
                           $v['image_path'] = $user['head_icon'];
                       }
                    }
                }
            }
        }
        return array('code'=>200,'data'=>$financial['list']);
    }
    
    public  function ajaxGetBuyLog(){
        $type = isset($_POST['num']) ? $_POST['num'] : 0;
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        if(!$type){
            return array(
                'code' => 400,
                'message' => '数据查询失败！'
            );
        }
        $on_array = array();
        if($type == 2){
             $on_array = array(
                'name' => DB_PREFIX . 'image',
                'on' => DB_PREFIX . 'image.id = ' . DB_PREFIX . 'audio.image',
                'columns' => array(
                    'img_id' => "id",
                    'img_path' => 'path',
                    'img_filename' => 'filename',
                ),
                'type'=>'left'
            );
        }
        $data = array(
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'audio',
                    'on' => DB_PREFIX . 'audio.id = ' . DB_PREFIX . 'buy_log.audio_id',
                    'columns' => array(
                        'audio_type' => 'type',
                        'audio_id' => "id",
                        'teacher_id' => "teacher_id",
                        'title' => "title",
                        'study_num' => "study_num",
                        'audio_length' => "audio_length",
                        'audio_img_id' => 'image',
                        'sell_type' => 'sell_type',
                        'price' => 'price',
                        'original_price' => 'original_price',
//                         'audio_length' => "count",
                    ),
                    'type'=>'left'
                ),
                array(
                    'name' => DB_PREFIX . 'teacher',
                    'on' => DB_PREFIX . 'teacher.id = ' . DB_PREFIX . 'audio.teacher_id',
                    'columns' => array(
                        'teacher_name' => "name",
                        'head_icon' => 'head_icon',
                    ),
                    'type'=>'left'
                ),
                $on_array
            ),
            'order' => array(
                DB_PREFIX.'buy_log.id' => 'DESC',
            ),
            'columns' => array(
              '*'
            ),
            //             'limit' => $p['limit'],
            'need_page' => true,
            'page' => $page
        );
        $where = new Where();
        $where->equalTo(DB_PREFIX.'buy_log.delete', "0");
        $where->equalTo(DB_PREFIX.'buy_log.user_id', $_SESSION['user_id']);
        $where->equalTo(DB_PREFIX.'audio.type', $type);
        $buy_log =  $this->getAll($where,$data,$data['page'],0,'buy_log');
        foreach ($buy_log['list'] as $v){
            if($type == 1){
                if($v['audio_img_id']){
                    $img_id = $v['audio_img_id'];
                }else{
                    $img_id = $v['head_icon'];
                }
                $path = $this->getImagePath($img_id);
                $v["img_filename"] = $path['path'];
            }
            $v['audio_length'] = $this->getShiftTime($v['audio_length']);
            $v['study_num'] = $this->convertTenThousandNum($v['study_num']);
            //观看的百分比
            $watch_record = $this->getOne(array('user_id' => $_SESSION['user_id'],'audio_id' => $v['audio_id']),array('id','time'),'watch_record');
            if(strpos($v['audio_length'],"时")){
                $str = preg_replace('/([\d]+)时([\d]+)分([\d]+)秒/', '$1:$2:$3', $v['audio_length']);
            }else{
                $str = preg_replace('/([\d]+)分([\d]+)秒/', '00:$1:$2', $v['audio_length']);
            }
            $parsed = date_parse($str);
            $v['count'] = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
            $v['count'] = $v['count'] ? round($watch_record['time']/$v['count']*100, 0). "%"  : "";
            if($v['sell_type'] == 2){
                $v['price'] = "仅限会员";
            }else{
                $v['price'] = $v['price'] == '0.00' ? "免费" : $v['price']."元";
            }
            $v['original_price'] = $v['original_price'] ==  '0.00' ? "免费" : $v['original_price']."元";
        }
       
        if($type == 1 && $buy_log){
            foreach ($buy_log['list'] as $v){
                $v['timestamp'] = date('m-d',strtotime($v['timestamp']));               
            }
        }
        
        return  $buy_log ? $buy_log : array();
    }
    
    public  function ajaxPresentRecordList(){
        $type = isset($_POST['num']) ? $_POST['num'] : 1;
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        if($type == 5){
            $data = array(
                'columns' => array(
                    '*'
                ),
                'need_page' => true,
                'page' => $page
            );
            $list = $this->getAll(array('user_id' => $_SESSION['user_id'],'delete' => 0,'type' => 5),$data,$data['page'],0,'giving_log');
            return $list['list'] ? $list['list'] : array();
        }
        if($type == 1){
            $collect_log_one = 1;
            $collect_log_two = 3;
        }else{
            $collect_log_one = 2;
            $collect_log_two = 4;
        }
        $sql = "SELECT `nb_giving_log`.*, `nb_audio`.`id` AS `audio_id`, `nb_audio`.`title` AS `audio_name` , `nb_audio`.`study_num` AS `study_num` ,`nb_audio`.`audio_length` AS `audio_length`, `nb_audio`.`image` AS `image` , `nb_audio`.`teacher_id` AS `teacher_id` , NULL AS `audio_num`  FROM `nb_giving_log` LEFT JOIN `nb_audio` ON `nb_audio`.`id` = `nb_giving_log`.`audio_id` WHERE ((`nb_giving_log`.`type` = `nb_audio`.`type`)  AND `nb_giving_log`.`type` = ".$collect_log_one."  AND `nb_giving_log`.`user_id` = ".$_SESSION['user_id'].") UNION SELECT `nb_giving_log`.*, `nb_courses`.`id` AS `courses_id`, `nb_courses`.`title` AS `audio_name` , `nb_courses`.`study_num` AS `study_num` , NULL , `nb_courses`.`image` AS `image` , NULL, `nb_courses`.`audios_num` AS `audios_num` FROM `nb_giving_log` LEFT JOIN `nb_courses` ON `nb_courses`.`id` = `nb_giving_log`.`audio_id` WHERE ((`nb_giving_log`.`type` = `nb_courses`.`type`) AND `nb_giving_log`.`type` = ".$collect_log_two." AND `nb_giving_log`.`user_id` = ".$_SESSION['user_id'].") ORDER BY `type` DESC"; 
        $num = 10;
        $offset = ($page-1)*$num;
        $sql = $sql ." LIMIT " . $offset .','. $num;
        $list = $this->executeSql($sql);
        if($list['list']){
            foreach ($list['list'] as $v){
                $v['timestamp'] = date('m-d',strtotime($v['timestamp']));
                if($v['type'] == 2 || $v['type'] == 1){
                    $teacher = $this->getOne(array('id'=>$v['teacher_id']),array('name','head_icon'),'teacher');
                    if($v['type'] == 2){
                        $img = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                    }else{
                        $img = $this->getOne(array('id'=>$teacher['head_icon']),array('path','filename'),'image');
                    }
                    $v['teacher_name'] = $teacher['name'];
                    $v['img'] = ROOT_PATH.UPLOAD_PATH.$img['path'].$img['filename'];
                }else if($v['type'] == 3 || $v['type'] == 4){
                    $img = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                    $v['img'] = ROOT_PATH.UPLOAD_PATH.$img['path'].$img['filename'];
                }
            }
        }
        return $list['list'] ? $list['list'] : array();
    }
    
    //赠送详情
    public function PresentRecordDetails($id){
        $code = isset($_GET['code']) ? $_GET['code'] : "";
        if($code){
            $data['code'] = 200;
            $data['qrcode'] = 'qrCode';
            return $data ? $data : array();
        }
        if(!$id){
            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        }
        if(!$id){
            return array(
                'code' => 400,
                'message' => '查询失败！',
            ); 
        }
        $data = $this->getOne(array('id' => $id),array('*'),'giving_log');
        $user_data = $this->getOne(array('id' => $data['user_id']),array('name'),'user');
        $data['share_user_name'] = $user_data['name'];
        //统计分享数量
        $totay_time = date('Y-m-d H:i',time());
        $date_time = date('Y-m-d',time());
        $visitor = $this->getOne(array('share_type'=>$data['type'],'date_time' => $date_time,'type' => 3,'user_id' => $_SESSION['user_id']),array('id'),'data_statistics');
        if(!$visitor){
            $this->insertData(array(
                'type' => 3,
                'value' => 1,
                'date' => $totay_time,
                'date_time' => $date_time,
                'user_id' => $_SESSION['user_id'],
                'share_type' => $data['type'],
                'timestamp' => $this->getTime(),
            ),'data_statistics');   
        }  
        
        $select_data = array(
            'join' => array(
               array(
                    'name' => DB_PREFIX . 'user',
                    'on' => DB_PREFIX . 'user.id = ' . DB_PREFIX . 'get_course.user_id',
                    'columns' => array(
                        'user_id '=> 'id',
                        'user_name' => 'name',
                        'user_img_id' => 'img_id',
                        'user_img_path' => 'img_path',
                        'user_img_head_icon' => 'head_icon',
                    ),
                    'type'=>'left'
                ),
            ),
            'order' => array(
                DB_PREFIX.'get_course.id' => 'DESC',
            ),
            'columns' => array('*'),
            'need_page' => false,
        );
        $where = new Where();
        $where->equalTo('giving_id', $id);
        $courses_list = $this->getAll($where,$select_data,$select_data['need_page'],PAGE_NUMBER,'get_course');
        if($courses_list['list']){
            foreach ($courses_list['list'] as $v){
                $data['user_ids'][] = $v['user_id'];
                if($v['user_img_id'] && $v['user_img_path']){
                    $v['user_img_head_icon'] = ROOT_PATH.UPLOAD_PATH.$v['user_img_path'];
                }
            } 
            $data['courses_list'] = $courses_list['list'];
        }
        if(!isset($data['user_ids'])){
            $data['user_ids'] = array();
        }
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
        
        if($type == 'member'){
            $data['code'] = 200;
            $data['return_type'] = 'member';
            return $data ? $data : array();
        }else{
            $data['return_type'] = '';
        }
        if($data['type'] == 1 || $data['type'] == 2){
            $data['audio'] = $this->getOne(array('id' => $data['audio_id']),array('id','type','title','audio_length','teacher_id','image'),'audio');
            $teacher = $this->getOne(array('id' => $data['audio']['teacher_id']),array('id','head_icon'),'teacher');
            if($data['type'] == 2){
                $img = $this->getImagePath($data['audio']['image']);
            }else{
                $img = $this->getImagePath($teacher['head_icon']);
            }
            $data['audio']['img'] = ROOT_PATH.UPLOAD_PATH.$img['path'];
            $data['code'] = 200;
            return $data ? $data : array();
        }else if($data['type'] == 3 || $data['type'] == 4){
            $data['audio'] = $this->getOne(array('id' => $data['audio_id']),array('id','type','title','audios_num','image'),'courses');
            $img = $this->getImagePath($data['audio']['image']);
            $data['audio']['img'] = ROOT_PATH.UPLOAD_PATH.$img['path'];
            $data['code'] = 200;
            return $data ? $data : array();
        }
    }
    
    public function ajaxGetmessageInform(){
        $page = $_POST['page'] ? $_POST['page'] : 1;
        $user_info = $this->getOne(array('id'=>$_SESSION['user_id']),array('*'),'user');
        $this->updateData(array('is_new' => 1),array('user_id' => $_SESSION['user_id']),'notification_subscibe');
        $this->updateData(array('notification_num' => 0),array('id' => $_SESSION['user_id']),'user');
        $this->updateData(array('is_new' => 1),array('user_id' => $_SESSION['user_id']),'notification_feedback');
        $sql = "(SELECT `id` ,NULL AS audio_id,NULL AS type,`title`,`timestamp`,1 AS `message_type`, NULL as is_new FROM nb_notification_system WHERE `delete` = 0 AND `send_status`=2 AND(`position` = ".$user_info['position']." OR `position` = 0) ORDER BY `timestamp` DESC LIMIT 1)
                UNION
                (SELECT `id`,NULL AS audio_id,NULL AS type,`content` AS title,`reply_time` AS `timestamp`,2 AS `message_type` ,NULL as is_new FROM nb_notification_feedback WHERE `delete` = 0 AND `reply_status` = 1 AND `user_id` = ".$user_info['id']." ORDER BY `timestamp` DESC LIMIT 1)
                UNION
                (SELECT `id`,`audio_id`,`type`,NULL AS `title`,`timestamp`,3 AS `message_type`,is_new FROM nb_notification_subscibe WHERE `delete` = 0 AND `user_id` = ".$user_info['id'].") ORDER BY `timestamp` DESC";
        $num = 10;
        $offset = ($page-1)*$num;
        $sql = $sql ." LIMIT " . $offset .','. $num;
        $list = $this->executeSql($sql); 
        
        if($list['list'])
        {
            foreach ($list['list'] as $v)
            {
                if($v['message_type'] == 3)
                {
                    $audio_info = $this->getOne(array('id'=>$v['audio_id']),array('*'),'audio');
                    $teacher = $this->getOne(array('id'=>$audio_info['teacher_id']),array('*'),'teacher');
                    $head_icon = $this->getOne(array('id'=>$teacher['head_icon']),array('path','filename'),'image');
                    $v['title'] = $audio_info['title'];
                    $v['teacher_name'] = $teacher ? $teacher['name'] : "";
                    $v['teacher_img'] = $head_icon ? ROOT_PATH.UPLOAD_PATH.$head_icon['path'].$head_icon['filename'] : "";
                }
                if($v['message_type'] == 2)
                {
                    $v['total'] = $this->countData(array('user_id'=>$user_info['id'],'is_new'=>2,'delete'=>0,'reply_status'=>1),'notification_feedback');
                }else{
                    $v['notification_num'] = $user_info['notification_num'];
                }
            }
        }
        return array('code'=>200,'data'=>$list['list'] ? $list['list'] : array());
    }
    
    public function ajaxGetCourse(){
        //领取礼包
        $type = isset($_POST['type']) ? $_POST['type'] : 0; 
        $get_id = isset($_POST['get_id']) ? $_POST['get_id'] : 0;
        $audio_id = isset($_POST['audio_id']) ? $_POST['audio_id'] : 0;
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;
        $giving = $this->getOne(array('id' => $get_id),array('*'),'giving_log');
        if($giving['num'] - $giving['remain_num'] == 0){
            return array(
                'code' => 400,
                'message' => '该礼包领取完了！'
            );
        }   
        if(!$type || !$get_id || !$audio_id || !$user_id){
            return array(
                'code' => 400,
                'message' => '领取失败！'
            );
        }else{
            if($type == 3 || $type == 4){
               $data = $this->getOne(array('id' => $audio_id),array('audios_ids'),'courses');
               $ids = array_filter(explode(',', $data['audios_ids']));
               foreach ($ids as $v){
                   $video_data = $this->getOne(array('id' => $v,'status' => 1),array('*'),'audio');
                   if($video_data){
                       $but_data = array(
                           'user_id' => $user_id,
                           'audio_id' => $v,
                           'is_giving' => 2,
                           'delete' => 0,
                           'tiemstamp_update' => $this->getTime(),
                           'timestamp' => $this->getTime(),
                       );
                       $this->insertData($but_data,'buy_log');
                   }
               }
            }else{
                $but_data = array(
                    'user_id' => $user_id,
                    'audio_id' => $audio_id,
                    'is_giving' => 2,
                    'delete' => 0,
                    'tiemstamp_update' => $this->getTime(),
                    'timestamp' => $this->getTime(),
                );
                $this->insertData($but_data,'buy_log');
            }    
            $get_coure = array(
                'giving_id' => $get_id,
                'user_id' => $user_id,
                'timestamp' => $this->getTime(),
            );
            $this->insertData($get_coure,'get_course');
           
            $this->updateKey(array('id'=> $get_id), 1, 'remain_num', 1,'giving_log');
            return array(
                'code' => 200,
                'message' => '领取失败！'
            );
        }
    }
    
    public function ajaxGetPresentMbmber(){
        //领取礼包
        $get_id = isset($_POST['get_id']) ? $_POST['get_id'] : 0;
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;
        if(!$get_id || !$user_id){
            return array(
                'code' => 400,
                'message' => '领取失败！'
            );
        }else{
            $giving = $this->getOne(array('id' => $get_id),array('*'),'giving_log');
            if($giving['num'] - $giving['remain_num'] == 0){
                return array(
                    'code' => 400,
                    'message' => '该礼包领取完了！'
                );
            }
            $price = $giving['price']/$giving['num'];
            $get_coure = array(
                'giving_id' => $get_id,
                'user_id' => $user_id,
                'timestamp' => $this->getTime(),
            );
           
            //记录成为会员
           $member = $this->getOne(array('type' => 1),array('*'),'member_set');
           $time = date('Y-m-d H:i:s',time() + 24 * 3600 * $member['number']);
           $user = $this->getOne(array('id' => $user_id),array('*'),'user');
           
           //冻结
           if(time() > strtotime($user['member_time'])){
               //非会员情况
               $this->updateData(array('freeze_amount' => 0),array('id' => $user['id']),'user');
               if($price+$user['amount'] >= $member['price']){
                   $user_freeze = $this->getOne(array('id' => $user['id']),array('freeze_amount','id'),'user');
                   $this->updateData(array('freeze_amount' => $member['price']),array('id' => $user_freeze['id']),'user');
               }
           }
           if($user['member_time'] <= date('Y-m-d H:i:s',time()) && $member['price'] <= $price){
               $this->updateData(array('member_time' => $time,'open_member_time' => date('Y-m-d H:i:s',time())), array('id' => $_SESSION['user_id']),'user');
               $arr = array(
                   'member_time' => $time,
                   'open_member_time' => date('Y-m-d H:i:s',time()),
                   'user_id' => $_SESSION['user_id'],
                   'type' => 4,
                   'value' => 1,
                   'date' => date('Y-m-d H:i',time()),
                   'date_time' => date('Y-m-d',time()),
                   'timestamp' =>$this->getTime()
                   );
               $this->insertData($arr,'data_statistics'); //记录开通会员情况，便于统计会员数
               if(SEND_KEY){
                   $send_data = array(
                       'price' => $price,
                       'open_time' => date('Y-m-d',time()),
                       'over_time' => date('Y-m-d',time() + 24 * 3600 * $member['number'])
                   );
                   $this->sendTempMessage($user['open_id'],$send_data);
               }
           }
            
           $this->updateKey($user_id, 1, 'amount', $price,'user');
           $financial_data = array(
               'type' => 5,
               'amount' => $price,
               'income' => 1,
               'transfer_no' => $this->makeSN(),
               'transfer_way' => 2,
               'remark' => '领取会员',
               'user_id' => $_SESSION['user_id'],
               'vip_pay' => $_SESSION['is_vip'],
               'pay_log_id' => 0,
               'delete' => 0,
               'timestamp_update' => $this->getTime(),
               'timestamp' => $this->getTime(),
           );
           $this->insertData($financial_data,'financial');
           $row = $this->insertData($get_coure,'get_course');
           $this->updateKey(array('id'=> $get_id), 1, 'remain_num', 1,'giving_log');
           if($row){
               return array(
                   'code' => 200,
                   'message' => '领取成功！'
               );
           }else{
               return array(
                   'code' => 400,
                   'message' => '领取成功！'
               );
           }
        }
    }
    
    public function ajaxSystemMassage()
    {
        $user_info = $this->getOne(array('id'=>$_SESSION['user_id']),array('*'),'user');
        $page = $_POST['page'] ? $_POST['page'] : 1;
        $data = array(
            'columns' => array('*'),
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'image',
                    'on' => DB_PREFIX . 'notification_system.image = ' . DB_PREFIX . 'image.id',
                    'columns' => array(
                        'path',
                        'filename',
                    ),
                    'type'=>'left'
                ),
            ),
            'need_page' => true,
            'limit' => PAGE_NUMBER,
        );
        $where_activated = new Where();
        $where_activated->equalTo('position', 0)->equalTo('delete', 0)->equalTo('send_status', 2);
        $where_active = new Where();
        $where_active->equalTo('position', $user_info['position'])->equalTo('delete', 0);
        $where_active->orPredicate($where_activated);
        
        $data = $this->getAll($where_active,$data,$page,PAGE_NUMBER,'notification_system');
        $this->updateData(array('notification_num' => 0),array('id' => $_SESSION['user_id']),'user');
        return array('code'=>200,'data'=>$data['list']);
    }
    
    public function ajaxIdeaFeedback()
    {
        $page = $_POST['page'] ? $_POST['page'] : 1;
        $data = array(
            'columns' => array('*'),
            'need_page' => true,
            'limit' => PAGE_NUMBER,
        );
        $data = $this->getAll(array('user_id'=>$_SESSION['user_id'],'reply_status'=>1,'delete'=>0),$data,$page,PAGE_NUMBER,'notification_feedback');
        if($data['total'])
        {
            foreach ($data['list'] as $v)
            {
                $this->updateData(array('is_new'=>1), array('id'=>$v['id']),'notification_feedback');
            }
        }
        return array('code'=>200,'data'=>$data['list']);
    }
    
    public function ajaxWatchRecord()
    {
        $page = $_POST['page'] ? $_POST['page'] : 1;
        $type = $_POST['audio_type'] ? $_POST['audio_type'] : 1;
        if(!$type)
        {
            return array('code'=>400,'message'=>'参数错误');
        }
        $data = array(
            'columns' => array('record_id'=>'id','audio_id','time'),
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'audio',
                    'on' => DB_PREFIX . 'watch_record.audio_id = ' . DB_PREFIX . 'audio.id',
                    'columns' => array(
                        'id',
                        'teacher_id',
                        'title',
                        'image',
                        'auditions_path',
                        'full_path'
                    ),
                    'type'=>'left'
                ),
                array(
                    'name' => DB_PREFIX . 'teacher',
                    'on' => DB_PREFIX . 'audio.teacher_id = ' . DB_PREFIX . 'teacher.id',
                    'columns' => array(
                        'teacher_name' => 'name',
                        'head_icon'
                    ),
                    'type'=>'left'
                ),
            ),
            'need_page' => true,
            'limit' => PAGE_NUMBER,
        );
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'watch_record.delete', 0);
        $where->equalTo(DB_PREFIX . 'audio.status',1);
        $where->equalTo(DB_PREFIX . 'audio.delete',0);
        $where->equalTo(DB_PREFIX . 'watch_record.user_id', $_SESSION['user_id'])->equalTo(DB_PREFIX . 'audio.type', $type);
        $data = $this->getAll($where,$data,$page,PAGE_NUMBER,'watch_record');
        if($data['total'])
        {
            foreach ($data['list'] as $v)
            {
                if($v['image'])
                {
                    $image = $this->getOne(array('id'=>$v['image']),array('path','filename'),'image');
                    $v['image'] = $image ? ROOT_PATH.UPLOAD_PATH.$image['path'].$image['filename'] : "";
                }
                else 
                {
                    $image = $this->getOne(array('id'=>$v['head_icon']),array('path','filename'),'image');
                    $v['image'] = $image ? ROOT_PATH.UPLOAD_PATH.$image['path'].$image['filename'] : "";
                }
            }
        }
        return array('code'=>200,'data'=>$data['list']);
    }
    
    public function deleteRecord()
    {
        $id = $_POST['id'] ? $_POST['id'] : 0;
        if(!$id)
        {
            return array('code'=>400,'message'=>'参数错误');
        }
        $res = $this->deleteData(array('user_id'=>$_SESSION['user_id'],'id'=>$id),'watch_record',true);
        if($res)
        {
            return array('code'=>200,'message'=>'删除成功');
        }
        else
        {
            return array('code'=>400,'message'=>'删除失败');
        }
    }
    
    public function deleteAllRecord()
    {
        $type = $_POST['type'] ? $_POST['type'] : 0;
        if(!$type)
        {
            return array('code'=>400,'message'=>'参数错误');
        }
        $res = $this->deleteData(array('user_id'=>$_SESSION['user_id'],'type'=>$type),'watch_record',true);
        if($res)
        {
            return array('code'=>200,'message'=>'删除成功');
        }
        else
        {
            return array('code'=>400,'message'=>'删除失败');
        }
    }
    
    public function getMember(){
        $data = $this->getOne(array('type' => 1),array('*'),'member_set');
        $data['user'] = $this->getOne(array('id' =>$_SESSION['user_id'],'status' => 1),array('*'),'user');
        if($data){
            $data['img_path'] = ROOT_PATH.UPLOAD_PATH.$data['img_path'];
            return $data;
        }else{
            return array();
        }   
    }

    public function getRegion()
    {
        $deep = $_REQUEST['deep'] ? $_REQUEST['deep'] : 1;
        $where = array('deep' => 1);
        $data = $this->getAll($where, array(
            'columns' => array(
                'id',
                'name'
            ),
            'need_page' => false,
            'order' => array(
                'id' => 'ASC'
            )
        ), 1, PAGE_NUMBER, 'region');
        return $data;
    }
    
    public function getSonRegion()
    {
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 1;
        $data = $this->getAll(array('deep' => 2,'parent_id' => $id), array(
            'columns' => array(
                'id',
                'name'
            ),
            'need_page' => false,
            'order' => array(
                'id' => 'ASC'
            )
        ), 1, PAGE_NUMBER, 'region');
        $parent = $this->getOne(array('id' => $id),array('name'),'region');
        $data['parent_name'] = $parent['name'];
        return $data;
    }
    
    public function ajaxAddRegion()
    {
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 1;
        $loca_name = isset($_REQUEST['location_name']) ? $_REQUEST['location_name'] : "";
        if($loca_name){
            $loca_name = explode(" ",$loca_name);
            $where = new Where();
            $where->like('name', $loca_name[2]);
            $region_data = $this->getOne(array('name' => $loca_name[2]),array('id'),'region');
            $id = $region_data['id'];
        }
        $user_id = $_SESSION['user_id'];
        $region = $this->getRegionInfo($id);
        $this->updateData(array('region_id' => $region['city'],'region_info' => $region['region_info']), array('id' => $user_id),'user');
        return array(
            'code' => 200,
            'message' => '修改成功'
        );
    }
    
    public function ajaxRecharge(){
        $price = $_POST['price'] ? $_POST['price'] : 0;
        $obj = isset($_POST['obj']) ? $_POST['obj'] : array();
        if($obj){
            if(IS_DEBUG == 1){
                return array(
                    'code' => 400,
                    'message' => '测试服务器无法调起支付'
                );
            }
            $member = $this->getOne(array('type' => 1),array('*'),'member_set');
            if($member){
                if($price <= $member['price']){
                    $price = $member['price'];
                }
            }else{
                return array(
                    'code' => 400,
                    'message' => '充值失败！',
                );
            }
        }
        if(!$price){
            return array(
                'code' => 400,
                'message' => '充值失败！',
            );
        }
        $data = array(
            'type' => 3,
            'pay_type' => 1,  
            'number' => 1,
            'genre' => 0,
            'audio_id' => 0,
            'amount' => $price,
            'status' => 2,
            'transfer_no' => $this->makeSN()."-3",
            'transfer_way' => 1,
            'user_id' => $_SESSION['user_id'],
            'pay_video' => json_encode($obj),
            'vip_pay' =>$_SESSION['is_vip'],
            'delete' => 0,
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime(),
        );
        $row = $this->insertData($data,'pay_log');
        if(IS_DEBUG == 1){
            $user_price = $this->getOne(array('id' => $data['user_id']),array('amount','id','member_time'),'user');
            if(time() > strtotime($user_price['member_time'])){
                //非会员情况
                $this->updateData(array('freeze_amount' => 0),array('id' => $user_price['id']),'user');
                $member = $this->getOne(array('type' => 1),array('*'),'member_set');
                if($data['amount']+$user_price['amount'] >= $member['price']){
                    $user_freeze = $this->getOne(array('id' => $data['user_id']),array('freeze_amount','id'),'user');
                    $this->updateData(array('freeze_amount' => $member['price']),array('id' => $user_freeze['id']),'user');
                }
            }
            $user = $this->updateKey($data['user_id'], 1, 'amount',$data['amount'],'user');
            $this->updateData(array('status' => 1), array('transfer_no' => $data['transfer_no']),'pay_log');
            //财务表修改
            $financial_data = array(
                'type' => 3,
                'amount' => $data['amount'],
                'income' => 1,
                'transfer_no' => $this->makeSN(),
                'transfer_way' => 1,
                'remark' => '',
                'user_id' => $data['user_id'],
                'pay_log_id' => $row,
                'vip_pay' => $_SESSION['is_vip'],
                'delete' => 0,
                'timestamp_update' => $this->getTime(),
                'timestamp' => $this->getTime(),
            );
            $this->insertData($financial_data,'financial');
        }
        if($row){
            return array(
                'code' => 200,
                'message' => '一起聚餐充值',
                'price' => $data['amount'],
                'out_trade_no' => $data['transfer_no'],
            );
        }else{
            return array(
                'code' => 400,
                'message' => '充值失败'
            );
        }
    }
    
    //会员
    public function ajaxMember(){
        $price = $_POST['price'] ? $_POST['price'] : 0;
        $type = $_POST['type'] ? $_POST['type'] : 0;
        $num = isset($_POST['num']) ? $_POST['num'] : 1;
        $pay_ment = isset($_POST['pay_ment']) ? $_POST['pay_ment'] : 0;
        $pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : 1;
        if(!$price && $pay_ment){
            return array(
                'code' => 400,
                'message' => '购买会员失败！',
            );
        }
        $data = array(
            'type' => 2,
            'pay_type' => $pay_type,
            'number' => $num,
            'genre' => 0,
            'audio_id' => 0,
            'amount' => $price,
            'status' => 2,
            'transfer_no' => $this->makeSN().'-2',
            'transfer_way' => $pay_ment,
            'vip_pay' =>$_SESSION['is_vip'],
            'user_id' => $_SESSION['user_id'],
            'delete' => 0,
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime(),
        );
        $row = $this->insertData($data,'pay_log');
        if($pay_ment == 1){
            return array(
                'code' => 200,
                'pay_ment' => 1,
                'message' => '一起聚餐会员购买',
                'price' => $data['amount'],
                'out_trade_no' => $data['transfer_no'],
            );
        }
        if($row){
            //财务表修改
            $user = $this->getOne(array('id' => $_SESSION['user_id']),array('*'),'user');
            if($user['amount'] < $data['amount']){
                return array(
                    'code' => 400,
                    'message' => '购买会员失败！',
                );
            }
            $financial_data = array(
                'type' => 4,
                'amount' => $data['amount'],
                'income' => 2,
                'transfer_no' => $this->makeSN(),
                'transfer_way' => 2,
                'remark' => '',
                'user_id' => $_SESSION['user_id'],
                'pay_log_id' => $row,
                'vip_pay' => $_SESSION['is_vip'],
                'delete' => 0,
                'timestamp_update' => $this->getTime(),
                'timestamp' => $this->getTime(),
            );
            $this->insertData($financial_data,'financial');
            if($pay_type == 2){
                //赠送会员
                $giving = array(
                    'user_id' => $_SESSION['user_id'],
                    'audio_id' => 0,
                    'type' => 5,
                    'num' => $data['number'],
                    'remain_num' => 0,
                    'price' => $data['amount'],
                    'delete' => 0,
                    'timestamp' => $this->getTime(),
                );
                $giv_id = $this->insertData($giving,'giving_log');
            }
            //余额支付
            $user = $this->updateKey($_SESSION['user_id'], 2, 'amount',$data['amount'],'user');
            $this->updateKey($_SESSION['user_id'], 1, 'consumption',$data['amount'],'user');
            $pay_log = $this->updateData(array('status' => 1), array('transfer_no' => $data['transfer_no']),'pay_log');
            return array(
                'code' => 200,
                'pay_ment' => 2,
                'message' => '购买成功',
                'giv_id' => $giv_id
            );
        }
    }
    //课程购买
    public function ajaxWxCoursePay($data=array()){
        if(IS_DEBUG == 1){
            return array(
                'code' => 400,
                'message' => '测试服务器无法调起支付'
            );
        }
        if(!$data){
            return array(
                'code' => 400,
                'message' => '课程购买失败！',
            );
        }else{
            $price = $data['pay_price'];
            $pay_type = $data['pay_type'];
            $num = $data['number'];
            $transfer_way = $data['transfer_way'];
            $user_id = $data['user_id'] ? $data['user_id'] : $_SESSION['user_id'];
            $id = $data['audio_id'];     
            $genre = $data['genre'];
            $type = $data['audio_type'];
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
            'genre' => $genre,
            'audio_id' => $id,
            'amount' => $price,
            'audio_type' => $type,
            'status' => 2,
            'transfer_no' => $this->makeSN().'-1',
            'transfer_way' => $transfer_way,
            'user_id' => $user_id,
            'vip_pay' =>$_SESSION['is_vip'],
            'delete' => 0,
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime(),
        );
        $row = $this->insertData($data,'pay_log');
        return array(
            'code' => 200,
            'message' => '一起聚餐课程购买',
            'price' => $data['amount'],
            'out_trade_no' => $data['transfer_no'],
        );
    }
    
    public function getSetup()
    {
        $setup = $this->getOne(array('type' => 2),array('id','content'),'setup');
        return array('code' => 200,'setup' => $setup ? $setup['content'] : "");
    }
    
    public function getLocationInfo(){
        $latitude = $_POST['latitude'] ? $_POST['latitude'] : 0;
        $longitude = $_POST['longitude'] ? $_POST['longitude'] : 0;
        $accuracy = $_POST['accuracy'] ? $_POST['accuracy'] : 0;;
        $location = $longitude.','.$latitude;
        $locationInfo = $this->getLocation($location);
        $locationInfo = $locationInfo['regeocode']['addressComponent'];
        if($locationInfo){
            $_SESSION['location'] = $locationInfo['country'].' '.$locationInfo['province'] . ' ' . $locationInfo['city'];
        }else{
            $_SESSION['location'] = "定位失败";
        }
        return array(
            'code' => 200,
            'location' => $location
        );
    }
    
    //根据经纬度获取地址
    public function getLocation($location)
    {
        $url = 'http://restapi.amap.com/v3/geocode/regeo?output=json&location='.$location.'&key='.AMAP_KEY;
        $data = file_get_contents($url);
        return json_decode($data, true);
    }
    
    public function ajaxUpdateUserInfo()
    {
        $mobile = $_POST['mobile'];
        $position_id = $_POST['position_id'];
        $region_id = $_POST['region_id'];
        if($mobile && $position_id && $region_id)
        {
            $region_name = explode(' ', $region_id);
            $where = new Where();
            $where->like('name', $region_name[1]);
            $region_data = $this->getOne(array('name' => $region_name[1]),array('id'),'region');
            if($region_data['id']){
                $region_id = $region_data['id'];
            }else{
                return array('code' => 400,'message'=>'更新失败');
            }
            $region = $this->getRegionInfo($region_id);
            $set = array('mobile' => $mobile , 'position' => $position_id , 'region_id' => $region_id,'binding_status'=>1,'binding_time'=>date('Y-m-d H:i:s'));
            $set['region_info'] = $region['region_info'];
            $res = $this->updateData($set, array('id'=>$_SESSION['user_id']),'user');
            if($res)
            {
                return array('code' => 200);
            }
            else 
            {
                return array('code' => 400,'message'=>'更新失败');
            }
        }
        else
        {
            return array('code' => 400,'message'=>'更新失败');
        }
    }

    /*
     *
     * 充值到钱包或者提现到微信
     *
     * */
    public function ajaxRechargeWallet($type){
        $this->adapter->getDriver()->getConnection()->beginTransaction();//开启事务
        $price = $_POST['price'] ? $_POST['price'] : 0;
        //查询当前用户的佣金余额情况
        $user = $this->getOne(array('id' => $_SESSION['user_id']),array('id','name','brokerage','amount','member_time','open_id'),'user');
        if(!$user){
            return array(false,'该用户不存在！');
        }
        //判断提交过来的金额是否大于佣金金额
        if(!$price){
            $this->adapter->getDriver()->getConnection()->rollback();
            return array(false,'金额不能为空！');
        }else if($price > $user['brokerage']){
            $this->adapter->getDriver()->getConnection()->rollback();
            return array(false,'金额不能大于佣金金额！');
        }
        if($type == 2){
            if($price < 1){
                $this->adapter->getDriver()->getConnection()->rollback();
                return array(false,'提现金额不能低于1元！');
            }
        }
        if($type == 1){
            //充值佣金的金额到钱包
            $plus = $this->updateKey($_SESSION['user_id'],1,'amount',$price,'user');
            $minus = $this->updateKey($_SESSION['user_id'],2,'brokerage',$price,'user');
            if($plus && $minus){
                if(!$this->addBrokerageLog($type,$price)){
                    $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                    return array(false,'操作失败！');
                }else{
                    $this->adapter->getDriver()->getConnection()->commit();//提交事务
                    return array(true,'ok！');
                }
            }else{
                $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                return array(false,'操作失败！');
            }
        }else if($type == 2){
            //微信下发红包
            if(strtotime($user['member_time']) <= time()){
                $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                return array(false,'会员才能够提现！');
            }else{
                $log = new Log('sendRedBag');
                if($user['open_id']){
                    $log->info('一起聚餐-->'.$user['name'] . '<--用户发起提现申请,该用户open_id为-->'.$user['open_id'].'<--提现金额为-->'.$price);
                    if(IS_DEBUG == 2){
                        $hb = new WXHongBao();
                        //初始化数据
                        list($status,$msg) = $hb->newhb($user['open_id'] ,$price*100 ,$_SERVER["REMOTE_ADDR"]);
                        if($status){
                            list($resut,$message) = $hb->send();
                        }else{
                            return array(false,$msg);
                        }
                    }else{
                        $resut = true;
                    }
                    if($resut){
                        if(!$this->addBrokerageLog($type,$price)){
                            $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                            return array(false,'操作失败！');
                        }else{
                            $minus = $this->updateKey($_SESSION['user_id'],2,'brokerage',$price,'user');
                            $this->adapter->getDriver()->getConnection()->commit();//提交事务
                            return array(true,'ok！');
                        }
                    }else{
                        $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                        return array(false,$message);
                    }
                }else{
                    $log->err('提现到微信,用户数据异常。用户id为：'.$user['id']);
                    $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
                    return array(false,'用户信息异常！');
                }

            }
        }else{
            return array(false,'操作失败！');
        }
    }

    //添加佣金记录表
    public function addBrokerageLog($type,$price){
        $set = array(
            'user_id' => $_SESSION['user_id'],
            'amount' => $price,
            'income' => 2,
            'type' => $type==1 ? 2 : 3,
        );
        $log = new Log('web');
        $row = $this->insertData($set,'brokerage_log');
        if($row){
            if($type = 1){
                //添加到财务表
                if(!$this->addPayLog($price)){
                    return false;
                }
            }
            return true;
        }else{
            return false;
        }
    }

    public function addPayLog($price){
        $data = array(
            'type' => 6,
            'amount' => $price,
            'status' => 1,
            'transfer_no' => $this->makeSN().'-8',
            'transfer_way' => 4,
            'vip_pay' => $_SESSION['is_vip'],
            'user_id' => $_SESSION['user_id'],
            'delete' => 0,
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime(),
        );
        $pay_log_id = $this->insertData($data, 'pay_log');
        if(!$pay_log_id){
            return false;
        }
        $financial_data = array(
            'type' => 8,
            'amount' => $price,
            'income' => 1,
            'transfer_no' => $this->makeSN(),
            'transfer_way' => 4,
            'remark' => ' 佣金充值到钱包',
            'user_id' => $_SESSION['user_id'],
            'pay_log_id' => $pay_log_id,
            'vip_pay' => $_SESSION['is_vip'],
            'delete' => 0,
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime(),
        );
        if(!$this->insertData($financial_data, 'financial')){
            return false;
        }
        return true;
    }

    /**
     * 合成头像信息
     */
    public function getImg()
    {
        //生成图片数据
        if(!$path = $this->compoundImg()){
            return false;
        }else{
            return $path;
        }
    }

    /**
     * 合成头像和二维码
     * @return mixed
     */
    public function compoundImg()
    {
        $log = new Log('web');
        //获取需要合成的图片信息
        list($status,$img, $bg_img_path, $logo_img,$bg_img_id,$user_img_id) = $this->getImageData();
        if(!$status) return false;
        if($status && $img) return $img;
        //不存在的话就即时生成
        $imgs = array(
            'dst' => LOCAL_SAVEPATH.$bg_img_path, //背景图
            'src' => $logo_img,
            'saveimg' => LOCAL_SAVEPATH . date('Ym/d/').time().'.jpg',
            'pos' => 10,
        );
        $img = new Compound();
        //合成头像信息
        $logo_path = $img->index($imgs);
        if(!$logo_path){
            $log->err(__CLASS__.'<-->'.__FUNCTION__.'缺少水印图片,行号为：'.__LINE__.'行');
            return false;
        }
        //生成二维码
        $value = DIS_QRCODE_URL.$_SESSION['user_id'];
        $qrcode = $this->getQrCode($imgs['dst'], $value,$_SESSION['user_id']);
        //合成二维码
        $path = date('Ym/d/');
        $fimename = time().rand(1,100);
        $code_imgs = array(
            'dst' => $logo_path,
            'src' => $qrcode,
            'saveimg' => LOCAL_SAVEPATH . $path . $fimename.'.jpg',
            'pos' => 9
        );
        $img_path = $img->mergerImg($code_imgs);
        unlink($code_imgs['dst']);
        $this->compoundTxt($img_path);
        if($img_path){
            $set = array(
                'user_id' => $_SESSION['user_id'],
                'img_id' => $bg_img_id,
                'head' => $user_img_id,
                'filename' => $fimename.'.jpg',
                'path' => $path,
                'timestamp' => $this->getTime(),
            );
            $this->insertData($set,'distribut_img');
        }
        $img_path = ROOT_PATH.UPLOAD_PATH. $path . $fimename.'.jpg';
        return $img_path;
    }

    //合成文字
    public function compoundTxt($path){
        if(!$path){
            return false;
        }
        $txt = new Image();
        $user = $this->getOne(array('id' => $_SESSION['user_id']), array('name'), 'user');
        $language = __DIR__ . "/../../../language/simhei.ttf";
        $txt_len = $txt->getFontSize(20,$language,'我是');
        $txt->str = $txt_len['0']/2*2.5;
        $name_len = $txt->getFontSize(20,$language,$user['name']);
        $txt->name_str = $name_len['0'];
        $data = $txt->watermark($path,'','txt','',$user['name'],'#FFFFFF',20,$language,10);
        $data = $txt->watermark($path,'','txt','','我是','#FFFFFF',20,$language,11);
        return $data;
    }

    //获取二维码数据
    public function getQrCode($path,$value,$user_id){
        $ext = pathinfo($path);
        include_once APP_PATH . '/vendor/Core/System/phpqrcode/phpqrcode.php';
        $path = $ext['dirname'].'/'.$user_id.'qrcode.jpg';
        \QRcode::png($value,$path, 'L',3,1);
        return $path;
    }

    /**
     * 获取合成图片信息
     * @return array
     */
    public function getImageData()
    {
        //接收参数
        $log = new Log('web');
        $type = isset($_POST['type']) ? $_POST['type'] : 0;
        $img = isset($_POST['img_id']) ? $_POST['img_id'] : 0;
        if (!$type && !$img) {
            $log->err('缺少type/缩略图id');
            return array(false,'','','','','');
        }
        //获取广告背景图
        $seach = array('big_img_paths', 'big_img_ids');
        $bg_img = $this->getOne(array(), $seach, 'distribut_set');
        $bg_img_path = explode(',', $bg_img['big_img_paths']);
        $bg_img_id = explode(',', $bg_img['big_img_ids']);
        $bg_img_path = $bg_img_path[$type];
        $bg_img_id = $bg_img_id[$type];
        //获取头像
        $user = $this->getOne(array('id' => $_SESSION['user_id']), array('name', 'id', 'img_id', 'head_icon', 'img_path'), 'user');
        if (!$user['img_id'] && !$user['head_icon']) {
            $log->err('缺少用户头像id/缺少用户微信头像信息');
            return array(false,'','','','','');
        } else if (!$user['img_id']) {
            $img_data = $this->saveImage($user['head_icon']);
//            $this->updateData(array('img_id' => $img_data['files'][0]['ajax']['id'], 'img_path' => $img_data['files'][0]['ajax']['path']), array('id' => $_SESSION['user_id']), 'user');
            $logo_img = LOCAL_SAVEPATH . $img_data['files'][0]['ajax']['path'];
            $user['img_id'] = $img_data['files'][0]['ajax']['id'];
        } else {
            $logo_img = LOCAL_SAVEPATH . $user['img_path'];
        }
        //查看数据是否存在，如果存在则拿数据表数据
        $wehre = array(
            'user_id' => $_SESSION['user_id'],
            'img_id' => $bg_img_id,
            'head' => $user['img_id']
        );
        $img_data = $this->getOne($wehre, array('id', 'filename', 'path'), 'distribut_img');
        if ($img_data) {
            $img = LOCAL_SAVEPATH . $img_data['path'] . $img_data['filename'];
            if(is_file($img)){
                $img = ROOT_PATH.UPLOAD_PATH. $img_data['path'] . $img_data['filename'];
                $log->info($_SESSION['user_id'].'用户获取'.$bg_img_id.'图片.该图片返回数据库数据,图片路劲：'.$img);
                return array(true,$img,'','',$bg_img_id,$user['img_id']);
            }
        }
        return array(true,'',$bg_img_path, $logo_img,$bg_img_id,$user['img_id']);
    }

    //查找分享详情页数据
    public function getDisData(){
        $data = array();
        $list = $this->getOne(array(),array('content'),'distribut_set');
        $member_set = $this->getOne(array('type' => 1),array('price'),'member_set');
        $data['content']  = $list['content'];
        $data['price']  = $member_set['price'];
        return $data;
    }

    /**
     * 我的推荐
     */
    public function getRecommend()
    {
        $page = $_POST['page'] ? $_POST['page'] : 1;
        $dis_data = array();
        $where = new where();
        $where->equalTo('first_user_id',$_SESSION['user_id']);
        $data = array(
            'columns' => array('user_id','first_user_id','stair_brokerage','second_brokerage','timestamp'),
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'user',
                    'on' => DB_PREFIX . 'user.id = ' . DB_PREFIX . 'distribut.user_id',
                    'columns' => array(
                        'name',
                        'img_path',
                        'head_icon',
                        'member_time'
                    ),
                ),
            ),
            'need_page' => true,
        );
        $dis_data = $this->getAll($where,$data,$page,PAGE_NUMBER,'distribut');
        foreach($dis_data['list'] as $v){
            $v['is_vip'] = 2;
            if($v['img_path']){
                $v['image_path'] = ROOT_PATH.UPLOAD_PATH.$v['img_path'];
            }else{
                $v['image_path'] = $v['head_icon'];
            }
            if(strtotime($v['member_time']) > time()){
                $v['is_vip'] = 1;
            }
            $v['price'] = $v['stair_brokerage'] + $v['second_brokerage'];
        }
        return array('code'=>200,'data'=>$dis_data['list']);
    }

    //获取用户推荐人数和金额
    public function getUserDetails(){
        $user = $this->getOne(array('id' =>$_SESSION['user_id']) ,array('stair_num','stair_brokerage','second_brokerage'),'user');
        $user['price'] = $user['stair_brokerage'] ? $user['stair_brokerage'] + $user['second_brokerage'] : '0.00';
        $user['num'] = $user['stair_num'] ? $user['stair_num'] : 0;
        return $user;
    }
}