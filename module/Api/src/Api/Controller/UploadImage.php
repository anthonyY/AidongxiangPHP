<?php
namespace Api\Controller;

use Core\System\UploadfileApi;
use Core\System\Image;

/**
 * 上传图片
 *
 * @author WZ
 *
 */
class UploadImage extends CommonController
{
    public $file_key = 'file';

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();

        $request->action = $request->action ? $request->action : 1;
        if(!in_array($request->action,[1,2,3]))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $data = array();
        if (isset($_FILES) && $_FILES)
        {
            if (1 == $request->action)
            {
                // 上传图片
                $files = $this->uploadImageForController($this->file_key);
            }
            elseif (2 == $request->action)
            {
                // 上传视频(本地)
                $data = $this->Uploadfile(LOCAL_SAVEPATH, false, 3,50*1024);
                $files = $this->saveFileInfo($data);
            }
            elseif(3 == $request->action)
            {
                //对象存储（腾讯云）
                $files = $this->uploadFilesByCos($this->file_key);
                if(isset($files['s']) && $files['s'])
                {
                    $response->status = $files['s'];
                    $response->description = $files['d'];
                    return $response;
                }
            }
            elseif (4 == $request->action) {
                // 文档
                $data = $this->Uploadfile(LOCAL_SAVEPATH, false, 4);
                $files = $this->saveFileInfo($data);
            }
        }
        else
        {
            return STATUS_NODATA;
        }

        $response->status = STATUS_SUCCESS;
        $response->ids = $files['ids'];
        $response->files = $files['files'];
        return $response;
    }

}