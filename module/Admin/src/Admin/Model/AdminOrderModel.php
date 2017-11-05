<?php
/**
 * Created by PhpStorm.
 * User: lyndon
 * Date: 2016/11/30
 * Time: 22:13
 */

namespace Admin\Model;

use Core\System\WxPayApi\WXHongBao;
use Zend\Db\Sql\Where;
use Api\Model\CommonModel;
use Api\Controller\Item\PushArgsItem;
use Zend\Db\Sql\Expression;

class AdminOrderModel extends CommonModel
{

    protected $table = "comment";

    /**
     * 评论列表
     * @param unknown $condition
     * @version 2016-12-3 WZ
     */
    public function getOrderList($condition,$boolean=true)
    {
        $where = new Where();
        $where->equalTo(DB_PREFIX.'pay_log.delete', DELETE_FALSE);
        $where->equalTo(DB_PREFIX.'pay_log.status', 1);
//         $where->notEqualTo(DB_PREFIX.'pay_log.type', 3);
        $c_where = $condition['where'];
        if (isset($c_where['start']) && $c_where['start']) {
            $where->greaterThanOrEqualTo(DB_PREFIX.'pay_log.timestamp', $c_where['start'].' 00:00:00');
        }
        if (isset($c_where['end']) && $c_where['end']) {
            $where->lessThanOrEqualTo(DB_PREFIX.'pay_log.timestamp', date("Y-m-d 00:00:00",strtotime($c_where['end']."+1 day")));
//             $where->lessThanOrEqualTo(DB_PREFIX.'pay_log.timestamp', $c_where['end'].' 00:00:00');
        }
        if (isset($c_where['type']) && $c_where['type']) {
            if($c_where['type']>=3){
                $where->equalTo(DB_PREFIX.'pay_log.genre', 2);
                $where->equalTo(DB_PREFIX.'pay_log.audio_type', $c_where['type']);
            }else{
                $where->equalTo(DB_PREFIX.'pay_log.genre', 1);
                $where->equalTo(DB_PREFIX.'pay_log.audio_type', $c_where['type']);
            }
//             $where->equalTo(DB_PREFIX.'comment.type', $c_where['type']);
        }
        if (isset($c_where['vip_pay']) && $c_where['vip_pay']) {
            $where->equalTo(DB_PREFIX.'pay_log.vip_pay', $c_where['vip_pay']);
        }

        if (isset($c_where['income']) && $c_where['income']) {
            if($c_where['income'] != 1)
            {
                $where->equalTo(DB_PREFIX.'pay_log.type', 1);
            }
            else
            {
                $where->notEqualTo(DB_PREFIX.'pay_log.type', 1);
            }

        }
        $data = array(
            'columns' => array(
                'id',
                'amount',
                'number',
                'genre',
                'timestamp',
                'type',
                'pay_type'
//                 'praise_num',
//                 'comment_num',
//                 'delete',
//                 'is_top',
            ),
            'join' =>array(
                array(
                    'name' => DB_PREFIX.'user',
                    'on' => DB_PREFIX.'user.id = '.DB_PREFIX.'pay_log.user_id',
                    'columns' => array(
                        'name',
                        'img_path',
                        'head_icon'
                    ),
                    'type' => 'left'
                ),
               array(
                    'name' => DB_PREFIX.'audio',
                   'on' =>new Expression(' `nb_pay_log`.`genre`= 1 and `nb_audio`.`id`=`nb_pay_log`.`audio_id`'),
//                     'on' => DB_PREFIX.'audio.id = '.DB_PREFIX.'comment.audio_id',
                    'columns' => array(
                        'p_name' => 'title',
                        'p_type' => 'type', 
                    ),
                    'type' => 'left'
                ),
                array(
                    'name' => DB_PREFIX.'courses',
                    'on' =>new Expression(' `nb_pay_log`.`genre`= 2 and `nb_courses`.`id`=`nb_pay_log`.`audio_id`'),
//                     'on' => DB_PREFIX.'courses.id = '.DB_PREFIX.'comment.courses_id',
                    'columns' => array(
                        'c_name' => 'title',
                        'c_type' => 'type',
                    ),
                    'type' => 'left'
                ),
            ),
            'need_page' => $boolean,
        );
        return $this->getAll($where, $data, $condition['page'], null, 'pay_log');
    }
    
    /**
     *  今天收入,总收入,购买人数
     */
    function getTodayOrderCount(){
        $where = new Where();
        $where->equalTo('status', 1);//状态：1支付成功；2支付失败；
        $where->equalTo('delete', 0);
//         $where->equalTo('type', 3);
        //$where->equalTo('transfer_way', 3);
        $where->in('transfer_way',array(1,3,4));
        $data = array(
            'columns' => array(
                new Expression("sum(`amount`) as sum")
            ),
        );
        $count = $this->countData($where,'pay_log');
        $list1 = $this->fetchAll($where,$data,'pay_log');
        $where->greaterThanOrEqualTo('timestamp', date("Y-m-d 00:00:00"));
        $list = $this->fetchAll($where,$data,'pay_log');
        $where = new Where();
        $where->equalTo("income","2");
        $where->equalTo("delete","0");
       //
        $columns = array(new Expression("sum(`amount`) as amount"));
        $total_consumption = $this->getOne($where,$columns,'financial');
        //$where->greaterThanOrEqualTo('timestamp', date("Y-m-d 00:00:00"));
        $where->equalTo('vip_pay',2);
        $where->in("type",array(1,2));
        $consumption = $this->getOne($where,$columns,'financial');

        $where = new Where();
        $where->equalTo("income","2");
        $where->equalTo("delete","0");
        $where->in("type",array(1,2));
        $columns = array(new Expression("sum(`amount`) as amount"));
        $where->greaterThanOrEqualTo('timestamp', date("Y-m-d 00:00:00"));
        $today_consumption= $this->getOne($where,$columns,'financial');
        $where = new Where();
        $where->equalTo("income","2");
        $where->equalTo("delete","0");
        $where->equalTo("vip_pay","1");
        //$where->in("type",array(1,2));
        $columns = array(new Expression("sum(`amount`) as amount"));
        $member_total_consumption = $this->getOne($where,$columns,'financial');
        $where->in("type",array(1,2));
        $member_class_consumption = $this->getOne($where,$columns,'financial');
        $where = new Where();
        $where->equalTo("delete","0");
        $where->equalTo("vip_pay","1");
        $where->equalTo("transfer_way",1);
        $columns =array(new Expression("sum(`amount`) as amount"));
        $member_total_income = $this->getOne($where,$columns,'financial');
        $where = new Where();
        $where->equalTo('income',1);
        $where->equalTo('delete',0);
        $where->equalTo('vip_pay',2);
        //$where->in('type',array(3,5));
        $columns = array(new Expression("sum(`amount`) as amount"));
        $user_total_come = $this->getOne($where,$columns,'financial');
        return array(
            'sum' => $list?$list['0']['sum']:0,
            'sum_total' => $list1?$list1['0']['sum']:0,
            'total_consumption' => $total_consumption->amount,//总消费
            'today_consumption' => $today_consumption->amount,
            'consumption'=>$consumption->amount,//非会员总消费
            'member_total_consumption'=>$member_total_consumption->amount,//会员总消费
            'member_class_consumption' => $member_class_consumption->amount,//会员课程总消费
            'member_total_income' => $member_total_income->amount,//会员总收入
            'user_total_come' => $user_total_come->amount,//非会员总收入
            'count' => $count,
        );
    }
    
    /**
     *  下单用户;访客数;浏览量
     */
    function getTodayCount(){
        //支付数量
        $where = new Where();
        $where->equalTo('status', 1);//状态：1支付成功；2支付失败；
        $where->equalTo('delete', 0);
        $where -> greaterThanOrEqualTo('timestamp', date("Y-m-d 00:00:00"));
        $count = $this->countData($where,'pay_log',array(),'user_id');
        //访客数
        $where2 = new Where();
        $where2 -> greaterThanOrEqualTo('timestamp', date("Y-m-d 00:00:00"));
        $where2 -> equalTo('type', 1);
        $visitors_info = $this->countData($where2,'data_statistics',array(),'user_id');//访客数
        //浏览量
        $where3 = new Where();
        $where3 -> greaterThanOrEqualTo('timestamp', date("Y-m-d 00:00:00"));
        $where3 -> equalTo('type', 2);
        $browse_info = $this->getOne($where3,null,'data_statistics');//浏览量
        //用户数量
        $where4 = new Where();
        $where4->equalTo('delete',0);
        $user_total = $this->countData($where4,'user');
        $where4->isNotNull('mobile');
        $user_vip_total = $this->countData($where4,'user');
        //会员数量
        $where5 = new Where();
        $where5->lessThanOrEqualTo('open_member_time',date('Y-m-d H:i:s'));
        $where5->greaterThanOrEqualTo('member_time',date('Y-m-d H:i:s'));
        $user_vip = $this->countData($where5,'user');//会员数
        //未绑定用户浏览量
        $start = date('Y-m-d 00:00:00');
        $sql = 'SELECT count(*) as sum from view_data_detail WHERE(date >="'.$start.'" AND type=1 AND (mobile is NULL OR binding_time >= date))';
        $unbinding_browse = $this->dbSelectSql($sql); //未绑定手机用户浏览量
        //分销注册数量
        $user_recom_total = $this->countData(array('is_recommend' => 1),'user');
        //分销总佣金
        $sql = "SELECT SUM(`stair_brokerage`+`second_brokerage`) AS `sum` FROM `nb_user`";
        $user_recom_price = $this->dbSelectSql($sql);


        return array(
            'visitors' => $visitors_info ? $visitors_info:0,
            'browse' => $browse_info ? $browse_info['value']:0,
            'userCount' => $count,
            'user_total' => $user_total,
            'user_vip_total' => $user_vip_total,
            'user_vip' => $user_vip,
            'unbinding_browse' => $unbinding_browse,
            'user_recom_total' => $user_recom_total,
            'user_recom_price' => $user_recom_price ? $user_recom_price : 0
        );
    }
    
    /**
     * 导出excel
     *
     * @version YSQ
     */
    public function setExcel($condition){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能导入
        $list = $this->getOrderList($condition,false);
//         $time = $this->getTime();
        foreach ($list['list'] as $k => $v){
            if($v['type'] == 4){
                $str = '集团会员充值';
            }else if($v['type'] == 3){
                $str = '充值';
            }else if($v['type'] == 2 && $v['pay_type'] == 2){
                $str = '会员赠送';
            }else if($v['genre'] == 1){
                if($v['p_type'] == 1){
                    $str = '音频';
                }else{
                    $str = '视频';
                }
            }else if($v['genre'] == 2){
                if($v['c_type'] == 3){
                    $str = '音频包';
                }else{
                    $str = '视频包';
                }
            }
            $arr[$k]['name'] = $v['name'];
            $arr[$k]['genre'] = $str;
            $arr[$k]['content'] = $v['genre']==1?$v['p_name']:$v['c_name'];
            $arr[$k]['number'] = $v['number'];
            $arr[$k]['amount'] = $v['amount'];
            $arr[$k]['timestamp'] = $v['timestamp'];
        }
        $allRoomList = $arr;
    
        $filename='财务列表';
        $headArr=array("用户名","购买类型","购买内容","购买数量","订单总额(元)","订单时间");
        $return = $this->getExcel($filename,$headArr,$allRoomList);
        if($return){
            return array(
                'code' =>200,
                'message' =>'成功',
            );
        }
    }

    /*
     *详情页面统计数据
     *
     */
    public function  getDetailList($condition,$boolean=true){
        $where = new Where();
        if($condition['where']['start']){
            $start = $condition['where']['start'];
        }
        if($condition['where']['end']){
            $end = $condition['where']['end'].' 23:59:59';
        }
        $type = $condition['where']['type'];

        if($type == 3){ //下单详情
            if(isset($start)){
                $where->greaterThanOrEqualTo('nb_pay_log.timestamp_update',$start);
            }
            if(isset($end)){
                $where->lessThanOrEqualTo('nb_pay_log.timestamp_update',$end);
            }
            $table = 'pay_log';
            $where->in('nb_pay_log.type',array(1,2));
            $where->equalTo('nb_pay_log.status',1);
            $data = array(
                'join' => array(
                    array(
                        'name' => DB_PREFIX.'user',
                        'on' => DB_PREFIX.'user.id = '.DB_PREFIX.'pay_log.user_id',
                        'columns' => array(
                            'user_name' => 'name',//用户名称
                            'img_path' => 'img_path',
                            'mobile'=>'mobile',
                            'head_icon' => 'head_icon'
                        ),
                        'type' => 'left'
                    ),
                    array(
                        'name' => DB_PREFIX.'audio',
                        'on' =>new Expression(' `nb_pay_log`.`genre`= 1 and `nb_audio`.`id`=`nb_pay_log`.`audio_id`'),
                        'columns' => array(
                            'p_name' => 'title',
                            'p_type' => 'type',
                        ),
                        'type' => 'left'
                    ),
                    array(
                        'name' => DB_PREFIX.'courses',
                        'on' =>new Expression(' `nb_pay_log`.`genre`= 2 and `nb_courses`.`id`=`nb_pay_log`.`audio_id`'),
                        'columns' => array(
                            'c_name' => 'title',
                            'c_type' => 'type',
                        ),
                        'type' => 'left'
                    ),
                ),
                'order' => array(
                    'timestamp_update' => 'DESC'
                ),
                'need_page' => $boolean
            );
        }else if($type == 1){//访客详情
            if(isset($start)){
                $where->greaterThanOrEqualTo('date',$start);
            }
            if(isset($end)){
                $where->lessThanOrEqualTo('date',$end);
            }
            $where->equalTo('type',1);
            $table = 'data_statistics';
            $data = array(
                'join' => array(
                    array(
                        'name' => DB_PREFIX.'user',
                        'on' => DB_PREFIX.'user.id = '.DB_PREFIX.'data_statistics.user_id',
                        'columns' => array(
                            'user_name' => 'name',//用户名称
                            'img_path' => 'img_path',
                            'mobile'=>'mobile',
                            'head_icon' => 'head_icon'
                        ),
                        'type' => 'left'
                    ),
                ),
                'order' => array(
                    'date' => 'DESC'
                ),
                'need_page' => $boolean
            );
        }else{//
            if($type == 2){
                $num = 1;
            }else{
                $num = 2;
            }
            if(isset($start)){
                $where->greaterThanOrEqualTo('date_time',$start);
            }
            if(isset($end)){
                $where->lessThanOrEqualTo('date_time',$end);
            }
            $table = 'data_detail';
            $where->equalTo(DB_PREFIX.'data_detail.type',$num);
            $data = array(
                'join' => array(
                    array(
                        'name' => DB_PREFIX.'user',
                        'on' => DB_PREFIX.'user.id = '.DB_PREFIX.'data_detail.user_id',
                        'columns' => array(
                            'user_name' => 'name',//用户名称
                            'img_path' => 'img_path',
                            'mobile'=>'mobile',
                            'head_icon' => 'head_icon'
                        ),
                        'type' => 'left'
                    ),
                    array(
                        'name' => DB_PREFIX.'audio',
                        'on' =>new Expression(' `nb_data_detail`.`detail_type`= 1 and `nb_audio`.`id`=`nb_data_detail`.`page_id`'),
                        'columns' => array(
                            'p_name' => 'title',
                            'p_type' => 'type',
                        ),
                        'type' => 'left'
                    ),
                    array(
                        'name' => DB_PREFIX.'courses',
                        'on' =>new Expression(' `nb_data_detail`.`detail_type`= 2 and `nb_courses`.`id`=`nb_data_detail`.`page_id`'),
                        'columns' => array(
                            'c_name' => 'title',
                            'c_type' => 'type',
                        ),
                        'type' => 'left'
                    ),
                ),
                'order' => array(
                    'date' => 'DESC'
                ),
                'need_page' => $boolean
            );
        }
        $list = $this->getAll($where, $data, $condition['page'], null, $table);

        return $list;
    }

}