<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\Appversion as AppversionModel;
use app\common\library\storage\Driver;

class Appversion extends Base
{
    protected $noAuthArr = ['upload'];
//接口白名单    不需要登录的接口方法
    protected $noCheckArr = ['upload'];
//权限白名单

    public function _initialize()
    {
        parent::_initialize();
    }
    public function index()
    {
    }
 
 /**
     * @name: 列表
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function list()
    {

        $data = $this->request->get();
        $AppversionModel = new AppversionModel();
        $this->getPageAndSize($data);
        $this->getSort($data);
        $condition = $this->filterParam(['id', 'status'], $data);
        $total = $AppversionModel->getCount($condition);
        $list = $AppversionModel->getList($condition, $this->from, $this->size, true, $this->sort);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }

    /**
     * @name: 单条获取
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function get()
    {
        $data = $this->request->get();
        $data = AppversionModel::get($data['id']);
        return show(1, $data);
    }

    /**
     * @name: 添加
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function create()
    {
        $data = $this->request->post();
        $data['createTime'] = time();
        $domain = $this->request->domain();
        $data['downLink'] = $domain . $data['fileDir'];
        $data['createTime'] = time();
      
        $result = AppversionModel::create($data);
        return show(1);
    }

    /**
     * @name: 编辑
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function edit()
    {
        $data = $this->request->post();
        $AppversionModel = new AppversionModel();
        $domain = $this->request->domain();
        $data['downLink'] = $domain . $data['fileDir'];
        $result = $AppversionModel->saveData($data);
        return show(1);
    }

    /**
     * @name: 删除
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function del()
    {

        $data = $this->request->get();
        $data = AppversionModel::destroy(['id' => $data['id']]);
        return show(1);
    }

    /**
     * @name: 修改状态
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function editStatus()
    {
        $data = $this->request->post();
        $where = ['id' => ['in', $data['checkArray']]];
        unset($data['checkArray']);
        AppversionModel::update($data, $where);
        return show(1, $data);
    }

    /**
     * @name: 获取状态
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getStatus()
    {

        $data = config('adminSetting.db_status');
        return show(1, $data);
    }
   /**
     * @name: 上传
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function upload()
    {
        
        $filedname = 'file';
        $config = config('upload.version');
        // 实例化存储驱动
        $StorageDriver = new Driver($config, $filedname);
        // 上传图片
        if (!$StorageDriver->upload()) {
            return show(0, '', $StorageDriver->getError());
        }

        //信息
        $fileInfo = $StorageDriver->getFileInfo();

        // 图片上传名
        $fileInfo['fileName'] = $StorageDriver->getFileName();
        $fileInfo['fileDir'] = $StorageDriver->getUplodDir();
        if(!$fileInfo['fileName'] || !$fileInfo['fileDir']){
            return show(0);
        }
        return show(1, $fileInfo);

        
    }
   /**
     * @name: 文件删除
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function deleteFile()
    {
        $data = $this->request->get();
        AppversionModel::update(['fileDir' => '','downLink' => '','size' => '','type' => '','fileName' => ''], ['fileDir' => $data['name']]);
        deleteImageFile($data['name']);
        return show(1);
    }
}
