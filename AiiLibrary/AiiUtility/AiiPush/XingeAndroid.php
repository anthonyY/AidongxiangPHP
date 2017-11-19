<?php
namespace Core\System\AiiPush;

/**
 * 基于信鸽推送开发的安卓类
 * 
 * @author WZ
 *
 */
class XingeAndroid extends AiiPushBase
{
    
    const TYPE_USER = 1;
    const TYPE_MERCHANT = 2;

    /**
     * 商家端的id
     * @var unknown
     */
    private $_access_id_biz;

    /**
     * 商家端的key
     * @var unknown
     */
    private $_secret_key_biz;
    
    /**
     * 是否推送 1是 2否
     * @var unknown
     */
    public $is_push= 1;
    
    /**
     * 免打扰开始时间
     * @var unknown
     */
    public $push_start_time= '';
    
    /**
     * 免打扰结束时间
     * @var unknown
     */
    public $push_end_time = '';
    
    /**
     * 推送是否开启声音
     * @var unknown
     */
    public $push_ring = 1;
    
    /**
     * 初始化
     */
    public function init()
    {
        $this->_access_id = XINGE_ANDROID_ACCESS_ID;
        $this->_secret_key = XINGE_ANDROID_SECRET_KEY;
        $this->_access_id_biz = XINGE_ANDROID_BIZ_ACCESS_ID;
        $this->_secret_key_biz = XINGE_ANDROID_BIZ_SECRET_KEY;
    }

    /**
     * 发送
     *
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function pushSingleDevice($deviceToken, $content, $title, $args = array(), $user_type = self::TYPE_USER)
    {

        if(self::TYPE_USER == $user_type)
        {
            $push = new XingeApp($this->_access_id, $this->_secret_key);
        }
        else
        {
            $push = new XingeApp($this->_access_id_biz, $this->_secret_key_biz);
        }
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle($title);
        $mess->setContent($content);
      
        //$style = new Style(0);
        // 义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
        $style = new Style(0, (int)$this->push_ring, 1, 1, 0);//$this->push_ring // 设置推送声音是否开启
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_ACTIVITY);
        // $action->setUrl("http://xg.qq.com");
        // 开url需要用户确认
        // $action->setComfirmOnUrl(1);
        // $custom = array('key1'=>'value1', 'key2'=>'value2');
        $mess->setStyle($style);
        $mess->setAction($action);
         if(is_array($args) && $args)
        {
            $mess->setCustom($args);
        }
        if($this->push_start_time !='00:00:00' && $this->push_end_time !='00:00:00')
        {//有设置打扰时间
            $start_time = explode(":", $this->push_start_time);
            $end_time = explode(":", $this->push_end_time);
            $acceptTime1 = new TimeInterval((int)$end_time[0], (int)$end_time[1], (int)$start_time[0],(int)$start_time[1]);//注意这里用的免打扰结束时间为推送开始时间，
        }
        else
        {//没设置打扰时间
            $acceptTime1 = new TimeInterval(0, 0, 23, 59);//全天推送
        }
        $mess->addAcceptTime($acceptTime1);
        $ret = $push->PushSingleDevice($deviceToken, $mess);
        return $ret;
    }
    
    /**
     * 推送给所有安卓设备，分客户端和商家端
     * 
     * @param string $content
     * @param string $title
     * @param array $args
     * @param number $user_type 
     * @version 2014-11-5 WZ
     */
    public function pushAllDevices($content, $title = '', $args = array(), $user_type = self::TYPE_USER)
    {
        if(self::TYPE_USER == $user_type)
        {
            $push = new XingeApp($this->_access_id, $this->_secret_key);
        }
        else
        {
            $push = new XingeApp($this->_access_id_biz, $this->_secret_key_biz);
        }
        $mess = new Message();
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setType(Message::TYPE_NOTIFICATION);
        //$style = new Style(0);
        // 义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
        $style = new Style(0, 1, 1, 1, 0);
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_ACTIVITY);
        // $action->setUrl("http://xg.qq.com");
        // 开url需要用户确认
        // $action->setComfirmOnUrl(1);
        // $custom = array('key1'=>'value1', 'key2'=>'value2');
        $mess->setStyle($style);
        $mess->setAction($action);
        if(is_array($args) && $args)
        {
            $mess->setCustom($args);
        }
        $acceptTime1 = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime1);
        $ret = $push->PushAllDevices(XingeApp::DEVICE_ANDROID, $mess);
    }

    /**
     * 批量发送
     *
     * @param array $deviceTokens
     *            id,device_token
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function pushCollectionDevice($deviceTokens, $content, $title = '', $args = array())
    {
        $res_arr = array(
            'success' => array(),
            'fail' => array(),
        );
       
        foreach ($deviceTokens as $value) {
            $this->is_push = $value->notification;
            $this->push_start_time = $value->quiet_start_time;
            $this->push_end_time = $value->quiet_end_time;
            $this->push_ring = $value->sound;
            
            if($this->is_push ==1)
            {//开启推送才推送
                $result = $this->pushSingleDevice($value['device_token'], $content, $title , $args, $value['user_type']);
            }
            else
            {
                //用户没开启推送直接返回推送成功    
                $result['ret_code'] = 0;
            }
            
            if(0 == $result['ret_code']) {
                $res_arr['success'][] = $value['id'];
            }else{
                $res_arr['fail'][] = $value['id'];
                $content = $result['ret_code'];
                $this->myfile->putAtEnd($content, 0);
            }
        }
        return $res_arr;
    }
}
?>