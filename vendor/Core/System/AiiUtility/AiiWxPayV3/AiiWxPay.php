<?php
namespace Core\System\AiiUtility\AiiWxPayV3;
//---------------------------------------------------------
//微信支付服务器签名支付请求示例，商户按照此文档进行开发即可
//---------------------------------------------------------
include_once(__DIR__."/WxPayPubHelper/AppApi_pub.php");

class AiiWxPay
{
    /**
     * 订单价格
     */
    private $order_price;
    
    /**
     * 产品名称
     */
    private $product_name;
    
    /**
     * 外部订单号
     */
    private $out_trade_no;
    
    /**
     * 是否输出条受
     * @val boolean
     */
    private $_debug;
    
    /**
     * 财付通商户号
     * @var string
     */
    private $_partner;
    
    /**
     * 财付通密钥
     */
    private $_partner_key;
    
    /**
     * appid
     */
    private $_app_id;
    
    /**
     * appsecret
     */
    private $_app_secret;
    
    /**
     * paysignkey(非appkey)
     */
    private $_app_key;
    
    /**
     * 支付完成后的回调处理页面,*替换成notify_url.asp所在路径
     */
    private $_notify_url;
    
    /**
     * 用来判断是否已经传入值
     */
    private $isSetValue = false;
    
    function __construct() {
        $this->init();
    }
    
    private function init()
    {
    }
    
    /**
     * 设置基本属性
     * @param unknown $value
     * @version 2015-4-10 WZ
     */
    function setValue($value)
    {
		$list = array(
            'order_price',
            'product_name',
            'out_trade_no'
        );
        $this->isSetValue = true;
        foreach ($list as $key)
        {
            if (isset($value[$key])) {
                $this->$key = $value[$key];
            }
            else {
                $this->isSetValue = false;
            }
        }
        return $this;
    }
    
    /**
     * @param $trade_type APP,JSAPI
     * 
     * APP_API支付类 (鲁铁辉2015-04-21).
     * ====================================================
     * 在第三方APP里面调起微信支付。参考:http://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=8_1。
     * 成功调起支付需要二个步骤：
     * 步骤1：使用统一支付接口，获取prepay_id
     * 步骤2：支付签名并返回
     */
    function getOutParams($trade_type, $openid)
    {
        if (! $this->isSetValue)
        {
            $outparams['retcode']=-3;
            $outparams['retmsg']='还没设置基本属性';
        }
        //=========步骤1：使用统一支付接口，获取prepay_id============
        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub();
        
        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $unifiedOrder->setParameter("body", $this->product_name);//商品描述
        //自定义订单号，此处仅作举例
        $timeStamp = time();
//         $out_trade_no = WxPayConf_pub::APPID."$timeStamp";
        $unifiedOrder->setParameter("out_trade_no",$this->out_trade_no);//商户订单号 
        $unifiedOrder->setParameter("total_fee",(string)($this->order_price*100));//总金额
        $unifiedOrder->setParameter("notify_url",\WxPayConf_pub::NOTIFY_URL);//通知地址 
        $unifiedOrder->setParameter("trade_type",$trade_type);//交易类型
        if ("JSAPI" == $trade_type) {
            $unifiedOrder->setParameter("openid","$openid");//商品描述
        }
        if ("NATIVE" == $trade_type) {
            $unifiedOrder->setParameter("product_id","$openid");//商品ID
        }
        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
        //$unifiedOrder->setParameter("device_info","XXXX");//设备号 
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据 
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
        $prepay_id = $unifiedOrder->getPrepayId();
        //return $prepay_id;
        //微信扫描支付数据
        #Vendor("phpqrcode/phpqrcode");          //引入第三方二维码生成
        if("NATIVE" == $trade_type){
            include_once __DIR__ .'/phpqrcode.php';
            //获取微信图片流
            ob_start();
            \QRcode::png($prepay_id["code_url"], false, QR_ECLEVEL_M, 10, 2);
            $image = base64_encode(ob_get_contents());
            ob_end_clean();

            header("Content-Type:text/html");           //变成正规html输出
            return $image;
        } else {
            //=========步骤2：使用jsapi调起支付============
            $prepay_id = $prepay_id["prepay_id"];
            $outparams = array();
            if ($prepay_id != null) {
                $appApi = new \AppApi_pub();
                $appApi->setPrepayId($prepay_id);

                $outparams = $appApi->getParameters($trade_type);
                $outparams['retcode'] 	= 0;
                $outparams['retmsg'] 	= 'ok';
            }
            else{
                $outparams['retcode'] = -2;
                $outparams['retmsg']  = '错误：获取prepayId失败';
            }

            return $outparams;
        }


        

    }
    
}
