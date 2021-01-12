<?php

namespace app\common\model;

class ExtensionDeal extends BaseModel
{
    /**
     * @name: 添加推广订单信息
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function createData($userId, $superiorId, $orderId, $incPrice)
    {

        $data = [
            'userId' => $userId,
            'superiorId' => $superiorId,
            'orderId' => $orderId,
            'amount' => $incPrice,
            'updateTime' => time(),
            'createTime' => time(),
        ];
        $dealData = self::create($data);
        self::computedDeal($userId, $superiorId, $dealData['dealId'], $incPrice);
    }
    /**
     * @name: 返回直接邀请的人下单数
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public static function getDirectDealNum($superiorId, $begin, $end)
    {
           return self::where([
                'superiorId' => $superiorId,
                'createTime' => ['between', [$begin, $end]],
            ])->count();
    }
    /**
     * @name: 返回间接邀请的人下单数
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public static function getIndirectDealNum($superiorId, $begin, $end)
    {
        $directNoTimeUserIds = ExtensionInvitation::where(['superiorId' => $superiorId])->column('userId');
        $conditionIndir = [
            'level' => 1,
            'superiorId' => ['in', $directNoTimeUserIds],
        ];
        $indirectUserIds = ExtensionInvitation::where($conditionIndir)->column('userId');

            return self::where([
                'userId' => ['in',$indirectUserIds],
                'createTime' => ['between', [$begin, $end]],
            ])->count();
    }
    
    /**
     * @name: 返回所有关联邀请的人下单数
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public static function getAllInvDealNum($superiorId, $begin, $end)
    {
       
                //直接邀请的id
                $invitationIds = ExtensionInvitation::where(['superiorId' => $superiorId])->column('invitationId');
                $invitIdArr = [];
        foreach ($invitationIds as $invitationId) {
            $getChildIdsStr = ExtensionInvitation::getChildIds($invitationId);
            $childIdsArr = explode(',', $getChildIdsStr);
            $keyIn = array_search($invitationId, $childIdsArr);
            array_splice($childIdsArr, $keyIn, 1);
            $invitIdArr = array_merge($invitIdArr, $childIdsArr);
        }

                $conditionIndir = [
                    'invitationId' => ['in', $invitIdArr],
                ];
                
                $allInvUserIds = ExtensionInvitation::where($conditionIndir)->column('userId');
                return self::where([
                    'userId' => ['in',$allInvUserIds],
                    'createTime' => ['between', [$begin, $end]],
                ])->count();
    }

    /**
     * @name: 计算推广各收益详情
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    private static function computedDeal($userId, $superiorId, $dealId, $incPrice)
    {
       
        #1 直接邀请人(初级，高级) 获得收益
        $extensionId = ExtensionUser::where(['userId' => $superiorId])->value('extensionId');
        $exDataFirIncome = Extension::where(['extensionId' => $extensionId])->value('firIncome');
        $exDataFirIncome = bcdiv($exDataFirIncome, '100', config('app.usdt_float_num'));
        switch ($extensionId) {
            case 2:
            case 3:
            case 4:
                    //初级 试用初级 高级
                    $amount = bcmul($incPrice, $exDataFirIncome, config('app.usdt_float_num'));
                    $dealDetail = [
                        'dealId' => $dealId,
                        'userId' => $superiorId,
                        'amount' => $amount,
                        'lock' => 1,
                        'updateTime' => time(),
                        'createTime' => time(),
                    ];
                    ExtensionDealDetail::profitAdd($dealDetail);
                    ExtensionAssets::upAmount($superiorId, $amount, 2, 1, '直接收益');
                break;
            
            default:
                break;
        }

        #2 间接邀请人(高级) 获得收益
        $inviData = ExtensionInvitation::get(['userId' => $superiorId]);
       
        if ($inviData) {
            $extensionJianId = ExtensionUser::where(['userId' => $inviData['superiorId']])->value('extensionId');
            $exDataSecIncome = Extension::where(['extensionId' => $extensionJianId])->value('secIncome');
           
            $exDataSecIncome = bcdiv($exDataSecIncome, '100', config('app.usdt_float_num'));
            
            switch ($extensionJianId) {
                case 4:
                    $jianAmount = bcmul($incPrice, $exDataSecIncome, config('app.usdt_float_num'));
                    $dealDetail = [
                        'dealId' => $dealId,
                        'userId' => $inviData['superiorId'],
                        'amount' => $jianAmount,
                        'lock' => 1,
                        'updateTime' => time(),
                        'createTime' => time(),
                    ];
                    
                    ExtensionDealDetail::profitAdd($dealDetail);
                   
                    ExtensionAssets::upAmount($inviData['superiorId'], $jianAmount, 2, 1, '间接收益');
                    break;
                            
                default:
                    # code...
                    break;
            }
        }
    }


    
    /**
     * @name: 通过订单id 修改状态 退款
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function upLockRefund($orderId)
    {

        $data = self::get(['orderId' => $orderId]);
        if (!$data) {
            return;
        }
     
        //将记录修改为 退款
        self::update(['lock' => 2,'updateTime' => time()], ['orderId' => $orderId]);
      
        //收益各详细记录添加修改
        ExtensionDealDetail::refundAdd($data['dealId']);
    }
    
    //7天之后 修改为可提现
    public static function upSevenDaysAgoLockToAlow()
    {
        
        $timeStr = time() - 86400 * 3;
        $dataDeal = self::where([
            'lock' => 1,
            'createTime' => ['lt',$timeStr]
        ])->column('dealId,userId,amount');
        $dealIds = array_column($dataDeal, 'dealId');
        //修改交易
        self::update(['lock' => 0,'updateTime' => time()], [
            'dealId' => ['in',$dealIds]
        ]);
       
        //可提现收益记录
        $exDealList = ExtensionDealDetail::where([
            'dealId' => ['in',$dealIds]
        ])->column('dealId,userId,amount', 'dealDetailId');

        foreach ($exDealList as $key => $value) {
            $dealDetail = [
                'dealId' => $value['dealId'],
                'userId' => $value['userId'],
                'amount' => $value['amount'],
                'lock' => 0,
                'updateTime' => time(),
                'createTime' => time(),
            ];
            //添加记录
            ExtensionDealDetail::profitAdd($dealDetail);
            //账户数值变化
            // ExtensionAssets::lockToAlow($value['userId'], $value['amount']);
            ExtensionAssets::upAmount($value['userId'], $value['amount'], 1, 1, '锁定金额转换');
            ExtensionAssets::upAmount($value['userId'], $value['amount'], 2, 1, '转换为可提现');
        }

         #3父级(主节点分红)获得荣耀值
       
        foreach ($dataDeal as $dealData) {
               //查询上级是否含有(主节点)
               $invitationId = ExtensionInvitation::where(['userId' => $dealData['userId']])->value('invitationId');

               //获取上级invitationIds
               $invitationIdsStr = ExtensionInvitation::getParentIds($invitationId);
               $invitationIdsArr = explode(',', $invitationIdsStr);
               $superiorIds = ExtensionInvitation::where([
                   'invitationId' => ['in',$invitationIdsArr],
               ])->order('invitationId desc')->column('superiorId');
               //查找最近的(主节点)【父级】
               $userAllIncomeId = ExtensionUser::where([
                   'userId' => ['in',$superiorIds],
                   'extensionId' => 5,
               ])->order('userId')->find();
                
            if ($userAllIncomeId) {
                $allIncome = Extension::where(['extensionId' => 5])->value('allIncome');
                $allIncome = bcdiv($allIncome, '100', config('app.usdt_float_num'));
                $gloryAmount =  bcmul($dealData['amount'], $allIncome, config('app.usdt_float_num')) * 100;
                //计算预估荣耀值
                ExtensionGlory::upAmount($userAllIncomeId['userId'], $gloryAmount, 1, '挖矿奖励');
            }
        }
    }
}
