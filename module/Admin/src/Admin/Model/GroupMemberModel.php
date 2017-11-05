<?php
namespace Admin\Model;

use Zend\Db\Sql\Where;
use Core\System\WxApi\WxApi;
use Api\Model\CommonModel;

class GroupMemberModel extends CommonModel
{

    protected $table = 'user';

    /**
     * 异步post上传文件
     * @version YSQ
     */
    function ajaxUpdateMeme($path,$column,$table){
        $filename = $_FILES["Filedata"]["tmp_name"];//规定要移动的文件
        $file_size = $_FILES["Filedata"]["size"];
        if ($_FILES["Filedata"]["error"] == 0) {
            if (! file_exists($filename)) {
                die("文件 $filename 不存在！");
            }
    
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
//             move_uploaded_file($path, $filename);
//             file_put_contents($path, $filename);
            $savefilename = iconv("utf-8", "gb2312", date('Y_m_d').'.xls');//$_FILES['Filedata']['name']);//文件名
//             var_dump($filename, $path, $savefilename,$_FILES['Filedata']['name']);exit;
           $boolean = move_uploaded_file($filename , rtrim($path,'/') . '/' .$savefilename );//函数将上传的文件移动到新位置，参数1：规定要移动的文件；2：规定文件的新位置
           if($boolean){
               return $return = $this->setExcel($savefilename,$column,$table);
           }else{
               return array(
                   'code' => 400,
                   'message' => '失败',
               );
           }
        }else {
            return array(
                'code' => 400,
                'message' => iconv("gb2312", "utf-8", $_FILES['Filedata']['name']).'文件已被跳过，原因：文件过大'
            );
        }
    }
    
    /**
     * Excel导入
     * @param unknown $excelpath
     * @param string $column
     * @param string $table
     * @return multitype:multitype:
     * @version YSQ
     */
    function setExcel($excelpath,$column='J',$table="user"){
        require_once  APP_PATH .'/vendor/Core/System/phpExcel/PHPExcel/IOFactory.php';
        $filename= APP_PATH .'/vendor/Core/System/phpExcel/'.$excelpath;
        $fileType=\PHPExcel_IOFactory::identify($filename);//自动获取文件的类型提供给phpexcel用
        $objReader=\PHPExcel_IOFactory::createReader($fileType);//获取文件读取操作对象
        $sheetName=array("Simple");
        $objReader->setLoadSheetsOnly($sheetName);//只加载指定的sheet
        $objPHPExcel=$objReader->load($filename);//加载文件
        $sheetCount=$objPHPExcel->getSheetCount();//获取excel文件里有多少个sheet
//         $sheet = $objPHPExcel->getSheet(0);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();           //取得总行数
        //         $highestColumn = $sheet->getHighestColumn(); //取得总列数
        $arr = array();
        for($j=2;$j<=$highestRow;$j++)                        //从第二行开始读取数据
        {
            $str="";

            for($k='A';$k<=$column;$k++)            //从A列读取数据
            {
                if(!$str){
                    $str .=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                }else{
                    $str .='|*|'.$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();//读取单元格
                }
            }
            $str=mb_convert_encoding($str,'utf8','auto');//根据自己编码修改GBK;gb2312
            $strs = explode("|*|",$str);
            $arr[] = $strs;
        }
        $time = $this->getTime();
        $member = $this->getOne(array('type' => 1),array('number','price'),'member_set');
//         $member_time =  date('Y-m-d H:i:s', strtotime('+'.$member['number'].' day'));
        if(!$member['number']){
            return array(
                'code' => 400,
                'message' => '请先设置会员期限！',
            );
        }
        $member_time = date('Y-m-d 00:00:00',time() + 24 * 3600 * $member['number']);
        foreach ($arr as $v){
            if($v[0]){
                $info = $this->getOne(array('mobile' => $v[0]),array('id','member_time','amount'),'user');
                if($info){
                    if($info['member_time'] < date('Y-m-d H:i:s',time())){
                        //非会员是冻结 充值
                        $this->updateData(array('freeze_amount' => 0),array('id' => $info['id']),'user');
                        $data = array(
                            'open_member_time' => $this->getTime(),
                            'member_time' => $member_time,
                            'freeze_amount' => $member['price'],
                            'amount' => isset($v[1]) && $v[1]  ? $info['amount']+$v[1] : $info['amount']+0,
                        );
                        $vip = 2;
                        $id = $this->updateData($data, array('id'=>$info['id']),'user');
                    }else{
                        $data = array(
                            'amount' => isset($v[1]) && $v[1]  ? $info['amount']+$v[1] : $info['amount']+0,
                        );
                        $vip = 1;
                        $id = $this->updateData($data, array('id'=>$info['id']),'user');
                    }
                    $info = $this->getOne(array('mobile' => $v[0]),array('id','member_time'),'user');
                    if($v[1]){
                        $pay_log = array(
                            'type' => 4,
                            'pay_type' => 1,
                            'number' => 1,
                            'genre' => 0,
                            'audio_type' => 0,
                            'audio_id' => 0,
                            'amount' => $v[1],
                            'status' => 1,
                            'transfer_no' => date('YmdHis') . mt_rand(10, 99) . '-4',
                            'transfer_way' => 3,
                            'user_id' => $info['id'],
                            'vip_pay' => $vip,
                            'delete' => 0,
                            'vip_price' => '',
                            'pay_video' => '',
                            'timestamp_update' => $this->getTime(),
                            'timestamp' => $this->getTime(),
                        );
                        $pay_log_id = $this->insertData($pay_log,'pay_log');
                        $financial_data = array(
                            'type' => 6,
                            'amount' => $v[1],
                            'income' => 1,
                            'transfer_no' => date('YmdHis') . mt_rand(10, 99),
                            'transfer_way' => 3,
                            'remark' => '集团会员充值',
                            'user_id' => $info['id'],
                            'pay_log_id' => $pay_log_id,
                            'vip_pay' => $vip,
                            'delete' => 0,
                            'timestamp_update' => $this->getTime(),
                            'timestamp' => $this->getTime(),
                        );
                        $row = $this->insertData($financial_data,'financial');
                    }
                    $group_status = 1;
                }else{
                    $group_status = 2;
                }
                $data = array(
                    'mobile' => $v[0],
                    'excel_time' => $this->getTime(),
                    'group_status' => $group_status,
                    'user_id' => $info ? $info['id'] : 0,
                    'price' => $v[1],
                    'timestamp_update' => $this->getTime(),
                    'timestamp' => $this->getTime(),
                    'member_time' => $info ? $info['member_time'] : ""
                );
                $member = $this->insertData($data,'member_import');
            }
        }
        return array(
            'code' => 200,
            'message' => '导入成功',
        );
//         return $arr;exit;
    }
    /**
     *  管理后台用户列表
     * @param unknown $condition
     * @return Ambigous <multitype:, multitype:unknown , multitype:multitype:mixed  number >
     * @version YSQ
     */
 public function index($condition,$boolean=true)
    {
        $where = new Where();
        $where->equalTo(DB_PREFIX.'member_import.delete', 0);
        if(isset($condition['where']['start']) && $condition['where']['start']) {
            $where->greaterThanOrEqualTo(DB_PREFIX.'member_import.excel_time',  $condition['where']['start']);
        }

        if (isset($condition['where']['end']) && $condition['where']['end']) {
            $where->lessThanOrEqualTo(DB_PREFIX.'member_import.excel_time', $condition['where']['end']);
        }

        if (isset($condition['where']['status']) && $condition['where']['status']) {//1 生效 2 没生效
            $where->equalTo(DB_PREFIX.'member_import.group_status', $condition['where']['status']);
        }
        
        $data = array(
            'join' => array(
                array(
                    'name' => DB_PREFIX.'user',
                    'on' => DB_PREFIX.'user.id = '.DB_PREFIX.'member_import.user_id',
                    'columns' => array(
                        'user_name' => 'name',//用户职务
                        'user_head_icon' => 'head_icon',
                        'user_img_id' => 'img_id',
                        'user_img_path' => 'img_path',
                    ),
                    'type' => 'left'
                ),
            ),
            'order' => array(
              DB_PREFIX.'member_import.id' => 'DESC'
            ),
            'columns' => array(
                '*'
            ),
            'need_page' => true
        );
        
        if (isset($condition['where']['keyword']) &&$condition['where']['keyword']) {
            $data['search_key'] = array(
                DB_PREFIX . 'user.name' => $condition['where']['keyword'],
                DB_PREFIX . 'member_import.mobile' => $condition['where']['keyword'],
            );
        }
        $list = $this->getAll($where, $data, $condition['page'], null, "member_import");
        return $list;
    }
    
    /**
     * 导出excel
     * @version YSQ
     */
    public function getExcel2(){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能导入
//         $list = $this->index($condition,false);
    
        $allRoomList = array();
    
        $filename='会员列表';
        $headArr=array("手机号码","导入金额");
        $return = $this->getExcel($filename,$headArr,$allRoomList);
        if($return){
            return array(
                'code' =>200,
                'message' =>'成功',
            );
        }
    }
    
    /**
     * 导出用户excel
     * @version YSQ
     */
    public function getExcel3($condition){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能导入
        $list = $this->index($condition,false);
        foreach ($list['list'] as $k => $v){
            $arr[$k]['name'] = $v['user_name'] ? $v['user_name'] : '--';
            $arr[$k]['mobile'] = $v['mobile'];
            $arr[$k]['excel_time'] = $v['excel_time'];
            $arr[$k]['member_time'] = $v['member_time'];
            $arr[$k]['price'] = $v['price'];
            $arr[$k]['group_status'] = $v['group_status'] == 1?'生效':'未生效';
        }
        $allRoomList = $arr;
    
        $filename='集团会员列表';
        $headArr=array('用户名','手机号码','导入时间','会员到期时间','充值金额','生效状态');
//         $headArr=array("手机号码");
        $return = $this->getExcel($filename,$headArr,$allRoomList);
        if($return){
            return array(
                'code' =>200,
                'message' =>'成功',
            );
        }
    }
    
    /**
     * 设置冻结/启用(ajax)
     * @param array $params
     * @return array
     * @version YSQ
     */
    public  function setUserDelete($params){
        $id = isset($params['id']) ? (int) $params['id'] : 0; // 用户ID
        $status = isset($params['status']) ? (int) $params['status'] : 0; // 1启用；2禁用；
        if (!$id || !in_array($status,array(1,2))) {
            return array(
                'code' => '300',
                'message' => '请求参数不正确!'
            );
        }
        $user_info = $this->getOne(array(
            'id' => $id
        ), array(
            'status'
        ), 'user');
        if (in_array($user_info['status'],array(1,2))) {
            if ($user_info['status'] == $status) {
                return array(
                    'code' => '400',
                    'message' => '错误操作!'
                );
            }
            $row = $this->updateData(array(
                'status' => $status
            ), array(
                'id' => $id
            ), 'user');
            if ($row) {
                return array(
                    'code' => '200',
                    'message' => '操作成功!'
                );
            }
        }
        return array(
            'code' => '400',
            'message' => '未知错误!'
        );
    }
    
    /**
     * 得到用户详情
     * @param unknown $id
     * @return multitype:number string Ambigous <boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown> |multitype:number string
     * @version YSQ
     */
    function getUserDetails($id){
        if (!$id) {
            return array(
                'code' => '300',
                'message' => '请求参数不完整!'
            );
        }
        $data = array(
            'join' => array(
                array(
                    'name' => DB_PREFIX.'user',
                    'on' => DB_PREFIX.'user.id = '.DB_PREFIX.'member_import.user_id',
                    'columns' => array(
                        '*',
                    ),
                    'type' => 'left'
                ),
            ),
            'order' => array(
                DB_PREFIX.'member_import.id' => 'DESC'
            ),
            'columns' => array(
                '*'
            ),
        );
        $list = $this->getAll(array(DB_PREFIX.'member_import.id'=>$id, DB_PREFIX.'user.delete'=>0), $data, null, null, "member_import");
        $user_info = $list['list']['0'];
        if($user_info)
        {
            $user_position_info = $this->getOne( array('id'=>$user_info['position']),array('*'),'position');
            if(!$user_position_info){
                return array(
                    'code' => 400,
                    'message' => '用户职务信息获取失败',
                );
            }
            $user_info['p_name'] =  $user_position_info['name'];
            if($user_info['region_info']){
                $user_info['region'] = $this->regionInfoToString($user_info['region_info'],'-');
            }else{
                $user_info['region'] = '';
            }
        }else{
            return array(
                'code' => 400,
                'message' => '用户信息获取失败',
            );
        }
            return array(
                'code' => 200,
                'info' => $user_info,
            );
    }
    
    
//     /**
//      * 得到页码数
//      * @param int $page
//      * @param int $total
//      * @return array
//      * @version YSQ
//      */
//     public function getPageSum($page, $total2, $limit=0){
//         $limit = $limit == 0 ? PAGE_NUMBER : $limit;
//         $total = ceil($total2/$limit);
//         $page_info= array(
//             'total' => $total,
//             'page' => $page,
//             'page_1' => $page-1,
//             'page_2' => $page+1,
//             'previous' => $page<=1?true:false,
//             'net' => $page>=$total?true:false,
//         );
        
// //         $page_info['pagesInRange'] = $this->getPageSum($page, $total);
//         if($page <= 4){
//             for($i=1;$i<=8;$i++){
//                 $pagesInRange[] = $i;
//                 if($i>=$total){
//                     break;
//                 }
//             }
//         }elseif($page>($total-4)){
//             if(($total-8)<=0){
//                 for($i=1;$i<=$total;$i++){
//                     $pagesInRange[] = $i;
//                 }
//             }else{
//                 for($i=$total-8;$i<=$total;$i++){
//                     $pagesInRange[] = $i;
//                     if($i>=$total){
//                         break;
//                     }
//                 }
//             }
//         }else{
//             for($i=$page-3;$i<=$page+4;$i++){
//                 $pagesInRange[] = $i;
//                 if($i>=$total){
//                     break;
//                 }
//             }
//         }
//         $page_info['pagesInRange'] = $pagesInRange;
//         return $page_info;
//     }
    
//     /**
//      * 用户课程列表
//      * @param unknown $condition
//      * @version YSQ
//      */
//     function getUserCourseList($condition){
//         $s_where = $condition['where'];
//         if(!$s_where['id']){
//             return array(
//                 'code' => '300',
//                 'message' => '请求参数不完整!'
//             );
//         }
//         $where = new Where();
//         $where->equalTo(DB_PREFIX.'buy_log.delete', 0);
//         $where->equalTo(DB_PREFIX.'buy_log.user_id', $s_where['id']);
//         if($s_where['type']){
//             $where->equalTo(DB_PREFIX.'audio.type', $s_where['type']);
//         }
//         $data = array(
//             'columns'=>array('audio_id'),
//             'join'=>array(
//                 array(
//                     'name' => DB_PREFIX.'audio',
//                     'on' => DB_PREFIX.'audio.id = '.DB_PREFIX.'buy_log.audio_id',
//                     'columns' => array(
//                         '*'
//                     ),
//                     'type' => 'left'
//                 ),
//                 array(
//                     'name' => DB_PREFIX.'teacher',
//                     'on' => DB_PREFIX.'teacher.id = '.DB_PREFIX.'audio.teacher_id',
//                     'columns' => array(
//                         't_name' => 'name',
//                     ),
//                     'type' => 'left'
//                 )
//             ),
//             'need_page' => true,
//         );
//         $list = $this->getAll($where,$data,$condition['page'],$condition['limit'],'buy_log');
//         return $list;
//     }
    
//     /**
//      * 修改用户专家头衔
//      * @param unknown $id
//      * @version YSQ
//      */
//     function amendUserInfo($id,$type,$keyword){
//         if(!$id){
//             return array(
//                 'code' =>'300',
//                 'message' => '请求参数不完整!'
//             );
//         }
//         if($type==0){
//             if(!$keyword){
//                 return array(
//                     'code' =>'300',
//                     'message' => '请求参数不完整!'
//                 );
//             }
//         }
//        $user_info = $this->getOne(array('id'=>$id),array('title'),'user');
// //        var_dump($keyword);exit;
//        if($user_info && ($user_info['title']==$keyword)){
//            return array(
//                'code' =>'400',
//                'message' => '参数没有改变!'
//            );
//        }
//         $user_row = $this->updateData(array('title'=>$keyword), array('id'=>$id),'user');
//         if(!$user_row){
//             return array(
//                 'code' =>'400',
//                 'message' => '修改用户专家头衔失败!'
//             );
//         }
//         return array(
//             'code' =>'200',
//             'message' => '操作成功!'
//         );
//     }
    
//     /**
//      * 新增用户页面(post)
//      * @version YSQ
//      */
//     function addUserInfo(){
//      if( $_POST['jjb'] == 2321){
//             $name = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';//用户名
//             $mobile = isset($_POST['mobile']) ? (string)$_POST['mobile'] : '';//移动电话（登录账号）
//             $password = isset($_POST['password']) ? trim($_POST['password']) : '';//	密码，md5加密\
//             $title = isset($_POST['title']) ? (string)$_POST['title'] : '';//专家头衔
//             $image = isset($_POST['img_path']) ? (string)$_POST['img_path'] : '';//	管理员头像
            
//             if(!($mobile && $name && $password && $title && $image)){
//                 return array(
//                     'code' =>'300',
//                     'message' => '请求参数不完整!'
//                 );
//             }
//             $info = $this->getOne(array('mobile'=>$mobile,'delete'=>0),array('id'),'user');
//             if(!$info){
//                 $data =array();
//                 $password_s = md5($password);
//                 $data =array(
//                     'nickname' => $name,
//                     'mobile' => $mobile,
//                     'password' => $password_s,
//                     'image' => $image,
//                     'title' => $title,
// //                     'type' => 11,
//                 );
//                 $id = $this->insertData($data,'user');
//                 if($id){
//                     $stat_id = $this->insertData(array('user_id'=>$id),'user_stat');
//                     return array(
//                         'code' =>'200',
//                         'message' => '成功!'
//                     );
//                 }
//                 return array(
//                     'code' =>'400',
//                     'message' => '新增用户失败!',
//                 );
//             }
//             return array(
//                 'code' =>'400',
//                 'message' => '该登录账号已注册,请注册其他号码!',
//             );
//         }
//     }

//     /**
//      * 查询用户详情
//      *
//      * @param number $id            
//      * @return boolean|Ambigous mixed>
//      * @version 2016-8-29 WZ
//      */
//     public function getUserInfo($id = 0)
//     {
//         if (! $id) {
//             $id = empty($_SESSION['user_id']) ? 0 : $_SESSION['user_id'];
//         }
//         if (! $id) {
//             return false;
//         }
        
//         $where = array(
//             'id' => $id
//         );
//         $info = $this->getOne($where, array(
//             '*'
//         ), 'q_user');
//         return $info;
//     }

//     /**
//      * 检查登录、自动登录
//      *
//      * @return multitype:number string |boolean|multitype:number string unknown Ambigous <>
//      * @version 2016-8-29 WZ
//      */
//     public function checkLogin()
//     {
//         // $_SESSION['user_id'] = 1; // @todo 上线的时候要删除，先用用户1
//         // $_SESSION['user_id'] = 0;
//         $result = array(
//             'code' => 200,
//             'message' => '登录成功',
//             'data' => ''
//         );
//         if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
//             $result['user_id'] = $_SESSION['user_id'];
//             return $result;
//         }
//         else {
//             return array('code' => '404', 'message' => '未登录');
//         }
//         // 未开启自动登录，若要开启，注释上方三行代码
//         $_SESSION['openid'] = 'test';
//         // $_SESSION['openid'] = $this->getOpenid();
//         if ($_SESSION['openid']) {
//             $user_partner = $this->getOne(array(
//                 'open_id' => $_SESSION['openid']
//             ), array(
//                 '*'
//             ), 'q_user_partner');
            
//             if ($user_partner) {
//                 if ($user_partner['user_id']) {
//                     $user = $this->getOne(array(
//                         'id' => $user_partner['user_id'],
//                         'delete' => DELETE_FALSE
//                     ), array(
//                         '*'
//                     ), 'q_user');
                    
//                     if ($user) {
//                         if ($user['status'] == 1) {
//                             return array(
//                                 'code' => '200',
//                                 'user_id' => $user_partner['user_id']
//                             );
//                         } elseif ($user['status'] == 2) {
//                             return array(
//                                 'code' => '401',
//                                 'message' => '待审核',
//                                 'user_id' => $user_partner['user_id']
//                             );
//                         } elseif ($user['status'] == 3) {
//                             return array(
//                                 'code' => '402',
//                                 'message' => '已拒绝',
//                                 'user_id' => $user_partner['user_id']
//                             );
//                         }
//                     } else {
//                         // 用户已删除
//                         $this->updateData(array(
//                             'user_id' => 0
//                         ), array(
//                             '*'
//                         ), 'q_user_partner'); // 清除微信绑定
//                         return array(
//                             'code' => '404',
//                             'message' => '用户不存在/用户未绑定',
//                             'user_id' => 0
//                         );
//                     }
//                 } else {
//                     return array(
//                         'code' => '404',
//                         'message' => '用户不存在/用户未绑定',
//                         'user_id' => 0
//                     );
//                 }
//             } else {
//                 // 没有第三方记录
//                 if ($_SESSION['openid'] == 'test') {
//                     $user['nickname'] = '测试';
//                     $user['headimgurl'] = 'https://ss1.baidu.com/6ONXsjip0QIZ8tyhnq/it/u=2028306100,2201833418&fm=80';
//                 } else {
//                     $user = $this->getWeixinUserInfo($_SESSION['openid']);
//                 }
//                 $data = array(
//                     'open_id' => $_SESSION['openid'],
//                     'nickname' => empty($user['nickname']) ? $user['nickname'] : '',
//                     'image_url' => empty($user['headimgurl']) ? $user['headimgurl'] : '',
//                     'partner' => 3,
//                     'user_id' => 0,
//                     'timestamp' => $this->getTime()
//                 );
//                 $this->insertData($data, 'q_user_partner');
//                 return array(
//                     'code' => '404',
//                     'message' => '用户不存在/用户未绑定',
//                     'user_id' => 0
//                 );
//             }
//         }
        
//         return array(
//             'code' => '500',
//             'message' => '异常，不能获取openid'
//         );
//     }
    
//     /**
//      * 登录
//      * 
//      * @param unknown $param  mobile, password密码不要加密
//      * @version 2016-9-5 WZ
//      */
//     function login($param) {
//         $where = array(
//             'mobile' => empty($param['mobile']) ? '' : $param['mobile'],
//             'password' => empty($param['password']) ? '' : md5($param['password']),
//             'delete' => 0
//         );
//         $info = $this->getOne($where, array('*'), 'q_user');
//         return $info;
//     }

//     /**
//      * 获取用户第三方信息
//      *
//      * @param string $openid            
//      * @return Ambigous <boolean, mixed>
//      * @version 2016-8-30 WZ
//      */
//     public function getUserPartner($openid)
//     {
//         return $this->getOne(array(
//             'open_id' => $openid
//         ), array(
//             '*'
//         ), 'q_user_partner');
//     }

//     /**
//      * 微信绑定用户
//      *
//      * @param string $openid            
//      * @param string $mobile            
//      * @version 2016-8-30 WZ
//      */
//     public function partnerBindUser($openid, $mobile)
//     {
//         $user = $this->getOne(array(
//             'mobile' => $mobile
//         ), array(
//             '*'
//         ), 'q_user');
//         if (! $user) {
//             $user_partner = $this->getOne(array(
//                 'open_id' => $openid
//             ), array(
//                 '*'
//             ), 'q_user_partner');
//             if (! $user_partner) {
//                 return false;
//             }
//             $image = $this->getImageForController($user_partner['image_url']);
            
//             $user = array(
//                 'department_id' => 0,
//                 'username' => '',
//                 'mobile' => $mobile,
//                 'password' => md5(mt_rand(10000000, 99999999)),
//                 'nickname' => $user_partner['nickname'],
//                 'img' => empty($image['files'][0]) ? '' : $image['files'][0]['file']['path'],
//                 'status' => 2,
//                 'delete' => DELETE_FALSE,
//                 'timestamp' => $this->getTime()
//             );
//             $user['id'] = $this->insertData($user, 'q_user');
//         }
//         $this->updateData(array(
//             'user_id' => $user['id']
//         ), array(
//             'open_id' => $openid
//         ), 'q_user_partner');
//     }

//     /**
//      * 根据企业微信号查找用户信息
//      *
//      * @return multitype:number string multitype: Ambigous <\Core\System\WxApi\mixed, mixed>
//      * @version 2016-8-23 WZ
//      */
//     public function getWeixinUserInfo($openid)
//     {
//         $wxapi = new WxApi();
//         return $user = $wxapi->GetUser($openid);
//     }

//     public function AddUser($params)
//     {
//         $department_id = isset($params['department_id']) ? $params['department_id'] : 0;
//         $password = $params['password']; // 密码，传过来之前不要加密
//         $mobile = $params['mobile']; // 手机号码
//         $nickname = $params['nickname']; // 昵称，姓名
//         $img = empty($params['path']) ? '' : $params['path']; // 头像

//         $province=$params['province'];
//         $city=$params['city'];
//         if (! empty($params['image'])) {
//             // 2016.09.07 前端改为插件上传
//             $upload = $this->uploadImageForBase64($params['image']);
//             $img = $upload['path'];
//         }
//         $status = isset($params['status']) ? $params['status'] : 1; // 1可用，2待审核，3已拒绝
        
//         $info = $this->getOne(array('mobile' => $mobile,'delete'=>0), array('*'), 'q_user');
//         if ($info) {
//             return array(
//                 'code' => 400,
//                 'message' => '用户已存在'
//             );
//         }
        
//         $set = array(
//             'username' => '',
//             'department_id' => $department_id,
//             'password' => $password,
//             'mobile' => $mobile,
//             'nickname' => $nickname,
//             'img' => $img,
//             'status' => $status,
//             'timestamp' => $this->getTime(),
//             'p_id'=>$province,
//             'c_id'=>$city,
//         );
//         if (! $set['password']) {
//             unset($set['password']);
//         } else {
//             $set['password'] = md5($set['password']);
//         }
//         $back = $this->insertData($set, "q_user");
        
//         if ($back) {
//             return array(
//                 'code' => 200,
//                 'message' => '用户增加成功',
//                 'user_id' => $back
//             );
//         } else {
//             return array(
//                 'code' => 400,
//                 'message' => '用户增加失败'
//             );
//         }
//     }

//     /**
//      * 更新用户个人信息
//      */
//     function updateUser($params)
//     {
//         $key = array(
//             'department_id',
//             'username',
//             'mobile',
//             'password',
//             'nickname',
//             'img',
//             'status',
//             'delete'
//         );
//         $set = array();
//         $id = empty($params['id']) ? 0 : (int) $params['id'];
//         foreach ($key as $k) {
//             if (isset($params[$k])) {
//                 $set[$k] = $params[$k];
//             }
//         }
//         if (isset($params['province'])) {
//             $set['p_id'] = $params['province'];
//         }
//         if (isset($params['city'])) {
//             $set['c_id'] = $params['city'];
//         }
//         if ($set && $id) {
//             $where = array(
//                 'id' => $id
//             );
//             $this->updateData($set, $where, 'q_user');
//         }
//         return array(
//             'code' => '200',
//             'message' => ''
//         );
//     }
    
//     /*
//      * 用户详情
//      *
//      */
//     public function UserDetail($id)
//     {
//         $user = $this->getOne(array(
//             'id' => $id
//         ), array(
//             "*"
//         ), "q_user");

//         $where = new Where();
//         $where->equalTo('q_department.delete', 0);

//         $category = $this->getAll($where, null, null, null, 'q_department');
//         foreach ($category['list'] as $k => $v) {
//             $cate[$v['id']] = $v['name'];
//         }
        
//         $list = array(
//             'list' => $user,
//             'category' => $cate
//         );
//         return $list;
//     }

//     public function UserModify($params)
//     {
//         // $this->p($params);
//         $name = $params['name'];
//         $password = $params['password'];
//         $mobile = $params['mobile'];
        
//         $department = $params['department'];
//         $nickname = $params['nickname'];
        
//         $img = $params['path'];
        
//         $timestamp = $this->getTime();
        
//         $set = array(
//             'username' => $name,
//             'password' => $password,
//             'department_id' => $department,
//             'mobile' => $mobile,
//             'img' => $img,
//             'nickname' => $nickname,
//             'timestamp' => $timestamp
//         );
//         if (isset($params['province'])) {
//             $set['p_id'] = $params['province'];
//         }
//         if (isset($params['city'])) {
//             $set['c_id'] = $params['city'];
//         }
//         if (! $set['password']) {
//             unset($set['password']);
//         } else {
//             $set['password'] = md5($set['password']);
//         }
        
//         $where = array(
//             'id' => $params['id']
//         );
//         $back = $this->updateData($set, $where, "q_user");
        
//         if ($back) {
//             return array(
//                 'code' => 200,
//                 'message' => '用户编辑成功'
//             );
//         } else {
//             return array(
//                 'code' => 400,
//                 'message' => '用户编辑失败'
//             );
//         }
//     }
    
//     /**
//      * 重置用户密码
//      * 
//      * @param unknown $mobile
//      * @param unknown $password
//      * @version 2016-9-5 WZ
//      */
//     public function updateUserPassword($mobile, $password) {
//         $set = array('password' => md5($password));
//         $where = array('mobile' => $mobile);
//         $this->updateData($set, $where, 'q_user');
//     }

//     /**
//      * 删除用户
//      */
//     public function DeleteUserInfo($id)
//     {
//         $back = $this->deleteData($id, "q_user");
        
//         if ($back) {
//             return array(
//                 'code' => 200,
//                 'message' => '已成功删除资料'
//             );
//         } else {
//             return array(
//                 'code' => 400,
//                 'message' => '资料删除失败'
//             );
//         }
//     }

//     /**
//      * 意见反馈显示
//      */
//     public function ShowOpinionInfo($condition)
//     {
//         $where = new Where();
//         $where->equalTo('q_feedback.status', 2);
//         $where->equalTo('q_feedback.delete', 0);
        
//         $data = array(
//             'join' => array(
                
//                 array(
//                     'name' => 'q_user',
//                     'on' => 'q_user.id = q_feedback.user_id',
//                     'columns' => array(
//                         'u_nickname' => 'nickname'
//                     )
//                 )
//             ),
//             'order' => array(
//                 'q_feedback.id' => 'DESC'
//             ),
//             'columns' => array(
//                 'f_content' => 'content',
//                 'f_timestamp'=>'timestamp'
//             ),
//             'need_page' => true
//         );
        
//         $list = $this->getAll($where, $data, $condition['page'], null, "q_feedback");
//         $list['where'] = $where;
//         $list['page'] = $condition['page'];
        
//         return $list;
//     }
    
//     /**
//      *   显示未读意见反馈数
//      * 
//      */
//     public function countOpinionInfo()
//     {
//         $where = new Where();
//         $where->equalTo('q_feedback.status', 1);
//         $where->equalTo('q_feedback.delete', 0);
//         $number = $this-> countData($where, 'q_feedback');
//         return $number;
//     }
    
//     public function updateOpinion()
//     {
//         $where = new Where();
//         $where->equalTo('q_feedback.status', 1);
//         $set['status']=2;
//         $back = $this->updateData($set, $where, "q_feedback");
//         return  $back ;
//     }

}
