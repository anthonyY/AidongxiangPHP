<?php
namespace Api\Controller;

/**
 * 业务，音频详情
 */
class AudioDetails extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $id = $request->id; // 音频id
        if(!$id)
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }

        $this->tableObj = $this->getViewAudioTable();
        $this->tableObj->id = $id;
        $details = $this->tableObj->getDetails();
        if(!$details)
        {
            return STATUS_NODATA;
        }

        $isBuy = 1;
        $isFavorite = 1;
        if($user_id = $this->getUserId())
        {
            $favoriteTable = $this->getFavoriteTable();
            $favoriteTable->userId = $user_id;
            $favoriteTable->audioId = $id;
            $res = $favoriteTable->checkUserFavorite();
            if($res)$isFavorite = 2;

            $buyLogTable = $this->getBuyLogTable();
            $buyLogTable->userId = $user_id;
            $buyLogTable->audioId = $id;
            $res = $buyLogTable->checkUserBuy();
            if($res)$isBuy = 2;
        }

        $audio = array(
            'id' => $details->id,
            'audioType' => $details->type,
            'name' => $details->name,
            'price' => $details->price,
            'payType' => $details->pay_type,
            'praiseNum' => $details->praise_num,
            'playNum' => $details->play_num,
            'commentNum' => $details->comment_num,
            'isFavorite' => $isFavorite,
            'isPraise' => 1,
            'status' => $details->status,
            'imagePath' => $details->image_filename?$details->image_path.$details->image_filename:'',
            'isBuy' => $isBuy,
            'description' => $details->description,
            'audioPath' => $details->pay_type==2?($isBuy==2?$details->full_path:$details->auditions_path):$details->full_path,
            'audioLength' => $details->pay_type==2?($isBuy==2?$details->audio_length:$details->auditions_length):$details->audio_length,
        );

        $praiseTable = $this->getPraiseTable();
        $praiseTable->userId = $this->getUserId();
        $praiseTable->type = 1;
        $praiseTable->fromId = $id;
        $praise_res = $praiseTable->checkUserPraise();
        if($praise_res)$audio['isPraise'] = 2;

        $response->status = STATUS_SUCCESS;
        $response->audio = $audio;
        return $response;
    }
}
