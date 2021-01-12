<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\Extension as ExtensionModel;

class Extension extends Base
{
    protected $noAuthArr = [];
//接口白名单    不需要登录的接口方法
    protected $noCheckArr = [];
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
        $ExtensionModel = new ExtensionModel();
        $this->getPageAndSize($data);

        $total = $ExtensionModel->getCount([]);
        $list = $ExtensionModel->getList([], $this->from, $this->size, true, $this->sort, ['admin']);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
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
        $data['adminId'] = $this->adminId;
        $data['updateTime'] = time();
        $ExtensionModel = new ExtensionModel();
        $ExtensionModel->saveData($data, 'extensionId');
        return show(1);
    }
}
