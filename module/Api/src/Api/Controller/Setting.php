<?php
namespace Api\Controller;

/**
 * 系统设置协议
 *
 * @author WZ
 */
class Setting extends CommonController
{
    /**
     *
     * @return \Api\Controller\Common\Response
     */
    public function index()
    {
        $requert = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $setup_table  = $this->getSetupTable();
        $data = $setup_table->getDataByInId();
        $user_level_table = $this->getUserLevelTable();
        $user_level_table->orderBy = 'amount ASC';
        $levels_data = $user_level_table->getList();
        $levels = array();

        foreach($levels_data['list'] as $v)
        {
            $levels[] =  array('id'=>$v->id,'name' => $v->name,'upgradeCost' => $v->amount,'discount'=>$v->discount);
        }

        $response->status = STATUS_SUCCESS;
        $response->levels = $levels;

        $response->goodsShareTitle = $data[1]['value'];
        $response->goodsShareContent = $data[2]['value'];
        $response->serviceShareTitle = $data[3]['value'];
        $response->serviceShareContent = $data[4]['value'];
        $response->merchantShareTitle = $data[5]['value'];
        $response->merchantShareContent = $data[6]['value'];
        $response->orderShareTitle = $data[7]['value'];
        $response->orderShareContent = $data[8]['value'];
        $response->evaluateShareTitle = $data[9]['value'];
        $response->evaluateShareContent = $data[10]['value'];
        $response->defaultSearchKey = $data[12]['value'];
        $response->hotSearchKey = $data[11]['value'] ? explode("|",$data[11]['value']) : '';
        $response->selfDefaultSearchKey = $data[14]['value'];
        $response->selfHotSearchKey =$data[13]['value'] ? explode("|",$data[13]['value']) : '';
        $response->orderShareCoupon =$data[18]['value'] ? $data[18]['value'] : '';
        $response->evaluateShareCoupon =$data[19]['value'] ? $data[19]['value'] : '';
        return $response;
    }
}
