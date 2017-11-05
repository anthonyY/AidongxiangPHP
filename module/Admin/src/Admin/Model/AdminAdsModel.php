<?php
namespace Admin\Model;

use Zend\Db\Sql\Where;
use Api\Model\CommonModel;

class AdminAdsModel extends CommonModel
{

    protected $table = "ads";
    
    public function getRelationList($condition)
    {
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'ads.delete', 0)->notEqualTo(DB_PREFIX . 'ads.plate', 3);
        $c_where = $condition['where'];
        if (isset($c_where['plate']) && $c_where['plate']) {
            $where->equalTo(DB_PREFIX . 'ads.plate', $c_where['plate']);
        }
        
        if (isset($c_where['status']) && $c_where['status']) {
            $where->equalTo(DB_PREFIX . 'ads.status', $c_where['status']);
        }
        
        if (isset($c_where['effective']) && $c_where['effective']) {
            $date = $this->getTime();
            $where->lessThanOrEqualTo(DB_PREFIX . 'ads.start_time', $date);
            $where->greaterThanOrEqualTo(DB_PREFIX . 'ads.end_time', $date);
        }
        
        $data = array(
            'columns' => array(
                '*'
            ),
            'join' => array(
//                 array(
//                     'name' => DB_PREFIX . 'ads_material',
//                     'on' => DB_PREFIX . 'ads_material.id = ' . DB_PREFIX . 'ads.ads_material_id',
//                     'columns' => array(
//                         'image',
//                     )
//                 ),
//                 array(
//                     'name' => DB_PREFIX . 'ads_position',
//                     'on' => DB_PREFIX . 'ads_position.id = ' . DB_PREFIX . 'ads.ads_position_id',
//                     'columns' => array(
//                         'name',
//                     )
//                 )
            ),
            'order' => array(
//                 'id asc'
//                 DB_PREFIX . 'ads.ads_position_id' => 'asc',
//                 DB_PREFIX . 'ads.sort' => 'asc',
            ),
            'need_page' => true,
        );
        return $this->getAll($where, $data, isset($condition['page']) ? $condition['page'] : 1, 0, 'ads');
    }
    
    public function getTodayAdsList($condition)
    {
        $where = new Where();
        $where->equalTo(DB_PREFIX . 'ads.delete', 0)->equalTo(DB_PREFIX . 'ads.plate', 3);
        $c_where = $condition['where'];
        if (isset($c_where['status']) && $c_where['status']) {
            $where->equalTo(DB_PREFIX . 'ads.status', $c_where['status']);
        }
    
        $data = array(
            'columns' => array(
                '*'
            ),
            'need_page' => true,
        );
        return $this->getAll($where, $data, isset($condition['page']) ? $condition['page'] : 1, 0, 'ads');
    }
    
    /**
     * 异步查找音频，视频，音频包，视频包的列表
     * @version YSQ
     */
    function getFindList(){
        $type = isset($_POST['type']) && $_POST['type']? (integer) $_POST['type']: 0;
        $id = isset($_POST['id']) && $_POST['id']? (integer) $_POST['id']: 0;
//         var_dump($type);exit;
        if(!$type || !in_array($type, array('2','3','4','5'))){
            return array(
                'status' => STATUS_UNKNOWN, 'msg' => '请求数据不完整！'
            );
        }
        if($type ==2 || $type == 3){
            $where = array(
                'delete' => 0,
                'type' => $type-1,
            );
            $table = 'audio';
        }else{
            $where = array(
                'delete' => 0,
                'type' => $type-1,
            );
            $table = 'courses';
        }
        $data = array(
            'columns'=>array('id','title'),
            'order' => array('id asc')
        );
        $list = $this->fetchAll($where,$data,$table);
        if(!$list){
            return array(
                'status' => STATUS_UNKNOWN, 'msg' => '查找数据失败或没有数据！'
            );
        }
        $str = '';
        if($id){
            foreach ($list as $k=>$v){
                $str.= '<option value="'.$v['id'].'" '.($id==$v['id']?'selected="selected"':'').'>'.$v['title'].'</option>';
            }
        }else{
            foreach ($list as $k=>$v){
    //             $info && $info['type'] == $k?'selected="selected"':''
                $str.= '<option value="'.$v['id'].'" >'.$v['title'].'</option>';
            }
        }
        return array(
                'status' => STATUS_SUCCESS, 'msg' => '成功！','content' => $str,
        );
    }
    
    /**
     * 新增广告
     * @version YSQ
     */
    function addRelationInfo($params){
        $plate = isset($params['plate']) && $params['plate']? trim($params['plate']) : '';
        $start_time = isset($params['start_time']) && $params['start_time']? trim($params['start_time']) : '';
        $end_time = isset($params['end_time']) && $params['end_time']? trim($params['end_time']) : '';
        $type = isset($params['type']) && $params['type']? trim($params['type']) : '';
        $content = isset($params['content']) && $params['content']? trim($params['content']) : '';
        $title = isset($params['title']) && $params['title']? trim($params['title']) : '';
        $image = isset($params['image']) && $params['image']? trim($params['image']) : '';
        $audio_id = isset($params['audio_id']) && $params['audio_id']? trim($params['audio_id']) : '';
        $link = isset($params['link']) && $params['link']? trim($params['link']) : '';
        $sort = isset($params['sort']) && $params['sort']? trim($params['sort']) : '';
        if(!($plate && $start_time && $end_time && $image && in_array($type, array(1,2,3,4,5,6)))){
             return array('status' => STATUS_UNKNOWN, 'msg' => '请求数据不完整！');
        }
        if($type == 1){
            if(!$content && $title){
                return array('status' => STATUS_UNKNOWN, 'msg' => '请求数据不完整！');
            }
        }elseif(2<=$type && $type<= 5){
            if(!$audio_id){
                return array('status' => STATUS_UNKNOWN, 'msg' => '请求数据不完整！');
            }
        }elseif($type == 6){
            if(!$link){
                return array('status' => STATUS_UNKNOWN, 'msg' => '请求数据不完整！');
            }
        }
        $data = array(
            'plate'=>$plate,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
            'sort'=>$sort,
            'image'=>$image,
            'type'=>$type,
            'title'=>$title,
            'content'=>$content,
            'audio_id'=>$audio_id,
            'link'=>$link,
            'timestamp' => $this->getTime(),
        );
        if($_POST['id']){
            $id = $this->updateData($data,array('id' => $_POST['id']),'ads');
        }else{
            $id = $this->insertData($data,'ads');
        }
        if($id){
            return array('status' => STATUS_SUCCESS, 'msg' => '新增成功！');
        }
        return array('status' => STATUS_UNKNOWN, 'msg' => '新增失败！');
    }
    
    /**
     * 编辑轮播图
     * @param unknown $params
     * @return multitype:string 
     * @version YSQ
     */
    function editRelationInfo($params){
        $id = isset($params['id']) && $params['id']? trim($params['id']) : '';
        $plate = isset($params['plate']) && $params['plate']? trim($params['plate']) : '';
        $start_time = isset($params['start_time']) && $params['start_time']? trim($params['start_time']) : '';
        $end_time = isset($params['end_time']) && $params['end_time']? trim($params['end_time']) : '';
        $type = isset($params['type']) && $params['type']? trim($params['type']) : '';
        $content = isset($params['content']) && $params['content']? trim($params['content']) : '';
        $image = isset($params['image']) && $params['image']? trim($params['image']) : '';
        $audio_id = isset($params['audio_id']) && $params['audio_id']? trim($params['audio_id']) : '';
        $link = isset($params['link']) && $params['link']? trim($params['link']) : '';
        $sort = isset($params['sort']) && $params['sort']? trim($params['sort']) : '';
        $info = $this->getOne(array('id' => $id), null, 'ads');
        $set = array();
        $set['sort'] = $sort;
        if($plate && $plate != $info['plate']){
            $set['plate'] = $plate;
        }
        if($start_time && $start_time != $info['start_time']){
            $set['start_time'] = $start_time;
        }
        if($end_time && $end_time != $info['end_time']){
            $set['end_time'] = $end_time;
        }
        if($type && $type != $info['type']){
            $set['type'] = $type;
        }
        if($image && $image != $info['image']){
            $set['image'] = $image;
        }
        if($type == 1){
           if($content && $content!=$info['content']){
               $set['content'] = $content;
           }
           if($_POST['title'] && $_POST['title'] != $info['content']){
               $set['title'] = $_POST['title'];
           }
           if($type && $type != $info['type']){
               $set['link'] = '';
               $set['audio_id'] = '';
           }
        }elseif(2<=$type && $type <= 5){
             if($audio_id && $audio_id!=$info['audio_id']){
               $set['audio_id'] = $audio_id;
           }
           if($type && $type != $info['type']){
               $set['link'] = '';
               $set['content'] = '';
           }
        }elseif($type == 6){
          if($link && $link!=$info['link']){
               $set['link'] = $link;
           }
           if($type && $type != $info['type']){
               $set['audio_id'] = '';
               $set['content'] = '';
           }
        }
       if(!$set){
           return array('status' => STATUS_SUCCESS, 'msg' => '修改成功！');
       }
        $row = $this->updateData($set, array('id'=>$id),'ads');
        return array('status' => STATUS_SUCCESS, 'msg' => '修改成功！');
//         if($row){
            
//         }
//         return array('status' => STATUS_UNKNOWN, 'msg' => '修改失败！');
    }
    
//     public function getMaterialList($condition)
//     {
//         $where = new Where();
//         $where->equalTo('delete', 0);
    
//         $data = array(
//             'order' => array(
//                 'id' => 'DESC'
//             ),
//             'need_page' => true,
//         );
//         return $this->getAll($where, $data, $condition['page'], 0, 'ads_material');
//     }
    
    public function updateRelation($id, $set) {
        $id = (int) $id;
        $where = array('id' => $id);
        $update = false;
        if ($id && $set) {
            $update = $this->updateData($set, $where, 'ads');
        }
        if ($update) {
            return array('status' => STATUS_SUCCESS, 'msg' => '操作成功');
        }
        return array('status' => STATUS_NOT_UPDATE, 'msg' => '更新失败');
    }
    
//     public function updateMaterial($id, $set) {
//         $id = (int) $id;
//         $where = array('id' => $id);
//         $update = false;
//         if ($id && $set) {
//             $update = $this->updateData($set, $where, 'ads_material');
//         }
//         if ($update) {
//             return array('status' => STATUS_SUCCESS, 'msg' => '操作成功');
//         }
//         return array('status' => STATUS_NOT_UPDATE, 'msg' => '更新失败');
//     }
    
// //     public function getAdsPosition() {
// //         $where = array(
// //             'delete' => 0,
// //         );
// //         $data = array(
// //             'order' => array(
// //                 'sort' => 'asc',
// //                 'id' => 'asc'
// //             )
// //         );
// //         return $this->getDataByIn($where, $data, 'ads_position');
// //     }
    
//     /**
//      * 保存关系
//      *
//      * @param unknown $condition
//      * @return boolean
//      * @version 2016-12-6 WZ
//      */
//     function saveAdsRelation($condition) {
//         $id = isset($condition['id']) && $condition['id'] ? (int) $condition['id'] : 0;
//         if (isset($condition['id'])) {
//             unset($condition['id']);
//         }
//         $data = array();
//         $key = array('ads_position_id','ads_material_id','start_time','end_time','sort');
//         foreach($key as $value) {
//             $data[$value] = $condition[$value];
//         }
//         $data['admin_id'] = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0;
//         if ($id) {
//             $this->updateData($data, array('id' => $id), 'ads');
//         }
//         else {
//             $data['status'] = 1;
//             $id = $this->insertData($data, 'ads');
//         }
//         return $id;
//     }
    
//     /**
//      * 保存物料
//      * 
//      * @param unknown $condition
//      * @return boolean
//      * @version 2016-12-6 WZ
//      */
//     function saveAdsMaterial($condition) {
//         $id = isset($condition['id']) && $condition['id'] ? (int) $condition['id'] : 0;
//         if (isset($condition['id'])) {
//             unset($condition['id']);
//         }
//         $data = array();
//         $key = array('name','image','url');
//         foreach($key as $value) {
//             $data[$value] = $condition[$value];
//         }
//         $data['admin_id'] = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0;
//         if ($id) {
//             $this->updateData($data, array('id' => $id), 'ads_material');
//         }
//         else {
//             $id = $this->insertData($data, 'ads_material');
//         }
//         return $id;
//     }
}
