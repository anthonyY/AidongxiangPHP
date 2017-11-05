<?php
namespace Admin\Controller;
use Zend\View\Model\ViewModel;
class DetailController extends CommonController{

    public function indexAction()
    {
        $this->checkLogin("");
        $page = $this->params()->fromRoute('page',1);//页数
        $type = isset($_GET['type'])?$_GET['type']:1;
        $start = isset($_GET['start'])?$_GET['start']:'';
        $end = isset($_GET['end'])?$_GET['end']:'';
        $where = array(
            'start' => $start,
            'end' => $end,
            'type' => $type
        );
        $condition = array(
            'controller' => 'Detail',
            'action' => 'index',
            'where' => $where,
            'page' => $page,
        );
        $list = $this->getModel("AdminOrder")->getDetailList($condition);
        $type_arr = array(
            '1' => array('1'=>'购买课程','2'=>'赠送课程'),
            '2' => array('1'=>'购买会员','2'=>'赠送会员'),
        );
        $page_arr = array(
            '1' => '首页',
            '2' => '音频列表',
            '3' => '视频列表',
            '4' => '导师列表',
            '5' => '用户中心',
            '6' => '订阅记录',
            '7' => '搜索页面',
            '8' => '扫码活动'
        );
        $user_arr = array(
            1 => '会员页',
            2 => '课程推荐',
            3 => '我爱分享',
            4 => '我的钱包',
            5 => '购买记录',
            6 => '赠送记录',
            7 => '我的收藏',
            8 => '观看记录',
            9 => '消息通知',
            10 => '设置',
            11 => '帐号与安全',
            12 => '手机号',
            13 => '关于一起聚餐',
            14 => '用户帮助',
            15 => '意见反馈',
            16 => '个人信息',
            17 => '修改昵称、个性签名',
            18 => '地区',
            19 => '账户明细',
            20 => '意见反馈通知',
            21 => '系统通知',
            22 => '帮助详情',
            23 => '我的佣金',
            24 => '充值到钱包',
            25 => '佣金页面',
            26 => '佣金明细',
            27 => '分享详情页',
            28 => '我的推荐'
        );
        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => $condition,
            'start' => $start,
            'end' => $end,
            'type_arr' => $type_arr,
            'page_arr' => $page_arr,
            'user_arr' => $user_arr
        ));

        if($type == 1){
            $view->setTemplate('admin/detail/index');
        }else if($type == 2){
            $view->setTemplate('admin/detail/browse');
        }else if($type == 3){
            $view->setTemplate('admin/detail/order');
        }else if($type == 4){
            $view->setTemplate('admin/detail/pageBrowse');
        }

        return $this->setMenu1($view);
    }

    /**
     * 下载excel
     * @version YSQ
     */
    public function getExcelAction(){
        $check = $this->checkLogin("User");
        if (! $check) {
            return false;
        }
        $key = array('keyword', 'status', 'cid', 'sex');
        $where = array();
        foreach ($key as $k) {
            $where[$k] = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
        }
        $condition = array(
            'where' => $where,
            'page' => 1,
        );
        $data = $this->getModel('AdminUser')->setExcel($condition);
        $this->ajax($data['message']);
    }


}
?>