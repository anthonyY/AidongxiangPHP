<?php
namespace Api\Controller;

use Platform\Model\ViewOrderGoodsGateway;
use Zend\Db\Sql\Where;
use Api\Controller\Request\GoodsWhereRequest;
use Zend\Db\Sql\Expression;
/**
 * 业务，商品列表
 */
class GoodsList extends CommonController
{
    /**
     * 一行的商品个数（PC端）
     */
    private $lineNumber = 5;

    public function __construct()
    {
        $this->myWhereRequest = new GoodsWhereRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $table_where = $this->getTableWhere();
        // a :1全部产品（默认，搜索有包括商品和服务），2商家产品；3限时抢购商品； 4猜你喜欢，5特惠产品，6收藏商品（区分商品和服务），7未评价商品，8 优惠券商品(V1.1 新增),9拼团商品列表V2.0，10优惠券产品列表V2.0，11首页热门推荐商品列表V2.0
        $action = $request->action = $request->action ? $request->action : 1;
        if(!in_array($action, array(1, 2, 3, 4, 5, 6, 7,8,9,10,11))){
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $total = 0;
        $list = array();

        $table = $this->getTable();
        $page = $table->page ? $table->page : 1;
        //推广商品列表
        $not_in_goods_ids = [];
        if(in_array($action,array(1,5)) || $table_where->merchantId == SELF_MERCHANT)
        {
//            if($page == 1 && !$table_where->search_key)
//            {
            if(!$table_where->search_key)
            {
                $moduleType = $table_where->moduleType ? $table_where->moduleType : 1;//1商城首页，2商城列表，3服务首页，4服务列表
                $map_array = [1=>4,2=>5,3=>6,4=>7];
                $view_spread_model = $this->getViewSpreadTable();
                $view_spread_model->moduleType = isset($map_array[$moduleType]) ? $map_array[$moduleType] : 0;
                if(!$view_spread_model->moduleType)
                {
                    return STATUS_PARAMETERS_INCOMPLETE;
                }
                if(in_array($moduleType,array(1,2)))
                {
                    $view_spread_model->type = 2;
                }
                if(in_array($moduleType,array(3,4)))
                {
                    $view_spread_model->type = 3;
                }
                if($table_where->categoryId)
                {
                    if($table_where->categoryType == 1)
                    {
                        $view_spread_model->platformCategoryId = $table_where->categoryId;
                    }
                    if($table_where->categoryType == 2)
                    {
                        $view_spread_model->shopCategoryId = $table_where->categoryId;
                    }
                }
                if($table_where->labelIds && is_array($table_where->labelIds))
                {
                    $view_spread_model->goodsLabelId = $table_where->labelIds;
                }
                $view_spread_model->from = $table_where->merchantId != SELF_MERCHANT ? 1 : 2;
                $view_spread_model->goodsStatus = 1;
                $goods_list = $view_spread_model->getApiSpreadList();
                if(isset($goods_list['list']) && $goods_list['list'])
                {
                    $view_album_model = $this->getViewAlbumTable();
                    $view_album_model->orderBy = 'id ASC';
                    $view_album_model->type = 1;
                    foreach ($goods_list['list'] as $val) {
                        if($page == 1 && !$table_where->search_key) {
                            $item = array(
                                'id' => $val->g_uuid,
                                'name' => $val->goods_name,
                                'price' => $val->goods_price,
                                'originalPrice' => $val->goods_original_price,
                                'goodsType' => $val->goods_type,
                                'imagePath' => '',
                                'label' => $val->goods_label_name,
                                'saleNumber' => $val->sales_volume,
                                'status' => $val->goods_status,
                                'isSelf' => $val->goods_merchant_id == SELF_MERCHANT ? 2 : 1,
                                'isPresale' => $val->is_presale != 2 ? 1 : 2,
                            );
                            $view_album_model->fromId = $val->goods_id;
                            $album = $view_album_model->getDetails();
                            $item['imagePath'] = $album && $album->path && $album->filename ? $album->path . $album->filename : '';
                            //商家信息
                            $merchant = ['name'=>$val->goods_merchant_name];
                            $item['merchant'] = $merchant;
                            $list[] = $item;
                        }
                        $not_in_goods_ids[] = $val->goods_id;
                    }
                }
            }
        }

        if($action == 1 || $action == 2)//1全部产品（默认，搜索有包括商品和服务） 2商家产品
        {
            if($action == 2 && !$table_where->merchantId)
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }

            $this->tableObj = $this->getViewGoodsTable();
            $this->tableObj->merchantStatus = 1;//门店开启的
            $this->initModel();
            $this->tableObj->searchKeyWord = $table_where->search_key;
//            if($table_where->cityId)
//            {
//                $this->tableObj->communityCityId = $table_where->cityId;
//            }
//            if($table_where->communityId)
//            {
//                $this->tableObj->communityRegionId = $table_where->communityId;
//            }
            if($table_where->merchantId)
            {
                $this->tableObj->mUuid = $table_where->merchantId;
            }
            if($table_where->categoryId)
            {
                if($table_where->categoryType == 1)
                {
                    $this->tableObj->platformCategoryId = $table_where->categoryId;
                }
                if($table_where->categoryType == 2)
                {
                    $this->tableObj->shopCategoryId = $table_where->categoryId;
                }
            }
            if($table_where->type)
            {
                $this->tableObj->goodsType = $table_where->type;
            }
            if($table_where->labelIds && is_array($table_where->labelIds))
            {
                $this->tableObj->goodsLabelId = $table_where->labelIds;
            }

            //补全因为推广出现的行数据空缺↓↓↓↓↓
            if($not_in_goods_ids)
            {
                $divider = count($not_in_goods_ids)%$this->lineNumber;
                if($divider)
                {
                    $limit_diff = $this->lineNumber - $divider;
                    $this->tableObj->limit = $limit_diff;
                    $this->tableObj->page = 1;
                    $goods_list = $this->tableObj->getApiList($action,$not_in_goods_ids);
                    if($goods_list['list'])
                    {
                        foreach ($goods_list['list'] as $val) {
                            if($page == 1 && !$table_where->search_key) {
                                $item = array(
                                    'id' => $val->uuid,
                                    'name' => $val->name,
                                    'price' => $val->price,
                                    'originalPrice' => $val->original_price,
                                    'goodsType' => $val->goods_type,
                                    'imagePath' => $val->image_path,
                                    'label' => $val->goods_label_name,
                                    'saleNumber' => $val->sales_volume,
                                    'status' => $val->status,
                                    'isSelf' => $val->merchant_id == SELF_MERCHANT ? 2 : 1,
                                    'isPresale' => $val->is_presale != 2 ? 1 : 2,
                                );
                                //商家信息
                                $merchant = ['name'=>$val->merchant_name];
                                $item['merchant'] = $merchant;
                                $list[] = $item;
                            }
                            $not_in_goods_ids[] = $val->id;
                        }
                    }
                    $this->initModel();
                }
            }
            //补全因为推广出现的行数据空缺↑↑↑↑↑

            $goods_list = $this->tableObj->getApiList($action,$not_in_goods_ids);
            if($goods_list['list'])
            {
                foreach ($goods_list['list'] as $val) {
                    $item = array(
                        'id' => $val->uuid,
                        'name' => $val->name,
                        'price' => $val->price,
                        'originalPrice' => $val->original_price,
                        'goodsType' => $val->goods_type,
                        'imagePath' => $val->image_path,
                        'label' => $val->goods_label_name,
                        'saleNumber' => $val->sales_volume,
                        'status' => $val->status,
                        'isSelf' => $val->merchant_id == SELF_MERCHANT ? 2 : 1,
                        'isPresale' => $val->is_presale != 2 ? 1 : 2,
                    );
                    //商家信息
                    $merchant = ['name'=>$val->merchant_name];
                    $item['merchant'] = $merchant;
                    $list[] = $item;
                }
                $total = $goods_list['total'];
            }
        }

        if($action == 3)//限时抢购
        {
            $this->tableObj = $this->getViewGoodsFlashSaleTable();
            $this->tableObj->merchantStatus = 1;
            $this->initModel();
//            if($table_where->cityId)
//            {
//                $this->tableObj->communityCityId = $table_where->cityId;
//            }
//            if($table_where->communityId)
//            {
//                $this->tableObj->communityRegionId = $table_where->communityId;
//            }
            $this->tableObj->from = 1;
            if($table_where->categoryType == 2)//自营超市的限时抢购
            {
                $this->tableObj->from = 2;
                $this->tableObj->merchantId = SELF_MERCHANT;
            }
            $goods_list = $this->tableObj->getApiList();
            if($goods_list['list'])
            {
                foreach ($goods_list['list'] as $val) {
                    $item = array(
                        'id' => $val->uuid,
                        'attrIds' => $val->attr_ids,
                        'name' => $val->name,
                        'price' => $val->active_price,
                        'originalPrice' => $val->original_price,
                        'goodsType' => $val->goods_type,
                        'imagePath' => $val->image_path,
                        'isSelf' => $val->merchant_id == SELF_MERCHANT ? 2 : 1,
                        'isPresale' => $val->is_presale != 2 ? 1 : 2,
                    );
                    $list[] = $item;
                }
                $total = $goods_list['total'];
            }
        }

        if($action == 4)//猜你喜欢
        {
            //1、对未登录用户或新用户。我们按照销量推荐商品及服务
            //2、对老用户，按照购买记录推荐以前购买商品同分类的商品
            $this->tableObj = $this->getViewGoodsTable();
            $this->tableObj->merchantStatus = 1;
            $this->initModel();
            $this->tableObj->orderBy = 'sales_volume DESC';
            $user_id = $this->getUserId();
            $category_ids = array('platform_category'=>array(),'shop_category'=>array());
            if($user_id)
            {
                $view_order_goods_model = $this->getViewOrderGoodsTable();
                $view_order_goods_model->userId = $user_id;
                $category_ids = $view_order_goods_model->getUserOrderGoodsList(2);
            }
            $where = new Where();
            $where->equalTo('delete',0)->equalTo('status',1);
            if($category_ids['platform_category'] || $category_ids['shop_category'])
            {
                //相关分类下的产品
                if($category_ids['platform_category'] && !$category_ids['shop_category'])
                {
                    $where->in('platform_category_id',$category_ids['platform_category']);
                }
                if(!$category_ids['platform_category'] && $category_ids['shop_category'])
                {
                    $where->in('shop_category_id',$category_ids['shop_category']);
                }
                if($category_ids['platform_category'] && $category_ids['shop_category'])
                {
                    $where->in('platform_category_id',$category_ids['platform_category'])->or->in('shop_category_id',$category_ids['shop_category']);
                }
                $goods_list = $this->tableObj->getYouLikeList($category_ids['platform_category'],$category_ids['shop_category']);
            }
            else
            {
                //销量推荐商品
                $goods_list = $this->tableObj->getApiList();
            }

            if($goods_list['list'])
            {
                foreach ($goods_list['list'] as $val) {
                    $item = array(
                        'id' => $val->uuid,
                        'name' => $val->name,
                        'price' => $val->price,
                        'originalPrice' => $val->original_price,
                        'goodsType' => $val->goods_type,
                        'imagePath' => $val->image_path,
                        'label' => $val->goods_label_name,
                        'saleNumber' => $val->sales_volume,
                        'status' => $val->status,
                        'isSelf' => $val->merchant_id == SELF_MERCHANT ? 2 : 1,
                        'isPresale' => $val->is_presale != 2 ? 1 : 2,
                    );
                    //商家信息
                    $merchant = ['name'=>$val->merchant_name];
                    $item['merchant'] = $merchant;
                    $list[] = $item;
                }
                $total = $goods_list['total'];
            }
        }

        if($action == 5)//特惠商品
        {
            $this->tableObj = $this->getViewGoodsTable();
            $this->initModel();
            $this->tableObj->searchKeyWord = $table_where->search_key;
            $this->tableObj->merchantStatus = 1;//门店开启的
            if($table_where->cityId)
            {
                $this->tableObj->communityCityId = $table_where->cityId;
            }
            if($table_where->communityId)
            {
                $this->tableObj->communityRegionId = $table_where->communityId;
            }
            if($table_where->type)
            {
                $this->tableObj->goodsType = $table_where->type;
            }

            //补全因为推广出现的行数据空缺↓↓↓↓↓
            if($not_in_goods_ids)
            {
                $divider = count($not_in_goods_ids)%$this->lineNumber;
                if($divider)
                {
                    $limit_diff = $this->lineNumber - $divider;
                    $this->tableObj->limit = $limit_diff;
                    $this->tableObj->page = 1;
                    $goods_list = $this->tableObj->getApiList($action,$not_in_goods_ids);
                    if($goods_list['list'])
                    {
                        foreach ($goods_list['list'] as $val) {
                            if($page == 1 && !$table_where->search_key) {
                                $item = array(
                                    'id' => $val->uuid,
                                    'name' => $val->name,
                                    'price' => $val->price,
                                    'originalPrice' => $val->original_price,
                                    'goodsType' => $val->goods_type,
                                    'imagePath' => $val->image_path,
                                    'label' => $val->goods_label_name,
                                    'saleNumber' => $val->sales_volume,
                                    'status' => $val->status,
                                    'isSelf' => $val->merchant_id == SELF_MERCHANT ? 2 : 1,
                                    'isPresale' => $val->is_presale != 2 ? 1 : 2,
                                );
                                //商家信息
                                $merchant = ['name'=>$val->merchant_name];
                                $item['merchant'] = $merchant;
                                $list[] = $item;
                            }
                            $not_in_goods_ids[] = $val->id;
                        }
                    }
                    $this->initModel();
                }
            }
            //补全因为推广出现的行数据空缺↑↑↑↑↑

            $goods_list = $this->tableObj->getApiList($action,$not_in_goods_ids);
            if($goods_list['list'])
            {
                foreach ($goods_list['list'] as $val) {
                    $item = array(
                        'id' => $val->uuid,
                        'name' => $val->name,
                        'price' => $val->price,
                        'originalPrice' => $val->original_price,
                        'goodsType' => $val->goods_type,
                        'imagePath' => $val->image_path,
                        'label' => $val->goods_label_name,
                        'saleNumber' => $val->sales_volume,
                        'status' => $val->status,
                        'isSelf' => $val->merchant_id == SELF_MERCHANT ? 2 : 1,
                        'isPresale' => $val->is_presale != 2 ? 1 : 2,
                    );
                    //商家信息
                    $merchant = ['name'=>$val->merchant_name];
                    $item['merchant'] = $merchant;
                    $list[] = $item;
                }
                $total = $goods_list['total'];
            }
        }

        if($action == 6)//6收藏商品（区分商品和服务）
        {
            $this->checkLogin();
            $user_id = $this->getUserId();
            if(!$table_where->type)
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $this->tableObj = $this->getViewFavoritesTable();
            $this->initModel();
            $this->tableObj->type = $table_where->type;
            $this->tableObj->userId = $user_id;
            $this->tableObj->orderBy = 'timestamp DESC';
            $goods_list = $this->tableObj->getApiList();
            if($goods_list['list'])
            {
                $view_album_model = $this->getViewAlbumTable();
                $view_album_model->orderBy = 'id ASC';
                $view_album_model->type = 1;
                foreach ($goods_list['list'] as $val) {
                    $item = array(
                        'id' => $val->g_uuid,
                        'favoritesId' => $val->id,
                        'name' => $val->goods_name,
                        'price' => $val->price,
                        'originalPrice' => $val->original_price,
                        'goodsType' => $val->goods_type,
                        'imagePath' => '',
                        'label' => $val->goods_label_name,
                        'saleNumber' => $val->sales_volume,
                        'status' => $val->status,
                        'isSelf' => $val->goods_merchant_id == SELF_MERCHANT ? 2 : 1,
                        'isPresale' => $val->is_presale != 2 ? 1 : 2,
                    );
                    $view_album_model->fromId = $val->goods_id;
                    $album = $view_album_model->getDetails();
                    $item['imagePath'] = $album && $album->path && $album->filename ? $album->path . $album->filename : '';
                    $list[] = $item;
                }
                $total = $goods_list['total'];
            }
        }

        if($action == 7) //7未评价商品
        {
            $this->checkLogin();
            $this->tableObj = $this->getViewOrderGoodsTable();
            $this->initModel();
            $user_id = $this->getUserId();
            $this->tableObj->userId = $user_id;
            $this->tableObj->commentStatus = 1;
            $this->tableObj->orderBy = 'id DESC';
            $goods_list = $this->tableObj->getUserOrderGoodsList(1);
            if($goods_list['list'])
            {
                foreach ($goods_list['list'] as $val) {
                    $item = array(
                        'id' => $val->id,
                        'name' => $val->goods_name,
                        'price' => $val->price,
                        'originalPrice' => $val->original_price,
                        'imagePath' => $val->image_path,
                        'saleNumber' => $val->sales_volume,
                        'isSelf' => $val->merchant_id == SELF_MERCHANT ? 2 : 1,
                    );
                    $list[] = $item;
                }
                $total = $goods_list['total'];
            }
        }

        if($action == 8 || $action == 10)//8 购物卡产品列表V1.1 新增 10优惠券商品(V2.0新增)
        {
            $shoppingCardId = $table_where->shoppingCardId;//购物卡ID（V1.1新增）
            $couponId = $table_where->couponId;//用户优惠券ID（V2.0新增）
            $type = $table_where->type ? $table_where->type : 1;//1.商品（默认）；2服务，
            if(($action == 8 && !$shoppingCardId) || ($action == 10 && !$couponId))
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
//            $this->checkLogin();
            $this->tableObj = $this->getViewGoodsTable();
            $this->tableObj->goodsType = $type;
            $this->initModel();
            if($action == 8)
            {
                $data = $this->tableObj->getShoppingCardGoodsList($shoppingCardId);
            }
            if($action == 10)
            {
                $data = $this->tableObj->getCouponGoodsList($couponId);
            }
            if($data['list'])
            {
                foreach ($data['list'] as $val) {
                    $item = array(
                        'id' => $val->uuid,
                        'name' => $val->name,
                        'price' => $val->price,
                        'originalPrice' => $val->original_price,
                        'goodsType' => $val->goods_type,
                        'imagePath' => $val->image_path,
                        'label' => $val->goods_label_name,
                        'saleNumber' => $val->sales_volume,
                        'isSelf' => $val->merchant_id == SELF_MERCHANT ? 2 : 1,
                        'isPresale' => $val->is_presale != 2 ? 1 : 2,
                    );
                    $list[] = $item;
                }
                $total = $data['total'];
            }
        }

        if($action == 9)//9拼团商品列表V2.0
        {
            $this->tableObj = $this->getViewGroupBuyingGoodsTable();
            $this->initModel();
            $data = $this->tableObj->getApiList();
            if(isset($data['list']) && $data['list'])
            {
                foreach ($data['list'] as $val) {
                    $item = array(
                        'id' => $val->g_uuid,
                        'attrIds' => $val->attr_ids,
                        'name' => $val->g_name,
                        'price' => $val->group_price,
                        'originalPrice' => $val->attr_original_price,
                        'goodsType' => $val->goods_type,
                        'imagePath' => $val->image_path,
                        'saleNumber' => $val->sales_volume,
                        'isSelf' => $val->merchant_id == SELF_MERCHANT ? 2 : 1,
                        'isPresale' => $val->is_presale != 2 ? 1 : 2,
                    );
                    $list[] = $item;
                }
                $total = $data['total'];
            }
        }

        if($action == 11)//11首页热门推荐商品列表V2.0
        {
            $partRecommendLabelId = $table_where->partRecommendLabelId;//模块推荐标签ID（V2.0新增）a=11
            if(!$partRecommendLabelId)
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $this->tableObj = $this->getViewPartRecommendGoodsTable();
            $this->tableObj->partRecommendLabelId = $partRecommendLabelId;
            $this->getTable()->order_by = 5;
            $this->initModel();
            $data = $this->tableObj->getApiList();
            if($data['list'])
            {
                foreach ($data['list'] as $val) {
                    $item = array(
                        'id' => $val->uuid,
                        'name' => $val->g_name,
                        'price' => $val->price,
                        'originalPrice' => $val->original_price,
                        'goodsType' => $val->goods_type,
                        'imagePath' => $val->image_path,
                        'label' => $val->goods_label_name,
                        'saleNumber' => $val->sales_volume,
                        'isSelf' => $val->merchant_id == SELF_MERCHANT ? 2 : 1,
                        'isPresale' => $val->is_presale != 2 ? 1 : 2,
                    );
                    $list[] = $item;
                }
                $total = $data['total'];
            }
        }

        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->goodses = $list;
        return $response;
    }

    /**
     *
     * @author liujun
     * @date 2014.3.18
     * @param number $order_by
     * @return string
     */
    public function OrderBy($order_by = 1)
    {
        switch($order_by){
            case 1:
                $result = 'sort';
                break;
            case 2:
                $result = 'stars';
                break;
            case 3:
                $result = 'sales_volume';
                break;
            case 4:
                $result = 'price';
                break;
            case 5:
                $result = 'id';
                break;
            default:
                $result = 'timestamp';
                break;
        }
        return $result;
    }
}
