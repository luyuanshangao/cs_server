<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\IdleInfo;
use app\admin\model\IdleDeal;
use app\admin\model\IdleDealDisputeEvidence;
use app\admin\model\IdleDealDispute;

class Idle extends Base
{
    protected $noAuthArr = ['month'];//接口白名单    不需要登录的接口方法
    protected $noCheckArr = [];    //权限白名单

    /**
     * @name: IdleInfo列表
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
        $condition = $this->filterParam(['userId','userName','verifyStatus','createTime'], $data);
        $condition['IdleInfo.isVerify'] = 1;
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
     * @name: IdleDeal列表
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function dealList()
    {
        
        $data = $this->request->get();
        $IdleDealModel = new IdleDeal();
        $this->getPageAndSize($data);
        $condition = $this->filterParam(['userId','userName','dealType','createTime'], $data);
        switch (true) {
            case isset($data['dealType']) && $data['dealType'] == 1: //代付款
                    $condition['IdleDeal.dealStats'] = 1;
                break;
            case isset($data['dealType']) && $data['dealType'] == 2: //待发货
                    $condition['IdleDeal.dealStats'] = 2;
                break;
            case isset($data['dealType']) && $data['dealType'] == 3: //待收货
                    $condition['IdleDeal.dealStats'] = 3;
                break;
            case isset($data['dealType']) && $data['dealType'] == 4: //已完成
                    $condition['IdleDeal.dealStats'] = 4;
                break;
            case isset($data['dealType']) && $data['dealType'] == 5: //已取消
                    $condition['IdleDeal.dealStats'] = 6;
                break;
            case isset($data['dealType']) && $data['dealType'] == 6: //退款订单
                    $condition['IdleDeal.dealStats'] = ['in',[0,8]];
                break;
            case isset($data['dealType']) && $data['dealType'] == 7: //纠纷订单
                    $condition['IdleDeal.dealStats'] = ['in',[5,9]];
                break;
            default:
                # code...
                break;
        }
        unset($condition['dealType']);
        $total = $IdleDealModel->getViewCount($condition);
        $list = $IdleDealModel->getViewList($condition, $this->from, $this->size);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }
    

   /**
     * @name: 通过审核
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function agree()
    {
        $dataArr = $this->request->get();
        IdleInfo::update(['verifyStatus' => 1,'groundStatus' => 1], ['idleInfoId' => $dataArr['idleInfoId']]);
        return show(1);
    }

   /**
     * @name: 拒绝审核
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function refuse()
    {
        $dataArr = $this->request->get();
        IdleInfo::update(['verifyStatus' => 2], ['idleInfoId' => $dataArr['idleInfoId']]);
        return show(1);
    }
   /**
     * @name: 获取举证
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getEvidence()
    {
        $dataArr = $this->request->get();
        $IdleDealDisputeEvidence = new IdleDealDisputeEvidence();
        $data  = $IdleDealDisputeEvidence->getViewList(['idleDealDisputeId' => $dataArr['idleDealDisputeId']]);
        return show(1, $data);
    }
   /**
     * @name: 判决
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function sellUp()
    {
        $dataArr = $this->request->get();
        $IdleDealDisputeData = IdleDealDispute::get(['idleDealDisputeId' => $dataArr['idleDealDisputeId']]);
        //$IdleDealData = IdleDeal::get(['idleDealId'=>$IdleDealDisputeData['idleDealId']]);
        #判断谁发起纠纷
        if ($IdleDealDisputeData['isWho'] == 'sell') {
            #卖家发起纠纷
            $IdleDealDisputeData->disputeResults = 1;
            $data['disputeResults'] = 1;
            $IdleDealDisputeData->save();
        } else {
            $IdleDealDisputeData->disputeResults = 2;
            $data['disputeResults'] = 2;
            $IdleDealDisputeData->save();
        }
        return show(1, $data);
    }
   /**
     * @name: 判决
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function buyUp()
    {
        $dataArr = $this->request->get();
        $IdleDealDisputeData = IdleDealDispute::get(['idleDealDisputeId' => $dataArr['idleDealDisputeId']]);
        //$IdleDealData = IdleDeal::get(['idleDealId'=>$IdleDealDisputeData['idleDealId']]);
        #判断谁发起纠纷
        if ($IdleDealDisputeData['isWho'] == 'sell') {
            #卖家发起纠纷
            $IdleDealDisputeData->disputeResults = 2;
            $data['disputeResults'] = 2;
            $IdleDealDisputeData->save();
        } else {
            $IdleDealDisputeData->disputeResults = 1;
            $data['disputeResults'] = 1;
            $IdleDealDisputeData->save();
        }
        
        return show(1, $data);
    }
    /**
     * @name: 获取审核状态
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function getVerifyStatus()
    {
        $arr = [
            ['name' => '未审核','verifyStatus' => 0],
            ['name' => '已通过','verifyStatus' => 1],
            ['name' => '未通过','verifyStatus' => 2],
        ];
        return show(1, $arr);
    }
    /**
     * @name: 获取订单状态
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function getDealStats()
    {
        //0申请退款/1待付款/2待发货/3待收货/4已完成/5纠纷订单/6被取消/7待退款/8退款成功/9纠纷完成
        $arr = [
            ['name' => '退款订单','dealStats' => 0],
            ['name' => '待付款','dealStats' => 1],
            ['name' => '待发货','dealStats' => 2],
            ['name' => '待收货','dealStats' => 3],
            ['name' => '已完成','dealStats' => 4],
            ['name' => '纠纷订单','dealStats' => 5],
            ['name' => '已取消','dealStats' => 6],
            ['name' => '退款订单','dealStats' => 7],
            ['name' => '退款订单','dealStats' => 8],
            ['name' => '纠纷订单','dealStats' => 9],
        ];
        return show(1, $arr);
    }
}
