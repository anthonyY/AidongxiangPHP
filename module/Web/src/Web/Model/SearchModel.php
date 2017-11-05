<?php
namespace Web\Model;

use Zend\Db\Sql\Where;
class SearchModel extends CommonModel
{
    protected $table = '';

    //获取搜索关键词
    public function getSearch(){
        $word = $this->getCache('SensitiveWords/search');
        $data = unserialize($word[0]);
        $search_word = array();
        if($data){
            $search_word = array(
                'word' => $data['search_word'],
                'content' => $data['content'] ? explode('|',$data['content']) : array(),
            );
        }
        return $search_word;
    }

    //获取搜索结果
//    public function searchResult($word){
//        $teacher_list = $this->getTacherList($word,3);
//        $audio_list = $this->getAudioList($word,3,1);
//        $video_list = $this->getAudioList($word,3,2);
//        return array(
//            'teacher' => $teacher_list ? $teacher_list : array(),
//            'audio_list' => $audio_list ? $audio_list : array(),
//            'video_list' => $video_list ? $video_list : array(),
//        );
//    }

    /**
     * @param $word
     * @return \Api\Model\Ambigous
     */
    public function getTacherList($word,$li)
    {
        $where = new Where();
        $where->equalTo('delete', 0);
        $where->equalTo('status', 1);
        $where->like('name', '%' . $word . '%');
        $data = array(
            'columns' => array('id', 'name', 'signature', 'head_icon', 'play_num'),
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'image',
                    'on' => DB_PREFIX . 'teacher.head_icon = ' . DB_PREFIX . 'image.id',
                    'columns' => array(
                        'img_id' => "id",
                        'img_path' => "path",
                        'img_filename' => "filename",
                    ),
                    'type' => 'left'
                ),
            ),
            'limit' => $li,
        );
        $teacher_list = $this->fetchAll($where, $data, 'teacher');
        return $teacher_list;
    }

    /**
     * @param $word
     * @return \Api\Model\Ambigous
     */
    /*public function getAudioList($word,$li,$type)
    {
        $where = new Where();
        $where->equalTo(DB_PREFIX .'audio.delete', 0);
        $where->equalTo(DB_PREFIX .'audio.status', 1);
        $where->equalTo(DB_PREFIX .'audio.type', $type);
        $where->like(DB_PREFIX .'audio.title', '%' . $word . '%');
        $data = array(
            'columns' => array('id', 'title' , 'audio_length' ,  'teacher_id' , 'type' , 'image' , 'price' , 'original_price' , 'pay_type' , 'sell_type'),
            'join' => array(
                array(
                    'name' => DB_PREFIX . 'image',
                    'on' => DB_PREFIX . 'audio.image = ' . DB_PREFIX . 'image.id',
                    'columns' => array(
                        'img_id' => "id",
                        'img_path' => "path",
                        'img_filename' => "filename",
                    ),
                    'type' => 'left'
                ),
                array(
                    'name' => DB_PREFIX . 'teacher',
                    'on' => DB_PREFIX . 'teacher.id = ' . DB_PREFIX . 'audio.teacher_id',
                    'columns' => array(
                        'teacher_name' => "name",
                        'teacher_id' => "id",
                    ),
                    'type' => 'left'
                ),
            ),
            'limit' => $li,
        );
        $audio_list = $this->fetchAll($where, $data, 'audio');
        if($audio_list){
            foreach($audio_list as $v){
                $watch_record = $this->getOne(array('user_id' => $_SESSION['user_id'],'audio_id' => $v['id']),array('id','time'),'watch_record');
                if(strpos($v['audio_length'],"时")){
                    $str = preg_replace('/([\d]+)时([\d]+)分([\d]+)秒/', '$1:$2:$3', $v['audio_length']);
                }else{
                    $str = preg_replace('/([\d]+)分([\d]+)秒/', '00:$1:$2', $v['audio_length']);
                }
                $parsed = date_parse($str);
                $v['audio_length'] = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
                $v['audio_length'] = $v['audio_length'] ? round($watch_record['time']/$v['audio_length']*100, 0). "%"  : "";
            }
        }
        return $audio_list;
    }*/
}