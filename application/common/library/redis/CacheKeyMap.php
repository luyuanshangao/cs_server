<?php

namespace app\common\library\redis;

/**
 * 缓存key映射类：缓存KEY要统一配置，便于后期批量更改和管理
 * 注意其命名规则： 表名：主键名：主键值：列名
 * Class CacheKeyMap
 */
class CacheKeyMap
{
    public static $prefix = 'cs:';

   /**
     * @name: 三方商品池的 hash
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function goodsPoolHash($pool_id, $prefix = 0)
    {
        if ($prefix) {
            // 用于keys，scan等命令
            return self::$prefix . 'pool:' . $pool_id . ':*';
        }
        return self::$prefix . 'pool:' . $pool_id . ':hash';
    }

   /**
     * @name: 三方商品池下商品编号的 string
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function goodsSkuString($pool_id, $prefix = 0)
    {
        if ($prefix) {
            // 用于keys，scan等命令
            return self::$prefix . 'goods_sku:' . $pool_id . ':*';
        }
        return self::$prefix . 'goods_sku:' . $pool_id . ':string';
    }

    /**
     * @name: 三方商品信息的 hash
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function goodsInfoHash($sku_num, $prefix = 0)
    {
        if ($prefix) {
            // 用于keys，scan等命令
            return self::$prefix . 'goodsInfo:' . $sku_num . ':*';
        }
        return self::$prefix . 'goodsInfo:' . $sku_num . ':hash';
    }
    /**
     * @name: 三方商品KeyName的集合
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function goodsKeySet()
    {
        return self::$prefix . 'goodsKey:' . ':set';
    }

   /**
     * @name: 三级的集合
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function theCateSet()
    {
        return self::$prefix . 'theCate:' . ':set';
    }

    /**
     * @name: 基于商品分类的 string
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function categoryString($catId)
    {
        return self::$prefix . 'category:' . $catId . ':string';
    }

    /**
     * @name: 订单取消延时队列的 sorted_set
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function orderDelayedSet()
    {
        return self::$prefix . 'order:Delayed:set';
    }

    /**
     * @name: 商品缓存操作队列
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    
    public static function goodsCacheUpdateList()
    {
        return self::$prefix . 'goods_cache:Update:list';
    }

    /**
     * @name: 购物车的hash
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function cartHash($userId)
    {
        return self::$prefix . 'cart:' . $userId . ':hash';
    }
}
