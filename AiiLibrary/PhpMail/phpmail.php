<?php
require("class.phpmailer.php");
 
$mail = new PHPMailer();
 
$mail->IsSMTP();                 // 启用SMTP
$mail->Host = "smtp.163.com";           //SMTP服务器
$mail->SMTPAuth = true;                  //开启SMTP认证
$mail->Username = "wplamps2013@163.com";            // SMTP用户名
$mail->Password = "michael168";                // SMTP密码
 
$mail->From = "wplamps2013@163.com";            //发件人地址
$mail->FromName = "liu";              //发件人
$mail->AddAddress("1292405261@qq.com", "Josh Adams"); //添加收件人
$mail->AddReplyTo("58630540@qq.com", "Information");    //回复地址
$mail->WordWrap = 50;                    //设置每行字符长度
/** 附件设置
18.$mail->AddAttachment("/var/tmp/file.tar.gz");        // 添加附件
19.$mail->AddAttachment("/tmp/image.jpg", "new.jpg");   // 添加附件,并指定名称
20.*/
$mail->IsHTML(true);                 // 是否HTML格式邮件
 
$mail->Subject = "Here is the subject";          //邮件主题
$mail->Body    = "This is the HTML message body <b>in bold!</b><img src='http://www.szcarmate.ru/images/company01.jpg'/>";        //邮件内容
$mail->AltBody = "This is the body in plain text for non-HTML mail clients"; //邮件正文不支持HTML的备用显示

if(!$mail->Send())
{
echo "Message could not be sent. <p>";
echo "Mailer Error: " . $mail->ErrorInfo;
exit;
}
 
echo "Message has been sent";