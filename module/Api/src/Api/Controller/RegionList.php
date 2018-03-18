<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;

/**
 * 省市区列表、region表
 *
 * @author WZ
 *
 */
class RegionList extends CommonController
{
    /**
     * @return Common\Response|string
     * @throws \Exception
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();

        $request->action = $request->action ? $request->action : 1;//1省市区
        if(!in_array($request->action,[1]))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $parent_id = (int)$request->id?(int)$request->id:1;
        $region_table = $this->getRegionTable();
        $region_table->parentId = $parent_id;
        $region_table->setTableColumns(array('id','parentId','name'));
        $data = $region_table->getApiList();
        $data = $region_table->dataConvert($data['list'],array('id','parentId','name'));
        $total = isset($data['total']) ? $data['total'] : 0;
        $response->status = STATUS_SUCCESS;
        $response->total = $total . '';
        $response->regions = $data;
        return $response;
    }


}
