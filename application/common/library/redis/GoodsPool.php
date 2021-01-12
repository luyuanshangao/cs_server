<?php

namespace app\common\library\redis;

class GoodsPool extends Redis
{

    /**
     * @name: 设置商品池缓存
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function setGoodsPool($pool_id, $data)
    {
        return self::getInstance()->hMSet(CacheKeyMap::goodsPoolHash($pool_id), $data);
    }
    
    /**
     * @name: 获取商品池缓存通过 pool_id
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getGoodsPoolByPoolId($pool_id, $fields)
    {
        return self::getInstance()->hMGet(CacheKeyMap::goodsPoolHash($pool_id), $fields);
    }

    /**
     * @name: 获取所有商品池缓存Key
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function allGoodsPoolKeys()
    {
        return self::getInstance()->keys(CacheKeyMap::goodsPoolHash('*'));
    }

   /**
     * @name: 获取所有商品池缓存 通过完整key
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getGoodsPoolByName($name, $fields)
    {

        return self::getInstance()->hMGet($name, $fields);
    }
}
