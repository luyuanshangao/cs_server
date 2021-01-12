<?php

namespace app\common\library\redis;

use app\api\controller\v1\Vop;
use app\common\library\redis\GoodsInfo;

class GoodsCache extends Redis
{


  /**
   * @name: 读取队列
   * @author: gz
   * @description:
   * @param {type}
   * @return:
   */
    public static function rPopData()
    {
        return self::getInstance()->rPop(CacheKeyMap::goodsCacheUpdateList());
    }

  /**
   * @name: 队列长度
   * @author: gz
   * @description:
   * @param {type}
   * @return:
   */
    public static function lLenData()
    {
        return self::getInstance()->lLen(CacheKeyMap::goodsCacheUpdateList());
    }

  /**
   * @name: 添加队列
   * @author: gz
   * @description:
   * @param {type}
   * @return:
   */
    public static function lPushData($data, $time, $action)
    {
        $cacheData = json_encode([
        'action' => $action,
        'time' => $time,
        'data' => $data,
        ]);
        return self::getInstance()->lPush(CacheKeyMap::goodsCacheUpdateList(), $cacheData);
    }

  /**
   * @name: 消费
   * @author: gz
   * @description:
   * @param {type}
   * @return:
   */
    public static function run($cacheData)
    {

             
      //处理任务
        switch ($cacheData['action']) {
            case 'price':
                $lPushData = $cacheData['data'];
                try {
                    $priceArr = Vop::upGetPrice($lPushData['sku_num']);
                    if ($priceArr['price']) {
                        GoodsInfo::setGoodsInfoOne($lPushData['sku_num'], 'price', $priceArr['price']);
                        GoodsInfo::setGoodsInfoOne($lPushData['sku_num'], 'originPrice', $priceArr['originPrice']);
                        //echo $lPushData['sku_num'] .'已更新价格--'.$priceArr['price'].PHP_EOL;
                    } else {
                        //echo $lPushData['sku_num'] .'更新失败'.PHP_EOL;
                    }
                } catch (\Exception $th) {
                }
                
                break;
            case 'state':
                $lPushData = $cacheData['data'];
                try {
                      GoodsInfo::setGoodsInfoOne($lPushData['sku_num'], 'state', $lPushData['state']);
                } catch (\Exception $th) {
                }
                
                break;
            case 'pool':
                $lPushData = $cacheData['data'];
                try {
                    if ($lPushData['state'] > 1) { //删除
                        GoodsInfo::delKeyByName($lPushData['sku_num']);
                        GoodsInfo::delGoodsKey($lPushData['sku_num']);
                    } else { //添加
                        $Task = new \app\common\library\task\Task();
                        $Task->setGoodsCache($lPushData['sku_num']);
                    }
                } catch (\Exception $th) {
                }
                
                break;
            case 'all':
                $lPushData = $cacheData['data'];
                try {
                    $Task = new \app\common\library\task\Task();
                    $Task->setGoodsCache($lPushData['sku_num']);
                } catch (\Exception $th) {
                }
                
                break;
         
            default:
                break;
        }
    }
}
