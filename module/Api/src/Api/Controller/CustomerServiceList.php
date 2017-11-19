<?php
namespace Api\Controller;

use Api\Controller\Request\CustomerServiceListWhereRequest;

/**
 * 售后列表
 * @author WZ
 */
class CustomerServiceList extends CommonController
{
    public function __construct()
    {
        $this->myWhereRequest = new CustomerServiceListWhereRequest();
        parent::__construct();
    }

    /**
     * @return \Api\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
//        $action = $request->action ? $request->action : 1;//售后类型：1商品售后 2服务售后
        $table_where = $this->getTableWhere();
        $status = $table_where->status ? $table_where->status : 0;
        if(!in_array($status,[0,1,2,3,4]))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $this->tableObj = $this->getViewCustomerServiceApplyTable();
        $this->initModel();
        $user_id = $this->getUserId();
        $this->tableObj->userId = $user_id;
        $this->tableObj->status = $status;
        $data = $this->tableObj->getApiList();
        $list = array();
        $total = 0;
        if($data['list'])
        {
            $view_album_model = $this->getViewAlbumTable();
            $view_album_model->type = 1;
            foreach ($data['list'] as $val) {
                $view_album_model->fromId  = $val->goods_id;
                $album = $view_album_model->getDetails();
                $service_goodses = array(
                    array(
                        'id' => $val->g_uuid,
                        'goodsId' => $val->g_uuid,
                        'name' => $val->goods_name,
                        'cash' => $val->price,
                        'number' => $val->number,
                        'imagePath' => $album && $album->path && $album->filename ? $album->path . $album->filename : '',
                        'attrDesc' => $val->attr_desc
                    ),
                );
                $merchant = array(
                    'id' => $val->m_uuid,
                    'type' => $val->merchant_type,
                    'name' => $val->merchant_name,
                    'serviceGoodses' => $service_goodses,
                    'telephone' => $val->m_telephone ? $val->m_telephone : $val->m_mobile,
                );
                $item = array(
                    'id' => $val->id,
                    'number' => $val->number,
                    'totalCash' => $val->cash,
                    'serviceSn' => $val->c_uuid,
                    'type' => $val->type,
                    'status' => $val->status,
                    'consignee' => $val->consignee,
                    'deliveryAddress' => $val->delivery_address,
                    'mobile' => $val->mobile,
                    'confirmationTime' => $val->confirmation_time,
                    'refundTime' => $val->refund_time,
                    'merchant' => $merchant
                );
                $list[] = $item;
            }
            $total = $data['total'];
        }

        $response->status = STATUS_SUCCESS;
        $response->services = $list;
        $response->total = $total;
        return $response;
    }
}