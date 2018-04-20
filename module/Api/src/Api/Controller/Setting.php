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
        $response->poster = isset($data[3])?$data[3]['value']:'';
        $response->status = STATUS_SUCCESS;
        return $response;
    }
}
