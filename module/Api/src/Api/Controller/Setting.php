<?php
namespace Api\Controller;

use Api\Controller\Item\PushArgsItem;
use Zend\Db\Sql\Where;
/**
 * 系统设置协议
 */
class Setting extends CommonController
{
    public function index()
    {
        $response = $this->getAiiResponse();
        
        //$filename = $this->makeCacheFilename(array(),1);
      
        //$data = $this->getCache($filename, 1, $this->timestampLeast);
        //if (STATUS_CACHE_AVAILABLE == $data) {
           // return $data;
        //}
           
        $integralScale= array();
       // if (!$data) {
            $where=new Where();
            $where->equalTo('key', FCODE_KEY);
          
            
            $setting_list = $this->getModel('Information')->fetchAll($where,null,'setting');
            //var_dump($setting_list);exit;
            $setting=array();
            foreach($setting_list as $value){
               
                if($value['name']=='F码优惠设置'){
                    $setting['discount']=$value['value'];
                }

            }
             //var_dump($setting);exit;
           
            
           // $integralScale['integralScale'] = $setting_list['value'];
           // $this->setCache($filename, array('list'=>$integralScale), 1);
       // }
       // else {
            //$integralScale = $data['list'];
       // }
        
        $response->setting = $setting;
        return $response;
    }
}
