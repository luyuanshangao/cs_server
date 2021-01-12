<?php

namespace app\common\library\redis;

class Cart extends Redis
{

    /**
     * @name: 设置购物车缓存
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function setCart($userId, $skuNum, $data)
    {
        
        $result =  self::getInstance()->hSet(CacheKeyMap::cartHash($userId), $skuNum, $data);
        $expireAtStr = time() + 1296000;
        self::getInstance()->expireAt(CacheKeyMap::cartHash($userId), $expireAtStr);
        return $result;
    }

    /**
     * @name: 获取购物车缓存
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getCart($userId, $skuNum)
    {
        return self::getInstance()->hGet(CacheKeyMap::cartHash($userId), $skuNum);
    }
    
    /**
     * @name: 删除购物车中商品
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function delCart($userId, $skuNum)
    {
        return self::getInstance()->hDel(CacheKeyMap::cartHash($userId), $skuNum);
    }

    /**
     * @name: 购物车中所有商品
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function allCart($userId)
    {
        return self::getInstance()->hGetAll(CacheKeyMap::cartHash($userId));
    }
    /**
     * @name: 购物车数量
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function countCart($userId)
    {
        return self::getInstance()->hlen(CacheKeyMap::cartHash($userId));
    }
}
