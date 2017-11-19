<?php
namespace Api\Controller;

use Api\Controller\Request\CommentSubmitRequest;
use Zend\Db\Sql\Where;

/**
 * 订单，订单评价
 * @author WZ
 */
class OrderCommentSubmit extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new CommentSubmitRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();

        $stars = $request->comment->stars; // 评价星级
        $images_id = $request->comment->imagesId;//评价图片ID
        $content = $request->comment->content;
        $order_goods_id = $request->orderGoodsId ? $request->orderGoodsId : 0;//订单产品表id

        if(!in_array($stars, array('1', '2', '3', '4', '5')) && !$order_goods_id){
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if(mb_strlen($content,"UTF-8")<1)
        {
             $response->description = "评论不能小于1个字符";
             $response->status = 1000;
             return $response;
        }

        $order_comment = $this->getEvaluateTable();
        $order_comment->stars = $stars;
        $order_comment->content = $content;
        $order_comment->orderGoodsId = $order_goods_id;
        $order_comment->userId = $this->getUserId();
        $order_comment->picShow = $images_id ? 1 : 2;
        $result = $order_comment->evaluateSubmit($images_id);
        $response->status = $result['code'];
        $response->description = $result['d'];
        return $response;
    }
}
