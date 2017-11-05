<?php
namespace Web\Model;

use Zend\Db\Sql\Where;
use Core\System\WxApi\WxApi;

class ScanCodeModel extends CommonModel
{
    protected $table = '';

    //获取二维码活动详情 type 1 正常  2 活动结束  3 礼包被拆过了
    public function getActvityDetails($code){
        if(!$code) return array('status' => false,'type' => 2);

        $where = new Where();
        $where->equalTo('code',$code[0]);
        $qrcode_data = $this->getOne($where,array('*'),'qrcode');
        if(!$qrcode_data) return array('status' => false,'type' => 2);
        //生成秘钥
        $key = $this->getkey($qrcode_data);
        //验证是否正常请求
        if($key != $code[1]) return array('status' => false,'type' => 2);
        //获取二维码数据
        //查看二维码是否过期
        $qrcode_log_data = $this->getOne(array('id' => $qrcode_data['qrcode_log_id']),array('*'),'qrcode_log');
        if(!$qrcode_log_data) return array('status' => false,'type' => 2);
        if(time() >= strtotime($qrcode_log_data['effective_time'])) return array('status' => false,'type' => 2);
        //查看是否被领取
        if($qrcode_data['status'] == 2) return array('status' => false,'type' => 3);
        //检查当前授权用户是否领取了奖励
        $is_get = $this->getOne(array('user_id' => $_SESSION['user_id'],'qrcode_log_id' => $qrcode_log_data['id'],'status' => 2),array('*'),'qrcode');
        //组装前端页面显示的数据
        $data['day'] = $qrcode_log_data['day'];
        $data['price'] = floatval($qrcode_log_data['price']);
        $data['m'] = date('m',strtotime($qrcode_log_data['effective_time'])); //月份
        $data['d'] = date('d',strtotime($qrcode_log_data['effective_time'])); //日期
        $data['is_get'] = $is_get ? 1 : 2; //1未领取过 2 未领取过
        return array(
            'status' => true,
            'id' => $qrcode_data['id'],
            'data' => $data,
        );
    }

    //生成二维码key
    public function getKey($qrcode_data){
        return md5(md5($qrcode_data['code'].$qrcode_data['timestamp']).QRCODE_TOKEN);
    }

    //获取微信公众号二维码
    public function getWeixinCode($json_data = ''){
        $wxapi = new WxApi();
        if(!$json_data){
            $json_data = '{"action_name":"QR_STR_SCENE","action_info":{"scene": {"scene_str": "jueniao"}}}';
        }
        $ticket_data = $wxapi->wxQrCodeTicket($json_data);
        $ticket = json_decode($ticket_data);
        $code = $wxapi->wxQrCode($ticket->ticket);
        return $code;
    }

    //获取二维码奖励
    public function updateCodeStatus(){
        $this->adapter->getDriver()->getConnection()->beginTransaction();//开启事务
        $mobile = $_POST['mobile'] ? $_POST['mobile'] : "";
        $qrcode_id = $_POST['id'] ? $_POST['id'] : 0;
        if(!$mobile || !$qrcode_id) return array('status' => false ,'msg' => '领取失败');
        //获取用户信息
        $session_user_id = $_SESSION['user_id'];
        $session_user_info = $this->getOne(array('id' => $session_user_id) , array('*'), "user");
        //获取二维码信息
        $qrcode_data = $this->getOne(array('id' => $qrcode_id,'status' => 1),array('*'),'qrcode');
        if(!$qrcode_data) return array('status' => false ,'msg' => '领取失败');
        $qrcode_log_data = $this->getOne(array('id' => $qrcode_data['qrcode_log_id']),array('*'),'qrcode_log');
        if(time() >= strtotime($qrcode_log_data['effective_time'])) return array('status' => false,'msg' => '领取失败');
        //获取成为会员的价格
        $member = $this->getOne(array('type' => 1),array('*'),'member_set');

        $set_data = array();
        //组装修改修改的用户信息
        if(!$session_user_info['mobile']){
            $set_data['mobile'] = $mobile;
            $set_data['binding_status'] = 1;
            $set_data['binding_time'] = $this->getTime();
        }else if($session_user_info['mobile'] != $mobile){
            return array('status' => false ,'msg' => '领取失败');
        }
        //修改会员信息
        list($status,$msg,$vip) = $this->updateUserData($set_data, $session_user_info, $qrcode_log_data, $member);
        if(!$status){
            $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
            return array('status' => $status ,'msg' => $msg);
        }
        //修改财务记录表
        list($f_status,$f_msg) = $this->insertFinancialData($qrcode_log_data, $session_user_id, $vip);
        if(!$f_status){
            $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
            return array('status' => $f_status ,'msg' => $f_msg);
        }

        //修改二维码使用状态和使用时间
        $row_qrcode = $this->updateData(array('status' => 2),array('id' => $qrcode_data['id']),'qrcode');
        if(!$row_qrcode){
            $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
            return array('status' => false,'msg' => '领取失败');
        }

        $this->updateData(array('use_time' => $this->getTime(),'user_id' => $session_user_id),array('id' => $qrcode_data['id']),'qrcode');
        //修改二维码日志使用份数
        $row_qrcode_log = $this->updateKey($qrcode_log_data['id'],1,'used_num',1,'qrcode_log');
        if(!$row_qrcode_log){
            $this->adapter->getDriver()->getConnection()->rollback();//事务回滚
            return array('status' => false,'msg' => '领取失败');
        }
        $this->adapter->getDriver()->getConnection()->commit();//提交事务
        return array('status' => true ,'msg' => '领取失败');

    }

    /**
     * 修改会员信息
     * @param $set_data
     * @param $session_user_info
     * @param $qrcode_log_data
     * @param $member
     */
    public function updateUserData($set_data, $session_user_info, $qrcode_log_data, $member)
    {
        $vip = 1;
        $set_data['amount'] = $session_user_info['amount'] + $qrcode_log_data['price'];
        if($session_user_info['member_time'] == '0000-00-00 00:00:00'){
            $set_data['member_time'] = date('Y-m-d H:i:s', time() + $qrcode_log_data['day'] * 24 * 3600);
        }else{
            $set_data['member_time'] = date('Y-m-d H:i:s', strtotime($session_user_info['member_time']) + $qrcode_log_data['day'] * 24 * 3600);
        }

        //非会员情况下清除冻结金额（如果是会员的话肯定是被冻结的了）
        if (time() > strtotime($session_user_info['member_time'])) {
            //非会员情况
            $set_data['freeze_amount'] = $member['price'];
            $set_data['open_member_time'] = $this->getTime();
            $vip = 2;
            //统计会员
            $arr = array(
                'member_time' => $set_data['member_time'],
                'open_member_time' => $set_data['open_member_time'],
                'user_id' => $session_user_info['id'],
                'type' => 4,
                'value' => 1,
                'date' => date('Y-m-d H:i',time()),
                'date_time' => date('Y-m-d',time()),
                'timestamp' =>$this->getTime()
            );
            if(!$this->insertData($arr,'data_statistics')){
                return array(false,'领取失败',$vip);
            };
        }
        if(!$this->updateData($set_data, array('id' => $session_user_info['id']), 'user')){
            return array(false,'领取失败',$vip);
        }
        return array(true,'ok',$vip);
    }

    /**
     * 修改财务记录表
     * @param $qrcode_log_data
     * @param $session_user_id
     * @param $vip
     */
    public function insertFinancialData($qrcode_log_data, $session_user_id, $vip)
    {
        $pay_log = array(
            'type' => 5,
            'pay_type' => 1,
            'number' => 1,
            'genre' => 0,
            'audio_type' => 0,
            'audio_id' => 0,
            'amount' => $qrcode_log_data['price'],
            'status' => 1,
            'transfer_no' => $this->makeSN() . '-5',
            'transfer_way' => 3,
            'user_id' => $session_user_id,
            'vip_pay' => $vip,
            'delete' => 0,
            'vip_price' => '',
            'pay_video' => '',
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime(),
        );
        $pay_log_id = $this->insertData($pay_log, 'pay_log');
        if(!$pay_log_id){
            return array(false,'领取失败');
        }
        $financial_data = array(
            'type' => 7,
            'amount' => $qrcode_log_data['price'],
            'income' => 1,
            'transfer_no' => $this->makeSN(),
            'transfer_way' => 3,
            'remark' => ' 营销活动充值',
            'user_id' => $session_user_id,
            'pay_log_id' => $pay_log_id,
            'vip_pay' => $vip,
            'delete' => 0,
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime(),
        );
        if(!$this->insertData($financial_data, 'financial')){
            return array(false,'领取失败');
        }

        return array(true,'ok');
    }

    public function getQrCodeData($id){
        $data = array();
        $qrcode_data = $this->getOne(array('id' => $id,'status' => 2),array('*'),'qrcode');
        $qrcode_log_data = $this->getOne(array('id' => $qrcode_data['qrcode_log_id']),array('*'),'qrcode_log');
        $data['day'] = $qrcode_log_data['day'];
        $data['price'] = floatval($qrcode_log_data['price']);
        return $data;
    }
}