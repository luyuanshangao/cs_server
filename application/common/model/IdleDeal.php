<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

use app\common\library\redis\OrderDelayed;

class IdleDeal extends BaseModel
{

    public function getIdleInfoAttr($value, $data)
    {
        $idleInfo = IdleInfo::get(['idleInfoId' => $data['idleInfoId']]);
        if ($idleInfo) {
            try {
                $pic = explode(',', $idleInfo['picPath'])[0];
            } catch (\Exception $th) {
                $pic = '';
            }
          
            $returnData = [
                'idleInfoId' => $idleInfo['idleInfoId'],
                'title' => $idleInfo['title'],
                'price' => $idleInfo['price'],
                'pic' => $pic,

            ];
            return $returnData;
        }
    }

    public function getPicArrAttr($value, $data)
    {
    }

    // /**
    //  * @name: 交易数
    //  * @author: gz
    //  * @description:
    //  * @param {*} $userId
    //  * @return {*}
    //  */
    // public static function sellNum($userId){
    //     return self::where([
    //         'sellUserId'=>$userId,
    //     ])->count();
    // }

    /**
     * @name: 添加deal
     * @author: gz
     * @description:
     * @param {*} $userId
     * @param {*} $dataArr
     * @return {*}
     */
    public function addDeal($userId, $data)
    {
        $addressId = $data['addressId'];
        $idleInfoId = $data['idleInfoId'];
        $this->startTrans();
        try {
            #获取idlenfo
            $idleInfo = IdleInfo::getDeail($idleInfoId);
            if (!$idleInfo) {
                throw new \Exception("", 1050);
            }
            #判断是否可购买
            if ($idleInfo['userId'] == $userId) {
                throw new \Exception("", 1051);
            }
            if ($idleInfo['verifyStatus'] !== 1) {
                throw new \Exception("", 1052);
            }
            if ($idleInfo['groundStatus'] !== 1) {
                throw new \Exception("", 1052);
            }
            if ($idleInfo['sellStatus'] === 1 || $idleInfo['sellStatus'] === '') {
                throw new \Exception("", 1052);
            }

            //收货地址信息
            $addressObj = UserAddress::get(['addressId' => $addressId,'userId' => $userId]);
       
            if (!$addressObj) {
                throw new \Exception("", 1040);
            }
            do {
                $dealSn = self::makeDealSn();
            } while (self::where(['dealSn' => $dealSn ])->count() == 1);
            $createTime = time();
            $dataSaveArr = [
                'dealSn' => $dealSn,
                'sellUserId' => $idleInfo['userId'],
                'buyUserId' => $userId,
                'idleInfoId' => $idleInfoId,
                'addressId' => $addressId,
                'price' => $idleInfo['price'],
                'remark' => isset($data['remark']) ? $data['remark'] : '',
                'dealStats' => 1,
                'createTime' => $createTime
            ];
            $dealOjb = self::create($dataSaveArr);
            //修改闲置信息状态
            $idleInfo->sellStatus = 1;
            $idleInfo->save();
            $this->commit();
            OrderDelayed::addOrderDelayedTask(['userId' => $userId,'idleDealId' => $dealOjb['idleDealId']], ($createTime + 900), 'delDeal');
            OrderDelayed::addOrderDelayedTask(['userId' => $userId,'idleDealId' => $dealOjb['idleDealId']], ($createTime + 600), 'sendDeal');
            return $dealOjb;
        } catch (\Exception $e) {
            $this->rollback();
            return ['code' => $e->getCode(),'message' => $e->getMessage()];
        }
    }
    
    public function payDeal($userId, $dealInfo)
    {
        $idleDealId = $dealInfo['idleDealId'];
        $this->startTrans();
        try {
            //用户扣款
            $AssetsModel = new Assets();
            $AssetsModel->costUSDT($userId, $dealInfo->price, '支付闲置订单');
            //修改订单状态
            $dealInfo->payTime = time();
            $dealInfo->dealStats = 2;
            $dealInfo->save();
            //提示消息
            Message::add($userId, '资产变动通知', '消费支出', '您消费支出' . $dealInfo->price . 'USDT' . ',请查看！');
             #恢复idleInfo状态


            $this->commit();
            //取消订单redis延时队列
            //['action'=>$action,'time'=>$time,'data' => $data,]
            
            OrderDelayed::delOrderDelayedTask(
                json_encode([
                    'action' => 'delDeal',
                    'time' => ($dealInfo->getData('createTime') + 900),
                    'data' => ['idleDealId' => $idleDealId]
                ])
            );
            
            OrderDelayed::delOrderDelayedTask(
                json_encode([
                    'action' => 'sendDeal',
                    'time' => ($dealInfo->getData('createTime') + 600),
                    'data' => ['userId' => $userId,'idleDealId' => $idleDealId]
                  ])
            );
              
            return true;
        } catch (\Exception $th) {
            $this->rollback();
            return false;
        }
    }

    /**
     * @name: 手动取消Deal
     * @author: gz
     * @description:
     * @param {*} $userId
     * @param {*} $idleDealId
     * @return {*}
     */
    public function cancelIdleDeal($userId, $idleDealId)
    {
     
        $confition = [
            'idleDealId' => $idleDealId,
            'buyUserId' => $userId,
            'dealStats' => 1,
        ];
        $this->startTrans();
        try {
            $dealInfo = $this::get($confition);
            
            if (!$dealInfo) {
                return false;
            }
            $dealInfo->dealStats = 6;
            $dealInfo->save();
            
            #恢复idleInfo状态
            $idleInfo = IdleInfo::getDeail($dealInfo['idleInfoId']);
            $idleInfo->sellStatus = 0;
            $idleInfo->save();
            $this->commit();
            # 清除redis队列中提示
            OrderDelayed::delOrderDelayedTask(
                json_encode([
                    'action' => 'delDeal',
                    'time' => ($dealInfo->createTime + 900),
                    'data' => ['userId' => $userId,'idleDealId' => $dealInfo['idleDealId']]
                ])
            );
            
            OrderDelayed::delOrderDelayedTask(
                json_encode([
                    'action' => 'sendDeal',
                    'time' => ($dealInfo->createTime + 600),
                    'data' => ['userId' => $userId,'idleDealId' => $dealInfo['idleDealId']]
                ])
            );
            return true;
        } catch (\Exception $th) {
            $this->rollback();
            return false;
        }
    }
    /**
     * @name: 卖家手动关闭Deal
     * @author: gz
     * @description:
     * @param {*} $userId
     * @param {*} $idleDealId
     * @return {*}
     */
    public function closeIdleDeal($userId, $idleInfoId)
    {
     
        $confition = [
            'idleInfoId' => $idleInfoId,
            'sellUserId' => $userId,
            'dealStats' => 1,
        ];
        $this->startTrans();
        try {
            $dealInfo = $this::get($confition);
            
            if (!$dealInfo) {
                return false;
            }
            $dealInfo->dealStats = 10;
            $dealInfo->save();
            
            #恢复idleInfo状态
            $idleInfo = IdleInfo::getDeail($idleInfoId);
            $idleInfo->sellStatus = 0;
            $idleInfo->save();
            $this->commit();
            # 清除redis队列中提示
            OrderDelayed::delOrderDelayedTask(
                json_encode([
                    'action' => 'delDeal',
                    'time' => ($dealInfo->createTime + 900),
                    'data' => ['userId' => $userId,'idleDealId' => $dealInfo['idleDealId']]
                ])
            );
            
            OrderDelayed::delOrderDelayedTask(
                json_encode([
                    'action' => 'sendDeal',
                    'time' => ($dealInfo->createTime + 600),
                    'data' => ['userId' => $userId,'idleDealId' => $dealInfo['idleDealId']]
                ])
            );
            return true;
        } catch (\Exception $th) {
            $this->rollback();
            return false;
        }
    }

    /**
     * @name: 成交率
     * @author: gz
     * @description:
     * @param {*} $userId
     * @return {*}
     */
    public static function turnRate($userId)
    {
        #完成数
        $overNum = self::where(['dealStats' => 4,'sellUserId' => $userId])->count();
        #总deal数
        $allNum = self::where(['dealStats' => ['in',[4,9]],'sellUserId' => $userId])->count();
        if ($allNum == 0) {
            return 100;
        }
        return ceil($overNum / $allNum * 100);
    }

    /**
     * @name: 卖出数
     * @author: gz
     * @description:
     * @param {*} $userId
     * @return {*}
     */
    public static function sellNum($userId)
    {
    
        $sellNum = self::where(['dealStats' => 4,'sellUserId' => $userId])->count();

        return $sellNum;
    }
    /**
     * @name: 在售数
     * @author: gz
     * @description:
     * @param {*} $userId
     * @return {*}
     */
    public static function onlineNum($userId)
    {
    
        $onlineNum = IdleInfo::where(['verifyStatus' => 1,'groundStatus' => 1,'sellStatus' => 0,'userId' => $userId])->count();

        return $onlineNum;
    }
    
    /**
     * @name: 买到数
     * @author: gz
     * @description:
     * @param {*} $userId
     * @return {*}
     */
    public static function buyNum($userId)
    {
    
        $buyNum = self::where(['dealStats' => 4,'buyUserId' => $userId])->count();

        return $buyNum;
    }

    /**
     * @name: 纠纷数
     * @author: gz
     * @description:
     * @param {*} $userId
     * @return {*}
     */
    public static function disputNum($userId)
    {
    
        $disputNum = self::where(['dealStats' => ['in',[5,9]],'sellUserId' => $userId])->count();
        return $disputNum;
    }

    private static function makeDealSn()
    {
        $sn = 'D' . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5);
        return $sn;
    }
}
