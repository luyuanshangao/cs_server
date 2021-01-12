<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\common\model\IdleDealDispute;

class Dispute extends Base
{
    protected $noAuthArr = ['month'];//接口白名单    不需要登录的接口方法
    protected $noCheckArr = [];    //权限白名单

    /**
     * @name: Idle列表
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function list()
    {
        
        $data = $this->request->get();
        $IdleInfoModel = new IdleInfo();
        $this->getPageAndSize($data);
        $condition = $this->filterParam(['userId','userName','assetsType','createTime'], $data);

        $total = $IdleInfoModel->getViewCount($condition);
        
        $list = $IdleInfoModel->getViewList($condition, $this->from, $this->size, $this->sort);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }
    

   /**
     * @name: 通过
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function agree()
    {
        $dataArr = $this->request->get();
        IdleDealDispute::update(['disputeResults' => 1], ['idleDealDisputeId' => $dataArr['idleDealDisputeId']]);
        return show(1);
    }

   /**
     * @name: 拒绝
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function refuse()
    {
        
        $dataArr = $this->request->get();
        IdleDealDispute::update(['disputeResults' => 2], ['idleDealDisputeId' => $dataArr['idleDealDisputeId']]);
        return show(1);
    }
}
