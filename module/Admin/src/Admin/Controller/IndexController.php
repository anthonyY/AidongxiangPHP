<?php
namespace Admin\Controller;
use Zend\View\Model\ViewModel;
use Admin\Model\PublicTable;

class IndexController extends CommonController{

    function addListAction(){
        $this->checkLogin("Index-addList");
        $view = new ViewModel(array(
        ));
        $view->setTemplate('admin/index/index2');
        return $this->setMenu1($view);
    }

    public function indexAction()
    {
        $this->checkLogin("Index");
        //用户统计
        $cid = isset($_GET['cid'])?$_GET['cid'] : $this->params()->fromRoute('cid',1);//1,收入;2访客数;3下单用户;4,分享,
        //$types =  isset($_POST['types'])?$_POST['types'] : $this->params()->fromRoute('types',24);//24,今天;7,一周;30,一个月
        $start = isset($_GET['start'])?$_GET['start']:'';
        $end = isset($_GET['end'])?$_GET['end']:'';
        if($start && $end){
            $timediff = strtotime($end.' 23:59:59') - strtotime($start);
            $days = intval($timediff/86400); //计算天数
            if($days >= 1){
                $types = $days+1;
            }else{
                $types = 24;
            }
        }else{
            //$start = $end = date('Y-m-d',time());
            $types =  isset($_GET['types'])?$_GET['types'] : $this->params()->fromRoute('types',24);//24,今天;7,一周;30,一个月
        }
        $data = $this->getModel("IndexAdmin")->getKey($types,$end);
        list($list,$title) = $this ->getModel("IndexAdmin")->getTypeInfo($cid,$types,$start,$data['array']);
        $arr = array(
            '1' => '浏览用户数(未绑定手机)',
            '2' => '用户数(已绑定手机)',
            '3' => '消费用户',
            '4' => '会员数',
            '5' => '会员贡献的收入',
            '6' => '非会员贡献的收入',
            '7' => '会员消费课程的金额',
            '8' => '非会员消费课程的金额',
            '9' => '分销注册统计'
        );
        //今天收入,总收入,购买人数
        $total = $this->getModel('AdminOrder')->getTodayOrderCount();
        //下单用户;访客数;浏览量
        $count = $this->getModel('AdminOrder')->getTodayCount();
        $view = new ViewModel(array(
            'total' => $total,
            'cid' =>$cid,
            'types' =>$types,
            'arr' => $arr,
            'data'=>$data,
            'count' => $count,
            'list' => $list,
            'start'=>$start,
            'end'=>$end,
            'title' => $title
            ));
        $view->setTemplate('admin/index/index');
        return $this->setMenu1($view);
    }
}
?>