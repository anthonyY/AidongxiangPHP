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
     * 返回一个数组或者Result类
     *
     * @return \Api\Controller\BaseResult
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();

        $request->action = $request->action ? $request->action : 1;//1省市区 2所有社区 3城市社区
        if(!in_array($request->action,[1,2,3]))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $city_id = (int)$request->id;
        if(1 == $request->action){
            $region_table = $this->getRegionTable();
            $region_table->setTableColumns(array('id','parentId','name'));
            $data = $region_table->getApiList();
            $data = $region_table->dataConvert($data['list'],array('id','parentId','name'));
            $total = isset($data['total']) ? $data['total'] : 0;
        }else{
            /*if(!$city_id)
            {
                return STATUS_PARAMETERS_CONDITIONAL_ERROR;
            }*/
            $region_table = $this->getViewRegionTable();
            $table_where = $this->getTableWhere();
            $region_table->searchKeyWord = $table_where->search_key;
            $region_table->parentId = $city_id;
            $region_table->status = 1;
            $region_table->type = 2;
            $region_table->orderBy = 'id ASC';

            $res =$region_table->getCommunity($request->action);
            $data = $res['list'];
            $total = $res['total'];
        }


        $response->status = STATUS_SUCCESS;
        $response->total = $total . '';
        $response->regions = $data;
        return $response;
    }


}
