<?php
namespace Web\Controller;


use Zend\View\Model\ViewModel;
use Core\System\AiiUtility\AiiPush\AiiMyFile;
use Core\System\AiiUtility\AiiWxPayV3\AiiWxPayNotify;
use Core\System\AiiUtility\Log;

class IndexController extends CommonController
{

    function __construct()
    {
        parent::__construct();
        $this->controller = 'index';
        $this->module = 'web';
    }
    
    public function indexAction() {
        $page_type = array(
            'page_type'=> 1,
            'page_id' => 0,
            'detail_type' =>0
        );
        $this->checkWebLogin($page_type);
        $this->action = substr(__FUNCTION__, 0 ,-6);   
        $data = $this->getModel('Index')->getIndex();
        $ads = $data['ads'];
        $home_manage = $data['home_manage'];
        $four_free_audios = $data['four_free_audios'];
        $today_ads = $data['today_ads'];
        if($ads)
        {
            foreach ($ads as $v)
            {
                $url = '';
                switch ($v['type'])//1 图文消息 2 音频课程 3 视频课程 4 音频包课程 5 视频包课程 6 外部链接
                {
                    case 2:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'audio','action' => 'details'))."?id=".$v['audio_id'].',1&type=1';
                        break;
                    case 3:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'video','action' => 'details'));
                        $url = $url."?id=".$v['audio_id'].',1&type=1';
                        break;
                    case 4:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'audio','action' => 'details'))."?id=".$v['audio_id'].',2'.'&type=2';
                        break;
                    case 5:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'video','action' => 'details'));
                        $url = $url."?id=".$v['audio_id'].',1&type=2';
                        break;
                    case 6:
                        $url = $v['link'];
                        break;
                    default:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'index','action' => 'articleDetails','id'=>$v['id']));
                        break;
                }
                $v['link'] = $url;
            }
        }
        if($today_ads)
        {
            foreach ($today_ads as $v)
            {
                $url = '';
                switch ($v['type'])//1 图文消息 2 音频课程 3 视频课程 4 音频包课程 5 视频包课程 6 外部链接
                {
                    case 2:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'audio','action' => 'details'))."?id=".$v['audio_id'].',1&type=1';
                        break;
                    case 3:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'video','action' => 'details'));
                        $url = $url."?id=".$v['audio_id'].',1&type=1';
                        break;
                    case 4:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'audio','action' => 'details'))."?id=".$v['audio_id'].',2'.'&type=2';
                        break;
                    case 5:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'video','action' => 'details'));
                        $url = $url."?id=".$v['audio_id'].',1&type=2';
                        break;
                    case 6:
                        $url = $v['link'];
                        break;
                    default:
                        $url = $this->plugin("url")->fromRoute('web-common',array('controller' => 'index','action' => 'articleDetails','id'=>$v['id']));
                        break;
                }
                $v['link'] = $url;
            }
        }
        
        $name_array = $link_array = array();
        $first_name = $second_name = "";
        if($home_manage)
        {
            $first_url_link = json_decode($home_manage['first_url_link']);
            $name_array = isset($home_manage['name']) ? json_decode($home_manage['name'],true) : array();
            $link_array = isset($home_manage['link']) ? json_decode($home_manage['link'],true) : array();
            $image_array = isset($home_manage['image']) ? json_decode($home_manage['image'],true) : array();
            $first_name = isset($home_manage['first_name']) ? $home_manage['first_name'] : "";
            $second_name = isset($home_manage['second_name']) ? $home_manage['second_name'] : "";
            $first_url_link = isset($home_manage['first_url_link']) ? $first_url_link->first_url_link : "";
            $second_url_link = isset($home_manage['second_url_link']) ? $home_manage['second_url_link'] : "";
        }
        if($four_free_audios)
        {
            foreach ($four_free_audios as $m)
            {
                $timestamp = strtotime($m['timestamp']);
                $today_end = strtotime(date("Y-m-d 23:59:59"));
                $today_start = strtotime(date("Y-m-d 00:00:00"));
                $m['is_today'] = $timestamp < $today_end && $timestamp > $today_start ? 1 : 0;
            }
        }
        $is_perfect = isset($_SESSION['is_perfect']) ? $_SESSION['is_perfect'] : 0;
        $user = $this->getModel('User')->userDetails($_SESSION['user_id']);
        $adsTime = $this->getModel('AdminAds')->getOne(array('type'=>3,'delete'=>0),array('content'),'setup');
        $adsTime = $adsTime['content'] * 1000;
        return $this->setMenu(
            array(
                'ads'=>$ads,
                'today_ads' => $today_ads,
                'four_free_audios'=>$four_free_audios,
                'name_array'=>$name_array,
                'link_array'=> isset($link_array) && $link_array ? $link_array : array(),
                'image_array' => isset($image_array) && $image_array ? $image_array : array(),
                'first_name'=>$first_name,
                'second_name'=>$second_name,
                'first_url_link' => $first_url_link,
                'second_url_link' => $second_url_link,
                'is_perfect'=>$is_perfect,
                'user'=>$user,
                'adsTime'=>$adsTime
            ));
    }
    
    /**
     * 文档详情
     */
    public function articleDetailsAction()
    {
        $id = $this->params()->fromRoute('id');
        $type = $this->params()->fromRoute('type',2);
        if(!$id)
        {
            $this->showMessage("请求参数错误");
        }
        $data = $this->getModel("Index")->articleDetails($id,$type);
        if($data['code'] != 200)
        {
            $this->showMessage($data['message']);
        }
        return $this->setMenu(array('data'=>$data['data']),'web/index/articleDetails');
    }
    
    /**
     * 微信回调
     *
     * @version 2015-4-10 WZ
     */
    public function getWxPayNotifyAction()
    {
        $log = new log('payLog');
        $notify = new AiiWxPayNotify();
        $result = $notify->getResult();
        //查看验签
        $log->info('签名结果result：'.var_export($result,true));
        if (! $result['status'] && $result['out_trade_no']) {
            $out_trade_no = $result['out_trade_no'];
            $id = $out_trade_no;
            $data = $this->getModel('Index')->wxNotifyTransacting($id);//回调业务处理
            if($data){
                if($data['genre'] == 1){
                    $this->getModel('Video')->audioOrderSubmit($data);
                }else{
                    $this->getModel('Video')->coursesOrderSubmit($data);
                }
            }
        }
        exit();
    }

}
