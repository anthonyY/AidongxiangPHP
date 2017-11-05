<?php
namespace Qcloud_cos;
class Auth
{
    const APPID = "1252925600";
    const SECRET_ID = "AKID0ta5QaqKrFTVPCwMhplV2NXZP7iBtBoK";
    const SECRET_KEY = "UmsmU0rX1MYYLwUvAFD0d4JNGxSlurbf";
    /**
     * 生成多次有效签名函数（用于上传和下载资源，有效期内可重复对不同资源使用）
     * @param  int $expired    过期时间,unix时间戳  
     * @param  string $bucketName 文件所在bucket
     * @return string          签名
     */
    public static function appSign($bucketName,$expired) {
        $appId = self::APPID;
        $secretId = self::SECRET_ID;
        $secretKey = self::SECRET_KEY;
        return self::appSignBase($appId, $secretId, $secretKey, $expired, null, $bucketName);
    }
   
    
    private static function appSignBase($appId, $secretId, $secretKey, $expired, $fileId, $bucketName) {
        $expired = time() + 600;
        $now = time();
        $rdm = rand();
        
        $multi_effect_signature = 'a='.$appId.'&b='.$bucketName.'&k='.$secretId.'&e='.$expired.'&t='.$now.'&r='.$rdm.'&f=';
        $multi_effect_signature = base64_encode(hash_hmac('SHA1', $multi_effect_signature, $secretKey, true).$multi_effect_signature);
        return $multi_effect_signature;
    }
}

if($_GET['bucketName'] && $_GET['expired']){
    $sign = Auth::appSign($_GET['bucketName'],$_GET['expired']);
    $json = array('code' => '0', 'message' => '成功', 'data' => array('sign' => $sign));
    echo json_encode($json);
}
