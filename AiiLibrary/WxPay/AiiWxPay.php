<?php
namespace AiiLibrary\WxPayApi;

require_once "lib/WxPay.Api.php";
require_once "lib/WxPay.Config.php";
require_once "example/WxPay.JsApiPay.php";
require_once "example/WxPay.NativePay.php";
class AiiWxPay
{
    /**
     * 订单总价格
     */
    private $total_fee;

    /**
     * 外部订单号
     */
    private $out_trade_no;

    /**
     * 商品描述
     */
    private $body;

    /**
     * 商户退款单号（退款用）
     */
    private $out_refund_no;

    /**
     * 退款金额 （退款用）
     */
    private $refund_fee;

    /**
     * 操作员ID （退款用）
     */
    private $op_user_id;

    /**
     * JSapi 支付用的openid
     * @var unknown
     */
    private $openid;

    private $transaction_id;

    function __construct()
    {
        $this->init();
    }

    private function init()
    {
    }

    /**
     * 设置基本属性
     * @param array $value
     * @param integer $type  接口类型 1统一下单  2 查询订单 3 退款  4 退款结果查询
     * @version 2015-07-28
     */
    public function setValue($value,$type=1)
    {
        $value_array = array(1,2,3,4);
        if(!in_array($type, $value_array))
        {
            throw new \WxPayException("接口类型错误！");
        }

        $list = array(
            1 => array(
                'total_fee',
                'body',
                'out_trade_no'
            ),
            2 => array(
                'out_trade_no'
            ),
            3 => array(
                'transaction_id',
                'out_refund_no',
                'total_fee',
                'refund_fee',
                'op_user_id'
            ),
            4 => array(
                'transaction_id'
            )
        );


        $this->isSetValue = true;
        foreach ($list[$type] as $v)
        {
            if (isset($value[$v]) && $value[$v])
            {
                $this->$v = $value[$v];
            }
            else
             {
                $this->isSetValue = false;
            }
        }

        return $this;
    }

   /**
    *
    * APP支付下单 获取微信支付参数
    * @version 2015年7月27日
    * @param $type 1 用户端 2 商家端
    * @author liujun
    */
  public function getAppParams()
    {
        $appApiObj = $this->checkParameter();//检查参数是否设置完整
        if($appApiObj){
            return $appApiObj;
        }
       //先下预支付订单
       $result =$this->unifiedOrder("APP");
       $prepay_id =	$prepay_id = isset($result["prepay_id"]) ? $result["prepay_id"] : null ;

        if ($prepay_id != null) {
           $WxPayResults = new \WxPayResults();
           $appApiObj = array();
		   $appApiObj['appid']			=  $result['appid'];
		   $appApiObj['partnerid']		= $result['mch_id'];
		   $appApiObj['prepayid']		= $result["prepay_id"];
		   $appApiObj['package']		= 'Sign=WXPay';
		   $appApiObj['noncestr']		= self::getNonceStr();
		   $appApiObj['timestamp']	= time();

		   $WxPayResults->FromArray($appApiObj);
		   $WxPayResults->type = "APP";
		   $sign = $WxPayResults->SetSign();

		   $appApiObj['sign']			= $sign ;
           $appApiObj['retcode'] 	= 0;
           $appApiObj['retmsg'] 	= 'ok';
        }
        else
        {
            $appApiObj['retcode'] = -2;
            $appApiObj['retmsg']  = '错误：获取prepayId失败';
        }
        return $appApiObj;
    }

    /**
     * 扫码支付下单
     *
     * @return Ambigous <multitype:number , multitype:number string >
     * @version 2015年7月27日
     * @author liujun
     */
    public function getNative()
    {
        $appApiObj = $this->checkParameter();//检查参数是否设置完整
        if($appApiObj)
        {
            return $appApiObj;
        }
        //先下预支付订单
        $result =$this->unifiedOrder("NATIVE");
         return $result;
    }

    /**
     * JS支付下单，获取返回的参数
     * @version 2015年7月31日
     * @author liujun
     */
    public function getJsPay()
    {
        $appApiObj = $this->checkParameter();//检查参数是否设置完整
        if($appApiObj)
        {
            return $appApiObj;
        }
        $jsApi = new \JsApiPay();
        $result =$this->unifiedOrder("JSAPI");
        $result = $jsApi->GetJsApiParameters($result);
        return $result;
    }

    /**
     * h5支付下单，获取返回的参数
     * @version 2015年7月31日
     * @author liujun
     */
    public function getMwebPay()
    {
        $appApiObj = $this->checkParameter();//检查参数是否设置完整
        if($appApiObj)
        {
            return $appApiObj;
        }
        $result =$this->unifiedOrder("MWEB");
        return $result;
    }

    /**
     * 查询订单支付状态
     * @param unknown $prepay_id
     * @version 2015年7月28日
     * @author liujun
     */
    public function orderQuery()
    {
        $appApiObj = $this->checkParameter();//检查参数是否设置完整
        if($appApiObj)
        {
            return $appApiObj;
        }
        $wxPayData = new \WxPayOrderQuery();
        $wxPayData->SetOut_trade_no($this->out_trade_no);
        $result = \WxpayApi::orderQuery($wxPayData);
        return $result;
    }

    /**
     * 微信退款
     * @version 2015年7月28日
     * @param 1公众号 2APP
     * @author liujun
     */
    public function refund($type=1)
    {
        $appApiObj = $this->checkParameter();//检查参数是否设置完整
        if($appApiObj)
        {
            return $appApiObj;
        }
        $wxPayData = new \WxPayRefund();
        $wxPayData->setType($type);
        $wxPayData->SetTransaction_id($this->transaction_id);//商户订单号
        $wxPayData->SetTotal_fee($this->refund_fee*100);//总金额
        $wxPayData->SetOut_refund_no($this->out_refund_no);//商户退款单号
        $wxPayData->SetRefund_fee($this->refund_fee*100);//退款金额
        $wxPayData->SetOp_user_id($this->op_user_id);//操作员ID
        $result = \WxpayApi::refund($wxPayData,6,$type);
        return $result;
    }

    /**
     * 微信退款结果查询
     * @version 2017.03.10
     * @param 1公众号 2APP
     */
    public function refundQuery($type=1)
    {
        $appApiObj = $this->checkParameter();//检查参数是否设置完整
        if($appApiObj)
        {
            return $appApiObj;
        }
        $wxPayData = new \WxPayRefundQuery();
        $wxPayData->setType($type);
        $wxPayData->SetTransaction_id($this->transaction_id);//商户订单号
        $result = \WxpayApi::refundQuery($wxPayData,6,$type);
        return $result;
    }


    /**
     * 检查参数是否设置完整
     * @return multitype:number string
     * @version 2015年7月27日
     * @author liujun
     */
    public function checkParameter()
    {
        $outparams = array();
        if (! $this->isSetValue)
        {
            $outparams['retcode']=-3;
            $outparams['retmsg']='基本参数未设置';
        }
        return $outparams;
    }

    /**
     * 统一下单
     * @param integer $type JSAPI，NATIVE，APP
     * @version 2015年7月27日
     * @author liujun
     */
    public function unifiedOrder($type = 'APP')
    {
        $wxPayData = new \WxPayUnifiedOrder();
        $wxPayData->SetBody($this->body);//商品描述
        $wxPayData->SetOut_trade_no($this->out_trade_no);//商户订单号
        $wxPayData->SetTotal_fee($this->total_fee * 100);//总金额
        $wxPayData->SetTrade_type($type);//交易类型
        $wxPayData->SetProduct_id($this->body);
        if($type == "JSAPI")
        {
            $this->setOpenId();
            $wxPayData->SetOpenid($this->openid);
        }
        $result = \WxPayApi::unifiedOrder($wxPayData,6);//生成预付单
        return $result;
    }
    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * 获取微信open_id
     * @version 2015年9月10日
     * @author liujun
     */
    public function getOpenId()
    {
        $jsApi = new \JsApiPay();
        $open_id = $jsApi->GetOpenid();
        return $open_id;
    }

    /**
     * 获取微信open_id
     * @version 2015年9月10日
     * @author liujun
     */
    public function setOpenId($open_id=0)
    {
        $this->openid =$_SESSION['open_id'];

    }
}