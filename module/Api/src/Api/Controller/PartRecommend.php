<?php
namespace Api\Controller;

/**
 * 模块推荐
 *
 */
class PartRecommend extends CommonController
{
    const HOT_RECOMMEND_ID = 1;//热门推荐ID

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action ? $request->action : 1;//1热门推荐
        $recommend = '';
        if($action == 1)
        {
            $ViewPartRecommendTable = $this->getViewPartRecommendTable();
            $PartRecommendLabelTable = $this->getPartRecommendLabelTable();
            $ViewPartRecommendTable->id = self::HOT_RECOMMEND_ID;
            $PartRecommendLabelTable->partRecommendId = self::HOT_RECOMMEND_ID;
            $details = $ViewPartRecommendTable->getDetails();
            if($details)
            {
                $recommend = [
                    'id' => self::HOT_RECOMMEND_ID,
                    'name' => $details->name,
                    'imagePath' => $details->path . $details->filename,
                    'link' => $details->link,
                ];
                $normalLabels = [];
                $categoryLabels = [];
                $list = $PartRecommendLabelTable->getApiList();
                if(isset($list['list']) && $list['list'])
                {
                    foreach ($list['list'] as $val) {
                        $item = ['id'=>$val->id,'name'=>$val->name];
                        if($val->type == 1)//模块普通标签
                        {
                            $item['link'] = $val->link;
                            $normalLabels[] = $item;
                        }
                        if($val->type == 2)//模块分类标签
                        {
                            $categoryLabels[] = $item;
                        }
                    }
                }
                $recommend['normalLabels'] = $normalLabels;
                $recommend['categoryLabels'] = $categoryLabels;
            }
        }

        $response->total = 1;
        $response->status = STATUS_SUCCESS;
        $response->recommend = $recommend;
        return $response;
    }
}
