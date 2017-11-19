<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Platform\Controller;

use Zend\View\Model\ViewModel;

class IndexController extends CommonController
{
    public function indexAction()
    {
//        $this->checkLogin('platform_index_index');
        $mType = '';
        $merchantId = '';
        $communityProvinceId = '';
        $communityCityId = '';
        $communityRegionId = '';
        $from = '';
        if($_GET){
            $mType = $_GET['mType'];
            $merchantId = $_GET['merchantId'];
            $communityProvinceId = $_GET['province'];
            $communityCityId = $_GET['city'];
            $communityRegionId = $_GET['area'];
            $from = $_GET['from'];
        }
        //用户数
        $user = $this->getUserTable();
        $user->regionId = $communityRegionId;
        if($communityProvinceId){
            $user->regionInfo = $communityProvinceId;
        }
        if($communityCityId){
            $user->regionInfo = $communityCityId;
        }
        $userTodayCount = $user->getUserCount(date('Y-m-d',time()));//今日注册用户
        $userTotal = $user->getUserCount();//用户总数
        //业主数
        $merchant = $this->getMerchantTable();
        $merchant->type = $mType;
        $merchant->communityProvinceId = $communityProvinceId;
        $merchant->communityCityId = $communityCityId;
        $merchant->communityRegionId = $communityRegionId;
        $mTocayCount = $merchant->getMerchantCount(date('Y-m-d',time()));//今日认证业主
        $mTotal = $merchant->getMerchantCount();//认证业主总数
        $coummityArr = ['communityProvinceId'=>$communityProvinceId,'communityCityId'=>$communityCityId,'communityRegionId'=>$communityRegionId];
        //订单数
        $order = $this->getViewOrderTable();
        $order->merchantId = $merchantId;
        $oTodayCount = $order->getOrderSum(1,date('Y-m-d',time()),'',$mType,$coummityArr);
        $oTotal = $order->getOrderSum(1,date('Y-m-01',time()),'',$mType,$coummityArr);
        //营业额
        $finance = $this->getViewFinancialTable();
        $finance->merchantId = $merchantId;
        $orderTodaySum = $finance->getCash(date('Y-m-d',time()),'',$mType,$coummityArr); //今日营业额
        $orderMonthSum = $finance->getCash(date('Y-m-01',time()),'',$mType,$coummityArr); //本月营业额
        $list = $finance->getCashList(30,$mType,$coummityArr);
        $data['category'] = array();
        $data['data'] = array();
        if($list){
            foreach ($list as $k=>$v){
                $data['category'][] = $k;
                $data['data'][] = $v;
            }
            sort($data['category']);
            krsort($data['data']);
        }
        //折线图数据
        $data['category'] = json_encode($data['category']);
        $data['data'] = implode(',',$data['data']);
        //商家列表
        $merchant->setTableColumns(array('id','name'));
        $merchantList = $merchant->getAllList();
        $view = new ViewModel(['data'=>$data,'userTodayCount'=>$userTodayCount,'userTotal'=>$userTotal,'mTodayCount'=>$mTocayCount,'mTotal'=>$mTotal,'oTodayCount'=>$oTodayCount,'oTotal'=>$oTotal,'orderTodaySum'=>$orderTodaySum,'orderMonthSum'=>$orderMonthSum,'mType'=>$mType,'province'=>$communityProvinceId,'city'=>$communityCityId,'area'=>$communityRegionId,'from'=>$from,'merchantId'=>$merchantId,'merchantList'=>$merchantList['list']]);
        $view->setTemplate("platform/index/index");
        return $this->setMenu($view);
    }

}
