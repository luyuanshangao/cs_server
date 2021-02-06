<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\Banner as BannerModel;
use app\common\library\storage\Driver;

/**
 * @name: banner管理
 * @author: gz
 * @description: GET POST
 * @param {type}
 * @return:
 */
class Banner extends Base
{
    protected $noAuthArr = ['getStatus'];
    //接口白名单    不需要登录的接口方法
    protected $noCheckArr = ['get', 'upload', 'deleteImageFile'];    //权限白名单

    /**
     * @name: Banner列表
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function list()
    {

        $data = $this->request->get();
        $BannerModel = new BannerModel();
        $this->getPageAndSize($data);
        $this->getSort($data);
        $condition = $this->filterParam(['id', 'activeLink', 'status'], $data);
        $total = $BannerModel->getCount($condition);
        $list = $BannerModel->getList($condition, $this->from, $this->size, true, $this->sort);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }

    /**
     * @name: Banner单条获取
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function get()
    {
        $data = $this->request->get();
        $data = BannerModel::get($data['id']);
        return show(1, $data);
    }

    /**
     * @name: Banner添加
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function create()
    {
        $data = $this->request->post();
        $data['createTime'] = time();
        $result = BannerModel::create($data);
        return show(1);
    }

    /**
     * @name: Banner编辑
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function edit()
    {
        $data = $this->request->post();
        $BannerModel = new BannerModel();
        $result = $BannerModel->saveData($data);
        return show(1);
    }

    /**
     * @name: Banner删除
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function del()
    {

        $data = $this->request->get();
        $data = BannerModel::destroy(['id' => $data['id']]);
        return show(1);
    }

    /**
     * @name: Banner修改状态
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
        BannerModel::update($data, $where);
        return show(1, $data);
    }

    /**
     * @name: Banner获取状态
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
     * @name: banner上传
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function upload()
    {
        $filedname = 'file';
        $config = config('upload.banner');
// 实例化存储驱动
        $StorageDriver = new Driver($config, $filedname);
// 上传图片
        if (!$StorageDriver->upload()) {
            return show(0, '', $StorageDriver->getError());
        }

        // 图片信息
        $fileInfo = $StorageDriver->getFileInfo();
// 图片上传名
        $fileInfo['fileName'] = $StorageDriver->getFileName();
        $fileInfo['fileDir'] = $StorageDriver->getUplodDir();
        if (!$fileInfo['fileName'] || !$fileInfo['fileDir']) {
            return show(0);
        }
        return show(1, $fileInfo);
    }


    /**
     * @name: deleteImageFile服务器图片删除
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function deleteImageFile()
    {
        $data = $this->request->get();
        BannerModel::update(['fileDir' => '','size' => '','type' => '','fileName' => ''], ['fileDir' => $data['name']]);
        deleteImageFile($data['name']);
        return show(1);
    }
}
