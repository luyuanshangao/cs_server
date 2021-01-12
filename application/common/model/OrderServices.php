<?php

namespace app\common\model;

use app\api\controller\v1\Vop;

class OrderServices extends BaseModel
{

    public static function refund($servicesId)
    {
        //重新获取server单
        $serviceObj = self::get(['servicesId' => $servicesId]);
        if (!$serviceObj || $serviceObj->isRefund) {
            return;
        }
        $orderGoodsInfo = OrderGoods::get(['orderGoodsId' => $serviceObj->orderGoodsId]);
        //商品退款
        (new Assets())->addUSDT($orderGoodsInfo->userId, $orderGoodsInfo->goodsUsdtPrice, '商品退款');
        $serviceObj->isRefund = 1;
        $serviceObj->save();
        //消息通知
        Message::add($orderGoodsInfo->userId, '资产变动通知', '商品退款', '您购买的商品金额退款已到账，请确认！');
        return true;
    }

    //客户自己发货
    public function setSendSkuUpdate($userId, $dataArr)
    {

        $this->startTrans();
        try {
            $OrderServicesObj = $this::get(['servicesId' => $dataArr['servicesId'],'userId' => $userId]);
            //记录发货信息
            $OrderServicesObj->sendSkuUpdate = json_encode($dataArr);
            //修改状态为退款中
            $OrderServicesObj->servicesStatus = 1;
            $OrderServicesObj->save();
            //发送到三方
            $result = Vop::sendSkuUpdate($dataArr['afsNum'], $dataArr['freightMoney'], $dataArr['expressCompany'], $dataArr['deliverDate'], $dataArr['expressCode']);
            
            if (!$result) {
                throw new \Exception("Error");
            }
            
            $this->commit();
            return true;
        } catch (\Exception $th) {
            $this->rollback();
            return false;
        }
    }
}
