<?php
/**
 * APP_API支付类 (鲁铁辉2015-04-21). 
 * ====================================================
 * 在第三方APP里面调起微信支付。参考:http://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=8_1。
 * 成功调起支付需要二个步骤：
 * 步骤1：使用统一支付接口，获取prepay_id
 * 步骤2：支付签名并返回
*/
include_once(__DIR__."/WxPayPubHelper/AppApi_pub.php");

//=========步骤1：使用统一支付接口，获取prepay_id============
//使用统一支付接口
$unifiedOrder = new UnifiedOrder_pub();

//设置统一支付接口参数
//设置必填参数
//appid已填,商户无需重复填写
//mch_id已填,商户无需重复填写
//noncestr已填,商户无需重复填写
//spbill_create_ip已填,商户无需重复填写
//sign已填,商户无需重复填写
//$unifiedOrder->setParameter("openid","$openid");//商品描述
$unifiedOrder->setParameter("body","贡献一分钱".$_GET['product_name']);//商品描述
//自定义订单号，此处仅作举例
$timeStamp = time();
$out_trade_no = WxPayConf_pub::APPID."$timeStamp";
$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号 
$unifiedOrder->setParameter("total_fee",$_GET['order_price']*100);//总金额
$unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址 
$unifiedOrder->setParameter("trade_type","APP");//交易类型
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

//=========步骤2：使用jsapi调起支付============
$outparams = array();
if ($prepay_id != null) {
	$appApi = new AppApi_pub();
	$appApi->setPrepayId($prepay_id);
	
	$outparams = $appApi->getParameters();
	$outparams['retcode'] 	= 0;
	$outparams['retmsg'] 	= 'ok';
}
else{
	$outparams['retcode'] = -2;
	$outparams['retmsg']  = '错误：获取prepayId失败';
}


/**
=========================
输出参数列表
=========================
*/
//Json 输出
ob_clean();
echo json_encode($outparams);
?>
