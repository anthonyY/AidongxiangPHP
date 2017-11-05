<?php
namespace Admin\Model;

use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Api\Model\CommonModel;
class OtherModel extends CommonModel{
    protected $table = 'e_position';

    public function positionInfo($params)
    {
        $where = new Where();
        $where -> equalTo('school_id', $params['id']);
        $school=$this->getOne(array('id' => $params['id']),array("region_id"),'e_school');
        $region = $this->getOne(array('id' => $school['region_id']),array("name"), 'e_region');
        $data = array('need_page'=>true,'order' => array('id' => 'ASC'));
        $res = $this->getAll($where, $data, $params['page'], 4,'e_position');
        $list['region'] = $region['name'];
        $list['list'] = $res;
        $list['id'] = $params['id'];
        $list['page'] = $params['page'];
        $list['where'] = $where;
        return $list;
    }

    public function uploadImage($params)
    {
        $list = $this->uploadImageForController($params);
        $path =  ROOT_PATH.UPLOAD_PATH . $list['files'][0]['Filedata']['path'] . @$list['files'][0]['Filedata']['filename'];
        $path1 = $list['files'][0]['Filedata']['path'] . @$list['files'][0]['Filedata']['filename'];
        $imgid = $list['files'][0]['Filedata']['id'];
        return array('path' => $path, 'imgid' => $imgid,'path1' => $path1);
    }
    
    public function phoneLoad()
    {
        $list = $this->mobileUpload1();
        return $list;
    }

}
