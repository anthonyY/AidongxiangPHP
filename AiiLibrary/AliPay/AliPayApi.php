<?php
use AiiLibrary\Alipay;
/* *
 * 功能：支付宝手机网站支付接口(alipay.trade.wap.pay)接口调试入口页面
 * 版本：2.0
 * 修改日期：2016-11-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 请确保项目文件有可写权限，不然打印不了日志。
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wappay/service/AlipayTradeService.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
class AlipayApi
{

    //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
    //应用ID,您的APPID。
    protected $app_id = ALIPAY_APP_ID;

    //商户私钥，您的原始格式RSA私钥
    protected $merchant_private_key = ALIPAY_MERCHANT_PRIVATE_KEY;

    //应用ID,您的APPID。
    protected $app_app_id = ALIPAY_APP_APP_ID;

    //卖家支付宝帐号
    protected $seller_id = ALIPAY_APP_SELLER_ID;

    //商户私钥，您的原始格式RSA私钥
    protected $app_merchant_private_key = ALIPAY_APP_MERCHANT_PRIVATE_KEY;

    //支付宝H5公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    private $alipay_public_key = ALIPAY_PUBLIC_KEY;

    //支付宝APP公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    private $alipay_app_public_key = ALIPAY_APP_PUBLIC_KEY;

    //异步通知地址
    protected $notify_url = ALIPAY_NOTIFY_URL;

    //同步跳转
    public $return_url = ALIPAY_RETURN_URL;

    //编码格式
    protected $charset = 'UTF-8';
    //签名方式
    private $sing_type = 'RSA';

    //支付宝网关
    private $gatewayUrl = 'https://openapi.alipay.com/gateway.do';

    //超时时间
    private $timeout_express = "1m";

    /**************************请求参数**************************/
    //商户网站订单系统中唯一订单号，必填
    public $out_trade_no = '';

    //订单名称
    public $subject = '';


    //付款金额
    public $total_amount = '';

    //订单描述
    public $body = '';

    /**
     * 支付宝配置信息
     * @var
     */
    private $config;

    /**
     * 初始化配置参数
     * alipayApi constructor.
     */
    public function __construct()
    {
        return $this->config = array(
            'app_id' => $this->app_id,
            'merchant_private_key' => $this->merchant_private_key,
            'notify_url' => $this->notify_url,
            'return_url' => $this->return_url,
            'charset' => $this->charset,
            'sign_type' => $this->sing_type,
            'gatewayUrl' => $this->gatewayUrl,
            'alipay_public_key' => $this->alipay_public_key
        );

    }

    /**
     * APP支付参数
     * @return array
     */
    public function AppParameter()
    {
        return $parameter = array(
            "partner" =>  $this->app_app_id, //签约的支付宝账号对应的支付宝唯一用户号
            "seller_id" => $this->seller_id, //卖家支付宝账号
            "out_trade_no" => $this->out_trade_no,
            "subject" => $this->subject,
            "body" => $this->body,
            "total_fee" => round($this->total_amount,2),
            "notify_url" => $this->notify_url, //支付宝服务器主动通知商户网站里指定的页面http路径(异步)
            "service" => "mobile.securitypay.pay",//接口名称，固定值
            "payment_type" => "1", //接口名称，固定值
            "_input_charset" => trim($this->charset),
            "it_b_pay" => "30m",
        );
    }

    /**
     * App端建立请求参数
     * @return string  返回请求参数
     * 2016-10-11
     */
    public function AppPostAlipay(){
        $data = $this->AppParameter();
        $data = $this->appCreateLinkstring($data);
        $rsa_sign= urlencode($this->rsaSign($data));
        //把签名得到的sign和签名类型sign_type拼接在待签名字符串后面。
        $data = $data.'&sign='.'"'.$rsa_sign.'"'.'&sign_type='.'"'.$this->sing_type.'"';
        return $data;
    }

    /**
     * 将数组转换成URL参数格式
     * @param $para
     * @return string
     */
    protected function appCreateLinkstring($para) {
        ksort($para);
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key.'="'.$val.'"&';
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }

    /**
     * RSA签名
     * @param $data 待签名数据
     * @param $private_key_path 商户私钥文件路径
     * return 签名结果
     */
    function rsaSign($data) {
        $priKey = $this->app_merchant_private_key;
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($priKey, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
       // echo '私钥：'.$res;
        //echo '待加密字符：'.$data;
        $res = openssl_get_privatekey($res);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        //echo '生成的加密串：'.$sign;
        return $sign;
    }

    /**
     * @param $type 支付类型 1H5支付 2APP支付
     * 支付提交
     */
    public function submitPay($type = 1)
    {
        if($type ==2)
        {//老版本APP支付
            return $this->AppPostAlipay();
        }
        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($this->body);
        $payRequestBuilder->setSubject($this->subject);
        $payRequestBuilder->setOutTradeNo($this->out_trade_no);
        $payRequestBuilder->setTotalAmount(round($this->total_amount,2));
        $payRequestBuilder->setTimeExpress($this->timeout_express);
        if($type == 2)
        {//新版本APP支付
            $this->config['app_id'] = $this->app_app_id;
            $this->config['merchant_private_key'] = $this->app_merchant_private_key;
            $payRequestBuilder->setProductCode();
        }
        $payResponse = new AlipayTradeService($this->config);

        $result = $payResponse->wapPay($payRequestBuilder, $this->return_url, $this->notify_url,$type);
        return $result;
    }

    /**
     * 异步回调验签
     */
    public function notifyCheck()
    {
        if(!isset($_POST['version']))
        {//旧版本异步验签，更改公钥
            $this->config['alipay_public_key'] = $this->alipay_app_public_key;
        }
        if(isset($_POST['fund_bill_list']))
        {
            $_POST['fund_bill_list'] = stripcslashes(html_entity_decode($_POST['fund_bill_list']));
        }
        $alipaySevice = new AlipayTradeService($this->config);
        $alipaySevice->writeLog(var_export($_POST, true));

        $result = $alipaySevice->check($_POST);
        return $result;
    }
}