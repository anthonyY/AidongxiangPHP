<?php
/**
 * 关于文件压缩和下载的类
 * @author  tycell
 * @version 1.0
 */
class Zipdown{

    protected $file_path;
    /**
     * 构造函数
     * @param [string] $path [传入文件目录]
     */
    public function __construct($path){
        $this->file_path=$path; //要打包的根目录
    }
    /**
     * 入口调用函数
     * @return [type] [以二进制流的形式返回给浏览器下载到本地]
     */
    public function index(){
        $zip=new ZipArchive();
        $end_dir = $this->file_path.'.zip';//定义打包后的包名
        $dir = $this->file_path;
        if(!is_dir($dir)){
            mkdir($dir);
        }
        if($zip->open($end_dir, ZipArchive::OVERWRITE) === TRUE){ ///ZipArchive::OVERWRITE 如果文件存在则覆盖
            $this->addFileToZip($dir, $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
            $zip->close();
        }
        if(!file_exists($end_dir)){
            exit("无法找到文件");
        }

        ob_end_clean();
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type: application/zip"); //zip格式的
        header('Content-disposition: attachment; filename='.basename($end_dir)); //文件名
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
        header('Content-Length:'.filesize($end_dir)); //告诉浏览器，文件大小
        @readfile($end_dir);
        $this->delDirAndFile($dir,true);//删除目录和文件
        unlink($end_dir);//删除压缩包
    }
    /**
     * 文件压缩函数 需要开启php zip扩展
     * @param [string] $path [路径]
     * @param [object] $zip  [扩展ZipArchive类对象]
     */
    protected function addFileToZip($path, $zip){
        $handler = opendir($path);
        while (($filename=readdir($handler)) !== false) {
            if ($filename!= "." && $filename!=".."){
                if(is_dir($path."/".$filename)){
                    $this->addFileToZip($path."/".$filename,$zip);
                } else {
                    $zip->addFile($path."/".$filename,$filename);
                }
            }
        }
        @closedir($path);
    }
    /**
     * 删除文件函数
     * @param  [string]  $dir    [文件目录]
     * @param  boolean $delDir [是否删除目录]
     * @return [type]          [description]
     */
    public function delDirAndFile($path,$delDir=true){
        $handle=opendir($path);
        if($handle){
            while(false!==($item = readdir($handle))){
                if($item!="."&&$item!=".."){
                    if(is_dir($path.'/'.$item)){
                        $this->delDirAndFile($path.'/'.$item, $delDir);
                    }else{
                        unlink($path.'/'.$item);
                    }
                }
            }
            @closedir($handle);
            if($delDir){return rmdir($path);}
        }else{
            if(file_exists($path)){
                return unlink($path);
            }else{
                return FALSE;
            }
        }
    }
}