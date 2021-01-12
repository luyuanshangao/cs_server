<?php

namespace app\common\library\storage;

use think\Exception;

/**
 * 存储模块驱动
 * Class driver
 * @package app\common\library\storage
 */
class Driver
{
    // use app\common\library\storage\Driver
    // /**
    //  * 图片上传接口
    //  * @return array
    //  * @throws \think\Exception
    //  */
    // public function image()
    // {
    //     // 实例化存储驱动
    //     $StorageDriver = new Driver(config('upload.Local'),$filedname);
    //     // 上传图片
    //     if (!$StorageDriver->upload())
    //         return json(['code' => 0, 'msg' => '图片上传失败' . $StorageDriver->getError()]);
    //     // 图片上传路径
    //     $fileName = $StorageDriver->getFileName();
    //     // 图片信息
    //     $fileInfo = $StorageDriver->getFileInfo();
    // }
    private $config;
// upload 配置  // upload 配置  array(default 调用的存储引擎类 engine=>对应存储引擎类)
    private $engine;
// 当前存储引擎类
    private $filename;
/**
     * 构造方法
     * Driver constructor.
     * @param $config
     * @throws Exception
     */
    public function __construct($config, $filedname)
    {
        $this->config = $config;
        $this->filedname = $filedname;
// 实例化当前存储引擎
        $this->engine = $this->getEngineClass();
    }
    /**
     * 执行文件上传
     */
    public function upload()
    {
        return $this->engine->upload();
    }
    /**
     * 获取错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->engine->getError();
    }
    /**
     * 获取文件名
     * @return mixed
     */
    public function getFileName()
    {
        return $this->engine->getFileName();
    }
    
    /**
     * 获取文件路径
     * @return mixed
     */
    public function getUplodDir()
    {
        return $this->engine->getUplodDir();
    }
    
    /**
     * 返回文件信息
     * @return mixed
     */
    public function getFileInfo()
    {
        return $this->engine->getFileInfo();
    }
    /**
     * 获取当前的存储引擎
     * @return mixed
     * @throws Exception
     */
    private function getEngineClass()
    {
        $engineName = $this->config['default'];
        $classSpace = __NAMESPACE__ . '\\engine\\' . ucfirst($engineName);
        if (!class_exists($classSpace)) {
            throw new Exception('未找到存储引擎类: ' . $engineName);
        }
        $config = isset($this->config['engine']) ? $this->config['engine'] : [];
        return new $classSpace($config, $this->filedname);
    }
}
