<?php
namespace Admin\Controller;


class CosController extends CommonController{


    public function getSignAction(){
        $sign = self::appSign();
        $json = array('code' => '0', 'message' => '成功', 'sign' => $sign);
        die(json_encode($json));
    }

    private static function appSign() {
        $appId = COS_APP_ID;
        $secretId = COS_SECRET_ID;
        $secretKey = COS_SECRET_KEY;
        $bucketName = COS_BUCKET;
        return self::appSignBase($appId, $secretId, $secretKey,$bucketName);
    }


    private static function appSignBase($appId, $secretId, $secretKey,$bucketName) {
        $expired = time() + 600;
        $now = time();
        $rdm = rand();

        $multi_effect_signature = 'a='.$appId.'&b='.$bucketName.'&k='.$secretId.'&e='.$expired.'&t='.$now.'&r='.$rdm.'&f=';
        $multi_effect_signature = base64_encode(hash_hmac('SHA1', $multi_effect_signature, $secretKey, true).$multi_effect_signature);
        return $multi_effect_signature;
    }
}