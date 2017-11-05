<?php
namespace Web\Model;

use Zend\Db\Sql\Where;
use Core\System\AiiUtility\Log;
class IndexModel extends CommonModel
{
    protected $table = '';
    
    public  function getIndex($type = 1){
        $code = isset($_GET['code']) ? $_GET['code'] : "";
        $where = new Where();
        if($code && $code=='qrCode'){
          $where->equalTo('delete', 0)->equalTo('plate', $type);
        }else{
          $where->equalTo('delete', 0)->equalTo('status', 1)->equalTo('plate', $type);
          $where->lessThanOrEqualTo('start_time', date("Y-m-d H:i:s"));
          $where->greaterThanOrEqualTo('end_time', date("Y-m-d H:i:s"));
        }
        $ads =  $this->getAll($where,array('columns'=>array('id','sort','image','type','link','audio_id'),'order'=>array('sort ASC')),null,null,'ads');
        $where = new Where();
        $where->equalTo('delete', 0)->equalTo('status', 1)->equalTo('plate', 3)->lessThanOrEqualTo('start_time', date("Y-m-d H:i:s"))->greaterThanOrEqualTo('end_time', date("Y-m-d H:i:s"));
        $today_ads =  $this->getAll($where,array('columns'=>array('id','sort','image','type','link','audio_id'),'order'=>array('sort ASC'),'need_page'=>false),null,null,'ads');
        $home_manage = $this->setHomeManage();
        //首页免费音频获取
        $four_free_audio = $this->getHomePageSetting();
        $data = array(
          'ads' => $ads['list'],
          'home_manage' => $home_manage,
          'four_free_audios' => $four_free_audio,
          'today_ads' => $today_ads['list']
        );
        return $data;
    }
    
    public function articleDetails($id,$type)
    {
        if($type == 2){
            $data = $this->getOne(array('id'=>$id),array('*'),'ads');
        }else{
            $data = $this->getOne(array('id'=>$id),array('*'),'notification_system');
        }
       
        if(!$data)
        {
            return array('code'=>400,'message'=>'数据不存在');
        }
        return array('code'=>200,'data'=>$data);
    }
    
    //支付充值回调
    public function wxNotifyTransacting($id){
        $pay_video_data = array();
        $pay_log = $this->getOne(array('transfer_no' => $id,'status' => 2),array('*'),'pay_log');
        $type = explode('-', $id)[1];
        if($pay_log){
            if($pay_log['type'] == 3 && $type == 3){
                $this->becomeMember($pay_log);
                if($pay_log['pay_video']){
                    $pay_video_data = (array)json_decode($pay_log['pay_video']);
                    if(!isset($pay_video_data['pay_price']) || !$pay_video_data['pay_price']){
                        $pay_video_data = array();
                    }
                }
                $financial_type = 3;
                $financial_income = 1;
            }else if($pay_log['type'] == 2 && $type == 2){
                //购买会员
                if($pay_log['pay_type'] == 2){
                    //赠送会员
                    $giving = array(
                        'user_id' => $pay_log['user_id'],
                        'audio_id' => 0,
                        'type' => 5,
                        'num' => $pay_log['number'],
                        'remain_num' => 0,
                        'price' => $pay_log['amount'],
                        'delete' => 0,
                        'timestamp' => $this->getTime(),
                    );
                    $giv_id = $this->insertData($giving,'giving_log');
                }else{
                    $this->becomeMember($pay_log);
                }
                $financial_type = 4;
                $financial_income = 2;
            }else if($pay_log['type'] == 1 && $type == 1){
                $financial_type = 1;
                $financial_income = 2;
                if($pay_log['pay_type'] == 2){
                    //赠送的
                    $giving = array(
                        'user_id' => $pay_log['user_id'],
                        'audio_id' => $pay_log['audio_id'],
                        'type' => $pay_log['audio_type'],
                        'num' => $pay_log['number'],
                        'remain_num' => 0,
                        'price' => $pay_log['amount'],
                        'delete' => 0,
                        'timestamp' => $this->getTime(),
                    );
                    $giving_log_id = $this->insertData($giving,'giving_log');
                }else if($pay_log['pay_type'] == 1){
                    //余额支付  单买
                    if($pay_log['genre'] == 2){
                        $courese = $this->getOne(array('id' => $pay_log['audio_id']),array('*'),'courses');
                        $courses_ids = array_filter(explode(',', $courese['audios_ids']));
                        foreach ($courses_ids as $v){
                            $video_data = $this->getOne(array('id' => $v,'status' => 1),array('*'),'audio');
                            if($video_data){
                                $buy_data = array(
                                    'user_id' => $pay_log['user_id'],
                                    'audio_id' => $v,
                                    'is_giving' => 1,
                                    'delete' => 0,
                                    'timestamp' => $this->getTime(),
                                );
                                $this->insertData($buy_data,'buy_log');
                            }
                        }
                    }else{
                        $buy_log = $this->insertData(array('user_id' => $pay_log['user_id'],'audio_id' => $pay_log['audio_id'],'is_giving'=>1,'delete' => 0,'timestamp' => $this->getTime()),'buy_log');
                    }
                }
                $this->updateKey($pay_log['user_id'], 1, 'consumption',$pay_log['amount'],'user');
            }
            $this->updateData(array('status' => 1), array('transfer_no' => $pay_log['transfer_no']),'pay_log');
            //财务表修改
            $financial_data = array(
                'type' => $financial_type,
                'amount' => $pay_log['amount'],
                'income' => $financial_income,
                'transfer_no' => $this->makeSN(),
                'transfer_way' => 1,
                'remark' => '',
                'vip_pay' => $pay_log['vip_pay'],
                'user_id' => $pay_log['user_id'],
                'pay_log_id' => $pay_log['id'],
                'delete' => 0,
                'timestamp_update' => $this->getTime(),
                'timestamp' => $this->getTime(),
            );
            $row = $this->insertData($financial_data,'financial');
            $this->addDisData($pay_log['amount'],$pay_log['user_id']);
            $row = true;
            if($row){
                return (array)$pay_video_data;
            }
        }else{
            return false;
        }
    }
    
    
    public function becomeMember($pay_log = array()){
        //充值回调
        $member = $this->getOne(array('type' => 1),array('*'),'member_set');
        $time = date('Y-m-d H:i:s',time() + 24 * 3600 * $member['number']);
        $user = $this->getOne(array('id' => $pay_log['user_id']),array('*'),'user');
        //冻结
        if(time() > strtotime($user['member_time'])){
            //非会员情况
            $this->updateData(array('freeze_amount' => 0),array('id' => $user['id']),'user');
            if($pay_log['amount']+$user['amount'] >= $member['price']){
                $user_freeze = $this->getOne(array('id' => $user['id']),array('freeze_amount','id'),'user');
                $this->updateData(array('freeze_amount' => $member['price']),array('id' => $user_freeze['id']),'user');
            }
        }
        
        if($user['member_time'] <= date('Y-m-d H:i:s',time()) && $member['price'] <= $pay_log['amount']){
            //成为会员然后扣除费用！
            $this->updateData(array(
                'member_time' => $time,
                'open_member_time' => date('Y-m-d H:i:s', time())
            ), array(
                'id' => $pay_log['user_id']
            ), 'user');
            $arr = array(
                'member_time' => $time,
                'open_member_time' => date('Y-m-d H:i:s',time()),
                'user_id' => $pay_log['user_id'],
                'type' => 4,
                'value' => 1,
                'date' => date('Y-m-d H:i',time()),
                'date_time' => date('Y-m-d',time()),
                'timestamp' =>$this->getTime()
            );
            $this->insertData($arr,'data_statistics'); //记录开通会员情况，便于统计会员数
            if(SEND_KEY){
                $log = new Log('web');
                $send_data = array(
                    'price' => $member['price'],
                    'open_time' => date('Y-m-d',time()),
                    'over_time' => date('Y-m-d',time() + 24 * 3600 * $member['number'])
                );
                $log->info('用户id：'.$user['id'].'<-->用户名为：'.$user['name'].'<-->开通了会员,充值金额为：'.$send_data['price'].'<-->开通时间：'.$send_data['open_time'].'<-->到期时间为：'.$send_data['over_time']);
                $this->sendTempMessage($user['open_id'],$send_data);
            }
        }
        $user = $this->updateKey($pay_log['user_id'], 1, 'amount',$pay_log['amount'],'user');
    }

    /**
     *
     * @return array
     */
    public function getHomePageSetting()
    {
        $four_free_data = array(
            'columns' => array('id', 'title', 'auditions_path', 'full_path', 'timestamp', 'pay_type', 'putaway'),
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'teacher',
                    'on' => DB_PREFIX . 'audio.teacher_id = ' . DB_PREFIX . 'teacher.id',
                    'columns' => array('teacher_name' => 'name'),
                ),
                array(
                    'name' => DB_PREFIX . 'audio_setting',
                    'on' => DB_PREFIX . 'audio.id = ' . DB_PREFIX . 'audio_setting.audio_id',
                    'columns' => array('setting_type' => 'type', 'setting_id' => 'id', 'setting_hide' => 'teacher_name_hide', 'setting_putaway' => 'putaway'),
                ),
            ),
            'need_page' => false,
            'limit' => 3,
            'order' => "setting_id DESC, setting_type DESC"
        );
        $where_array = new Where();
        $where_array->lessThanOrEqualTo(DB_PREFIX . 'audio_setting.putaway', date("Y-m-d H:i:s"));
        $four_free_audios = $this->getAll($where_array, $four_free_data, null, 10, 'audio');
        $four_free_audio = array();
        if ($four_free_audios['total'] > 0) {
            foreach ($four_free_audios['list'] as $v) {
                if (mb_strlen($v['teacher_name'], 'utf-8') == 2) {
                    $v['teacher_name'] = substr_replace($v['teacher_name'], "　", 3, 0);
                }
                if (date("Y-m-d", strtotime($v['putaway'])) == date('Y-m-d', time())) {
                    $v['is_new'] = 1;
                } else {
                    $v['is_new'] = 2;
                }
                if (IS_OPEN_HTTPS == 1) {
                    preg_match("/^(http).*$/", $v['full_path'], $full_match);
                    preg_match("/^(http).*$/", $v['auditions_path'], $auditions_match);
                    if ($full_match) {
                        $v['full_path'] = str_replace("http", "https", $v['full_path']);
                    }
                    if ($auditions_match) {
                        $v['auditions_path'] = str_replace("http", "https", $v['auditions_path']);
                    }
                }
                if (!in_array($v['id'], $_SESSION['buy_audio_ids'])) {
                    if ($v['pay_type'] == 3) {
                        $v['full_path'] = $v['full_path'];
                    } else {
                        $v['full_path'] = $v['auditions_path'];
                    }
                }
                $four_free_audio[$v['setting_id']] = $v;
            }
        }
        if (isset($four_free_audio['2']) && $four_free_audio['2']) {
            unset($four_free_audio['1']);
        }
        if (isset($four_free_audio['4']) && $four_free_audio['4']) {
            unset($four_free_audio['3']);
        }
        return $four_free_audio;
    }

    /**
     *
     * @return array|\ArrayObject|bool|null
     */
    public function setHomeManage()
    {
        $home_manage = $this->getOne(array('delete' => 0), array('*'), 'home_manage');
        if ($home_manage['first_url_link']) {
            $first_url_link = (array)json_decode($home_manage['first_url_link']);
            if (time() > strtotime($first_url_link['stay_time'])) {
                $set = array(
                    'first_url_link' => $first_url_link['to_stay_on'],
                    'to_stay_on' => $first_url_link['first_url_link'],
                    'stay_time' => date('Y-m-d H:i:s', strtotime($first_url_link['stay_time']) + 24 * 3600)
                );
                $this->updateData(array('first_url_link' => json_encode($set)), array('id' => $home_manage['id']), 'home_manage');
            }
        }
        $home_manage = $this->getOne(array('delete' => 0), array('*'), 'home_manage');
        return $home_manage;
    }

    /*
     * user_id 用户id
     * price string 价钱
     * 添加返利数据
     *
     * */
    public function addDisData($price,$user_id){
        $this->adapter->getDriver()->getConnection()->beginTransaction();//开启事务
        //查询成为会员的价格
        $mem_set = $this->getOne(array('type'=>1),array('price'),'member_set');
        if(!$mem_set){
            $this->updateAffair('没有设置会员价格.行号为：'.__LINE__.'行');
            return false;
        }
        //如果价格小于于成为会员价格则返回
        if($price < $mem_set['price']){
            $this->updateAffair('id为'.$user_id.'用户消费'.$price.'元。消费价格小于成为会员价格,所以无返利.行号为：'.__LINE__.'行');
            return false;
        }
        //查询是否开启了分销和启用了几级分销 is_open 1 开启  2 关闭  rank 1 一级 2 二级
        $set = $this->getOne(array(),array('is_open','rank'),'distribut_set');
        if(!$set){
            $this->updateAffair('分销返利没有设置,所以无返利.行号为：'.__LINE__.'行');
            return false;
        }
        //开启分销
        if($set['is_open'] == 1){
            $dis_data = $this->getOne(array('user_id' => $user_id),array('id','first_user_id'),'distribut');
            if(!$dis_data){
                $this->updateAffair('一级数据->id为'.$user_id.'用户。无上级用户id.行号为：'.__LINE__.'行');
                return false;
            }
            //一级用户id
            $first_user_id = $dis_data['first_user_id'];  /**需要使用**/
            //查找一级用户下线数量
            $first_user_num = $this->getOne(array('id' => $first_user_id),array('stair_num','name','open_id'),'user');
            $first_user_name = $first_user_num['name'];  /**需要使用**/
            $first_open_id =  $first_user_num['open_id'];  /**需要使用**/
            $first_price = $this->checkNum($first_user_num['stair_num'],1);
            if(!$first_price){
                $this->updateAffair('一级数据->id为'.$user_id.'用户。上级用户id为：'.$first_user_id.'无找到对应的返利等级数据，行号为：'.__LINE__.'行');
                return false;
            }
            $this->updateKey($dis_data['id'], 1, 'stair_brokerage', $first_price, 'distribut');
            $sec_user_id = 0;
            $sec_price = '0.00';
            $sec_user_name = '';
            $sec_open_id = '';
            $sec_type = 0;
            if($set['rank'] == 2){
                //启用二级分销
                $_dis_data = $this->getOne(array('user_id' => $first_user_id),array('id','first_user_id'),'distribut');
                if(!$_dis_data){
                    $this->updateAffair('二级数据->id为'.$first_user_id.'用户。无上级用户id.行号为：'.__LINE__.'行',2);
                }else{
                    //二级用户id
                    $sec_user_id = $_dis_data && $_dis_data['first_user_id'] ? $_dis_data['first_user_id'] : 0;
                    //二级下线数量
                    $sec_user_num = $this->getOne(array('id' => $sec_user_id),array('stair_num','name','open_id'),'user');
                    $sec_user_name = $sec_user_num['name'];     /**需要使用**/
                    $sec_open_id =  $sec_user_num['open_id'];  /**需要使用**/
                    $sec_price = $this->checkNum($sec_user_num['stair_num'],2);
                    if(!$sec_price){
                        $this->updateAffair('二级数据->id为'.$user_id.'用户。上级用户id为：'.$first_user_id.'上两级id为：'.$sec_user_id.'无找到对应的返利等级数据，行号为：'.__LINE__.'行',2);
                    }else{
                        $this->updateKey($_dis_data['id'], 1, 'second_brokerage', $sec_price, 'distribut');
                        $sec_type = 1;
                    }
                }
            }
            if(!$this->upDatePrice($first_user_id,$first_price,$sec_user_id,$sec_price,$sec_type,$user_id,$first_user_name,$sec_user_name,$first_open_id,$sec_open_id)){
                $this->updateAffair($first_user_id.'用户一级分销金额：'.$first_price.'添加添加失败.行号为：'.__LINE__.'行');
                return false;
            }else{
                $this->adapter->getDriver()->getConnection()->commit();//提交事务
                return true;
            }
        }else{
            $this->updateAffair('分销系统没有开启,所以无返利.行号为：'.__LINE__.'行');
            return false;
        }
    }

    //修改佣金数据数据
    public function upDatePrice($first_user_id,$first_price,$sec_user_id,$sec_price,$sec_type,$user_id,$first_user_name,$sec_user_name,$first_open_id,$sec_open_id){
        if($this->addBrokerageLog($first_user_id,$user_id,1,$first_price)){
            if(!$this->updateKey($first_user_id, 1, 'stair_brokerage', $first_price, 'user')){
                $this->updateAffair('修改佣金数据数据失败，行号为：'.__LINE__.'行',2);
                return false;
            }else{
                $this->updateKey($first_user_id, 1, 'brokerage', $first_price, 'user');
                if(SEND_KEY){
                    $this->sendTempMessage($first_open_id,array('name' => $first_user_name,'price' => $first_price),3);
                }
            }
        }
        if($sec_type){
            //添加到二级数据
            if($this->addBrokerageLog($sec_user_id,$user_id,2,$sec_price)){
                if(!$this->updateKey($sec_user_id, 1, 'second_brokerage', $sec_price, 'user')){
                    $this->updateAffair($sec_user_id.'用户二级分销金额：'.$sec_price.'添加添加失败.行号为：'.__LINE__.'行',2);
                    return false;
                }else{
                    $this->updateKey($sec_user_id, 1, 'brokerage', $sec_price, 'user');
                    if(SEND_KEY){
                        $this->sendTempMessage($sec_open_id,array('name' => $sec_user_name,'price' => $sec_price),3);
                    }

                }
            };
        }
        return true;
    }

    //添加到佣金明细表
    public function addBrokerageLog($user_id,$to_id,$grade,$amount){
        $set = array(
            'user_id' => $user_id,
            'to_user_id' => $to_id,
            'grade' => $grade,
            'amount' => $amount,
            'income' => 1,
            'type' => 1,
            'timestamp' => $this->getTime(),
        );
        $row = $this->insertData($set,'brokerage_log');
        if(!$row){
            $this->updateAffair($grade.'级添加佣金日志失败。行号为：'.__LINE__.'行',2);
            return false;
        }else{
            return true;
        }
    }

    //验证数字是否在上限
    public function checkNum($num,$type){
        $where = new where();
        $where->greaterThanOrEqualTo('end',$num);
        $where->lessThanOrEqualTo('start',$num);
        $where->equalTo('delete','0');
        $data = $this->getOne($where,array('stair_brokerage','second_brokerage'),'distribut_rank');
        if($type == 1 && $data){
            $price = $data['stair_brokerage'];
        }else if($type == 2 && $data){
            $price = $data['second_brokerage'];
        }
        return $data ? $price : false;
    }

    //进行事务回滚和写入错误日志
    public function updateAffair($msg,$num = 1){
        if($num == 1){
            $this->adapter->getDriver()->getConnection()->rollback();
        }
        $log = new Log('web');
        $log->err(__CLASS__.'<-->'.__FUNCTION__.'<--->'.$msg);
    }
}