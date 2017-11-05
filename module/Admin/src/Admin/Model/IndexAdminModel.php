<?php
namespace Admin\Model;

use Zend\Db\Sql\Where;
use Core\System\WxApi\WxApi;
use Api\Model\CommonModel;

class IndexAdminModel extends CommonModel
{

    protected $table = '';

    
    function getKey($types,$end){
        $data = array();
        $item = array();
        if($types == 24){
            for($i=1;$i<=$types;$i++){
                if($i<10){
                    $data[$i] = '0'.$i;
                    $item[$i] = '0'.$i.':00:00';
                }else{
                    $data[$i] = $i;
                    $item[$i] =$i.':00:00';
                }
            }
        }else{
            for($i=0;$i<$types;$i++){
                $day =  date('Y-m-d',strtotime($end.'-'.$i.'day'));
                $data[] = $day;
            }
            sort($data);
        }
        return array(
            'data' => $types == 24  ? "'".implode("','", $item)."'" : "'".implode("','", $data)."'",
            'array' => $data,

        );
    }
    /**
     * 统计图
     * @version YSQ
     */
    
    function getTypeInfo($cid,$types,$start,$date){
        $title = '';
        if(!$types){
            return array(
                'code' => 400,
                'message' => '参数不完整!',
            );
        }
        if(!$start){
            if($types == 24){
                $start  = date('Y-m-d 00:00:00');
            }else{
                $start  = date('Y-m-d 00:00:00',strtotime('-'.($types-1).'day'));
            }
        }
        $sql = '';
        if($cid == 1){//1,未绑定用户浏览数;2绑定手机用户数;3下单用户;4,会员数,
            $type = 1;
            $table = 'view_data_detail';
            if($types == 24){
                $sql = 'SELECT DATE_FORMAT(`date`,"%H") as days,count(*) as sum from '.$table.' WHERE(date >="'.$start.'" AND type='.$type.' AND (mobile is NULL OR binding_time >= date)) GROUP BY days';
            }else{
                $sql = 'SELECT DATE_FORMAT(`date`,"%Y-%m-%d") as days,count(*) as sum from '.$table.' WHERE(date >="'.$start.'" AND type='.$type.' AND (mobile is NULL OR binding_time >= date)) GROUP BY days';
            }
            $title = '个';
        }else if($cid == 2){//绑定手机用户数
            if($types == 24){
                foreach($date as $v){
                    $nowtime = date('Y-m-d '.$v.':00:00');
                    if($v == 24){
                        $nowtime = date('Y-m-d 23:59:59');
                    }
                    $where = new Where();
                    $where->lessThanOrEqualTo('binding_time',$nowtime);
                    $item[$v] = $this->countData($where,'user');
                }
            }else{
                foreach($date as $v){
                    $where = new Where();
                    $where->lessThanOrEqualTo('binding_time',$v.' 23:59:59');
                    $item[$v] = $this->countData($where,'user');
                }
            }
            $title = '个';
        }else if($cid == 3){//下单用户
            $table = 'nb_pay_log';
            if($types == 24){
                $sql = 'SELECT days,count(1) as sum FROM (SELECT min(DATE_FORMAT(`timestamp`,"%H")) days,COUNT(1) sum FROM '.$table.' WHERE (TIMESTAMP >="'.$start .'" AND status= 1 AND `delete` = 0) GROUP BY user_id) as i GROUP BY days;';
            }else{
                $sql = 'SELECT days,count(1) as sum FROM (SELECT DATE_FORMAT(`timestamp`,"%Y-%m-%d") as days,COUNT(1) as sum  FROM '.$table.' WHERE (TIMESTAMP >="'.$start .'" AND status= 1 AND `delete`=0) GROUP BY days,user_id) as o GROUP BY days;';
            }
            $title = '个';
        }else if($cid == 4){
            $table = 'nb_data_statistics';
            if($types == 24){
               foreach($date as $v){
                    $nowtime = date('Y-m-d '.$v.':00:00',strtotime($start));
                    if($v == 24){
                        $nowtime = date('Y-m-d 23:59:59',strtotime($start));
                    }
                    $sql1 = 'SElECT count(*) as sum FROM '.$table.' WHERE `open_member_time` <= "'.$nowtime.'" AND `member_time` >= "'.$nowtime.'"';
                   $item[$v] = $this->dbSelectSql($sql1);
                }
            }else{
                foreach($date as $v){
                    $sql1 = 'SElECT count(*) as sum FROM '.$table.' WHERE `open_member_time` <= "'.$v.'" AND `member_time` >= "'.$v.'"';
                    $item[$v] = $this->dbSelectSql($sql1);
                }
            }
            $title = '个';
        }else if($cid == 5){//1,会员贡献的收入;2,非会员贡献的收入;3,会员消费课程的金额;4,非会员消费课程的金额,
            $table = 'nb_financial';
            if($types == 24){
                $sql = 'SELECT days,sum(i.sum) as sum FROM (SELECT DATE_FORMAT(`timestamp`,"%H") as days,sum(amount) as sum FROM '.$table.' WHERE (timestamp >="'.$start.'" AND income = 1 AND `delete`=0 AND vip_pay=1) GROUP BY days)as i GROUP BY days;';
            }else{
                $sql = 'SELECT days, sum(o.sum) as sum FROM (SELECT DATE_FORMAT(`timestamp`,"%Y-%m-%d") as days,sum(amount) as sum  FROM '.$table.' WHERE (timestamp >="'.$start .'" AND income = 1 AND `delete`=0 AND vip_pay=1) GROUP BY days) as o GROUP BY days;';
            }
            $title = '元';
        }else if($cid == 6){//非会员贡献的收入
            $table = 'nb_financial';
            if($types == 24){
                $sql = 'SELECT days,sum(i.sum) as sum FROM (SELECT DATE_FORMAT(`timestamp`,"%H") days,sum(amount) sum FROM '.$table.' WHERE (timestamp >="'.$start .'" AND income = 1 AND `delete`=0 AND vip_pay=2) GROUP BY days)as i GROUP BY days;';
            }else{
                $sql = 'SELECT days, sum(o.sum) as sum from (SELECT DATE_FORMAT(`timestamp`,"%Y-%m-%d") as days,sum(amount) as sum  FROM '.$table.' WHERE (timestamp >="'.$start .'" AND income = 1 AND `delete`=0 AND vip_pay=2) GROUP BY days) as o GROUP BY days;';
            }
        }else if($cid == 7){//会员消费课程的金额
            $table = 'nb_financial';
            if($types == 24){
                $sql = 'SELECT days,sum(i.sum) as sum FROM (SELECT DATE_FORMAT(`timestamp`,"%H") days,sum(amount) sum FROM '.$table.' WHERE (timestamp >="'.$start .'" AND income = 2 AND `delete`=0 AND vip_pay=1 AND type in (1,2)) GROUP BY days)as i GROUP BY days;';
            }else{
                $sql = 'SELECT days, sum(o.sum) as sum from (SELECT DATE_FORMAT(`timestamp`,"%Y-%m-%d") as days,sum(amount) as sum  FROM '.$table.' WHERE (timestamp >="'.$start .'" AND income = 2 AND `delete`=0 AND vip_pay=1 AND type in (1,2)) GROUP BY days) as o GROUP BY days;';
            }
            $title = '元';
        }else if($cid == 8){//非会员消费课程的金额
            $table = 'nb_financial';
            if($types == 24){
                $sql = 'SELECT days,sum(i.sum) as sum FROM (SELECT DATE_FORMAT(`timestamp`,"%H") days,sum(amount) sum FROM '.$table.' WHERE (timestamp >="'.$start .'" AND income = 2 AND `delete`=0 AND vip_pay=2 AND type in (1,2)) GROUP BY days)as i GROUP BY days;';
            }else{
                $sql = 'SELECT days, sum(o.sum) as sum from (SELECT DATE_FORMAT(`timestamp`,"%Y-%m-%d") as days,sum(amount) as sum  FROM '.$table.' WHERE (timestamp >="'.$start .'" AND income = 2 AND `delete`=0 AND vip_pay=2 AND type in (1,2)) GROUP BY days) as o GROUP BY days;';
            }
            $title = '元';
        }else if($cid == 9){
            $table = 'nb_user';
            if($types == 24){
                $sql = 'SELECT DATE_FORMAT(`timestamp`,"%H") as days,count(*) as sum from '.$table.' WHERE(timestamp >="'.$start.'" AND is_recommend = 1) GROUP BY days';
            }else{
                $sql = 'SELECT DATE_FORMAT(`timestamp`,"%Y-%m-%d") as days,count(*) as sum from '.$table.' WHERE(timestamp >="'.$start.'" AND is_recommend = 1) GROUP BY days';
            }
            $title = '个';
        }
        if($sql){
            $result = $this->executeSql($sql);
            $item = array();
            if($result['total']){
                foreach ($result['list'] as $k=>$v){
                    $item[$v['days']] = $v['sum'];
                }
            }
        }
        return array($item,$title);
    }

  /**
   * 社团F码申请数
   * @version YSQ
   */
  function getFCode(){
      $total = $this->countData(array('status'=>1,'delete'=>0),'fcode');
      return $total?$total:0;
  }
  
  /**
   * 意见反馈数
   * @version YSQ
   */
  function getFeedback(){
      $total = $this->countData(array('status'=>1,'delete'=>0),'feedback');
      return $total?$total:0;
  }
  /**
   * 用户举报
   * @version YSQ
   */
  function getReport(){
      $total = $this->countData(array('status'=>1,'delete'=>0),'report');
      return $total?$total:0;
  }
  /**
   * 让爱成书申请
   * @version YSQ
   */
  function getLoveBook(){
      $total = $this->countData(array('status'=>1,'delete'=>0),'family_book');
      return $total?$total:0;
  }
  
  /**
   * 用户统计
   * @version YSQ
   */
  function getUserCount(){
      $data['total'] = $this->countData(array('status'=>1,'delete'=>0),'user');
      $data['man'] = $this->countData(array('status'=>1,'delete'=>0,'sex'=>1),'user');
      $data['women'] = $this->countData(array('status'=>1,'delete'=>0,'sex'=>2),'user');
      $data['total'] = $data['total'] ? $data['total'] : 0;
      $data['man'] = $data['man'] ? $data['man'] : 0;
      $data['women'] = $data['women'] ? $data['women'] : 0;
      return $data;
  }
  
  /**
   * 密聊数
   * @version YSQ
   */
  function getAffinityCount(){
      $total = $this->countData(array('status'=>2,'delete'=>0),'user_relationship');
      return $total?$total:0;
  }
  
  /**
   * 家庭数
   * @version YSQ
   */
  function getFamilyCount(){
      $total = $this->countData(array('is_pay'=>2),'family');
      return $total?$total:0;
  }

}
