<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

class IdleDealRefund extends BaseModel
{
    /**
     * @name: 退款
     * @author: gz
     * @description:
     * @param {*} $idleDealRefundId
     * @return {*}
     */
    public static function refund($idleDealRefundId)
    {
        //重新获取server单
        $dealRefundObj = self::get(['idleDealRefundId' => $idleDealRefundId]);
        if (!$dealRefundObj || $dealRefundObj->isRefund || $dealRefundObj->status == 2) {
            return;
        }
        $idleDealInfo = IdleDeal::get(['idleDealId' => $dealRefundObj->idleDealId]);
        //商品退款
        (new Assets())->addUSDT($idleDealInfo->buyUserId, $idleDealInfo->price, '闲置退款');
        $dealRefundObj->isRefund = 1;
        $dealRefundObj->isRefundTime = time();
        $dealRefundObj->save();
        //消息通知
        Message::add($idleDealInfo->userId, '资产变动通知', '闲置退款', '您购买的闲置金额退款已到账，请确认！');
        return true;
    }
  
    /**
     * @name: 创建申请
     * @author: gz
     * @description:
     * @param {*} $userId
     * @param {*} $data
     * @return {*}
     */
    public static function createReund($userId, $IdleDealInfo, $data)
    {
      
        try {
             $picArr = json_decode($data['picPath'], true);
             $picPath = implode(',', $picArr);
        } catch (\Exception $e) {
             $picPath = '';
        }
        $IdleDealInfo->dealStats = 0;
        $IdleDealInfo->save();
        $saveData = [
          'idleDealId' => $data['idleDealId'],
          'idleInfoId' => $data['idleInfoId'],
          'userId' => $userId,
          'sellUserId' => $IdleDealInfo['sellUserId'],
          'reson' => $data['reson'],
          'resonDetail' => $data['resonDetail'],
          'picPath' => $picPath,
          'status' => 1,
          'isRefund' => 0,
          'createTime' => time(),
        ];
        self::create($saveData);
        return true;
    }
}
