<?php
/**
 * Created by PhpStorm.
 * User: lyndon
 * Date: 2016/12/17
 * Time: 17:59
 */
namespace Api\Controller;

use Core\System\AiiUtility\AiiWxPayV3\AiiWxPayNotify;
use Web\Model\WOrderModel;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Where;

class PlanController extends CommonController
{
    
    
    /**
     * 轮播图结束时间,改变状态
     * */
    public function adsStatusAction()
    {
        set_time_limit(0);
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'ads.delete', 0);
        $where->equalTo(DB_PREFIX . 'ads.status', 1);
        $date = $this->getTime();
        $where->lessThanOrEqualTo(DB_PREFIX . 'ads.end_time', $date);
        $data = array(
            'columns' => array(
                '*'
            ),
        );
        $ads_list = $this->getModel('AdminAds')->getAll($where, $data,0, 0, 'ads');
        $where = new Where();
        foreach ($ads_list['list'] as $item) {
            $where = array(
                'id' => $item['id'],
            );
            $ads_row = $this->getModel('AdminAds')->updateData(array('status'=>2), $where, 'ads');
        }
        exit('OK');
    }
    /**
     * 课程上架,改变状态
     * @version YSQ
     */
    public function aduioStatusAction()
    {
        $filename = APP_PATH . '/Cache/plan_ceshi.txt';
        file_put_contents($filename, 'hellp2');
        set_time_limit(0);
        $where = new Where();
        $where->equalTo('delete', 0);
        $where->equalTo('status', 3);
        $date = $this->getTime();
        $where->lessThanOrEqualTo('putaway', $date);
        $data = array(
            'columns' => array(
                'id',
                'teacher_id',
                'pay_type',
                'audio_two_type',
                'putaway',
                'title',
                'type'
            ),
        );
        $ads_list = $this->getModel('AdminAds')->getAll($where, $data,0, 0, 'audio');

        $where = new Where();
        foreach ($ads_list['list'] as $item) {
            $where = array(
                'id' => $item['id'],
            );
            $ads_row = $this->getModel('AdminAds')->updateData(array('status'=>1), $where, 'audio');
            //1 是音频 2 视频
            $sub_teacher = $this->getModel('AdminAds')->fetchAll(array('teacher_id' => $item['teacher_id']),array("columns" => array('id','user_id')),'subscription');
            if($sub_teacher){
                foreach ($sub_teacher as $v){
                    $this->getModel('AdminAds')->insertData(array(
                        'user_id' => $v['user_id'],
                        'audio_id' => $item['id'],
                        'type' => $item['type'],
                        'delete' => 0,
                        'is_new' => 2,
                        'timestamp' => $this->getTime(),
                    ),'notification_subscibe');
                    if(SEND_KEY && $item['pay_type'] != 1){
                        $send_user = $this->getModel('AdminAds')->getOne(array('id' => $v['user_id'],'delete' => 0,'status' => 1),array('id','open_id'),'user');
                        $send_teacher = $this->getModel('AdminAds')->getOne(array('id' => $item['teacher_id'],'delete' => 0),array('id','name'),'teacher');
                        $send_category = $this->getModel('AdminAds')->getOne(array('id' => $item['audio_two_type'],'delete' => 0),array('id','name'),'category');
                        $str = date('m',strtotime($item['putaway'])).'月'.date('d',strtotime($item['putaway'])).'日';
                        if(date('H',strtotime($item['putaway'])) > 12){
                            $str .= " 下午".date('H:i',strtotime($item['putaway']));
                        }else{
                            $str .= " 上午".date('H:i',strtotime($item['putaway']));
                        }
                        
                        if($item['type'] == 1){
                            $url = "https://".SERVER_NAME.ROOT_PATH.'web/audio/details?id='.$item['id'].',1'.'&type=1';
                        }else{
                            $url = "https://".SERVER_NAME.ROOT_PATH.'web/video/details?id='.$item['id'].',1'.'&type=1';
                        }
                        if(!empty($send_user['id']) && !empty($send_user['open_id'])){
                            $send_data['id'] = isset($item['id']) ? $item['id'] : 0;
                            $send_data['title'] = $item['title'];
                            $send_data['category'] =  $send_category['name'];
                            $send_data['teacher_name'] = $send_teacher['name'].'老师';
                            $send_data['time'] = $str;
                            $send_data['url'] = $url;
                            $shop_sendRs = $this->getModel('AdminAds')->sendTempMessage(2,$send_user['open_id'],$send_data);
                        }
                    }
                }
            }
        }
        exit('OK');
    }
    
    /**
     * 系统消息,改变状态
     * @version YSQ
     */
    public function notificationStatusAction()
    {
//         $filename = APP_PATH . '/Cache/plan_ceshi.txt';
//         $result = file_put_contents($filename, 'hellp');
        set_time_limit(0);
        $where = new Where();
        $where->equalTo('delete', 0);
        $where->equalTo('send_status', 1);
        $date = $this->getTime();
        $where->lessThanOrEqualTo('send_time', $date);
        $data = array(
            'columns' => array(
                'id'
            ),
        );
        $noti_list = $this->getModel('AdminAds')->getAll($where, $data,0, 0, 'notification_system');

        $where = new Where();
        foreach ($noti_list['list'] as $item) {
            $where = array(
                'id' => $item['id'],
            );
            $ads_row = $this->getModel('AdminAds')->updateData(array('send_status'=>2), $where, 'notification_system');
        }
        exit('OK');
    }
     
    /**
     * 会员是否到期,到期前一天(是否推送,还是发短信)
     * 一天一次(10点执行)
     */
    public function memberStatusAction()
    {
        set_time_limit(0);
        $date = date("Y-m-d",strtotime('+ 1 day'));
        $table = 'user';
        $sql = 'SELECT * FROM (SELECT DATE_FORMAT(member_time,"%Y-%m-%d") AS days,id,`open_id`,`open_member_time`,`member_time` FROM nb_user) AS nb_users WHERE days = "'.$date.'";';
        $adsModel =  $this->getModel('AdminAds');
        $noti_list =  $adsModel->executeSql($sql);
        $info = $this->getModel('System')->getMemberInfo();
        if(!$noti_list['total']){
            exit('OK');
        }
        $total = 0;
        foreach ($noti_list['list'] as $va){ 
            if(!empty($va['id']) && !empty($va['open_id'])){
                $data['id'] = $va['id'];
                $data['open_time'] = date('Y-m-d',strtotime($va['open_member_time']));
                $data['over_time'] = date('Y-m-d',strtotime($va['member_time']));
                $shop_sendRs = $adsModel->sendTempMessage(1,$va['open_id'],$data);
                if(!$shop_sendRs){
                    $total ++;
                }
            }
        }
      if($total){
          exit($total);
      }else{
          exit('OK');
      }
    }
    
//     /**
//      * 查看那些会员过期的用户中的余额满足 再次成为会员的用户
//      * 一天一次(6点钟好,还是0点钟好)
//      * @version YSQ
//      */
//     public function memberStatus2Action()
//     {
//         set_time_limit(0);
// //         $where = new Where();
// //         $where->equalTo('delete', 0);
// //         $date = date("Y-m-d");
// //         $where->greaterThanOrEqualTo('member_time', $date);
//         $info = $this->getModel('System')->getMemberInfo();
// //         $where->greaterThanOrEqualTo('amount', $info?$info['price']:299);
//         if(!$info){
//             $amount = '299';
//         }else{
//             $amount = $info['price'];
//         }
//         $table = 'user';
//         $sql = 'SELECT DATE_FORMAT(`member_time`,"%Y-%m-%d") as days,id,open_id FROM '.$table.' WHERE (days ="'.$date .'" AND `delete` = 0 AND amount >="'.$amount .'" );';
//         $adsModel =  $this->getModel('AdminAds');
//         $noti_list =  $adsModel->executeSql($sql);
//         $i = 0;
//         if($noti_list['total']){
//             $data2 = array(
//                 'member_time' => date('Y-m-d 00:00:00',($info?strtotime('+ '.$info['number'].' days'):0)),
//             );
//             foreach ($noti_list['list'] as $va){
//                 $row = $adsModel->updateData($data2, array('id'=>$va['id']),'user');
//                 if(!$row){
//                     $i ++;
//                 }
//             }
//         }
//         if($i){
//             exit($i);
//         }else{
//             exit('OK');
//         }
//     }
    
//     /**
//      * 查看那些会员过期的用户中的余 额 不满足 再次成为会员的用户
//      * 一天一次(6点钟好,还是0点钟好)
//      * @version YSQ
//      */
//     function memberStatus3Action(){
//         $info = $this->getModel('System')->getMemberInfo();
//         //         $where->greaterThanOrEqualTo('amount', $info?$info['price']:299);
//         if(!$info){
//             $amount = '299';
//         }else{
//             $amount = $info['price'];
//         }
// //         $where2 = new Where();
// //         $where2->equalTo('delete', 0);
//         $date = date("Y-m-d");
// //         $where2->greaterThanOrEqualTo('member_time', $date);
// //         $noti_list2 = $adsModel->getAll($where2, $data,0, 0, 'user');
        
//         $table = 'user';
//         $sql = 'SELECT DATE_FORMAT(`member_time`,"%Y-%m-%d") as days,id,open_id FROM '.$table.' WHERE (days ="'.$date .'" AND `delete` = 0 AND amount <"'.$amount .'" );';
//         $adsModel =  $this->getModel('AdminAds');
//         $noti_list2 =  $adsModel->executeSql($sql);
        
//         $total = 0;
//         if($noti_list2['total']){
//             $or = array(
//                 'time' => date('Y年m月d日',strtotime($va['days'])),
//                 'price'=>$info['price'],
//             );
//             foreach ($noti_list2['list'] as $va){
//                 //发送用户催单模板消息推送
//                 if(!empty($va['open_id']) && !empty($va['open_id'])){
//                     $or['id'] = $va['id'];
//                     $shop_sendRs = $adsModel->sendTempMessage($adsModel->getWxApi(1),$va['open_id'],2,$or);
//                     if(!$shop_sendRs){
//                         $total ++;
//                     }
//                 }
//             }
//         }
//         if($total){
//             exit($total);
//         }else{
//             exit('OK');
//         }
//     }
}