<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\PriceRule;

class Adjustment extends Base
{
    protected $noAuthArr = ['getStatus'];
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
        $PriceRule = new PriceRule();
        $this->getPageAndSize($data);
        $condition = $this->filterParam(['status'], $data);
        $total = $PriceRule->getCount($condition);
        $list = $PriceRule->getList($condition, $this->from, $this->size, true, $this->sort, ['admin']);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
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
        $data['adminId'] = $this->adminId;
        $data['createTime'] = time();
        $data['updateTime'] = time();
        $result = PriceRule::create($data);
        $result->admin = $this->clientInfo->username;
        return show(1, $result);
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
        $PriceRule = new PriceRule();
        $result = $PriceRule->saveData($data, 'priceRuleId');
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
        $PriceRule = new PriceRule();
        $data = $PriceRule::destroy($data['priceRuleId']);
        return show(1);
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
     * @name: 修改状态
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function editStatus()
    {
        $data = $this->request->post();
        $where = ['priceRuleId' => ['in', $data['checkArray']]];
        unset($data['checkArray']);
        PriceRule::update($data, $where);
        return show(1, $data);
    }
}
