<?php
namespace Common\Service;

class UploadService
{
    private $file_types = '';
    private $files = null;
    private $filename_sanitized = null;
    private $filename_original = null;
    private $max_filesize = 10485760; //10MB
    private $upload_path = '/tmp/upload';
    private $iso_path  = '';
    private $img_path = '';
    private $file_type = '';

    
    public function __construct($files,  $realname, $name)
    {
        $libvirtConf          =   C('LIBVIRT');
        $this->iso_path       =   $libvirtConf['iso_storage_path'];
        $this->img_path       =   $libvirtConf['storage_path'];
        $this->file_types     =   $libvirtConf['upload_file_type'];
        $this->files          =   $files;
        $this->realname       =   $realname;
        $this->md5realname    =   md5($realname);
        $this->filename_sanitized = $name.$this->md5realname;
    }

    public function setFileTypes($fileTypes = array())
    {
        $this->file_types = $fileTypes;
        return $this;
    }

    public function setFileNameOriginal($filename)
    {
        $this->filename_original = $filename;
    }

    public function fileNameOriginal()
    {
        return $this->filename_original;
    }

    public function sanitize()
    {
        return $this;
    }

    public function fileSize()
    {
        return $this->files['size'];
    }

    public function fileNameValid()
    {
        if(!preg_match('/^\w{1,100}$/', $this->filename_sanitized)) {
            throw new \Think\Exception('无效名称!');
        }
        return $this;
    }

    public function extensionValid()
    {
        $fileTypes = implode('|', $this->file_types);
        $rEFileTypes = "/^\.($fileTypes){1}$/i";
        $ret = preg_match($rEFileTypes, strrchr($this->realname, '.'), $match);
        if(!$ret) {
            throw new \Think\Exception('文件类型仅允许\'s '.str_replace('|','，',$fileTypes).'.');
        }
        $this->file_type = $match;
        return $this;
    }

    public function isUploadedFile()
    {
        if(!is_uploaded_file($this->files['tmp_name']))
        {
            throw new \Think\Exception("上传非法");
        }
    }

    public function saveUploadedFile()
    {
        if(!is_dir($this->upload_path)){
            $ret = mkdir($this->upload_path, 0777); 
        }
        if(!move_uploaded_file ($this->files['tmp_name'],$this->upload_path.DIRECTORY_SEPARATOR.$this->filename_sanitized))
            throw new \Think\Exception("复制文件失败!");
    }

    public function fileNameSanitized()
    {
        return $this->filename_sanitized;
    }

    public function uploadFile()
    {
        $this->isUploadedFile();
        if ($this->sanitize->files['size'] <= $this->max_filesize)
        {
            $ret = $this->fileNameValid()->saveUploadedFile();
        }
        else
        {
            throw new \Think\Exception("文件过大!");
        }
        return $this;
    }

    public function buildFile() {
        $type = '';
        $file = $this->findDir($this->upload_path, 0, 0);
        sort($file);
        if(preg_match('/.*\.iso$/i', $this->realname)) {
            $path = $this->iso_path.$this->realname;
            $type = 'iso';
        } else {
            $path = $this->img_path.$this->realname;
        }
        foreach($file as $info) {
            $str = file_get_contents($this->upload_path.DIRECTORY_SEPARATOR.$info.$this->md5realname);
            if(file_put_contents($path, $str, FILE_APPEND) === false) {
                throw new \Think\Exception("重组文件失败 !");
            }
        }
        foreach($file as $info) {
            unlink($this->upload_path.DIRECTORY_SEPARATOR.$info.$this->md5realname);
        }
        $data = array(
            'status'     =>  true,
            'filename'=>$this->readlname,
            'fileType'  =>$this->file_type,
            'path'        =>$path, 
            'size'          =>filesize($path), 
            'md5'       =>$this->md5($str)
            );
        return $data;
    }

    public function findDir($path, $deeplimit=0, $deep=0) {
        $deep   += 1;
        if ($deeplimit != 0 && $deep > $deeplimit) {
            return array();
        }
        $dir	= dir($path);
        $ls		= array();
        while ($item = $dir->read()) {
            $tmp = $path. DIRECTORY_SEPARATOR .$item;
            if (is_file($tmp) && preg_match('/^\d+'.$this->md5realname.'$/', $item) && ($item=str_replace($this->md5realname, '', $item))) {
                $ls[] = $item;
            }
        }
        return $ls;
    }
}
