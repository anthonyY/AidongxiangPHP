<?php
namespace Api\Controller;
use Api\Controller\Request\GoodsDetailsRequest;

/**
 * 业务，商品详情
 */
class GoodsDetails extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new GoodsDetailsRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $id = $request->id; // 商品uuid
        $action = $request->action ? $request->action : 1;//1普通商品，2限时抢购商品，3拼团V2.0
        if(!$id)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if($action == 1)
        {
            $this->tableObj = $this->getViewGoodsTable();
            $this->tableObj->uuid = $id;
        }
        else
        {
            if(!$request->attrIds)
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            if($action == 2)
            {
                $this->tableObj = $this->getViewGoodsFlashSaleTable();
                $this->tableObj->uuid = $id;
            }
            if($action == 3)
            {
                $this->tableObj = $this->getViewGroupBuyingGoodsTable();
                $this->tableObj->gUuid = $id;
                $this->tableObj->status = 2;
            }
            $this->tableObj->attrIds = $request->attrIds;
        }

        $details = $this->tableObj->getDetails();
        if(!$details || (isset($details->delete) && $details->delete == 1))
        {
            return STATUS_NODATA;
        }
        $user_id = $this->getUserId();
        $images = array();
        $view_album_model = $this->getViewAlbumTable();
        $view_album_model->type = 1;
        $view_album_model->fromId = $action == 1 ? $details->id : $details->goods_id;
        $album_list = $view_album_model->getList();
        if($album_list)
        {
            foreach ($album_list as $item) {
                $images[] = $item['path'] . $item['filename'];
            }
        }

        //产品是否收藏
        $is_favorites = 0;
        if($user_id)
        {
            $view_favorites = $this->getViewFavoritesTable();
            $view_favorites->goodsId = $action == 1 ? $details->id : $details->goods_id;
            $view_favorites->userId = $user_id;
            $res = $view_favorites->isFavorites();
            if($res)
            {
                $is_favorites = 1;
            }
        }

        $attr_array = array();
        $goods_attr_relation_model = $this->getGoodsAttrRelationTable();
        if($action == 1)//普通商品
        {
            $goods_attr_relation_model->goodsId = $details->id;
            $goods_attrs = $goods_attr_relation_model->getGoodsAttrList();
            if($goods_attrs)
            {
                foreach ($goods_attrs as $val) {
                    $item = array(
                        'id' => $val['id'],
                        'parentId' => $val['parent_id'],
                        'name' => $val['name'],
                    );
                    $attr_array[] = $item;
                }
            }
        }
        else //限时抢购或拼团产品
        {
            if($details->attr_ids)
            {
                $attr_ids = explode('|',$details->attr_ids);
                $goods_attrs = $goods_attr_relation_model->getFlashGoodsAttrByAttrIds($attr_ids);
                if($goods_attrs)
                {
                    foreach ($goods_attrs as $val) {
                        $item = array(
                            'id' => $val['id'],
                            'parentId' => $val['parent_id'],
                            'name' => $val['name'],
                        );
                        $attr_array[] = $item;
                    }
                }
            }
        }

        $merchant = '';
        if($details->merchant_id)
        {
            $view_merchant = $this->getViewMerchantTable();
            $view_merchant->id = $details->merchant_id;
            $merchant_details = $view_merchant->getDetails();
            if($merchant_details)
            {
                $view_album_model->type = 2;
                $view_album_model->fromId = $details->merchant_id;
                $merchant_album = $view_album_model->getDetails();
                $address = array(
                    'regionId' => $merchant_details->community_region_id,
                    'regionInfo' => $merchant_details->address,
                    'street' => $merchant_details->street,
                    'longitude' => $merchant_details->longitude,
                    'latitude' => $merchant_details->latitude
                );
                $merchant = array(
                    'id' => $merchant_details->uuid,
                    'name' => $merchant_details->name,
                    'mobile' => $merchant_details->mobile,
                    'label' => $merchant_details->merchant_level_name,
                    'imagePath' => $merchant_album && $merchant_album->path && $merchant_album->filename ? $merchant_album->path . $merchant_album->filename : '',
                    'stars' => $merchant_details->stars,
                    'goodEvaluate' => $merchant_details->praise_proportion,
                    'address' => $address,
                );

                //门店是否收藏
                $is_merchant_favorites = 0;
                if($user_id)
                {
                    $view_favorites = $this->getViewFavoritesTable();
                    $view_favorites->merchantId = $details->merchant_id;
                    $view_favorites->goodsId = '';
                    $view_favorites->userId = $user_id;
                    $res = $view_favorites->isFavorites();
                    if($res)
                    {
                        $is_merchant_favorites = 1;
                    }
                    $merchant['isFavorite'] = $is_merchant_favorites;
                }
            }
        }

        $goods = array(
            'id' => $action == 3 ? $details->g_uuid : $details->uuid,
            'goodsId' => $action == 1 ? 0 : $details->id,
            'name' => $action == 3 ? $details->g_name : $details->name,
            'label' => $details->goods_label_name,
            'stars' => $details->stars,
            'salesVolume' => $details->sales_volume,
            'goodsType' => $details->goods_type,
            'status' => $action == 1 ? $details->status : ($action == 2 ? $details->goods_status : $details->g_status),
            'images' => $images,
            'isFavorites' => $is_favorites,
            'attrs' => $attr_array,
            'merchant' => $merchant,
            'description' => $details->description,
            'isPresale' => $details->is_presale,
            'isOnlineBook' => $details->is_online_book,
        );

        if($details->is_presale == 2)//是预购商品
        {
            $goods['presaleStartTime'] = $details->presale_start_time;
            $goods['presaleEndTime'] = $details->presale_end_time;
        }
        if($details->is_online_book == 2 && $details->room_type_id)//是实时选房
        {
            //查询房型名称
            $RoomTypeTable = $this->getRoomTypeTable();
            $RoomTypeTable->id = $details->room_type_id;
            $RoomTypeDetails = $RoomTypeTable->getDetails();
            if($RoomTypeDetails)
            {
                $goods['roomTypeName'] = $RoomTypeDetails->name;
            }
        }

        if($action == 3)
        {
            $goods['upperLimit'] = $details->upper_limit;//团购商品最大购买数量（V2.0新增）
        }

        //查询商品的平台一级，二级分类
        $category = '';
        if($details->platform_category_id)
        {
            $category_table = $this->getCategoryTable();
            $category_table->id = $details->platform_category_id;
            $cate_details = $category_table->getDetails();
            $category = [];
            if($cate_details)
            {
                $category['second'] = $cate_details->name;
            }
            if($cate_details && $cate_details->parent_id)
            {
                $category_table->id = $cate_details->parent_id;
                $cate_details = $category_table->getDetails();
                $category['first'] = $cate_details->name;
            }
            $category = $category ? $category : '';
        }
        $goods['category'] = $category;

        if($details->type == 2)//套餐
        {
            $goods_packages = array();
            $package_total_price = 0;//套餐总价

            $view_goods_package = $this->getViewGoodsPackageTable();
            $view_goods_package->goodsId = $action == 1 ? $details->id : $details->goods_id;
            $sub_goods_list = $view_goods_package->getPackSubGoodsList();
            if($sub_goods_list['list'])
            {
                $view_album_model = $this->getViewAlbumTable();
                $view_album_model->type = 1;
                foreach ($sub_goods_list['list'] as $val) {
                    $package_total_price += $val->price;
                    $item = array(
                        'id' => $val->sub_g_uuid,
                        'name' => $val->sub_g_name,
                        'imagePath' => '',
                        'price' => $val->price,
                        'number' => $val->number
                    );
                    $view_album_model->fromId = $val->goods_sub_id;
                    $album = $view_album_model->getDetails();
                    $item['imagePath'] = $album && $album->path && $album->filename ? $album->path . $album->filename : '';
                    $goods_packages[] = $item;
                }
            }
            $goods['packageTotalPrice'] = $package_total_price;
            $goods['goodsPackages'] = $goods_packages;
        }
        $visitor = $this->getVisitorTable();
        $visitor->merchantId = $details->merchant_id;
        $visitor->userId = $user_id ? $user_id : 0;
        $visitor->addData();
        $response->status = STATUS_SUCCESS;
        $response->goods = $goods;
        return $response;
    }
}
