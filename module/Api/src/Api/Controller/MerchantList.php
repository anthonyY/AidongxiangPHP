<?php
namespace Api\Controller;

use Api\Controller\Request\MerchantWhereRequest;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

/**
 * 业务，商家列表
 */
class MerchantList extends CommonController
{
    /**
     * 一行的门店个数（PC端）
     */
    private $lineNumber = 5;

    public function __construct()
    {
        $this->myWhereRequest = new MerchantWhereRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action ? $request->action : 1; //1所有商家 2收藏商家 3附近商家
        if (!in_array($action, array(1, 2,3))) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $list = array();
        $total = 0;
        $table_where = $this->getTableWhere();
        $search_key = $table_where->search_key;

        $table = $this->getTable();
        $table_where = $this->getTableWhere();
        $page = $table->page ? $table->page : 1;
        //推广商品列表
        $not_in_merchant_ids = [];
        if(in_array($action,array(1,3)))
        {
//            if($page == 1)
//            {
            if(!$search_key)
            {
                $moduleType = $table_where->moduleType ? $table_where->moduleType : 1;//板块类型1首页；2门店首页，3门店列表
                $view_spread_model = $this->getViewSpreadTable();
                $view_spread_model->moduleType = $moduleType;
                if(!$view_spread_model->moduleType)
                {
                    return STATUS_PARAMETERS_INCOMPLETE;
                }
                $view_spread_model->type = 1;
                $view_spread_model->from = 1;
                $view_spread_model->merchantStatus = 1;
                $merchant_list = $view_spread_model->getApiSpreadList();
                if(isset($merchant_list['list']) && $merchant_list['list'])
                {
                    $view_album_model = $this->getViewAlbumTable();
                    $view_album_model->type = 2;
                    foreach ($merchant_list['list'] as $val) {
                        if($page == 1)
                        {
                            $item = array(
                                'id' => $val->m_uuid,
                                'name' => $val->merchant_name,
                                'imagePath' => '',
                                'label' => $val->merchant_label_name,
                                'goodEvaluate' => $val->m_praise_proportion,
                                'distance' => 0,
                                'stars' => $val->m_stars,
                                'regionInfo' => $val->m_address,
                                'isConsumerRightsProtection' => $val->is_consumer_rights_protection == 1 ? 2 : 1
                            );
                            $view_album_model->fromId = $val->merchant_id;
                            $album = $view_album_model->getDetails();
                            $item['imagePath'] = $album && $album->path && $album->filename ? $album->path . $album->filename : '';
                            $list[] = $item;
                        }
                        if($val->merchant_id)
                        {
                            $not_in_merchant_ids[] = $val->merchant_id;
                        }
                    }
                }
            }
        }

        $this->tableObj = $this->getViewMerchantTable();
        $this->initModel();
        $this->tableObj->searchKeyWord = $search_key;
        $user_id = $this->getUserId();
        if($table_where->categoryId)
        {
            $this->tableObj->merchantCategoryId = $table_where->categoryId;
        }
//        if($table_where->cityId)
//        {
//            $this->tableObj->communityCityId = $table_where->cityId;
//        }
        if($table_where->communityId)
        {
            $this->tableObj->communityRegionId = $table_where->communityId;
        }
        $coordinate = array();
        if ($table_where->longitude && $table_where->latitude && $action == 3) {
            $coordinate = $this->getCornersCoordinate($table_where->longitude, $table_where->latitude, 1);
        }

        //补全因为推广出现的行数据空缺↓↓↓↓↓
        if($not_in_merchant_ids)
        {
            $divider = count($not_in_merchant_ids)%$this->lineNumber;
            if($divider)
            {
                $limit_diff = $this->lineNumber - $divider;
                $this->tableObj->limit = $limit_diff;
                $this->tableObj->page = 1;
                $merchant_list = $this->tableObj->getApiList($action,$user_id,$not_in_merchant_ids,$table_where->longitude,$table_where->latitude,$coordinate);
                if ($merchant_list['list']) {
                    $album_model = $this->getViewAlbumTable();
                    $album_model->type = 2;

                    foreach ($merchant_list['list'] as $val) {
                        if($page == 1)
                        {
                            $item = array(
                                'id' => isset($val->uuid) ? $val->uuid : (isset($val->m_uuid) ? $val->m_uuid : ''),
                                'name' => isset($val->name) ? $val->name : (isset($val->merchant_name) ? $val->merchant_name : ''),
                                'imagePath' => '',
                                'label' => isset($val->merchant_label_name) ? $val->merchant_label_name : '',
                                'goodEvaluate' => $val->praise_proportion,
                                //                    'distance' => $val->distance,
                                'stars' => $val->stars,
                                'regionInfo' => $val->region_info,
                                'address' => $val->address,
                                'isConsumerRightsProtection' => $val->is_consumer_rights_protection == 1 ? 2 : 1
                            );
                            if($table_where->longitude && $table_where->latitude) {
                                $distance = $this->getDistance($table_where->latitude, $table_where->longitude, $val->latitude, $val->longitude);
                                $item['distance'] = $distance . '';
                            }

                            $album_model->fromId = $action == 2 ? $val->merchant_id : $val->id;
                            $album = $album_model->getDetails();
                            $item['imagePath'] = $album && isset($album->path) && isset($album->filename) ? $album->path . $album->filename : '';
                            if($action == 2)
                            {
                                $item['favoritesId'] = $val->id;
                            }
                            $list[] = $item;
                        }
                        $not_in_merchant_ids[] = $val->id;
                    }
                }
                $this->initModel();
            }
        }
        //补全因为推广出现的行数据空缺↑↑↑↑↑

        $merchant_list = $this->tableObj->getApiList($action,$user_id,$not_in_merchant_ids,$table_where->longitude,$table_where->latitude,$coordinate);
        if ($merchant_list['list']) {
            $album_model = $this->getViewAlbumTable();
            $album_model->type = 2;

            foreach ($merchant_list['list'] as $val) {
                $item = array(
                    'id' => isset($val->uuid) ? $val->uuid : (isset($val->m_uuid) ? $val->m_uuid : ''),
                    'name' => isset($val->name) ? $val->name : (isset($val->merchant_name) ? $val->merchant_name : ''),
                    'imagePath' => '',
                    'label' => isset($val->merchant_label_name) ? $val->merchant_label_name : '',
                    'goodEvaluate' => $val->praise_proportion,
//                    'distance' => $val->distance,
                    'stars' => $val->stars,
                    'regionInfo' => $val->region_info,
                    'address' => $val->address,
                    'isConsumerRightsProtection' => $val->is_consumer_rights_protection == 1 ? 2 : 1
                );
                if($table_where->longitude && $table_where->latitude) {
                    $distance = $this->getDistance($table_where->latitude, $table_where->longitude, $val->latitude, $val->longitude);
                    $item['distance'] = $distance . '';
                }

                $album_model->fromId = $action == 2 ? $val->merchant_id : $val->id;
                $album = $album_model->getDetails();
                $item['imagePath'] = $album && isset($album->path) && isset($album->filename) ? $album->path . $album->filename : '';
                if($action == 2)
                {
                    $item['favoritesId'] = $val->id;
                }
                $list[] = $item;
            }

            $total = $merchant_list['total'];
        }

        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->merchants = $list;
        return $response;
    }

    /**

     * @abstract order_by 1、timestamp
     * @param number $order_by 1智能排序（默认）；2距离；3评价
     * @return string
     */
    public function OrderBy($order_by = 1)
    {
        switch ($order_by) {
            case 1:
                $result = 'weight';
                break;
            case 2:
                $result = 'distance';
                break;
            case 3:
                $result = 'stars';
                break;
            default:
                $result = 'id';
                break;
        }
        return $result;
    }

}
