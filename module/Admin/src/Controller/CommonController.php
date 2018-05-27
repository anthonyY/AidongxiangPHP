<?php
namespace Admin\Controller;

use Admin\Controller\Upload\UploadfileApi;
use Admin\Controller\Upload\Uploadfile as Uploadfiles;
use Admin\Controller\Upload\Image;
use Admin\Controller\Table;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Captcha\Image as imageCaptcha;
use Api\Controller\CommonController as Api;

class CommonController extends Table
{
	/**
	 * 面包屑导航设置
	 *
	 * @var array(array('url'=>"","title"=>),array('url'=>'','title'=>''))
	 */
	protected $breadcrumb;
	
	public function __construct()
    {
        parent::__construct();
        $_SESSION['module'] = 'platform';
    }
    
    /**
     * 2014/09/11
     * 解析
     * region_info
     * 字段
     *
     * @author liujn
		 *
     * @param string $result
     *            数据库region_info
     *            JSON数据
     * @return string 省市区名字
		 *
     */
    public function getProvinceCityCountryName($region_info)
    {
        $region = json_decode($region_info);
        $result = '';
        if (isset($region[0]->region->id))
        {
            $result = $region[0]->region->name;
            $result .= ' ';
        }
        if (isset($region[1]->region->id))
        {
            $result .= $region[1]->region->name;
            $result .= ' ';
        }
        if (isset($region[2]->region->id))
        {
            $result .= $region[2]->region->name;
            $result .= ' ';
        }
        return $result;
    }
    
    
    /**
     * 上传文件处理
     *
     * @author liujun
     * @param integer $filetype
     *            1,为图片类;2,swf类;3,音频类;4,文本文件类;5,可执行文件类;
     *            默认为
     *            1图片类
     * @param integer $size
     *            设置上传最大文件的大小（与PHP配置文件有关）此项默认为：2M
     * @return array $array
     *         array('filename','path','size','mime','extension')
     */
    public function UploadSinglefile($filetype = 1, $size = 8192)
    {
        set_time_limit(0);
        $upload = new UploadFiles($path = UPLOAD_PATH, $size, $filetype, 'Ymd');
        $upload->uploadfile();
        $filename = $upload->getUploadFileInfo();
        $regionName = '';
        if($filetype == 4){
            $regionName = $filename['file']['name'];
        }
        $name = $upload->newfilename;
        $path = $upload->imgPath;
        $array = array('name' => $regionName, 'filename' => $name, 'path' => $path, 'timestamp' => date("Y-m-d H:i:s"));
        
        return $array;
    }
	
	/**
	 * 验证码生成
	 *
	 * @author liujun
	 */
	public function generateCaptchaAction()
	{
		$captcha = new imageCaptcha();
		$number = rand(1, 6);
		$language = __DIR__ . "/../../language/$number.ttf";
		$captcha->setFont($language); // 字体路径
		$captcha->setImgDir(APP_PATH.'/public/uploadfiles/tmp/'); // 验证码图片放置路径
		$captcha->setImgUrl(APP_PATH.'/public/uploadfiles/tmp/');
		$captcha->setWordlen(4);
		$captcha->setFontSize(30);
        $captcha->setLineNoiseLevel(3); // 随机线
        $captcha->setDotNoiseLevel(30); // 随机点
        $captcha->setExpiration(10); // 图片回收有效时间
        $captcha->setUseNumbers(false);//设置验证码生成类型，true 字母加数字，false 字母
		$captcha->generate(); // 生成验证码
		$_SESSION['captcha'] = $captcha->getWord();
		return '/uploadfiles/tmp/' . $captcha->getId() . $captcha->getSuffix(); // 图片路径
		die();
	}
	
	/**
	 * 前端接收表单文件域传过来文件 用于上传文件处理 4:3
	 *
	 * @author liujun
	 * @return string 用于模板页面JS处理
	 */
	public function getAdminFileAction()
	{
		if (isset($_FILES) && $_FILES['Filedata']['error'] == 0 && $this->check_file_type($_FILES['Filedata']['tmp_name']))
		{
			/* $ima_info = getimagesize($_FILES['Filedata']['tmp_name']);
			if ($ima_info[0] != $ima_info[1])
			{
				$path = '';
				$imgid = '';
				$error = '图片比例不正确，请上传200X200以下像素的正方形图片！';
			}
			else
			{ */
				$data = $this->uploadImageForController('Filedata');
				$path = ROOT_PATH . UPLOAD_PATH . $data['files'][0]['Filedata']['path'] . $data['files'][0]['Filedata']['filename'];
				$imgid = $data['files'][0]['Filedata']['id'];
				if (! $imgid)
				{
					$error = '上传失败，未知错误！';
				}
				else
				{
					$error = '';
				}
			/* } */

			$return = array('error' => $error, 'path' => $path, 'imgid' => $imgid);
			echo json_encode($return);
			// echo "{";
			// echo "error: '" . $error . "',\n";
			// echo "path: '" . $path . "',\n";
			// echo "imgid: '" . $imgid . "'\n";
			// echo "}";
			die();
		}
		else
		{
			$error = '文件类型不正确，或未选择上传图片！';
			$path = '';
			$image_id = '';
			echo "{";
			echo "error: '" . $error . "',\n";
			echo "path: '" . $path . "',\n";
			echo "imgid: '" . $image_id . "'\n";
			echo "}";
			die();
		}
		// die();
	}
	
	/**
	 * 检查图片文件类型
	 *
	 * @access public
	 * @param
	 *        	string filename 文件名（地址）
	 * @return string 空为验证失败
	 */
	public function check_file_type($filename)
	{
		$limit_ext_types = '|GIF|JPG|JEPG|PNG|';
		if ($filename)
		{
			$extname = strtolower(substr($filename, strrpos($filename, '.') + 1));
		}
		else
		{
			return '';
		}

		$str = $format = '';

		$file = @fopen($filename, 'rb');
		if ($file)
		{
			$str = @fread($file, 0x400); // 读取前 1024 个字节
			@fclose($file);
		}
		else
		{

			return '';
		}

		if ($format == '' && strlen($str) >= 2)
		{

			if (substr($str, 0, 3) == "\xFF\xD8\xFF")
			{
				$format = 'jpg';
			}
			elseif (substr($str, 0, 4) == 'GIF8' && $extname != 'txt')
			{
				$format = 'gif';
			}
			elseif (substr($str, 0, 8) == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A")
			{
				$format = 'png';
			}
		}

		if ($limit_ext_types && stristr($limit_ext_types, '|' . $format . '|') === false)
		{
			$format = '';
		}

		return $format;
	}
	
	/**
	 * 上传文件总入口
	 *
	 * @param $_FILES $file
	 * @param string $file_key
	 *        	post过来的key
	 * @return Ambigous <\Api\Controller\multitype:multitype:multitype:multitype:unknown, multitype:multitype:multitype:multitype:unknown multitype:string unknown >
	 * @version 2014-12-6 WZ
	 */
	public function uploadImageForController($file_key)
	{
		$this->file_key = $file_key;
		$data = array();
		if (! isset($_FILES[$this->file_key]))
		{
			return array(
					'ids' => array(), 'files' => array()
			);
		}
		if (is_array($_FILES[$this->file_key]['name']))
		{
			foreach ( $_FILES[$this->file_key]['name'] as $key => $value )
			{
				if (! $_FILES[$this->file_key]['error'][$key])
				{
					$source_file = array(
							$this->file_key => array(
									'name' => array($_FILES[$this->file_key]['name'][$key]
									),
									'type' => array($_FILES[$this->file_key]['type'][$key]
									),
									'tmp_name' => array($_FILES[$this->file_key]['tmp_name'][$key]
									),
									'error' => array($_FILES[$this->file_key]['error'][$key]
									),
									'size' => array($_FILES[$this->file_key]['size'][$key]))
					);
					$data[] = $this->checkFileMd5($source_file);
				}
			}
		}
		else
		{
			if (! $_FILES[$this->file_key]['error'])
			{
				$source_file = array(
						$this->file_key => array(
								'name' => array($_FILES[$this->file_key]['name']
								),
								'type' => array($_FILES[$this->file_key]['type']
								),
								'tmp_name' => array($_FILES[$this->file_key]['tmp_name']
								),
								'error' => array($_FILES[$this->file_key]['error']
								),
								'size' => array($_FILES[$this->file_key]['size']))
				);
				$data[] = $this->checkFileMd5($source_file);
			}
		}

		$files = $this->saveFileInfo($data);
		return $files;
	}

	/**
	 * 通过对图片的md5验证，查看图片是否存在，<br />
	 * 如果存在返回数据库中的图片信息，<br />
	 * 如果不存在，上传新图片，再返回图片信息<br />
	 *
	 * @param array $source_file
	 * @return array|Ambigous number string >
	 * @version 2014-12-6 WZ
	 */
	public function checkFileMd5($source_file)
	{
		if (is_array($source_file[$this->file_key]['tmp_name']))
		{
			if (isset($source_file[$this->file_key]['data'][0]))
			{
				$content = $source_file[$this->file_key]['data'][0];
			}
			else
			{
				$content = $this->getUrlImage($source_file[$this->file_key]['tmp_name'][0]);
				$source_file[$this->file_key]['data'][0] = $content;
			}
		}
		else
		{
			if (isset($source_file[$this->file_key]['data']))
			{
				$content = $source_file[$this->file_key]['data'];
			}
			else
			{
				$content = $this->getUrlImage($source_file[$this->file_key]['tmp_name']);
				$source_file[$this->file_key]['data'] = $content;
			}
		}
		$md5 = md5($content);
		$data = $this->getImageTable()->getOne(array('md5' => $md5
		));
		if ($data)
		{
			return ( array ) $data;
		}
		else
		{
			$data = $this->Uploadfile(LOCAL_SAVEPATH, true, 1, 2048, $source_file);
			return $data[0];
		}
	}
	
	/**
	 * 获取图片内容
	 *
	 * @param unknown $path
	 * @return mixed
	 * @version 2014-12-16 WZ
	 */
	public function getUrlImage($path)
	{
		if (preg_match('/http\:\/\//i', $path))
		{
			$cookie_file = tempnam('./temp', 'cookie');
			$url = $path;
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			$content = curl_exec($ch);
		}
		else
		{
			$content = file_get_contents($path);
		}

		return $content;
	}
	
	/**
	 * 上传文件处理
	 *
	 * @author liujun
	 * @param string $pash
	 *            要上传到的文件夹 默认为public 下的uploadfiles/年月命名的文件夹（此文件夹为大图文件夹）
	 * @param boolean $is_thumb
	 *            是否生成缩略图 默认为否false，true为是
	 * @param integer $filetype
	 *            1,为图片类;2,swf类;3,音频类;4,文本文件类;5,可执行文件类; 默认为 1图片类
	 * @param integer $size
	 *            设置上传最大文件的大小（与PHP配置文件有关）此项默认为：2M
	 * @return array $array array('filename','path','size','mime','extension')
	 */
	public function Uploadfile($filetype = 1, $size = 2048)
	{
		set_time_limit(0);
		$upload = new UploadFiles($pash = APP_PATH.'/'.UPLOAD_PATH, $size, $filetype, 'Ymd');
		$upload->uploadfile();
		$filename = $upload->getUploadFileInfo();
		$name = $upload->newfilename;
		$path = $upload->imgPath;
		$array = array('name' => '', 'filename' => $name, 'path' => $path, 'timestamp' => date("Y-m-d H:i:s"));

		return $array;
	}

	/**
	 * 结果保存到数据库
	 *
	 * @param unknown $data
	 * @return multitype:multitype:multitype:multitype:unknown multitype:string unknown
	 * @version 2014-12-6 WZ
	 */
	public function saveFileInfo($data)
	{
		$ids = array();
		$files = array();
		foreach ( $data as $key => $value )
		{
			if (! isset($value['id']) && isset($value['filename']) && isset($value['path']) && $value['filename'] && $value['path'])
			{
				$value['timestamp'] = $this->getTime();
				$id = $this->getImageTable()->insertData($value);
				$ids[] = $id;
				$files[] = array(
						$this->file_key => array(
								'id' => $id,
								'path' => $value['path'], 'filename' => $value['filename'])
				);
			}
			else
			{
				$this->getImageTable()->updateKey($value['id'], 1, 'count', 1);
				$ids[] = $value['id'];
				$files[] = array(
						$this->file_key => array(
								'id' => $value['id'],
								'path' => $value['path'], 'filename' => $value['filename'])
				);
			}
		}

		return array(
				'ids' => $ids, 'files' => $files
		);
	}
	
	/**
	 * 2014/08/08
	 *
	 * @author liujun
	 *         获到当前时间用于插入数据库里的timestamp字段
	 * @return string
	 */
	protected function getTime()
	{
		return date("Y-m-d H:i:s");
	}

	/**
	 * 前端接收表单文件域传过来文件 用于上传文件处理 4:3
	 *
	 * @author liujun
	 * @return string 用于模板页面JS处理
	 */
	public function getFileAction()
	{
		if(isset($_FILES) && $_FILES['file']['error'] == 0 && $this->check_file_type($_FILES['file']['tmp_name'])){

			$data = $this->uploadImageForController('file');
			$path = ROOT_PATH . UPLOAD_PATH . $data['files'][0]['file']['path'] . $data['files'][0]['file']['filename'];
			$imgid = $data['files'][0]['file']['id'];
			if(!$imgid){
				$error = '上传失败，未知错误！';
			}else{
				$error = '';
			}
			die("{\"jsonrpc\" : \"2.0\", \"result\" : null, \"id\" : \"$imgid\", \"exist\": 1}");
		}else{
			$error = '文件类型不正确，或未选择上传图片！';
			die("{\"jsonrpc\" : \"2.0\", \"error\" : {\"code\": 100, \"message\": \"$error\"}, \"id\" : \"0\"}");
		}
		die("{\"jsonrpc\" : \"2.0\", \"error\" : {\"code\": 100, \"message\": \"未选择文件\"}, \"id\" : \"0\"}");
	}

	/**
	 * 前端接收表单文件域传过来文件 用于上传文件处理 4:3
	 *
	 * @author liujun
	 * @return string 用于模板页面JS处理
	 */
	public function getAdminFileTwoAction()
	{

		if(isset($_FILES) && $_FILES['Filedata']['error'] == 0 /*&& $this->check_file_type($_FILES['File']['tmp_name'])*/){
			//$ima_info = getimagesize($_FILES['File']['tmp_name']);

			$data = $this->uploadImageForController('Filedata');
			$path = ROOT_PATH . UPLOAD_PATH . $data['files'][0]['Filedata']['path'] . $data['files'][0]['Filedata']['filename'];
			$imgid = $data['files'][0]['Filedata']['id'];
			if(!$imgid){
				$error = '上传失败，未知错误！';
			}else{
				$error = '';
				$url = "http://api.wwei.cn/dewwei.html?data=" . "http://www.kuaiyao.name" . $path . "&apikey=20160118172115";
				$json_data = file_get_contents($url);
				$data = json_decode($json_data);
				if(isset($data->status) && $data->status == 1){
					$text = $data->data->raw_text;
					$imgInfo = $this->generationQRcode($text);
					$imgid = $imgInfo['id'];
					$path = $imgInfo['path'];
				}else{
					$error = '二维码识别失败!';
				}
			}

			echo "{";
			echo "error: '" . $error . "',\n";
			echo "path: '" . $path . "',\n";
			echo "imgid: '" . $imgid . "'\n";
			echo "}";
			die();
		}else{
			$error = '文件类型不正确，或未选择上传图片！';
			$path = '';
			$image_id = '';
			echo "{";
			echo "error: '" . $error . "',\n";
			echo "path: '" . $path . "',\n";
			echo "imgid: '" . $image_id . "'\n";
			echo "}";
			die();
		}
		// die();
	}

	public function getAdminFileLimitAction()
    {
		if(isset($_FILES) && $_FILES['Filedata']['error'] == 0 && $this->check_file_type($_FILES['Filedata']['tmp_name'])){
			$ima_info = getimagesize($_FILES['Filedata']['tmp_name']);
			if($ima_info[0] != 750 || $ima_info[1] != 320){
				$path = '';
				$imgid = '';
				$error = '图片比例不正确，请上传750X320像素的图片！';
			}else{
				$data = $this->uploadImageForController('Filedata');
				$path = ROOT_PATH . UPLOAD_PATH . $data['files'][0]['Filedata']['path'] . $data['files'][0]['Filedata']['filename'];
				$imgid = $data['files'][0]['Filedata']['id'];
				if(!$imgid){
					$error = '上传失败，未知错误！';
				}else{
					$error = '';
				}
			}

			$return = array('error' => $error, 'path' => $path, 'imgid' => $imgid);
			echo json_encode($return);
			// echo "{";
			// echo "error: '" . $error . "',\n";
			// echo "path: '" . $path . "',\n";
			// echo "imgid: '" . $imgid . "'\n";
			// echo "}";
			die();
		}else{
			$error = '文件类型不正确，或未选择上传图片！';
			$path = '';
			$image_id = '';
			echo "{";
			echo "error: '" . $error . "',\n";
			echo "path: '" . $path . "',\n";
			echo "imgid: '" . $image_id . "'\n";
			echo "}";
			die();
		}
		// die();
    }

	public function checkSize($size)
	{
		if($size > 1 * 1024 * 1024){
			$this->showMessage("只能上传小于1M的图片");
		}
	}

	/**
	 * 错误提示信息输出
	 *
	 * @param string $message
	 *            提示信息
	 * @param integer $type
	 *            是否要后退一页
	 */
	public function showMessage($message, $type = true, $url = '')
	{
		$location = $type ? "history.back(-1);" : "location.href='" . $url . "'";
		echo "<script type='text/javascript'>alert('{$message}');{$location}</script>";
		die();
	}

    public function getRegionInfoArray($region_id)
    {
        $result = array(
            'region_info' => "[]",
            'province' => 0,
            'city' => 0,
            'county' => 0,
            'district' => 0
        );
        if (! $region_id)
        {
            return $result;
        }
        $count = 0;
        $region_array = array();
        $region_data = array();
        // 开始获取数据
		$region_model = $this->getRegionTable();
		$region_model->id = $region_id;
        while($region_info = $region_model->getDetails()) {
            $region_array[] = $region_id;
            $region_data[$region_id] = $region_info;
            $region_id = $region_info['parent_id'];
			$region_model->id = $region_id;
            if (1 == $region_info['parent_id']) { // 省级就退出
                break;
            }
            if (++ $count > 4) { // 防死循环
                break;
            }
        }
        if (! $region_array)
        {
            return $result;
        }
        $region_array = array_reverse($region_array);

		// 开始整理数据
        $item = array(
            0 => 'province',
            1 => 'city',
            2 => 'county',
            3 => 'district'
        );
        $region_list = array();
        foreach ($item as $k => $v) {
            if (isset($region_array[$k])) {
                $region_id = $region_array[$k];
                $region_item = $region_data[$region_id];
                $result[$v] = $region_id;
                $region_list[]['region'] = array(
                    'id' => $region_item->id,
                    'name' => $region_item->name,
                    'parent_id' => $region_item->parent_id,
                    'pinyin' => $region_item->pinyin
                );
            }
        }
        $result['region_info'] = $this->JSON($region_list);
        return $result;
    }

    /**
     * 2014/09/10
     * 订单号财务流水号生成
     *
     * @author liujun
     * @return integer $order_id
     */
    public function generate()
    {
        $code = date('YmdHis') . mt_rand(10, 99);

		return $code;
    }

	public function filterWords($words)
    {
        return addslashes(trim($words));
    }

	/**
     * @abstract计算剩下的日时分秒
     * @param string $begin_time 开始时间戳
     * @param string $end_time 结束时间戳
	 * @return multitype:number
     * @author linzhiwen
     * @version2016-09-13
     */
    function timediff($begin_time,$end_time,$rob_time)
    {
        if($begin_time < $end_time)
        {
            $starttime = $begin_time;
            $endtime = $end_time;
        }else{
            $starttime = $end_time;
            $endtime = $begin_time;
        }


		$timediff = $rob_time - ($endtime - $starttime);
        if($timediff > 0){
            //计算天数
            $days = intval($timediff/86400);
            //计算小时数
            $remain = $timediff%86400;
            $hours = intval($remain/3600);
            //计算分钟数
            $remain = $remain%3600;
            $mins = intval($remain/60);
            //计算秒数
            $secs = $remain%60;
            $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
            return $res;
        }else
        {
            return false;
        }

	}
	
    /**
     * 无限分类排序显示
     * Array $arr 排序的数组
	 *
     * @version 2016年9月19日
     * @author csx
     */
    function classify(array $data,$id=0,$level=1) {
        $subs = array(); // 子孙数组
        foreach($data as $v)
        {

			if($v['parent_id'] == $id) {
                $v['type_number'] = $level;
                $v['level'] = $level;
                $subs[] = $v; // 举例说找到array('id'=>1,'name'=>'安徽','parent'=>0),
                $subs = array_merge($subs,$this->classify($data,$v['id'],$level+1));
            }
        }
        return $subs;
    }

	/**
     * 格式化打印数组。
     *
     * @param array $data
     *@version 2016-9-21 csx
     */
    public function dump($expression)
    {
        echo '<pre>';
        var_dump($expression);
        echo '</pre>';
    }

    /*
     * $invariant 不需要转成字符串格式的字段名
     */
	public function getExcel($fileName,$headArr,$data,$invariant=array()){
        $date = date("Y_m_d",time());
        $fileName .= "_{$date}.csv";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'"');
        header('Cache-Control: max-age=0');
        $fp = fopen('php://output','w');
        foreach($headArr as $i => $v){
            // CSV用Excel打开时，不支持UTF-8编码，而支持GBK编码，一定要转换，否则乱码
            $head[$i] = iconv('utf-8', 'gbk', $v);
        }
        // 将数据通过fputcsv写到文件句柄
        fputcsv($fp, $head);
        foreach ($data as $key=>$value)
        {
            foreach ($value as $k=> $vl){
                if($invariant){
                    if(!in_array($k,$invariant)){
                        $vl = $vl."\t";
                    }
                }else{
                    $vl = $vl."\t";
                }
                $row[$k] = iconv('utf-8', 'gbk//TRANSLIT', $vl);
            }
            fputcsv($fp, $row);
        }

		fclose($fp);
        return true;
    }

	public function getApiController()
    {
        $controller = new Api();
        return $controller;
    }

	/**
	 * 登录判断
	 *
	 * @param string $action_list
	 *            权限列表
	 * @return boolean
	 */
	protected function checkLogin($action_list = '')
	{
		if(isset($_SESSION['admin_id']) && isset($_SESSION['admin_name'])){
		    $admin = $this->getAdminTable();
		    $admin->id = $_SESSION['admin_id'];
		    $admin_info = $admin->getDetails();
		    if($admin_info->status != 1 || $admin_info->delete != DELETE_FALSE){
                $_SESSION = array();
                $url = $this->url()->fromRoute('admin',['action'=>'login']);
                $this->showMessage('该帐号已被删除或禁用！',false,$url);
            }
			if(!$this->admin_priv($action_list) && $action_list !== 'admin_index_index'){
				echo "<script>alert('对不起，你无此操作权限！！');history.back(-1);</script>";
				exit();
			}
			return true;
		}else{
			$this->redirect()->toRoute('admin', array('action' => 'login'));
		}
	}

	/**
	 * 判断管理员对某一个操作是否有权限。
	 *
	 * 根据当前对应的action_code，然后再和用户session里面的action_list做匹配，以此来决定是否可以继续执行。
	 *
	 * @param string $priv_str
	 *            操作对应的priv_str
	 * @return true/false
	 */
    protected function admin_priv($priv_str)
    {
        if($_SESSION['action_list'] == 'all'){
            return true;
        }
        //		var_dump($_SESSION['action_list']);die;
        $arr = explode(',',$priv_str);
        foreach ($arr as $v){
            if(!in_array($v,explode('|',$_SESSION['action_list']))){
                return false;
            }else{
                return true;
            }
        }
    }

	/**
	 * 用户登出
	 *
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	protected function quit()
	{
		session_destroy();
		return $this->redirect()->toRoute('admin', array('action' => 'login'));
	}

	/**
	 * 设置模板跟菜单
	 *
	 * @param unknown $mainView
	 * @param string $controller
	 * @return \Zend\View\Model\ViewModel
	 */
	protected function setMenu($mainView)
	{
		if(!$this->breadcrumb){
			$this->breadcrumb = array(array('url' => '', 'title' => ''), array('url' => '', 'title' => ''));
		}
		$menuView = new ViewModel();
		$this->layout('layout/layout');
		$menuView->setTemplate('layout/menu');
		$menuView->addChild($mainView, 'main');
		return $menuView;
	}

	/**
	 * 格式化 region_info 字段（转为JSON，用于插入数据库）
	 *
	 * @author liujun
	 * @param integer $county
	 *            区域ID
	 * @param integer $city
	 *            城市ID
	 * @param integer $province
	 *            省份直辖市ID
	 * @return string region_info JSON数据
	 *
	 */
	protected function encode($county, $city, $province)
	{
		$region_info = array();
		if($province > 1){
			$res = $this->getRegionTable()->getOne(array('id' => $province));
			$province_info = array("id" => $res->id, "name" => $res->name, "parent_id" => 1, "pinyin" => $res->pinyin);
			$region_info[] = array("region" => $province_info);
		}
		if($city > 1){
			$res = $this->getRegionTable()->getOne(array('id' => $city));
			$city_info = array("id" => $res->id, "name" => $res->name, "parent_id" => $res->parent_id, "pinyin" => $res->pinyin);
			$region_info[] = array("region" => $city_info);
		}
		if($county > 1){
			$res = $this->getRegionTable()->getOne(array('id' => $county));
			$county_info = array("id" => $res->id, "name" => $res->name, "parent_id" => $res->parent_id, "pinyin" => $res->pinyin);
			$region_info[] = array("region" => $county_info);
		}
		return $this->JSON($region_info);
	}

	/**
	 * 将数组转换为JSON字符串（兼容中文）
	 *
	 * @param array $array
	 * @return string
	 * @access public
	 */
	protected function JSON($array)
	{
		$this->arrayRecursive($array, 'urlencode', true);
		$json = json_encode($array);
		return urldecode($json);
	}

	//打印excel

	/**
	 *
	 *
	 *
	 * 使用特定function对数组中所有元素做处理
	 *
	 * @param
	 *            string    &$array        要处理的字符串
	 * @param string $function
	 * @return boolean
	 * @access public
	 *
	 *
	 */
	protected function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
	{
		static $recursive_counter = 0;
		if(++$recursive_counter > 1000){
			die('possible deep recursion attack');
		}
		foreach($array as $key => $value){
			if(is_array($value)){
				$this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
			}else{
				$array[$key] = $function($value);
			}

			if($apply_to_keys_also && is_string($key)){
				$new_key = $function($key);
				if($new_key != $key){
					$array[$new_key] = $array[$key];
					unset($array[$key]);
				}
			}
		}
		$recursive_counter--;
	}

	//敏感词

	/**
	 * 解析 region_info 字段（转为数组，用于模板数据）
	 *
	 * @author liujun
	 * @param string $result
	 *            数据库region_info JSON数据
	 * @return array array('province'=>省信息数组,'city'=>市信息数组，'county'=>区信息数组)
	 * @version 2015/04/14 WZ 大改
	 */
	protected function decode($result)
	{
		$result_info = array();
		$result = json_decode($result, true);
		if(is_array($result)){
			foreach($result as $key => $value){
				$value = $value['region'];
				if(isset($value['parent_id'])){
					$value['parentId'] = $value['parent_id'];
					unset($value['parent_id']);
				}
				$result_info[]['region'] = $value;
			}
		}
		return $result_info;
	}
	
	/**
     * 返回json信息
     */
	public function ajaxReturn($status, $msg, $url=''){
        if($url){
            echo json_encode(['s'=>$status, 'd'=>$msg, 'url'=>$url]);
            die;
        }else{
            echo json_encode(['s'=>$status, 'd'=>$msg]);
            die;
        }
    }
}
