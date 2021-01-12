<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\AssetsWithdraw;
use app\common\model\AssetsType;

class Cash extends Base
{
    protected $noAuthArr = ['month'];//接口白名单    不需要登录的接口方法
    protected $noCheckArr = [];    //权限白名单

    /**
     * @name: Cash列表
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    /**
     * @name: User列表
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function list()
    {
        
        $data = $this->request->get();
        $AssetsWithdrawModel = new AssetsWithdraw();
        $this->getPageAndSize($data);
        $condition = $this->filterParam(['userId','userName','assetsType','createTime'], $data);

        $total = $AssetsWithdrawModel->getViewCount($condition);
        
        $list = $AssetsWithdrawModel->getViewList($condition, $this->from, $this->size, $this->sort);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }
    
    /**
     * @name: 支付方式
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getPayType()
    {
        
        $data = AssetsType::all(['canPayOrder' => 1]);
  
        return show(1, $data);
    }

   /**
     * @name: 提现通过
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function agree()
    {
        
        $dataArr = $this->request->get();
        AssetsWithdraw::update(['withdrawStatus' => 2], ['withdrawId' => $dataArr['withdrawId']]);
        return show(1);
    }
   /**
     * @name: 提现拒绝
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function refuse()
    {
        
        $dataArr = $this->request->get();
        AssetsWithdraw::update(['withdrawStatus' => 0], ['withdrawId' => $dataArr['withdrawId']]);
        return show(1);
    }
}
