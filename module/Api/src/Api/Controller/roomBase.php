<?php
namespace Api\Controller;

/**
 *订单订票接口
 */
class roomBase extends CommonController
{
    /**
     * 请求api
     */
    protected $roomApiUrl;

    /**
     * 交易ID，用于区分报文体的类型
     * @var
     */
    protected $tradeId;

    /**
     * 报文请求时间，格式yyyyMMddHHmmss
     * @var
     */
    private $timeStamp;

    /**
     * 校验码用来校验数据包是否有效
     * validCode=MD5(tradeId+
     * timestamp+ version+ merchantId+key)
     * key由平台分配.（客户端密码）
     * @var
     */
    private $validCode;

    /**
     * 版本号
     */
    protected $versionNo = 1;

    /**
     * 商户Id，对于需要登录后的才能操作的接口都要输入商户Id
     * @var
     */
    public $merchantId;

    /**
     * 商户key
     * @var
     */
    protected $merchantKey;

    /**
     * 商户登录后sessionId，如果需要权限的操作必须需要带tokenId值
     * @var
     */
    protected $tokenId;

    /**
     * 报文头提交参数数组
     * @var array
     */
    private $headRequestArray = ['tradeId', 'timeStamp', 'validCode', 'versionNo','merchantId','tokenId'];

    /**
     * 请求报文体数组
     */
    protected $bodyRequestArray = [];

    /**
     * 响应报文头数组
     * @var array
     */
    private $headReturnArray = ['tradeId', 'timeStamp', 'respCode', 'respMsg'];

    /**
     * 响应报文体数组
     */
    protected $bodyReturnArray = [];

    /**
     * 状态描述
     * @var
     */
    protected $respMsg;

    /**
     * 返回状态码
     * @var
     */
    protected $respCode;

    /**
     * 不需要登录的接口
     */
    private $merchantNotLoginArray = ['merchantLogin'];

    public function __construct()
    {
        parent::__construct();
        $this->timeStamp = date("YmdHis");
    }

    /**
     * PHP公共请求方法(PHP请求JAVA调用)
     */
    public function roomRequest()
    {
        if(!$this->tradeId){
            die('交易ID不能为空！');
        }
        $this->generateValidCode();//对请求数据生成验证码

        $data = ['head'=>[],'body'=>new \stdClass()];

        foreach($this->headRequestArray as $v){
            $data['head'][$v] = $this->$v;
        }
        if($this->bodyRequestArray)
        {
            $data['body'] = [];
            foreach ($this->bodyRequestArray as $v) {
                $data['body'][$v] = $this->$v;
            }
        }

        //给tokenId赋值
        if(!in_array($this->tradeId,$this->merchantNotLoginArray) && !$this->tokenId)
        {
            $merchant_login = new merchantLogin();
            $merchant_login->merchantKey = $this->merchantKey;
            $res = $merchant_login->submit();
            $respond = $this->getRespCode();
            if($respond['respCode'] == 0)
            {
                $this->tokenId = $res->tokenId;
            }
            else
            {
                $this->respCode = 10000;
                $this->respMsg = $respond['respMsg'];
                return false;
            }
        }

        foreach($this->headRequestArray as $v)
        {
            if(!$this->$v && !in_array($this->tradeId,$this->merchantNotLoginArray))
            {
                if($v == 'merchantId' || $v == 'tokenId')
                {
                    $this->respCode = 100;
                    $this->returnStatusCodeMapping();
                    return false;
                }
            }
        }

        $response = $this->getHttpResponsePOST($data);//提交请求参数

        $response_array = json_decode($response, true);

        if($response_array && isset($response_array['head']) && isset($response_array['body'])){
            foreach($this->headReturnArray as $v){
                if(isset($response_array['head'][$v])){
                    $this->$v = $response_array['head'][$v];//响应赋值
                }
            }
            if($this->bodyReturnArray)
            {
                foreach($this->bodyReturnArray as $v){
                    if(isset($response_array['body'][$v])){
                        $this->$v = $response_array['body'][$v];//响应赋值
                    }
                }
            }
        }else{
            $this->respCode = 99;
            $this->returnStatusCodeMapping();
        }
        return $this;
    }

    /**
     * php响应JAVA请求方法
     */
    public function roomReturn()
    {
        $this->returnStatusCodeMapping();
        $request_array = array('head'=>[],'body'=>new \stdClass());
        foreach($this->headReturnArray as $v){
            $request_array['head'][$v] = $this->$v;
        }
        if($this->bodyReturnArray)
        {
            $request_array['body'] = [];
            foreach($this->bodyReturnArray as $v){
                $request_array['body'][$v] = $this->$v;
            }
        }
        $json = json_encode($request_array);
        $file_name = APP_PATH . '/public/uploadfiles/java_room_api_' . date("Ymd") . '.txt';
        file_put_contents($file_name, "\n\r数据返回：" . $json, FILE_APPEND);
        die($json);//响应
    }

    /**
     * 返回状态码映射类
     */
    public function returnStatusCodeMapping()
    {
        if($this->respCode === null){
            die('状态码未设置!');
        }

        $status_desc = array(
            '0' => '成功',
            '99' => '接口处理异常',
            '100' => '请求参数错误',
            '101' => '请求客户端不在授权列表中',
            '102' => '该接口不存在',
            '103' => '请求验证失败',
        );

        if(!array_key_exists($this->respCode, $status_desc)){
            die('返回状态码设置有误!');
        }
        $this->respMsg = $status_desc[$this->respCode];
        return true;
    }


    /**
     *api生成验证码,给merchantKey和roomApiUrl赋值
     */
    protected function generateValidCode()
    {
        $valid_code_array = array('tradeId', 'timeStamp', 'versionNo', 'merchantId', 'merchantKey');
        $valid_code_str = '';
        if(!$this->merchantId)
        {
            return false;
        }
        $merchantTable = $this->getMerchantTable();
        $merchantTable->merchantId = $this->merchantId;
        $merchant_info = $merchantTable->getMerchantInfoByMerchantId();
        if(!$merchant_info || !$merchant_info->merchant_key || !$merchant_info->room_ticket_api)
        {
            return false;
        }
        $this->merchantKey = $merchant_info->merchant_key;
        $this->roomApiUrl = $merchant_info->room_ticket_api;
        foreach($valid_code_array as $v){
            $valid_code_str .= $this->$v;
        }
        $this->validCode = strtoupper(md5($valid_code_str));
        return $this->validCode;
    }

    /**
     * 数据验签
     * @param array $data
     * @return bool
     */
    protected function checkValidCode(array $data)
    {
        if(!isset($data['validCode'])){
            return false;
        }
        foreach($data as $k => $v)
        {
            $this->$k = $v;
        }
        $valid_code_str = $this->generateValidCode();
        if($valid_code_str != $data['validCode']){
            return false;
        }
        return $this;//验签成功
    }

    public function getHttpResponsePOST($para)
    {
        $curl = curl_init($this->roomApiUrl);
        $para = json_encode($para);
        $file_name = APP_PATH . '/public/uploadfiles/room_api_' . date("Ymd") . '.txt';
        file_put_contents($file_name, date("Y-m-d H:i:s") . "\n\r数据提交：" . $para . "\n\r", FILE_APPEND);
       // var_dump($para);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $para);// post传输数据
        curl_setopt($curl, CURLOPT_HTTPHEADER,['Content-Type: application/json','Content-Length: ' . strlen($para)]
        );
        $responseText = curl_exec($curl);
        file_put_contents($file_name, '数据返回：' . $responseText . "\n\r", FILE_APPEND);
//        var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

    public function getJavaResponse()
    {
        $file_name = APP_PATH . '/public/uploadfiles/java_room_api_' . date("Ymd") . '.txt';
        $json = file_get_contents("php://input");
        $content = "\n\r".date("Y-m-d H:i:s") . "\n\r java数据提交：" .$json;
        if(!$json)
        {
            die('请求参数不能为空!');
        }

        file_put_contents($file_name, $content, FILE_APPEND);
        $data = json_decode($json, true);
        if(!is_array($data))
        {
            die('请求参数错误!');
        }
        if(!isset($data['head']) || !isset($data['body']))
        {
            die('请求参数错误!');
        }
        $head = $data['head'];
        $body = $data['body'];

        if(!isset($head['tradeId']) || !$head['tradeId'])
        {
            die('请求参数错误!');
        }
        $className = $head['tradeId'];
        switch($className){
            case 'noticeUpdateRoomType':
                $obj = new noticeUpdateRoomType();
                break;
        }
        $check = $obj->checkValidCode($head);
        if(!$check){
            $this->respCode = 103;
            return $this->roomReturn();
        }

        if(!isset($head['tradeId'])){
            $this->respCode = 100;
            return $this->roomReturn();
        }
        if($body && is_array($body))
        {
            foreach ($body as $k=>$v) {
                $this->$k = $v;
            }
        }
        $response = $obj->index();
        return $response;
    }

    /**
     * 获取状态码及描述
     */
    public function getRespCode()
    {
        return array('respCode' => $this->respCode, 'respMsg' => $this->respMsg);
    }

}