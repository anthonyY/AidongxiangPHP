<?php
namespace Core\System\AiiPush;

/**
 * 基于友盟开发的推送类
 * @author WZ
 *
 */
class Umeng
{
	private $config_file = 'config/umeng_config.php';
    private $myfile;
    
    /**
     * 构造函数
     * @param string $masterSecret
     * @param string $appkeys
     */
    function __construct() {
    	$this->myfile = new AiiMyFile();
    	$this->init();
    }
    /**
     * 初始化
     */
    private function init() {
    	if (is_file ( PUSH_ROOT . '/'.$this->config_file )) {
    		require_once PUSH_ROOT . '/'.$this->config_file;
    	} else {
    		$this->myfile->put(sprintf(STATUS_12001,$this->config_file),1);
    		die ();
    	}
    }
    
    /**
     * 推送主要函数，push类调用或直接调用
     * @param array $deviceTokens id,device_token,user_type
     * @param string $content 内容
     * @param string $title 标题
     * @param array $args 自定义数据
     * @return array $result success(array) , fail(array)
     */
    function send($deviceTokens,$content,$title , $args = array() ){
    	$result = array(
    			'success' => array (),
    			'fail' => array ()
    	);
    	//     	if (mb_strlen ( $content, 'utf-8' ) > 30) {
    	//     		$content = mb_substr ( $content, 0, 22, 'utf-8' ) . '...';
    	//     	}
    	$tempList = array(); // 临时设备列表
    	$ids_temp = array(); // 临时id列表
    	$ids_f = array();  // 发送失败的id
    	foreach ($deviceTokens as $value) {
    		if(isset($value["device_token"]) && $value["device_token"]) {
    		    $value["user_type"] = isset($value["user_type"]) ? $value["user_type"] : 0 ; // 默认设备为客户端，还是默认为发送失败？
    		    $user_type = $this->checkFromType($value["user_type"]);   // 1:用户 2:司机
    		    
    			$tempList [$user_type][] = $value["device_token"];   //  分类存储
    			$ids_temp [$user_type][] = $value["id"]; // id也分类存储
    			
    			if(count($tempList [$user_type]) >= UMENT_LIMIT ) { // 达到最大发送限制，先发送
    			    $res_arr = $this->postReady ( $tempList [$user_type] , $content , $title , $user_type , $args);
    			    if(!$res_arr) { // 连接有问题，返回信息不正常，视为发送失败
    			    	$result ['fail'] = array_merge($result ['fail'] , $ids_temp [$user_type]);
    			    } elseif ($res_arr ['ret'] == 'SUCCESS') { // 返回正常，返回成功，记录成功id
    			    	$result ['success'] = array_merge($result ['success'] , $ids_temp [$user_type]);
    			    } else { // 返回正常，返回失败，记录失败id
    			    	$result ['fail'] = array_merge($result ['fail'] , $ids_temp [$user_type]);
    			    }
    			    $tempList [$user_type] = array();
    			    $ids_temp [$user_type] = array();
    			}
    		}else{
    		    // 记录没有设备号的id
    			$ids_f [] = $value["id"];
    		}
    	}
    	// 确认有没有漏，漏了再发送，不过大部分情况都是在这里发送，很少在上面达到最大发送限制，上面那个只是防范于未然。
    	foreach ($tempList as $user_type => $list) {
    	    if(count($list) > 0 ) { // 非空就发送
    	    	$res_arr = $this->postReady ( $list , $content , $title , $user_type , $args);
    	    	if(!$res_arr) { // 连接有问题，返回信息不正常，视为发送失败
    	    		$result ['fail'] = array_merge($result ['fail'] , $ids_temp [$user_type]);
    	    	} elseif ($res_arr ['ret'] == 'SUCCESS') { // 返回正常，返回成功，记录成功id
    	    		$result ['success'] = array_merge($result ['success'] , $ids_temp [$user_type]);
    	    	} else { // 返回正常，返回失败，记录失败id
    	    		$result ['fail'] = array_merge($result ['fail'] , $ids_temp [$user_type]);
    	    	}
    	    }
    	}
    	$result ['fail'] = array_merge($result ['fail'] , $ids_f);
    	return $result;
    }
    
    /**
     * 整理数据然后发送，然后返回结果
     * @param array $device_tokens 设备列表，只有设备号
     * @param string $content 显示内容
     * @param string $title 显示标题
     * @param number $type 用户类型 1：用户 ， 2：司机
     * @param array $args 自定义类型
     * @return boolean|array ret: SUSSESS|FAIL
     */
    private function postReady( array $device_tokens , $content , $title , $user_type = 1 , $args = array() ) {
    	$url = 'http://msg.umeng.com/api/send';
    	$config = $this->type2key($user_type);
    	$AppKey = $config["AppKey"];
    	$AppMasterSecrete = $config["AppMasterSecrete"];
    	/* 参数准备 */
    	$timestamp = time();
    	$validation_token = md5($AppKey.$AppMasterSecrete.$timestamp);
    	$payload = array();
    	$payload ['display_type'] = 'notification' ; // notification-通知, message-消息.
    	$body = array();   // $payload的body
        $body["ticker"] = "【回头车】新消息通知...";    // 必填 通知栏提示文字
        $body["title"] = $title;    // 必填 通知标题
        $body["text"] = $content;      // 必填 通知文字描述
        $body["custom"] = $args;      // 选填 自定义参数
        $payload ["body"] = $body;
    	$param = '';
        $data = array();
        $data ['appkey'] = $AppKey;
        $data ['timestamp'] = $timestamp;
        $data ['validation_token'] = $validation_token;
        $data ['type'] = 'listcast';
        $data ['device_tokens'] = implode(",", $device_tokens);
        $data ['payload'] = $payload;
        $param = json_encode($data);
        /* 发送 */
    	$res = $this->request_post($url, $param);
    	if ($res === false) {
    		return false;
    	}
    	$res_arr = json_decode($res, true);
    	$res_arr['errmsg']= "没有错误信息";
    	if($res_arr['ret'] == 'SUSSESS') {
    	}else{
    	    if(isset($res_arr['data']['error_code'])) {
    	        $res_arr['errmsg'] = $this->api_err_type($res_arr['data']['error_code']);
    	    }
            $this->myfile->put(sprintf($res_arr['errmsg'],$this->config_file),1);
    	}
    	return $res_arr;
    }
    
    /**
     * 模拟post进行url请求
     * @param string $url
     * @param string $param
     */
    private function request_post($url = '', $param = '') {
    	if (empty($url) || empty($param)) {
    		return false;
    	}
    	$postUrl = $url;
    	$curlPost = $param;
    	$ch = curl_init();//初始化curl
    	curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
    	curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); // 2014/4/2 仅用IPv4方式
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    	curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    	curl_setopt($ch, CURLOPT_TIMEOUT, 30);//超时时间
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    	$data = curl_exec($ch);//运行curl
    	curl_close($ch);
    
    	return $data;
    }
    
    /**
     * 根据用户类型，选择使用不同的APPKEY
     * @param number $user_type
     * @return multitype:string
     */
    private function type2key ($user_type) {
        switch ($user_type) {
        	case 1:
        	    // 用户端
        		$AppKey = UMENT_APPKEY_USER;  // 在配置文件设置
        		$AppMasterSecrete = UMENT_APPMASTERSECRETE_USER;  // 在配置文件设置
        		break;
        	case 2:
        	    // 司机端
        		$AppKey = UMENT_APPKEY;  // 在配置文件设置
        		$AppMasterSecrete = UMENT_APPMASTERSECRETE;  // 在配置文件设置
        		break;
        }
        return array("AppKey" => $AppKey , "AppMasterSecrete" => $AppMasterSecrete);
    }
    
    /**
     * 对文字进行转码 Jpush时候用的umeng不需要
     * @param string $title
     * @param string $content
     * @return string
     */
    function makemsg($title,$content){
    	$a_content = array(
    			'n_builder_id'=>0,
    			'n_title'=>$title,
    			'n_content'=>$content
    	);
    	return json_encode($a_content);
    }
    
    /**
     * 2014/3/20
     * 根据用户类型id，辨别是用户还是司机
     *
     * @author WZ
     * @param number $from 用户类型
     * @return Ambigous <multitype:, multitype:string >|boolean
     */
    private function checkFromType($from) {
    	$fromArray = array();
    	$fromArray[] = array();
    	$fromArray[] = array("0","11","12","13");
    	$fromArray[] = array("21");
    	foreach($fromArray as $key => $f) {
    		if(in_array($from,$f)) {
    			return $key;
    		}
    	}
    	return false;
    }
    
    /**
     * 反馈错误信息的号码，转换成具体信息
     * @param number $err_code
     * @return string
     */
    private function api_err_type($err_code) {
    	$errmsg = "没有错误信息";
    	$err_array = array(
    	    '1000' => '请求参数没有appkey',
    	    '1001' => '请求参数没有payload',
    	    '1002' => '请求参数payload中没有body',
    	    '1003' => 'display_type为message时，请求参数没有custom',
    	    '1004' => '请求参数没有display_type',
    	    '1005' => 'img url格式不对，请以https或者http开始',
    	    '1006' => 'sound url格式不对，请以https或者http开始',
    	    '1007' => 'url格式不对，请以https或者http开始',
    	    '1008' => 'display_type为notification时，body中ticker不能为空',
    	    '1009' => 'display_type为notification时，body中title不能为空',
    	    '1010' => 'display_type为notification时，body中text不能为空',
    	    '1011' => 'play_vibrate的值只能为true或者false',
    	    '1012' => 'play_lights的值只能为true或者false',
    	    '1013' => 'play_sound的值只能为true或者false',
    	    '1014' => '请求参数中没有task_id',
    	    '1015' => '请求参数中没有device_tokens',
    	    '1016' => '请求参数没有type',
    	    '1017' => 'production_mode只能为true或者false',
    	    '2000' => '该应用已被禁用',
    	    '2001' => '过期时间必须大于当前时间',
    	    '2002' => '定时发送时间必须大于当前时间',
    	    '2003' => '过期时间必须大于定时发送时间',
    	    '2004' => '尚未开通API服务，请联系msg-support@umeng.com申请开通',
    	    '2005' => '该应用不存在',
    	    '2006' => 'validation token错误',
    	    '2007' => 'appkey或app_master_secret错误',
    	    '2008' => 'json解析错误',
    	    '2009' => '请填写alias或者file_id',
    	    '2010' => '与alias对应的device_tokens为空',
    	    '2011' => 'alias个数已超过50',
    	    '2012' => '此appkey今天的广播数已超过限制',
    	    '2013' => '消息还在排队，请稍候再查询',
    	    '2014' => '消息取消失败，请稍候再试',
    	    '2015' => 'device_tokens个数已超过50',
    	    '2016' => '请填写filter',
    	    '2017' => '添加tag失败',
    	    '2018' => '请填写file_id',
    	    '2019' => '与此file_id对应的文件不存在',
    	    '2020' => '服务正在升级中，请稍候再试',
    	    '2021' => 'appkey不存在',
    	    '3000' => '数据库错误',
    	    '3001' => '数据库错误',
    	    '3002' => '数据库错误',
    	    '3003' => '数据库错误',
    	    '3004' => '数据库错误',
    	    '4000' => '系统错误',
    	    '4001' => '系统忙',
    	    '4002' => '操作失败',
    	    '4003' => 'appkey格式错误',
    	    '4004' => '消息类型格式错误',
    	    '4005' => 'msg格式错误',
    	    '4006' => 'body格式错误',
    	    '4007' => 'deliverPolicy格式错误',
    	    '4008' => '失效时间格式错误',
    	    '4009' => '单个服务器队列已满',
    	    '4010' => '设备号格式错误',
    	    '4011' => '消息扩展字段无效',
    	    '4012' => '没有权限访问',
    	    '4013' => '异步发送消息失败',
    	    '4014' => 'appkey和device_tokens不对应',
    	    '4015' => '没有找到应用信息',
    	    '4016' => '文件编码有误',
    	    '4017' => '文件类型有误',
    	    '4018' => '文件远程地址有误',
    	    '4019' => '文件描述信息有误',
    	    '4020' => 'device_token有误(注意，友盟的device_token是严格的44位字符串)',
    	    '4021' => 'HSF异步服务超时',
    	    '4022' => 'appkey已经注册',
    	);
    	if(array_key_exists($err_code, $err_array)) {
    		$errmsg = $err_array[$err_code];
    	}
    	return $errmsg;
    }
}

?>