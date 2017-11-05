<?php
namespace Api\Controller;

use Api\Controller\PublicFunctionController;
use Zend\Captcha\Image as imageCaptcha;

class PublicController extends PublicFunctionController
{
    /**
     * 用户等级信息
     * @var unknown
     */
    private $grade;
    
    
    
    protected $tableArray;
    
    /**
     * 模型定位器
     * @return 
     * @author arong
     */
    public function getModel($name)
    {
        if (empty($this->tableArray[$name])) {
            $sm = $this->getServiceLocator();
            $this->tableArray[$name] = $sm->get($name);
        }
        return $this->tableArray[$name];
    }
    
    /**
     * 获取缓存的城市信息
     * @param int 城市Id 空为返回所有城市
     * @return 返回一条或多条城市是信息
     * @author arong
     */
    public function getCityInfo($city_id=''){
        //缓存
        $cityData = $this->getCache('cityData.txt',1);
        if(!$cityData){
            $city = $this->getTable('region')->fetchAll(array('deep'=>2));
            $cityData = array();
            foreach ($city as $k=>$v){
                $cityData[$v['id']] = $v;
            }
            $this->setCache('cityData.txt', $cityData, 1);
        }
        if($city_id){
            return $cityData[$city_id];
        }else{
            return $cityData;
        }
        
    }
    
    
    
    /**
     * 获取用户等级属性
     * @param int  $activityNum 用户发布并完成的活动数  为空返回所有等级信息
     * return 用户等级信息(一维)  或 所有等级信息(二维)
     * @author arong
     */
    public function getGradeAttr($activityNum=null)
    {
       $grade_data = array();
       if(!$this->grade){
          $this->grade = $this->getTable('setting_item')->fetchAll(array('setting_group_id'=>1),array('value asce'));
       }
       foreach ($this->grade as $v) {
           $param = json_decode($v['param'],true);
           $activity =  $param['activityNum'];
           if($activityNum !== null){
               $grade_data = array(
                   'grade_name'    => $v['name'],
                   'grade_value'   => $v['value'],
                   'activity_num'  => $activity,
                   'advance'       => $param['advance']
               );
               if ($activityNum <= $activity) {
                   return $grade_data;
               }
           }
           elseif($activityNum === null){
               $grade_data[] = array(
                   'grade_name'    => $v['name'],
                   'grade_value'   => $v['value'],
                   'activity_num'  => $activity,
                   'advance'       => $param['advance']
               );
           }
       }
       return $grade_data;
    }
    
    /**
     * 返回分类名称数组
     * @param int 1 电子商城 2 积分商城 3 活动
     * @return array array('分类ID'=>分类名称)
     * @author arong
     */
    public  function getCategoryName($type = 1){
        
        $cat = $this->getTable('category')->fetchAll(array('type'=>$type));
        $goods_cat = array();
        foreach ($cat as $v) {
            $goods_cat[$v['id']] = $v['name'];
        }
        return $goods_cat;
    }
    
    /**
     * 
     */
    
    
    /**
     * 返回性别
     *
     * @return multitype:string
     * @author Administrator
     */
    public function getUserSex(){
        return array(
            0 => '未知',
            1 => '男',
            2 => '女'
        );
    }
    
    /**
     * 返回收支
     *
     * @return multitype:string
     * @author Administrator
     */
    public function getIncome(){
        return array(
            0 => '未知',
            1 => '收入',
            2 => '支出'
        );
    }
    
    /**
     * 返回交易类型
     * 交易类型：1发布活动；2参与活动；3商场消费；4商城兑换
     * @return multitype:string
     * @author Administrator
     */
    public function getTransactionType(){
        return array(
            1 => '发布活动',
            2 => '参与活动',
            3 => '商场消费',
            3 => '商城兑换'
        );
    }
    
    
    
    /**
     * 返回活动状态
     *
     * @return 状态：1审核中；2未出行；3进行中；4待总结；5已结束；6已取消
     * @author Administrator
     */
    public function getActivityStatus(){
        return array(
            1 => '审核中',
            2 => '未出行',
            3 => '进行中',
            4 => '待总结',
            5 => '已结束',
            91 => '已取消',
            7 => '审核不通过'
        );
    }
    
    /**
     * 返回商品状态
     *
     * @return 状态：1审核中；2未出行；3进行中；4待总结；5已结束；6已取消
     * @author Administrator
     */
    public function getGoodsStatus(){
        return array(
            1 => '上架',
            2 => '下架',
        );
    }
    
    /**
     * 生成交易流水号 ,如（140601） +（235001）+（10000）年月日+时分秒+五位随机数
     */
    public function getBatch () {
       return date('YmdHis',time()).$this->makeCode(5, 4);
    }
    
    /**
     * 根据图片id,转json数组
     * @param $id
     */
    public function imgToJson($id)
    {
        if (! $id) {
            return '[]';
        }
        $image = array();
        if (is_array($id)) {
            foreach ($id as $v) {
                $img_info = $this->getTable('image')->getOne(array('id' => $v));
                $path = $img_info['path'] . $img_info['filename'];
                $image[] = array('id' => $v, 'path' => $path);
            }
        }
        else {
            $img_info = $this->getTable('image')->getOne(array('id' => $id));
            $path = $img_info['path'] . $img_info['filename'];
            $image[] = array('id' => $img_info['id'], 'path' => $path);
        }
        $img_json = json_encode($image);
        return $img_json;
    }
    
    /**
     * 返回订单状态
     *
     * @return 状态：1待付款；2待发货；3待收货；4待评价；5已完成；31申请退货；32同意退货；33退货中；34已退货；35退货驳回；91已取消；
     * @author Administrator
     */
    public function getOrderStatus(){
        return array(
            1 => '待付款',
            2 => '待发货',
            3 => '待收货',
            4 => '待评价',
            5 => '已完成',
            31 => '申请退货',
            32 => '同意退货',
            33 => '退货中',
            34 => '已退货',
            35 => '退货驳回',
            91 => '已取消',
        );
    }
    
    //类型：1活动报名；2活动取消3；活动垫付（活动开始）；4活动垫付（活动结束）；5电子商城用户支付；6电子商城收入（商家/平台）；7电子商城退货退款（用户）；8电子商城退货退款（商家/平台）；9平台商家提现（商家）；10电子商城订单取消（用户）；11电子商城订单取消（商家/平台）；
    public function getFinancialType(){
        return array(
            1 => '活动报名',
            2 => '活动取消',
            3 => '活动垫付（活动开始）',
            4 => '活动垫付（活动结束）',
            5 => '电子商城用户支付',
            6 => '电子商城收入（商家/平台）',
            7 => '电子商城退货退款（用户）',
            8 => '电子商城退货退款（商家/平台）',
            9 => '平台商家提现（商家）',
            10 => '电子商城订单取消（用户）',
            11 => '电子商城订单取消（商家/平台）',
        );
    }
    
    /**
     * 商品审核状态
     * 
     * @version 2016-6-20 WZ
     */
    public function getGoodsAuditStatus($type = 1) {
        if ($type == 1){
            return array(
                '1' => '审核通过',
                '2' => '新增商品',
                '3' => '资料变更',
                '91' => '审核不通过'
            );
        }
        elseif ($type == 2) {
            return array(
                '1' => '审核通过',
                '2' => '待审核',
                '3' => '待审核',
                '91' => '审核不通过'
            );
        }
        return array();
    }
    
    /**
     * 使用高德地图获取地理编码
     * 
     * @param string $street 省市区街道
     * @return mixed|boolean
     * @version 2016-5-23 WZ
     */
    public function getLocation($street) {
        if ($street) {
            $url = 'http://restapi.amap.com/v3/geocode/geo?address=' . urlencode($street) . '&output=json&key=' . AMAP_KEY;
            $data = file_get_contents($url);
            return json_decode($data, true);
        }
        else {
            return false;
        }
    }
    
    /**
     * 验证码生成
     *
     * @author liujun
     */
    public function generateCaptchaAction()
    {
        $captcha = new imageCaptcha();
        $number = rand(1, 6);
        $language = __DIR__ . "/../../../language/$number.ttf";
        $captcha->setFont($language); // 字体路径
        $captcha->setImgDir('public/' . UPLOAD_PATH . 'captcha/'); // 验证码图片放置路径
        $captcha->setImgUrl(ROOT_PATH . 'uploadfiles/captcha/');
        $captcha->setWordlen(5);
        $captcha->setFontSize(30);
        $captcha->setLineNoiseLevel(4); // 随机线
        $captcha->setDotNoiseLevel(40); // 随机点
        $captcha->setExpiration(10); // 图片回收有效时间
        $captcha->generate(); // 生成验证码
        $_SESSION['captcha'] = $captcha->getWord();
        echo $captcha->getImgUrl() . $captcha->getId() . $captcha->getSuffix(); // 图片路径
        die();
    }
    
}











