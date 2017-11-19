<?php
namespace Api\Controller;

use Api\Controller\Request\UserDetailsRequest;

/**
 * 售后详细
 * @author WZ
 */
class CustomerServiceDetails extends CommonController
{

    /**
     * @return \Api\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $id = $request->id;
        if(!$id){
            return STATUS_PARAMETERS_INCOMPLETE;
        }

        $this->tableObj = $this->getViewCustomerServiceApplyTable();
        $this->tableObj->id = $id;
        $details = $this->tableObj->getDetails();
        if(!$details || $details->delete == '1')
        {
            return STATUS_NODATA;
        }

        $service = array();
        $view_album_model = $this->getViewAlbumTable();
        $view_album_model->type = 1;
        $view_album_model->fromId  = $details->goods_id;
        $album = $view_album_model->getDetails();
        $service_goodses = array(
            array(
                'id' => $details->g_uuid,
                'goodsId' => $details->g_uuid,
                'name' => $details->goods_name,
                'cash' => $details->price,
                'number' => $details->number,
                'imagePath' => $album && $album->path && $album->filename ? $album->path . $album->filename : '',
                'attrDesc' => $details->attr_desc
            ),
        );
        $reason_type = array("1"=>'商品与描述不符','2'=>'少件漏发','3'=>'卖家发错货','4'=>'未按约定时间发货','5'=>'其它原因');
        $service = array(
            'serviceSn' => $details->c_uuid,
            'type' => $details->type,
            'status' => $details->status,
            'cash' => $details->cash,
            'number' => $details->number,
            'reason' => $reason_type[$details->reason_type],//产品说显示用户选择的原因
            'refuseReason' => $details->remark,
            'timestamp' => $details->timestamp,
            'confirmationTime' => $details->confirmation_time,
            'refundTime' => $details->refund_time,
            'consignee' => $details->consignee,
            'deliveryAddress' => $details->delivery_address,
            'mobile' => $details->c_mobile,
            'serviceGoodses' => $service_goodses,
            'address' => ['name'=>$details->o_name,'mobile'=>$details->o_mobile,'address'=>$details->address],
        );
        $response->service = $service;
        $response->status = STATUS_SUCCESS;
        return $response;
    }
}