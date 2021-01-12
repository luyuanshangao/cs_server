<?php

namespace app\common\library\storage\engine;

/**
 * 本地文件驱动
 * Class Local
 * @package app\common\library\storage\drivers
 */
class Local extends Server
{
    public $config;
    public $uplodDir;
    public function __construct($config = ['foldername' => 'image','size' => 4,'ext' => 'jpg,jpeg,png,gif'], $filedname)
    {
        parent::__construct($filedname);
        $this->config = $config;
    }

    /**
     * 上传图片文件
     * @return array|bool
     */
    public function upload()
    {
        if (empty($this->file)) {
            return 'empty';
        }
        // 上传目录
        $this->uplodDir = WEB_PATH . 'uploads/' . $this->config['foldername'];
       
        if (is_array($this->file)) {
            foreach ($this->file as $key => $value) {
                $info = $value->validate(['size' => $this->config['size'] * 1024 * 1024, 'ext' => $this->config['ext']])
                ->move($this->uplodDir, $this->fileName[$key]);
                if (empty($info)) {
                    $this->error = $value->getError();
                    return false;
                }
            }
        } else {
        // 验证文件并上传
            $info = $this->file->validate(['size' => $this->config['size'] * 1024 * 1024, 'ext' => $this->config['ext']])
            ->move($this->uplodDir, $this->fileName);
            if (empty($info)) {
                $this->error = $this->file->getError();
                return false;
            }
        }
       
        return true;
    }

    /**
     * 返回文件路径
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * 返回文件路径
     * @return mixed
     */
    public function getUplodDir()
    {
        if (is_array($this->fileName)) {
            foreach ($this->fileName as $key => $value) {
                $updir[] = '/uploads/' . $this->config['foldername'] . '/' . $value;
            }
            return $updir;
        } else {
            return '/uploads/' . $this->config['foldername'] . '/' . $this->fileName;
        }
    }
}
