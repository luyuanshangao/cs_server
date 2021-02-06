<?php

namespace app\queue\common;

use app\api\controller\v1\Vop;
use app\common\library\redis\GoodsCache;

class NoticeLib
{

    /**
     * @name: 商品类型通知接收
     * @author: gz
     * @description: 商品类型：GoodsMessage，type值包括（2,4,6,16,17）
     * @param {type}
     * @return {type}
     */
    public static function getGoodsMessage()
    {
        //今天开始时间戳
        $timeBegStr = strtotime(date('Y-m-d'));
        
        try {
            $messageNum = 0;
        //商品价格变动 2
            $priceResult  =  Vop::getMessage(2, 'GoodsMessage');
       
            $msgIdArr = [];
            foreach ($priceResult as $value) {
                $timeStr = strtotime($value['time']) + 86400 * 2;
                if ($timeBegStr > $timeStr) {
                    break;//最新通知中 不是今天更新的 直接跳过循环
                }

                $msgIdArr[] = $value['msg_id'];
                $lPushData['msg_id'] = $value['msg_id'];
                $resultArr = json_decode($value['result_json'], true);
                $lPushData =  array_merge($lPushData, $resultArr);
                GoodsCache::lPushData($lPushData, time(), 'price');
                $messageNum += 1;
            }
            //删除消息
            if (count($msgIdArr) > 0) {
                $msgIdStr  = implode(',', $msgIdArr);
                Vop::delMessage($msgIdStr);
            }
            
            
        //商品上下架变动 4
            $stateResult =  Vop::getMessage(4, 'GoodsMessage');
            $msgIdArr_2 = [];
            foreach ($stateResult as $value) {
                $timeStr = strtotime($value['time']) + 86400;
                if ($timeBegStr > $timeStr) {
                    break;//最新通知中 不是今天更新的 直接跳过循环
                }
                $msgIdArr_2[] = $value['msg_id'];
                $lPushData['msg_id'] = $value['msg_id'];
                $resultArr = json_decode($value['result_json'], true);
                $lPushData =  array_merge($lPushData, $resultArr);
                GoodsCache::lPushData($lPushData, time(), 'state');
                $messageNum += 1;
            }
            if (count($msgIdArr_2) > 0) {
                $msgIdStr  = implode(',', $msgIdArr_2);
                Vop::delMessage($msgIdStr);
            }
           
        //添加、删除商品库内商品 6
            $poolResult =  Vop::getMessage(6, 'GoodsMessage');
            $msgIdArr_3 = [];
            foreach ($poolResult as $value) {
                $timeStr = strtotime($value['time']) + 86400;
                if ($timeBegStr > $timeStr) {
                    break;//最新通知中 不是今天更新的 直接跳过循环
                }
                $msgIdArr_3[] = $value['msg_id'];
                $lPushData['msg_id'] = $value['msg_id'];
                $resultArr = json_decode($value['result_json'], true);
                $lPushData =  array_merge($lPushData, $resultArr);
                GoodsCache::lPushData($lPushData, time(), 'pool');
                $messageNum += 1;
            }
            
            if (count($msgIdArr_3) > 0) {
                $msgIdStr  = implode(',', $msgIdArr_3);
                Vop::delMessage($msgIdStr);
            }
            
            return $messageNum;
        //商品介绍及规格参数变更通知 16
        //赠品促销变更通知 17
        } catch (\Exception $th) {
            //throw $th;
        }
    }


        /**
     * @name: 商品类型通知接收
     * @author: gz
     * @description:  订单类型 type值包括（1,5,10,12,14,15,18,23,25,28,31）
     * @param {type}
     * @return {type}
     */
    public static function getOrderMessage()
    {
        //不使用通知修改订单状态
        try {
        //代表订单拆分变更 1
        //代表该订单已妥投 5
        //代表订单取消 10
        //代表配送单生成（打包完成后通知，仅提供给买卖宝类型客户） 12
        //支付失败通知 14
        //7天未支付取消通知/未确认取消 15
        //订单等待确认收货通知 18
        //订单配送退货通知 23
        //新订单通知 25
        //售后服务单状态变更 28
        //订单完成通知 31
        } catch (\Exception $th) {
            //throw $th;
        }
    }


    /**
     * @name: 商品缓存 消费
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function run()
    {
        //消费
        try {
            $num = GoodsCache::lLenData();
            if ($num !== 0) {
                $data = GoodsCache::rPopData();
                $runData = json_decode($data, true);
                GoodsCache::run($runData);
                \app\common\library\Log::mylog('swoole','当前剩余:'.$num,'swoole_cache'); 
            }else{
                \app\common\library\Log::mylog('swoole','完成缓存队列','swoole_cache'); 
            }
            return;
        } catch (\Exception $th) {
            return;
        }
    }


    /**
     * @name: 删除缓存集合中商品缓存不在的
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public static function clearCache()
    {
        try {
            //清除无用的缓存
            $GoodsInfo = new \app\common\library\redis\GoodsInfo();
            $allGoodsInfoKeyArr = $GoodsInfo::getAllGoodsKey();
           
            foreach ($allGoodsInfoKeyArr as $keyName) {
                $info = $GoodsInfo::getAllGoodsInfoByName($keyName);
                if (!$info) {
                     \app\common\library\redis\GoodsInfo::delGoodsKeyByName($keyName);
                }
            }
        } catch (\Exception $th) {
        }
    }
}
