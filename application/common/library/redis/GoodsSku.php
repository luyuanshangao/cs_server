<?php

namespace app\common\library\redis;

class GoodsSku extends Redis
{

    /**
     * @name: 设置商品池下商品编号缓存
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function setGoodsSku($pool_id, $data)
    {
        return self::getInstance()->set(CacheKeyMap::goodsSkuString($pool_id), $data);
    }
    
    /**
     * @name: 获取商品池下商品编号缓存通过 pool_id
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getGoodsSkuByPoolId($pool_id)
    {
        return self::getInstance()->get(CacheKeyMap::goodsSkuString($pool_id));
    }

    /**
     * @name: 获取所有商品池下所有商品编号缓存Key
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function allGoodsSkuKeys()
    {
        return self::getInstance()->keys(CacheKeyMap::goodsSkuString('*'));
    }

   /**
     * @name: 获取所有商品池商品编号缓存 通过完整key
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getGoodsPoolByName($name)
    {
        return self::getInstance()->get($name);
    }
}
