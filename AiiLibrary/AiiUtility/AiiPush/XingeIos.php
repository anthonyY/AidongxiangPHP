<?php
namespace AiiLibrary\AiiUtility\AiiPush;

/**
 * 基于信鸽开发的iOS类
 *
 * @author WZ
 *        
 */
class XingeIos extends AiiPushBase
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
     * 设置参数，子类注意改写此方法
     */
    public function init()
    {
        $this->_access_id = XINGE_IOS_ACCESS_ID;
        $this->_secret_key = XINGE_IOS_SECRET_KEY;
        $this->_iosenv = XINGE_IOSENV;
        $this->_access_id_biz = XINGE_IOS_BIZ_ACCESS_ID;
        $this->_secret_key_biz = XINGE_IOS_BIZ_SECRET_KEY;
    }

    /**
     * 根据设备号发送给单个设备
     *
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function pushSingleDevice($deviceToken, $content, $title, $args = array(), $user_type = self::TYPE_USER , $badge = 1)
    {

        if(self::TYPE_USER == $user_type)
        {
            $push = new XingeApp($this->_access_id, $this->_secret_key);
        }
        else
        {
            $push = new XingeApp($this->_access_id_biz, $this->_secret_key_biz);
        }

        $mess = new MessageIOS();
        // $mess->setSendTime("2014-03-13 16:00:00");
        $mess->setAlert($content);
        // $mess->setAlert(array('key1'=>'value1'));
        $mess->setBadge($badge);
        if($this->push_ring ==0)
        {
          $mess->setSound("aiitecMute.wav");
        }
        // $custom = array('key1'=>'value1', 'key2'=>'value2');
        // $mess->setCustom($custom);
        if(is_array($args) && $args)
        {
            $mess->setCustom($args);
        }
        
        $acceptTime = new TimeInterval(0, 0, 23, 59);//全天推送
        $mess->addAcceptTime($acceptTime);
        $ret = $push->PushSingleDevice($deviceToken, $mess, $this->_iosenv);
       
        return $ret;
    }

    /**
     * 推送给所有iOS设备
     * 
     * @param string $content 正文
     * @param string $title 标题，其实是没用，只是跟安卓推送保持一致所以才有的参数
     * @param array $args 其它参数
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
        $mess = new MessageIOS();
        $mess->setAlert($content);
        $mess->setBadge(1);
        if(is_array($args) && $args)
        {
            $mess->setCustom($args);
        }
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime);
        $ret = $push->PushAllDevices(XingeApp::DEVICE_IOS, $mess, $this->_iosenv);
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
            'fail' => array()
        );
        
        foreach ($deviceTokens as $value)
        {
            $this->push_ring = $value->sound;
            $start_time = (int)str_replace(":", "", $value->quiet_start_time);
            $end_time = (int)str_replace(":", "", $value->quiet_end_time);
            if($value->notification ==1 && ((!$start_time || !$end_time) || (date("His") < $start_time && date("His") >$end_time)))
            {//开启推送才推送
                $result = $this->pushSingleDevice($value['device_token'], $content, $title, $args,$value['user_type'] ,$value['notice_number'] );
            }
            else
            {
                //用户没开启推送直接返回推送成功
                $result['ret_code'] = 0;
            }
            if (0 == $result['ret_code'])
            {
                $res_arr['success'][] = $value['id'];
            }
            else
            {
                $res_arr['fail'][] = $value['id'];
                $content = $result['ret_code'];
                $this->myfile->putAtEnd($content, 0);
            }
        }
        return $res_arr;
    }
}
?>