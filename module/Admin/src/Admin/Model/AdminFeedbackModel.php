<?php
/**
 * Created by PhpStorm.
 * User: lyndon
 * Date: 2016/11/30
 * Time: 22:13
 */

namespace Admin\Model;

use Zend\Db\Sql\Where;
use Api\Model\CommonModel;
use Api\Controller\Item\PushArgsItem;
use Zend\Db\Sql\Expression;

class AdminFeedbackModel extends CommonModel
{

    protected $table = "Feedback";

    /**
     * 意见反馈列表
     * @param unknown $condition
     * @version 2016-12-3 WZ
     */
    public function getFeedbackList($condition)
    {
//         var_dump($condition['where']);exit;
        $where = new Where();
//         $where->equalTo(DB_PREFIX.'comment.delete', DELETE_FALSE);
        $where->equalTo(DB_PREFIX.'notification_feedback.delete', 0);
        $c_where = $condition['where'];
        if (isset($c_where['start']) && $c_where['start']) {
            $where->greaterThanOrEqualTo(DB_PREFIX.'notification_feedback.timestamp', $c_where['start'].' 00:00:00');
        }
        if (isset($c_where['end']) && $c_where['end']) {
            $where->lessThanOrEqualTo(DB_PREFIX.'notification_feedback.timestamp', date("Y-m-d 00:00:00",strtotime($c_where['end']."+1 day")));
        }
        if (isset($c_where['status']) && $c_where['status']) {
            $where->equalTo(DB_PREFIX.'notification_feedback.reply_status', $c_where['status']);
        }
        
        $data = array(
            'columns' => array(
                'id',
                'content',
                'timestamp',
                'reply_status',
            ),
            'join' =>array(
                array(
                    'name' => DB_PREFIX.'user',
                    'on' => DB_PREFIX.'user.id = '.DB_PREFIX.'notification_feedback.user_id',
                    'columns' => array(
                        'name',
                    ),
                    'type' => 'left'
                ),
               array(
                    'name' => DB_PREFIX.'position',
                    'on' => DB_PREFIX.'position.id = '.DB_PREFIX.'user.position',
                    'columns' => array(
                        'z_name' => 'name'
                    ),
                    'type' => 'left'
                ),
            ),
            'need_page' => true,
        );
    
        if (isset($c_where['keyword']) && $c_where['keyword']) {
            $data['search_key'] = array(
                 DB_PREFIX.'user.name' => $c_where['keyword'],
            );
        }
    
        return $this->getAll($where, $data, $condition['page'], null, 'notification_feedback');
    }
    
 

    /**
     * 得到用户详情
     * @param unknown $id
     * @return multitype:number string Ambigous <boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown> |multitype:number string
     * @version YSQ
     */
    function getFeedbackDetails($id){
        if (!$id) {
            return array(
                'code' => '300',
                'message' => '请求参数不完整!'
            );
        }
         $data = array(
            'columns' => array(
                'id',
                'content',
                'timestamp',
                'reply_status',
            ),
            'join' =>array(
                array(
                    'name' => DB_PREFIX.'user',
                    'on' => DB_PREFIX.'user.id = '.DB_PREFIX.'notification_feedback.user_id',
                    'columns' => array(
                        'name',
                    ),
                    'type' => 'left'
                ),
               array(
                    'name' => DB_PREFIX.'position',
                    'on' => DB_PREFIX.'position.id = '.DB_PREFIX.'user.position',
                    'columns' => array(
                        'z_name' => 'name'
                    ),
                    'type' => 'left'
                ),
            ),
            'need_page' => true,
        );
        $list = $this->getAll(array(DB_PREFIX.'notification_feedback.id'=>$id),$data,null,null,'notification_feedback');
        if(!$list['total']){
            return array(
                'code' => 400,
                'message' => '查询失败',
            );
        }
//         var_dump($list['list']);exit;
        return array(
            'code' => 200,
            'info' => $list['list']['0'],
        );
    }
    
    /**
     * 回复评论
     * @return multitype:number string 
     * @version YSQ
     */
    function editFeedback(){
        if(!$_SESSION['role_nb_admin_id']){
            return array(
                'code' => '300',
                'message' => '请求参数不完整!'
            );
        }
        $admin_id = $_SESSION['role_nb_admin_id'];
        $content = isset($_POST['content']) && $_POST['content']? trim($_POST['content']):'';
        $id = isset($_POST['id']) && $_POST['id']? trim($_POST['id']):'';
        if(!$content && !$id){
            return array(
                'code' => '300',
                'message' => '请求参数不完整!'
            );
        }
        $set = array(
            'admin_id' => $admin_id,
            'reply' => $content,
//             'parent_id' => $id,
            'reply_status' => 1,
            'reply_time' => $this->getTime(),//回复时间
        );
        $row = $this->updateData($set,array('id'=>$id),'notification_feedback');
     
        if($row){
            return array(
                'code' => 200,
                'message' => '成功!',
            );
        }
        return array(
            'code' => 400,
            'message' => '新增失败!',
        );
    }
}