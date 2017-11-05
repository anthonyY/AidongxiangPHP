<?php
namespace Core\System\WxPayApi;
use Core\System\AiiUtility\Log;
require_once "lib/WxPay.Config.php";
/**
 * 微信公众号发放红包的类 
 * 
 */
class WXHongBao {
    private $mch_id = \WxPayConfig::MCHID;//商户ID写死
    private $wxappid =  \WxPayConfig::APPID;//微信公众号
    private $client_ip = ""; //调用红包接口的主机的IP,服务端IP,写死，即脚本文件所在的IP
    private $apikey = \WxPayConfig::KEY;
    private $total_num = 1;//发放人数。固定值1，不可修改    
    private $nick_name = "一起聚餐"; //红包商户名称
    private $send_name = "一起聚餐";//红包派发者名称
    private $wishing = "红包到手啦，继续去推广！"; // 红包祝福语
    private $act_name = "佣金提现红包"; //活动名称
    private $remark = "发放一个佣金提现红包";
    private $nonce_str = "";
    private $mch_billno = "";
    private $re_openid = "";//接收方的openID    
    private $total_amount = 1 ;//红包金额，单位 分
    private $min_value = 1;//最小金额
    private $max_value = 1; //根据接口要求，上述3值必须一致             
    private $sign = ""; //签名在send时生成    
    private $amt_type; //分裂红包参数，在sendgroup中进行定义，是常量 ALL_RAND 
    
    //证书，在构造函数中定义，注意！
    private $apiclient_cert;
    private $apiclient_key;
    private $root_ca;
    
    //分享参数
    private $isShare = false; //有用？似乎是无用参数，全部都不是必选和互相依赖的参数
    private $share_content = ""; 
    private $share_url ="";
    private $share_imgurl = "";
    private $scene_id = "PRODUCT_5";

    private $wxhb_inited;
    
    private $api_hb_single = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
    
    private $error = "ok"; //init
    


    /**
     * WXHongBao::__construct()
     * 步骤
     * new(openid,amount)
     * setnickname
     * setsend_name
     * setwishing
     * setact_name
     * setremark
     * send()
     * @return void
     */
    function __construct(){
        //好像没有什么需要构造函数做的
        $this->wxhb_inited = false; 
        $this->apiclient_cert = __DIR__ . "/cert/apiclient_cert.pem";
        $this->apiclient_key = __DIR__ . "/cert/apiclient_key.pem";
        $this->root_ca = __DIR__."/WxPayPubHelper/cacert/rootca.pem";    
    }
    
    public function err(){
        return $this->error;
    } 
    public function error(){
        return $this->err();
    }
    /**
     * WXHongBao::newhb()
     * 构造新红包 
     * @param mixed $toOpenId
     * @param mixed $amount 金额分
     * @return void
     */
    public function newhb($toOpenId,$amount,$ip){
        $log = new log('sendRedBag');
        if(!is_numeric($amount)){
            $this->error = "金额参数错误";
            $log->err($this->error());
            return array(false,$this->error());
        }elseif($amount < 0.01 * 100){
            $this->error = "提现金额不能小于1元";
            $log->err($toOpenId.'<<---该用户提现失败,原因为：-->>'.$this->error().',金额为：'.$amount);
            return array(false,$this->error());
        }elseif($amount > 100*100){
            $this->error = "提现金额不能超过100元";
            $log->err($toOpenId.'<<---该用户提现失败,原因为：-->>'.$this->error().',金额为：'.$amount);
            return array(false,$this->error());
        }
        $this->client_ip = $ip;
        $this->gen_nonce_str();//构造随机字串
        $this->gen_mch_billno();//构造订单号
        $this->setOpenId($toOpenId);
        $this->setAmount($amount);
        $this->wxhb_inited = true; //标记微信红包已经初始化完毕可以发送
        
        //每次new 都要将分享的内容给清空掉，否则会出现残余被引用
        $this->share_content= "";
        $this->share_imgurl = "";
        $this->share_url = "";
        return array(true,'ok');
    }
    
    /**
     * WXHongBao::send()
     * 发出红包
     * 构造签名
     * 注意第二参数，单发时不要改动！
     * @return boolean $success
     */
    public function send($url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack",$total_num = 1){
        $log = new log('sendRedBag');
        if(!$this->wxhb_inited){
            $this->error .= "(红包未准备好)";
            $log->err($this->error());
            return array(false,$this->error()); //未初始化完成
        }
        $this->total_num = $total_num;
        $parameters = array(
            'act_name' => $this->act_name,
            'client_ip' => $this->getServerIp(),
            'nick_name' => $this->nick_name,
            'mch_billno' => $this->mch_billno,
            'mch_id' => $this->mch_id,
            'nonce_str' => $this->nonce_str,
            're_openid' => $this->re_openid,
            'remark' => $this->remark,
            'send_name' => $this->send_name,
            'total_amount' => $this->total_amount,
            'total_num' => $this->total_num,
            'wishing' => $this->wishing,
            'wxappid' => $this->wxappid,
            'scene_id' => $this->scene_id,
        );
        //没问题
        $log->info('红包数据：'.var_export($parameters,true));
        $rawString = $this->FormatArray($parameters);
        $signature = $this->Md5Sign($rawString, $this->apikey);//微信MD5签名生成器 - 需要将参数数组转化成为字符串[wxFormatArray方法
        $parameters['sign'] = $signature;
        $data = $this->ArrayToXml($parameters);//将数组解析XML
        $result = $this->HttpsRequestPem($url, $data);//微信带证书提交数据 
    	if($result){
    		$rsxml = simplexml_load_string($result);
            if($rsxml->return_code == 'SUCCESS' ){ //return_code 反映的是接口响应有没有技术层面的错误，例如参数、签名、服务异常等错误
                if($rsxml->result_code == 'SUCCESS'){ //result_code 反映的是业务逻辑上的结果，如账号欠费、下发次数或者金额达到上限等错误
                    $log->info($this->re_openid.'-->红包下发成功');
                    return array(true,'OK');
                }else{
                    $this->error = "(".$rsxml->err_code.")".$rsxml->err_code_des;
                    $log->err($this->error());
                    return array(false,'提现失败');
                }
                
            }else{
                $this->error = $rsxml->return_msg;
                $log->err($this->error());
                return array(false,'提现失败');
            }
            
    	}else{ 
    		$this->error = curl_errno($ch);
            $log->err($this->error());
    		curl_close($ch);
            return array(false,'提现失败');
    	}

    }
    
    /**
     * WXHongBao::sendGroup()
     * 发送裂变红包,参数为裂变数量
     * @param integer $num 3-20
     * @return
     */
    public function sendGroup($num=3){
        $this->amt_type = "ALL_RAND";//$amt; 固定值。发送裂变红包组文档指定参数，随机
        return $this->send($this->api_hb_group,$num);
    }
    
    public function getApiSingle(){
        return $this->api_hb_single;
    }
    
    public function getApiGroup(){
        return $this->api_hb_group;
    }
    
    public function setNickName($nick){
        $this->nick_name = $nick;
    }
    
    public function setSendName($name){
        $this->send_name = $name;
    }
    
    public function setWishing($wishing){
        $this->wishing = $wishing;
    }
    
    /**
     * WXHongBao::setActName()
     * 活动名称 
     * @param mixed $act
     * @return void
     */
    public function setActName($act){
        $this->act_name = $act;
    }
    
    public function setRemark($remark){
        $this->remark = $remark;
    }
    
    public function setOpenId($openid){
        $this->re_openid = $openid;
    }
    
    /**
     * WXHongBao::setAmount()
     * 设置红包金额
     * 文档有两处冲突描述 
     * 一处指金额 >=1 (分钱)
     * 另一处指金额 >=100 < 20000 [1-200元]
     * 有待测试验证！
     * @param mixed $price 单位 分
     * @return void
     */
    public function setAmount($price){
        $this->total_amount = $price;
        $this->min_value = $price;
        $this->max_value = $price;
    }
    //以下方法，为设置分裂红包时使用
    public function setHBminmax($min,$max){
        $this->min_value = $min;
        $this->max_value = $max;
    }
    
    
    public function setShare($img="",$url="",$content=""){
        $this->share_content = $content;
        $this->share_imgurl = $img;
        $this->share_url = $url;
    }
    
    private function gen_nonce_str(){
        $this->nonce_str = strtoupper(md5(mt_rand().time())); //确保不重复而已
    }
    
    
    /**
     * WXHongBao::gen_mch_billno()
     *  商户订单号（每个订单号必须唯一） 
        组成： mch_id+yyyymmdd+10位一天内不能重复的数字。 
        接口根据商户订单号支持重入， 如出现超时可再调用。 
     * @return void
     */
    private function gen_mch_billno(){
        //生成一个长度10，的阿拉伯数字随机字符串
        $rnd_num = array('0','1','2','3','4','5','6','7','8','9');
        $rndstr = "";
        while(strlen($rndstr)<10){
            $rndstr .= $rnd_num[array_rand($rnd_num)];    
        }
        
        $this->mch_billno = $this->mch_id.date("Ymd").$rndstr;
    }
    
    
    /**
     * 以上是现金红包的代码
     * ------------------------
     * 以下是企业付款到个人的代码
     * 
     * */
    
    public function transfersMoney($open_id,$money)
    {    
        $parameters = array(
            'mch_appid' => $this->wxappid,
            'mchid' => $this->mch_id,
            'nonce_str' => $this->NonceStr(32),
            'partner_trade_no' => date('YmdHis').mt_rand(10000,99999),
            'openid' => $open_id,
            'check_name' => 'NO_CHECK', // NO_CHECK, FORCE_CHECK, OPTION_CHECK
            // 're_user_name' => '',
            'amount' => $money,
            'desc' => "提现",
            'spbill_create_ip' => $this->getServerIp()
        );
        $rawString = $this->FormatArray($parameters);
        $signature = $this->Md5Sign($rawString, $this->apikey);//微信MD5签名生成器 - 需要将参数数组转化成为字符串[wxFormatArray方法
        $parameters['sign'] = $signature;
    
        $data = $this->ArrayToXml($parameters);//将数组解析XML
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $result = $this->HttpsRequestPem($url, $data);//微信带证书提交数据
        $result = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($result['result_code'] == 'FAIL') {
            return array('status' => 2, 'msg' => $result['return_msg']);
        }
        return array('status' => 0, 'msg' => 'ok');
    }
    
    /**
     * 获取服务器端IP地址
     *
     * @return string
     */
    private function getServerIp()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR']) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            } else {
                $server_ip = $_SERVER['LOCAL_ADDR'];
            }
        } else {
            $server_ip = getenv('SERVER_ADDR');
        }
        return $server_ip;
    }
    
    /**
     * ***************************************************
     * 生成随机字符串 - 最长为32位字符串
     * ***************************************************
     */
    private function NonceStr($length = 16, $type = FALSE)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        if ($type == TRUE) {
            return strtoupper(md5(time() . $str));
        } else {
            return $str;
        }
    }
    
    /**
     * *****************************************************
     * 微信格式化数组变成参数格式 - 支持url加密
     * *****************************************************
     */
    private function FormatArray($parameters = NULL, $urlencode = FALSE)
    {
        if (is_null($parameters)) {
            $parameters = $this->parameters;
        }
        $restr = ""; // 初始化空
        ksort($parameters); // 排序参数
        foreach ($parameters as $k => $v) { // 循环定制参数
            if (null != $v && "null" != $v && "sign" != $k) {
                if ($urlencode) { // 如果参数需要增加URL加密就增加，不需要则不需要
                    $v = urlencode($v);
                }
                $restr .= $k . "=" . $v . "&"; // 返回完整字符串
            }
        }
        if (strlen($restr) > 0) { // 如果存在数据则将最后“&”删除
            $restr = substr($restr, 0, strlen($restr) - 1);
        }
        return $restr; // 返回字符串
    }
    
    /**
     * *****************************************************
     * 微信MD5签名生成器 - 需要将参数数组转化成为字符串[wxFormatArray方法]
     * *****************************************************
     */
    private function Md5Sign($content, $privatekey)
    {
        try {
            if (is_null($privatekey)) {
                throw new \Exception("财付通签名key不能为空！");
            }
            if (is_null($content)) {
                throw new \Exception("财付通签名内容不能为空");
            }
            $signStr = $content . "&key=" . $privatekey;
            return strtoupper(md5($signStr));
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }
    
    /**
     * *****************************************************
     * 将数组解析XML - 微信红包接口
     * *****************************************************
     */
    private function ArrayToXml($parameters = NULL)
    {
        if (is_null($parameters)) {
            $parameters = $this->parameters;
        }
    
        if (! is_array($parameters) || empty($parameters)) {
            die("参数不为数组无法解析");
        }
    
        $xml = "<xml>";
        foreach ($parameters as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }
    
    /**
     * **************************************************
     * 微信带证书提交数据 - 微信红包使用
     * **************************************************
     */
    private function HttpsRequestPem($url, $vars, $second = 30, $aHeader = array())
    {
        $ch = curl_init();
        // 超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 这里设置代理，如果有的话
        // curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');//HTTP代理通道。
        // curl_setopt($ch,CURLOPT_PROXYPORT, 8080);//代理服务器的端口。端口也可以在CURLOPT_PROXY中进行设置。
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//禁用后cURL将终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  FALSE);//CURLOPT_SSL_VERIFYPEER禁用,需要被设置FALSE。
    
        // 以下两种方式需选择一种
        
        // 第一种方法，cert 与 key 分别属于两个.pem文件
        // 默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');//证书的类型。支持的格式有"PEM" (默认值), "DER"和"ENG"。
        curl_setopt($ch, CURLOPT_SSLCERT,$this->apiclient_cert );//一个包含PEM格式证书的文件名。
        // 默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型，支持的密钥类型为"PEM"(默认值)、"DER"和"ENG"。
        curl_setopt($ch, CURLOPT_SSLKEY,$this->apiclient_key);//包含SSL私钥的文件名。

        curl_setopt($ch, CURLOPT_CAINFO, 'PEM');//设置证书
        curl_setopt($ch, CURLOPT_CAINFO, $this->root_ca);//一个保存着1个或多个用来让服务端验证的证书的文件名。这个参数仅仅在和CURLOPT_SSL_VERIFYPEER一起使用时才有意义。
        // 第二种方式，两个文件合成一个.pem文件
        // curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
    
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
    
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }
}
?>