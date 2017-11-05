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

class AdminCommentModel extends CommonModel
{

    protected $table = "comment";

    /**
     * 评论列表
     * @param unknown $condition
     * @version 2016-12-3 WZ
     */
    public function getReviewList($condition)
    {
        $where = new Where();
//         $where->equalTo(DB_PREFIX.'comment.delete', DELETE_FALSE);
        $where->equalTo(DB_PREFIX.'comment.deep', 1);
        $c_where = $condition['where'];
        if (isset($c_where['teacher_id']) && $c_where['teacher_id']) {
            $where->equalTo(DB_PREFIX.'teacher.id', $c_where['teacher_id']);
        }
        if (isset($c_where['type']) && $c_where['type']) {
            $where->equalTo(DB_PREFIX.'comment.type', $c_where['type']);
            if (isset($c_where['id']) && $c_where['id']) {
                if($c_where['type']==1 || $c_where['type']==2){
                    $where->equalTo(DB_PREFIX.'comment.audio_id', $c_where['id']);
                }elseif($c_where['type']==3 || $c_where['type']==4){
                    $where->equalTo(DB_PREFIX.'comment.courses_id', $c_where['id']);
                }
            }
        }
//         var_dump($where,$c_where);exit;
        
        $data = array(
            'columns' => array(
                'content',
                'timestamp',
                'type',
                'praise_num',
                'comment_num',
                'delete',
                'is_top',
                'id'
               
//                 'id',
//                 'content',
//                 'timestamp',
//                 'shop_id',
            ),
            'join' =>array(
                array(
                    'name' => DB_PREFIX.'user',
                    'on' => DB_PREFIX.'user.id = '.DB_PREFIX.'comment.user_id',
                    'columns' => array(
                        'name',
                        'img_path',
                        'head_icon'
                    ),
                    'type' => 'left'
                ),
               array(
                    'name' => DB_PREFIX.'audio',
                    'on' => DB_PREFIX.'audio.id = '.DB_PREFIX.'comment.audio_id',
                    'columns' => array(
                        'p_name' => 'title'
                    ),
                    'type' => 'left'
                ),
                array(
                    'name' => DB_PREFIX.'courses',
                    'on' => DB_PREFIX.'courses.id = '.DB_PREFIX.'comment.courses_id',
                    'columns' => array(
                        'c_name' => 'title'
                    ),
                    'type' => 'left'
                ),
                array(
                    'name' => DB_PREFIX.'teacher',
                     'on' =>new Expression('`nb_teacher`.`id`=`nb_audio`.`teacher_id` or `nb_teacher`.`id`=`nb_courses`.`teacher_ids`'),
//                     'on' => DB_PREFIX.'teacher.id = '.DB_PREFIX.'audio.teacher_id',
                    'columns' => array(
                        't_name' => 'name',
                    ),
                    'type' => 'left'
                ),
            ),
            'need_page' => true,
        );

        if (isset($c_where['keyword']) && $c_where['keyword']) {
            $data['search_key'] = array(
                 DB_PREFIX.'audio.title' => $c_where['keyword'],
                DB_PREFIX.'courses.title' => $c_where['keyword']
            );
        }
    
        return $this->getAll($where, $data, $condition['page'], null, 'comment');
    }
    
    /**
     * 今天评论数,今天评论用户数,今天官方回复数
     */
    function getTodayCommentCount(){
        $where = new Where();
        $where -> greaterThanOrEqualTo('timestamp', date("Y-m-d 00:00:00"));
        $count = $this->countData($where,'comment');
        $user_count = $this->countData($where,'comment',null,'user_id');
        $where->equalTo('user_type', 2);
        $admin_count =  $this->countData($where,'comment');
        return array(
            'count' => $count?$count:0,
            'user_count' => $user_count?$user_count:0,
            'admin_count' => $admin_count?$admin_count:0,
        );
    }
    
    /**
     * 本月评论数
     */
    function getThisMonthCommentCount(){
        $where = new Where();
        $where -> greaterThanOrEqualTo('timestamp', date("Y-m-01 00:00:00"));
        $count = $this->countData($where,'comment');
        $where->equalTo('user_type', 2);
        $admin_count =  $this->countData($where,'comment');
        return array(
            'count' => $count?$count:0,
            'admin_count' => $admin_count?$admin_count:0,
        );
    }
    
    /**
     * 得到老师列表
     */
    function getTeacherList(){
        $where = array(
            'delete' => 0,
        );
        $list = $this->fetchAll($where,array('order'=>array('id asc')),'teacher');
        return $list?$list:array();
    }
    
    /**
     * 隐藏/显示(ajax)
     * @param array $params
     * @return array
     * @version YSQ
     */
    public  function setCommentDelete($params){
        $id = isset($params['id']) ? (int) $params['id'] : 0; // 用户ID
        $status = isset($params['status']) ? (int) $params['status'] : 0; // 1启用；2禁用；
        if (!$id || !in_array($status,array(1,0))) {
            return array(
                'code' => '300',
                'message' => '请求参数不正确!'
            );
        }
        $user_info = $this->getOne(array(
            'id' => $id
        ), array(
            'delete',
            'is_top'
        ), 'comment');
        if (in_array($user_info['delete'],array(1,0))) {
            if ($user_info['delete'] == $status) {
                return array(
                    'code' => '400',
                    'message' => '错误操作!'
                );
            }
            $row = $this->updateData(array(
                'delete' => $status
            ), array(
                'id' => $id
            ), 'comment');
            if ($row) {
                return array(
                    'code' => '200',
                    'message' => '操作成功!',
                    'is_top' => $user_info['is_top'],
                );
            }
        }
        return array(
            'code' => '400',
            'message' => '未知错误!'
        );
    }
    
    /**
     * 取消置顶/置顶(ajax)
     * @param array $params
     * @return array
     * @version YSQ
     */
    public  function setCommentTop($params){
        $id = isset($params['id']) ? (int) $params['id'] : 0; // 用户ID
        $status = isset($params['status']) ? (int) $params['status'] : 0; // 1启用；2禁用；
        if (!$id || !in_array($status,array(1,2))) {
            return array(
                'code' => '300',
                'message' => '请求参数不正确!'
            );
        }
        $user_info = $this->getOne(array(
            'id' => $id
        ), array(
            'is_top',
            'delete'
        ), 'comment');
        if (in_array($user_info['is_top'],array(1,2))) {
            if ($user_info['is_top'] == $status) {
                return array(
                    'code' => '400',
                    'message' => '错误操作!'
                );
            }
            $row = $this->updateData(array(
                'is_top' => $status,
                'timestamp_update' => $this->getTime(),
            ), array(
                'id' => $id
            ), 'comment');
            if ($row) {
                return array(
                    'code' => '200',
                    'message' => '操作成功!',
                    'status' => $user_info['delete'],
                );
            }
        }
        return array(
            'code' => '400',
            'message' => '未知错误!'
        );
    }
    
//     /**
//      * 异步删除评论
//      */
//     function deleteReview($params){
//         $arr = array('shop_id','id');
//         foreach ($arr as $v){
//             if(isset($params[$v])){
//                 $array[$v] =$params[$v];
//             }else{
//                 return  array(
//                     'status' =>'1',
//                     'msg' => '请求参数不完整！',
//                 );
//             }
//         }
//         $where = array('shop_id'=>$array['shop_id'],'id'=>$array['id']);
//         $row = $this->updateData(array('delete'=>1), $where,'comment');
//         if(!$row){
//             return  array(
//                 'status' =>'2',
//                 'msg' => '删除评论失败！',
//             );
//         }
//         return  array(
//             'status' =>'0',
//             'msg' => '成功！',
//         );
//     }
    /**
     * 得到用户详情
     * @param unknown $id
     * @return multitype:number string Ambigous <boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown> |multitype:number string
     * @version YSQ
     */
    function getCommentDetails($id){
        if (!$id) {
            return array(
                'code' => '300',
                'message' => '请求参数不完整!'
            );
        }
        $data = array(
            'columns' => array(
                'content',
                'timestamp',
                'type',
                'praise_num',
                'comment_num',
                'delete',
                'is_top',
                'id',
            ),
            'join' =>array(
                array(
                    'name' => DB_PREFIX.'user',
                    'on' => DB_PREFIX.'user.id = '.DB_PREFIX.'comment.user_id',
                    'columns' => array(
                        'name',
                        'img_path',
                        'head_icon'
                    ),
                    'type' => 'left'
                ),
                array(
                    'name' => DB_PREFIX.'audio',
                    'on' => DB_PREFIX.'audio.id = '.DB_PREFIX.'comment.audio_id',
                    'columns' => array(
                        'p_name' => 'title'
                    ),
                    'type' => 'left'
                ),
                array(
                    'name' => DB_PREFIX.'courses',
                    'on' => DB_PREFIX.'courses.id = '.DB_PREFIX.'comment.courses_id',
                    'columns' => array(
                        'c_name' => 'title'
                    ),
                    'type' => 'left'
                ),
                array(
                    'name' => DB_PREFIX.'teacher',
                    'on' =>new Expression('`nb_teacher`.`id`=`nb_comment`.`audio_id` or `nb_teacher`.`id`=`nb_comment`.`courses_id`'),
                    //                     'on' => DB_PREFIX.'teacher.id = '.DB_PREFIX.'audio.teacher_id',
                    'columns' => array(
                        't_name' => 'name',
                    ),
                    'type' => 'left'
                ),
            ),
           // 'need_page' => true,
        );
        $list = $this->getAll(array(DB_PREFIX.'comment.id'=>$id),$data,null,null,'comment');
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
     * 得到页码数
     * @param int $page
     * @param int $total
     * @return array
     * @version YSQ
     */
    public function getPageSum($page, $total2, $limit=0){
        $limit = $limit == 0 ? PAGE_NUMBER : $limit;
        $total = ceil($total2/$limit);
        $page_info= array(
            'total' => $total,
            'page' => $page,
            'page_1' => $page-1,
            'page_2' => $page+1,
            'previous' => $page<=1?true:false,
            'net' => $page>=$total?true:false,
        );
    
        //         $page_info['pagesInRange'] = $this->getPageSum($page, $total);
        if($page <= 4){
            for($i=1;$i<=8;$i++){
                $pagesInRange[] = $i;
                if($i>=$total){
                    break;
                }
            }
        }elseif($page>($total-4)){
            if(($total-8)<=0){
                for($i=1;$i<=$total;$i++){
                    $pagesInRange[] = $i;
                }
            }else{
                for($i=$total-8;$i<=$total;$i++){
                    $pagesInRange[] = $i;
                    if($i>=$total){
                        break;
                    }
                }
            }
        }else{
            for($i=$page-3;$i<=$page+4;$i++){
                $pagesInRange[] = $i;
                if($i>=$total){
                    break;
                }
            }
        }
        $page_info['pagesInRange'] = $pagesInRange;
        return $page_info;
    }
    
    /**
     *  得到评论的回复列表
     * @param unknown $condition
     * @version YSQ
     */
    function getCommentReplyList($condition){
        $s_where = $condition['where'];
        if(!$s_where['id']){
            return array(
                'code' => '300',
                'message' => '请求参数不完整!'
            );
        }
        $where = new Where();
        $where->equalTo(DB_PREFIX.'comment.parent_id', $s_where['id']);
        $where->equalTo(DB_PREFIX.'comment.deep', 2);
//         if($s_where['type']){
//             $where->equalTo(DB_PREFIX.'audio.type', $s_where['type']);
//         }
          $data = array(
            'columns' => array(
                'content',
                'timestamp',
                'delete',
                'id',
                'user_type',
                'user_id',
                'praise_num'
            ),
            'join' =>array(
//                 array(
//                     'name' => DB_PREFIX.'user',
//                     'on' => DB_PREFIX.'user.id = '.DB_PREFIX.'comment.user_id',
//                     'columns' => array(
//                         'name',
//                         'img_path',
//                         'head_icon'
//                     ),
//                     'type' => 'left'
//                 ),
            ),
            'need_page' => true,
        );
        $list = $this->getAll($where,$data,$condition['page'],$condition['limit'],'comment');
        foreach ($list['list'] as $n){
            if($n['user_type'] == 1)//用户
            {
                $user = $this->getOne(array('id'=>$n['user_id']),array('user_name' => "name",'user_img' => "img_id",'user_head_icon' => "head_icon",'img_path'),'user');
                if($user['img_path']){
                    $n['img_path'] = $user['img_path'];
                }else{
                    $n['img_path'] = "";
                    $n['head_icon'] = $user['user_head_icon'];
                }
                
                $n['name'] = $user ? $user['user_name'] : "";
                 
            }
            else //管理员
            {
                $admin = $this->getOne(array('id'=>$n['user_id']),array('name','image'),'admin');
                if($admin)
                {
                    //$image = $this->getOne(array('id'=>$admin['image']),array('id','path','filename'),'image');
                    $n['img_path'] = $admin['image'] ?  $admin['image'] : "";
                }
                else
                {
                    $n['head_icon'] = "";
                }
                $n['name'] = $admin ? $admin['name'] : "";
            }
        }
        return $list;
    }
    
    /**
     * 回复评论
     * @return multitype:number string 
     * @version YSQ
     */
    function editComment(){
//         var_dump($_SESSION);exit;
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
            'user_id' => $admin_id,
            'content' => $content,
            'parent_id' => $id,
            'deep' => 2,
            'user_type' => 2,
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime(),
        );
        $row = $this->insertData($set,'comment');
        $this->updateKey($id, 1, 'comment_num', 1,'comment');
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