<?php
namespace Admin\Controller;

use Zend\Db\Sql\Where;
use Zend\View\Model\ViewModel;

class OrderController extends CommonController
{
    
//     function __construct(){
//          $this->controller = 'comment';
//     }
    /**
     * 财务列表
     * 
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $check = $this->checkLogin("Order");
//         if($check['code']!=200){
//             $this->showMessage($check['message']);
//         }
        $page = $this->params()->fromRoute('page', 1);
        $key = array('start','end','type','vip_pay','income');
        $where = array();
        foreach ($key as $k) {
            $where[$k] = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
        }
        $condition = array(
            'controller' => 'Order',
            'action' => 'index',
            'page' => $page,
            'where' => $where,
        );
        $list = $this->getModel('AdminOrder')->getOrderList($condition);
        //今天收入,总收入,购买人数
        $total = $this->getModel('AdminOrder')->getTodayOrderCount();

        $view = new ViewModel(array(
            'list'=> $list['list'],
            'paginator' => $list['paginator'],
            'condition' => $condition,
            'total' => $total,
        ));
        $view->setTemplate('admin/order/index');
        return $this->setMenu1($view);
    }
    
    /**
     * 下载excel
     * @version YSQ
     */
    public function getExcelAction(){
        $check = $this->checkLogin("Order");
        if (! $check) {
            return false;
        }
        $key = array('start','end','type','vip_pay','income');
        $where = array();
        foreach ($key as $k) {
            $where[$k] = isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : '';
        }
        $condition = array(
            'where' => $where,
            'page' => 1,
        );
        $data = $this->getModel('AdminOrder')->setExcel($condition);
        $this->ajax($data['message']);
    }
}
