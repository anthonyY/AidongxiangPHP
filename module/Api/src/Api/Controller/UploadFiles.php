<?php
namespace Api\Controller;

/**
 * 上传文件协议
 *
 * @author WZ
 *        
 */
class UploadFiles extends CommonController
{
    public $file_key = 'file';

    /**
     * 返回一个数组或者Result类
     * 
     * @return \Api21\Controller\BaseResult
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        
        $request->action = $request->action ? $request->action : 1;
        $data = array();
        if (isset($_FILES) && $_FILES) {
            if (1 == $request->action) {
                // 上传图片
                $files = $this->getModel('User')->uploadImageForController($this->file_key);
            }
            elseif (2 == $request->action) {
                // 上传视频
                $data = $this->getModel('User')->Uploadfile(LOCAL_SAVEPATH, false, 3);
                $files = $this->saveFileInfo($data);
            }
            elseif (3 == $request->action) {
                $data = $this->getModel('User')->Uploadfile(LOCAL_SAVEPATH, false, 4);
                $files = $this->saveFileInfo($data);
            }
        }
        else {
            return STATUS_NODATA;
        }

        $response->status = STATUS_SUCCESS;
        $response->ids = $files['ids'];
        $response->files = $files['files'];
        return $response;
    }
}