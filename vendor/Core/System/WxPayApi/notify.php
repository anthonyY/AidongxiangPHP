<?php
require "lib/WxPay.Api.php";
require_once 'lib/WxPay.Notify.php';
require_once 'example/log.php';
//use Core\System\AiiPush\AiiMyFile;
//初始化日志

class PayNotifyCallBack extends WxPayNotify
{
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		/* $myfile = new AiiMyFile();
	    $myfile->setFileToPublicLog();
		$content = var_export($data,TRUE);
		$myfile->putAtStart($content); */
		return $data;
	}
}
