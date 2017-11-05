<?php
namespace Web\Controller;

class ScanCodeController extends CommonController
{

    function __construct()
    {
        parent::__construct();
        $this->controller = 'scancode';
        $this->module = 'web';
    }

    /*
     * qrCode 二维码数据
     * type 1 正常  2 活动结束  3 礼包被拆过了 4 领取成功
     * */
    function getActvityAction(){
        $type = isset($_GET['type']) && $_GET['type'] ? explode(',',$_GET['type']) : 0;
        //如果type == 4 则领取成功流程
        if($type[0] == 4){
            return $this->goSuccessAction($type);
        }

        // 正常领取流程
        $page_type = array(
            'page_type'=> 8,
            'page_id' => 0,
            'detail_type' =>0
        );
		$_SESSION['user_id'] = 0;
        $_SESSION['wx_open_id'] = '';
        $this->checkWebLogin($page_type);
        $code = isset($_GET['qrCode']) && $_GET['qrCode'] ? explode(',',$_GET['qrCode']) : array();
        $type = 1;
        $act = $this->getModel('ScanCode')->getActvityDetails($code);
        if(!$act['status']){
            $ticket = $this->getModel('ScanCode')->getWeixinCode();
            $type = $act['type'];
        }
        if($type == 1){
            $this->action = substr(__FUNCTION__, 0 ,-6);
            return $this->setMenu(array('type' => $type,'id' => $act['id'],'data' => $act['data']));
        }else{
            return $this->setMenu(array('type' => $type,'ticket' => $ticket),'web/scancode/discernCode');
        }

    }

    /*
     * 成功修改二维码状态
     *
     * **/
    function updateCodeStatusAction(){
        $result = $this->getModel('ScanCode')->updateCodeStatus();
        echo json_encode($result);
        exit;
    }

    /**
     * @param $type
     * @return \Zend\View\Model\ViewModel
     */
    public function goSuccessAction($type)
    {
        $this->checkWebLogin();
        $data = $this->getModel('ScanCode')->getQrCodeData($type[1]);
        $ticket = $this->getModel('ScanCode')->getWeixinCode();
        return $this->setMenu(array('type' => $type[0], 'ticket' => $ticket,'data' => $data), 'web/scancode/discernCode');
    }

}
