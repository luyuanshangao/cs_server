<?php

namespace app\common\library\storage\engine;

use app\api\exception\ApiException;
use think\Request;

/**
 * 存储引擎抽象类
 * Class server
 * @package app\common\library\storage\drivers
 */
abstract class Server
{
    protected $file;
    protected $error;
    protected $fileName;
    protected $fileInfo;

    /**
     * 构造函数
     * Server constructor.
     * @throws Exception
     */
    protected function __construct($filedname)
    {
        // 接收上传的文件
        
        $this->file = Request::instance()->file($filedname);
        
        if (empty($this->file)) {
            return false;
        }

        $this->fileName = $this->buildSaveName();
        if (is_array($this->file)) {
            foreach ($this->file as $k => $v) {
                 // 文件信息
                $this->fileInfo[] = $v->getInfo();
            }
        } else {
            // 文件信息
            $this->fileInfo = $this->file->getInfo();
        }
    }

    /**
     * 文件上传
     * @return mixed
     */
    abstract protected function upload();

    /**
     * 返回上传后文件路径
     * @return mixed
     */
    abstract public function getFileName();

    /**
     * 返回文件信息
     * @return mixed
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * 返回错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 生成保存文件名
     */
    private function buildSaveName()
    {

        if (is_array($this->file)) {
            foreach ($this->file as $k => $v) {
                // 要上传图片的本地路径
                $realPath = $v->getRealPath();
                
                // 扩展名
                $ext = pathinfo($v->getInfo('name'), PATHINFO_EXTENSION);
                // 自动生成文件名
                $saveName[] = date('YmdHis') . substr(md5($realPath), 0, 5) . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT) . '.' . $ext;
            }
        } else {
             // 要上传图片的本地路径
            $realPath = $this->file->getRealPath();
            
            // 扩展名
            $ext = pathinfo($this->file->getInfo('name'), PATHINFO_EXTENSION);
            // 自动生成文件名
            $saveName = date('YmdHis') . substr(md5($realPath), 0, 5) . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT) . '.' . $ext;
        }

        // 自动生成文件名
        return $saveName;
    }
}
