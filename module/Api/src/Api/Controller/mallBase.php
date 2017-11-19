<?php
namespace Api\Controller;

/**
 * 积分登记
 *
 *
 */
class mallBase extends CommonController
{
    /**
     * 请求的类型(命名空间)
     * @var
     */
    protected $method;

    /**
     * 协议提交时间
     * @var
     */
    private $timeStamp;

    /**
     * 用户ID
     * @var
     */
    public $userId;

    /**
     * 手机号
     * @var
     */
    public $mobileNo;

    /**
     * 版本号
     */
    protected $versionNo = 1;

    /**
     * 签名
     * @var
     */
    private $sign;

    /**
     * 返回状态码
     * @var
     */
    protected $respCode;


    /**
     * 请求参数数组
     */
    public $request = [];

    /**
     * 响应参数数组
     */
    public $return = [];

    /**
     * 公共提交参数数组
     * @var array
     */
    private $publicRequest = ['method', 'timeStamp', 'userId', 'mobileNo', 'versionNo', 'sign'];

    /**
     * 公共响应参数数组
     * @var array
     */
    private $publicReturn = ['method', 'timeStamp', 'respCode', 'respMsg'];

    /**
     * 状态描述
     * @var
     */
    protected $respMsg;

    public function __construct()
    {
        parent::__construct();
        $this->timeStamp = date("YmdHis");
    }

    /**
     * PHP公共请求方法(PHP请求JAVA调用)
     */
    public function mallRequest()
    {
        if(!$this->method){
            die('请求参数命名空间不能为空！');
        }
        $this->apiSign();//对请求数据进行签名
        $request_array_key = array_merge($this->publicRequest, $this->request);
        $data = array();
        foreach($request_array_key as $v){
            if(in_array($v, array('payAmount', 'rechargeAmount', 'scoreNumber', 'orderAmount', 'refundAmount','withdrawAmount','rebateAmount')) && $this->method != 'mallExchangeScore' && $this->method != 'mallAddScore'){
                $this->$v = $this->$v * 100;
            }
            $data[$v] = $this->$v;

        }
        foreach($this->publicRequest as $v)
        {//循环必填参数
            if(!$this->$v)
            {
                if($v == 'mobileNo')
                {
                    $this->respCode = 100;
                    $this->returnStatusCodeMapping();
                    return false;
                }
                if($v == 'userId')
                {
                    if(!in_array($this->method,array('mallRegisterAccount','mallSendMsg')))
                    {
                        $this->respCode = 100;
                        $this->returnStatusCodeMapping();
                        return false;
                    }
                    else
                    {
                        if($this->method == 'mallSendMsg' && !in_array($this->msgType, array(1, 2, 6, 7,10,11)))
                        {
                            $this->respCode = 100;
                            $this->returnStatusCodeMapping();
                            return false;
                        }
                    }
                }
            }
        }


        $response = $this->getHttpResponsePOST($data);//提交请求参数

        $response_array = json_decode($response, true);
        if($response_array){
            $request_array_key = array_merge($this->publicReturn, $this->return);
            foreach($request_array_key as $v){
                if(isset($response_array[$v])){
                    if(in_array($v, array('balance'))){
                        $response_array[$v] = $response_array[$v] / 100;
                    }
                    $this->$v = $response_array[$v];//响应赋值
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
    public function mallReturn()
    {
        $request_array_key = array_merge($this->publicReturn, $this->return);
        $this->returnStatusCodeMapping();
        $request_array = array();
        foreach($request_array_key as $v){
            $request_array[$v] = $this->$v;
        }
        $json = json_encode($request_array);
        $file_name = APP_PATH . '/public/uploadfiles/java_api_' . date("Ymd") . '.txt';
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
            '103' => '请求签名验证失败',
            '200' => '请求帐户不存在',
            '300' => '请求用户不存在'
        );

        if(!array_key_exists($this->respCode, $status_desc)){
            die('返回状态码设置有误!');
        }
        $this->respMsg = $status_desc[$this->respCode];
        return true;
    }


    /**
     *api签名
     */
    protected function apiSign()
    {
        $sign_array = array('method', 'timeStamp', 'userId', 'mobileNo');
        $sign_str = '';
        foreach($sign_array as $v){
            $sign_str .= $this->$v;
        }
        $this->sign = strtoupper(substr(md5($sign_str . KTX_API_KEY), 0, 16));

        return $this->sign;
    }

    /**
     * 数据验签
     * @param array $data
     * @return bool
     */
    protected function checkSign(array $data)
    {
        if(!isset($data['sign'])){
            return false;
        }
        foreach($data as $k => $v)
        {
            $this->$k = $v;
        }
        $sign_str = $this->apiSign();
        if($sign_str != $data['sign']){
            return false;
        }
        return $this;//验签成功
    }

    public function getHttpResponsePOST($para)
    {
        $curl = curl_init(KTX_API_URL);
        $para = json_encode($para);
        $file_name = APP_PATH . '/public/uploadfiles/api_' . date("Ymd") . '.txt';
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
        $file_name = APP_PATH . '/public/uploadfiles/java_api_' . date("Ymd") . '.txt';
        $json = file_get_contents("php://input");
        $content = "\n\r".date("Y-m-d H:i:s") . "\n\r java数据提交：" .$json;
//        $content = isset($_REQUEST['json']) ? $_REQUEST['json'] : '';
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

        if(!isset($data['method']) || !$data['method'])
        {
            die('请求参数错误!');
        }
        $className = $data['method'];
        switch($className){
            case 'mallQueryAccountBalanceScore':
                $obj = new mallQueryAccountBalanceScore();
                break;
            case 'mallPaymentByBalance':
                $obj = new mallPaymentByBalance();
                break;
            case 'mallAccountBalanceRecharge':
                $obj = new mallAccountBalanceRecharge();
                break;
            case 'mallExchangeScore':
                $obj = new mallExchangeScore();
                break;
            case 'mallAddScore':
                $obj = new mallAddScore();
                break;
            case 'mallUpdateAccountInfo':
                $obj = new mallUpdateAccountInfo();
                break;
            case 'mallUpdateAccountLogo':
                $obj = new mallUpdateAccountLogo();
                break;
            case 'mallUpdateAccountPassword':
                $obj = new mallUpdateAccountPassword();
                break;
            case 'mallUpdateMobileNo':
                $obj = new mallUpdateMobileNo();
                break;
            case 'mallRegisterAccount':
                $obj = new mallRegisterAccount();
                break;
            case 'mallSendMsg':
                $obj = new mallSendMsg();
                break;
            case 'mallUpdatePayPassword':
                $obj = new mallUpdatePayPassword();
                break;
            case 'mallUpdateBalanceScore':
                $obj = new mallUpdateBalanceScore();
                break;
            case 'mallDistributionRebate':
                $obj = new mallDistributionRebate();
                break;
            case 'mallWithdraw':
                $obj = new mallWithdraw();
                break;
        }
        $check = $obj->checkSign($data);
        if(!$check){
            $this->respCode = 103;
            return $this->mallReturn();
        }

        if(!isset($data['method'])){
            $this->respCode = 100;
            return $this->mallReturn();
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