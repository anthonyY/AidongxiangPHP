<?php
namespace Api\Controller;

use Api\Controller\Request\MerchantRequest;

/**
 * 业务，商家详情
 */
class MerchantDetails extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new MerchantRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $id = $request->id; // 商家编号id
        $merchant = array();
        if(is_numeric($id) && $id > 0){
            $this->tableObj = $this->getViewMerchantTable();
            $this->tableObj->uuid = $id;
            $info = $this->tableObj->getDetails();
            if(!$info){
                return STATUS_NODATA; // 返回无记录
            }
            $user_id = $this->getUserId();
            $visitor = $this->getVisitorTable(); //访客记录
            $visitor->userId = $user_id ? $user_id : 0;
            $visitor->addData($id);
            $merchant['id'] = $info->uuid;
            $merchant['name'] = $info->name;
            $merchant['stars'] = $info->stars;
            $merchant['label'] = $info->merchant_label_name;
            $merchant['goodEvaluate'] = $info->praise_proportion;
            $merchant['mobile'] = $info->mobile;
            $merchant['description'] = $info->description;
            $merchant['startTime'] = $info->start_time;
            $merchant['endTime'] = $info->end_time;
            $merchant['category'] = $info->category_name;

            $view_album_model = $this->getViewAlbumTable();
            $view_album_model->type = 2;
            $view_album_model->fromId = $info->id;
            $images = array();
            $album = $view_album_model->getList();
            if($album)
            {
                foreach ($album as $val) {
                    $item = array(
                        'id' => $val['image_id'],
                        'path' => $val['path'] && $val['filename'] ? $val['path'] . $val['filename'] : '',
                    );
                    $images[] = $item;
                }
            }
            $merchant['images'] = $images;
            $isFavorites = 1;
            if($user_id && $user_id != 'userId')
            {
                $view_favorites_model = $this->getViewFavoritesTable();
                $view_favorites_model->userId = $user_id;
                $view_favorites_model->merchantId = $info->id;
                $res = $view_favorites_model->isFavorites();
                $isFavorites = $res ? 2 : 1;
            }
            $merchant['isFavorite'] = $isFavorites;
            $merchant['evaluateNum'] = $info->evaluate_num;

            $address = array();
            $address['regionId'] = $info->region_id;
            $address['street'] = $info->street;
            $address['longitude'] = $info->longitude;
            $address['latitude'] = $info->latitude;
            $address['regionInfo'] = $info->address;
            $merchant['address'] = $address;
            $merchant['isConsumerRightsProtection'] = $info->is_consumer_rights_protection == 1 ? 2 : 1;
        }
        $response->status = STATUS_SUCCESS;
        $response->merchant = $merchant;
        return $response;
    }
}
