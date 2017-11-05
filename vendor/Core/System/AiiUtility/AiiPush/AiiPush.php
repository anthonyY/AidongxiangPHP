<?php
namespace Core\System\AiiUtility\AiiPush;

/**
 * 推送类，
 * 分类、分配，返回结果
 *
 * @author WZ
 *        
 */
class AiiPush
{

    private $_ios_push;

    private $_android_push;

    private $_weixin_push;

    private $_sms_push;

    const DEVICE_TYPE_IOS = '1';

    const DEVICE_TYPE_ANDROID = '2';

    const DEVICE_TYPE_WEIXIN = '8';

    const DEVICE_TYPE_SMS = '16';

    /**
     * 构造函数
     */
    function __construct()
    {
        $this->_ios_push = new XingeIos();
//         $this->_ios_push = new AiiIosPush();
        $this->_android_push = new XingeAndroid();
        $this->_weixin_push = new AiiWeixinPush();
        switch (SMS_SERVICE) {
            case 1:
                $this->_sms_push = new SmsPush();
                break;
            case 2:
                break;
            case 3:
                break;
            case 4:
                $this->_sms_push = new SmsPushYunCe();
                break;
            case 5:
                $this->_sms_push= new SmsPushQiXin();
                break;
            case 6:
                $this->_sms_push = new SmsPushQcloud();
                break;
            case 7:
                $this->_sms_push= new SmsPushBao();
                break;
            default:
                break;
        }
    }

    function __destruct()
    {
    }

    /**
     * 推送一个设备号
     *
     * @param string $device_token
     *            设备号
     * @param int $type
     *            设备类型
     * @param string $content
     *            内容，正文
     * @param string $title
     *            标题（安卓需要）
     * @param array $args
     *            自定义参数（推送目标之类的参数）
     * @return array success,fail
     */
    public function pushSingleDevice($device_token, $type, $content, $title = '', $args = array(), $nid = 0, $environment = 0)
    {
        $device_collection = array(
            array(
                'id' => 1,
                'device_token' => $device_token,
                'nid' => $nid
            )
        );
        return $this->assign($device_collection, $type, $content, $title, $args, $environment);
    }

    /**
     * 通知，推送给所有用户
     *
     * @param string $content
     *            内容，正文
     * @param string $title
     *            标题（安卓需要）
     * @param array $args
     *            自定义参数（推送目标之类的参数）
     * @param int $type
     *            0全部,1IOS,2安卓,3=1+2,4商家,
     */
    public function pushAllDevices($content, $title, $args = array(), $type = 0)
    {
        if (! $type || self::DEVICE_TYPE_IOS & $type)
        {
            $this->_ios_push->pushAllDevices($content, $title, $args);
        }
        if (! $type || self::DEVICE_TYPE_ANDROID & $type)
        {
            $this->_android_push->pushAllDevices($content, $title, $args);
        }
        if (! $type || self::DEVICE_TYPE_WEIXIN & $type)
        {
            $this->_weixin_push->pushAllDevices($content, $title, $args);
        }
    }

    /**
     * 批量推送信息，
     * 分类，分配，获取结果。
     *
     * @param array $device_collection
     *            array(id,device_token,device_type,user_type)
     * @param string $content
     *            内容
     * @param string $title
     *            标题（安卓需要）
     * @param array $args
     *            自定义参数
     * @return array $result success,fail
     */
    public function pushCollectionDevice($device_collection, $content, $title = '', $args = array())
    {
        $result = array(
            'success' => array(),
            'fail' => array()
        );
        $temp_group = array();
        foreach ($device_collection as $value)
        {
            if(isset($value['notification']) && 2 != $value['notification'])
            {
                $temp_group[$value['device_type']][] = $value;
            }
        }
        foreach ($temp_group as $key => $temp_devices)
        {
            if ($temp_devices)
            {
                $temp_result = $this->assign($temp_devices, $key, $content, $title, $args);
                if ($temp_result["success"])
                {
                    $result['success'] = $result['success'] ? array_merge($result['success'], $temp_result['success']) : $temp_result['success'];
                }
                if ($temp_result["fail"])
                {
                    $result['fail'] = $result['fail'] ? array_merge($result['fail'], $temp_result['fail']) : $temp_result['fail'];
                }
            }
        }
        return $result;
    }

    /**
     * 根据不同类型的设备号分配到不同的方法
     *
     * @param array $device_collection
     *            array(id,device_token,device_type,ring,vibrate)
     * @param int $type
     *            1.ios 2.Android 8.sms
     * @param strng $content            
     * @param string $title            
     * @return array $result success,fail
     */
    private function assign($device_collection, $type, $content, $title = '', $args = array(), $environment = 0)
    {
        $result = array(
            "success" => array(),
            "fail" => array()
        );
        if (self::DEVICE_TYPE_IOS == $type)
        {
            $result = $this->_ios_push->pushCollectionDevice($device_collection, $content, $title, $args, $environment);
        }
        elseif (self::DEVICE_TYPE_ANDROID == $type)
        {
            $result = $this->_android_push->pushCollectionDevice($device_collection, $content, $title, $args);
        }
        elseif (self::DEVICE_TYPE_WEIXIN == $type)
        {
            $result = $this->_weixin_push->pushCollectionDevice($device_collection, $content, $title, $args);
        }
        elseif (self::DEVICE_TYPE_SMS == $type)
        {
            $result = $this->_sms_push->pushCollectionDevice($device_collection, $content);
        }
        return $result;
    }
}
?>